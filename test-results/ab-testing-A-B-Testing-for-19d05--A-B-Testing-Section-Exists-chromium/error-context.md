# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: ab-testing.spec.js >> A/B Testing for Campaign Modal >> 2. Verify A/B Testing Section Exists
- Location: tests/e2e/ab-testing.spec.js:25:3

# Error details

```
Error: expect(locator).toBeVisible() failed

Locator:  locator('#wps_wpr_enable_ab_testing')
Expected: visible
Received: hidden
Timeout:  5000ms

Call log:
  - Expect "toBeVisible" with timeout 5000ms
  - waiting for locator('#wps_wpr_enable_ab_testing')
    13 × locator resolved to <input value="1" type="checkbox" checked="checked" id="wps_wpr_enable_ab_testing" name="wps_wpr_enable_ab_testing"/>
       - unexpected value "hidden"

```

```yaml
- navigation "Main menu":
  - link "Skip to main content":
    - /url: "#wpbody-content"
  - link "Skip to toolbar":
    - /url: "#wp-toolbar"
  - list:
    - listitem:
      - link "Dashboard":
        - /url: index.php
      - list:
        - listitem:
          - link "Home":
            - /url: index.php
        - listitem:
          - link "Updates 2":
            - /url: update-core.php
    - listitem:
      - link "Posts":
        - /url: edit.php
      - list:
        - listitem:
          - link "All Posts":
            - /url: edit.php
        - listitem:
          - link "Add Post":
            - /url: post-new.php
        - listitem:
          - link "Categories":
            - /url: edit-tags.php?taxonomy=category
        - listitem:
          - link "Tags":
            - /url: edit-tags.php?taxonomy=post_tag
    - listitem:
      - link "Media":
        - /url: upload.php
      - list:
        - listitem:
          - link "Library":
            - /url: upload.php
        - listitem:
          - link "Add Media File":
            - /url: media-new.php
    - listitem:
      - link "Pages":
        - /url: edit.php?post_type=page
      - list:
        - listitem:
          - link "All Pages":
            - /url: edit.php?post_type=page
        - listitem:
          - link "Add Page":
            - /url: post-new.php?post_type=page
    - listitem:
      - link "Comments":
        - /url: edit-comments.php
    - listitem:
      - link "WooCommerce":
        - /url: admin.php?page=wc-admin
      - list:
        - listitem:
          - link "Home 2":
            - /url: admin.php?page=wc-admin
        - listitem:
          - link "Orders 3":
            - /url: admin.php?page=wc-orders
        - listitem:
          - link "Points and Rewards":
            - /url: admin.php?page=wps-rwpr-setting
        - listitem:
          - link "Customers":
            - /url: admin.php?page=wc-admin&path=/customers
        - listitem:
          - link "Coupons":
            - /url: admin.php?page=coupons-moved
        - listitem:
          - link "Reports":
            - /url: admin.php?page=wc-reports
        - listitem:
          - link "Settings":
            - /url: admin.php?page=wc-settings
        - listitem:
          - link "Status":
            - /url: admin.php?page=wc-status
        - listitem:
          - link "Extensions":
            - /url: admin.php?page=wc-admin&path=/extensions
    - listitem:
      - link "Products":
        - /url: edit.php?post_type=product
      - list:
        - listitem:
          - link "All Products":
            - /url: edit.php?post_type=product
        - listitem:
          - link "Add new product":
            - /url: post-new.php?post_type=product
        - listitem:
          - link "Brands":
            - /url: edit-tags.php?taxonomy=product_brand&post_type=product
        - listitem:
          - link "Categories":
            - /url: edit-tags.php?taxonomy=product_cat&post_type=product
        - listitem:
          - link "Tags":
            - /url: edit-tags.php?taxonomy=product_tag&post_type=product
        - listitem:
          - link "Attributes":
            - /url: edit.php?post_type=product&page=product_attributes
        - listitem:
          - link "Reviews":
            - /url: edit.php?post_type=product&page=product-reviews
    - listitem:
      - link "Payments":
        - /url: admin.php?page=wc-settings&tab=checkout&from=PAYMENTS_MENU_ITEM
    - listitem:
      - link "Analytics":
        - /url: admin.php?page=wc-admin&path=/analytics/overview
      - list:
        - listitem:
          - link "Overview":
            - /url: admin.php?page=wc-admin&path=/analytics/overview
        - listitem:
          - link "Products":
            - /url: admin.php?page=wc-admin&path=/analytics/products
        - listitem:
          - link "Revenue":
            - /url: admin.php?page=wc-admin&path=/analytics/revenue
        - listitem:
          - link "Orders":
            - /url: admin.php?page=wc-admin&path=/analytics/orders
        - listitem:
          - link "Variations":
            - /url: admin.php?page=wc-admin&path=/analytics/variations
        - listitem:
          - link "Categories":
            - /url: admin.php?page=wc-admin&path=/analytics/categories
        - listitem:
          - link "Coupons":
            - /url: admin.php?page=wc-admin&path=/analytics/coupons
        - listitem:
          - link "Taxes":
            - /url: admin.php?page=wc-admin&path=/analytics/taxes
        - listitem:
          - link "Downloads":
            - /url: admin.php?page=wc-admin&path=/analytics/downloads
        - listitem:
          - link "Stock":
            - /url: admin.php?page=wc-admin&path=/analytics/stock
        - listitem:
          - link "Settings":
            - /url: admin.php?page=wc-admin&path=/analytics/settings
    - listitem:
      - link "Marketing":
        - /url: admin.php?page=wc-admin&path=/marketing
      - list:
        - listitem:
          - link "Overview":
            - /url: admin.php?page=wc-admin&path=/marketing
        - listitem:
          - link "Coupons":
            - /url: edit.php?post_type=shop_coupon
    - listitem:
      - link "Appearance":
        - /url: themes.php
      - list:
        - listitem:
          - link "Themes":
            - /url: themes.php
        - listitem:
          - link "Design":
            - /url: site-editor.php
        - listitem:
          - link "Customize":
            - /url: customize.php?return=%2Fwp-admin%2Fadmin.php%3Fpage%3Dwps-rwpr-setting%26tab%3Dwps-campaign-settings
        - listitem:
          - link "Widgets":
            - /url: widgets.php
        - listitem:
          - link "Fonts":
            - /url: font-library.php
        - listitem:
          - link "Menus":
            - /url: nav-menus.php
        - listitem:
          - link "Header":
            - /url: customize.php?return=%2Fwp-admin%2Fadmin.php%3Fpage%3Dwps-rwpr-setting%26tab%3Dwps-campaign-settings&autofocus%5Bcontrol%5D=header_image
        - listitem:
          - link "Background":
            - /url: customize.php?return=%2Fwp-admin%2Fadmin.php%3Fpage%3Dwps-rwpr-setting%26tab%3Dwps-campaign-settings&autofocus%5Bcontrol%5D=background_image
        - listitem:
          - link "Storefront":
            - /url: themes.php?page=storefront-welcome
        - listitem
        - listitem
        - listitem:
          - link "Theme File Editor":
            - /url: theme-editor.php
    - listitem:
      - link "Plugins 2":
        - /url: plugins.php
      - list:
        - listitem:
          - link "Installed Plugins":
            - /url: plugins.php
        - listitem:
          - link "Add Plugin":
            - /url: plugin-install.php
        - listitem:
          - link "Plugin File Editor":
            - /url: plugin-editor.php
    - listitem:
      - link "Users":
        - /url: users.php
      - list:
        - listitem:
          - link "All Users":
            - /url: users.php
        - listitem:
          - link "Add User":
            - /url: user-new.php
        - listitem:
          - link "Profile":
            - /url: profile.php
    - listitem:
      - link "Tools":
        - /url: tools.php
      - list:
        - listitem:
          - link "Available Tools":
            - /url: tools.php
        - listitem:
          - link "Import":
            - /url: import.php
        - listitem:
          - link "Export":
            - /url: export.php
        - listitem:
          - link "Site Health":
            - /url: site-health.php
        - listitem:
          - link "Export Personal Data":
            - /url: export-personal-data.php
        - listitem:
          - link "Erase Personal Data":
            - /url: erase-personal-data.php
        - listitem:
          - link "Plugin Check":
            - /url: tools.php?page=plugin-check
        - listitem:
          - link "Plugin Check Namer":
            - /url: tools.php?page=plugin-check-namer
        - listitem:
          - link "Cron Events":
            - /url: tools.php?page=wp-crontrol
        - listitem:
          - link "Scheduled Actions":
            - /url: tools.php?page=action-scheduler
    - listitem:
      - link "WP Swings Role Editor":
        - /url: admin.php?page=wpswings-role-permissions-editor
    - listitem:
      - link "Settings":
        - /url: options-general.php
      - list:
        - listitem:
          - link "General":
            - /url: options-general.php
        - listitem:
          - link "Connectors":
            - /url: options-connectors.php
        - listitem:
          - link "Writing":
            - /url: options-writing.php
        - listitem:
          - link "Reading":
            - /url: options-reading.php
        - listitem:
          - link "Discussion":
            - /url: options-discussion.php
        - listitem:
          - link "Media":
            - /url: options-media.php
        - listitem:
          - link "Permalinks":
            - /url: options-permalink.php
        - listitem:
          - link "Privacy":
            - /url: options-privacy.php
        - listitem:
          - link "Plugin Check":
            - /url: options-general.php?page=plugin-check-settings
        - listitem:
          - link "Cron Schedules":
            - /url: options-general.php?page=wp-crontrol-schedules
        - listitem:
          - link "Developer Dashboard":
            - /url: options-general.php?page=developer-dashboard-settings
    - listitem:
      - button "Collapse Main menu" [expanded]: Collapse Menu
- navigation "Toolbar":
  - menu:
    - group:
      - menuitem "About WordPress"
    - group:
      - menuitem "Points and Rewards plugin june update"
    - group:
      - menuitem "Live"
    - group:
      - menuitem "2 updates available"
    - group:
      - menuitem "Ctrl+K Open command palette"
    - group:
      - menuitem "0 Comments in moderation"
    - group:
      - menuitem "New"
  - menu:
    - group:
      - menuitem "Howdy, admin"
- main:
  - text: PRO ACTIVE
  - paragraph: Points and Rewards for WooCommerce Pro
  - text: v3.7.2
  - link "Overview":
    - /url: "?page=wps-rwpr-setting&nonce=0ae1fa110d&tab=overview-setting"
  - link "General":
    - /url: "?page=wps-rwpr-setting&nonce=0ae1fa110d&tab=general-setting"
  - link "Per Currency Points & Coupon Settings":
    - /url: "?page=wps-rwpr-setting&nonce=0ae1fa110d&tab=coupon-setting"
  - link "Points Table":
    - /url: "?page=wps-rwpr-setting&nonce=0ae1fa110d&tab=points-table"
  - link "Transaction Log":
    - /url: "?page=wps-rwpr-setting&nonce=0ae1fa110d&tab=transaction-log"
  - link "At-Risk Customers":
    - /url: "?page=wps-rwpr-setting&nonce=0ae1fa110d&tab=churn-customers"
  - link "Points Notification":
    - /url: "?page=wps-rwpr-setting&nonce=0ae1fa110d&tab=points-notification"
  - link "Membership":
    - /url: "?page=wps-rwpr-setting&nonce=0ae1fa110d&tab=membership"
  - button "More ▾"
  - text: Settings
  - heading "Campaigning" [level=2]
  - paragraph: Create referral and promotional campaigns to drive engagement using reward incentives.
  - link "Read Documentation":
    - /url: https://docs.wpswings.com/points-and-rewards-for-woocommerce/?utm_source=wpswings-par-doc&utm_medium=par-org-backend&utm_campaign=doc
  - main:
    - text: Campaign ▼
    - article:
      - text: Enable Campaign
      - checkbox [checked]
      - text: Turn on this setting to start the points and rewards campaign on your site.
    - article:
      - text: Select the page where you want to show the Campaign modal.
      - combobox:
        - list:
          - listitem "My account": × My account
          - listitem:
            - textbox
      - text: Campaign modal will only show on the selected pages.
    - article:
      - text: Sign Up Points
      - checkbox [checked]
      - text: Use this toggle to start the signup points campaign
    - article:
      - text: Referral Points
      - checkbox [checked]
      - text: Enable this to launch the referral points campaign.
    - article:
      - text: Birthday Points
      - checkbox [checked]
      - text: Enable this setting to show birthday points option.
    - article:
      - text: Comments Points
      - checkbox [checked]
      - text: Turn this on to activate the comments points campaign.
    - article:
      - text: First Order Points
      - checkbox [checked]
      - text: Turn this on to activate the first order points campaign.
    - article:
      - text: Gamification Points
      - checkbox [checked]
      - text: Enable this setting to show the option for Spin Wheel gameplay.
    - text: Quiz Contest ▼ Campaign Modal – Additional Data ▼ Social Share Campaign ▼
  - button "Save Changes"
  - text: Select Campaign Banner × Halloween Black Friday Happy Easter Merry Christmas Mother's Day Thanksgiving Women's Day Valentine's Day Summer Sale Flash Deal Back to School VIP Member Reveal
  - img "festive image"
  - text: Spooky deals await this Halloween Festival!
  - button "Apply"
  - img "festive image"
  - text: Halloween Festival treats you can’t miss!
  - button "Apply"
  - img "festive image"
  - text: Get your Halloween Festival savings now!
  - button "Apply"
  - img "festive image"
  - text: Frightful fun during the Halloween Festival!
  - button "Apply"
  - img "festive image"
  - text: Scare up some deals — Halloween Festival style!
  - button "Apply"
  - text: Campaign Modal A/B Testing & Scheduling ▼
  - complementary:
    - heading "Need help with this plugin?" [level=4]
    - link "Watch Video":
      - /url: https://www.youtube.com/watch?v=9BFowjkTU2Q
    - link "Documentation":
      - /url: https://docs.wpswings.com/points-and-rewards-for-woocommerce/?utm_source=wpswings-par-doc&utm_medium=par-org-backend&utm_campaign=doc
    - link "Support":
      - /url: https://wpswings.com/submit-query/
    - heading "Grow Your Store With WP Swings" [level=4]
    - paragraph: Expert solutions to boost your store's performance.
    - link "SEO Services Improve rankings & organic traffic":
      - /url: https://wpswings.com/woocommerce-services/?utm_source=wpswings-par-services&utm_medium=par-org-backend&utm_campaign=woocommerce-services
    - link "Google Ads Setup And G4 Setup Run profitable ad campaigns":
      - /url: https://wpswings.com/woocommerce-services/?utm_source=wpswings-par-services&utm_medium=par-org-backend&utm_campaign=woocommerce-services
    - link "Speed Optimization Faster store, happier customers":
      - /url: https://wpswings.com/woocommerce-services/?utm_source=wpswings-par-services&utm_medium=par-org-backend&utm_campaign=woocommerce-services
    - link "WooCommerce Development Services Custom Solution For your store needs":
      - /url: https://wpswings.com/woocommerce-services/?utm_source=wpswings-par-services&utm_medium=par-org-backend&utm_campaign=woocommerce-services
    - button "Talk to an Expert"
    - text: Services by WP Swings
    - heading "Still facing problems?" [level=4]
    - paragraph: Connect with our team for workflow and integration support.
    - link "Contact Us":
      - /url: https://wpswings.com/contact-us/?utm_source=wpswings-contact-us&utm_medium=par-org-backend&utm_campaign=contact-us
    - heading "Explore more plugins" [level=4]
    - paragraph: Discover additional plugins from the same product family.
    - link "View More Plugins":
      - /url: https://wpswings.com/woocommerce-plugins/?utm_source=wpswings-par-shop&utm_medium=par-pro-backend&utm_campaign=shop-page
- contentinfo:
  - paragraph:
    - text: Thank you for creating with
    - link "WordPress":
      - /url: https://wordpress.org/
    - text: .
  - paragraph: Version 7.0.2
```

# Test source

```ts
  1   | const { test, expect } = require('@playwright/test');
  2   | 
  3   | // Test configuration
  4   | const ADMIN_URL = '/wp-admin/admin.php?page=wps-rwpr-setting&tab=wps-campaign-settings';
  5   | const USERNAME = 'admin';
  6   | const PASSWORD = 'admin';
  7   | 
  8   | test.describe('A/B Testing for Campaign Modal', () => {
  9   | 
  10  |   // Login before all tests
  11  |   test.beforeEach(async ({ page }) => {
  12  |     await page.goto('/wp-admin');
  13  |     await page.fill('#user_login', USERNAME);
  14  |     await page.fill('#user_pass', PASSWORD);
  15  |     await page.click('#wp-submit');
  16  |     await page.waitForLoadState('networkidle');
  17  |   });
  18  | 
  19  |   test('1. Check Campaign Settings Page Loads', async ({ page }) => {
  20  |     await page.goto(ADMIN_URL);
  21  |     await expect(page.locator('h2:has-text("Campaign Settings")')).toBeVisible();
  22  |     console.log('✓ Campaign Settings page loaded successfully');
  23  |   });
  24  | 
  25  |   test('2. Verify A/B Testing Section Exists', async ({ page }) => {
  26  |     await page.goto(ADMIN_URL);
  27  | 
  28  |     // Scroll to A/B testing section
  29  |     await page.locator('text=Campaign Modal A/B Testing').scrollIntoViewIfNeeded();
  30  | 
  31  |     // Check for enable checkbox
  32  |     const enableCheckbox = page.locator('#wps_wpr_enable_ab_testing');
> 33  |     await expect(enableCheckbox).toBeVisible();
      |                                  ^ Error: expect(locator).toBeVisible() failed
  34  | 
  35  |     console.log('✓ A/B Testing section found');
  36  |   });
  37  | 
  38  |   test('3. Enable A/B Testing', async ({ page }) => {
  39  |     await page.goto(ADMIN_URL);
  40  | 
  41  |     // Scroll to A/B testing section
  42  |     await page.locator('text=Campaign Modal A/B Testing').scrollIntoViewIfNeeded();
  43  | 
  44  |     // Check the enable checkbox if not already checked
  45  |     const enableCheckbox = page.locator('#wps_wpr_enable_ab_testing');
  46  |     const isChecked = await enableCheckbox.isChecked();
  47  | 
  48  |     if (!isChecked) {
  49  |       await enableCheckbox.check();
  50  |       await page.click('input[name="wps_wpr_save_ab_testing"]');
  51  |       await page.waitForLoadState('networkidle');
  52  |       console.log('✓ A/B Testing enabled');
  53  |     } else {
  54  |       console.log('✓ A/B Testing already enabled');
  55  |     }
  56  | 
  57  |     // Verify it's checked
  58  |     await expect(enableCheckbox).toBeChecked();
  59  |   });
  60  | 
  61  |   test('4. Complete Any Existing Test', async ({ page }) => {
  62  |     await page.goto(ADMIN_URL);
  63  |     await page.locator('text=Campaign Modal A/B Testing').scrollIntoViewIfNeeded();
  64  | 
  65  |     // Check if there's an active test
  66  |     const activeTestHeading = page.locator('h3:has-text("Active A/B Test Campaign")');
  67  |     const testExists = await activeTestHeading.isVisible();
  68  | 
  69  |     if (testExists) {
  70  |       // Check if complete test form is visible
  71  |       const completeForm = page.locator('select[name="winner"]');
  72  |       if (await completeForm.isVisible()) {
  73  |         await completeForm.selectOption('A');
  74  |         await page.click('input[name="wps_wpr_complete_ab_test"]');
  75  |         await page.waitForLoadState('networkidle');
  76  |         console.log('✓ Existing test completed');
  77  |       }
  78  |     } else {
  79  |       console.log('✓ No active test to complete');
  80  |     }
  81  |   });
  82  | 
  83  |   test('5. Verify Campaign Modal is Enabled', async ({ page }) => {
  84  |     await page.goto(ADMIN_URL);
  85  | 
  86  |     // Check for campaign modal enable checkbox
  87  |     const campaignEnableCheckbox = page.locator('input[name="wps_wpr_enable_campaign_modal"]');
  88  | 
  89  |     if (await campaignEnableCheckbox.isVisible()) {
  90  |       const isChecked = await campaignEnableCheckbox.isChecked();
  91  | 
  92  |       if (!isChecked) {
  93  |         await campaignEnableCheckbox.check();
  94  |         await page.click('input[name="wps_wpr_save_campaign_settings"]');
  95  |         await page.waitForLoadState('networkidle');
  96  |         console.log('✓ Campaign Modal enabled');
  97  |       } else {
  98  |         console.log('✓ Campaign Modal already enabled');
  99  |       }
  100 |     } else {
  101 |       console.log('⚠ Campaign Modal checkbox not found - may already be enabled');
  102 |     }
  103 |   });
  104 | 
  105 |   test('6. Create New A/B Test Campaign', async ({ page }) => {
  106 |     await page.goto(ADMIN_URL);
  107 |     await page.locator('text=Campaign Modal A/B Testing').scrollIntoViewIfNeeded();
  108 | 
  109 |     // Wait a bit for section to load
  110 |     await page.waitForTimeout(1000);
  111 | 
  112 |     // Check if create form is visible
  113 |     const createHeading = page.locator('h3:has-text("Test Your Current Campaign")');
  114 |     const canCreateTest = await createHeading.isVisible();
  115 | 
  116 |     if (!canCreateTest) {
  117 |       console.log('⚠ Cannot create test - active test might exist');
  118 |       return;
  119 |     }
  120 | 
  121 |     // Fill in test details
  122 |     await page.fill('input[name="wps_wpr_test_name"]', 'Playwright Test Campaign');
  123 | 
  124 |     // Fill Variant B details (Variant A is automatic from current settings)
  125 |     await page.fill('input[name="wps_wpr_variant_b_heading"]', 'Test Heading for Variant B');
  126 | 
  127 |     // Submit the form
  128 |     await page.click('input[name="wps_wpr_create_ab_test"]');
  129 |     await page.waitForLoadState('networkidle');
  130 | 
  131 |     // Check for success message
  132 |     const successMessage = page.locator('.notice-success');
  133 |     await expect(successMessage).toBeVisible({ timeout: 5000 });
```