# Construction Stages API

A modern, lightweight RESTful API for managing construction project stages. Built with PHP 8.4.6, this API leverages the latest PHP features to provide a robust solution for tracking and managing construction project timelines.

## Key Features

- **RESTful Endpoints**: Full CRUD operations for construction stages
- **Smart Duration Calculation**: Automatic calculation of stage durations in hours, days, or weeks
- **Modern PHP Features**:
  - Enums for type-safe status and duration units
  - Constructor property promotion
  - Readonly properties
  - Match expressions
  - Nullsafe operator
  - Union types
  - Named arguments
- **Validation System**: Comprehensive validation for all stage properties
- **Soft Delete**: Safe deletion with status tracking
- **API Documentation**: Auto-generated Swagger/OpenAPI documentation
- **Security**: Built-in security headers and input validation
- **Performance**: Route caching and optimized database queries

## Technical Stack

- PHP 8.4.6
- SQLite Database
- PDO for database operations
- JSON request/response handling
- No external frameworks or dependencies

## Requirements

- PHP 8.4.6 or higher
- SQLite extension for PHP
- JSON extension for PHP

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/construction-stages-api.git
   cd construction-stages-api
   ```

2. Run the setup script to initialize the database and generate documentation:
   ```bash
   php setup.php
   ```

3. Start the PHP development server:
   ```bash
   php -S localhost:8000
   ```

4. The API will be available at `http://localhost:8000`

## API Endpoints

### Get All Construction Stages
```bash
GET http://localhost:8000/constructionStages
```

### Get Specific Construction Stage
```bash
GET http://localhost:8000/constructionStages/{id}
```

### Create New Construction Stage
```bash
POST http://localhost:8000/constructionStages
Content-Type: application/json

{
    "name": "Foundation Work",
    "startDate": "2024-01-01T00:00:00Z",
    "endDate": "2024-01-31T00:00:00Z",
    "durationUnit": "DAYS",
    "color": "#FF0000",
    "externalId": "FOUND-001",
    "status": "NEW"
}
```

### Update Construction Stage
```bash
PATCH http://localhost:8000/constructionStages/{id}
Content-Type: application/json

{
    "name": "Updated Stage Name",
    "status": "PLANNED"
}
```

### Delete Construction Stage
```bash
DELETE http://localhost:8000/constructionStages/{id}
```

## Data Model

Each construction stage includes:

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| name | string | Yes | Stage name (max 255 chars) |
| startDate | string | Yes | ISO 8601 format |
| endDate | string | No | ISO 8601 format |
| duration | float | Auto | Calculated based on dates |
| durationUnit | enum | No | HOURS, DAYS, or WEEKS |
| color | string | No | HEX color code |
| externalId | string | No | External reference ID |
| status | enum | No | NEW, PLANNED, or DELETED |

## Validation Rules

- `name`: Maximum 255 characters
- `startDate`: Valid ISO 8601 format
- `endDate`: Optional, must be after startDate
- `durationUnit`: One of HOURS, DAYS, WEEKS
- `color`: Valid HEX color code
- `externalId`: Maximum 255 characters
- `status`: One of NEW, PLANNED, DELETED

## Documentation

API documentation is available at:
- Swagger UI: `http://localhost:8000/swagger.json`
- Markdown: `docs/api.md`

To regenerate documentation:
```bash
php generate-docs.php
```

## Development

### Code Style
- Follows PSR-12 coding standards
- Uses PHP 8.4.6 features
- Includes PHPDoc comments
- Type declarations for all properties and methods

### Testing
```bash
# Run tests
php vendor/bin/phpunit
```

## Security

- Input validation
- SQL injection prevention
- XSS protection
- CSRF protection
- Security headers

## License

[Your chosen license]

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request