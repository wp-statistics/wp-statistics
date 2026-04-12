/**
 * DataTable Widget for Overview/Detail Pages
 *
 * Renders a DataTable within the overview widget grid.
 * Supports columns from PHP config, expandable sub-rows, and "See all" link.
 */

import { useNavigate } from '@tanstack/react-router'
import type { Row } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { useMemo } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { ExpandableSubRow } from '@/lib/expandable-sub-row'
import { createColumnsFromConfig } from '@/lib/standard-column-renderers'

import type { WidgetRenderContext } from './types'

export function DataTableWidget({
  widget,
  ctx,
  headerRight,
  footerLeft,
}: {
  widget: PhpOverviewWidget
  ctx: WidgetRenderContext
  headerRight?: React.ReactNode
  footerLeft?: React.ReactNode
}) {
  const config = widget.dataTableConfig!
  const rows = (ctx.batchItems[widget.queryId!]?.data?.rows || []) as Record<string, unknown>[]
  const navigate = useNavigate()

  const effectiveApiDateParams = ctx.widgetDateRanges[widget.id]?.apiDateParams ?? ctx.apiDateParams

  const columns = useMemo(
    () => createColumnsFromConfig(config.columns, { expandable: !!config.expandableRows }),
    [config.columns, config.expandableRows]
  )

  const expandableRows = config.expandableRows
  const renderSubComponent = useMemo(() => {
    if (!expandableRows) return undefined
    return ({ row }: { row: Row<Record<string, unknown>> }) => (
      <ExpandableSubRow
        row={row}
        config={expandableRows}
        apiDateParams={effectiveApiDateParams}
      />
    )
  }, [expandableRows, effectiveApiDateParams])

  const fullReportLink = widget.link && rows.length > 0
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    ? { action: () => navigate({ to: widget.link!.to as any }), text: widget.link.title || __('See all', 'wp-statistics') }
    : undefined

  return (
    <DataTable
      title={widget.label}
      columns={columns}
      data={rows}
      emptyMessage={config.emptyMessage}
      getRowCanExpand={expandableRows ? () => true : undefined}
      renderSubComponent={renderSubComponent}
      stickyHeader={true}
      showPagination={false}
      fullReportLink={fullReportLink}
      headerRight={headerRight}
      footerLeft={footerLeft}
    />
  )
}
