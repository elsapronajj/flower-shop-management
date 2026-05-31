import { useEffect, useMemo, useState } from 'react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { api, getList, unwrap } from '../api/client'

const modules = {
  flowers: {
    title: 'Flowers',
    description: 'Manage flower inventory, colors, seasons, prices, stock, and photos.',
    endpoint: '/flowers',
    detail: true,
    fields: [
      ['name', 'Name', 'text'],
      ['type', 'Type', 'text'],
      ['description', 'Description', 'textarea'],
      ['category_id', 'Category', 'select', 'categories'],
      ['supplier_id', 'Supplier', 'select', 'suppliers'],
      ['price', 'Price', 'number'],
      ['stock_quantity', 'Stock Quantity', 'number'],
      ['season', 'Season', 'text'],
      ['lifespan_days', 'Lifespan Days', 'number'],
      ['color', 'Color', 'text'],
      ['image_url', 'Image URL', 'url'],
      ['photo', 'Photo URL', 'url'],
      ['is_active', 'Active', 'checkbox'],
    ],
    columns: [
      ['name', 'Name'],
      ['type', 'Type'],
      ['category.name', 'Category'],
      ['supplier.name', 'Supplier'],
      ['price', 'Price', (value) => `$${Number(value).toFixed(2)}`],
      ['stock_quantity', 'Stock'],
      ['is_active', 'Active', (value) => (value ? 'Yes' : 'No')],
    ],
    empty: {
      name: '',
      type: '',
      description: '',
      category_id: '',
      supplier_id: '',
      price: '',
      stock_quantity: 0,
      season: '',
      lifespan_days: '',
      color: '',
      image_url: '',
      photo: '',
      is_active: true,
    },
  },
  bouquets: {
    title: 'Bouquets',
    description: 'Create bouquets and define their flower composition.',
    endpoint: '/bouquets',
    detail: true,
    fields: [
      ['name', 'Name', 'text'],
      ['description', 'Description', 'textarea'],
      ['price', 'Price', 'number'],
      ['size', 'Size', 'text'],
      ['photo', 'Photo URL', 'url'],
      ['is_active', 'Active', 'checkbox'],
    ],
    columns: [
      ['name', 'Name'],
      ['size', 'Size'],
      ['price', 'Price', (value) => `$${Number(value).toFixed(2)}`],
      ['is_active', 'Active', (value) => (value ? 'Yes' : 'No')],
    ],
    empty: { name: '', description: '', price: '', size: '', photo: '', is_active: true },
  },
  'bouquet-flowers': {
    title: 'Bouquet Flowers',
    description: 'Define which flowers and quantities belong to each bouquet.',
    endpoint: '/bouquet-flowers',
    fields: [
      ['bouquet_id', 'Bouquet', 'select', 'bouquets'],
      ['flower_id', 'Flower', 'select', 'flowers'],
      ['quantity', 'Quantity', 'number'],
    ],
    columns: [
      ['bouquet.name', 'Bouquet'],
      ['flower.name', 'Flower'],
      ['quantity', 'Quantity'],
    ],
    empty: { bouquet_id: '', flower_id: '', quantity: 1 },
  },
  categories: {
    title: 'Categories',
    description: 'Organize flowers into categories.',
    endpoint: '/categories',
    fields: [
      ['name', 'Name', 'text'],
      ['description', 'Description', 'textarea'],
    ],
    columns: [
      ['name', 'Name'],
      ['description', 'Description'],
      ['flowers_count', 'Flowers'],
    ],
    empty: { name: '', description: '' },
  },
  suppliers: {
    title: 'Suppliers',
    description: 'Manage supplier contact details and specialties.',
    endpoint: '/suppliers',
    fields: [
      ['name', 'Name', 'text'],
      ['contact', 'Contact', 'text'],
      ['phone', 'Phone', 'text'],
      ['email', 'Email', 'email'],
      ['address', 'Address', 'textarea'],
      ['specialty', 'Specialty', 'text'],
    ],
    columns: [
      ['name', 'Name'],
      ['contact', 'Contact'],
      ['phone', 'Phone'],
      ['email', 'Email'],
      ['specialty', 'Specialty'],
      ['flowers_count', 'Flowers'],
    ],
    empty: { name: '', contact: '', phone: '', email: '', address: '', specialty: '' },
  },
  customers: {
    title: 'Customers',
    description: 'Store customer details, VIP status, and order history.',
    endpoint: '/customers',
    fields: [
      ['first_name', 'First Name', 'text'],
      ['last_name', 'Last Name', 'text'],
      ['phone', 'Phone', 'text'],
      ['email', 'Email', 'email'],
      ['address', 'Address', 'textarea'],
      ['registration_date', 'Registration Date', 'date'],
      ['is_vip', 'VIP', 'checkbox'],
    ],
    columns: [
      ['full_name', 'Name'],
      ['phone', 'Phone'],
      ['email', 'Email'],
      ['is_vip', 'VIP', (value) => (value ? 'Yes' : 'No')],
      ['orders_count', 'Orders'],
    ],
    empty: { first_name: '', last_name: '', phone: '', email: '', address: '', registration_date: '', is_vip: false },
  },
  deliveries: {
    title: 'Deliveries',
    description: 'Track delivery dates, couriers, statuses, and recipient signatures.',
    endpoint: '/deliveries',
    fields: [
      ['order_id', 'Order', 'select', 'orders'],
      ['courier_id', 'Courier', 'select', 'users'],
      ['delivery_date', 'Delivery Date', 'date'],
      ['delivery_time', 'Delivery Time', 'time'],
      ['status', 'Status', 'select', ['pending', 'in_transit', 'delivered', 'failed']],
      ['recipient_signature', 'Recipient Signature', 'textarea'],
    ],
    columns: [
      ['order.id', 'Order', (value) => `#${value}`],
      ['courier.name', 'Courier'],
      ['delivery_date', 'Date'],
      ['delivery_time', 'Time'],
      ['status', 'Status'],
    ],
    empty: { order_id: '', courier_id: '', delivery_date: '', delivery_time: '', status: 'pending', recipient_signature: '' },
  },
  occasions: {
    title: 'Occasions',
    description: 'Manage special events, dates, and discount percentages.',
    endpoint: '/occasions',
    fields: [
      ['name', 'Name', 'text'],
      ['description', 'Description', 'textarea'],
      ['event_date', 'Event Date', 'date'],
      ['discount_percentage', 'Discount Percentage', 'number'],
    ],
    columns: [
      ['name', 'Name'],
      ['event_date', 'Event Date'],
      ['discount_percentage', 'Discount %'],
    ],
    empty: { name: '', description: '', event_date: '', discount_percentage: 0 },
  },
  'supply-orders': {
    title: 'Supply Orders',
    description: 'Manage purchase orders from suppliers.',
    endpoint: '/supply-orders',
    fields: [
      ['supplier_id', 'Supplier', 'select', 'suppliers'],
      ['order_date', 'Order Date', 'date'],
      ['total_amount', 'Total Amount', 'number'],
      ['status', 'Status', 'select', ['pending', 'ordered', 'received', 'cancelled']],
    ],
    columns: [
      ['supplier.name', 'Supplier'],
      ['order_date', 'Order Date'],
      ['total_amount', 'Total', (value) => `$${Number(value).toFixed(2)}`],
      ['status', 'Status'],
    ],
    empty: { supplier_id: '', order_date: '', total_amount: 0, status: 'pending' },
  },
  reviews: {
    title: 'Reviews',
    description: 'Track customer ratings and comments for completed orders.',
    endpoint: '/reviews',
    fields: [
      ['customer_id', 'Customer', 'select', 'customers'],
      ['order_id', 'Order', 'select', 'orders'],
      ['rating', 'Rating', 'number'],
      ['comment', 'Comment', 'textarea'],
      ['review_date', 'Review Date', 'date'],
    ],
    columns: [
      ['customer.full_name', 'Customer'],
      ['order.id', 'Order', (value) => `#${value}`],
      ['rating', 'Rating'],
      ['review_date', 'Review Date'],
    ],
    empty: { customer_id: '', order_id: '', rating: 5, comment: '', review_date: '' },
  },
  users: {
    title: 'Users',
    description: 'Manage system users and their assigned roles.',
    endpoint: '/users',
    fields: [
      ['name', 'Name', 'text'],
      ['email', 'Email', 'email'],
      ['password', 'Password', 'password'],
      ['role_ids', 'Roles', 'multiselect', 'roles'],
    ],
    columns: [
      ['name', 'Name'],
      ['email', 'Email'],
      ['roles', 'Roles', (value) => value?.map((role) => role.name).join(', ') || '-'],
    ],
    empty: { name: '', email: '', password: '', role_ids: [] },
  },
  roles: {
    title: 'Roles',
    description: 'Define roles that can be assigned to users.',
    endpoint: '/roles',
    fields: [
      ['name', 'Name', 'text'],
      ['description', 'Description', 'textarea'],
    ],
    columns: [
      ['name', 'Name'],
      ['description', 'Description'],
    ],
    empty: { name: '', description: '' },
  },
}

const lookupEndpoints = {
  bouquets: '/bouquets',
  categories: '/categories',
  customers: '/customers',
  flowers: '/flowers',
  orders: '/orders',
  roles: '/roles',
  suppliers: '/suppliers',
  users: '/users',
}

function valueAt(row, path) {
  return path.split('.').reduce((value, part) => value?.[part], row)
}

function normalizeForm(config, source = {}) {
  return Object.fromEntries(
    Object.entries(config.empty).map(([key, fallback]) => [key, source[key] ?? fallback]),
  )
}

function normalizePayload(form) {
  return Object.fromEntries(
    Object.entries(form).map(([key, value]) => [key, value === '' ? null : value]),
  )
}

function optionLabel(type, option) {
  if (type === 'orders') return `#${option.id} - ${option.customer?.full_name || 'Order'}`
  if (type === 'users') return `${option.name} (${option.email})`
  return option.full_name || option.name || `#${option.id}`
}

function StatusCell({ value }) {
  if (!value) return <span>-</span>
  return <span className={`status ${value}`}>{value.replace('_', ' ')}</span>
}

function useCrudConfig(type) {
  return modules[type]
}

export function CrudList({ type }) {
  const config = useCrudConfig(type)
  const [items, setItems] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    api
      .get(config.endpoint)
      .then((response) => setItems(getList(unwrap(response))))
      .finally(() => setLoading(false))
  }, [config.endpoint])

  async function deleteItem(id) {
    if (!window.confirm(`Delete this ${type.slice(0, -1)}? This action cannot be undone.`)) return
    await api.delete(`${config.endpoint}/${id}`)
    setItems((current) => current.filter((item) => item.id !== id))
  }

  return (
    <section className="page">
      <div className="page-heading">
        <div>
          <h1>{config.title}</h1>
          <p>{config.description || `View, create, edit, and remove ${config.title.toLowerCase()}.`}</p>
        </div>
        <Link className="button" to={`/${type}/new`}>
          Add {config.title.slice(0, -1)}
        </Link>
      </div>

      <div className="table-wrap">
        <table>
          <thead>
            <tr>
              {config.columns.map(([, label]) => (
                <th key={label}>{label}</th>
              ))}
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {loading && (
              <tr>
                <td colSpan={config.columns.length + 1} className="table-message">
                  Loading {config.title.toLowerCase()}...
                </td>
              </tr>
            )}
            {!loading && items.length === 0 && (
              <tr>
                <td colSpan={config.columns.length + 1} className="table-message">
                  No {config.title.toLowerCase()} found.{' '}
                  <Link to={`/${type}/new`}>Add the first one.</Link>
                </td>
              </tr>
            )}
            {!loading &&
              items.map((item) => (
                <tr key={item.id}>
                  {config.columns.map(([path, label, format]) => {
                    const rawValue = valueAt(item, path)
                    if (label === 'Status' && !format) {
                      return (
                        <td key={label}>
                          <StatusCell value={rawValue} />
                        </td>
                      )
                    }
                    return <td key={label}>{format ? format(rawValue, item) : rawValue ?? '-'}</td>
                  })}
                  <td className="actions">
                    {config.detail && (
                      <Link to={`/${type}/${item.id}`}>View</Link>
                    )}
                    <Link to={`/${type}/${item.id}/edit`}>Edit</Link>
                    <button type="button" className="link-danger" onClick={() => deleteItem(item.id)}>
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

export function CrudForm({ type }) {
  const config = useCrudConfig(type)
  const { id } = useParams()
  const navigate = useNavigate()
  const [form, setForm] = useState(() => config.empty)
  const [lookups, setLookups] = useState({})
  const [error, setError] = useState(null)
  const [saving, setSaving] = useState(false)
  const isEdit = Boolean(id)

  useEffect(() => {
    const lookupNames = [
      ...new Set(config.fields.map((field) => field[3]).filter((lookup) => typeof lookup === 'string')),
    ]

    Promise.all(
      lookupNames.map((lookup) =>
        api.get(lookupEndpoints[lookup]).then((response) => [lookup, getList(unwrap(response))]),
      ),
    ).then((results) => setLookups(Object.fromEntries(results)))
  }, [config.fields])

  useEffect(() => {
    if (!isEdit) return
    api
      .get(`${config.endpoint}/${id}`)
      .then((response) => setForm(normalizeForm(config, unwrap(response))))
  }, [config, id, isEdit])

  async function handleSubmit(event) {
    event.preventDefault()
    setSaving(true)
    setError(null)

    try {
      const payload = normalizePayload(form)
      if (isEdit) {
        await api.put(`${config.endpoint}/${id}`, payload)
      } else {
        await api.post(config.endpoint, payload)
      }
      navigate(`/${type}`)
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
          <h1>
            {isEdit ? 'Edit' : 'Add'} {config.title.slice(0, -1)}
          </h1>
          <p>Fields marked as required are validated by the API.</p>
        </div>
      </div>

      {error && (
        <div className="error-box">
          <strong>{error.response?.data?.message || error.message}</strong>
          {error.response?.data?.errors &&
            Object.entries(error.response.data.errors).map(([field, messages]) => (
              <span key={field}>{messages.join(' ')}</span>
            ))}
        </div>
      )}

      <div className="form-card">
        <form className="form-grid" onSubmit={handleSubmit}>
          {config.fields.map(([name, label, inputType, lookup]) => (
            <label key={name} className={inputType === 'checkbox' ? 'check-row' : undefined}>
              {inputType !== 'checkbox' && label}
              {inputType === 'textarea' && (
                <textarea
                  value={form[name] || ''}
                  onChange={(event) => setForm({ ...form, [name]: event.target.value })}
                />
              )}
              {inputType === 'select' && (
                <select
                  value={form[name] || ''}
                  onChange={(event) => setForm({ ...form, [name]: event.target.value })}
                >
                  <option value="">Select {label.toLowerCase()}</option>
                  {(Array.isArray(lookup) ? lookup : lookups[lookup] || []).map((option) => (
                    <option key={option.id || option} value={option.id || option}>
                      {typeof option === 'string' ? option : optionLabel(lookup, option)}
                    </option>
                  ))}
                </select>
              )}
              {inputType === 'multiselect' && (
                <select
                  multiple
                  value={form[name] || []}
                  onChange={(event) =>
                    setForm({
                      ...form,
                      [name]: Array.from(event.target.selectedOptions).map((option) => option.value),
                    })
                  }
                >
                  {lookups[lookup]?.map((option) => (
                    <option key={option.id} value={option.id}>
                      {optionLabel(lookup, option)}
                    </option>
                  ))}
                </select>
              )}
              {inputType === 'checkbox' && (
                <>
                  <input
                    type="checkbox"
                    checked={Boolean(form[name])}
                    onChange={(event) => setForm({ ...form, [name]: event.target.checked })}
                  />
                  {label}
                </>
              )}
              {!['textarea', 'select', 'multiselect', 'checkbox'].includes(inputType) && (
                <input
                  type={inputType}
                  min={inputType === 'number' ? '0' : undefined}
                  step={name === 'price' || name === 'total_amount' ? '0.01' : undefined}
                  value={form[name] ?? ''}
                  onChange={(event) => setForm({ ...form, [name]: event.target.value })}
                />
              )}
            </label>
          ))}
          <div className="form-actions">
            <button type="submit" disabled={saving}>
              {saving ? 'Saving...' : isEdit ? 'Save Changes' : 'Create'}
            </button>
            <Link className="secondary button" to={`/${type}`}>
              Cancel
            </Link>
          </div>
        </form>
      </div>
    </section>
  )
}

export function CrudDetail({ type }) {
  const config = useCrudConfig(type)
  const { id } = useParams()
  const fields = useMemo(() => config.columns, [config.columns])
  const [item, setItem] = useState(null)

  useEffect(() => {
    api.get(`${config.endpoint}/${id}`).then((response) => setItem(unwrap(response)))
  }, [config.endpoint, id])

  if (!item) {
    return (
      <section className="page narrow">
        <p className="loading-text">Loading record...</p>
      </section>
    )
  }

  return (
    <section className="page narrow">
      <div className="page-heading">
        <div>
          <h1>{item.name || `${config.title.slice(0, -1)} #${item.id}`}</h1>
          <p>Detailed record information.</p>
        </div>
        <div className="page-heading-actions">
          <Link className="button secondary" to={`/${type}`}>
            Back
          </Link>
          <Link className="button" to={`/${type}/${item.id}/edit`}>
            Edit
          </Link>
        </div>
      </div>
      <dl className="details">
        {fields.map(([path, label, format]) => {
          const value = valueAt(item, path)
          return (
            <div key={label}>
              <dt>{label}</dt>
              <dd>{format ? format(value, item) : value ?? '-'}</dd>
            </div>
          )
        })}
        {item.description && (
          <div>
            <dt>Description</dt>
            <dd>{item.description}</dd>
          </div>
        )}
        {item.image_url && (
          <div>
            <dt>Image URL</dt>
            <dd>{item.image_url}</dd>
          </div>
        )}
      </dl>
    </section>
  )
}
