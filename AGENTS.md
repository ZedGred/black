# OpenCode Agent Instructions

## Project Context
This is a Laravel API application with JWT authentication, following best practices for RESTful API development.

## Technology Stack
- **Backend:** Laravel 10+
- **Authentication:** JWT (tymon/jwt-auth)
- **Authorization:** Spatie Permissions
- **Database:** MySQL

## Code Style & Conventions

### Naming Conventions
- **Controllers:** Use singular noun with `Controller` suffix (e.g., `UserController`)
- **Models:** Use singular noun (e.g., `User`, `Article`)
- **Migrations:** Use descriptive names with timestamp (e.g., `2026_03_31_000000_create_users_table`)
- **Routes:** RESTful naming convention

### Controller Guidelines
- Always use Form Requests for validation
- Return consistent JSON response format
- Use Services for business logic
- Use Resources for API responses

### Response Format
```json
{
  "success": true,
  "message": "Success message",
  "data": { }
}
```

### Error Response Format
```json
{
  "success": false,
  "message": "Error message",
  "errors": { }
}
```

### Model Guidelines
- Use UUIDs for primary keys ( HasUuids trait)
- Define relationships using PHP 8 typed properties
- Use scopes for frequently used queries

### Service Guidelines
- Use Services for business logic separation
- Inject dependencies via constructor
- Keep services focused on single responsibility

### Migration Guidelines
- Always use UUID for user-related tables
- Add indexes for frequently queried columns
- Use foreign keys with cascade delete

## Common Tasks

### Creating New Feature
1. Create migration first
2. Create Model with relationships
3. Create Controller with proper methods
4. Create Form Request for validation
5. Create API Resource for response
6. Add routes
7. Add tests

### Adding API Endpoint
1. Add route in `routes/api.php`
2. Implement controller method
3. Return consistent JSON format

### Database Changes
1. Create migration: `php artisan make:migration create_table_name`
2. Define schema with proper foreign keys
3. Run migration: `php artisan migrate`

## Always Do
- Run `php artisan migrate` after creating migrations
- Run `php artisan route:list` to verify routes
- Test endpoints after implementation
- Follow existing code patterns

## Environment
- API runs at: `/api/v1/`
- All routes prefixed with `api/v1/`

## Available Commands
```bash
# Run migrations
php artisan migrate

# Check routes
php artisan route:list

# Clear cache
php artisan cache:clear
php artisan config:clear

# Create model with migration
php artisan make:model ModelName -m

# Create controller
php artisan make:controller ControllerName

# Create resource
php artisan make:resource ResourceName

# Create service
# Just create manually in app/Services/
```
