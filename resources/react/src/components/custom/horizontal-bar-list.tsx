import { __ } from '@wordpress/i18n'
import { Loader2 } from 'lucide-react'

import { Panel, PanelAction, PanelContent, PanelFooter, PanelHeader, PanelTitle } from '@/components/ui/panel'

import { HorizontalBar } from './horizontal-bar'

interface HorizontalBarItem {
  icon?: React.ReactNode
  label: string
  value: string | number
  percentage: string | number
  fillPercentage?: number // 0-100, proportion of total for bar fill
  isNegative?: boolean
  tooltipTitle?: string
  tooltipSubtitle?: string
}

interface HorizontalBarListProps {
  title: string
  items: HorizontalBarItem[]
  link: {
    title?: string
    action(): void
  }
  loading?: boolean
}

export function HorizontalBarList({ title, items, link, loading = false }: HorizontalBarListProps) {
  return (
    <Panel className="h-full flex flex-col">
      <PanelHeader>
        <PanelTitle>{title}</PanelTitle>
      </PanelHeader>

      <PanelContent className="flex-1">
        {loading ? (
          <div className="flex h-32 flex-1 flex-col items-center justify-center">
            <Loader2 className="h-6 w-6 animate-spin text-muted-foreground" />
          </div>
        ) : items.length === 0 ? (
          <div className="flex h-full flex-1 flex-col items-center justify-center text-center">
            <p className="text-sm text-neutral-500">No data available for the selected period</p>
          </div>
        ) : (
          <div className="flex flex-col gap-3">
            {items.map((item, index) => (
              <HorizontalBar
                key={index}
                icon={item.icon}
                label={item.label}
                value={item.value}
                percentage={item.percentage}
                fillPercentage={item.fillPercentage}
                isNegative={item.isNegative}
                tooltipTitle={item.tooltipTitle}
                tooltipSubtitle={item.tooltipSubtitle}
                isFirst={index === 0}
              />
            ))}
          </div>
        )}
      </PanelContent>

      {items.length !== 0 && (
        <PanelFooter>
          <PanelAction onClick={link.action}>
            {link.title || __('View Entry Pages', 'wp-statistics')}
          </PanelAction>
        </PanelFooter>
      )}
    </Panel>
  )
}
