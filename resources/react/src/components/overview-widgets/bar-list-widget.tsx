import { __ } from '@wordpress/i18n'
import { useMemo } from 'react'

import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import { transformToBarList } from '@/lib/bar-list-helpers'
import { getAnalyticsRoute } from '@/lib/url-utils'
import { getTotalValue } from '@/lib/utils'

import { extractRouteParamName, ICON_RENDERERS, LABEL_TRANSFORMS } from './constants'
import type { WidgetRenderContext } from './types'

export function BarListWidget({
  widget,
  ctx,
  contextMenu,
}: {
  widget: PhpOverviewWidget
  ctx: WidgetRenderContext
  contextMenu?: React.ReactNode
}) {
  const navigate = ctx.navigate
  const queryResult = ctx.batchItems[widget.queryId!]
  const rows = queryResult?.data?.rows || []
  const totals = queryResult?.data?.totals

  const linkResolvers = useMemo(() => {
    if (widget.linkType === 'analytics-route') {
      // Cache per-item route lookup to avoid calling getAnalyticsRoute twice per row
      let lastItem: Record<string, unknown> | null = null
      let lastRoute: ReturnType<typeof getAnalyticsRoute> = undefined
      const resolveRoute = (item: Record<string, unknown>) => {
        if (item !== lastItem) {
          lastItem = item
          lastRoute = getAnalyticsRoute(item.page_type as string, item.page_wp_id as number, undefined, item.resource_id as number)
        }
        return lastRoute
      }
      return {
        linkTo: (item: Record<string, unknown>) => resolveRoute(item)?.to,
        linkParams: (item: Record<string, unknown>) => resolveRoute(item)?.params,
      }
    }
    if (widget.linkTo && widget.linkParamField) {
      const paramName = extractRouteParamName(widget.linkTo)
      return {
        linkTo: (item: Record<string, unknown>) => item[widget.linkParamField!] ? widget.linkTo! : undefined,
        linkParams: (item: Record<string, unknown>) => {
          const value = String(item[widget.linkParamField!] || '').toLowerCase()
          return { [paramName]: value }
        },
      }
    }
    return {}
  }, [widget.linkType, widget.linkTo, widget.linkParamField])

  return (
    <HorizontalBarList
      title={widget.label}
      showComparison={ctx.isCompareEnabled}
      columnHeaders={widget.columnHeaders}
      items={transformToBarList(rows, {
        label: (item) => {
          let raw = widget.labelField ? item[widget.labelField] : undefined
          if (!raw && widget.labelFallbackFields) {
            for (const field of widget.labelFallbackFields) {
              raw = item[field]
              if (raw) break
            }
          }
          const label = String(raw || __('Unknown', 'wp-statistics'))
          return widget.labelTransform ? LABEL_TRANSFORMS[widget.labelTransform](label) : label
        },
        value: (item) => Number(item[widget.valueField!]) || 0,
        previousValue: (item) => {
          const prev = item.previous as Record<string, unknown> | undefined
          return prev ? Number(prev[widget.valueField!]) || 0 : 0
        },
        total: getTotalValue(totals?.[widget.valueField!])
          || rows.reduce((sum: number, row: Record<string, unknown>) => sum + (Number(row[widget.valueField!]) || 0), 0)
          || 1,
        icon:
          widget.iconType && widget.iconSlugField
            ? (item) => ICON_RENDERERS[widget.iconType!](item as Record<string, unknown>, widget.iconSlugField!)
            : undefined,
        isCompareEnabled: ctx.isCompareEnabled,
        comparisonDateLabel: ctx.comparisonDateLabel,
        ...linkResolvers,
      })}
      link={widget.link ? { action: () => navigate({ to: widget.link!.to }) } : undefined}
      headerRight={contextMenu}
    />
  )
}
