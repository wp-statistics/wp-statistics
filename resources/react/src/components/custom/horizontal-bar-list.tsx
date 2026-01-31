import { __ } from '@wordpress/i18n'
import { Loader2 } from 'lucide-react'

import { EmptyState } from '@/components/ui/empty-state'
import { Panel, PanelAction, PanelActions, PanelContent, PanelFooter, PanelHeader, PanelTitle } from '@/components/ui/panel'

import { BarListHeader } from './bar-list-header'
import { HorizontalBar } from './horizontal-bar'

interface HorizontalBarItem {
  icon?: React.ReactNode
  label: string
  value: string | number
  percentage?: string | number
  fillPercentage?: number // 0-100, proportion of total for bar fill
  isNegative?: boolean
  tooltipSubtitle?: string
  /** Date range comparison header for tooltip */
  comparisonDateLabel?: string
  /** Internal link route path */
  linkTo?: string
  /** Internal link route params */
  linkParams?: Record<string, string>
}

interface HorizontalBarListProps {
  title: string
  items: HorizontalBarItem[]
  link?: {
    title?: string
    action(): void
  }
  loading?: boolean
  showComparison?: boolean // Whether to show percentage change indicators
  columnHeaders?: {
    left: string
    right: string
  }
  footerLeft?: React.ReactNode
  headerRight?: React.ReactNode
}

export function HorizontalBarList({ title, items, link, loading = false, showComparison = true, columnHeaders, footerLeft, headerRight }: HorizontalBarListProps) {
  // Ensure items is always an array
  const safeItems = items || []

  return (
    <Panel className="h-full flex flex-col">
      <PanelHeader>
        <PanelTitle>{title}</PanelTitle>
        {headerRight && <PanelActions>{headerRight}</PanelActions>}
      </PanelHeader>

      <PanelContent className="flex-1">
        {loading ? (
          <div className="flex h-32 flex-1 flex-col items-center justify-center">
            <Loader2 className="h-6 w-6 animate-spin text-muted-foreground" />
          </div>
        ) : safeItems.length === 0 ? (
          <EmptyState title={__('No data available', 'wp-statistics')} className="py-6" />
        ) : (
          <>
            {columnHeaders && (
              <BarListHeader left={columnHeaders.left} right={columnHeaders.right} />
            )}
            <div className="flex flex-col gap-3">
            {safeItems.map((item, index) => (
              <HorizontalBar
                key={`${item.label}-${index}`}
                icon={item.icon}
                label={item.label}
                value={item.value}
                percentage={item.percentage}
                fillPercentage={item.fillPercentage}
                isNegative={item.isNegative}
                tooltipSubtitle={item.tooltipSubtitle}
                comparisonDateLabel={item.comparisonDateLabel}
                linkTo={item.linkTo}
                linkParams={item.linkParams}
                isFirst={index === 0}
                showComparison={showComparison}
              />
            ))}
            </div>
          </>
        )}
      </PanelContent>

      {(footerLeft || (link && safeItems.length !== 0)) && (
        <PanelFooter className="justify-between">
          <div>{footerLeft}</div>
          <div>
            {link && safeItems.length !== 0 && (
              <PanelAction onClick={link.action}>{link.title || __('See all', 'wp-statistics')}</PanelAction>
            )}
          </div>
        </PanelFooter>
      )}
    </Panel>
  )
}
