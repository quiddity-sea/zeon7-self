# Testing Instructions

## Prerequisites
- PHP 8.0+ installed and in your PATH.
- MySQL database running and configured in `.env`.

## Running the Test Server
Open a terminal in the project root (`e:\Dev\Projects\zeon7\self`) and run:

```bash
php -S localhost:8000 -t public
```

Keep this terminal open.

## Running the Tests
Open a **new** terminal in the project root and run:

```bash

```

## Expected Output
You should see a series of checks passing:
- Initial Auth Check: PASS
- Login: PASS
- Authenticated Check: PASS
- CSRF Token received: PASS
- Create Post with CSRF: PASS
- Delete Post: PASS

If any test fails, check the server terminal for error logs.
