# Submission verification for CipherStack TAMS POS

Reviewed on November 26, 2026. The application is functionally ready for the midterm submission with the project owner's explicitly confirmed approval to use Laravel.

## Submit these two links

- **GitHub source and setup instructions:** https://github.com/JoshuaNeilzz/tams-pos-group
- **Hosted working application:** https://tams-pos-group.vercel.app

The midterm asks for raw project files including a database export **or migrations and seeders**. This repository contains migrations and seeders, so a separate export containing hosted records is not needed.

## Documents reviewed

The original files were located in the owner's Downloads folder and read locally. The instructor's original documents have not been redistributed in this repository.

- `IT0049 - Midterm Project - Complete Point-of-Sale System (CodeIgniter).docx` - primary feature and submission requirements.
- `IT0049 - TFA7 - Forms, Validation, and File Upload (Laravel POS).docx` - supporting Laravel form, CRUD, and prepared-avatar requirements.
- `IT0049 - TFA8 - Locking It Down (Resource Controllers and Auth in Laravel).docx` - supporting Laravel resource-routing and authentication requirements.

**Framework clarification:** the midterm document names CodeIgniter. The owner explicitly confirmed **"Midterm with Laravel already approved"** during the November 26 review. The repository remains Laravel 12 under that approval; it is not represented as a CodeIgniter application. TFA7/TFA8 explicitly concern Laravel. This record documents the approved project scope rather than claiming the original midterm wording was different.

**Product scope:** the midterm specifies generic product records and does not prescribe a merchandise category. The sari-sari foods/snacks theme retains the required product/customer/staff/sale relationships.

## Required features and evidence

| Requirement from the midterm brief | Status | Verification evidence |
| --- | --- | --- |
| Relational product, customer, staff, and sale schema | Passed | Committed migrations include the specified fields and foreign-key relationships; additional order/restock tables extend the required core. |
| Product list, add, edit, delete or archive | Passed | Local regression tests and live add/edit/archive checks. Archive preserves historical sales. |
| Uploaded product image prepared for display | Passed | Live PNG upload was re-encoded as 640 x 640 WebP, retrieved successfully through authenticated media, and retrieved again in a fresh browser session. |
| Full customer CRUD | Passed | Live create, detail/read, edit/update, and delete checks using a temporary fictional customer. |
| Full staff/user CRUD | Passed | Live create, detail/read, edit/update, and removal checks using a temporary fictional staff account. |
| Staff avatar upload and preparation | Passed | Live avatar upload was resized to 256 x 256; listing and detail pages display the stored avatar. |
| Hashed passwords | Passed | Laravel hashed-password model cast; automated hash-verification tests; newly created staff credentials also successfully signed in on the live site. |
| Only authenticated staff can reach management | Passed | Logged-out requests to Dashboard, Products, Customers, Staff, Sales, and Orders redirect to login; shopper requests to staff management return 403. Write permissions are also covered by tests and route middleware. |
| Staff Record Sale with product and optional customer | Passed | Live sale with a selected customer; automated walk-in-sale coverage for an omitted customer. |
| Stock decreases by sold quantity | Passed | Live sale of 2 packs from 3 left 1 available; automatic totals and transaction locking also covered by tests. |
| Reject sale above available stock with clear message | Passed | A direct live request for 2 packs when 1 remained returned HTTP 422 with an Insufficient stock message; stock stayed at 1. |
| Sales history shows product/customer/staff/quantity/total/date | Passed | Live history record checked for all required columns and the expected PHP 26.00 total. |
| Validation and safe uploads | Passed | Automated rejection of invalid prices, quantities, file types, and image inputs; prepared media served with the expected MIME type and nosniff header. |
| MVC organization and framework conventions | Checked | Separate models/controllers/Blade views; resource routes for products, customers, and staff; shared image-preparation service; transaction-based sales and order services. |
| Reproducible database | Passed | Migrations and seeders tracked in Git; regression suite recreates the schema in a clean test database. |
| README explains setup and usable accounts | Passed | README includes hosted links, exact staff/shopper credentials, local setup, account approval, adding snacks, restocking, and troubleshooting. |
| Working hosted deployment and GitHub source | Passed | Production URL is live; the final source and documentation are pushed to main and deployed to the existing Vercel project. |

The brief does not prescribe image dimensions; 640 x 640 product images and 256 x 256 avatars are this project's display-ready sizes. Local installs use file storage; the serverless host uses database-backed media for persistence. The product/avatar columns retain image paths.

## Additional requested workflows

These extend the graded POS features and do not replace the required staff Record Sale page.

- **Staff Add stock:** verified live with 10 starting packs plus a 5-pack delivery giving 15 packs, visible to the shopper.
- **Customer Buy / reserve:** verified live with stock reservation, server-side pricing, and duplicate-submit protection.
- **Cash on pickup:** staff completes an order after payment/collection; pending orders are not counted as paid revenue, and completion does not deduct stock twice.
- **Cancellation:** verified live; reserved stock returns once.
- **Persistence:** live purchases and media were retrieved in fresh browser sessions; the evaluation store also retained data across deployment.
- **Design:** responsive snack catalog with original editable packet sketches, plus CipherStack and all four members on the guide page.

## Evaluation accounts

| Role | Correct sign-in page | Username | Password |
| --- | --- | --- | --- |
| Approved staff | https://tams-pos-group.vercel.app/staff/login | `demo_staff` | `DemoStaff123!` |
| User / shopper | https://tams-pos-group.vercel.app/login | `demo_user` | `DemoUser123!` |

Both accounts were verified against the hosted database. Sign out before switching roles, or use separate browser profiles. New self-registered staff require approval. For adding snacks or stock, use the already approved staff account and open **Snacks & stock**.

These are shared credentials for a fictional classroom store. They are intentionally documented for evaluation, not suitable for a real store holding personal or business data. Infrastructure credentials, private application keys, local environment files, and dependencies are excluded from Git.

## Test run and audit cleanup

- `php artisan test`: **36 passed, 339 assertions** in the final audit.
- `php artisan route:list --except-vendor`: management/resource routes and middleware inspected.
- Live customer/staff/product CRUD, product/avatar uploads, authentication, counter sales, overselling rejection, and history preservation passed.
- Temporary QA snacks were archived, temporary QA staff were removed through the staff module, and the temporary directory-only customer was deleted. Fictional audit sales remain in history to verify preservation; the six sample snacks were not consumed by this audit.

The group still needs to submit the two links through the instructor's required submission channel. This verification does not submit work to a course portal or predict a grade.
