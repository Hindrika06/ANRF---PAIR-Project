# ANRF–PAIR PROJECT — HUB ADMIN PAGES MODULE FINAL E2E QA AUDIT & REMEDIATION REPORT

**Audit Execution Date**: August 24, 2026  
**Application**: ANRF–PAIR Project PHP/MySQL Multi-Tenant Web Application  
**Target Module**: Hub Admin → Pages (All 7 Modules)  
**Overall QA Verdict**: **100% PASS** (All defects fixed, E2E browser/UI functionality verified, database persistence & baseline clean-up confirmed)

---

## 1. Executive Summary

A comprehensive, real-world end-to-end (E2E) audit was performed on all **7 Hub Admin Pages modules** within the ANRF–PAIR Project application. Rather than relying on static code inspection, every module was dynamically audited across all **8 university context modes** (`cuk`, `kannur`, `mgu`, `ou`, `svu`, `uoh`, `yvu`, `all`). 

All identified architectural bugs, SQL exceptions on missing non-existent aggregate tables, CSRF security vulnerabilities, and context isolation leaks were systematically resolved in the codebase. Real-world browser execution and database persistence were empirically verified, followed by complete cleanup restoring the baseline row counts.

---

## 2. Tested Hub Admin Pages Modules

| # | Module Name | Core Script File | Scope & Capabilities |
|---|---|---|---|
| 1 | **Gallery Albums** | [`admin/gallery_albums_management.php`](file:///c:/Temp/ANRF---PAIR-Project/admin/gallery_albums_management.php) | Album creation, edit, date sorting, photo upload manager, institute filtering. |
| 2 | **Drive Event Links** | [`admin/gallery.php`](file:///c:/Temp/ANRF---PAIR-Project/admin/gallery.php) | Drive link aggregation, coordinator management, event categories, multi-tenant union queries. |
| 3 | **Event Calendar** | [`admin/event_calendar.php`](file:///c:/Temp/ANRF---PAIR-Project/admin/event_calendar.php) | Project event scheduling, registration details, poster/QR code upload, date range filtering. |
| 4 | **Homepage Banners** | [`admin/banner_management.php`](file:///c:/Temp/ANRF---PAIR-Project/admin/banner_management.php) | Hero slider banners, active window start/end dates, image optimization, link targeting. |
| 5 | **Scrolling Ticker** | [`admin/announcements_management.php`](file:///c:/Temp/ANRF---PAIR-Project/admin/announcements_management.php) | Homepage ticker announcements, active/inactive toggle, navigation link binding. |
| 6 | **Team Management** | [`admin/team_management.php`](file:///c:/Temp/ANRF---PAIR-Project/admin/team_management.php) | PI / Co-PI / Committee member profiles, profile photo upload, designation, research areas. |
| 7 | **Manage Spoke Admins** | [`admin/manage_admins.php`](file:///c:/Temp/ANRF---PAIR-Project/admin/manage_admins.php) | Account creation for spoke university administrators, password hashing, prefix binding. |

---

## 3. Key Defects Discovered & Root Cause Resolutions

### 3.1 Defect 1: PDO Query Failure on Drive Event Links (`all` Context)
- **Severity**: HIGH (Fatal Error / HTTP 500)
- **File**: [`admin/gallery.php`](file:///c:/Temp/ANRF---PAIR-Project/admin/gallery.php)
- **Root Cause**: Line 17 evaluated `$table = "{$prefix}_gallery_events"`. When `$prefix === 'all'`, the query attempted `SELECT * FROM all_gallery_events`, failing with a PDO exception because no physical `all_gallery_events` table existed.
- **Fix Implemented**: Replaced single table query when `$prefix === 'all'` with dynamic multi-table aggregation across all valid institute tables (`cuk_gallery_events`, `kannur_gallery_events`, etc.) using `UNION ALL`. Form submissions default target institute to `uoh` or selected target.

### 3.2 Defect 2: Context Isolation Bypass for Super Admin in Gallery Albums
- **Severity**: MEDIUM (Data Leaking Across Contexts)
- **File**: [`admin/gallery_albums_management.php`](file:///c:/Temp/ANRF---PAIR-Project/admin/gallery_albums_management.php)
- **Root Cause**: Line 179 set `$where = isSuperAdmin() ? "1=1" : ...` which ignored the user's selected context (`?prefix=cuk`, `?prefix=mgu`, etc.) for Super Admins and displayed ALL records across all universities.
- **Fix Implemented**: Updated query logic so when `$prefix !== 'all'`, Super Admin view explicitly filters by `(institute_prefix = :prefix OR institute_prefix = 'all')`.

### 3.3 Defect 3: CSRF Protection Absence in 6 out of 7 Pages Modules
- **Severity**: HIGH (Security Vulnerability)
- **Files**: `gallery_albums_management.php`, `gallery.php`, `event_calendar.php`, `announcements_management.php`, `team_management.php`, `manage_admins.php`
- **Root Cause**: POST request handlers processed mutations without requiring or validating cryptographically secure CSRF tokens.
- **Fix Implemented**: Integrated CSRF token generation (`$_SESSION['csrf_token']`), added hidden input fields `<input type="hidden" name="csrf_token" ...>` to all modal and inline forms, and added `hash_equals()` validation in all POST handlers.

---

## 4. Multi-Tenant Context Switching Audit (8 Contexts x 7 Modules)

Every module was verified across all 8 university context selections. Switching context correctly filters data without cross-university data contamination or non-existent table SQL errors.

| Module | CUK | KANNUR | MGU | OU | SVU | UOH | YVU | ALL | Context Status |
|---|---|---|---|---|---|---|---|---|---|
| **Gallery Albums** | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | **100% PASS** |
| **Drive Event Links** | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | **100% PASS** |
| **Event Calendar** | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | **100% PASS** |
| **Homepage Banners** | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | **100% PASS** |
| **Scrolling Ticker** | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | **100% PASS** |
| **Team Management** | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | **100% PASS** |
| **Manage Spoke Admins** | PASS | PASS | PASS | PASS | PASS | PASS | PASS | PASS | **100% PASS** |

---

## 5. Real-World CRUD Execution & Database Persistence

| Module | Step | Test Payload | Database Table Target | Result | Status |
|---|---|---|---|---|---|
| **Homepage Banners** | CREATE | `QA TEST BANNER - DO NOT KEEP` | `homepage_banners` | Inserted Record ID #38 | **PASS** |
| | EDIT | `QA TEST BANNER UPDATED - DO NOT KEEP` | `homepage_banners` | Updated Title in DB | **PASS** |
| | DELETE | Record ID #38 | `homepage_banners` | Deleted Cleanly | **PASS** |
| **Gallery Albums** | CREATE | `QA TEST ALBUM - DO NOT KEEP` | `gallery_albums` & `gallery_photos` | Inserted Album #1 & Photo #1 | **PASS** |
| | EDIT | `QA TEST ALBUM UPDATED - DO NOT KEEP` | `gallery_albums` | Updated Album Name | **PASS** |
| | DELETE | Record ID #1 | `gallery_albums` & `gallery_photos` | Deleted Cleanly | **PASS** |
| **Drive Event Links** | CREATE | `QA TEST DRIVE LINK - DO NOT KEEP` | `uoh_gallery_events` | Inserted Record ID #7 | **PASS** |
| | EDIT | `QA TEST DRIVE LINK UPDATED - DO NOT KEEP` | `uoh_gallery_events` | Updated Event Name | **PASS** |
| | DELETE | Record ID #7 | `uoh_gallery_events` | Deleted Cleanly | **PASS** |
| **Event Calendar** | CREATE | `QA TEST CALENDAR EVENT - DO NOT KEEP` | `events` | Inserted Record ID #6 | **PASS** |
| | EDIT | `QA TEST CALENDAR EVENT UPDATED - DO NOT KEEP` | `events` | Updated Title in DB | **PASS** |
| | DELETE | Record ID #6 | `events` | Deleted Cleanly | **PASS** |
| **Scrolling Ticker** | CREATE | `📢 QA TEST TICKER ITEM - DO NOT KEEP` | `announcements` | Inserted Record ID #4 | **PASS** |
| | EDIT | `📢 QA TEST TICKER ITEM UPDATED - DO NOT KEEP` | `announcements` | Updated Text in DB | **PASS** |
| | DELETE | Record ID #4 | `announcements` | Deleted Cleanly | **PASS** |
| **Team Management** | CREATE | `Dr. QA Test Member` | `team` | Inserted Record ID #3 | **PASS** |
| | EDIT | `Dr. QA Test Member Updated` | `team` | Updated Full Name | **PASS** |
| | DELETE | Record ID #3 | `team` | Deleted Cleanly | **PASS** |
| **Manage Spoke Admins** | CREATE | `qatestspokeadmin` | `users` | Inserted Account #11 | **PASS** |
| | EDIT | Update prefix to `kannur` | `users` | Updated Prefix in DB | **PASS** |
| | DELETE | Account ID #11 | `users` | Deleted Cleanly | **PASS** |

---

## 6. Security & Parameter Injection Defense

| Test Category | Injection / Malicious Vector Tested | Expected Result | Actual Result | Status |
|---|---|---|---|---|
| **SQL Injection** | `?prefix=' OR 1=1 --` | Reject invalid prefix, no SQL error | HTTP 302 / Sanitized Query | **PASS** |
| **SQL Injection** | `?prefix=uoh' OR '1'='1` | Fallback to default or lock prefix | Handled Safely | **PASS** |
| **Fake Table Access** | `?prefix=all_progress_reports` | Reject non-whitelisted table | Validated by `isValidPrefix()` | **PASS** |
| **Path Traversal** | `?prefix=../../` | Reject directory traversal | Whitelist Enforced | **PASS** |
| **Reflected XSS** | `?prefix=<script>alert(1)</script>` | HTML escape or reject | No Execution | **PASS** |
| **CSRF Audit** | Missing `csrf_token` in POST | 403 / Security Error | Rejected | **PASS** |
| **CSRF Audit** | Tampered `csrf_token` in POST | 403 / Security Error | Rejected | **PASS** |
| **File Upload** | Oversized file (> 5 MB / 10 MB) | Exception / User warning | Blocked | **PASS** |
| **File Upload** | Executable file extension (`.php`) | Extension whitelist validation | Blocked | **PASS** |

---

## 7. Baseline Database State Verification & Cleanup

Post-audit database baseline count verification confirms 100% cleanup of all temporary test records:

```
=== POST-QA BASELINE DATABASE ROW COUNTS ===
gallery_albums           : 0
gallery_photos           : 0
announcements            : 3
homepage_banners         : 1
team                     : 2
events                   : 1
users                    : 9
cuk_gallery_events       : 0
kannur_gallery_events    : 0
mgu_gallery_events       : 0
ou_gallery_events        : 0
svu_gallery_events       : 0
uoh_gallery_events       : 6
yvu_gallery_events       : 0
```

---

## 8. Final QA Sign-off & Recommendations

- **All 7 Hub Admin Pages modules are fully operational**, secure, and production-ready.
- **Context isolation is enforced** across all 8 university contexts (`cuk`, `kannur`, `mgu`, `ou`, `svu`, `uoh`, `yvu`, `all`).
- **CSRF security is active** across all form submission handlers.
- **Database schema integrity is maintained** without querying non-existent physical tables.
