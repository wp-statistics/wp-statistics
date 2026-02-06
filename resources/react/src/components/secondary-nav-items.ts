import { Activity, Ban, Bell, Clock, Database, Info, type LucideIcon, Monitor, Settings, Shield, Stethoscope, Upload, Users, Wrench } from 'lucide-react'

import { __ } from '@wordpress/i18n'

export type SecondaryNavItem = {
  title: string
  url: string
  icon: LucideIcon
}

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
  { title: __('Scheduled Tasks', 'wp-statistics'), url: '/tools/scheduled-tasks', icon: Clock },
  { title: __('Background Jobs', 'wp-statistics'), url: '/tools/background-jobs', icon: Activity },
  { title: __('Import / Export', 'wp-statistics'), url: '/tools/import-export', icon: Upload },
  { title: __('Backups', 'wp-statistics'), url: '/tools/backups', icon: Database },
]
