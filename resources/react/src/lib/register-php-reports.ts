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
import { createColumnsFromConfig } from '@/lib/standard-column-renderers'
import { createBarListWidgetRenderer } from '@/lib/standard-widget-renderers'
import { WordPress } from '@/lib/wordpress'
import { registerExportConfig, registerReport, registerWidget } from '@/registration'

export function registerPhpReports(): void {
  const reports = WordPress.getInstance().getData<Record<string, PhpReportDefinition>>('reports')
  if (!reports || Object.keys(reports).length === 0) return

  for (const [slug, config] of Object.entries(reports)) {
    // Skip if already registered by JS (JS always wins)
    if (window.wpsContentRegistry?.getReport(slug)) continue

    // Build columnConfig with context for the query factory
    const columnConfig = config.columnConfig
      ? { ...config.columnConfig, context: config.context }
      : undefined

    const queryOptionsFn = createGenericQueryOptions(slug, config.dataSource, {
      context: config.context,
      defaultApiColumns: config.defaultApiColumns,
    })

    // Collect comparable columns from config
    const comparableColumns = config.columns
      .filter((col) => col.comparable)
      .map((col) => col.key)

    registerReport(slug, {
      title: config.title,
      context: config.context,
      filterGroup: config.filterGroup,
      routeName: config.routeName || slug,
      queryOptions: queryOptionsFn,
      columns: (options) => createColumnsFromConfig(config.columns, options),
      transformData: (record: Record<string, unknown>) => record,
      defaultSort: config.defaultSort || { id: 'views', desc: true },
      perPage: config.perPage || 20,
      comparableColumns,
      defaultComparisonColumns: comparableColumns,
      emptyStateMessage: config.emptyStateMessage,
      // New fields
      defaultHiddenColumns: config.defaultHiddenColumns,
      columnConfig,
      defaultApiColumns: config.defaultApiColumns,
      customFilters: config.customFilters,
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

    // Register export config if defined
    if (config.export) {
      registerExportConfig(slug, {
        reportSlug: slug,
        csvConfig: {
          sources: config.export.sources,
          group_by: config.export.group_by,
          ...(config.export.context && { context: config.export.context }),
          ...(config.export.columns && { columns: config.export.columns }),
        },
      })
    }
  }
}
