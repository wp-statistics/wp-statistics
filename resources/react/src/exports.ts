/**
 * WP Statistics - Exports Bridge
 *
 * Exposes React hooks and components on window for premium plugin access.
 * The premium plugin's components are rendered within the free plugin's
 * React tree via PremiumSlot, so they have access to contexts.
 *
 * @package WP_Statistics
 */

import type { ComponentType, ReactNode } from 'react'
import type { QueryClient } from '@tanstack/react-query'
import React from 'react'
import * as ReactDOM from 'react-dom'
import * as TanStackQuery from '@tanstack/react-query'

// Options Drawer components
import {
  OptionsDetailView,
  OptionsMenuItem,
  OptionsToggleItem,
  useOptionsDrawer,
} from '@/components/custom/options-drawer/options-drawer'
// Content Registry
import { useContentRegistry } from '@/contexts/content-registry-context'
// Report Page Renderer
import {
  ReportPageRenderer,
  type ReportConfig,
} from '@/components/report-page-renderer'
import { useGlobalFilters } from '@/hooks/use-global-filters'
// Hooks
import { usePageOptions } from '@/hooks/use-page-options'
// Data Table components for premium to use
import { DataTable } from '@/components/custom/data-table'
import { DateRangePicker, type DateRange } from '@/components/custom/date-range-picker'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { Panel } from '@/components/ui/panel'
import {
  createPageViewsColumns,
  type PageViewsData,
  type PageViewsColumnOptions,
} from '@/components/data-table-columns/page-views-columns'
import {
  transformToBarList,
  type HorizontalBarItem,
  type TransformBarListOptions,
} from '@/lib/bar-list-helpers'
import { ErrorMessage } from '@/components/custom/error-message'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { NoticeContainer } from '@/components/ui/notice-container'
// Services
import {
  getEntryPagesQueryOptions,
  type EntryPageRecord,
  type GetEntryPagesParams,
} from '@/services/page-insight/get-entry-pages'
import {
  getExitPagesQueryOptions,
  type ExitPageRecord,
  type GetExitPagesParams,
} from '@/services/page-insight/get-exit-pages'
// Query Client
import { queryClient } from '@/lib/query-client'
// Response Helpers
import { extractRows, extractMeta } from '@/lib/response-helpers'

// Expose React globally for premium plugin to use as external
// @ts-expect-error - Setting global React
window.React = React
// @ts-expect-error - Setting global ReactDOM
window.ReactDOM = ReactDOM
// @ts-expect-error - Setting global TanStack Query for premium plugin
window.__TANSTACK_QUERY__ = TanStackQuery

/**
 * Convenience function to register a report with the content registry.
 * Premium modules can use this to register config-based reports.
 */
function registerReport<TData = unknown, TRecord = unknown>(
  pageId: string,
  config: ReportConfig<TData, TRecord>
): void {
  window.wpsContentRegistry?.registerReport(pageId, config)
}

// Type definition for exports
interface WpsExports {
  version: string
  apiVersion: number
  hooks: {
    usePageOptions: typeof usePageOptions
    useGlobalFilters: typeof useGlobalFilters
    useOptionsDrawer: typeof useOptionsDrawer
    useContentRegistry: typeof useContentRegistry
  }
  components: {
    OptionsMenuItem: typeof OptionsMenuItem
    OptionsDetailView: typeof OptionsDetailView
    OptionsToggleItem: typeof OptionsToggleItem
    DataTable: typeof DataTable
    DateRangePicker: typeof DateRangePicker
    HorizontalBarList: typeof HorizontalBarList
    Panel: typeof Panel
    ErrorMessage: typeof ErrorMessage
    PanelSkeleton: typeof PanelSkeleton
    TableSkeleton: typeof TableSkeleton
    NoticeContainer: typeof NoticeContainer
    ReportPageRenderer: typeof ReportPageRenderer
  }
  utils: {
    createPageViewsColumns: typeof createPageViewsColumns
    transformToBarList: typeof transformToBarList
    extractRows: typeof extractRows
    extractMeta: typeof extractMeta
  }
  services: {
    getEntryPagesQueryOptions: typeof getEntryPagesQueryOptions
    getExitPagesQueryOptions: typeof getExitPagesQueryOptions
  }
  /**
   * Register a config-based report page.
   * Core handles the full UI (header, filters, table, pagination).
   * Premium provides the config (title, columns, query, etc.).
   */
  registerReport: typeof registerReport
  queryClient: QueryClient
}

// Expose on window
declare global {
  interface Window {
    wps_exports?: WpsExports
    __TANSTACK_QUERY__?: typeof TanStackQuery
  }
}

// Initialize exports
window.wps_exports = {
  version: '15.0.0',
  apiVersion: 1,
  hooks: {
    usePageOptions,
    useGlobalFilters,
    useOptionsDrawer,
    useContentRegistry,
  },
  components: {
    OptionsMenuItem,
    OptionsDetailView,
    OptionsToggleItem,
    DataTable,
    DateRangePicker,
    HorizontalBarList,
    Panel,
    ErrorMessage,
    PanelSkeleton,
    TableSkeleton,
    NoticeContainer,
    ReportPageRenderer,
  },
  utils: {
    createPageViewsColumns,
    transformToBarList,
    extractRows,
    extractMeta,
  },
  services: {
    getEntryPagesQueryOptions,
    getExitPagesQueryOptions,
  },
  registerReport,
  queryClient,
}

// Dispatch event to notify premium plugin that exports are ready
window.dispatchEvent(new CustomEvent('wps:exports-ready', {
  detail: { version: '15.0.0', apiVersion: 1 }
}))
