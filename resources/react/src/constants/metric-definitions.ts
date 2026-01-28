/**
 * Shared Metric Definitions
 *
 * Single source of truth for metric configurations used across report pages.
 * This eliminates duplication and ensures consistent naming.
 */

import { __ } from '@wordpress/i18n'

import type { MetricConfig } from '@/contexts/page-options-context'

/**
 * Shared metric definitions - single source of truth.
 * Use the `pickMetrics` helper to select metrics for each page.
 */
export const SHARED_METRICS = {
  // Core traffic metrics
  visitors: { id: 'visitors', label: __('Visitors', 'wp-statistics'), defaultVisible: true },
  views: { id: 'views', label: __('Views', 'wp-statistics'), defaultVisible: true },

  // Engagement metrics
  bounceRate: { id: 'bounce-rate', label: __('Bounce Rate', 'wp-statistics'), defaultVisible: true },
  avgTimeOnPage: { id: 'avg-time-on-page', label: __('Avg. Time on Page', 'wp-statistics'), defaultVisible: true },

  // Content metrics
  contents: { id: 'contents', label: __('Contents', 'wp-statistics'), defaultVisible: true },
  publishedContent: { id: 'published-content', label: __('Published Content', 'wp-statistics'), defaultVisible: true },
  comments: { id: 'comments', label: __('Comments', 'wp-statistics'), defaultVisible: true },

  // Session metrics
  sessionDuration: { id: 'session-duration', label: __('Session Duration', 'wp-statistics'), defaultVisible: true },
  viewsPerSession: { id: 'views-per-session', label: __('Views/Session', 'wp-statistics'), defaultVisible: true },

  // Content-specific computed metrics
  viewsPerContent: { id: 'views-per-content', label: __('Views per Content', 'wp-statistics'), defaultVisible: true },
  avgCommentsPerContent: { id: 'avg-comments-per-content', label: __('Avg. Comments per Content', 'wp-statistics'), defaultVisible: true },

  // Page metrics (for single content pages)
  entryPage: { id: 'entry-page', label: __('Entry Page', 'wp-statistics'), defaultVisible: true },
  exitPage: { id: 'exit-page', label: __('Exit Page', 'wp-statistics'), defaultVisible: true },
  exitRate: { id: 'exit-rate', label: __('Exit Rate', 'wp-statistics'), defaultVisible: true },

  // Author metrics
  activeAuthors: { id: 'active-authors', label: __('Active Authors', 'wp-statistics'), defaultVisible: true },
  viewsPerAuthor: { id: 'views-per-author', label: __('Views per Author', 'wp-statistics'), defaultVisible: true },
  avgPostsPerAuthor: { id: 'avg-posts-per-author', label: __('Avg. Posts per Author', 'wp-statistics'), defaultVisible: true },

  // Visitor insights context metrics
  topCountry: { id: 'top-country', label: __('Top Country', 'wp-statistics'), defaultVisible: true },
  topReferrer: { id: 'top-referrer', label: __('Top Referrer', 'wp-statistics'), defaultVisible: true },
  topSearchTerm: { id: 'top-search-term', label: __('Top Search Term', 'wp-statistics'), defaultVisible: true },
  loggedInShare: { id: 'logged-in-share', label: __('Logged-in Share', 'wp-statistics'), defaultVisible: true },

  // Device metrics (context-style, no comparison)
  topBrowser: { id: 'top-browser', label: __('Top Browser', 'wp-statistics'), defaultVisible: true },
  topOperatingSystem: { id: 'top-operating-system', label: __('Top Operating System', 'wp-statistics'), defaultVisible: true },
  topDeviceCategory: { id: 'top-device-category', label: __('Top Device Category', 'wp-statistics'), defaultVisible: true },
  topResolution: { id: 'top-resolution', label: __('Top Resolution', 'wp-statistics'), defaultVisible: true },

  // Geographic metrics (context-style, no comparison)
  topRegion: { id: 'top-region', label: __('Top Region', 'wp-statistics'), defaultVisible: true },
  topCity: { id: 'top-city', label: __('Top City', 'wp-statistics'), defaultVisible: true },

  // Referral metrics
  referredVisitors: { id: 'referred-visitors', label: __('Referred Visitors', 'wp-statistics'), defaultVisible: true },
  topSearchEngine: { id: 'top-search-engine', label: __('Top Search Engine', 'wp-statistics'), defaultVisible: true },
  topSocialMedia: { id: 'top-social-media', label: __('Top Social Media', 'wp-statistics'), defaultVisible: true },
  topEntryPage: { id: 'top-entry-page', label: __('Top Entry Page', 'wp-statistics'), defaultVisible: true },
} as const satisfies Record<string, MetricConfig>

/**
 * Helper to pick metrics for a page by their keys.
 * Returns a new array of MetricConfig objects.
 *
 * @example
 * const METRIC_CONFIGS = pickMetrics('visitors', 'views', 'bounceRate', 'avgTimeOnPage')
 */
export function pickMetrics(...keys: (keyof typeof SHARED_METRICS)[]): MetricConfig[] {
  return keys.map(key => ({ ...SHARED_METRICS[key] }))
}

/**
 * Get a single metric config by key.
 * Useful when you need to modify a metric before using it.
 *
 * @example
 * const metric = getMetric('visitors')
 */
export function getMetric(key: keyof typeof SHARED_METRICS): MetricConfig {
  return { ...SHARED_METRICS[key] }
}
