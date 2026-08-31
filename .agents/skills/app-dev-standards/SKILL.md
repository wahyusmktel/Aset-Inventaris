---
name: app-dev-standards
description: >-
  Standard operating procedures, security guidelines, and UI component standards for developing features
  in the Laravel + Inertia + Vue 3 SPA application. Always follow these rules for CRUD, modals, alerts,
  toasts, pagination, loading states, and cybersecurity.
---

# Application Development Standards & Guidelines

This skill enforces 8 core engineering and design principles across the application.

## 1. CRUD Notifications (Toast System)
- Every Create, Read/Fetch Error, Update, and Delete action MUST trigger a Toast notification.
- Toasts must have clear visual states:
  - `success` (green/emerald container with checkmark icon)
  - `error` (red/error container with alert icon)
  - `warning` (amber container with warning icon)
  - `info` (blue/purple container with info icon)
- Toasts must auto-dismiss (default 3.5s - 5s) and allow manual close.

## 2. Interactive Alerts & Confirmations (SweetAlert Style)
- Destructive actions (e.g. Delete, Invalidate, Bulk Operations) MUST prompt the user with a confirmation alert dialog.
- The alert modal must feature:
  - Expressive iconography (warning triangle, trash, question mark)
  - Clear Title & Subtitle explaining the consequence
  - Confirm Action button (with auto-disable and loading state) and Cancel button.

## 3. Data Pagination (10 Items Per Page)
- Every data table MUST implement pagination with exactly **10 items per page** by default.
- Navigation controls must show:
  - Previous / Next buttons
  - Page number buttons
  - Current item range indicator (e.g. "Menampilkan 1 - 10 dari 45 data").

## 4. Interactive Modals (Draggable, Maximizable, Persistent)
- All modal dialogs must support:
  - **Draggable**: Header can be grabbed and dragged smoothly across the viewport.
  - **Maximizable**: Toggle button to expand modal to full screen or restore to default size.
  - **Persistent Backdrop**: Modal MUST NOT close when clicking the outside backdrop. It can ONLY be closed using the dedicated 'X' close button or a 'Tutup / Batal' button.

## 5. Action Buttons (Auto-Disable & Loading State)
- Every button that triggers an asynchronous action, form submission, or state change MUST:
  - Automatically become `disabled` immediately upon click to prevent double-submits.
  - Display a clean loading spinner animation.
  - Re-enable gracefully upon success or failure.

## 6. Industry-Grade Cybersecurity Standards
- **SQL Injection**: Always use Eloquent ORM or parameterized bindings; never concatenate raw SQL.
- **XSS Prevention**: Sanitize user inputs; rely on Vue and Blade automatic HTML escaping.
- **CSRF & JWT**: Verify CSRF tokens for web routes and JWT Bearer/Cookie tokens for authenticated requests.
- **IDOR Prevention**: Always authorize access to records using Laravel Policies / Gates against `auth()->id()`.
- **Mass Assignment**: Use strict `$fillable` arrays on all Eloquent models.
- **Rate Limiting**: Apply throttling on sensitive endpoints (e.g. login, reset password, export).
- **UUID**: Use UUIDs for all database primary and foreign keys.

## 7. Robust Error Handling (Try-Catch & Sanitization)
- Wrap database transactions, external API calls, and business logic in `try-catch` blocks.
- Never expose internal database error messages or stack traces to the client in production responses.
- Log detailed errors using `Log::error()` while returning sanitized, friendly Indonesian messages to users.

## 8. Data Leakage & Theft Prevention
- Exclude sensitive attributes (e.g. `password`, `remember_token`, internal secrets) using `$hidden` in Eloquent models.
- Use Laravel API Resources to strictly control returned JSON data structures.
