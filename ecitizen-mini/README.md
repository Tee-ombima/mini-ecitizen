
# Mini eCitizen

A Laravel web application that models a small public-service portal. Citizens can register, browse public services, submit applications with supporting documents, and follow them from submission through departmental review, payment (where required), and certificate issue.

It is designed as a configurable eCitizen-style system: an administrator can create services, their form fields, required documents, fees, service-level targets, and the department responsible. This makes it suitable for services such as permits, land searches, certificates, or local-government requests without changing application code.

## Highlights

- Citizen registration, login, password reset, and six-digit email OTP verification.
- Service catalogue with category and keyword filtering.
- Dynamic application forms, configurable by an administrator per service.
- Required document uploads (PDF, JPG, JPEG, or PNG; up to 5 MB per file).
- Application reference numbers, urgency selection, status tracking, and a full activity trail.
- Role-based portals for citizens, department officers, and system administrators.
- Department-scoped officer queues with status, urgency, date, and reference filters.
- Approve, reject, or request a revision; citizens can amend and resubmit revision requests.
- Simulated payment for paid services and a printable certificate once approved.
- QR-linked public certificate verification.
- Admin reporting for application status, revenue, turnaround time, service volumes, and SLA breaches.
- Private document storage with authorization checks before a file is streamed to a citizen, responsible officer, or administrator.

## Roles and workflows

| Role | What it can do |
| --- | --- |
| Citizen | Register and verify an account, discover services, submit and amend applications, upload documents, track progress, make a simulated payment, and print/download approved certificates. |
| Department officer | See only applications assigned to the officer's department, review submissions, and approve, reject, or request revisions. |
| Administrator | View operational analytics, manage departments and service definitions, and assign user roles and officer departments. |

### Application lifecycle

```text
Submitted → Under Review → Approved
                     ├→ Revision Requested → Submitted
                     ├→ Rejected
                     └→ Pending Payment → Approved
```

Approval of a free service immediately issues a certificate. Paid services move to **Pending Payment** until the citizen uses the demo payment action; this action is intentionally simulated and is not a real payment integration.

## Technology

- PHP 8.2+
- Laravel 12
- SQLite by default (MySQL can also be configured in `.env`)
- Blade templates, Laravel Mix, and Webpack

## Quick start

### Prerequisites

- PHP 8.2 or newer with SQLite enabled
- Composer
- Node.js and npm

### Installation

From the project directory (`ecitizen-mini`):

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

The repository includes an empty SQLite database at `database/database.sqlite`. If it has been removed, create it before migrating:

```powershell
New-Item database/database.sqlite -ItemType File
```

Build the frontend assets, migrate the database, and load the demo data:

```powershell
npm install
npm run build
php artisan migrate --seed
```

Start the application:

```powershell
php artisan serve
```

Open `http://127.0.0.1:8000` in a browser. During CSS or JavaScript work, run `npm run watch` in a second terminal.

### Resetting demo data

To recreate the database and load fresh seed data, run the following command. It deletes all data in the configured database, so use it only for local development:

```powershell
php artisan migrate:fresh --seed
```

## Demo accounts

All seeded accounts use the password `password`.

| Account | Role | Department |
| --- | --- | --- |
| `admin@ecitizen.test` | Administrator | — |
| `licensing@ecitizen.test` | Officer | Business Licensing |
| `lands@ecitizen.test` | Officer | Lands Registry |
| `dci@ecitizen.test` | Officer | Criminal Investigations |
| `registrar@ecitizen.test` | Officer | Civil Registration |

You can also create a citizen account at `/register`. With the default `MAIL_MAILER=log` configuration, the OTP and password-reset messages are written to `storage/logs/laravel.log`. Configure a real mail provider in `.env` before deploying.

## Seeded services

The demo includes Business Permit, Single Business Permit, Land Search, Good Conduct Certificate, and Certificate Request. Each is connected to a department and has its own SLA, fee, form fields, and document requirements.

Administrators can add or update services in **Services** without editing PHP code. The service builder supports text, textarea, number, date, and select fields, together with optional required-document lists.

## Security and access control

- All private areas require authentication and verified email status.
- Role middleware restricts citizen, officer, and administrator routes.
- Officers may access applications and documents only within their assigned department.
- Citizens may access only their own applications, documents, and certificates.
- Uploaded documents are stored on Laravel's private `local` disk and served only through an authorised controller action.
- Form inputs and uploads are validated server-side.

## Tests and code quality

Run the automated tests with:

```powershell
php artisan test
```

Run Laravel's code formatter with:

```powershell
vendor/bin/pint
```

The bundled `tests/Feature/ExampleTest.php` is a Laravel starter test that expects a `200` response from `/`; this application deliberately redirects `/` to login or the signed-in user's dashboard. Update or remove that starter assertion before treating the entire default suite as a passing acceptance suite.

## Production notes

- Replace `CitizenController::pay()` with a verified M-Pesa/card payment callback before accepting real money.
- Use a production database, set `APP_ENV=production`, `APP_DEBUG=false`, and configure trusted mail credentials.
- Certificates use the browser print dialog and the QR-code CDN library. If server-generated PDF certificates or no external CDN are required, add a PDF renderer and bundle the QR library locally.
- Ensure `storage` and `bootstrap/cache` are writable by the web server, configure a persistent queue worker, and use HTTPS.

## Project structure

```text
app/
  Http/Controllers/       Authentication and role-specific workflows
  Http/Middleware/        Role and email-verification gate
  Models/                 Users, services, applications, files, and audit logs
database/
  migrations/             Schema for departments, services, users, and applications
  seeders/                Demo departments, accounts, and service definitions
resources/views/
  citizen/                Catalogue, application, tracking, and certificate screens
  officer/                Department queue and review screens
  admin/                  Analytics and configuration screens
routes/web.php            Public, citizen, officer, and administrator routes
```

## License

This project is provided for educational and demonstration use. Add an explicit license file if you intend to distribute it.