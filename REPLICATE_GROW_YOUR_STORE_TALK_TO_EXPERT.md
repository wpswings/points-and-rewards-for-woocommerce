# Replicate "Grow Your Store With Our Services" + "Talk to an Expert" In The Current Plugin

Implement the full admin-side marketing rail card and Talk to an Expert workflow from the source plugin into the **current plugin** (the current working directory). Do not ask clarifying questions. Inspect the current plugin structure, choose the analogous files/entry points, implement the feature end-to-end, add regression coverage, run verification, and stop only when the feature is complete.

## Operating Assumptions

- The **target plugin** is the current working directory.
- The **source of truth** is the plugin at:
  - `/home/cedcoss/Local Sites/pdf-optimization-and-performance/app/public/wp-content/plugins/pdf-generator-for-wp`
- Do **not** blindly copy the source plugin prefix (`pgfw`, `wps_pgfw`, etc.) into the target plugin.
- Instead:
  - detect the target plugin's existing naming convention
  - use the target plugin's prefix, class naming pattern, text domain, menu slug, asset handles, and file layout
- Preserve behavior, markup intent, and data contract from the source implementation.

## Source Files To Study First

Use these as the implementation reference:

- Dashboard rail/card markup:
  - `/home/cedcoss/Local Sites/pdf-optimization-and-performance/app/public/wp-content/plugins/pdf-generator-for-wp/admin/partials/pdf-generator-for-wp-admin-dashboard.php`
- Modal + HubSpot submit class:
  - `/home/cedcoss/Local Sites/pdf-optimization-and-performance/app/public/wp-content/plugins/pdf-generator-for-wp/includes/class-pdf-generator-for-wp-talk-to-expert-form.php`
- Admin JS for modal open/close + submit:
  - `/home/cedcoss/Local Sites/pdf-optimization-and-performance/app/public/wp-content/plugins/pdf-generator-for-wp/admin/src/js/pdf-generator-for-wp-admin.js`
- Admin CSS for rail card + modal:
  - `/home/cedcoss/Local Sites/pdf-optimization-and-performance/app/public/wp-content/plugins/pdf-generator-for-wp/admin/src/css/pdf-generator-for-wp-admin-modern.css`
- Bootstrap/loader wiring:
  - `/home/cedcoss/Local Sites/pdf-optimization-and-performance/app/public/wp-content/plugins/pdf-generator-for-wp/includes/class-pdf-generator-for-wp.php`
- Admin script localization / nonce wiring:
  - `/home/cedcoss/Local Sites/pdf-optimization-and-performance/app/public/wp-content/plugins/pdf-generator-for-wp/admin/class-pdf-generator-for-wp-admin.php`

Also mirror the behavior locked by these tests:

- `/home/cedcoss/Local Sites/pdf-optimization-and-performance/app/public/wp-content/plugins/pdf-generator-for-wp/tests/dashboard-marketing-rail-regression.php`
- `/home/cedcoss/Local Sites/pdf-optimization-and-performance/app/public/wp-content/plugins/pdf-generator-for-wp/tests/dashboard-talk-to-expert-modal-regression.php`
- `/home/cedcoss/Local Sites/pdf-optimization-and-performance/app/public/wp-content/plugins/pdf-generator-for-wp/tests/talk-to-expert-service-values-regression.php`
- `/home/cedcoss/Local Sites/pdf-optimization-and-performance/app/public/wp-content/plugins/pdf-generator-for-wp/tests/talk-to-expert-budget-values-regression.php`
- `/home/cedcoss/Local Sites/pdf-optimization-and-performance/app/public/wp-content/plugins/pdf-generator-for-wp/tests/talk-to-expert-payload-key-normalization-regression.php`
- `/home/cedcoss/Local Sites/pdf-optimization-and-performance/app/public/wp-content/plugins/pdf-generator-for-wp/tests/talk-to-expert-hubspot-payload-regression.php`
- `/home/cedcoss/Local Sites/pdf-optimization-and-performance/app/public/wp-content/plugins/pdf-generator-for-wp/tests/talk-to-expert-annual-revenue-regression.php`
- `/home/cedcoss/Local Sites/pdf-optimization-and-performance/app/public/wp-content/plugins/pdf-generator-for-wp/tests/talk-to-expert-success-message-regression.php`

## Goal

Recreate the **admin dashboard side rail marketing panel** and the full **Talk to an Expert** modal form flow in the target plugin, matching the source behavior closely:

1. Add the **Grow Your Store With Our Services** rail card
2. Add the **Talk to an Expert** CTA button in that card
3. Render the modal on the plugin dashboard/admin screen
4. Submit the form via AJAX to WordPress
5. Forward the sanitized payload to HubSpot
6. Show a cleaned success/error response in the modal
7. Add regression tests in the target plugin analogous to the source plugin

## Required UI: Side Rail Card

Add a rail/sidebar card on the target plugin's main admin dashboard/overview page. Place it in the same relative position as the source implementation:

- after the "Need help with this plugin?"-style card if one exists
- before the "Still facing problems?" / contact card if one exists
- otherwise place it in the right-hand support/marketing rail area that is most analogous

### Card content

Use this content:

- Heading: `Grow Your Store With Our Services`
- Supporting copy: `Expert solutions to boost your store's performance.`
- Four service rows:
  - `SEO Services`
    - `Improve rankings & organic traffic`
  - `Google Ads Setup And G4 Setup`
    - `Run profitable ad campaigns`
  - `Speed Optimization`
    - `Faster store, happier customers`
  - `WooCommerce Development Services`
    - `Custom Solution For your store needs`
- CTA button:
  - `Talk to an Expert`
- Footer note:
  - `Services by WP Swings`

Each service row should link to the same external contact URL used by the source rail card. Keep the `Talk to an Expert` CTA as an in-page trigger that opens the modal.

### Visual style

Match the source card treatment closely:

- rounded card
- soft border
- elevated but subtle box shadow
- stacked service items
- icon circle on left
- chevron on right
- dark full-width CTA button
- compact muted footer note

Do not introduce a "New Feature" badge.

## Required UI: Talk to an Expert Modal

Render the modal only on the target plugin's main admin dashboard/settings screen equivalent.

Modal structure and behavior must match the source:

- hidden by default
- opened via `[data-*-open-expert-modal]`
- closed via backdrop, close button, and `Escape`
- adds/removes body overflow lock class
- shows inline success/error status area
- disables submit button while request is in flight
- restores submit button text after request completes

### Modal copy

- Eyebrow: `Marketing Services`
- Title: `Talk to an Expert`
- Intro: `Share your store goals and our team will reach out with the right next step.`
- Submit button:
  - normal: `Submit Request`
  - loading: `Sending...`

## Required Form Fields

Create the form with these fields:

- `firstname`
  - text
  - default from current user first name if available
- `lastname`
  - text
  - default from current user last name if available
- `email`
  - email
  - required
  - default from current user email if available
- `phone`
  - text
- `what_services_do_you_need_help_with`
  - checkbox group
- `budget`
  - select
- `message`
  - textarea

## Required Service Value Contract

The visible labels and submitted values are **not** the same. Preserve this exact mapping:

- `seo_services` => `SEO services`
- `google_ads_setup_and_ga4_setup` => `Google Ads Setup and GA4 setup`
- `speed_optimization` => `Speed Optimization`
- `woocommerce_development_services` => `WooCommerce Development Services`

Implementation requirements:

- checkbox input `value` must be the slug
- visible label must be the human-readable text
- sanitize submitted services against the **array keys** of the service option map

## Required Budget Value Contract

The budget select must use this exact contract:

- `''` => `Please Select`
- `'$500 - $1000'` => `'$500 - $1000'`
- `'$1001 - $5000'` => `'$1001 - $5000'`
- `'$5001 - $10000'` => `'$5001 - $10000'`
- `'$10001 - $15000'` => `'$10001 - $15000'`

Implementation requirements:

- the placeholder option must have `value=""`
- the placeholder option must be disabled
- sanitize submitted budget against the **array keys** of the budget option map
- do not store or send `"Please Select"` as the selected value

## Required AJAX Payload Behavior

The client-side JS must normalize `FormData` keys before serializing to JSON.

Important:

- checkbox names may be emitted as `field_name[]`
- before building the JSON payload, strip the trailing `[]`
- repeated values must still aggregate into an array

This must prevent payloads like:

- `what_services_do_you_need_help_with[]: "woocommerce_development_services"`

and instead produce:

- `what_services_do_you_need_help_with: ["woocommerce_development_services"]`

## Required AJAX/Server Flow

Add an authenticated admin AJAX action in the target plugin, analogous to the source implementation.

Requirements:

- use nonce verification
- restrict access to:
  - `manage_options`
  - or `manage_woocommerce`
- read `form_data` from AJAX POST body
- decode JSON to array
- validate email
- sanitize all fields
- build the HubSpot field payload
- return JSON success or JSON error with message

## Required HubSpot Integration

Replicate the source HubSpot submission behavior using:

- Base URL:
  - `https://api.hsforms.com/`
- Portal ID:
  - `25444144`
- Form ID:
  - `91bfc24e-c1a7-4858-878a-9f2fb4728620`

Send these HubSpot fields when available:

- `firstname`
- `lastname`
- `email`
- `phone`
- `what_services_do_you_need_help_with`
- `budget`
- `message`
- `currency`
- `org_plugin_name`
- `company`
- `website`
- `country`
- `annualrevenue`

### Additional HubSpot requirements

- `fields` must always encode as a dense JSON array
  - use `array_values( array_filter( $fields ) )` before `wp_json_encode()`
- if a field value is an array, join it with `;`
- do not send empty strings
- do not send nulls

### org_plugin_name

Do **not** hardcode `PDF Generator For Wp`.

Instead:

- derive the target plugin label/name from the target plugin's own naming context
- send that as `org_plugin_name`

### company / website / country / currency

Match source behavior:

- company => site name
- website => home URL
- country => WooCommerce default country label if available
- currency => WooCommerce currency code if available

## Required annualrevenue Behavior

Do **not** use budget as annual revenue.

`annualrevenue` must be the store's **last 12 months of paid order revenue**.

Implement exactly this behavior:

1. Prefer WooCommerce analytics order stats table when available:
   - table: `{wpdb_prefix}wc_order_stats`
   - sum `total_sales`
   - only paid statuses
   - only parent orders
   - only orders with valid `date_paid`
   - only last 12 months
2. Fallback to `wc_get_orders()` if analytics table is unavailable
3. Format as a string with `number_format( ..., 2, '.', '' )`

## Required Success/Error Message Cleanup

When HubSpot returns `inlineMessage`, clean it before showing it.

Required cleanup:

- strip tags
- decode HTML entities
- collapse whitespace including non-breaking spaces / `&#xa0;`
- trim the final string

This must turn messages like:

- `&#xa0; Thank you for submitting your request.`

into:

- `Thank you for submitting your request.`

## Required Wiring In The Target Plugin

Detect the target plugin equivalents and wire the feature in cleanly:

1. Bootstrap / loader:
   - include the new Talk to an Expert class
   - instantiate it only in the admin context where the target plugin initializes dashboard/admin features
2. Admin script enqueue:
   - localize the AJAX URL
   - localize the Talk to an Expert nonce
3. Dashboard partial:
   - insert the marketing rail card markup
4. Admin JS:
   - open/close modal
   - normalize `[]` keys
   - submit AJAX request
   - show inline state
5. Admin CSS:
   - rail card styling
   - modal styling
   - form styling

## File Creation / Update Guidance

Adapt this to the target plugin structure, but expect to touch files analogous to:

- a dashboard/admin partial
- the main admin JS bundle
- the main admin CSS bundle
- the admin enqueue/localization class
- the plugin bootstrap/loader
- a new include/class file for the Talk to an Expert form flow
- test files under the target plugin's `tests/` directory

## Regression Coverage To Add In The Target Plugin

Add tests analogous to the source plugin's regression tests. Name them according to the target plugin's conventions, but they must cover:

1. marketing rail card is present
2. modal markup and AJAX hooks are present
3. service labels and submitted values mapping
4. budget labels and submitted values mapping
5. payload key normalization for `[]`
6. HubSpot payload uses JSON array for `fields`
7. annual revenue comes from last 12 months of paid orders
8. success message strips encoded spacing artifacts

These tests can be source-contract tests like the ones in `pdf-generator-for-wp/tests/`.

## Implementation Rules

- Keep the feature scoped to the target plugin only
- Follow the target plugin's existing code style
- Preserve the target plugin's prefix/text domain/class naming
- Do not modify the source plugin
- Do not add unrelated refactors
- Use targeted edits only

## Verification Requirements

Before finishing:

1. Run `php -l` on all touched PHP files
2. Run the new target-plugin regression tests you added
3. If the target plugin already has related tests, run the narrow affected subset
4. Report exactly which files changed
5. Report any remaining risk if live HubSpot submission was not exercised

## Final Deliverable

Complete the implementation in the current plugin so it behaves like the `pdf-generator-for-wp` feature described above, with target-plugin-specific naming and wiring, and with regression coverage proving the behavior.
