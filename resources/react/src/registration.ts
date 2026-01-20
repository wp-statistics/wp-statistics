/**
 * Registration API for Premium modules
 *
 * Modules call these to register reports/widgets.
 * Registry is available immediately (same bundle).
 */

// Side-effect import: ensures registry is initialized before this module runs
// This creates a dependency chain that the bundler must respect:
// premium module → registration.ts → content-registry-context.tsx
import '@/contexts/content-registry-context'

import type { ReportConfig } from '@/components/report-page-renderer'
import type { RegisteredWidget, WidgetRenderProps } from '@/contexts/content-registry-context'

export function registerReport<TData = unknown, TRecord = unknown>(
  pageId: string,
  config: ReportConfig<TData, TRecord>
): void {
  window.wpsContentRegistry!.registerReport(pageId, config)
}

export function registerWidget(pageId: string, widget: RegisteredWidget): void {
  window.wpsContentRegistry!.registerWidget(pageId, widget)
}

export type { ReportConfig } from '@/components/report-page-renderer'
export type { RegisteredWidget, WidgetRenderProps } from '@/contexts/content-registry-context'
