# Books API - Symfony

## Overview

This project is a REST API built with Symfony 7.x. It allows users to search for books by title, ISBN, or author. The API stores book data in a database and asynchronously fetches missing data from Open Library.

## Features

- Search books by title, ISBN, or author.
- Uses an internal database as the primary data source.
- Asynchronously fetches data from Open Library when not found in the database.
- Handles rate limiting by rescheduling requests when necessary.
- Implements API versioning (`/api/v1/`).

## Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- Symfony CLI
- MySQL or PostgreSQL

### Steps

1. Clone the repository:
   ```sh
   git clone https://github.com/EduardoJaraba/books-api.git
   cd books-api
   ```
2. Install dependencies:
   ```sh
   composer install
   ```
3. Configure the `.env` file with database credentials:
   ```env
   DATABASE_URL="mysql://user:password@127.0.0.1:3306/books_db"
   ```
4. Set up the database:
   ```sh
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```
5. Run the Symfony server:
   ```sh
   symfony server:start
   ```

## Usage

### Search for a book

```sh
GET /api/v1/books?title=Harry+Potter
GET /api/v1/books?isbn=9780747532743
GET /api/v1/books?author=J.K.+Rowling
```

### Expected Response

```json
{
  "title": "Harry Potter and the Philosopher's Stone",
  "isbn": "9780747532743",
  "author": "J.K. Rowling"
}
```

If the book is not found, the API will return:

```json
{
  "message": "Fetching book info..."
}
```

while it fetches the data asynchronously.

## Messenger Queue

Symfony Messenger handles background jobs. Run the worker to process messages:

```sh
php bin/console messenger:consume async -vv
```

## Contributing

Pull requests are welcome. Please follow Symfony best practices.

## License

MIT
