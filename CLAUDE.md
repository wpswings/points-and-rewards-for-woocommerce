# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## IMPORTANT: Plugin Scope

**ALL user prompts and instructions apply ONLY to these two plugins:**
1. **points-and-rewards-for-woocommerce** (Free version)
2. **ultimate-woocommerce-points-and-rewards** (Pro/Premium version)

Do NOT modify any other plugins in the `/wp-content/plugins/` directory, including the WooCommerce core plugin or any third-party plugins, unless explicitly instructed to do so with clear confirmation from the user.

## Project Overview

**Points and Rewards for WooCommerce** is a WordPress plugin that creates a loyalty program for WooCommerce stores. The plugin enables merchants to reward customers with points for various activities (purchases, referrals, sign-ups, social sharing) and allows customers to redeem those points for discounts or products.

This is a WordPress plugin with:
- **PHP Backend**: Standard WordPress plugin architecture
- **React Frontend Components**: For admin dashboard reporting and interactive UI
- **WooCommerce Integration**: Hooks into WooCommerce orders, cart, and checkout flows

## Development Commands

### Build and Asset Compilation

```bash
# Install dependencies
npm install

# Build production assets (React components, JS, CSS)
npm run build

# Start development mode with watch/hot-reload
npm run start

# Lint JavaScript
npm run lint:js

# Lint CSS
npm run lint:css

# Lint package.json
npm run lint:pkg-json
```

### PHP Code Quality

```bash
# Run PHP CodeSniffer to check coding standards
composer global require squizlabs/php_codesniffer wp-coding-standards/wpcs woocommerce/woocommerce-sniffs phpcompatibility/php-compatibility phpcsstandards/phpcsextra
phpcs --standard=phpcs.xml --extensions=php,html .

# Auto-fix coding standard violations
phpcbf --standard=phpcs.xml --extensions=php,html .

# Check specific file
phpcs --standard=phpcs.xml path/to/file.php
```

### Internationalization (i18n)

```bash
# Generate POT translation file
wp i18n make-pot . languages/points-and-rewards-for-woocommerce.pot \
  --exclude=node_modules,vendor,tests,public/js,admin/js,packages,build,dist

# Compile .po files to .mo
msgfmt languages/filename.po -o languages/filename.mo
```

### Local WordPress Environment

This project is in a **Local by Flywheel** environment at:
- Path: `/home/cedcoss/Local Sites/points-and-rewards-plugin-june-update/app/public/wp-content/plugins/`
- Access the local site through Local by Flywheel's interface

## Plugin Architecture

### Core Structure (WordPress Plugin Boilerplate Pattern)

The plugin follows the **WordPress Plugin Boilerplate** architecture with clear separation of concerns:

```
points-and-rewards-for-woocommerce/
├── points-rewards-for-woocommerce.php    # Main plugin file, bootstrap
├── includes/                              # Core plugin classes
│   ├── class-points-rewards-for-woocommerce.php          # Main orchestrator
│   ├── class-points-rewards-for-woocommerce-loader.php   # Hook registration system
│   ├── class-points-rewards-for-woocommerce-i18n.php     # Internationalization
│   └── class-wpswings-onboarding-helper.php              # Onboarding wizard
├── admin/                                 # Admin-facing functionality
│   ├── class-points-rewards-for-woocommerce-admin.php    # Admin hooks & logic
│   ├── partials/                         # Admin view templates
│   ├── css/, js/                         # Admin assets
│   └── images/
├── public/                                # Public-facing functionality
│   ├── class-points-rewards-for-woocommerce-public.php   # Frontend hooks & logic
│   ├── partials/                         # Frontend templates (My Account tabs)
│   ├── css/, js/                         # Frontend assets
│   └── woocommerce/                      # WooCommerce template overrides
├── emails/                                # Email notification system
│   └── class-wps-wpr-emails-notification.php
├── src/                                   # React source files
│   ├── App.js                            # Main React component (reporting charts)
│   ├── index.js                          # React entry point
│   └── store/                            # State management
├── build/                                 # Compiled React assets (generated)
└── languages/                             # Translation files (.pot, .po, .mo)
```

### Key Architectural Patterns

1. **Hook Loader Pattern**: `class-points-rewards-for-woocommerce-loader.php` centralizes all WordPress action/filter registration. Hooks are defined in admin/public classes then registered through the loader.

2. **Admin/Public Separation**:
   - `admin/class-points-rewards-for-woocommerce-admin.php` (~123KB) handles all admin panel settings, configuration, and merchant-facing features
   - `public/class-points-rewards-for-woocommerce-public.php` (~265KB) handles customer-facing features, cart/checkout integration, points redemption

3. **Template System**:
   - Admin templates in `admin/partials/` for settings pages
   - Public templates in `public/partials/` for My Account page integration (points display, logs, campaigns)
   - WooCommerce template overrides in `public/woocommerce/`

4. **Email Notifications**: Custom email system in `emails/` with templates for points earned/redeemed/expired notifications

5. **React Integration**: React components (in `src/`) compile to `build/` and are enqueued via WordPress for:
   - Admin reporting dashboard with charts (uses Recharts library)
   - User points overview tables
   - Interactive UI elements

### Hook System

The plugin extensively uses WordPress hooks:
- 99 hooks defined in main class (`includes/class-points-rewards-for-woocommerce.php`)
- Admin class registers hooks for settings pages, product meta boxes, order processing
- Public class registers hooks for cart, checkout, order completion, My Account pages

## Key Feature Areas

1. **Points Earning System**: Purchase points, referral points, signup points, order-based points, category/product-specific points
2. **Points Redemption**: Cart/checkout integration for applying points as discounts with conversion rates
3. **Membership Tiers**: Multi-level membership system with exclusive discounts
4. **Gamification**: Spin-wheel, badges, user levels, campaigns
5. **Referral System**: Unique referral links with social sharing (Facebook, Twitter, WhatsApp, Email)
6. **Email Notifications**: Points earned/redeemed/expired/membership level notifications
7. **Admin Reports**: Points logs, user points table, transaction history
8. **Integrations**: Klaviyo, WooCommerce Subscriptions, MultiVendorX, Gift Cards, Wallet System, Currency Switcher

## Coding Standards

The plugin follows:
- **WordPress Coding Standards** (WordPress-Extra)
- **WooCommerce Coding Standards**
- **PHP Compatibility**: PHP 7.4+ (checked via PHPCompatibility)
- **PHPCS Extra Rules**: Universal, Modernize, NormalizedArrays

Key exclusions in `phpcs.xml`:
- Commenting standards (Squiz.Commenting)
- File naming conventions
- WordPress whitespace rules (handled by PHPCBF)

Configuration: `phpcs.xml` (uses WordPress-Extra + WooCommerce + PHPCompatibility + PHPCSExtra rulesets)

## Database & Data Storage

- Custom user meta keys for points storage (prefixed with `wps_wpr_`)
- WooCommerce order meta for points transactions
- WordPress options for plugin settings (serialized arrays)
- Points logs stored in custom tables or post meta (verify implementation)

## HPOS Compatibility

The plugin declares compatibility with WooCommerce High-Performance Order Storage (HPOS):
```php
\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
```

Also compatible with WooCommerce Cart & Checkout Blocks.

## Testing & CI/CD

GitHub Actions workflow (`.github/workflows/main.yml`) runs on push/PR:
1. PHPCS checks with all coding standards
2. PHPCBF auto-fixes (report generated)
3. WP-CLI POT file generation
4. Translation file compilation
5. Artifact upload (reports, translations)

## Common Development Workflows

### Adding a New Points Earning Rule

1. Add settings UI in `admin/partials/` or `admin/class-points-rewards-for-woocommerce-admin.php`
2. Implement logic in `public/class-points-rewards-for-woocommerce-public.php`
3. Register hooks in the public class constructor
4. Add email notification template if needed in `emails/templates/`
5. Update points calculation methods

### Modifying Frontend Display

1. Edit templates in `public/partials/` for My Account pages
2. Modify `src/App.js` for React-based reporting components
3. Run `npm run build` to compile React changes
4. Enqueue scripts/styles in `public/class-points-rewards-for-woocommerce-public.php`

### Adding Admin Settings

1. Add settings fields in `admin/class-points-rewards-for-woocommerce-admin.php`
2. Create/modify templates in `admin/partials/settings/`
3. Register settings with WordPress Settings API or custom save handlers
4. Add corresponding public-facing logic to handle the setting

## Important Files

- **points-rewards-for-woocommerce.php:17**: Plugin version (currently 2.10.2)
- **includes/class-points-rewards-for-woocommerce.php:79**: Main plugin initialization
- **admin/class-points-rewards-for-woocommerce-admin.php**: All admin functionality (~3800 lines)
- **public/class-points-rewards-for-woocommerce-public.php**: All frontend functionality (~8000 lines)
- **package.json:8**: Build scripts using @wordpress/scripts
- **phpcs.xml**: Coding standards configuration

## Integration Notes

When integrating with third-party plugins:
- Check for plugin activation before hooking (use `class_exists()` or `function_exists()`)
- Use WooCommerce hooks for cart/order manipulation
- Follow WooCommerce template override conventions
- Test with HPOS enabled and disabled
- Verify compatibility with WooCommerce Blocks

## Jira Integration

This project is tracked on **wpswings.atlassian.net**. MCP configuration available at `~/.config/claude/mcp_settings.json` for Jira integration.
