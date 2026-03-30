import { __ } from '@wordpress/i18n'
import { useMemo } from 'react'

import { BarListContent } from '@/components/custom/bar-list-content'
import { HorizontalBar } from '@/components/custom/horizontal-bar'
import { TabbedPanel, type TabbedPanelTab } from '@/components/custom/tabbed-panel'
import { getAnalyticsRoute } from '@/lib/url-utils'
import { formatCompactNumber, formatDecimal } from '@/lib/utils'

import { extractRouteParamName } from './constants'

export function TabbedBarListWidget({
  widget,
  rows,
  batchItems,
  isCompareEnabled,
  calcPercentage,
  comparisonDateLabel,
}: {
  widget: PhpOverviewWidget
  rows: Record<string, unknown>[]
  /** Full batch items map — used when tabs have per-tab queryId */
  batchItems?: Record<string, { data?: { rows?: Record<string, unknown>[] } }>
  isCompareEnabled: boolean
  calcPercentage: (current: number, previous: number) => { percentage: string; isNegative: boolean }
  comparisonDateLabel: string
}) {
  const config = widget.tabbedBarListConfig!
  const labelField = config.labelField || 'page_title'
  const labelFallback = config.labelFallbackField || 'page_uri'

  const tabs = useMemo((): TabbedPanelTab[] => {
    return config.tabs
      .map((tabConfig) => {
        // Use per-tab queryId when available, otherwise fall back to shared rows
        const sourceRows = tabConfig.queryId && batchItems
          ? (batchItems[tabConfig.queryId]?.data?.rows || []) as Record<string, unknown>[]
          : rows
        let tabRows = [...sourceRows]

        // Filter rows if specified (e.g., comments > 0)
        if (tabConfig.filterField && tabConfig.filterMinValue !== undefined) {
          tabRows = tabRows.filter((row) => Number(row[tabConfig.filterField!]) >= tabConfig.filterMinValue!)
        }

        // Skip tab entirely if filtered to empty
        if (tabRows.length === 0 && tabConfig.filterField) return null

        // Compute ratio values when computedField is set
        const hasComputed = !!tabConfig.computedField
        if (hasComputed) {
          tabRows = tabRows.map((row) => ({
            ...row,
            _computed: Number(row[tabConfig.computedField!.denominator]) > 0
              ? Number(row[tabConfig.computedField!.numerator]) / Number(row[tabConfig.computedField!.denominator])
              : 0,
          }))
        }

        // Sort rows (use _computed for computed fields when sortBy matches)
        const sortField = hasComputed && tabConfig.sortBy === '_computed' ? '_computed' : tabConfig.sortBy
        tabRows.sort((a, b) => {
          let aVal: number, bVal: number
          if (tabConfig.sortType === 'date') {
            aVal = new Date(String(a[sortField] || 0)).getTime()
            bVal = new Date(String(b[sortField] || 0)).getTime()
          } else {
            aVal = Number(a[sortField]) || 0
            bVal = Number(b[sortField]) || 0
          }
          return tabConfig.sortDesc === false ? aVal - bVal : bVal - aVal
        })

        tabRows = tabRows.slice(0, tabConfig.maxItems || 5)

        return {
          id: tabConfig.id,
          label: tabConfig.label,
          columnHeaders: tabConfig.columnHeaders,
          ...(tabConfig.link && { link: tabConfig.link }),
          content: (
            <BarListContent isEmpty={tabRows.length === 0}>
              {tabRows.map((item, idx) => {
                // Resolve value: computed ratio or direct field
                const value = hasComputed ? (Number(item._computed) || 0) : (Number(item[tabConfig.valueField]) || 0)
                const prevValue = Number((item.previous as Record<string, unknown>)?.[tabConfig.valueField]) || 0
                const showComp = tabConfig.showComparison !== false && isCompareEnabled && !hasComputed
                const comparison = showComp ? calcPercentage(value, prevValue) : null

                // Resolve per-item link
                let linkTo: string | undefined
                let linkParams: Record<string, string> | undefined
                if (config.linkType === 'analytics-route') {
                  const route = getAnalyticsRoute(item.page_type as string, item.page_wp_id as number)
                  linkTo = route?.to
                  linkParams = route?.params
                } else if (config.linkTo && config.linkParamField) {
                  linkTo = config.linkTo
                  linkParams = { [extractRouteParamName(config.linkTo)]: String(item[config.linkParamField] || '') }
                }

                // Resolve icon (author avatar)
                let icon: React.ReactNode | undefined
                if (config.iconType === 'author-avatar' && config.iconField) {
                  const avatarUrl = String(item[config.iconField] || '')
                  icon = avatarUrl
                    ? <img src={avatarUrl} alt="" className="h-6 w-6 rounded-full object-cover" />
                    : <div className="h-6 w-6 rounded-full bg-neutral-200" />
                }

                const label = String(item[labelField] || item[labelFallback] || '/')
                const formatFn = tabConfig.valueFormat === 'decimal' ? formatDecimal : formatCompactNumber
                const valueLabel = tabConfig.valueSuffix
                  ? `${formatFn(value)} ${tabConfig.valueSuffix}`
                  : formatFn(value)

                return (
                  <HorizontalBar
                    key={`${String(item[labelFallback] || item[labelField] || idx)}-${idx}`}
                    icon={icon}
                    label={label}
                    value={valueLabel}
                    percentage={comparison?.percentage}
                    isNegative={comparison?.isNegative}
                    tooltipSubtitle={
                      showComp ? `${__('Previous:', 'wp-statistics')} ${formatCompactNumber(prevValue)}` : undefined
                    }
                    comparisonDateLabel={comparisonDateLabel}
                    showComparison={showComp}
                    showBar={false}
                    highlightFirst={false}
                    linkTo={linkTo}
                    linkParams={linkParams}
                  />
                )
              })}
            </BarListContent>
          ),
        }
      })
      .filter((t): t is TabbedPanelTab => t !== null)
  }, [rows, batchItems, config, isCompareEnabled, calcPercentage, comparisonDateLabel, labelField, labelFallback])

  return <TabbedPanel title={widget.label} tabs={tabs} defaultTab={config.tabs[0]?.id} />
}
