/**
 * DataTable Widget for Overview/Detail Pages
 *
 * Renders a DataTable within the overview widget grid.
 * Supports columns from PHP config, expandable sub-rows, and "See all" link.
 */

import type { Row } from '@tanstack/react-table'
import { useNavigate } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import { useMemo } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { ExpandableSubRow } from '@/lib/expandable-sub-row'
import { createColumnsFromConfig } from '@/lib/standard-column-renderers'

import type { WidgetRenderContext } from './types'

export function DataTableWidget({
  widget,
  ctx,
}: {
  widget: PhpOverviewWidget
  ctx: WidgetRenderContext
}) {
  const config = widget.dataTableConfig!
  const rows = (ctx.batchItems[widget.queryId!]?.data?.rows || []) as Record<string, unknown>[]
  const navigate = useNavigate()

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
        apiDateParams={ctx.apiDateParams}
      />
    )
  }, [expandableRows, ctx.apiDateParams])

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
    />
  )
}
