# Flower Shop Management System

## Project Description

Flower Shop Management System is a full-stack student project for managing flower shop operations. It is built with a Laravel REST API backend and a ReactJS frontend.

The system supports management of flowers, bouquets, bouquet compositions, categories, suppliers, customers, orders, order details, deliveries, occasions, supply orders, reviews, users, roles, and refresh tokens.

Existing extra entities such as Categories are intentionally kept because they are useful for organizing flowers and were already part of the working project.

## Main Features

- User registration and login
- JWT Access Token authentication
- Refresh Token stored in the database
- Refresh Token sent through an HttpOnly cookie
- Access Token stored only in React memory/state
- Protected backend API routes
- Protected frontend routes
- CRUD modules for official flower shop entities
- CRUD for system users and roles
- Role assignment through `user_roles`
- Dashboard with totals, low stock flowers, pending deliveries, reviews, and recent orders
- Flower stock validation
- Individual flower order support
- Bouquet order support based on bouquet flower composition
- Automatic order item amount and order total calculation
- Stock reduction when creating active orders
- Stock restoration when orders are cancelled or deleted
- Axios API client with Authorization header interceptor
- Axios refresh-token interceptor
- Environment configuration for backend and frontend
- Consistent JSON API responses

## Technologies Used

### Backend

- Laravel
- PHP
- Eloquent ORM
- MySQL configuration in `.env.example`
- Laravel database configuration can also be adapted for SQL Server/MSSQL if the required PHP driver is installed
- Custom JWT Access Token service
- Database-backed Refresh Tokens
- Laravel migrations
- Form Request validation
- API Resources
- Custom JWT middleware
- Service and repository classes for auth/order logic

### Frontend

- ReactJS
- Vite
- React Router
- Axios
- React Context API for auth state
- Protected route component
- Environment files

## Project Structure

```text
/backend   -> Laravel REST API
/frontend  -> React Vite application
```

Important backend paths:

```text
backend/routes/api.php                  -> API routes
backend/app/Models                      -> Eloquent models
backend/app/Http/Controllers/Api        -> API controllers
backend/app/Http/Requests               -> Form Request validation
backend/app/Http/Resources              -> API response resources
backend/app/Http/Middleware             -> JWT auth middleware
backend/app/Services                    -> Auth, JWT, and order business logic
backend/app/Repositories                -> Query helpers
backend/database/migrations             -> Database schema
backend/database/seeders                -> Demo seed data
```

Important frontend paths:

```text
frontend/src/api/client.js              -> Axios instance and interceptors
frontend/src/auth/AuthContext.jsx       -> In-memory auth state
frontend/src/components/Layout.jsx      -> Dashboard layout/sidebar
frontend/src/components/ProtectedRoute.jsx
frontend/src/pages/CrudPages.jsx        -> Generic CRUD screens
frontend/src/pages/OrderPages.jsx       -> Order create/edit/detail screens
frontend/src/pages/Dashboard.jsx        -> Dashboard
frontend/src/pages/AuthPages.jsx        -> Login/register
```

## Backend Explanation

### Models

The backend includes these main models:

- `User`
- `Role`
- `RefreshToken`
- `Flower`
- `Category`
- `Supplier`
- `Customer`
- `Bouquet`
- `BouquetFlower`
- `Order`
- `OrderItem`
- `Delivery`
- `Occasion`
- `SupplyOrder`
- `Review`

### Relationships

- A flower belongs to one category.
- A flower may belong to one supplier.
- A bouquet has many bouquet flower rows.
- A bouquet flower row belongs to one bouquet.
- A bouquet flower row belongs to one flower.
- A flower can be used in many bouquets.
- A customer has many orders.
- A customer has many reviews.
- An order belongs to one customer.
- An order has many order items.
- An order item can reference either a flower or a bouquet.
- A delivery belongs to one order.
- A delivery belongs to one user as courier.
- A supplier has many supply orders.
- A review belongs to one customer.
- A review belongs to one order.
- A user belongs to many roles.
- A role belongs to many users.

### Authentication

Authentication is implemented with custom services:

```text
backend/app/Services/JwtService.php
backend/app/Services/AuthService.php
backend/app/Http/Middleware/JwtAuthMiddleware.php
```

The backend returns a short-lived JWT Access Token after login or registration. It also creates a long-lived Refresh Token, stores a hash of it in the `refresh_tokens` table, and sends the plain token as an HttpOnly cookie.

### Order and Stock Logic

Order business logic is handled in:

```text
backend/app/Services/OrderService.php
```

Implemented rules:

- An order item must contain either `flower_id` or `bouquet_id`.
- Individual flower order items reduce that flower stock.
- Bouquet order items reduce stock for each flower in the bouquet composition.
- Stock cannot go below zero.
- Subtotal/amount is calculated automatically.
- Order total is calculated automatically.
- Cancelling an order restores stock.
- Deleting a non-cancelled order restores stock.

### Validation

Form Request classes validate incoming API requests, including:

- Required relationship IDs exist.
- `courier_id` exists in the `users` table.
- Review `rating` is between 1 and 5.
- Order item quantity is at least 1.
- Prices and totals cannot be negative.
- Status values must match allowed enums.

## Frontend Explanation

The frontend uses React Router and a protected layout with a collapsible sidebar.

### Public Routes

These routes are accessible without login:

- `/` — Public homepage with hero section, feature cards, and links to login/register
- `/login` — Login page
- `/register` — Register page

### Protected Routes

These routes require authentication and render inside the sidebar layout:

- `/dashboard` — Admin dashboard with metrics, quick actions, recent orders, and low stock flowers
- `/flowers` — Flower inventory management
- `/bouquets` — Bouquet management
- `/bouquet-flowers` — Bouquet composition management
- `/categories` — Category management
- `/suppliers` — Supplier management
- `/customers` — Customer management
- `/orders` — Order management
- `/deliveries` — Delivery management
- `/occasions` — Special occasion management
- `/supply-orders` — Supply order management
- `/reviews` — Customer review management
- `/users` — User management
- `/roles` — Role management

### Pages and Modules

Implemented frontend modules:

- Homepage (public)
- Login
- Register
- Dashboard
- Flowers
- Bouquets
- Bouquet Flowers
- Categories
- Suppliers
- Customers
- Orders
- Deliveries
- Occasions
- Supply Orders
- Reviews
- Users
- Roles

### Layout

The authenticated layout in `frontend/src/components/Layout.jsx` renders:

- A sticky sidebar with navigation links and active-state highlighting
- A topbar with the signed-in user name and logout button
- A hamburger toggle button for collapsing the sidebar on mobile and tablet

### Dashboard

The dashboard at `/dashboard` shows:

- Metric cards for total flowers, bouquets, categories, suppliers, customers, orders, pending deliveries, and reviews
- A quick actions bar with buttons to add flowers, bouquets, orders, customers, deliveries, and suppliers
- A recent orders table with links to individual orders
- A low stock flowers table with a link to manage inventory

All dashboard values have safe fallbacks and will not break if the API returns fewer fields.

### Axios Client

The Axios client is configured in:

```text
frontend/src/api/client.js
```

It:

- Uses `VITE_API_BASE_URL`
- Sends cookies with `withCredentials: true`
- Adds the Access Token to protected requests
- Retries failed `401` requests by calling `/api/refresh-token`
- Keeps the Access Token out of `localStorage` and `sessionStorage`

### Auth State

Auth state is stored in React memory through:

```text
frontend/src/auth/AuthContext.jsx
```

The access token exists only in memory/state while the app is running.

### CRUD Screens

Most CRUD modules use the reusable generic CRUD page in:

```text
frontend/src/pages/CrudPages.jsx
```

Each list page shows an empty state message when there are no records. Each form wraps fields in a white card with shadow. Status columns render colored badges automatically.

The order module uses a custom page because it needs item rows, stock validation, flower/bouquet selection, and total preview.

## Database Tables

### users

Stores system users.

Main fields:

- `id`
- `name`
- `email`
- `password`

### roles

Stores simple system roles.

Main fields:

- `id`
- `name`
- `description`

### user_roles

Pivot table between users and roles.

Main fields:

- `id`
- `user_id`
- `role_id`

### refresh_tokens

Stores hashed refresh tokens.

Main fields:

- `id`
- `user_id`
- `token_hash`
- `expires_at`
- `revoked_at`
- `ip_address`
- `user_agent`

### flowers

Stores individual flower inventory.

Official fields:

- `id`
- `name`
- `type`
- `color`
- `price`
- `stock_quantity`
- `season`
- `lifespan_days`
- `photo`

Additional fields kept from the original project:

- `description`
- `category_id`
- `supplier_id`
- `image_url`
- `is_active`

### bouquets

Stores bouquet products.

Fields:

- `id`
- `name`
- `description`
- `price`
- `size`
- `photo`
- `is_active`

### bouquet_flowers

Stores bouquet composition.

Fields:

- `id`
- `bouquet_id`
- `flower_id`
- `quantity`

### categories

Extra existing table used to organize flowers.

Fields:

- `id`
- `name`
- `description`

### customers

Stores customer information.

Fields:

- `id`
- `first_name`
- `last_name`
- `email`
- `phone`
- `address`
- `registration_date`
- `is_vip`

### orders

Stores customer orders.

Fields:

- `id`
- `customer_id`
- `order_date`
- `delivery_date`
- `delivery_address`
- `card_message`
- `total_amount`
- `status`

Supported statuses:

- `pending`
- `completed`
- `cancelled`

### order_items

This project keeps the existing `order_items` table and uses it as the official OrderDetails concept.

Fields:

- `id`
- `order_id`
- `bouquet_id`
- `flower_id`
- `quantity`
- `unit_price`
- `subtotal`
- `amount`

At least one of `bouquet_id` or `flower_id` must be present.

### deliveries

Stores delivery records.

Fields:

- `id`
- `order_id`
- `courier_id`
- `delivery_date`
- `delivery_time`
- `status`
- `recipient_signature`

Supported statuses:

- `pending`
- `in_transit`
- `delivered`
- `failed`

### occasions

Stores special occasions and discounts.

Fields:

- `id`
- `name`
- `description`
- `event_date`
- `discount_percentage`

### suppliers

Stores supplier information.

Fields:

- `id`
- `name`
- `contact`
- `email`
- `phone`
- `address`
- `specialty`

### supply_orders

Stores supplier purchase orders.

Fields:

- `id`
- `supplier_id`
- `order_date`
- `total_amount`
- `status`

Supported statuses:

- `pending`
- `ordered`
- `received`
- `cancelled`

### reviews

Stores customer reviews.

Fields:

- `id`
- `customer_id`
- `order_id`
- `rating`
- `comment`
- `review_date`

## Authentication Flow

1. User logs in with email and password.
2. Laravel validates the credentials.
3. Laravel returns a JWT Access Token.
4. Laravel creates a Refresh Token.
5. Laravel stores a hash of the Refresh Token in the database.
6. Laravel sends the plain Refresh Token through an HttpOnly cookie.
7. React stores the Access Token only in memory/state.
8. Axios sends the Access Token with protected requests:

```http
Authorization: Bearer <token>
```

9. If a protected request returns `401`, Axios calls `/api/refresh-token`.
10. Laravel validates the Refresh Token from the cookie and database.
11. Laravel returns a new Access Token.
12. React updates its in-memory token and retries the original request.
13. Logout revokes the Refresh Token and clears the cookie.

## API Endpoints

### Auth

```http
POST /api/register
POST /api/login
POST /api/refresh-token
POST /api/logout
GET  /api/me
```

### Dashboard

```http
GET /api/dashboard
```

### CRUD Resources

Protected REST endpoints exist for:

```http
/api/flowers
/api/bouquets
/api/bouquet-flowers
/api/categories
/api/suppliers
/api/customers
/api/orders
/api/deliveries
/api/occasions
/api/supply-orders
/api/reviews
/api/users
/api/roles
```

Each resource supports the standard Laravel API resource actions where applicable:

```http
GET    /api/{resource}
POST   /api/{resource}
GET    /api/{resource}/{id}
PUT    /api/{resource}/{id}
PATCH  /api/{resource}/{id}
DELETE /api/{resource}/{id}
```

## Installation and Setup

### Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Configure the database in `backend/.env`.

Example MySQL configuration:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=flower_shop
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations:

```bash
php artisan migrate
```

Optional demo data:

```bash
php artisan db:seed
```

Run the API:

```bash
php artisan serve --host=127.0.0.1 --port=8011
```

### Frontend

```bash
cd frontend
npm install
```

Development environment:

```env
VITE_API_BASE_URL=http://127.0.0.1:8011/api
```

Run the frontend:

```bash
npm run dev -- --host 127.0.0.1 --port 5174
```

Open in a browser:

```text
http://127.0.0.1:5174/
```

The homepage is public. Click **Login** or navigate to `/login` to sign in.

After login you are redirected to `/dashboard`.

Seeded login:

```text
Email: admin@flowershop.test
Password: password
```

## Environment Variables

Important backend values:

```env
APP_URL=http://127.0.0.1:8011

JWT_SECRET=
JWT_ACCESS_TTL_MINUTES=15
JWT_REFRESH_TTL_DAYS=14
JWT_REFRESH_COOKIE=flower_shop_refresh_token
JWT_REFRESH_COOKIE_SECURE=false
JWT_REFRESH_COOKIE_SAME_SITE=lax

CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173,http://localhost:5174,http://127.0.0.1:5174
```

Important frontend value:

```env
VITE_API_BASE_URL=http://127.0.0.1:8011/api
```

Notes:

- `JWT_SECRET` falls back to `APP_KEY` if it is empty.
- Set `JWT_REFRESH_COOKIE_SECURE=true` in production with HTTPS.
- `CORS_ALLOWED_ORIGINS` must include the frontend origin used in the browser.

## How to Run the Project

Terminal 1:

```bash
cd backend
php artisan serve --host=127.0.0.1 --port=8011
```

Terminal 2:

```bash
cd frontend
npm run dev -- --host 127.0.0.1 --port 5174
```

Then open:

```text
http://127.0.0.1:5174/
```

## What We Implemented

- Created a Laravel REST API.
- Created a React frontend.
- Added JWT Access Token authentication.
- Added database-backed Refresh Tokens.
- Added HttpOnly refresh token cookie handling.
- Added protected API routes.
- Added protected frontend routes.
- Added CRUD for Flowers, Bouquets, Bouquet Flowers, Categories, Suppliers, Customers, Orders, Deliveries, Occasions, Supply Orders, Reviews, Users, and Roles.
- Added role assignment through `user_roles`.
- Added missing official fields to Flowers, Customers, Orders, and Suppliers.
- Extended order items to support the official OrderDetails behavior.
- Added bouquet stock deduction based on bouquet composition.
- Added stock restoration on order cancellation/deletion.
- Added dashboard metrics for official modules.
- Connected frontend and backend with Axios.
- Added Axios interceptors for Bearer tokens and token refresh.
- Added environment configuration.
- Added backend feature tests for individual flower and bouquet stock behavior.

## Current Identity Scope

Implemented identity-related tables:

- `users`
- `roles`
- `user_roles`
- `refresh_tokens`

The project does not implement separate `user_claims` or separate identity `user_tokens` tables. Token refresh is handled by the custom `refresh_tokens` table.

## Testing

Backend tests:

```bash
cd backend
php artisan test
```

Frontend lint:

```bash
cd frontend
npm run lint
```

Frontend build:

```bash
cd frontend
npm run build
```

## Future Improvements

- More detailed role-based permissions
- Image upload for flowers and bouquets
- Order invoice generation
- Search and filtering
- Full pagination controls in the frontend
- More unit and feature tests
- Better UI design and accessibility
- Sales and inventory reporting
