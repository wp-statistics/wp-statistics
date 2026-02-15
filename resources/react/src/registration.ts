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
import type {
  PageContentProps,
  RegisteredPageContent,
  RegisteredWidget,
  WidgetRenderProps,
} from '@/contexts/content-registry-context'

export function registerReport<TData = unknown, TRecord = unknown>(
  pageId: string,
  config: ReportConfig<TData, TRecord>
): void {
  window.wpsContentRegistry!.registerReport(pageId, config)
}

export function registerWidget(pageId: string, widget: RegisteredWidget): void {
  window.wpsContentRegistry!.registerWidget(pageId, widget)
}

export function registerPageContent(
  pageId: string,
  content: Omit<RegisteredPageContent, 'pageId'>
): void {
  window.wpsContentRegistry!.registerPageContent(pageId, content)
  // Dispatch event so routes can re-render with premium content
  window.dispatchEvent(new CustomEvent('wps:content-registered', { detail: { pageId } }))
}

export type { ReportConfig } from '@/components/report-page-renderer'
export type { PageContentProps,RegisteredWidget, WidgetRenderProps } from '@/contexts/content-registry-context'
