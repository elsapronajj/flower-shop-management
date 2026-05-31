import { useEffect, useMemo, useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { api, getList, unwrap } from '../api/client'

const emptyOrder = {
  customer_id: '',
  order_date: new Date().toISOString().slice(0, 16),
  delivery_date: '',
  delivery_address: '',
  card_message: '',
  status: 'pending',
  items: [{ item_type: 'flower', flower_id: '', bouquet_id: '', quantity: 1 }],
}

function formatDateForInput(value) {
  if (!value) return new Date().toISOString().slice(0, 16)
  return new Date(value).toISOString().slice(0, 16)
}

function orderPayload(form) {
  return {
    customer_id: form.customer_id,
    order_date: form.order_date,
    delivery_date: form.delivery_date || null,
    delivery_address: form.delivery_address || null,
    card_message: form.card_message || null,
    status: form.status,
    items: form.items.map((item) => ({
      flower_id: item.item_type === 'flower' ? item.flower_id : null,
      bouquet_id: item.item_type === 'bouquet' ? item.bouquet_id : null,
      quantity: Number(item.quantity),
    })),
  }
}

function errorMessages(error) {
  if (!error) return []
  if (error.messages) return error.messages
  const errors = error.response?.data?.errors
  if (!errors) return [error.response?.data?.message || error.message]
  return Object.values(errors).flat()
}

function validateStock(form, flowers, bouquets, originalOrder) {
  const requested = {}
  const restored = {}

  if (originalOrder?.status !== 'cancelled') {
    originalOrder?.items?.forEach((item) => {
      if (item.flower_id) {
        restored[item.flower_id] = (restored[item.flower_id] || 0) + Number(item.quantity || 0)
      }
      if (item.bouquet?.bouquet_flowers) {
        item.bouquet.bouquet_flowers.forEach((composition) => {
          restored[composition.flower_id] =
            (restored[composition.flower_id] || 0) +
            Number(composition.quantity || 0) * Number(item.quantity || 0)
        })
      }
    })
  }

  form.items.forEach((item) => {
    if (item.item_type === 'flower' && item.flower_id) {
      requested[item.flower_id] = (requested[item.flower_id] || 0) + Number(item.quantity || 0)
    }
    if (item.item_type === 'bouquet' && item.bouquet_id) {
      const bouquet = bouquets.find((candidate) => String(candidate.id) === String(item.bouquet_id))
      bouquet?.bouquet_flowers?.forEach((composition) => {
        requested[composition.flower_id] =
          (requested[composition.flower_id] || 0) +
          Number(composition.quantity || 0) * Number(item.quantity || 0)
      })
    }
  })

  const messages = Object.entries(requested)
    .map(([flowerId, quantity]) => {
      const flower = flowers.find((candidate) => String(candidate.id) === String(flowerId))
      const available = Number(flower?.stock_quantity || 0) + Number(restored[flowerId] || 0)
      if (quantity > available) {
        return `${flower?.name || 'Selected flower'} has only ${available} in stock. Requested: ${quantity}.`
      }
      return null
    })
    .filter(Boolean)

  return messages.length ? { messages } : null
}

function availableStockForFlower(flower, originalOrder) {
  if (!flower) return undefined
  const restored =
    originalOrder?.status === 'cancelled'
      ? 0
      : originalOrder?.items
          ?.filter((item) => String(item.flower_id) === String(flower.id))
          .reduce((total, item) => total + Number(item.quantity || 0), 0) || 0
  return Number(flower.stock_quantity || 0) + restored
}

export function OrdersList() {
  const [orders, setOrders] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    api
      .get('/orders')
      .then((response) => setOrders(getList(unwrap(response))))
      .finally(() => setLoading(false))
  }, [])

  async function deleteOrder(id) {
    if (!window.confirm('Delete this order? Stock will be restored when applicable.')) return
    await api.delete(`/orders/${id}`)
    setOrders((current) => current.filter((order) => order.id !== id))
  }

  return (
    <section className="page">
      <div className="page-heading">
        <div>
          <h1>Orders</h1>
          <p>Create orders, update statuses, and keep stock quantities in sync.</p>
        </div>
        <Link className="button" to="/orders/new">
          New Order
        </Link>
      </div>

      <div className="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Order</th>
              <th>Customer</th>
              <th>Date</th>
              <th>Status</th>
              <th>Total</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {loading && (
              <tr>
                <td colSpan="6" className="table-message">Loading orders...</td>
              </tr>
            )}
            {!loading && orders.length === 0 && (
              <tr>
                <td colSpan="6" className="table-message">
                  No orders yet. <Link to="/orders/new">Create the first one.</Link>
                </td>
              </tr>
            )}
            {!loading &&
              orders.map((order) => (
                <tr key={order.id}>
                  <td>
                    <Link to={`/orders/${order.id}`} className="table-link">
                      #{order.id}
                    </Link>
                  </td>
                  <td>{order.customer?.full_name || '-'}</td>
                  <td className="nowrap">{new Date(order.order_date).toLocaleDateString()}</td>
                  <td>
                    <span className={`status ${order.status}`}>{order.status}</span>
                  </td>
                  <td className="nowrap">${Number(order.total_amount).toFixed(2)}</td>
                  <td className="actions">
                    <Link to={`/orders/${order.id}`}>View</Link>
                    <Link to={`/orders/${order.id}/edit`}>Edit</Link>
                    <button type="button" className="link-danger" onClick={() => deleteOrder(order.id)}>
                      Delete
                    </button>
                  </td>
                </tr>
              ))}
          </tbody>
        </table>
      </div>
    </section>
  )
}

export function OrderForm() {
  const { id } = useParams()
  const navigate = useNavigate()
  const [form, setForm] = useState(emptyOrder)
  const [customers, setCustomers] = useState([])
  const [flowers, setFlowers] = useState([])
  const [bouquets, setBouquets] = useState([])
  const [originalOrder, setOriginalOrder] = useState(null)
  const [error, setError] = useState(null)
  const [saving, setSaving] = useState(false)
  const isEdit = Boolean(id)

  useEffect(() => {
    Promise.all([
      api.get('/customers').then((response) => getList(unwrap(response))),
      api.get('/flowers').then((response) => getList(unwrap(response))),
      api.get('/bouquets').then((response) => getList(unwrap(response))),
    ]).then(([customerData, flowerData, bouquetData]) => {
      setCustomers(customerData)
      setFlowers(flowerData.filter((flower) => flower.is_active))
      setBouquets(bouquetData.filter((bouquet) => bouquet.is_active))
    })
  }, [])

  useEffect(() => {
    if (!isEdit) return
    api.get(`/orders/${id}`).then((response) => {
      const order = unwrap(response)
      setOriginalOrder(order)
      setForm({
        customer_id: order.customer_id,
        order_date: formatDateForInput(order.order_date),
        delivery_date: order.delivery_date || '',
        delivery_address: order.delivery_address || '',
        card_message: order.card_message || '',
        status: order.status,
        items: order.items.map((item) => ({
          item_type: item.bouquet_id ? 'bouquet' : 'flower',
          flower_id: item.flower_id,
          bouquet_id: item.bouquet_id,
          quantity: item.quantity,
        })),
      })
    })
  }, [id, isEdit])

  const previewTotal = useMemo(
    () =>
      form.items.reduce((total, item) => {
        const flower = flowers.find((candidate) => String(candidate.id) === String(item.flower_id))
        const bouquet = bouquets.find((candidate) => String(candidate.id) === String(item.bouquet_id))
        const price = item.item_type === 'bouquet' ? bouquet?.price : flower?.price
        return total + Number(price || 0) * Number(item.quantity || 0)
      }, 0),
    [bouquets, flowers, form.items],
  )

  function updateItem(index, patch) {
    setForm((current) => ({
      ...current,
      items: current.items.map((item, itemIndex) =>
        itemIndex === index ? { ...item, ...patch } : item,
      ),
    }))
  }

  async function handleSubmit(event) {
    event.preventDefault()
    setError(null)

    const stockError = validateStock(form, flowers, bouquets, originalOrder)
    if (stockError) {
      setError(stockError)
      return
    }

    setSaving(true)
    try {
      if (isEdit) {
        await api.put(`/orders/${id}`, orderPayload(form))
      } else {
        await api.post('/orders', orderPayload(form))
      }
      navigate('/orders')
    } catch (caught) {
      setError(caught)
    } finally {
      setSaving(false)
    }
  }

  return (
    <section className="page narrow">
      <div className="page-heading">
        <div>
          <h1>{isEdit ? 'Edit' : 'Create'} Order</h1>
          <p>Unit prices, subtotals, and total amount are calculated by the API.</p>
        </div>
      </div>

      {error && (
        <div className="error-box">
          {errorMessages(error).map((message) => (
            <span key={message}>{message}</span>
          ))}
        </div>
      )}

      <div className="form-card">
        <form className="form-grid" onSubmit={handleSubmit}>
          <div className="form-row-2">
            <label>
              Customer
              <select
                value={form.customer_id}
                onChange={(event) => setForm({ ...form, customer_id: event.target.value })}
              >
                <option value="">Select customer</option>
                {customers.map((customer) => (
                  <option key={customer.id} value={customer.id}>
                    {customer.full_name}
                  </option>
                ))}
              </select>
            </label>
            <label>
              Status
              <select
                value={form.status}
                onChange={(event) => setForm({ ...form, status: event.target.value })}
              >
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </label>
          </div>

          <div className="form-row-2">
            <label>
              Order Date
              <input
                type="datetime-local"
                value={form.order_date}
                onChange={(event) => setForm({ ...form, order_date: event.target.value })}
              />
            </label>
            <label>
              Delivery Date
              <input
                type="date"
                value={form.delivery_date}
                onChange={(event) => setForm({ ...form, delivery_date: event.target.value })}
              />
            </label>
          </div>

          <label>
            Delivery Address
            <textarea
              value={form.delivery_address}
              onChange={(event) => setForm({ ...form, delivery_address: event.target.value })}
              placeholder="Street, city, postal code..."
            />
          </label>

          <label>
            Card Message
            <textarea
              value={form.card_message}
              onChange={(event) => setForm({ ...form, card_message: event.target.value })}
              placeholder="Optional message for the recipient..."
            />
          </label>

          <div className="order-items">
            <div className="section-title">
              <h2>Order Items</h2>
              <button
                type="button"
                className="secondary"
                onClick={() =>
                  setForm({
                    ...form,
                    items: [
                      ...form.items,
                      { item_type: 'flower', flower_id: '', bouquet_id: '', quantity: 1 },
                    ],
                  })
                }
              >
                + Add Item
              </button>
            </div>

            {form.items.map((item, index) => {
              const flower = flowers.find(
                (candidate) => String(candidate.id) === String(item.flower_id),
              )
              const bouquet = bouquets.find(
                (candidate) => String(candidate.id) === String(item.bouquet_id),
              )
              const availableStock = availableStockForFlower(flower, originalOrder)
              const itemPrice = Number(
                (item.item_type === 'bouquet' ? bouquet?.price : flower?.price) || 0,
              )
              const subtotal = itemPrice * Number(item.quantity || 0)

              return (
                <div className="item-row" key={`item-${index}`}>
                  <label>
                    Type
                    <select
                      value={item.item_type}
                      onChange={(event) =>
                        updateItem(index, {
                          item_type: event.target.value,
                          flower_id: '',
                          bouquet_id: '',
                        })
                      }
                    >
                      <option value="flower">Flower</option>
                      <option value="bouquet">Bouquet</option>
                    </select>
                  </label>

                  <label>
                    {item.item_type === 'bouquet' ? 'Bouquet' : 'Flower'}
                    <select
                      value={item.item_type === 'bouquet' ? item.bouquet_id : item.flower_id}
                      onChange={(event) =>
                        updateItem(
                          index,
                          item.item_type === 'bouquet'
                            ? { bouquet_id: event.target.value }
                            : { flower_id: event.target.value },
                        )
                      }
                    >
                      <option value="">Select {item.item_type}</option>
                      {(item.item_type === 'bouquet' ? bouquets : flowers).map((option) => (
                        <option key={option.id} value={option.id}>
                          {option.name}
                          {item.item_type === 'flower' ? ` (${option.stock_quantity} in stock)` : ''}
                        </option>
                      ))}
                    </select>
                  </label>

                  <label>
                    Qty
                    <input
                      type="number"
                      min="1"
                      max={availableStock}
                      value={item.quantity}
                      onChange={(event) => updateItem(index, { quantity: event.target.value })}
                    />
                  </label>

                  <div className="item-subtotal">
                    <span className="item-subtotal-label">Subtotal</span>
                    <strong>${subtotal.toFixed(2)}</strong>
                  </div>

                  <div className="item-remove">
                    <button
                      type="button"
                      className="link-danger"
                      disabled={form.items.length === 1}
                      onClick={() =>
                        setForm({
                          ...form,
                          items: form.items.filter((_, itemIndex) => itemIndex !== index),
                        })
                      }
                    >
                      Remove
                    </button>
                  </div>
                </div>
              )
            })}
          </div>

          <div className="total-preview">
            <span>Estimated total</span>
            <strong>${previewTotal.toFixed(2)}</strong>
          </div>

          <div className="form-actions">
            <button type="submit" disabled={saving}>
              {saving ? 'Saving...' : isEdit ? 'Save Changes' : 'Create Order'}
            </button>
            <Link className="secondary button" to="/orders">
              Cancel
            </Link>
          </div>
        </form>
      </div>
    </section>
  )
}

export function OrderDetail() {
  const { id } = useParams()
  const [order, setOrder] = useState(null)

  useEffect(() => {
    api.get(`/orders/${id}`).then((response) => setOrder(unwrap(response)))
  }, [id])

  if (!order) {
    return (
      <section className="page">
        <p className="loading-text">Loading order...</p>
      </section>
    )
  }

  return (
    <section className="page">
      <div className="page-heading">
        <div>
          <h1>Order #{order.id}</h1>
          <p>
            {order.customer?.full_name || 'Unknown customer'} &middot;{' '}
            {new Date(order.order_date).toLocaleString()}
          </p>
        </div>
        <div className="page-heading-actions">
          <Link className="button secondary" to="/orders">
            Back
          </Link>
          <Link className="button" to={`/orders/${order.id}/edit`}>
            Edit
          </Link>
        </div>
      </div>

      <div className="summary-line">
        <span className={`status ${order.status}`}>{order.status}</span>
        <div className="summary-meta">
          {order.delivery_date && (
            <span>Delivery: {new Date(order.delivery_date).toLocaleDateString()}</span>
          )}
          {order.delivery_address && <span>{order.delivery_address}</span>}
        </div>
        <strong className="summary-total">${Number(order.total_amount).toFixed(2)}</strong>
      </div>

      <div className="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Item</th>
              <th>Quantity</th>
              <th>Unit Price</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            {order.items.map((item) => (
              <tr key={item.id}>
                <td>{item.flower?.name || item.bouquet?.name || '-'}</td>
                <td>{item.quantity}</td>
                <td>${Number(item.unit_price).toFixed(2)}</td>
                <td>${Number(item.subtotal).toFixed(2)}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      {order.card_message && (
        <div className="order-note">
          <strong>Card message:</strong> {order.card_message}
        </div>
      )}
    </section>
  )
}
