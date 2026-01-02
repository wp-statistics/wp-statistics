# WP Statistics v15 Email Reporting System - Implementation Plan

## Executive Summary

This document outlines the comprehensive plan for restructuring and improving the email reporting system in WP Statistics v15. The goal is to modernize the email experience, introduce new metrics, implement a drag-and-drop builder, and create a clear upgrade path from the free version to Advanced Reporting add-on.

---

## 1. Current State Analysis

### 1.1 Free Version (Current)

**Scheduling Options:**
- Daily, Weekly, Bi-weekly, Monthly
- Fixed time (8:00 AM based on timezone)

**Metrics Included:**
- Visitors (with % change)
- Views (with % change)
- Referrals (with % change)
- Published Contents (with % change)
- Top Author, Top Category, Top Content, Top Referral

**Customization:**
- Custom header/footer (HTML editor)
- Custom report content via shortcodes
- Privacy audit issues toggle

**Template:**
- Single HTML template (`layout.php`)
- Inline CSS styling
- RTL support
- Fixed layout structure

### 1.2 Advanced Reporting Add-on (Current)

**Additional Features:**
- Visual charts and graphs
- PDF/CSV export
- Custom date ranges
- Specific time scheduling
- Customizable metric selection
- Country-level reporting
- Search engine referrals breakdown
- Author analytics

---

## 2. v15 Improvements Overview

### 2.1 Architecture Changes

```
src/
├── Service/
│   └── Email/
│       ├── Builder/
│       │   ├── EmailBuilder.php              # Core builder logic
│       │   ├── BlockRegistry.php             # Available blocks registry
│       │   └── Blocks/
│       │       ├── AbstractBlock.php         # Base block class
│       │       ├── HeaderBlock.php           # Logo + title
│       │       ├── MetricsGridBlock.php      # KPI metrics grid
│       │       ├── TopItemsBlock.php         # Top content/authors/etc
│       │       ├── ChartBlock.php            # Visual charts (Pro)
│       │       ├── TableBlock.php            # Data tables (Pro)
│       │       ├── TextBlock.php             # Custom text/HTML
│       │       ├── DividerBlock.php          # Visual separator
│       │       ├── CtaBlock.php              # Call-to-action button
│       │       └── PromoBlock.php            # Add-on promotion
│       ├── Templates/
│       │   ├── Base/
│       │   │   ├── layout.php                # Main wrapper
│       │   │   └── blocks/                   # Block templates
│       │   └── Presets/
│       │       ├── default.json              # Default email layout
│       │       ├── minimal.json              # Minimal report
│       │       └── comprehensive.json        # Full report (Pro)
│       ├── Metrics/
│       │   ├── MetricRegistry.php            # Available metrics
│       │   ├── MetricInterface.php           # Metric contract
│       │   └── Providers/
│       │       ├── VisitorsMetric.php
│       │       ├── ViewsMetric.php
│       │       ├── ReferralsMetric.php
│       │       ├── BounceRateMetric.php      # New
│       │       ├── SessionDurationMetric.php  # New
│       │       ├── TopPagesMetric.php        # New
│       │       ├── TopCountriesMetric.php    # Pro
│       │       ├── SearchEnginesMetric.php   # Pro
│       │       └── ...
│       ├── Scheduler/
│       │   ├── EmailScheduler.php            # Cron management
│       │   └── ScheduleOptions.php           # Schedule configurations
│       └── Sender/
│           ├── EmailSender.php               # Send logic
│           └── Renderer.php                  # HTML rendering
```

### 2.2 Database Schema

```sql
-- New table for saved email templates/layouts
CREATE TABLE {prefix}_statistics_email_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) UNIQUE,
    layout JSON NOT NULL,                    -- Block configuration
    is_default BOOLEAN DEFAULT FALSE,
    is_system BOOLEAN DEFAULT FALSE,         -- System presets
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- New table for email schedule configurations
CREATE TABLE {prefix}_statistics_email_schedules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_id BIGINT UNSIGNED,
    name VARCHAR(255) NOT NULL,
    recipients TEXT NOT NULL,                -- JSON array of emails
    frequency ENUM('daily', 'weekly', 'biweekly', 'monthly', 'custom'),
    custom_cron VARCHAR(100),                -- For custom schedules (Pro)
    send_time TIME DEFAULT '08:00:00',
    send_day TINYINT,                        -- Day of week/month
    timezone VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    last_sent DATETIME,
    next_send DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES {prefix}_statistics_email_templates(id)
);
```

---

## 3. Feature Breakdown

### 3.1 Free Version Features

#### 3.1.1 New Metrics (v15)

| Metric | Description | Comparison |
|--------|-------------|------------|
| Visitors | Unique visitors count | vs previous period % |
| Views | Total page views | vs previous period % |
| Sessions | User sessions (new) | vs previous period % |
| Bounce Rate | Single-page visits % (new) | vs previous period |
| Avg. Session Duration | Time on site (new) | vs previous period |
| Referrals | External traffic sources | vs previous period % |
| Published Content | New posts/pages | vs previous period % |
| Top Pages | Most viewed content (top 5) | - |
| Top Referrers | Traffic sources (top 5) | - |
| Top Authors | Most prolific writers | - |
| Top Categories | Popular taxonomies | - |

#### 3.1.2 Drag & Drop Builder (Simplified)

**Available Blocks (Free):**

1. **Header Block**
   - Logo (customizable)
   - Report title
   - Date range display

2. **Metrics Grid Block**
   - 2x2 or 1x4 layout
   - Select from available free metrics
   - Show/hide comparison percentages

3. **Top Items Block**
   - Top 5 Pages
   - Top 5 Referrers
   - Top Author
   - Top Category

4. **Text Block**
   - Custom HTML/text content
   - Shortcode support

5. **Divider Block**
   - Visual separator

6. **CTA Block**
   - "View Full Dashboard" button

7. **Promo Block** (System-injected)
   - Advanced Reporting promotion
   - Shown when add-on not active

**Builder UI:**
```
┌─────────────────────────────────────────────────────────────┐
│  Email Report Builder                          [Preview] [Save] │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────────┐  ┌─────────────────────────────────────┐   │
│  │  Blocks     │  │  Canvas                             │   │
│  ├─────────────┤  │  ┌─────────────────────────────────┐│   │
│  │ □ Header    │  │  │ [Header Block]                  ││   │
│  │ □ Metrics   │  │  │ Logo + Title + Date Range       ││   │
│  │ □ Top Items │  │  └─────────────────────────────────┘│   │
│  │ □ Text      │  │  ┌─────────────────────────────────┐│   │
│  │ □ Divider   │  │  │ [Metrics Grid]                  ││   │
│  │ □ CTA       │  │  │ Visitors | Views | Sessions     ││   │
│  │ ─────────── │  │  └─────────────────────────────────┘│   │
│  │ Pro Blocks: │  │  ┌─────────────────────────────────┐│   │
│  │ 🔒 Charts   │  │  │ [Top Pages Block]               ││   │
│  │ 🔒 Tables   │  │  │ 1. /home - 1,234 views          ││   │
│  │ 🔒 Geo Map  │  │  │ 2. /about - 890 views           ││   │
│  └─────────────┘  │  └─────────────────────────────────┘│   │
│                   └─────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

#### 3.1.3 Email Template Design

**New Design Principles:**
- Clean, modern aesthetic matching v15 dashboard
- Mobile-responsive (single column on small screens)
- Dark mode support (via media queries)
- Consistent with WP Statistics branding
- Maximum width: 600px for email client compatibility

**Color Palette:**
```css
:root {
    --primary: #404BF2;       /* WP Statistics blue */
    --success: #196140;       /* Positive change */
    --danger: #D54037;        /* Negative change */
    --neutral: #A9AAAE;       /* No change */
    --background: #F7F9FA;    /* Email background */
    --card: #FFFFFF;          /* Content cards */
    --text-primary: #1E1E20;  /* Headings */
    --text-secondary: #3D3D44; /* Body text */
}
```

#### 3.1.4 Scheduling Options (Free)

| Option | Values |
|--------|--------|
| Frequency | Daily, Weekly, Bi-weekly, Monthly |
| Send Time | Fixed at 8:00 AM (site timezone) |
| Recipients | Comma-separated emails |

### 3.2 Advanced Reporting Add-on Features (Pro)

#### 3.2.1 Additional Metrics

| Metric | Description |
|--------|-------------|
| Top Countries | Visitor geography (top 10) |
| Top Cities | City-level data (top 10) |
| Search Engines | Google, Bing, etc. breakdown |
| Search Keywords | Top search terms |
| Device Breakdown | Desktop/Mobile/Tablet % |
| Browser Stats | Chrome, Safari, Firefox, etc. |
| OS Distribution | Windows, macOS, iOS, Android |
| UTM Campaign Performance | Campaign tracking |
| Conversion Tracking | Goal completions |
| Real-time Stats | Current active users |

#### 3.2.2 Additional Blocks (Pro)

1. **Chart Block**
   - Line chart (trends)
   - Bar chart (comparisons)
   - Pie chart (distributions)
   - Customizable colors

2. **Data Table Block**
   - Sortable columns
   - Configurable rows (5, 10, 20)
   - Multiple metrics per row

3. **Geo Map Block**
   - World map visualization
   - Country highlighting
   - Heat map style

4. **Comparison Block**
   - Period-over-period comparison
   - Custom date ranges

5. **AI Insights Block** (Future)
   - Automated insights
   - Anomaly detection
   - Recommendations

#### 3.2.3 Advanced Scheduling (Pro)

| Option | Values |
|--------|--------|
| Frequency | Daily, Weekly, Bi-weekly, Monthly, Custom |
| Send Time | Custom time picker |
| Send Day | Day of week/month selection |
| Custom Cron | Cron expression support |
| Multiple Schedules | Different reports for different recipients |
| Timezone | Per-schedule timezone |

#### 3.2.4 Export Options (Pro)

- PDF export (branded)
- CSV export (raw data)
- Scheduled exports
- Cloud storage integration (Google Drive, Dropbox)

---

## 4. Implementation Phases

### Phase 1: Foundation (Weeks 1-2)

**Tasks:**
1. Create new directory structure
2. Implement `EmailBuilder` core class
3. Create `BlockRegistry` and base block classes
4. Implement basic blocks (Header, Metrics, Text, Divider)
5. Design new email template base layout
6. Create database migrations

**Deliverables:**
- [ ] New email architecture classes
- [ ] Base HTML template
- [ ] Migration scripts
- [ ] Unit tests for core classes

### Phase 2: Metrics System (Weeks 3-4)

**Tasks:**
1. Create `MetricRegistry` class
2. Implement metric providers for all free metrics
3. Add new metrics (Sessions, Bounce Rate, Avg Duration)
4. Create comparison calculation logic
5. Implement caching for expensive queries

**Deliverables:**
- [ ] All free metric providers
- [ ] Metric caching system
- [ ] Comparison period logic
- [ ] API endpoints for metrics

### Phase 3: Builder UI (Weeks 5-7)

**Tasks:**
1. Create React-based drag-and-drop builder
2. Implement block configuration panels
3. Create live preview functionality
4. Build template save/load system
5. Add preset templates

**Deliverables:**
- [ ] Builder UI component
- [ ] Block configuration panels
- [ ] Preview renderer
- [ ] Template management CRUD

### Phase 4: Settings & Scheduling (Week 8)

**Tasks:**
1. Redesign notifications settings page
2. Implement new scheduler with database persistence
3. Add schedule management UI
4. Create "Send Test Email" functionality
5. Add email log/history

**Deliverables:**
- [ ] New settings page UI
- [ ] Schedule management
- [ ] Email history log
- [ ] Test email functionality

### Phase 5: Pro Features Integration (Weeks 9-10)

**Tasks:**
1. Create Pro block implementations
2. Add chart rendering system
3. Implement advanced scheduling options
4. Add PDF/CSV export
5. Create Pro-only metric providers

**Deliverables:**
- [ ] Chart blocks
- [ ] Advanced scheduling
- [ ] Export functionality
- [ ] Geo/device metrics

### Phase 6: Polish & Migration (Weeks 11-12)

**Tasks:**
1. Migration path from old settings
2. Performance optimization
3. Email client testing (Gmail, Outlook, Apple Mail)
4. Documentation
5. Beta testing

**Deliverables:**
- [ ] Migration scripts
- [ ] Email client compatibility
- [ ] User documentation
- [ ] Release notes

---

## 5. Technical Specifications

### 5.1 Block JSON Schema

```json
{
    "id": "unique-block-id",
    "type": "metrics-grid",
    "settings": {
        "layout": "2x2",
        "metrics": [
            {"id": "visitors", "showComparison": true},
            {"id": "views", "showComparison": true},
            {"id": "sessions", "showComparison": true},
            {"id": "bounce_rate", "showComparison": false}
        ],
        "style": {
            "backgroundColor": "#ffffff",
            "padding": "24px"
        }
    }
}
```

### 5.2 Template JSON Schema

```json
{
    "version": "1.0",
    "name": "My Custom Report",
    "blocks": [
        {
            "id": "header-1",
            "type": "header",
            "settings": {
                "showLogo": true,
                "logoUrl": "",
                "title": "Weekly Performance Report",
                "showDateRange": true
            }
        },
        {
            "id": "metrics-1",
            "type": "metrics-grid",
            "settings": {...}
        }
    ],
    "globalSettings": {
        "primaryColor": "#404BF2",
        "fontFamily": "system-ui",
        "maxWidth": 600
    }
}
```

### 5.3 API Endpoints

```
GET    /wp-json/wp-statistics/v2/email/templates
POST   /wp-json/wp-statistics/v2/email/templates
GET    /wp-json/wp-statistics/v2/email/templates/{id}
PUT    /wp-json/wp-statistics/v2/email/templates/{id}
DELETE /wp-json/wp-statistics/v2/email/templates/{id}

GET    /wp-json/wp-statistics/v2/email/schedules
POST   /wp-json/wp-statistics/v2/email/schedules
PUT    /wp-json/wp-statistics/v2/email/schedules/{id}
DELETE /wp-json/wp-statistics/v2/email/schedules/{id}

POST   /wp-json/wp-statistics/v2/email/send-test
POST   /wp-json/wp-statistics/v2/email/preview

GET    /wp-json/wp-statistics/v2/email/metrics
GET    /wp-json/wp-statistics/v2/email/blocks
```

### 5.4 Hooks & Filters

```php
// Filters
apply_filters('wp_statistics_email_blocks', $blocks);
apply_filters('wp_statistics_email_metrics', $metrics);
apply_filters('wp_statistics_email_template', $template, $schedule);
apply_filters('wp_statistics_email_subject', $subject, $schedule);
apply_filters('wp_statistics_email_recipients', $recipients, $schedule);
apply_filters('wp_statistics_email_rendered', $html, $template);

// Actions
do_action('wp_statistics_before_email_send', $schedule, $template);
do_action('wp_statistics_after_email_send', $schedule, $result);
do_action('wp_statistics_email_template_saved', $template);
do_action('wp_statistics_email_schedule_created', $schedule);
```

---

## 6. UI/UX Design Guidelines

### 6.1 Settings Page Layout

```
┌─────────────────────────────────────────────────────────────┐
│  Email Reports                                    [View Guide] │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │  Email Configuration                                    ││
│  │  ───────────────────────────────────────────────────────││
│  │  Recipients: [admin@example.com                       ] ││
│  │  [+ Add more recipients]                                ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │  Report Templates                            [+ Create] ││
│  │  ───────────────────────────────────────────────────────││
│  │  ┌──────────────────┐ ┌──────────────────┐              ││
│  │  │ Default Report   │ │ Weekly Summary   │              ││
│  │  │ [Edit] [Delete]  │ │ [Edit] [Delete]  │              ││
│  │  └──────────────────┘ └──────────────────┘              ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │  Schedules                                   [+ Create] ││
│  │  ───────────────────────────────────────────────────────││
│  │  ┌─────────────────────────────────────────────────────┐││
│  │  │ Weekly Report                                       │││
│  │  │ Every Monday at 8:00 AM • admin@example.com        │││
│  │  │ Next: Jan 6, 2025 • [Toggle: ON]                   │││
│  │  │ [Edit] [Send Now] [Delete]                         │││
│  │  └─────────────────────────────────────────────────────┘││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  ┌─────────────────────────────────────────────────────────┐│
│  │  📊 Want More Insights?                                 ││
│  │  ───────────────────────────────────────────────────────││
│  │  Upgrade to Advanced Reporting for:                     ││
│  │  ✓ Visual charts & graphs                              ││
│  │  ✓ Geographic data                                      ││
│  │  ✓ PDF/CSV exports                                      ││
│  │  ✓ Custom scheduling                                    ││
│  │  [Learn More →]                                         ││
│  └─────────────────────────────────────────────────────────┘│
│                                                             │
│  [Send Test Email]                              [Save Changes] │
└─────────────────────────────────────────────────────────────┘
```

### 6.2 Email Template Preview

The email should look consistent across:
- Gmail (Web & Mobile)
- Outlook (Desktop & Web)
- Apple Mail (macOS & iOS)
- Yahoo Mail
- Other major clients

**Testing Checklist:**
- [ ] Images load correctly
- [ ] Links are clickable
- [ ] Colors display properly
- [ ] Fonts fallback gracefully
- [ ] RTL languages render correctly
- [ ] Dark mode displays well
- [ ] Mobile responsive layout

---

## 7. Migration Strategy

### 7.1 From Current Settings

```php
// Migration logic
class EmailSettingsMigration {
    public function migrate() {
        $oldSettings = [
            'email_list' => Option::get('email_list'),
            'time_report' => Option::get('time_report'),
            'send_report' => Option::get('send_report'),
            'content_report' => Option::get('content_report'),
            'email_free_content_header' => Option::get('email_free_content_header'),
            'email_free_content_footer' => Option::get('email_free_content_footer'),
        ];

        // Create default template from old content
        $template = $this->createTemplateFromLegacy($oldSettings);

        // Create schedule from old settings
        $schedule = $this->createScheduleFromLegacy($oldSettings, $template->id);

        // Mark migration complete
        Option::set('email_v15_migrated', true);
    }
}
```

### 7.2 Backward Compatibility

- Keep old shortcode support in Text blocks
- Maintain existing hooks/filters
- Old email template continues to work during transition
- Deprecation notices for removed features

---

## 8. Success Metrics

### 8.1 Technical Metrics
- Email delivery rate > 99%
- Email render time < 500ms
- Builder load time < 1s
- Zero breaking changes for existing users

### 8.2 User Metrics
- Email open rate improvement
- User engagement with builder
- Upgrade conversion from free to Pro
- Support ticket reduction

---

## 9. Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| Email client compatibility | High | Extensive testing with Litmus/Email on Acid |
| Performance degradation | Medium | Implement caching, lazy loading |
| Migration failures | High | Rollback mechanism, backup old settings |
| Builder complexity | Medium | Start with simple blocks, iterate |
| Add-on integration | Medium | Clear API contracts, version compatibility |

---

## 10. Open Questions

1. **Block persistence:** Should blocks be stored in options or custom table?
2. **Real-time preview:** Server-side or client-side rendering?
3. **Multiple schedules:** Should free version support multiple schedules?
4. **Email queue:** Implement queue system for large recipient lists?
5. **Analytics:** Track email opens/clicks within WP Statistics?

---

## 11. Appendix

### A. Current File Locations

| Component | Path |
|-----------|------|
| Email Template | `/src/Service/Messaging/Templates/Emails/layout.php` |
| Mail Provider | `/src/Service/Messaging/Provider/MailProvider.php` |
| Scheduler | `/includes/class-wp-statistics-schedule.php` |
| Settings Page | `/includes/admin/templates/settings/notifications.php` |
| Performance Data | `/src/Service/Admin/WebsitePerformance/WebsitePerformanceDataProvider.php` |

### B. Related Add-on Files

The Advanced Reporting add-on should be updated to:
1. Register Pro blocks with the builder
2. Add Pro metrics to the registry
3. Enable advanced scheduling options
4. Integrate export functionality

### C. Reference Designs

- Look at similar SaaS email builders (Mailchimp, ConvertKit)
- Reference WP Statistics v15 dashboard design system
- Follow WordPress admin UI patterns