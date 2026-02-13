/**
 * Register custom React components for component-based settings/tools tabs.
 *
 * These are lazily imported so each tab's code is only loaded when visited.
 * Import this file once in app.tsx.
 */
import { lazy } from 'react'

import { registerSettingsComponent } from '@/registry/settings-registry'

// ── Settings tab components ──────────────────────────────────────────

registerSettingsComponent(
  'NotificationSettings',
  lazy(() =>
    import('@/components/settings/tabs/notification-settings').then((m) => ({
      default: m.NotificationSettings,
    }))
  )
)

registerSettingsComponent(
  'ExclusionSettings',
  lazy(() =>
    import('@/components/settings/tabs/exclusion-settings').then((m) => ({
      default: m.ExclusionSettings,
    }))
  )
)

registerSettingsComponent(
  'AccessSettings',
  lazy(() =>
    import('@/components/settings/tabs/access-settings').then((m) => ({
      default: m.AccessSettings,
    }))
  )
)

registerSettingsComponent(
  'DataManagementSettings',
  lazy(() =>
    import('@/components/settings/tabs/data-management-settings').then((m) => ({
      default: m.DataManagementSettings,
    }))
  )
)

registerSettingsComponent(
  'AdvancedSettings',
  lazy(() =>
    import('@/components/settings/tabs/advanced-settings').then((m) => ({
      default: m.AdvancedSettings,
    }))
  )
)

// ── Tools tab components ─────────────────────────────────────────────

registerSettingsComponent(
  'SystemInfoPage',
  lazy(() =>
    import('@/components/tools/tabs/system-info-page').then((m) => ({
      default: m.SystemInfoPage,
    }))
  )
)

registerSettingsComponent(
  'DiagnosticsPage',
  lazy(() =>
    import('@/components/tools/tabs/diagnostics-page').then((m) => ({
      default: m.DiagnosticsPage,
    }))
  )
)

registerSettingsComponent(
  'ScheduledTasksPage',
  lazy(() =>
    import('@/components/tools/tabs/scheduled-tasks-page').then((m) => ({
      default: m.ScheduledTasksPage,
    }))
  )
)

registerSettingsComponent(
  'BackgroundJobsPage',
  lazy(() =>
    import('@/components/tools/tabs/background-jobs-page').then((m) => ({
      default: m.BackgroundJobsPage,
    }))
  )
)

registerSettingsComponent(
  'ImportExportPage',
  lazy(() =>
    import('@/components/tools/tabs/import-export-page').then((m) => ({
      default: m.ImportExportPage,
    }))
  )
)

registerSettingsComponent(
  'BackupsPage',
  lazy(() =>
    import('@/components/tools/tabs/backups-page').then((m) => ({
      default: m.BackupsPage,
    }))
  )
)
