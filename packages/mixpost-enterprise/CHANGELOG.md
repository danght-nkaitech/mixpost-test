# Changelog

All notable changes to `Mixpost Enterprise` will be documented in this file.

## 5.3.0 - 2026-02-11

Media Library features and improvements!

## 5.2.0 - 2025-12-29

**Added**

- Workspace API endpoints
- Subscription API endpoints
- User API endpoints
- Receipt API endpoints

## 5.1.0 - 2025-08-15

**Fixes**

- Fixed WorkspaceServiceManager -> get function.

**Changes**

- Installed code quality tools: ESLint, Prettier, Pinia, and Larastan
- Reformatted all files after installing code quality tools

## 5.0.0 - 2025-08-03

**New features**

- Use Mixpost Pro `v4.0.0`

**Changes**

- Added `nl-NL` language
- Added support for VAT field in Stripe payments

## 4.1.1 - 2025-05-28

**Fixes**

- Fixed the issue with creating new subscriptions on the Paddle Billing platform.

## 4.1.0 - 2025-05-19

**Miscellaneous**

- Support Mixpost Pro 3.6
- Support Laravel 12
- Upgraded to Tailwind CSS 4

## 4.0.3 - 2024-12-07

**Fixes**

- Fixed `X` service default documentation URL

**Changes**

- Enhanced translations

## 4.0.2 - 2024-11-16

**Changes**

- Updated translations

## 4.0.1 - 2024-11-08

**Fixes**

- Fixed incorrect trial days data type for subscription

## 4.0.0 - 2024-10-31

**Fixes**

- Delete workspace owner if user owner is detached;
- Attach the new user to a workspace with the `is_owner` checkbox

**Changes**

- Refined mail layout
- Improved session expired user experience

**Miscellaneous**

- Support Mixpost Pro core v3.0
- Support Laravel 11

## 3.1.1 - 2024-10-25

**Fixes**

- Fixed checks if a member is already invited to the current workspace
- Fixed notification error render
- Fixed dropdown item text alignment

## 3.1.0 - 2024-09-12

**Fixes**

- Fixed subscription status translation

**Miscellaneous**

- Enhanced Paystack subscription process

## 3.0.4 - 2024-09-06

**Fixes**

- Fixed stop impersonation, when impersonate user email, is not confirmed

## 3.0.3 - 2024-09-06

**Fixes**

- Fixed logout and profile routes when user email is not confirmed
- Fixed button style disabled

## 3.0.1 - 2024-06-04

**Fixes**

- Fixed Paddle API endpoint when sandbox is disabled

## 3.0.0 - 2024-06-03

**New features**

- Integrated Paddle Billing
- Coupons
- Email verification
- Automatically remove unverified emails
- User account deletion

**Changes**

- Stripe payment with the native checkout page
- Create a friendly name for the workspace during user registration
- Optimized invitation feature
- Added `forceFullWidth` props for the VerticalGroup component
- Improved content of the emails
- Dropped unique index for ['workspace_id', 'order_id'].
- Renamed `order_id` column to `invoice_number` for `mixpost_e_receipts` table
- Renamed `checkout_id` column to `transaction_id` for `mixpost_e_receipts` table
- Created a unique index for the `invoice_number` column
- Created index for `workspace_id` & `invoice_number` columns
- Optimized manages subscription methods for all payment platform
- Update translations

**Fixes**

- Fixed subscribe action during the trial
- Fixed workspace valid function when is status unlimited and subscription trialing
- Fixed translations for price yearly/monthly
- Fixed SubscriptionCanceled event on Paystack disable subscription
- Fixed localization on error pages
- Fixed receipt pdf charset

## 2.0.1 - 2024-05-10

**Fixes**

- Fixed billing config prorate option
- Fixed billing title translation

**Miscellaneous**

- Update translations
- Enhanced support for RTL layout

## 2.0.0 - 2024-04-01

**New Features**
Everything from Mixpost Pro `v2` +

- System webhooks
- AI Credit for plan
- Twitter API for workspace

**Changes**

- Improved design and layout

## 1.2.5 - 2024-01-26

- Fixed validation of workspace subscription after generic trialing
- Fixed remaining trial days value
- Added more tests for the Workspace model

## 1.2.4 - 2024-01-23

- Fixed Stripe success subscription redirection

## 1.2.3 - 2024-01-23

Composer requires `"inovector/mixpost-pro-team": "^1.4"`

## 1.2.2 - 2024-01-22

- Fixed Mail localization
- Fixed `created_at` column for Users & Workspaces table.
- Update translations

## 1.2.1 - 2024-01-20

- Fixed translation for Stripe form payment

## 1.2.0 - 2024-01-20

**Localization Support**

- Fully Translated: ar-SA, de-De, fr-CA
- In Beta: ca-ES, cs-CZ, es-ES, eu-ES, fr-FR, it-IT, ro-RO, ru-RU, sk-SK.

## 1.1.0 - 2024-01-04

- Paystack payment integration

## 1.0.2 - 2023-10-26

- Added version to admin status page
- Remove empty spaces from payment details

## 1.0.1 - 2023-10-26

- Display disabled plan when adding generic subscription as admin
- Fix the primary color for the date picker
- Fix the "Post Now" error when the subscription has a scheduled post limit.

**Internal Changes**

- Removed duplicate classes from `overrideFlatPickr.css`
- `README.md` modified

## 1.0.0 - 2023-09-27

**First Release**

- Manage Customers
- Manage Subscriptions
- Manage Receipts
- Integrate Payment Platforms
- Create Plans
- Enterprise Settings
- Theme Customization
- and more...
