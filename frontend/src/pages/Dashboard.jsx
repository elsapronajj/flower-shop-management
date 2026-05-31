import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { api, unwrap } from '../api/client'

const metricLabels = {
  flowers: 'Total Flowers',
  bouquets: 'Total Bouquets',
  categories: 'Categories',
  suppliers: 'Total Suppliers',
  customers: 'Total Customers',
  orders: 'Total Orders',
  pending_deliveries: 'Pending Deliveries',
  reviews: 'Total Reviews',
}

const quickActions = [
  { label: 'Add Flower', to: '/flowers/new' },
  { label: 'Create Bouquet', to: '/bouquets/new' },
  { label: 'Create Order', to: '/orders/new' },
  { label: 'Add Customer', to: '/customers/new' },
  { label: 'Add Delivery', to: '/deliveries/new' },
  { label: 'Add Supplier', to: '/suppliers/new' },
]

export default function Dashboard() {
  const [dashboard, setDashboard] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    api
      .get('/dashboard')
      .then((response) => setDashboard(unwrap(response)))
      .finally(() => setLoading(false))
  }, [])

  const totals = dashboard?.totals || {}
  const lowStockFlowers = dashboard?.low_stock_flowers || []
  const recentOrders = dashboard?.recent_orders || []

  return (
    <section className="page">
      <div className="page-heading">
        <div>
          <h1>Dashboard</h1>
          <p>Overview of your flower shop activity.</p>
        </div>
      </div>

      {loading ? (
        <p className="loading-text">Loading dashboard data...</p>
      ) : (
        <>
          <div className="metrics">
            {Object.entries(totals).map(([key, value]) => (
              <article key={key} className="metric">
                <span>{metricLabels[key] || key}</span>
                <strong>{value ?? 0}</strong>
              </article>
            ))}
          </div>

          <div className="quick-actions">
            <h2>Quick Actions</h2>
            <div className="quick-actions-grid">
              {quickActions.map(({ label, to }) => (
                <Link key={to} to={to} className="button secondary">
                  {label}
                </Link>
              ))}
            </div>
          </div>

          <div className="two-column">
            <section>
              <div className="section-title">
                <h2>Recent Orders</h2>
                <Link to="/orders">View all</Link>
              </div>
              {recentOrders.length === 0 ? (
                <p className="empty-text">No recent orders.</p>
              ) : (
                <div className="table-wrap">
                  <table>
                    <thead>
                      <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      {recentOrders.map((order) => (
                        <tr key={order.id}>
                          <td>
                            <Link to={`/orders/${order.id}`} className="table-link">
                              #{order.id}
                            </Link>
                          </td>
                          <td>{order.customer?.full_name || '-'}</td>
                          <td>
                            <span className={`status ${order.status}`}>{order.status}</span>
                          </td>
                          <td>${Number(order.total_amount ?? 0).toFixed(2)}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </section>

            <section>
              <div className="section-title">
                <h2>Low Stock Flowers</h2>
                <Link to="/flowers">Manage</Link>
              </div>
              {lowStockFlowers.length === 0 ? (
                <p className="empty-text">No low stock flowers.</p>
              ) : (
                <div className="table-wrap">
                  <table>
                    <thead>
                      <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Stock</th>
                      </tr>
                    </thead>
                    <tbody>
                      {lowStockFlowers.map((flower) => (
                        <tr key={flower.id}>
                          <td>{flower.name}</td>
                          <td>{flower.category?.name || '-'}</td>
                          <td>
                            <span className="badge-warning">{flower.stock_quantity}</span>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </section>
          </div>
        </>
      )}
    </section>
  )
}
