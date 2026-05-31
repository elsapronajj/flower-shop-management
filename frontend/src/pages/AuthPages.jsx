import { useState } from 'react'
import { Link, Navigate, useNavigate } from 'react-router-dom'
import { useAuth } from '../auth/AuthContext'

function ErrorBlock({ error }) {
  if (!error) return null

  const errors = error.response?.data?.errors

  return (
    <div className="error-box">
      <strong>{error.response?.data?.message || error.message}</strong>
      {errors &&
        Object.entries(errors).map(([field, messages]) => (
          <span key={field}>{messages.join(' ')}</span>
        ))}
    </div>
  )
}

export function LoginPage() {
  const { isAuthenticated, login } = useAuth()
  const navigate = useNavigate()
  const [form, setForm] = useState({ email: 'admin@flowershop.test', password: 'password' })
  const [error, setError] = useState(null)
  const [saving, setSaving] = useState(false)

  if (isAuthenticated) return <Navigate to="/dashboard" replace />

  async function handleSubmit(event) {
    event.preventDefault()
    setSaving(true)
    setError(null)

    try {
      await login(form)
      navigate('/dashboard')
    } catch (caught) {
      setError(caught)
    } finally {
      setSaving(false)
    }
  }

  return (
    <main className="auth-page">
      <section className="auth-panel">
        <h1>Welcome back</h1>
        <p>Login to manage your flower shop</p>
        <ErrorBlock error={error} />
        <form onSubmit={handleSubmit} className="form-grid">
          <label>
            Email
            <input
              type="email"
              value={form.email}
              onChange={(event) => setForm({ ...form, email: event.target.value })}
              placeholder="you@example.com"
            />
          </label>
          <label>
            Password
            <input
              type="password"
              value={form.password}
              onChange={(event) => setForm({ ...form, password: event.target.value })}
              placeholder="Your password"
            />
          </label>
          <button disabled={saving}>{saving ? 'Signing in...' : 'Login'}</button>
        </form>
        <p className="auth-link">
          No account yet? <Link to="/register">Create one</Link>
        </p>
        <p className="auth-back">
          <Link to="/">Back to Homepage</Link>
        </p>
      </section>
    </main>
  )
}

export function RegisterPage() {
  const { isAuthenticated, register } = useAuth()
  const navigate = useNavigate()
  const [form, setForm] = useState({ name: '', email: '', password: '', password_confirmation: '' })
  const [error, setError] = useState(null)
  const [saving, setSaving] = useState(false)

  if (isAuthenticated) return <Navigate to="/dashboard" replace />

  async function handleSubmit(event) {
    event.preventDefault()
    setSaving(true)
    setError(null)

    try {
      await register(form)
      navigate('/dashboard')
    } catch (caught) {
      setError(caught)
    } finally {
      setSaving(false)
    }
  }

  return (
    <main className="auth-page">
      <section className="auth-panel">
        <h1>Create your account</h1>
        <p>Start managing your flower shop</p>
        <ErrorBlock error={error} />
        <form onSubmit={handleSubmit} className="form-grid">
          <label>
            Name
            <input
              value={form.name}
              onChange={(event) => setForm({ ...form, name: event.target.value })}
              placeholder="Your full name"
            />
          </label>
          <label>
            Email
            <input
              type="email"
              value={form.email}
              onChange={(event) => setForm({ ...form, email: event.target.value })}
              placeholder="you@example.com"
            />
          </label>
          <label>
            Password
            <input
              type="password"
              value={form.password}
              onChange={(event) => setForm({ ...form, password: event.target.value })}
              placeholder="Choose a password"
            />
          </label>
          <label>
            Confirm Password
            <input
              type="password"
              value={form.password_confirmation}
              onChange={(event) => setForm({ ...form, password_confirmation: event.target.value })}
              placeholder="Repeat your password"
            />
          </label>
          <button disabled={saving}>{saving ? 'Creating...' : 'Register'}</button>
        </form>
        <p className="auth-link">
          Already registered? <Link to="/login">Login</Link>
        </p>
        <p className="auth-back">
          <Link to="/">Back to Homepage</Link>
        </p>
      </section>
    </main>
  )
}
