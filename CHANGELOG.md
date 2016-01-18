# Change Log

## v1.4

### Fixed

### Improved

### Added

- Added support for tax no on users.

### Changed

### Backwards Incompatible Changes

- Setting `billing.paymentTerms` has been removed in favor of `invoice.terms`.

- Extracted invoicing into billing_invoice, models have been
  moved into new namespaces:
  - `billing_core\models\Invoices` -> `billing_invoice\models\Invoices`

- Extracted payments into billing_payment, models have been
  moved into new namespaces:
  - `billing_core\models\Payments` -> `billing_payment\models\Payments`

- Settings `billing.vatRegNo` and `billing.taxNo` have been deprecated
  in favor of fields on `contact.billing`.
