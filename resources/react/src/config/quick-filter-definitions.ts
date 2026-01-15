import { __ } from '@wordpress/i18n'
import type { LucideIcon } from 'lucide-react'
import { Globe, Link, Search, Share2, UserCheck, UserPlus, Users } from 'lucide-react'

export interface QuickFilterDefinition {
  id: string
  label: string
  icon: LucideIcon
  fieldName: FilterFieldName
  operator: FilterOperator
  value: string
  valueLabel?: string
}

/**
 * Quick filter definitions organized by filter group.
 * Each group corresponds to a page section in the app.
 *
 * Note: Only filters with static/predictable values are used here.
 * Filters like device_type use database IDs which vary between installations.
 */
const QUICK_FILTER_DEFINITIONS: Record<string, QuickFilterDefinition[]> = {
  visitors: [
    {
      id: 'new-visitors',
      label: __('New Visitors', 'wp-statistics'),
      icon: UserPlus,
      fieldName: 'visitor_type',
      operator: 'is',
      value: '0',
      valueLabel: __('New', 'wp-statistics'),
    },
    {
      id: 'returning-visitors',
      label: __('Returning Visitors', 'wp-statistics'),
      icon: Users,
      fieldName: 'visitor_type',
      operator: 'is',
      value: '1',
      valueLabel: __('Returning', 'wp-statistics'),
    },
    {
      id: 'logged-in-users',
      label: __('Logged-in Users', 'wp-statistics'),
      icon: UserCheck,
      fieldName: 'logged_in',
      operator: 'is',
      value: '1',
      valueLabel: __('Logged-in', 'wp-statistics'),
    },
    {
      id: 'direct-traffic',
      label: __('Direct Traffic', 'wp-statistics'),
      icon: Globe,
      fieldName: 'referrer_channel',
      operator: 'is',
      value: 'direct',
      valueLabel: __('Direct', 'wp-statistics'),
    },
    {
      id: 'search-traffic',
      label: __('Search Traffic', 'wp-statistics'),
      icon: Search,
      fieldName: 'referrer_channel',
      operator: 'is',
      value: 'search',
      valueLabel: __('Search', 'wp-statistics'),
    },
  ],

  views: [
    {
      id: 'direct-traffic',
      label: __('Direct Traffic', 'wp-statistics'),
      icon: Globe,
      fieldName: 'referrer_channel',
      operator: 'is',
      value: 'direct',
      valueLabel: __('Direct', 'wp-statistics'),
    },
    {
      id: 'search-traffic',
      label: __('Search Traffic', 'wp-statistics'),
      icon: Search,
      fieldName: 'referrer_channel',
      operator: 'is',
      value: 'search',
      valueLabel: __('Search', 'wp-statistics'),
    },
    {
      id: 'social-traffic',
      label: __('Social Traffic', 'wp-statistics'),
      icon: Share2,
      fieldName: 'referrer_channel',
      operator: 'is',
      value: 'social',
      valueLabel: __('Social', 'wp-statistics'),
    },
    {
      id: 'referral-traffic',
      label: __('Referral Traffic', 'wp-statistics'),
      icon: Link,
      fieldName: 'referrer_channel',
      operator: 'is',
      value: 'referral',
      valueLabel: __('Referral', 'wp-statistics'),
    },
  ],

  'individual-content': [
    {
      id: 'direct-traffic',
      label: __('Direct Traffic', 'wp-statistics'),
      icon: Globe,
      fieldName: 'referrer_channel',
      operator: 'is',
      value: 'direct',
      valueLabel: __('Direct', 'wp-statistics'),
    },
    {
      id: 'search-traffic',
      label: __('Search Traffic', 'wp-statistics'),
      icon: Search,
      fieldName: 'referrer_channel',
      operator: 'is',
      value: 'search',
      valueLabel: __('Search', 'wp-statistics'),
    },
    {
      id: 'social-traffic',
      label: __('Social Traffic', 'wp-statistics'),
      icon: Share2,
      fieldName: 'referrer_channel',
      operator: 'is',
      value: 'social',
      valueLabel: __('Social', 'wp-statistics'),
    },
    {
      id: 'logged-in-users',
      label: __('Logged-in Users', 'wp-statistics'),
      icon: UserCheck,
      fieldName: 'logged_in',
      operator: 'is',
      value: '1',
      valueLabel: __('Logged-in', 'wp-statistics'),
    },
  ],

  categories: [
    {
      id: 'direct-traffic',
      label: __('Direct Traffic', 'wp-statistics'),
      icon: Globe,
      fieldName: 'referrer_channel',
      operator: 'is',
      value: 'direct',
      valueLabel: __('Direct', 'wp-statistics'),
    },
    {
      id: 'search-traffic',
      label: __('Search Traffic', 'wp-statistics'),
      icon: Search,
      fieldName: 'referrer_channel',
      operator: 'is',
      value: 'search',
      valueLabel: __('Search', 'wp-statistics'),
    },
  ],

  referrals: [
    {
      id: 'search-traffic',
      label: __('Search Traffic', 'wp-statistics'),
      icon: Search,
      fieldName: 'referrer_channel',
      operator: 'is',
      value: 'search',
      valueLabel: __('Search', 'wp-statistics'),
    },
    {
      id: 'social-traffic',
      label: __('Social Traffic', 'wp-statistics'),
      icon: Share2,
      fieldName: 'referrer_channel',
      operator: 'is',
      value: 'social',
      valueLabel: __('Social', 'wp-statistics'),
    },
    {
      id: 'referral-traffic',
      label: __('Referral Traffic', 'wp-statistics'),
      icon: Link,
      fieldName: 'referrer_channel',
      operator: 'is',
      value: 'referral',
      valueLabel: __('Referral', 'wp-statistics'),
    },
  ],

  content: [
    {
      id: 'direct-traffic',
      label: __('Direct Traffic', 'wp-statistics'),
      icon: Globe,
      fieldName: 'referrer_channel',
      operator: 'is',
      value: 'direct',
      valueLabel: __('Direct', 'wp-statistics'),
    },
    {
      id: 'search-traffic',
      label: __('Search Traffic', 'wp-statistics'),
      icon: Search,
      fieldName: 'referrer_channel',
      operator: 'is',
      value: 'search',
      valueLabel: __('Search', 'wp-statistics'),
    },
    {
      id: 'social-traffic',
      label: __('Social Traffic', 'wp-statistics'),
      icon: Share2,
      fieldName: 'referrer_channel',
      operator: 'is',
      value: 'social',
      valueLabel: __('Social', 'wp-statistics'),
    },
  ],
}

/**
 * Get quick filter definitions for a specific filter group.
 * Returns an empty array if the group doesn't have quick filters defined.
 */
export function getQuickFiltersForGroup(group: string): QuickFilterDefinition[] {
  return QUICK_FILTER_DEFINITIONS[group] || []
}

export { QUICK_FILTER_DEFINITIONS }
