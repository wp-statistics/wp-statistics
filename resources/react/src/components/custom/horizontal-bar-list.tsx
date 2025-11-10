import { ChevronRight } from 'lucide-react'

import { Button } from '@/components/ui/button'
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'

import { HorizontalBar } from './horizontal-bar'

interface HorizontalBarItem {
  icon?: React.ReactNode
  label: string
  value: string | number
  percentage: string | number
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
}

export function HorizontalBarList({ title, items, link }: HorizontalBarListProps) {
  return (
    <Card className="h-full">
      <CardHeader>
        <CardTitle>{title}</CardTitle>
      </CardHeader>

      <CardContent className="flex-1">
        {items.length === 0 ? (
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
                isNegative={item.isNegative}
                tooltipTitle={item.tooltipTitle}
                tooltipSubtitle={item.tooltipSubtitle}
                isFirst={index === 0}
              />
            ))}
          </div>
        )}
      </CardContent>

      {items.length !== 0 && (
        <CardFooter>
          <Button
            className="ml-auto gap-1 items-center font-normal hover:no-underline text-xs text-neutral-600"
            onClick={link.action}
            variant="link"
          >
            {link.title || 'View Entry Pages'}
            <ChevronRight className="w-3 h-4 ms-0" />
          </Button>
        </CardFooter>
      )}
    </Card>
  )
}
