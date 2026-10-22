# Nivad Project Structure

This skeleton preserves the existing landing page and frontend assets. New user/admin pages and backend architecture are placeholders and will be implemented phase-by-phase.

## Current frontend
- `index.php` and `partials/` are preserved.
- `assets/` is preserved.

## Backend
- `config/`: configuration placeholders
- `app/Core/`: security, request, response, session and database core
- `app/Controllers/`: request/page controllers
- `app/Services/`: business logic
- `app/Repositories/`: PDO/data access
- `app/Validators/`: server-side validation
- `app/Middleware/`: request middleware
- `routes/`: route definitions
- `pages/`: customer-facing page placeholders
- `admin/`: admin page placeholders

## Rule
Do not modify the existing landing page unless explicitly requested. Implement backend modules in the agreed phase order.
