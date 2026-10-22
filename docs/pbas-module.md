# PBAS (Performance Based Appraisal System)

Migration of the standalone PHP/mysqli app (`pbas`, SSASIT Surat) into the ERP as a Laravel + Blade module.
Menu: **PBAS** (level 1) > group (level 2) > page (level 3), the same grouping as the original sidebar.

## What was migrated

| Original | ERP |
|---|---|
| `index.php`, `chpw.php`, `logout.php`, `year.php` | not migrated: ERP login / change password / logout / academic-year choice replace them |
| `update_profile.php`, `dashboard.php` (Part A) | `pbas.profile.index` (PBAS > Dashboard) |
| 15 entry pages (`teaching_*`, `da_*`, `ia_*`, `cs*`) + their `delete_*.php` | one controller/view driven by `app/Services/Pbas/PbasEntries.php` (routes `pbas.<slug>.index/store/destroy`) |
| `ia_exam.php`, `da_ind_training.php` (commented out of the original sidebar) | kept as routes without a menu row, same as the original |
| `pbas_facl.php` (API Form / Annexure-B) | `pbas.report.index`, view `pbas/report.blade.php` generated from the original markup, scoring in `PbasScore` |
| `hod_comp.php`, `comment.php` | `pbas.hod.*` |
| `principal_acr.php`, `acr.php` | `pbas.principal.*` |
| `facl_view.php` (empty file in the original) | `pbas.report.show` - HOD/Principal open a member's API Form |
| `student_feedback.php` (linked but never existed) | not migrated |

Tables (`database/migrations/2026_10_22_100000_create_pbas_tables.php`): 17 entry tables `pbas_*` plus `pbas_profiles`
(`personal`), `pbas_locks` (`flag`), `pbas_acr`, `pbas_comments`. Every row carries `sub_institute_id` + `user_id`
(tbluser.id) instead of the free-text `faculty_id`; column names are otherwise the original ones.

## Reuse of ERP data (instead of copies)

| Original dropdown | Source now |
|---|---|
| Year (hard-coded `2025-2026`; HOD comment year `2020..2023`) | `academic_year` via the session list (`academicYears`), session `syear` preselected - `PbasLookups::years()` |
| Department (12 fixed values) | `hrms_departments` of the institute - `PbasLookups::departments()` |
| Principal's six department buttons | all `hrms_departments` of the institute |
| Name / email / mobile / address / joining date / designation prefill | `tbluser`, `hrms_job_titles` |
| Faculty lists for HOD / Principal | `tbluser` (active staff, not students/parents) by department |

Everything else (semester 1-8, mode of teaching, paper type, level, post, ... ) is PBAS-specific; no equivalent exists in the ERP, so the original option lists are kept as they were.

## Roles and menu

Roles come from the session profile name (`PbasAccess`): any staff profile = faculty, `HOD`, `Principal`, `Admin`/`Super Admin`
(pass everything). Students/parents are refused. Route authorisation is `pbas.role:*` + the usual `check_permissions`
(view/add/delete of the menu row). A HOD works on faculty of their own department only; Principal and Admin on everyone.
Locked members (`pbas_locks.flag = 1`) cannot add or delete records (the original only disabled the fieldset in HTML).

`PbasMenuSeeder` creates the menu rows and grants rights to existing profiles (`HOD`, `Principal`, `Lecturer`, `Assistant Professor`,
`Associate Professor`, `Adhoc Assistant Professor`, `Adhoc Lecturer`, `Visiting Lecturer`; `Admin` gets all). No profile is created.

## Going live (nothing was run against the configured database)

```
php artisan migrate --path=database/migrations/2026_10_22_100000_create_pbas_tables.php
PBAS_SEED_INSTITUTES=<institute ids>   # optional, default: every institute in school_setup
php artisan db:seed --class=PbasMenuSeeder
```

## Deliberate differences from the original

- **Look**: the pages use the ERP design (page title bar, cards, form-group fields, striped tables, ERP buttons and alerts) inside the ERP header and sidebar; no PBAS-specific stylesheet. The original Maruti CSS/JS/images were not copied and the institute footer line is replaced by the ERP footer. The API Form (printable Annexure-B) keeps the original markup.
- Fixed in passing (all were plain bugs in the original):
  - profile update wrote *promotion date* into *pay band*;
  - the profile form saved even after showing "All Fields are required";
  - anyone could delete anyone else's rows by id (`delete_*.php`); deletes are now limited to the owner;
  - the ACR entry form inserted into the *comment* table with a missing field, so it always failed; it now saves/updates `pbas_acr` for the session year;
  - HOD "View Data" opened an empty page (`facl_view.php` was 0 bytes); it now opens the member's API Form;
  - the API Form showed "From June 2025 TO May 2026" for every year; it now follows the session year.
- Kept as they were, even though they look wrong (change `PbasScore` / `PbasEntries` if the institute wants them fixed):
  - research-work marks use the fixed year **2023** (`(2023 - enrolment year) * 3`);
  - research-project marks compare the stored option value ("1"/"2") with 100000, so both options score 5;
  - the project-guidance row mark repeats the previous row's mark when a row earns nothing;
  - the API Form includes all years' records, not just the current one;
  - the exam-duty block of the API Form reads columns the table does not have, so it prints blanks;
  - a value of `0` counts as "empty" in every form (hence "write 00").
- Added: integer columns reject non-numeric text with "Problem in insertion. Try Again!" instead of dying with an SQL error; the API Form
  asks for Part A first when it has not been saved (the original printed an empty page); the ACR accepts a mark of `0`.

## Tests

`php vendor/bin/phpunit tests/Feature/Pbas` (in-memory SQLite only; the base class aborts on any other connection).
`PbasReportOracleTest` runs the **original** `pbas_facl.php` against a fake database and compares its rendered page with the
migrated one for 28 datasets (set `PBAS_ORIGINAL_DIR` if the original is not at `C:/Users/Asus/Downloads/pbas/pbas`; skipped if absent).
