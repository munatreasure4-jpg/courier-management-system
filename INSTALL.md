# Courier Management System - Setup Guide

## Quick Start with Docker

### Prerequisites
- Docker
- Docker Compose

### Installation Steps

1. **Clone the repository**
```bash
git clone https://github.com/munatreasure4-jpg/courier-management-system.git
cd courier-management-system
```

2. **Copy environment file**
```bash
cp .env.example .env
```

3. **Start Docker containers**
```bash
docker-compose up -d
```

4. **Install PHP dependencies**
```bash
docker-compose exec app composer install
```

5. **Generate application key**
```bash
docker-compose exec app php artisan key:generate
```

6. **Run database migrations**
```bash
docker-compose exec app php artisan migrate
```

7. **Seed database with sample data**
```bash
docker-compose exec app php artisan db:seed
```

8. **Access the application**
- API: http://localhost:8000/api
- Database: localhost:3306
  - Username: courier_user
  - Password: courier_password
  - Database: courier_system

## Manual Setup (Without Docker)

### Prerequisites
- PHP 8.1+
- MySQL 8.0+
- Composer
- Node.js & NPM

### Installation Steps

1. **Clone the repository**
```bash
git clone https://github.com/munatreasure4-jpg/courier-management-system.git
cd courier-management-system
```

2. **Install PHP dependencies**
```bash
composer install
```

3. **Copy environment file**
```bash
cp .env.example .env
```

4. **Update .env with your database credentials**
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=courier_system
DB_USERNAME=root
DB_PASSWORD=your_password
```

5. **Generate application key**
```bash
php artisan key:generate
```

6. **Run database migrations**
```bash
php artisan migrate
```

7. **Seed database with sample data**
```bash
php artisan db:seed
```

8. **Start development server**
```bash
php artisan serve
```

Access the application at http://localhost:8000

## Testing

### Run all tests
```bash
docker-compose exec app php artisan test
# or without Docker
php artisan test
```

### Run specific test file
```bash
docker-compose exec app php artisan test tests/Feature/ShipmentTest.php
```

### Run with coverage
```bash
docker-compose exec app php artisan test --coverage
```

## API Documentation

### Authentication Endpoints

#### Register
```
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "customer"
}
```

#### Login
```
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

#### Get Current User
```
GET /api/me
Authorization: Bearer {token}
```

### Shipment Endpoints

#### List Shipments
```
GET /api/shipments?page=1&per_page=15&status=pending
Authorization: Bearer {token}
```

#### Create Shipment
```
POST /api/shipments
Authorization: Bearer {token}
Content-Type: application/json

{
  "receiver_name": "Jane Doe",
  "receiver_email": "jane@example.com",
  "receiver_phone": "+0987654321",
  "origin_address": "123 Main St",
  "origin_city": "New York",
  "origin_state": "NY",
  "origin_postal_code": "10001",
  "destination_address": "456 Oak Ave",
  "destination_city": "Los Angeles",
  "destination_state": "CA",
  "destination_postal_code": "90001",
  "weight": 5.5,
  "contents_description": "Electronics package",
  "shipping_cost": 25.00
}
```

#### Track Shipment
```
GET /api/shipments/track/TRK1234567890
```

### Delivery Endpoints

#### Assign Delivery
```
POST /api/deliveries
Authorization: Bearer {token}
Content-Type: application/json

{
  "shipment_id": 1,
  "delivery_personnel_id": 2,
  "estimated_delivery_time": "2024-01-20 14:00:00"
}
```

#### Update Delivery Status
```
PUT /api/deliveries/{id}/status
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "delivered",
  "latitude": 40.7128,
  "longitude": -74.0060,
  "recipient_name": "Jane Doe"
}
```

#### Track Location
```
POST /api/deliveries/{id}/track-location
Authorization: Bearer {token}
Content-Type: application/json

{
  "latitude": 40.7128,
  "longitude": -74.0060,
  "accuracy": 5.0,
  "speed": 10.5
}
```

### Payment Endpoints

#### Get Payments
```
GET /api/payments?page=1&status=completed
Authorization: Bearer {token}
```

#### Process Payment
```
POST /api/payments
Authorization: Bearer {token}
Content-Type: application/json

{
  "shipment_id": 1,
  "amount": 25.00,
  "payment_method": "credit_card"
}
```

#### Refund Payment
```
POST /api/payments/{id}/refund
Authorization: Bearer {token}
```

### Dashboard Endpoints

#### Get Statistics
```
GET /api/dashboard/statistics?from_date=2024-01-01&to_date=2024-01-31
Authorization: Bearer {token}
```

#### Get Charts Data
```
GET /api/dashboard/charts?days=30
Authorization: Bearer {token}
```

## Sample Login Credentials

After seeding the database, use these credentials:

**Admin**
- Email: admin@courier.test
- Password: password

**Delivery Personnel**
- Email: delivery1@courier.test
- Password: password

**Customer**
- Email: customer1@courier.test
- Password: password

## Useful Commands

### Docker Commands

```bash
# View logs
docker-compose logs -f app

# Stop containers
docker-compose down

# Rebuild containers
docker-compose build --no-cache

# Access app shell
docker-compose exec app bash
```

### Laravel Commands

```bash
# Create new migration
php artisan make:migration create_table_name

# Create new model
php artisan make:model ModelName

# Create new controller
php artisan make:controller Api/ControllerName

# Clear cache
php artisan cache:clear

# Optimize application
php artisan optimize
```

## Troubleshooting

### Database Connection Error
```bash
# Check if database is running
docker-compose ps

# Restart database
docker-compose restart db

# Run migrations again
docker-compose exec app php artisan migrate
```

### Permission Denied Error
```bash
# Fix permissions
chmod -R 775 storage bootstrap/cache
```

### Port Already in Use
```bash
# Change port in docker-compose.yml
# From:
# ports:
#   - "8000:80"
# To:
# ports:
#   - "8001:80"
```

## Contributing

See CONTRIBUTING.md for guidelines.

## License

MIT License - see LICENSE file for details

## Support

For issues and questions, please open an issue on GitHub.
