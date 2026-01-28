# Radiif Codebase AI Instructions

## Project Overview
**Radiif** is a Laravel 12 web application connecting companies with expert service providers. The platform supports multi-user roles (company employees, experts/suppliers) with distinct dashboards and manages AI-driven task workflows and expert services.

**Key Stack:**
- Backend: PHP 8.2+ with Laravel 12, Eloquent ORM, Sanctum authentication
- Frontend: Vite, Tailwind CSS 4.0, Arabic/English localization (ar/en)
- Database: SQLite (testing), MySQL (production)
- Queue/Jobs: Laravel queue system with sync driver for testing

---

## Architecture & Data Flows

### User & Role System
**Models:** `User`, `Company`, `ExpertService`, `AiTask`, `AiResponse`, `Career`, `Post`

- **User roles:** `expert`, `supplier`, `company` (inferred from context)
- **User-Company relationship:** `User.company_id` → `Company.company_id` (belongsTo)
- **Expert relationships:** `ExpertService.expert_id` → `User.id`, `User.company_id` groups team members
- **Authentication:** Laravel Sanctum + session-based auth; middleware validates roles with `EnsureUserIsExpert`, `EnsureUserIsSupplier`

### Dashboard Structure
Two primary dashboard controllers:
- **`DashboardController`** (company/team view): Statistics (team count, services count), project/settings management
- **`ExpertDashboardController`** (expert workbench): Tasks, services, availability, CV builder, financials (level-based: `ai_responses_v2` row count determines rank: 20→Active, 100→Certified, 500→Elite)

### AI Task & Response Workflows
- **Tables:** `ai_tasks_v2` (task_type, status, assigned_expert_id), `ai_responses_v2` (expert_id, response data, timestamps)
- **Task lifecycle:** Pending tasks surfaced in expert workbench via `pending` status in `ai_tasks_v2`
- **Financials:** Calculated as `total_tasks * $5` per expert (see `ExpertDashboardController.php` line 35)

### Localization Layer
**Middleware:** `SetLocale` (active on all routes)
- **Query param:** `?lang=ar|en` sets `App::setLocale()` and persists to session
- **Resources:** [lang/ar/](lang/ar/), [lang/en/](lang/en/)
- **Routing:** All views support both locales; check translation keys in views

---

## Developer Workflows

### Local Development
```bash
# Full setup (one-time)
composer run setup

# Concurrent development (runs: artisan serve, queue listener, logs, vite dev)
composer run dev

# Run tests
composer run test

# Database commands
php artisan migrate              # Apply migrations
php artisan migrate:refresh      # Reset + seed (testing)
php artisan tinker               # Interactive shell
```

### Database Migrations
- Location: [database/migrations/](database/migrations/) (numbered by timestamp: 2026_01_*)
- Pattern: Use `up()` for schema changes, `down()` for rollback
- Key tables: `users`, `companies`, `expert_services`, `ai_tasks_v2`, `ai_responses_v2`
- Always include `->timestamp()` or explicit datetime casting in migrations

### Testing
- **Config:** [phpunit.xml](phpunit.xml) — SQLite in-memory DB, array cache/mail, sync queue
- **Directories:** [tests/Unit/](tests/Unit/), [tests/Feature/](tests/Feature/)
- **Base class:** `Tests\TestCase` extends `Illuminate\Foundation\Testing\TestCase`
- **Pattern:** Feature tests for routes/controllers, Unit tests for services/models
- Run: `php artisan test` or `./vendor/bin/phpunit`

### Build & Frontend
- **Vite config:** [vite.config.js](vite.config.js) — Tailwind CSS 4.0 integration, Laravel plugin
- **Build:** `npm run build` (production), `npm run dev` (watch mode)
- **Frontend entry:** [resources/js/app.js](resources/js/app.js), [resources/css/app.css](resources/css/app.css)
- **Ignored in Vite watch:** [storage/framework/views/](storage/framework/views/) (blade cache)

---

## Project-Specific Patterns

### Routing & Controllers
- **Route file:** [routes/web.php](routes/web.php) (124 lines)
- **Naming:** Controller methods match views in [resources/views/](resources/views/); explicit route names (e.g., `dashboard.expert.services`)
- **Auth middleware:** `middleware(['auth'])` protects company/expert features; public routes at top
- **Role protection:** Use `EnsureUserIsExpert` middleware on expert-only routes (e.g., `/dashboard/expert/*`)

### Model Conventions
- **Custom primary keys:** `ExpertService` uses `service_id` (not `id`), set via `$primaryKey = 'service_id'`
- **Fillable attributes:** Always define; avoid mass-assign vulnerabilities
- **Timestamps:** Auto-included; datetime casts for `assigned_at`, `completed_at`, `created_at`
- **Relationships:** Use explicit foreign key names in `belongsTo()/hasMany()` if non-standard

### View & Localization
- **Blade structure:** Views in [resources/views/](resources/views/); subfolders mirror dashboard layout
- **Translation syntax:** `__('key.CONSTANT')` fetches [lang/{locale}/key.php](lang/en/)
- **Example:** `__('contact.CONTACT_SUCCESS_MESSAGE')`, `__('auth.PASSWORD_RESET_SENT')`
- **Locale detection:** Session persists `setLocale` from URL param; use `app()->getLocale()` in code

### Error Handling & Status
- **Company/Expert status:** Tables have `is_active` flag; check before operations
- **Exception handling:** Try-catch blocks around DB queries in controllers (see `ExpertDashboardController` lines 18-26)
- **Response pattern:** Redirect with `.with('success'|'error', message)` for flash data

---

## Key Files & Navigation

| File | Purpose |
|------|---------|
| [app/Http/Controllers/DashboardController.php](app/Http/Controllers/DashboardController.php) | Company dashboard, stats, team/settings |
| [app/Http/Controllers/ExpertDashboardController.php](app/Http/Controllers/ExpertDashboardController.php) | Expert workbench, tasks, services, financials |
| [app/Models/User.php](app/Models/User.php) | Core user model with company relationship |
| [app/Models/ExpertService.php](app/Models/ExpertService.php) | Expert service offerings (title, price, delivery_days) |
| [app/Models/AiTask.php](app/Models/AiTask.php) | AI task assignments (status, assigned_expert_id) |
| [routes/web.php](routes/web.php) | Route definitions, auth middleware groups |
| [config/app.php](config/app.php) | Application name, timezone, providers, services |
| [vite.config.js](vite.config.js) | Frontend build config, Tailwind + Laravel plugin |
| [phpunit.xml](phpunit.xml) | Test suite configuration, in-memory SQLite setup |

---

## Integration Points & Dependencies

### External Auth
- **Sanctum:** API token authentication ([config/sanctum.php](config/sanctum.php)) for mobile/external clients
- **Session-based:** Web routes use Laravel session auth; API routes use bearer tokens

### Database & Queueing
- **ORM:** Eloquent; use `Model::find()`, `where()`, relationships for queries
- **Raw SQL:** `DB::table()` used in some dashboards (see `DashboardController` line 38-44) for complex joins
- **Queue:** Jobs table created; use `dispatch(Job::class)` for async work

### Mail & Notifications
- **Service:** [config/mail.php](config/mail.php); testing uses array driver
- **Mailable:** [app/Mail/InviteEmployee.php](app/Mail/InviteEmployee.php) — team invite emails
- **Usage:** `Mail::send()` or `Notification::send()` in controllers

---

## Common Tasks & Quick Reference

**Add a new expert feature:**
1. Define route in [routes/web.php](routes/web.php) with `EnsureUserIsExpert` middleware
2. Create method in `ExpertDashboardController`
3. Create view in [resources/views/dashboard/expert/](resources/views/dashboard/expert/)
4. Add translation keys to [lang/ar/](lang/ar/) and [lang/en/](lang/en/)

**Create a new migration:**
```bash
php artisan make:migration create_table_name
# Edit database/migrations/YYYY_MM_DD_*.php
php artisan migrate
```

**Add a model:**
```bash
php artisan make:model ModelName -m  # With migration
# Define $fillable, relationships, casts in app/Models/ModelName.php
```

**Debug & Test:**
```bash
php artisan tinker  # Interactive DB exploration
php artisan test --filter=TestClassName  # Run specific test
composer run dev  # Watch logs in real-time
```
