# Library Management System

A complete web-based Library Management System built with pure PHP 8 and MySQL.  
100% pure PHP — no JavaScript used at all.

## Features

- User registration and secure login (passwords hashed with PHP password_hash)
- Two user roles:
  - Student
  - Librarian (Admin)
- Book catalog with search by title, author, ISBN,ISBN and category
- Students can:
  - View all books and book details
  - See their currently borrowed books
  - View borrowing history and fines
- Admin (Librarian) can:
  - Full CRUD on books (Add / Edit / Delete / View)
  - Manage users (view & delete)
  - Borrow and return books on behalf of students
  - View and manage fines (automatic calculation for overdue books)
- Automatic fine calculation (1 SAR per day after due date)
- Responsive design using Bootstrap 5

## Default Login Credentials

| Role       | Email                     | Password |
|------------|---------------------------|----------|
| Admin      | admin@library.com         | 123456   |
| Student    | student@library.com       | 123456   |

## Technologies Used

- PHP 8+
- MySQL
- Bootstrap 5
- HTML5 & CSS3
- No JavaScript (pure server-side rendering)

## Database Structure (5 tables)

- `users` – members and admin
- `categories` – book categories
- `books` – book details + available copies
- `borrows` – borrowing transactions
- `fines` – overdue fines (auto-calculated)
