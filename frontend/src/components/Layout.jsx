import { useState } from 'react'
import { NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../auth/AuthContext'

const navItems = [
  ['Dashboard', '/dashboard'],
  ['Flowers', '/flowers'],
  ['Bouquets', '/bouquets'],
  ['Bouquet Flowers', '/bouquet-flowers'],
  ['Categories', '/categories'],
  ['Suppliers', '/suppliers'],
  ['Customers', '/customers'],
  ['Orders', '/orders'],
  ['Deliveries', '/deliveries'],
  ['Occasions', '/occasions'],
  ['Supply Orders', '/supply-orders'],
  ['Reviews', '/reviews'],
  ['Users', '/users'],
  ['Roles', '/roles'],
]

export default function Layout() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const [sidebarOpen, setSidebarOpen] = useState(false)

  async function handleLogout() {
    await logout()
    navigate('/login')
  }

  return (
    <div className="app-shell">
      {sidebarOpen && (
        <div className="sidebar-overlay" onClick={() => setSidebarOpen(false)} />
      )}

      <aside className={`sidebar${sidebarOpen ? ' sidebar-open' : ''}`}>
        <div className="brand">
          <span className="brand-mark">FS</span>
          <div>
            <strong>Flower Shop</strong>
            <small>Management</small>
          </div>
        </div>
        <nav>
          {navItems.map(([label, to]) => (
            <NavLink key={to} to={to} onClick={() => setSidebarOpen(false)}>
              {label}
            </NavLink>
          ))}
        </nav>
      </aside>

      <main className="content">
        <header className="topbar">
          <button
            type="button"
            className="sidebar-toggle"
            onClick={() => setSidebarOpen((open) => !open)}
            aria-label="Toggle navigation"
          >
            &#9776;
          </button>
          <div className="topbar-user">
            <span>Signed in as</span>
            <strong>{user?.name}</strong>
          </div>
          <button type="button" className="secondary" onClick={handleLogout}>
            Logout
          </button>
        </header>
        <Outlet />
      </main>
    </div>
  )
}
