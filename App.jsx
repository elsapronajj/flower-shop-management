import { BrowserRouter, Route, Routes } from 'react-router-dom'
import { AuthProvider } from './auth/AuthContext'
import Layout from './components/Layout'
import ProtectedRoute from './components/ProtectedRoute'
import { LoginPage, RegisterPage } from './pages/AuthPages'
import { CrudDetail, CrudForm, CrudList } from './pages/CrudPages'
import Dashboard from './pages/Dashboard'
import HomePage from './pages/HomePage'
import { OrderDetail, OrderForm, OrdersList } from './pages/OrderPages'
import './App.css'

function crudRoutes(type, hasDetail = false) {
  return (
    <>
      <Route path={type} element={<CrudList type={type} />} />
      <Route path={`${type}/new`} element={<CrudForm type={type} />} />
      <Route path={`${type}/:id/edit`} element={<CrudForm type={type} />} />
      {hasDetail && <Route path={`${type}/:id`} element={<CrudDetail type={type} />} />}
    </>
  )
}

export default function App() {
  return (
    <BrowserRouter>
      <AuthProvider>
        <Routes>
          <Route path="/" element={<HomePage />} />
          <Route path="/login" element={<LoginPage />} />
          <Route path="/register" element={<RegisterPage />} />

          <Route element={<ProtectedRoute />}>
            <Route element={<Layout />}>
              <Route path="dashboard" element={<Dashboard />} />
              {crudRoutes('flowers', true)}
              {crudRoutes('bouquets', true)}
              {crudRoutes('bouquet-flowers')}
              {crudRoutes('categories')}
              {crudRoutes('suppliers')}
              {crudRoutes('customers')}
              <Route path="orders" element={<OrdersList />} />
              <Route path="orders/new" element={<OrderForm />} />
              <Route path="orders/:id" element={<OrderDetail />} />
              <Route path="orders/:id/edit" element={<OrderForm />} />
              {crudRoutes('deliveries')}
              {crudRoutes('occasions')}
              {crudRoutes('supply-orders')}
              {crudRoutes('reviews')}
              {crudRoutes('users')}
              {crudRoutes('roles')}
            </Route>
          </Route>
        </Routes>
      </AuthProvider>
    </BrowserRouter>
  )
}
