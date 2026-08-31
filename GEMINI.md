# Application Development Rules & Guidelines

These rules are mandatory and MUST ALWAYS BE FOLLOWED during the development of this Laravel + Vue 3 SPA application:

1. **Toast Notifications for CRUD**:
   - Every Create, Read/Fetch Error, Update, and Delete operation MUST trigger a toast notification (Success, Error, Warning, Info).
2. **Attractive Alerts (SweetAlert Style)**:
   - Use interactive, beautifully styled confirmation dialogs with clear titles, consequences, and confirm/cancel action buttons for destructive operations.
3. **Pagination on All Tables**:
   - Every table MUST implement pagination with exactly **10 items per page** (`limit = 10`), pagination buttons, and range counters.
4. **Interactive Modals**:
   - MUST be **Draggable** (can be moved across the screen by header).
   - MUST be **Maximizable** (expand to fullscreen / restore).
   - MUST have a **Persistent Backdrop** (CANNOT be closed by clicking outside; ONLY closable via dedicated 'X' or 'Tutup' button).
5. **Action Buttons Loading State**:
   - Every submit or action button MUST automatically disable on click and display a loading spinner animation to prevent double submissions.
6. **Industry-Grade Cybersecurity**:
   - Always use UUIDs for all database IDs (`HasUuids`).
   - Prevent SQL Injection using Eloquent and parameterized queries.
   - Prevent XSS, CSRF, and IDOR vulnerabilities with strict policy authorization.
   - Secure JWT token management.
7. **Robust Error Handling**:
   - Wrap business logic in `try-catch` blocks.
   - Sanitize all error messages returned to clients; never leak database stack traces.
8. **Data Leakage & Anti-Theft Protection**:
   - Protect sensitive fields using `$hidden` in Eloquent models and transform responses with API Resources.
