
# PHP OOP Shopping Cart

A simple, interactive shopping cart built with PHP and Object-Oriented Programming (OOP).

## Features

- Add products to the shopping cart
- Increase and decrease product quantities
- Remove individual products
- Clear the entire cart
- Calculate subtotals and the grand total
- Sort products by price
- Preserve the cart using PHP sessions
- Validate product names and prices
- Escape displayed text to reduce HTML injection risks
- Responsive layout for desktop, tablet, and mobile
- CSRF token validation for form submissions

## Technologies

- PHP 7.4 or newer
- HTML5
- CSS3
- PHP Sessions

No database is required for this version.

## Project Structure

```text
ShoppingCart/
├── index.php
├── ShoppingCart.php
└── README.md
```

## Requirements

Install PHP 7.4 or newer.

You can use:
- PHP's built-in development server
- XAMPP with PHP support

## How to Run

### Option 1: PHP built-in server

1. Download or clone this repository.
2. Open a terminal in the project directory.
3. Start the development server:

   ```bash
   php -S localhost:8000
   ```

4. Open this address in your browser:

   http://localhost:8000

### Option 2: XAMPP

1. Install and start Apache in XAMPP.
2. Copy the project folder into the `htdocs` directory.
3. Open:

   http://localhost/ShoppingCart/

## How It Works

The `ShoppingCart` class manages products, quantities, price calculations, sorting, and removal.

The `index.php` file handles form submissions, stores the cart in a PHP session, and renders the user interface.

Product prices are defined server-side. The application does not accept product prices directly from form submissions.

## Current Limitations

- Product data is stored in the application code.
- Cart contents are stored in a PHP session.
- Cart data is not permanently saved in a database.
- There is no user account or checkout/payment integration.
- This project requires a PHP-enabled server and cannot run as a PHP application on GitHub Pages alone.

## Future Improvements

- Add a product catalog with images
- Add persistent storage using MySQL
- Add product search and filtering
- Add automated tests
- Add a checkout workflow

## Author

Nikolas Marantas

GitHub: https://github.com/nikolasmarantas9
