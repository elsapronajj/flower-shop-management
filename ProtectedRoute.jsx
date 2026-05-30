import { Navigate, Outlet } from 'react-router-dom'
import { useAuth } from '../auth/AuthContext'

export default function ProtectedRoute() {
  const { booting, isAuthenticated } = useAuth()

  if (booting) {
    return <div className="screen-message">Loading flower shop...</div>
  }

  return isAuthenticated ? <Outlet /> : <Navigate to="/login" replace />
}
