/**
 * Content Registry
 *
 * Global registration system for premium content:
 * - Widgets: Small components on overview pages
 * - Reports: Config-based table reports
 * - Page Content: Full custom components
 *
 * Premium modules register at bundle load time.
 * Routes read from registry after React renders.
 */

import type { NavigateFunction } from '@tanstack/react-router'

import type { ReportConfig } from '@/components/report-page-renderer'

// Widget types
export interface WidgetRenderProps {
  data: unknown[]
  totals: Record<string, unknown>
  isCompareEnabled: boolean
  comparisonDateLabel: string
  navigate: NavigateFunction
  getTotalFromResponse: (totals: Record<string, unknown> | undefined, key: string) => number
}

export interface RegisteredWidget {
  id: string
  label: string
  defaultVisible: boolean
  queryId: string
  render: (props: WidgetRenderProps) => React.ReactNode
  link?: { title: string; to: string }
}

// Page content types
export interface PageContentProps {
  [key: string]: unknown
}

export interface RegisteredPageContent {
  pageId: string
  render: (props?: PageContentProps) => React.ReactNode
}

// Report types
export interface RegisteredReport<TData = unknown, TRecord = unknown> {
  pageId: string
  config: ReportConfig<TData, TRecord>
}

// Export config types
export interface ExportCsvConfig {
  sources: string[]
  group_by: string[]
  context?: string
  columns?: string[]
}

export interface ExportConfig {
  reportSlug: string
  csvConfig?: ExportCsvConfig
  pdfTargetSelector?: string
}

// Registry storage
const widgetRegistry = new Map<string, Map<string, RegisteredWidget>>()
const pageContentRegistry = new Map<string, RegisteredPageContent>()
const reportRegistry = new Map<string, RegisteredReport>()
const exportConfigRegistry = new Map<string, ExportConfig>()

// Registry API
const contentRegistry = {
  // Widgets
  registerWidget(pageId: string, widget: RegisteredWidget): void {
    if (!widgetRegistry.has(pageId)) {
      widgetRegistry.set(pageId, new Map())
    }
    widgetRegistry.get(pageId)!.set(widget.id, widget)
  },
  unregisterWidget(pageId: string, widgetId: string): void {
    widgetRegistry.get(pageId)?.delete(widgetId)
  },
  getWidgetsForPage(pageId: string): RegisteredWidget[] {
    return Array.from(widgetRegistry.get(pageId)?.values() ?? [])
  },

  // Page content
  registerPageContent(pageId: string, content: Omit<RegisteredPageContent, 'pageId'>): void {
    pageContentRegistry.set(pageId, { ...content, pageId })
  },
  unregisterPageContent(pageId: string): void {
    pageContentRegistry.delete(pageId)
  },
  getPageContent(pageId: string): RegisteredPageContent | null {
    return pageContentRegistry.get(pageId) ?? null
  },

  // Reports
  registerReport<TData = unknown, TRecord = unknown>(pageId: string, config: ReportConfig<TData, TRecord>): void {
    reportRegistry.set(pageId, { pageId, config: config as ReportConfig })
  },
  unregisterReport(pageId: string): void {
    reportRegistry.delete(pageId)
  },
  getReport<TData = unknown, TRecord = unknown>(pageId: string): RegisteredReport<TData, TRecord> | null {
    return (reportRegistry.get(pageId) as RegisteredReport<TData, TRecord>) ?? null
  },

  // Export configs
  registerExportConfig(pageId: string, config: ExportConfig): void {
    exportConfigRegistry.set(pageId, config)
  },
  getExportConfig(pageId: string): ExportConfig | undefined {
    return exportConfigRegistry.get(pageId)
  },

  // Export drawer renderer (set once by premium)
  exportDrawerRenderer: null as ((config: ExportConfig) => React.ReactNode) | null,
  registerExportDrawerRenderer(renderer: (config: ExportConfig) => React.ReactNode): void {
    contentRegistry.exportDrawerRenderer = renderer
  },
  renderExportDrawerContent(pageId: string): React.ReactNode {
    const config = exportConfigRegistry.get(pageId)
    if (!config || !contentRegistry.exportDrawerRenderer) return null
    return contentRegistry.exportDrawerRenderer(config)
  },
}

// Expose on window for premium modules
declare global {
  interface Window {
    wpsContentRegistry: typeof contentRegistry
  }
}

window.wpsContentRegistry = contentRegistry

// Type for backward compatibility
export type ContentRegistryContextValue = typeof contentRegistry

/**
 * Hook to access the content registry
 * Provides same API as before, but without Context overhead
 */
export function useContentRegistry(): ContentRegistryContextValue {
  return window.wpsContentRegistry
}
