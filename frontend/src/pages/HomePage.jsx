import { Link } from 'react-router-dom'

const features = [
  {
    title: 'Flower Inventory',
    desc: 'Manage flowers, colors, seasons, prices, stock quantities, and photos.',
  },
  {
    title: 'Bouquet Management',
    desc: 'Create bouquets and define which flowers belong to each bouquet.',
  },
  {
    title: 'Customer Management',
    desc: 'Store customer details, VIP status, and order history.',
  },
  {
    title: 'Online Orders',
    desc: 'Create and manage orders with flowers, bouquets, totals, statuses, and delivery details.',
  },
  {
    title: 'Deliveries',
    desc: 'Track delivery dates, couriers, delivery status, and recipient signatures.',
  },
  {
    title: 'Suppliers',
    desc: 'Manage suppliers, specialties, contact details, and supply orders.',
  },
  {
    title: 'Reviews',
    desc: 'Track customer ratings and comments for completed orders.',
  },
  {
    title: 'Special Occasions',
    desc: 'Manage events, dates, and discount percentages.',
  },
]

export default function HomePage() {
  return (
    <div className="home-page">
      <header className="home-header">
        <div className="home-header-inner">
          <span className="home-logo">Flower Shop</span>
          <div className="home-header-actions">
            <Link to="/login" className="button secondary">Login</Link>
            <Link to="/register" className="button">Create Account</Link>
          </div>
        </div>
      </header>

      <section className="hero">
        <div className="hero-inner">
          <h1>Flower Shop<br />Management System</h1>
          <p className="hero-subtitle">
            A simple and powerful platform for managing flowers, bouquets, customers, orders,
            deliveries, suppliers, reviews, and special occasions.
          </p>
          <div className="hero-actions">
            <Link to="/login" className="button hero-btn-primary">Login</Link>
            <Link to="/register" className="button hero-btn-secondary">Create Account</Link>
          </div>
          <p className="hero-description">
            Manage your flower shop from one beautiful dashboard. Track inventory, create bouquets,
            handle customer orders, organize deliveries, and monitor shop activity in one place.
          </p>
        </div>
      </section>

      <section className="features-section">
        <div className="features-inner">
          <div className="features-grid">
            {features.map((feature) => (
              <div key={feature.title} className="feature-card">
                <h3>{feature.title}</h3>
                <p>{feature.desc}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="built-for-section">
        <div className="built-for-inner">
          <h2>Built for modern flower shops</h2>
          <p>
            This system helps organize daily flower shop operations by combining inventory, orders,
            customers, deliveries, suppliers, and reviews in one clean application.
          </p>
        </div>
      </section>

      <footer className="home-footer">
        Flower Shop Management System &middot; Laravel API + ReactJS
      </footer>
    </div>
  )
}
