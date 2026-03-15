/**
 * PHP-to-JS Registration Bridge
 *
 * Reads PHP-defined report configs from window.wps_react.reports
 * and registers them using the existing registerReport/registerWidget/registerExportConfig APIs.
 *
 * Called at boot time (before React renders).
 * JS modules imported later can override by re-registering the same slug.
 */

import { createGenericQueryOptions } from '@/lib/generic-report-query'
import { createChartSlot } from '@/lib/standard-chart-renderer'
import { createColumnsFromConfig } from '@/lib/standard-column-renderers'
import { createBarListWidgetRenderer } from '@/lib/standard-widget-renderers'
import { WordPress } from '@/lib/wordpress'
import { registerExportConfig, registerReport, registerWidget } from '@/registration'

/**
 * Apply sensible defaults to PHP-defined columns so PHP configs can be shorter.
 * Explicit values always win (spread last).
 */
function applyColumnDefaults(col: PhpReportColumn): PhpReportColumn {
  return {
    priority: 'primary',
    sortable: true,
    ...(col.type === 'numeric' && !col.size ? { size: 'views' } : {}),
    ...(col.comparable && !col.previousKey ? { previousKey: `previous.${col.dataField || col.key}` } : {}),
    ...col,
  }
}

/**
 * Auto-derive columnMapping from columns' dataField when not explicitly provided.
 * Only includes entries where key differs from dataField (identity mappings are redundant).
 */
function deriveColumnMapping(columns: PhpReportColumn[]): Record<string, string> | undefined {
  const mapping: Record<string, string> = {}
  for (const col of columns) {
    if (col.dataField && col.dataField !== col.key) {
      mapping[col.key] = col.dataField
    }
  }
  return Object.keys(mapping).length > 0 ? mapping : undefined
}

export function registerPhpReports(): void {
  const reports = WordPress.getInstance().getData<Record<string, PhpReportDefinition>>('reports')
  if (!reports || Object.keys(reports).length === 0) return

  for (const [slug, config] of Object.entries(reports)) {
    // Skip overview/detail configs (handled by OverviewPageRenderer via PhpOverviewRoute)
    if (config.type === 'overview' || config.type === 'detail') continue

    // Skip if already registered by JS (JS always wins)
    if (window.wpsContentRegistry?.getReport(slug)) continue

    // Apply column defaults so PHP configs can omit common boilerplate
    const columns = config.columns.map(applyColumnDefaults)

    // Auto-derive columnMapping from columns when not explicitly provided
    const autoMapping = !config.dataSource.columnMapping ? deriveColumnMapping(columns) : undefined
    const dataSource = autoMapping
      ? { ...config.dataSource, columnMapping: autoMapping }
      : config.dataSource

    // Build columnConfig with context for the query factory
    const columnConfig = config.columnConfig
      ? { ...config.columnConfig, context: config.context }
      : undefined

    const queryOptionsFn = createGenericQueryOptions(slug, dataSource, {
      context: config.context,
      defaultApiColumns: config.defaultApiColumns,
      ...(config.realtime && { realtime: { windowMinutes: config.realtime.windowMinutes } }),
    })

    // Collect comparable columns from config
    const comparableColumns = columns
      .filter((col) => col.comparable)
      .map((col) => col.key)

    registerReport(slug, {
      title: config.title,
      context: config.context,
      filterGroup: config.filterGroup,
      routeName: config.routeName || slug,
      queryOptions: queryOptionsFn,
      columns: (options) => createColumnsFromConfig(columns, { ...options, expandable: !!config.expandableRows }),
      transformData: (record: Record<string, unknown>) => record,
      defaultSort: config.defaultSort || { id: 'visitors', desc: true },
      perPage: config.perPage || 25,
      comparableColumns,
      defaultComparisonColumns: config.defaultComparisonColumns || comparableColumns,
      emptyStateMessage: config.emptyStateMessage,
      // New fields
      defaultHiddenColumns: config.defaultHiddenColumns,
      columnConfig,
      defaultApiColumns: config.defaultApiColumns,
      customFilters: config.customFilters,
      hideFilters: config.hideFilters,
      lockedFilters: config.lockedFilters,
      hardcodedFilters: config.hardcodedFilters,
      enabled: config.enabled,
      headerFilter: config.headerFilter,
      expandableRows: config.expandableRows,
      realtime: config.realtime,
      // Chart support: metrics-based charts use built-in ReportPageRenderer chart,
      // legacy charts (no metrics) use createChartSlot as beforeTable slot
      ...(config.chart && config.chart.metrics
        ? { chart: config.chart }
        : config.chart ? { beforeTable: createChartSlot(config.chart) } : {}),
    })

    // Register widget if defined
    if (config.widget) {
      const widgetConfig = config.widget
      registerWidget(widgetConfig.pageId, {
        id: widgetConfig.id,
        label: widgetConfig.label,
        defaultVisible: true,
        queryId: widgetConfig.queryId,
        render: createBarListWidgetRenderer(widgetConfig),
        link: widgetConfig.link,
      })
    }

    // Register export config if defined (sources/group_by auto-derived from dataSource when omitted)
    if (config.export) {
      const mainQuery = dataSource.queries
        ? dataSource.queries.find((q) => q.id === dataSource.queryId) || dataSource.queries[0]
        : undefined
      registerExportConfig(slug, {
        reportSlug: slug,
        csvConfig: {
          sources: config.export.sources || mainQuery?.sources || dataSource.sources || [],
          group_by: config.export.group_by || mainQuery?.group_by || dataSource.group_by || [],
          ...(config.export.context && { context: config.export.context }),
          ...(config.export.columns && { columns: config.export.columns }),
        },
      })
    }
  }
}
