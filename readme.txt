=== Bullet Forms ===
Contributors: mdtanvirahmed
Tags: form builder, multi-step form, forms, contact form, submissions
Requires at least: 5.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.1.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Bullet Forms is a lightweight WordPress plugin to create multi-step forms, collect submissions, and review or export responses from the dashboard.

== Description ==

Bullet Forms lets you build and publish professional forms without writing code. Create your form in the built-in builder, insert it anywhere with a shortcode, and manage entries from the submissions screen.

### Key Features

* Create multi-step forms with a simple visual editor
* Add common field types such as text, email, textarea, select, radio, checkbox, phone, number, and date
* Set required fields and option lists directly in the builder
* Use the shortcode `[bfmsf_form id="1"]` to display a form on any page or post
* Review submissions, view entry details, and export data to CSV
* Works with the WordPress admin interface and responsive front-end layout

== External Services ==

This plugin relies on the following third-party / external services to function:

1. **hCaptcha** (https://www.hcaptcha.com)
   * Purpose: Provides spam and abuse protection for form submissions.
   * Data Sent: When a visitor submits a form containing an hCaptcha field, the user's captcha response token and browser properties (collected by the hCaptcha widget) are sent to hCaptcha to verify the submission's legitimacy.
   * Terms of Service: https://www.hcaptcha.com/terms
   * Privacy Policy: https://www.hcaptcha.com/privacy

2. **Google reCAPTCHA v2** (https://www.google.com/recaptcha)
   * Purpose: Provides spam and abuse protection for form submissions.
   * Data Sent: When a visitor submits a form containing a reCAPTCHA field, the user's captcha verification response token and browser interaction data (collected by Google reCAPTCHA) are sent to Google for validation.
   * Terms of Service: https://policies.google.com/terms
   * Privacy Policy: https://policies.google.com/privacy

3. **Cloudflare Turnstile** (https://www.cloudflare.com/products/turnstile)
   * Purpose: Provides user-friendly, non-intrusive spam protection for form submissions.
   * Data Sent: When a visitor submits a form containing a Cloudflare Turnstile field, the user's captcha response token and telemetry/challenge data (collected by Turnstile) are sent to Cloudflare to check validity.
   * Terms of Service: https://www.cloudflare.com/website-terms/
   * Privacy Policy: https://www.cloudflare.com/privacypolicy/

4. **Stripe** (https://stripe.com)
   * Purpose: Processes payments for payment-enabled forms.
   * Data Sent: If Stripe is selected as the payment gateway and configured by the administrator, the form's payment amount, currency, and name are sent to Stripe's APIs to construct a secure checkout session.
   * Terms of Service: https://stripe.com/terms
   * Privacy Policy: https://stripe.com/privacy

5. **PayPal** (https://www.paypal.com)
   * Purpose: Redirects users to PayPal to complete payments for payment-enabled forms.
   * Data Sent: If PayPal is selected as the payment gateway and configured, payment details (amount, currency, item name, business email) are sent via redirect parameters to PayPal's checkout server to complete the transaction.
   * Terms of Service: https://www.paypal.com/webapps/mpp/ua/legalhub-full
   * Privacy Policy: https://www.paypal.com/webapps/mpp/ua/privacy-full

6. **Google Fonts** (https://fonts.google.com)
   * Purpose: Used to style typography in the admin dashboard panel.
   * Data Sent: When loading the plugin's form builder in the WordPress admin panel, the browser requests the 'Inter' font family from Google's content delivery servers (fonts.googleapis.com).
   * Terms of Service: https://policies.google.com/terms
   * Privacy Policy: https://policies.google.com/privacy

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` on your WordPress site.
2. Activate **frankel-bullet-form** from the Plugins screen.
3. Open the plugin menu in the admin area and create your first form.
4. Copy the generated shortcode and paste it into any page, post, or widget area.

== Usage ==

### Create a form

1. Go to **Bullet Forms > Add New**.
2. Enter a form title and configure your steps and fields.
3. Save the form to publish it.

### Display a form on your site

1. Copy the shortcode from the forms list or builder page.
2. Paste it into any page or post, for example:
   `[bfmsf_form id="1"]`

### Review submissions

1. Go to **Bullet Forms > Submissions**.
2. Select the form you want to inspect.
3. Use the **View details** and **Export to CSV** actions to manage form entries.

== Frequently Asked Questions ==

= How do I embed a form? =

Use the shortcode displayed in the plugin interface, such as `[bfmsf_form id="1"]`.

= Can I export form responses? =

Yes. Open the Submissions page for a form and use the CSV export option.

= Do I need coding skills to use it? =

No. The plugin is designed to be used from the WordPress admin dashboard.

= Does this plugin work with multi-site WordPress installations? =

Yes, Bullet Forms supports WordPress multi-site installations.

== Changelog ==

= 1.1.3 =

* Added country code dropdown for phone fields with configurable default.
* Enhanced confirm field with confirmation text support for terms and conditions.
* Improved checkbox styling with custom checkboxes and default checked state.
* Added field-specific options panel to show only relevant settings per field type.
* Fixed entries/submissions not displaying due to missing sync_fields_to_db() execution.
* Fixed checkbox and confirm field options not showing proper controls in the builder.
* Fixed phone field submission storing array instead of combined string.
* Removed duplicate buildOptionControlHtml function from admin.js.

= 1.1.2 =

* Added full third-party / external service disclosure to readme.txt to comply with WordPress.org review guidelines.
* Implemented server-side CAPTCHA verification for hCaptcha, Google reCAPTCHA v2, and Cloudflare Turnstile.
* Updated frontend submission handler to collect and forward CAPTCHA response tokens to the server.
* Created missing languages directory required by the plugin header Domain Path declaration.

= 1.1.1 =

* Resolved WordPress.org Plugin Directory review feedback.
* Replaced direct style and script echo output with `wp_add_inline_style()` and `wp_add_inline_script()`.
* Removed global `ob_start()` to prevent output buffer misalignment.
* Switched from `move_uploaded_file()` to `wp_handle_upload()` for secure file uploads.

= 1.1.0 =

* Updated the plugin builder click to drag and drop.
* Refreshed the plugin documentation and usage instructions for the current release.
* Improved the submission-management overview and admin guidance.
* Updated plugin naming, documentation, and compatibility details for review-ready packaging.

= 1.0.3 =

* Initial release.

== Upgrade Notice ==

= 1.1.3 =

This release includes major improvements to the form builder with field-specific options, enhanced phone field with country codes, and improved checkbox/confirm field styling. Critical fixes for submissions display are also included. Recommended update for all users.

= 1.1.2 =

This release includes CAPTCHA support and external service disclosures. Update recommended.

= 1.1.1 =

Important security and compatibility fixes. Recommended update for all users.

== Screenshots ==

1. Form builder interface showing the row-based layout system.
2. Field options panel with type-specific controls.
3. Frontend form with multi-step navigation.
4. Submissions management screen with CSV export.

== Arbitrary Section ==

### Developer Notes

Bullet Forms stores form data in custom database tables (`bfmsf_forms`, `bfmsf_form_fields`, `bfmsf_submissions`) for optimal performance. The plugin also includes a REST API (namespace `bfmsf/v1`) for programmatic access to forms and submissions.

### Support

For support, bug reports, and feature requests, please visit the WordPress plugin support forum.