# API Documentation

## Overview
This API manages construction stages with the following endpoints:

## Endpoints

### GET /constructionStages
Retrieves all construction stages.

**Response Format:**
```json
[
    {
        "id": 1,
        "name": "Stage Name",
        "startDate": "2022-12-31T14:59:00Z",
        "endDate": "2023-01-31T14:59:00Z",
        "duration": 31,
        "durationUnit": "DAYS",
        "color": "#FF0000",
        "externalId": "EXT123",
        "status": "NEW"
    }
]
```

### GET /constructionStages/{id}
Retrieves a specific construction stage by ID.

**Parameters:**
- `id` (integer): The ID of the construction stage

**Response Format:**
```json
{
    "id": 1,
    "name": "Stage Name",
    "startDate": "2022-12-31T14:59:00Z",
    "endDate": "2023-01-31T14:59:00Z",
    "duration": 31,
    "durationUnit": "DAYS",
    "color": "#FF0000",
    "externalId": "EXT123",
    "status": "NEW"
}
```

### POST /constructionStages
Creates a new construction stage.

**Request Format:**
```json
{
    "name": "Stage Name",
    "startDate": "2022-12-31T14:59:00Z",
    "endDate": "2023-01-31T14:59:00Z",
    "durationUnit": "DAYS",
    "color": "#FF0000",
    "externalId": "EXT123",
    "status": "NEW"
}
```

**Validation Rules:**
- `name`: Required, max 255 characters
- `startDate`: Required, ISO8601 format
- `endDate`: Optional, must be after startDate if provided
- `durationUnit`: Optional, one of: HOURS, DAYS, WEEKS (default: DAYS)
- `color`: Optional, valid HEX color
- `externalId`: Optional, max 255 characters
- `status`: Optional, one of: NEW, PLANNED, DELETED (default: NEW)

### PATCH /constructionStages/{id}
Updates an existing construction stage.

**Parameters:**
- `id` (integer): The ID of the construction stage to update

**Request Format:**
```json
{
    "name": "Updated Name",
    "startDate": "2022-12-31T14:59:00Z",
    "endDate": "2023-01-31T14:59:00Z",
    "durationUnit": "DAYS",
    "color": "#FF0000",
    "externalId": "EXT123",
    "status": "PLANNED"
}
```

**Validation Rules:**
Same as POST endpoint. All fields are optional.

### DELETE /constructionStages/{id}
Soft deletes a construction stage by setting its status to 'DELETED'.

**Parameters:**
- `id` (integer): The ID of the construction stage to delete

**Response Format:**
```json
{
    "success": {
        "code": 204,
        "message": "Record deleted successfully"
    }
}
```

## Error Responses

### 400 Bad Request
```json
{
    "error": {
        "code": 400,
        "message": "Invalid request format"
    }
}
```

### 404 Not Found
```json
{
    "error": {
        "code": 404,
        "message": "Construction stage not found"
    }
}
```

### 422 Unprocessable Entity
```json
{
    "error": {
        "code": 422,
        "message": "Validation failed",
        "details": [
            "Name is required",
            "Start date must be in ISO8601 format"
        ]
    }
}
```

### 500 Internal Server Error
```json
{
    "error": {
        "code": 500,
        "message": "Internal server error"
    }
}
```

## Notes
- All dates must be in ISO8601 format (e.g., 2022-12-31T14:59:00Z)
- Duration is automatically calculated based on startDate, endDate, and durationUnit
- A week has 7 days and one day has 24 hours
- Duration is calculated in whole hours, ignoring minutes and seconds