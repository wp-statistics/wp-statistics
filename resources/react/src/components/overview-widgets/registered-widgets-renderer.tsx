import { getTotalValue } from '@/lib/utils'

import type { WidgetRenderContext } from './types'

// Stable reference for registered widget render props (avoids per-render lambda allocation)
const getTotalFromResponse = (t: Record<string, unknown> | undefined, key: string): number =>
  getTotalValue(t?.[key])

export function RegisteredWidgetsRenderer({ colSpan, ctx }: { colSpan: string; ctx: WidgetRenderContext }) {
  return (
    <>
      {ctx.registeredWidgets.map((rw) => {
        if (!ctx.isWidgetVisible(rw.id)) return null
        const widgetData = ctx.batchItems[rw.queryId]
        const data = (widgetData as { data?: { rows?: unknown[] } })?.data?.rows || []
        const totals = (widgetData as { data?: { totals?: Record<string, unknown> } })?.data?.totals || {}
        return (
          <div key={rw.id} className={colSpan}>
            {rw.render({
              data,
              totals,
              isCompareEnabled: ctx.isCompareEnabled,
              comparisonDateLabel: ctx.comparisonDateLabel,
              isFetching: ctx.isFetching,
              navigate: ctx.navigate,
              getTotalFromResponse,
              routeParams: ctx.routeParams,
            })}
          </div>
        )
      })}
    </>
  )
}
