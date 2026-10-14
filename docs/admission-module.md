# Admission module (workflow extension)

Extends the existing admission module (`routes/admission.php`, `app/Http/Controllers/admission/`) with document
verification, configurable eligibility, selection, offers, fee view, an idempotent confirmation, audit history, reports
and a dashboard. Laravel 9 + Blade, session auth, the existing `session` / `menu` / `logRoute` / `check_permissions`
middleware. No new frontend framework, no parallel module: workflow state is keyed by the legacy `admission_enquiry.id`.

## What was added

| Area | Files |
|---|---|
| Schema (additive only) | `database/migrations/2026_10_18_10000{1,2,3}_*.php` |
| Services | `app/Services/Admission/*` (Workflow, Document, Eligibility, Selection, Offer, Fee, Confirmation, Number, Dashboard, Report, Settings, Audit, Access, DuplicateInquiry, ApplicationQuery) |
| Controllers / requests | `app/Http/Controllers/admission/Admission*Controller.php`, `app/Http/Requests/admission/*` |
| Routes | appended to `routes/admission.php` (`/admission/process/...`, names `admission.*`) |
| Views | `resources/views/admission/process/*` |
| Menu + rights | `database/seeders/AdmissionMenuSeeder.php` (not run automatically) |
| Config | `config/admission.php` |
| Tests | `tests/Feature/Admission/*` (SQLite in memory), `tests/views/includes/*` (empty layout stubs for tests) |

## Legacy files changed (small, intentional)

* `admissionRegistrationController::saveStudent` now delegates to `ConfirmationService` (transaction, number lock,
  idempotency, checklist). `max_enrollment_no*` delegate to `NumberService` (the old `_new` variant returned SQL *text*).
* `admissionEnquiryController`: `update` / `destroy` / `edit` are now scoped to the session institute (they previously
  matched on `id` only); `store` calls the optional duplicate-inquiry check; `ajax_getFeesBreakoff` delegates to
  `FeeService::demand` (same rule).
* `admissionFormController`: the enquiry must belong to the institute before a form row is written; form-row updates are
  institute-scoped; saving a form marks the application registered (`WorkflowService::markLegacyRegistered`, guarded so it
  can never break the legacy screen).
* `app/Http/Kernel.php`: `admission.role` middleware alias. `phpunit.xml`: tests forced onto in-memory SQLite.

## Deploy / rollback

Run on a **development or staging copy first**.

```
php artisan migrate --path=database/migrations/2026_10_18_100001_create_admission_core_tables.php
php artisan migrate --path=database/migrations/2026_10_18_100002_create_admission_document_tables.php
php artisan migrate --path=database/migrations/2026_10_18_100003_create_admission_eligibility_and_offer_tables.php
php artisan db:seed --class=AdmissionMenuSeeder        # menus + Admission Officer / Approver / Accounts Operator profiles
```

Migrations must be applied **before** the code is deployed: `saveStudent` now needs the workflow tables.
(The legacy form hook and the duplicate-inquiry check tolerate missing tables; confirmation does not.)

Rollback (reverse order; drops only the new tables, never touches legacy tables or students already created):

```
php artisan migrate:rollback --path=database/migrations/2026_10_18_100003_create_admission_eligibility_and_offer_tables.php
php artisan migrate:rollback --path=database/migrations/2026_10_18_100002_create_admission_document_tables.php
php artisan migrate:rollback --path=database/migrations/2026_10_18_100001_create_admission_core_tables.php
```

Seeder rollback is manual: delete the `tblmenumaster` rows whose `link` starts with `admission.` and their
`tblgroupwise_rights` rows. The seeder is idempotent and never creates a second Admin/Student profile.

Run the tests: `php vendor/bin/phpunit tests/Feature/Admission` (in-memory SQLite; the suite aborts if the connection is
anything else).

## Roles

Resolved from `tbluserprofilemaster.name` (keywords in `config/admission.php`; order admin, approver, accounts, officer):

| Profile contains | Role | Can |
|---|---|---|
| `admin` | admin | everything, incl. eligibility override, confirmation override, fee waiver, settings |
| `approver` / `committee` / `principal` / `director` | approver | view, selection decisions |
| `account` / `fee` | accounts | view, fee screen |
| `admission` | officer | convert, submit, documents, eligibility run, offers, confirm |
| anything else (e.g. Teacher) | none | no access |

Menu rights (`tblgroupwise_rights`) still apply on top for menu-linked screens (`check_permissions`).

## Assumptions (all configurable; nothing is pre-approved)

* No eligibility thresholds, merit formula, fee amounts, required documents or payment policy are built in.
  With no criteria configured every applicant is "Needs review"; a missing/unreadable value is never an auto-rejection.
* Confirmation policy switches default **off** (`Admission Settings`), preserving today's behaviour. Structural checks
  (form, registration record, submitted, programme/profile) always apply and cannot be overridden. An `Ineligible` or
  `Rejected` applicant is always blocked unless an admin overrides, with a reason.
* Fee demand = existing `fees_breackoff` rule (General quota, month 4202). Payment = what is recorded on
  `admission_registration` (amount + mode/date). The new screens cannot mark anything paid.
* A form that already exists on the legacy screen counts as a submitted application; an existing `tblstudent` with a
  matching `admission_id` counts as confirmed.
* "Programme" = `standard`; "academic year" = `syear`; scope boundary = `sub_institute_id`.
* Student becomes active (`status = 1`) at confirmation, as `saveStudent` always did.

## Not done / needs a decision

* **Edits after submission** are not blocked on the *legacy* edit screens (blocking would stop routine edits of every
  existing form). The new workspace is read-only for applicant data and offers an audited "Reopen".
* **Merit ranking**, seat limits / waitlist capacity, reservation rules: no approved rules; waitlist is a status only.
* **Notifications** (email/SMS/in-app) and an **applicant portal**: not built.
* Legacy `mobile*` columns on `admission_enquiry` are `integer` (10-digit numbers overflow on MySQL): not altered here
  (changing a live column is a separate, deliberate migration).
* Enrollment-number serialisation relies on `SELECT ... FOR UPDATE`, which SQLite ignores: the logic is tested, the
  *concurrency* must be exercised on MySQL staging.
* The dashboard "Pending fees" tile uses "no payment recorded on the registration" (cheap, exact); the Fee screen shows
  the fuller due/paid/balance status.
