# Product Requirements Document (PRD)

## Project Overview
- **Project Name:** Template UI - Modern Fullstack SPA
- **Tech Stack:** Laravel 12 + Inertia.js + Vue 3 (Composition API) + Tailwind CSS + MySQL + JWT Authentication + Material Design 3 (M3).
- **Primary Objective:** Deliver an enterprise-grade, high-security, responsive web application template with complete CRUD, interactive modals, sweet alerts, toast notifications, paginated tables, and hardened cybersecurity.

---

## Core Engineering Rules & Architecture Requirements

### 1. Notification & Toast System
- All CRUD actions (Create, Read, Update, Delete) must trigger a notification toast.
- Available variants: `success`, `error`, `warning`, `info`.
- Auto-dismiss duration: 4000ms with manual close option.

### 2. SweetAlert-Style Dialogs
- Destructive and critical confirmation actions must open an attractive M3 confirmation dialog.
- Must display custom action icons, customizable confirmation/cancellation buttons, and loading states upon confirmation.

### 3. Data Tables & Pagination
- All data tables must implement pagination at **10 records per page** (`limit = 10` / `per_page = 10`).
- Display current record range and total counts.
- Responsive table with horizontal scroll on small devices.

### 4. Interactive Modals
- **Draggable:** Users can click and drag the modal header to move the window.
- **Maximizable:** Fullscreen toggle icon to maximize and restore dimensions.
- **Persistent Backdrop:** Backdrop click does NOT close the modal; only the 'X' button or explicit cancel/close buttons can dismiss it.

### 5. Action Buttons & Loading States
- Every submission and action button must disable immediately upon click and show a spinning loading indicator.
- Prevents race conditions and duplicate record submissions.

### 6. Industry-Standard Cybersecurity
- **UUID Keys:** All entity primary keys and foreign keys use UUIDv7 / UUID (`HasUuids`).
- **JWT & Session Security:** Double-layer authentication with JSON Web Tokens and secure session guards.
- **Input Validation & Sanitization:** Strict form requests and model `$fillable` protection.
- **SQLi & XSS Immunity:** Parameterized Eloquent queries and Vue/Blade automatic escaping.
- **Route Authorization:** Comprehensive Policy / Gate validation on all user resources.
- **Rate Limiting:** Throttle limits applied to auth and mutation endpoints.

### 7. Try-Catch & Exception Handling
- All controllers, services, and asynchronous API calls use `try-catch` with clean logging.
- Client responses receive user-friendly Indonesian messages without exposing stack traces.

### 8. Data Privacy & Anti-Theft
- Model `$hidden` protection for sensitive columns.
- API Resource response mapping.

---

## Roadmap & Feature Development Next Steps
1. Master Data CRUD Management (Users, Roles, Permissions).
2. Advanced Reporting & Data Export (PDF/Excel with UUID tracking).
3. Activity Audit Logs (Log user login, IP, changes).
4. Profile & Account Security Management (2FA, Password Reset, Session Invalidation).
