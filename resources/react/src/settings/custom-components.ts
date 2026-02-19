/**
 * Register custom React components for component-based settings fields and tools tabs.
 *
 * Settings tabs are now fully declarative (cards + fields from PHP config).
 * Complex UI elements use `type: 'component'` fields backed by these small,
 * focused components that receive `settings` and `field` props.
 *
 * Tools tabs remain fully component-based (rendered entirely in React).
 *
 * These are lazily imported so each component's code is only loaded when visited.
 * Import this file once in app.tsx.
 */
import { lazy } from 'react'

import { registerSettingsComponent } from '@/registry/settings-registry'

// ── Settings field-level components ─────────────────────────────────

registerSettingsComponent(
  'RestoreDefaultsAction',
  lazy(() =>
    import('@/components/settings/components/restore-defaults-action').then((m) => ({
      default: m.RestoreDefaultsAction,
    }))
  )
)

registerSettingsComponent(
  'RoleExclusions',
  lazy(() =>
    import('@/components/settings/components/role-exclusions').then((m) => ({
      default: m.RoleExclusions,
    }))
  )
)

registerSettingsComponent(
  'ExcludeIpField',
  lazy(() =>
    import('@/components/settings/components/exclude-ip-field').then((m) => ({
      default: m.ExcludeIpField,
    }))
  )
)

registerSettingsComponent(
  'QueryParamsField',
  lazy(() =>
    import('@/components/settings/components/query-params-field').then((m) => ({
      default: m.QueryParamsField,
    }))
  )
)

registerSettingsComponent(
  'AccessLevelTable',
  lazy(() =>
    import('@/components/settings/components/access-level-table').then((m) => ({
      default: m.AccessLevelTable,
    }))
  )
)

registerSettingsComponent(
  'RetentionModeSelector',
  lazy(() =>
    import('@/components/settings/components/retention-mode-selector').then((m) => ({
      default: m.RetentionModeSelector,
    }))
  )
)

registerSettingsComponent(
  'ApplyRetentionAction',
  lazy(() =>
    import('@/components/settings/components/apply-retention-action').then((m) => ({
      default: m.ApplyRetentionAction,
    }))
  )
)

registerSettingsComponent(
  'EmailReportContentInfo',
  lazy(() =>
    import('@/components/settings/components/email-report-content-info').then((m) => ({
      default: m.EmailReportContentInfo,
    }))
  )
)

registerSettingsComponent(
  'EmailReportScheduleInfo',
  lazy(() =>
    import('@/components/settings/components/email-report-schedule-info').then((m) => ({
      default: m.EmailReportScheduleInfo,
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

registerSettingsComponent(
  'PrivacyAuditPage',
  lazy(() =>
    import('@/components/tools/tabs/privacy-audit-page').then((m) => ({
      default: m.PrivacyAuditPage,
    }))
  )
)
