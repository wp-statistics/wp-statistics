/**
 * DataTable Widget for Overview/Detail Pages
 *
 * Renders a DataTable within the overview widget grid.
 * Supports columns from PHP config and expandable sub-rows.
 *
 * Parent (renderWidget in overview-page-renderer) guards: widget.queryId && widget.dataTableConfig must exist.
 */

import type { Row } from '@tanstack/react-table'
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

  return (
    <DataTable
      title={widget.label}
      columns={columns}
      data={rows}
      emptyMessage={config.emptyMessage}
      getRowCanExpand={expandableRows ? () => true : undefined}
      renderSubComponent={renderSubComponent}
      stickyHeader={true}
    />
  )
}
