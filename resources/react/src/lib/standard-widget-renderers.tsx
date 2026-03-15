/**
 * Standard Widget Renderers
 *
 * Creates widget render functions from PHP config.
 * Supports 'bar-list' type (used by all current premium widgets).
 */

import { __ } from '@wordpress/i18n'

import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'
import type { WidgetRenderProps } from '@/contexts/content-registry-context'
import { transformToBarList } from '@/lib/bar-list-helpers'
import { getAnalyticsRoute } from '@/lib/url-utils'

/**
 * Create a bar-list widget render function from PHP config
 */
export function createBarListWidgetRenderer(config: PhpReportWidget) {
  return function BarListWidget({
    data,
    totals,
    isCompareEnabled,
    comparisonDateLabel,
    navigate,
    getTotalFromResponse,
  }: WidgetRenderProps) {
    const totalValue = getTotalFromResponse(totals, config.valueField) || 1

    const items = transformToBarList(data as Record<string, unknown>[], {
      label: (item) => String(item[config.labelField] || item.page_title || __('Unknown', 'wp-statistics')),
      value: (item) => Number(item[config.valueField]) || 0,
      previousValue: config.previousValueField
        ? (item) => {
            const prev = item.previous as Record<string, unknown> | undefined
            return prev ? Number(prev[config.previousValueField!.replace('previous.', '')]) || 0 : 0
          }
        : undefined,
      total: totalValue,
      isCompareEnabled,
      comparisonDateLabel,
      linkTo: (item) => {
        if (item.page_type) {
          return getAnalyticsRoute(
            item.page_type as string,
            item.page_wp_id as number | undefined,
            undefined,
            item.resource_id as number | undefined
          )?.to
        }
        return undefined
      },
      linkParams: (item) => {
        if (item.page_type) {
          return getAnalyticsRoute(
            item.page_type as string,
            item.page_wp_id as number | undefined,
            undefined,
            item.resource_id as number | undefined
          )?.params
        }
        return undefined
      },
    })

    return (
      <HorizontalBarList
        title={config.label}
        items={items}
        showComparison={isCompareEnabled}
        columnHeaders={config.columnHeaders}
        link={
          config.link
            ? {
                title: config.link.title,
                action: () => navigate({ to: config.link!.to }),
              }
            : undefined
        }
      />
    )
  }
}
