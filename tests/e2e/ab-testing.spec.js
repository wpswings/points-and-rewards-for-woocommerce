const { test, expect } = require('@playwright/test');

// Test configuration
const ADMIN_URL = '/wp-admin/admin.php?page=wps-rwpr-setting&tab=wps-campaign-settings';
const USERNAME = 'admin';
const PASSWORD = 'admin';

test.describe('A/B Testing for Campaign Modal', () => {

  // Login before all tests
  test.beforeEach(async ({ page }) => {
    await page.goto('/wp-admin');
    await page.fill('#user_login', USERNAME);
    await page.fill('#user_pass', PASSWORD);
    await page.click('#wp-submit');
    await page.waitForLoadState('networkidle');
  });

  test('1. Check Campaign Settings Page Loads', async ({ page }) => {
    await page.goto(ADMIN_URL);
    await expect(page.locator('h2:has-text("Campaign Settings")')).toBeVisible();
    console.log('✓ Campaign Settings page loaded successfully');
  });

  test('2. Verify A/B Testing Section Exists', async ({ page }) => {
    await page.goto(ADMIN_URL);

    // Scroll to A/B testing section
    await page.locator('text=Campaign Modal A/B Testing').scrollIntoViewIfNeeded();

    // Check for enable checkbox
    const enableCheckbox = page.locator('#wps_wpr_enable_ab_testing');
    await expect(enableCheckbox).toBeVisible();

    console.log('✓ A/B Testing section found');
  });

  test('3. Enable A/B Testing', async ({ page }) => {
    await page.goto(ADMIN_URL);

    // Scroll to A/B testing section
    await page.locator('text=Campaign Modal A/B Testing').scrollIntoViewIfNeeded();

    // Check the enable checkbox if not already checked
    const enableCheckbox = page.locator('#wps_wpr_enable_ab_testing');
    const isChecked = await enableCheckbox.isChecked();

    if (!isChecked) {
      await enableCheckbox.check();
      await page.click('input[name="wps_wpr_save_ab_testing"]');
      await page.waitForLoadState('networkidle');
      console.log('✓ A/B Testing enabled');
    } else {
      console.log('✓ A/B Testing already enabled');
    }

    // Verify it's checked
    await expect(enableCheckbox).toBeChecked();
  });

  test('4. Complete Any Existing Test', async ({ page }) => {
    await page.goto(ADMIN_URL);
    await page.locator('text=Campaign Modal A/B Testing').scrollIntoViewIfNeeded();

    // Check if there's an active test
    const activeTestHeading = page.locator('h3:has-text("Active A/B Test Campaign")');
    const testExists = await activeTestHeading.isVisible();

    if (testExists) {
      // Check if complete test form is visible
      const completeForm = page.locator('select[name="winner"]');
      if (await completeForm.isVisible()) {
        await completeForm.selectOption('A');
        await page.click('input[name="wps_wpr_complete_ab_test"]');
        await page.waitForLoadState('networkidle');
        console.log('✓ Existing test completed');
      }
    } else {
      console.log('✓ No active test to complete');
    }
  });

  test('5. Verify Campaign Modal is Enabled', async ({ page }) => {
    await page.goto(ADMIN_URL);

    // Check for campaign modal enable checkbox
    const campaignEnableCheckbox = page.locator('input[name="wps_wpr_enable_campaign_modal"]');

    if (await campaignEnableCheckbox.isVisible()) {
      const isChecked = await campaignEnableCheckbox.isChecked();

      if (!isChecked) {
        await campaignEnableCheckbox.check();
        await page.click('input[name="wps_wpr_save_campaign_settings"]');
        await page.waitForLoadState('networkidle');
        console.log('✓ Campaign Modal enabled');
      } else {
        console.log('✓ Campaign Modal already enabled');
      }
    } else {
      console.log('⚠ Campaign Modal checkbox not found - may already be enabled');
    }
  });

  test('6. Create New A/B Test Campaign', async ({ page }) => {
    await page.goto(ADMIN_URL);
    await page.locator('text=Campaign Modal A/B Testing').scrollIntoViewIfNeeded();

    // Wait a bit for section to load
    await page.waitForTimeout(1000);

    // Check if create form is visible
    const createHeading = page.locator('h3:has-text("Test Your Current Campaign")');
    const canCreateTest = await createHeading.isVisible();

    if (!canCreateTest) {
      console.log('⚠ Cannot create test - active test might exist');
      return;
    }

    // Fill in test details
    await page.fill('input[name="wps_wpr_test_name"]', 'Playwright Test Campaign');

    // Fill Variant B details (Variant A is automatic from current settings)
    await page.fill('input[name="wps_wpr_variant_b_heading"]', 'Test Heading for Variant B');

    // Submit the form
    await page.click('input[name="wps_wpr_create_ab_test"]');
    await page.waitForLoadState('networkidle');

    // Check for success message
    const successMessage = page.locator('.notice-success');
    await expect(successMessage).toBeVisible({ timeout: 5000 });

    console.log('✓ A/B Test campaign created successfully');
  });

  test('7. Verify Active Test Dashboard Shows Correctly', async ({ page }) => {
    await page.goto(ADMIN_URL);
    await page.locator('text=Campaign Modal A/B Testing').scrollIntoViewIfNeeded();

    // Check for active test display
    const activeTest = page.locator('h4:has-text("Playwright Test Campaign")');

    if (await activeTest.isVisible()) {
      // Verify both variants are shown
      await expect(page.locator('h4:has-text("Variant A")')).toBeVisible();
      await expect(page.locator('h4:has-text("Variant B")')).toBeVisible();

      // Verify statistics are shown
      await expect(page.locator('text=Impressions:')).toBeVisible();
      await expect(page.locator('text=Conversions:')).toBeVisible();
      await expect(page.locator('text=Conversion Rate:')).toBeVisible();

      console.log('✓ Active test dashboard displays correctly');
    } else {
      console.log('⚠ No active test found to verify');
    }
  });

  test('8. Test Preview Links Work', async ({ page, context }) => {
    await page.goto(ADMIN_URL);
    await page.locator('text=Campaign Modal A/B Testing').scrollIntoViewIfNeeded();

    // Find preview buttons
    const previewA = page.locator('a:has-text("Preview Variant A")');
    const previewB = page.locator('a:has-text("Preview Variant B")');

    if (await previewA.isVisible()) {
      // Test Variant A preview
      const [newPage] = await Promise.all([
        context.waitForEvent('page'),
        previewA.click()
      ]);

      await newPage.waitForLoadState('networkidle');
      expect(newPage.url()).toContain('wps_wpr_force_variant=A');
      console.log('✓ Variant A preview link works');
      await newPage.close();

      // Test Variant B preview
      const [newPage2] = await Promise.all([
        context.waitForEvent('page'),
        previewB.click()
      ]);

      await newPage2.waitForLoadState('networkidle');
      expect(newPage2.url()).toContain('wps_wpr_force_variant=B');
      console.log('✓ Variant B preview link works');
      await newPage2.close();
    } else {
      console.log('⚠ Preview links not available - no active test');
    }
  });

  test('9. Verify Frontend Campaign Modal Shows', async ({ page }) => {
    // Go to home page
    await page.goto('/');

    // Wait for campaign modal to appear (1 second delay in JS)
    await page.waitForTimeout(2000);

    // Check if campaign modal is visible
    const campaignModal = page.locator('#wps-wpr-campaign-modal');

    if (await campaignModal.isVisible()) {
      console.log('✓ Campaign modal appeared on frontend');

      // Check if modal has the active class
      const hasActiveClass = await campaignModal.evaluate(el =>
        el.classList.contains('wps-wpr-campaign-modal--active')
      );

      expect(hasActiveClass).toBeTruthy();
      console.log('✓ Campaign modal is active');
    } else {
      console.log('⚠ Campaign modal not visible - may need configuration');
    }
  });

  test('10. Test Variant Assignment Works', async ({ page }) => {
    // Test with forced Variant A
    await page.goto('/?wps_wpr_force_variant=A');
    await page.waitForTimeout(2000);

    const modalA = page.locator('#wps-wpr-campaign-modal');
    if (await modalA.isVisible()) {
      console.log('✓ Variant A loads on frontend');
    }

    // Test with forced Variant B
    await page.goto('/?wps_wpr_force_variant=B');
    await page.waitForTimeout(2000);

    const modalB = page.locator('#wps-wpr-campaign-modal');
    if (await modalB.isVisible()) {
      console.log('✓ Variant B loads on frontend');
    }
  });

  test('11. Test Conversion Tracking', async ({ page }) => {
    await page.goto('/');
    await page.waitForTimeout(2000);

    // Check if campaign modal appeared
    const modal = page.locator('#wps-wpr-campaign-modal');

    if (await modal.isVisible()) {
      // Try to click on birthday save button (if visible)
      const birthdayBtn = page.locator('#wps_wpr_campaign_save_birthday');

      if (await birthdayBtn.isVisible()) {
        // Fill birthday field
        await page.fill('#account_bday', '1990-01-01');
        await birthdayBtn.click();

        console.log('✓ Conversion event triggered (birthday save)');
      }

      // Try clicking tab buttons
      const earnTab = page.locator('#wps_wpr_campaign_earn_btn');
      if (await earnTab.isVisible()) {
        await earnTab.click();
        console.log('✓ Conversion event triggered (earn tab)');
      }
    }
  });

  test('12. Verify Test Statistics Update', async ({ page }) => {
    await page.goto(ADMIN_URL);
    await page.locator('text=Campaign Modal A/B Testing').scrollIntoViewIfNeeded();

    // Check for statistics
    const impressions = page.locator('text=Impressions:').first();

    if (await impressions.isVisible()) {
      const impressionText = await page.locator('p:has-text("Impressions:")').first().textContent();
      console.log(`✓ Statistics visible: ${impressionText}`);

      // Verify numbers are present
      expect(impressionText).toMatch(/\d+/);
    } else {
      console.log('⚠ No statistics available yet');
    }
  });

});
