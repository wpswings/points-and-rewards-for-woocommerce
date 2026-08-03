# Campaign Template Library & Enhancement Plan

**Version:** 2.10.3+
**Feature Launch:** v2.9.0 (Template Library), v2.10.x (A/B Testing, Scheduling, Analytics)
**Status:** Implementation in progress
**Last Updated:** 2026-07-29

---

## Executive Summary

This document outlines the implementation plan for expanding the Campaign template library and adding Pro-tier features including A/B testing, campaign scheduling, and enhanced analytics. These enhancements are designed to:

1. Reduce campaign setup time from 30 minutes to under 5 minutes
2. Increase free-to-Pro conversion through A/B testing upsell
3. Provide data-driven campaign optimization
4. Offer seasonal templates that drive engagement

---

## Current Implementation Analysis

### Existing Campaign System

**Database Storage:**
- Option: `wps_wpr_campaign_settings` (serialized array)
- Key Fields:
  - `wps_wpr_enable_campaign_settings` - Master toggle
  - `wps_wpr_enter_campaign_heading` - Modal heading text
  - `wps_wpr_enter_campaign_image_url` - Banner image URL
  - `wps_wpr_campaign_color_one` - Primary color
  - `wps_wpr_campaign_color_two` - Secondary color
  - `wps_wpr_select_page_for_campaign` - Page visibility array
  - Quiz arrays: `wps_wpr_quiz_question[]`, `wps_wpr_quiz_option_*[]`, `wps_wpr_quiz_answer[]`, `wps_wpr_quiz_rewards_points[]`
  - Social share arrays: `wps_wpr_social_share_campaign_label[]`, `wps_wpr_social_share_url[]`, `wps_wpr_social_share_points[]`

**Existing Templates (Images in `/admin/camp-images/`):**
- **Christmas:** Chr1-Chr5.webp
- **Mother's Day:** MD1-MD5.webp
- **Thanksgiving:** TG1-TG5.webp
- **Women's Day:** Wo1-Wo5.webp
- **Valentine's Day:** Va1-Va5.webp
- **Black Friday:** bf1-bf5.webp
- **Easter:** eas1-eas5.webp
- **Halloween:** hal1-hal5.webp

**Key Files:**
- Admin settings: `admin/partials/templates/wps-wpr-campaign-settings.php` (28,706 tokens)
- Frontend modal: `public/partials/wps-wpr-points-campaign-template.php`
- Public class: `public/class-points-rewards-for-woocommerce-public.php`

**Current Capabilities:**
- ✅ Single campaign modal per site
- ✅ Manual image URL entry
- ✅ Custom colors
- ✅ Multiple quiz questions (already supported via arrays)
- ✅ Social share campaigns
- ✅ Earn/Referral tabs
- ✅ Birthday, signup, referral, first order, gamification campaigns
- ❌ No template selection UI
- ❌ No A/B testing
- ❌ No campaign scheduling
- ❌ No analytics tracking

---

## Feature 1: New Campaign Templates

### Objective
Add 4 new seasonal template categories with 5 variants each (20 total templates) to expand the existing 8 categories (40 templates).

### New Templates to Create

#### 1. Summer Sale Templates (SS1-SS5.webp)
- **SS1:** Warm orange/yellow (#FF6B35, #F7931E) - "Hot Summer Deals — Cool Down with Huge Savings!"
- **SS2:** Cool blue (#00B4D8, #90E0EF) - "Beat the Heat with Unbeatable Summer Offers!"
- **SS3:** Vibrant orange (#FFB703, #FB8500) - "Sizzling Summer Savings Start Now!"
- **SS4:** Tropical teal (#06FFA5, #00D9FF) - "Make Waves with Our Summer Sale — Dive In!"
- **SS5:** Bold pink/yellow (#FF006E, #FFBE0B) - "Sun's Out, Deals Out — Shop the Summer Sale!"

#### 2. Flash Deal Templates (FD1-FD5.webp)
- **FD1:** Electric red/orange (#FF0054, #FF4D00) - "Lightning Deals — Grab Them Before They're Gone!"
- **FD2:** Golden yellow (#FFD60A, #FFC300) - "Flash Sale Alert — Limited Time Only!"
- **FD3:** Purple/pink (#7209B7, #F72585) - "Act Fast — Flash Deals Disappear in Hours!"
- **FD4:** Cyan blue (#00F5FF, #00B4D8) - "Blink and You'll Miss It — Flash Sale Now!"
- **FD5:** Magenta gradient (#F72585, #B5179E) - "Don't Wait — Flash Deals End Tonight!"

#### 3. Back to School Templates (BTS1-BTS5.webp)
- **BTS1:** Royal blue (#4361EE, #3A0CA3) - "Back to School Savings — Get Ready to Ace the Year!"
- **BTS2:** Teal/orange (#2EC4B6, #FF9F1C) - "Smart Savings for Smart Students — Shop Now!"
- **BTS3:** Bright cyan (#06FFA5, #4CC9F0) - "Gear Up for Success — Back to School Deals Inside!"
- **BTS4:** Purple/blue (#6A4C93, #1982C4) - "Hit the Books — Not Your Budget!"
- **BTS5:** Warm orange/red (#F77F00, #D62828) - "School Essentials at Unbeatable Prices!"

#### 4. VIP Member Reveal Templates (VIP1-VIP5.webp)
- **VIP1:** Luxury gold (#D4AF37, #C9A227) - "Welcome to the VIP Club — Exclusive Perks Await!"
- **VIP2:** Royal purple (#9D4EDD, #7209B7) - "You're VIP Now — Unlock Premium Rewards!"
- **VIP3:** Navy/gold (#14213D, #FCA311) - "Exclusive Access Granted — VIP Benefits Inside!"
- **VIP4:** Deep red (#6A040F, #DC2F02) - "VIP Treatment — Because You Deserve the Best!"
- **VIP5:** Premium blue (#03045E, #0077B6) - "Join the Elite — VIP Membership Activated!"

### Template Specifications
- **Format:** WebP (for performance)
- **Dimensions:** 800x400px (2:1 aspect ratio)
- **File Size:** <50KB per image
- **Storage:** `/admin/camp-images/`
- **Naming Convention:** `{CategoryPrefix}{Number}.webp` (e.g., SS1.webp, FD2.webp)

### Template Library UI Implementation

#### Admin Interface Changes

**New Section in Campaign Settings:**
```
Section: "Campaign Template Library"
Location: admin/partials/templates/wps-wpr-campaign-settings.php (after "Campaign Modal – Additional Data")
```

**Template Selection Modal:**
- Trigger: "Browse Templates" button next to "Enter Campaign Image URL" field
- Modal structure:
  - Category tabs (All, Summer Sale, Flash Deal, Back to School, VIP, Christmas, Easter, etc.)
  - Grid layout (3 columns)
  - Each template card shows:
    - Thumbnail image
    - Template name
    - "Preview" button (lightbox)
    - "Select" button (auto-fills URL + heading + colors)
  - Search/filter functionality

**Database Schema Addition:**
```php
// New option for template metadata
$wps_wpr_campaign_templates = array(
    'summer_sale' => array(
        'label' => 'Summer Sale',
        'templates' => array(
            'SS1' => array(
                'file' => 'SS1.webp',
                'heading' => 'Hot Summer Deals — Cool Down with Huge Savings!',
                'color_primary' => '#FF6B35',
                'color_secondary' => '#F7931E',
            ),
            // ... SS2-SS5
        ),
    ),
    // ... other categories
);
```

### Implementation Steps

1. **Create Template Metadata System**
   - File: `admin/includes/class-wps-wpr-campaign-templates.php`
   - Methods:
     - `get_all_templates()` - Returns array of all template metadata
     - `get_template_categories()` - Returns category list
     - `get_templates_by_category($category)` - Filtered templates
     - `get_template_url($template_id)` - Returns full URL to image

2. **Add Template Selection Modal**
   - Enqueue modal JS/CSS in admin
   - AJAX endpoint: `wps_wpr_get_campaign_templates`
   - Modal HTML structure with Vue.js or vanilla JS for reactivity

3. **Auto-Fill Functionality**
   - When template selected:
     - Set `wps_wpr_enter_campaign_image_url` field
     - Set `wps_wpr_enter_campaign_heading` field
     - Set `wps_wpr_campaign_color_one` and `wps_wpr_campaign_color_two` fields
     - Show preview in settings page

4. **Create Image Assets**
   - Design 20 new template images (outsource or use Canva/Figma)
   - Optimize with WebP compression
   - Upload to `/admin/camp-images/`

---

## Feature 2: Multiple Quiz Questions Per Campaign (Pro)

### Current Status
✅ **Already Implemented!** The quiz system uses arrays:
- `wps_wpr_quiz_question[]`
- `wps_wpr_quiz_option_one[]` through `wps_wpr_quiz_option_four[]`
- `wps_wpr_quiz_answer[]`
- `wps_wpr_quiz_rewards_points[]`

### Enhancement Needed
The free version currently allows unlimited quiz questions. For Pro differentiation:

**Free Version Limitation:**
- Restrict to 1 quiz question maximum
- Show "Upgrade to Pro" notice when trying to add 2nd question

**Pro Version:**
- Unlimited quiz questions
- Drag-and-drop question reordering
- Bulk import/export quiz questions (CSV)

### Implementation
1. Add license check in `wps_wpr_add_quiz` button handler
2. Display upgrade notice via `$upgrade_link` variable
3. Pro: Add jQuery UI Sortable for drag-and-drop reordering

---

## Feature 3: A/B Testing Mode (Pro)

### Objective
Allow Pro users to create two campaign variants and split traffic 50/50 to determine which performs better.

### Architecture

#### Database Schema
```php
// New option: wps_wpr_campaign_ab_tests
array(
    'enabled' => 'yes', // yes/no
    'variant_a' => array(
        'heading' => 'Points and Rewards Program',
        'image_url' => 'http://example.com/image-a.webp',
        'color_one' => '#a13a93',
        'color_two' => '#ffbb21',
    ),
    'variant_b' => array(
        'heading' => 'Unlock Exclusive Rewards!',
        'image_url' => 'http://example.com/image-b.webp',
        'color_one' => '#FF6B35',
        'color_two' => '#F7931E',
    ),
    'traffic_split' => 50, // Percentage for variant A (B gets remainder)
);

// New user meta for tracking variant assignment
// Key: wps_wpr_campaign_variant_{campaign_id}
// Value: 'a' or 'b'

// New analytics table (see Feature 4)
```

#### Admin UI

**New Section: "A/B Testing (Pro)"**
- Location: After "Campaign Modal – Additional Data"
- Fields:
  - Enable A/B Testing (checkbox with Pro badge)
  - Variant A Settings (inherit from main campaign settings OR customize)
  - Variant B Settings (separate heading, image, colors)
  - Traffic Split slider (default 50/50, allow 30/70, 25/75, etc.)
  - Test Duration (start/end dates - ties into scheduling)
  - "View Results" button (opens analytics modal)

#### Frontend Logic

**Variant Assignment (in `public/class-points-rewards-for-woocommerce-public.php`):**
```php
public function wps_wpr_assign_campaign_variant( $user_id ) {
    $ab_settings = get_option( 'wps_wpr_campaign_ab_tests', array() );

    if ( empty( $ab_settings['enabled'] ) || 'yes' !== $ab_settings['enabled'] ) {
        return 'default';
    }

    // Check if user already assigned
    $assigned_variant = get_user_meta( $user_id, 'wps_wpr_campaign_variant', true );
    if ( ! empty( $assigned_variant ) ) {
        return $assigned_variant;
    }

    // Assign based on traffic split
    $split = ! empty( $ab_settings['traffic_split'] ) ? intval( $ab_settings['traffic_split'] ) : 50;
    $random = mt_rand( 1, 100 );
    $variant = ( $random <= $split ) ? 'a' : 'b';

    update_user_meta( $user_id, 'wps_wpr_campaign_variant', $variant );

    // Track assignment in analytics
    $this->wps_wpr_track_campaign_event( 'assigned', $variant );

    return $variant;
}
```

**Modal Rendering:**
```php
// In wps-wpr-points-campaign-template.php
$variant = $this->wps_wpr_assign_campaign_variant( $user_id );
$settings = ( 'b' === $variant ) ? $ab_settings['variant_b'] : $ab_settings['variant_a'];

// Use $settings for heading, image, colors
```

#### Implementation Steps
1. Create admin UI for A/B test configuration
2. Add variant assignment logic on modal load
3. Track variant in user meta
4. Render appropriate variant in frontend modal
5. Integrate with analytics system (Feature 4)

---

## Feature 4: Campaign Scheduling

### Objective
Allow merchants to schedule campaigns to start and end automatically at specific dates/times.

### Database Schema
```php
// Add to wps_wpr_campaign_settings
'wps_wpr_campaign_schedule' => array(
    'enabled' => 'yes',
    'start_datetime' => '2026-08-01 00:00:00', // UTC
    'end_datetime' => '2026-08-31 23:59:59',   // UTC
    'timezone' => 'America/New_York',          // WordPress timezone
),
```

### Admin UI

**New Section: "Campaign Scheduling"**
- Enable Scheduling (checkbox)
- Start Date & Time (datetime-local input)
- End Date & Time (datetime-local input)
- Timezone display (read from WordPress settings)
- "Clear Dates" button
- Status indicator: "Scheduled", "Active", "Ended"

### Frontend Logic

**Visibility Check (in `public/class-points-rewards-for-woocommerce-public.php`):**
```php
public function wps_wpr_is_campaign_active() {
    $settings = get_option( 'wps_wpr_campaign_settings', array() );
    $schedule = ! empty( $settings['wps_wpr_campaign_schedule'] ) ? $settings['wps_wpr_campaign_schedule'] : array();

    // If scheduling disabled, campaign is always active (if master toggle is on)
    if ( empty( $schedule['enabled'] ) || 'yes' !== $schedule['enabled'] ) {
        return true;
    }

    // Get current time in site timezone
    $timezone = new DateTimeZone( wp_timezone_string() );
    $now = new DateTime( 'now', $timezone );

    // Check start date
    if ( ! empty( $schedule['start_datetime'] ) ) {
        $start = new DateTime( $schedule['start_datetime'], $timezone );
        if ( $now < $start ) {
            return false; // Not started yet
        }
    }

    // Check end date
    if ( ! empty( $schedule['end_datetime'] ) ) {
        $end = new DateTime( $schedule['end_datetime'], $timezone );
        if ( $now > $end ) {
            return false; // Already ended
        }
    }

    return true; // Within scheduled window
}
```

**Integration with Modal Display:**
```php
// In wps-wpr-points-campaign-template.php
if ( ! $this->wps_wpr_is_campaign_active() ) {
    return; // Don't render modal
}
```

### WP-Cron Integration (Future Enhancement)
- Schedule email notifications: "Your campaign starts in 1 day"
- Auto-disable campaign after end date
- Clear user variant assignments after test ends

---

## Feature 5: Enhanced Analytics Tab

### Objective
Provide data-driven insights into campaign performance with metrics:
- **Impressions:** Modal shown to user
- **Opens:** User interacted with modal (clicked tab, scrolled)
- **CTR:** Percentage of impressions that led to opens
- **Points Awarded:** Total points distributed via campaign
- **Conversion Rate (A/B):** Which variant performs better

### Database Schema

**New Custom Table: `wp_wps_wpr_campaign_analytics`**
```sql
CREATE TABLE wp_wps_wpr_campaign_analytics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    event_type VARCHAR(20) NOT NULL, -- 'impression', 'open', 'click', 'conversion', 'points_awarded'
    campaign_variant VARCHAR(10) DEFAULT 'default', -- 'default', 'a', 'b'
    campaign_id INT UNSIGNED DEFAULT 0, -- For future multi-campaign support
    event_value INT DEFAULT 0, -- Points awarded, etc.
    event_date DATETIME NOT NULL,
    event_meta TEXT, -- JSON for additional data
    INDEX idx_user (user_id),
    INDEX idx_event_type (event_type),
    INDEX idx_date (event_date),
    INDEX idx_variant (campaign_variant)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Tracking Implementation

#### JavaScript Event Tracking (`public/js/points-and-rewards-for-woocommerce-public.js`)
```javascript
// Track impression
jQuery(document).ready(function($) {
    if ($('#wps-wpr-campaign-modal').length > 0) {
        wps_wpr_track_event('impression');
    }
});

// Track open
$('.wps-wpr-hlw_container').on('click', function() {
    wps_wpr_track_event('open');
});

// AJAX tracking function
function wps_wpr_track_event(event_type, event_value = 0, event_meta = {}) {
    $.ajax({
        url: wps_wpr_public_param.ajax_url,
        type: 'POST',
        data: {
            action: 'wps_wpr_track_campaign_event',
            security: wps_wpr_public_param.nonce,
            event_type: event_type,
            event_value: event_value,
            event_meta: JSON.stringify(event_meta),
        },
    });
}
```

#### PHP Event Handler
```php
// In public/class-points-rewards-for-woocommerce-public.php
public function wps_wpr_track_campaign_event_ajax() {
    check_ajax_referer( 'wps-wpr-nonce', 'security' );

    $event_type = isset( $_POST['event_type'] ) ? sanitize_text_field( $_POST['event_type'] ) : '';
    $event_value = isset( $_POST['event_value'] ) ? intval( $_POST['event_value'] ) : 0;
    $event_meta = isset( $_POST['event_meta'] ) ? sanitize_text_field( $_POST['event_meta'] ) : '{}';

    $this->wps_wpr_track_campaign_event( $event_type, '', $event_value, $event_meta );
    wp_send_json_success();
}

public function wps_wpr_track_campaign_event( $event_type, $variant = '', $event_value = 0, $event_meta = '{}' ) {
    global $wpdb;

    $user_id = get_current_user_id();
    if ( empty( $variant ) ) {
        $variant = get_user_meta( $user_id, 'wps_wpr_campaign_variant', true );
        $variant = ! empty( $variant ) ? $variant : 'default';
    }

    $table_name = $wpdb->prefix . 'wps_wpr_campaign_analytics';

    $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'event_type' => $event_type,
            'campaign_variant' => $variant,
            'event_value' => $event_value,
            'event_date' => current_time( 'mysql' ),
            'event_meta' => $event_meta,
        ),
        array( '%d', '%s', '%s', '%d', '%s', '%s' )
    );
}
```

### Analytics Dashboard

**New Admin Tab: "Campaign Analytics"**
- Location: New top-level tab in Points & Rewards settings
- Layout:
  - Date range selector (Last 7 days, Last 30 days, Custom)
  - Key metrics cards:
    - Total Impressions
    - Total Opens
    - Overall CTR
    - Total Points Awarded
  - A/B Test Comparison (if enabled):
    - Variant A vs B metrics side-by-side
    - Winner indicator (statistical significance)
  - Campaign activity timeline chart (line graph)
  - Top performing campaigns table

**Implementation:**
- Use existing React infrastructure (`src/App.js`)
- Create new component: `CampaignAnalytics.js`
- AJAX endpoint: `wps_wpr_get_campaign_analytics`
- Use Recharts library for visualizations

### Analytics Queries

```php
// Get campaign metrics
public function wps_wpr_get_campaign_metrics( $start_date, $end_date, $variant = null ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wps_wpr_campaign_analytics';

    $where = $wpdb->prepare(
        "WHERE event_date BETWEEN %s AND %s",
        $start_date,
        $end_date
    );

    if ( $variant ) {
        $where .= $wpdb->prepare( " AND campaign_variant = %s", $variant );
    }

    $metrics = array(
        'impressions' => $wpdb->get_var( "SELECT COUNT(*) FROM $table_name $where AND event_type = 'impression'" ),
        'opens' => $wpdb->get_var( "SELECT COUNT(*) FROM $table_name $where AND event_type = 'open'" ),
        'points_awarded' => $wpdb->get_var( "SELECT SUM(event_value) FROM $table_name $where AND event_type = 'points_awarded'" ),
    );

    $metrics['ctr'] = ( $metrics['impressions'] > 0 )
        ? round( ( $metrics['opens'] / $metrics['impressions'] ) * 100, 2 )
        : 0;

    return $metrics;
}
```

---

## Implementation Roadmap

### Phase 1: Template Library (Week 1-2)
- [x] Create NEW_TEMPLATES_README.md specification (DONE)
- [ ] Design 20 new template images (SS1-SS5, FD1-FD5, BTS1-BTS5, VIP1-VIP5)
- [ ] Create template metadata class
- [ ] Build template selection modal UI
- [ ] Implement auto-fill functionality
- [ ] Test template library on staging

### Phase 2: A/B Testing (Week 3-4)
- [ ] Create A/B test admin UI
- [ ] Implement variant assignment logic
- [ ] Add user meta tracking
- [ ] Create analytics table
- [ ] Test traffic split functionality
- [ ] QA: Verify variant persistence

### Phase 3: Campaign Scheduling (Week 5)
- [ ] Add scheduling fields to admin
- [ ] Implement visibility check logic
- [ ] Add timezone handling
- [ ] Test scheduled start/end behavior
- [ ] Add status indicators

### Phase 4: Enhanced Analytics (Week 6-7)
- [ ] Create analytics database table
- [ ] Implement JavaScript event tracking
- [ ] Build PHP tracking handlers
- [ ] Create React analytics dashboard
- [ ] Add date range filtering
- [ ] Implement A/B comparison view
- [ ] Add export functionality (CSV)

### Phase 5: Testing & Launch (Week 8)
- [ ] Full regression testing
- [ ] Performance testing (analytics table queries)
- [ ] PHPCS compliance
- [ ] Security audit (nonce checks, sanitization)
- [ ] Translation file updates (.pot)
- [ ] Documentation updates
- [ ] Release v2.10.3

---

## Technical Specifications

### File Structure
```
points-and-rewards-for-woocommerce/
├── admin/
│   ├── camp-images/
│   │   ├── (existing templates)
│   │   ├── SS1.webp ... SS5.webp
│   │   ├── FD1.webp ... FD5.webp
│   │   ├── BTS1.webp ... BTS5.webp
│   │   └── VIP1.webp ... VIP5.webp
│   ├── includes/
│   │   └── class-wps-wpr-campaign-templates.php (NEW)
│   ├── partials/
│   │   ├── templates/
│   │   │   └── wps-wpr-campaign-settings.php (MODIFY)
│   │   └── wps-wpr-campaign-analytics-tab.php (NEW)
│   ├── js/
│   │   └── wps-wpr-campaign-admin.js (NEW)
│   └── css/
│       └── wps-wpr-campaign-admin.css (NEW)
├── public/
│   ├── js/
│   │   └── points-and-rewards-for-woocommerce-public.js (MODIFY - add analytics tracking)
│   └── partials/
│       └── wps-wpr-points-campaign-template.php (MODIFY - add variant rendering)
├── src/
│   ├── components/
│   │   └── CampaignAnalytics.js (NEW)
│   └── App.js (MODIFY - add analytics route)
└── includes/
    └── class-points-rewards-for-woocommerce.php (MODIFY - add hooks)
```

### Database Changes

**New Options:**
- `wps_wpr_campaign_ab_tests` (array)
- `wps_wpr_campaign_templates_metadata` (array, cached)

**Modified Options:**
- `wps_wpr_campaign_settings` (add `wps_wpr_campaign_schedule` key)

**New User Meta:**
- `wps_wpr_campaign_variant` (string: 'a' or 'b')

**New Table:**
- `{prefix}_wps_wpr_campaign_analytics`

### Hooks & Filters

**New Actions:**
```php
do_action( 'wps_wpr_campaign_template_selected', $template_id, $template_data );
do_action( 'wps_wpr_campaign_variant_assigned', $user_id, $variant );
do_action( 'wps_wpr_campaign_event_tracked', $event_type, $user_id, $variant );
do_action( 'wps_wpr_campaign_ab_test_started', $test_id );
do_action( 'wps_wpr_campaign_ab_test_ended', $test_id, $results );
```

**New Filters:**
```php
apply_filters( 'wps_wpr_campaign_templates', $templates );
apply_filters( 'wps_wpr_campaign_variant_assignment', $variant, $user_id );
apply_filters( 'wps_wpr_campaign_is_active', $is_active, $schedule_settings );
apply_filters( 'wps_wpr_campaign_analytics_metrics', $metrics, $date_range );
```

---

## Free vs Pro Differentiation

| Feature | Free | Pro |
|---------|------|-----|
| Campaign Templates | ✅ All 60 templates | ✅ All 60 templates |
| Template Library UI | ✅ Browse & select | ✅ Browse & select |
| Custom Colors | ✅ 2 colors | ✅ Unlimited |
| Quiz Questions | ❌ 1 question max | ✅ Unlimited |
| A/B Testing | ❌ Upgrade notice | ✅ Full A/B testing |
| Campaign Scheduling | ❌ Upgrade notice | ✅ Start/end dates |
| Analytics | ✅ Basic (impressions, opens) | ✅ Full (CTR, A/B comparison, export) |
| Multi-Campaign Support | ❌ 1 campaign | ✅ Unlimited (future) |

---

## Success Metrics

### User Experience
- **Setup Time:** Reduce from 30 minutes to <5 minutes
- **Template Usage:** 70%+ of merchants use templates vs. custom images
- **A/B Test Adoption:** 30%+ of Pro users run A/B tests within 30 days

### Business Impact
- **Free-to-Pro Conversion:** 15% increase attributed to A/B testing upsell
- **Customer Engagement:** 25% increase in campaign modal opens
- **Support Tickets:** 20% reduction in campaign setup questions

### Technical Performance
- **Analytics Query Time:** <200ms for 90-day range
- **Modal Load Time:** <100ms additional for variant logic
- **Database Growth:** <1MB per 10,000 events

---

## Security Considerations

1. **Nonce Verification:** All AJAX requests require `wp_verify_nonce()`
2. **Capability Checks:** `manage_woocommerce` required for analytics access
3. **Input Sanitization:**
   - `sanitize_text_field()` for all text inputs
   - `esc_url_raw()` for image URLs
   - `intval()` for numeric values
   - `wp_kses_post()` for rich text
4. **SQL Injection Prevention:** Use `$wpdb->prepare()` for all queries
5. **XSS Prevention:** `esc_html()`, `esc_attr()`, `esc_url()` on output
6. **File Upload Security:** Restrict template uploads to admins only
7. **Rate Limiting:** Limit analytics tracking to 1 event per user per 5 seconds

---

## Backward Compatibility

- All new features are opt-in (disabled by default)
- Existing campaign settings remain unchanged
- Database migration handled via activation hook
- Fallback to default variant if A/B test data corrupted
- Analytics table created only if not exists

---

## Testing Checklist

### Unit Tests
- [ ] Template metadata retrieval
- [ ] Variant assignment randomization
- [ ] Schedule visibility logic
- [ ] Analytics metric calculations

### Integration Tests
- [ ] Template selection updates all fields correctly
- [ ] Variant assignment persists across sessions
- [ ] Scheduled campaigns show/hide at correct times
- [ ] Analytics events tracked accurately

### User Acceptance Tests
- [ ] Merchant can browse templates and select one
- [ ] Merchant can create A/B test with 2 variants
- [ ] Customer sees consistent variant across sessions
- [ ] Analytics dashboard shows accurate data

### Performance Tests
- [ ] 10,000 analytics records query <200ms
- [ ] Modal load time increase <100ms
- [ ] Template library loads <500ms

---

## Migration Notes

### From Previous Versions
- v2.9.0 → v2.10.x: Add analytics table, add scheduling fields
- Preserve existing campaign settings
- Auto-migrate single quiz to array format (already done)

### Database Migration Hook
```php
public function wps_wpr_upgrade_campaign_system() {
    $current_version = get_option( 'wps_wpr_campaign_version', '1.0.0' );

    if ( version_compare( $current_version, '2.10.0', '<' ) ) {
        // Create analytics table
        $this->wps_wpr_create_analytics_table();

        // Add default schedule settings
        $settings = get_option( 'wps_wpr_campaign_settings', array() );
        if ( ! isset( $settings['wps_wpr_campaign_schedule'] ) ) {
            $settings['wps_wpr_campaign_schedule'] = array(
                'enabled' => 'no',
            );
            update_option( 'wps_wpr_campaign_settings', $settings );
        }

        update_option( 'wps_wpr_campaign_version', '2.10.0' );
    }
}
```

---

## Support & Documentation

### User Documentation
- Knowledge base article: "Using Campaign Templates"
- Video tutorial: "Setting Up A/B Tests"
- Analytics dashboard guide
- Template customization guide

### Developer Documentation
- Hook reference
- Filter usage examples
- Custom template creation guide
- Analytics API documentation

---

## Future Enhancements (Post-Launch)

1. **Multi-Campaign Support:** Run different campaigns on different pages
2. **Advanced Scheduling:** Recurring campaigns (e.g., every Monday)
3. **Conditional Logic:** Show campaigns based on user segments, cart value, etc.
4. **Email Integration:** Send campaign results via email
5. **Template Marketplace:** Allow third-party template submissions
6. **AI-Powered Optimization:** Auto-select winning variant after statistical significance
7. **Mobile-First Templates:** Responsive template variants
8. **Integration with Email Marketing:** Sync campaign data with Klaviyo, Mailchimp
9. **Gamification Enhancements:** Leaderboard campaigns, milestone campaigns
10. **REST API Endpoints:** Allow external tools to trigger campaigns

---

## Notes & Decisions

- **Decision (2026-07-29):** Quiz questions already support multiple entries via arrays. Pro differentiation will be enforced via UI limitations in free version.
- **Decision (2026-07-29):** A/B testing will use client-side assignment (cached in user meta) rather than server-side randomization on every page load for performance.
- **Decision (2026-07-29):** Analytics table will use custom table instead of post meta for better query performance at scale.
- **Decision (2026-07-29):** Template images will be generated using Canva Pro to ensure consistency and quality.

---

## References

- Campaign Settings File: `admin/partials/templates/wps-wpr-campaign-settings.php`
- Frontend Template: `public/partials/wps-wpr-points-campaign-template.php`
- Image Specifications: `admin/camp-images/NEW_TEMPLATES_README.md`
- CLAUDE.md: Project overview and development guidelines

---

**Document Owner:** Development Team
**Approval Required:** Product Manager, Lead Developer
**Next Review:** After Phase 1 completion
