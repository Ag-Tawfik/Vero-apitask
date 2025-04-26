Hello dear API creator!

This repository is a playground for your submission.

Before getting started, please hit the `Use this template button` to create a new repository on which you commit and push your code regularly for your task. Once you are done, please mail us the link to your repository.

If you encounter a problem or have questions about the task, feel free to email us under christian.schaefers@vero.de

Good luck and have fun ☘️

## Prerequisites:
The already built up code frame in this repo is a very basic API with limited functionality. Your task is to pick it up and develop new features on top of it.

You can change existing code structure however you can't add any external frameworks and third party classes.

There is an SQLite database (`testDb.db`) which is created and filled on the fly.

There is a basic routing in `index.php` which supports `GET` and `POST` calls in particular:
- `GET constructionStages`
- `GET constructionStages/{id}`
- `POST constructionStages`

The API serves data and accepts payload only in JSON format.

## Running the Project

### Requirements
- PHP 7.4 or higher
- SQLite extension for PHP
- JSON extension for PHP

### Setup
1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd <repository-name>
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

### Testing the API
You can test the API using curl or any API client like Postman. Here are some example requests:

1. Get all construction stages:
   ```bash
   curl http://localhost:8000/constructionStages
   ```

2. Get a specific construction stage:
   ```bash
   curl http://localhost:8000/constructionStages/1
   ```

3. Create a new construction stage:
   ```bash
   curl -X POST http://localhost:8000/constructionStages \
     -H "Content-Type: application/json" \
     -d '{
       "name": "Test Stage",
       "startDate": "2024-01-01T00:00:00Z",
       "endDate": "2024-01-31T00:00:00Z",
       "durationUnit": "DAYS",
       "color": "#FF0000",
       "externalId": "TEST123",
       "status": "NEW"
     }'
   ```

4. Update a construction stage:
   ```bash
   curl -X PATCH http://localhost:8000/constructionStages/1 \
     -H "Content-Type: application/json" \
     -d '{
       "name": "Updated Stage",
       "status": "PLANNED"
     }'
   ```

5. Delete a construction stage:
   ```bash
   curl -X DELETE http://localhost:8000/constructionStages/1
   ```

### API Documentation
The API documentation is automatically generated in `docs/api.md`. You can view it using any markdown viewer or convert it to HTML.

To regenerate the documentation:
```bash
php generate-docs.php
```

## Task 1:
Add a new API call `PATCH constructionStages/{id}` which to allow the API users to edit an arbitrary field of a selected (by id) construction stage. The API should touch only the fields which are sent by the user. Add validation which to ensure that if `status` field is sent it is either `NEW`, `PLANNED` or `DELETED` and throw a proper error if it is not.

Add another `DELETE constructionStages/{id}` API call which changes the `status` of the selected resource to `DELETED`.

## Task 2:
Write a validation system which checks every posted field against a set of rules as follows:
- `name` is maximum of 255 characters in length
- `start_date` is a valid date&time in iso8601 format i.e. `2022-12-31T14:59:00Z`
- `end_date` is either `null` or a valid datetime which is later than the `start_date`
- `duration` is skipped because it should be automatically calculated based on `start_date`, `end_date` and `durationUnit`
- `durationUnit` is one of `HOURS`, `DAYS`, `WEEKS` or can be skipped (which fallbacks to default value of `DAYS`)
- `color` is either `null` or a valid HEX color i.e. `#FF0000`
- `externalId` is `null` or any string up to 255 characters in length
- `status` is one of `NEW`, `PLANNED` or `DELETED` and the default value is `NEW`.

You should throw proper errors if a rule is not met.

## Task 3:
Set a logic which automatically calculates `duration` based on `start_date`, `end_date` and `durationUnit` as you know that:
- `start_date` is required and is a valid date&time in iso8601 format i.e. `2022-12-31T14:59:00Z`
- `end_date` is either `null` (then `duration` is also `null`) or a valid datetime which is later than the `start_date`
- `durationUnit` is one of `HOURS`, `DAYS`, `WEEKS` where `DAYS` is the default fallback.
- `duration` is a positive float value calculated in precision of whole hours (ignore minutes and seconds if any)
- a week has 7 days and one day has 24 hours

## Default task:
Add a nice phpDoc to every method you create!

## Bonus task:
Add a system which generates a documentation out of your API!

## API Documentation
The API documentation can be generated using the following command:
```bash
php generate-docs.php
```

This will create a markdown file (`docs/api.md`) containing comprehensive documentation of all API endpoints, including:
- Available endpoints and their methods
- Request and response formats
- Validation rules
- Error responses
- Usage notes

The documentation is automatically generated from the codebase and includes examples of requests and responses.

