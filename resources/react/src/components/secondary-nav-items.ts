import { __ } from '@wordpress/i18n'
import { Activity, Ban, Bell, Clock, Database, Info, type LucideIcon, Monitor, Settings, Shield, ShieldCheck, Stethoscope, Upload, Users, Wrench } from 'lucide-react'

import { getSettingsTabs } from '@/registry/settings-registry'
import type { SettingsConfig } from '@/services/settings-config'

export type SecondaryNavItem = {
  title: string
  url: string
  icon: LucideIcon
}

/**
 * Map icon name strings from PHP config to Lucide icon components.
 */
const iconMap: Record<string, LucideIcon> = {
  settings: Settings,
  monitor: Monitor,
  shield: Shield,
  bell: Bell,
  ban: Ban,
  users: Users,
  database: Database,
  wrench: Wrench,
  info: Info,
  stethoscope: Stethoscope,
  clock: Clock,
  activity: Activity,
  upload: Upload,
  shieldCheck: ShieldCheck,
}

/**
 * Build nav items for an area from the settings config.
 */
export function getNavItemsFromConfig(config: SettingsConfig, area: 'settings' | 'tools'): SecondaryNavItem[] {
  return getSettingsTabs(config, area).map((tab) => ({
    title: tab.label,
    url: `/${area}/${tab.id}`,
    icon: iconMap[tab.icon] ?? Settings,
  }))
}

// ── Static fallbacks (used before config loads) ──────────────────────

export const settingsNavItems: SecondaryNavItem[] = [
  { title: __('General', 'wp-statistics'), url: '/settings/general', icon: Settings },
  { title: __('Display', 'wp-statistics'), url: '/settings/display', icon: Monitor },
  { title: __('Privacy', 'wp-statistics'), url: '/settings/privacy', icon: Shield },
  { title: __('Notifications', 'wp-statistics'), url: '/settings/notifications', icon: Bell },
  { title: __('Exclusions', 'wp-statistics'), url: '/settings/exclusions', icon: Ban },
  { title: __('Access', 'wp-statistics'), url: '/settings/access', icon: Users },
  { title: __('Data Management', 'wp-statistics'), url: '/settings/data-management', icon: Database },
  { title: __('Advanced', 'wp-statistics'), url: '/settings/advanced', icon: Wrench },
]

export const toolsNavItems: SecondaryNavItem[] = [
  { title: __('System Info', 'wp-statistics'), url: '/tools/system-info', icon: Info },
  { title: __('Diagnostics', 'wp-statistics'), url: '/tools/diagnostics', icon: Stethoscope },
  { title: __('Privacy Audit', 'wp-statistics'), url: '/tools/privacy-audit', icon: ShieldCheck },
  { title: __('Scheduled Tasks', 'wp-statistics'), url: '/tools/scheduled-tasks', icon: Clock },
  { title: __('Background Jobs', 'wp-statistics'), url: '/tools/background-jobs', icon: Activity },
  { title: __('Import / Export', 'wp-statistics'), url: '/tools/import-export', icon: Upload },
  { title: __('Backups', 'wp-statistics'), url: '/tools/backups', icon: Database },
]
