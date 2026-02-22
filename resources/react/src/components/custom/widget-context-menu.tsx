import { __ } from '@wordpress/i18n'
import { ArrowDownIcon, ArrowUpIcon, Columns2, Columns3, EllipsisIcon, EyeOffIcon, Maximize2Icon, PanelLeftIcon } from 'lucide-react'
import { useCallback, useMemo } from 'react'

import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { type WidgetSize } from '@/contexts/page-options-context'
import { usePageOptions } from '@/hooks/use-page-options'

const SIZE_OPTIONS: Record<number, { label: string; icon: typeof Columns2 }> = {
  4: { label: __('⅓ width', 'wp-statistics'), icon: Columns3 },
  6: { label: __('½ width', 'wp-statistics'), icon: Columns2 },
  8: { label: __('⅔ width', 'wp-statistics'), icon: PanelLeftIcon },
  12: { label: __('Full width', 'wp-statistics'), icon: Maximize2Icon },
}

interface WidgetContextMenuProps {
  widgetId: string
  allowedSizes?: WidgetSize[]
}

export function WidgetContextMenu({ widgetId, allowedSizes = [4, 6, 12] }: WidgetContextMenuProps) {
  const { getWidgetSize, setWidgetSize, setWidgetVisibility, getOrderedVisibleWidgets, setWidgetOrder } = usePageOptions()
  const currentSize = getWidgetSize(widgetId)

  const visibleWidgets = getOrderedVisibleWidgets()
  const widgetIndex = useMemo(() => visibleWidgets.findIndex((w) => w.id === widgetId), [visibleWidgets, widgetId])
  const canMoveUp = widgetIndex > 0
  const canMoveDown = widgetIndex >= 0 && widgetIndex < visibleWidgets.length - 1

  const handleMove = useCallback(
    (direction: -1 | 1) => {
      const order = visibleWidgets.map((w) => w.id)
      const idx = order.indexOf(widgetId)
      if (idx < 0) return
      const swapIdx = idx + direction
      if (swapIdx < 0 || swapIdx >= order.length) return
      ;[order[idx], order[swapIdx]] = [order[swapIdx], order[idx]]
      setWidgetOrder(order)
    },
    [visibleWidgets, widgetId, setWidgetOrder]
  )

  return (
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <button
          className="inline-flex items-center justify-center rounded p-0.5 text-muted-foreground hover:text-foreground opacity-0 group-hover/widget:opacity-100 data-[state=open]:opacity-100 transition-opacity focus:opacity-100 focus:outline-none"
          aria-label={__('Widget options', 'wp-statistics')}
        >
          <EllipsisIcon className="size-4" />
        </button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end">
        {allowedSizes.map((size) => {
          const option = SIZE_OPTIONS[size]
          const Icon = option?.icon
          const isSelected = currentSize === size
          return (
            <DropdownMenuItem
              key={size}
              className={isSelected ? 'bg-accent text-accent-foreground' : undefined}
              onSelect={() => setWidgetSize(widgetId, size)}
            >
              {Icon && <Icon className="size-3.5" />}
              {option?.label ?? `${size} cols`}
            </DropdownMenuItem>
          )
        })}
        <DropdownMenuSeparator />
        <DropdownMenuItem disabled={!canMoveUp} onSelect={() => handleMove(-1)}>
          <ArrowUpIcon className="size-3.5" />
          {__('Move up', 'wp-statistics')}
        </DropdownMenuItem>
        <DropdownMenuItem disabled={!canMoveDown} onSelect={() => handleMove(1)}>
          <ArrowDownIcon className="size-3.5" />
          {__('Move down', 'wp-statistics')}
        </DropdownMenuItem>
        <DropdownMenuSeparator />
        <DropdownMenuItem
          variant="destructive"
          onSelect={() => setWidgetVisibility(widgetId, false)}
        >
          <EyeOffIcon className="size-3.5" />
          {__('Hide widget', 'wp-statistics')}
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>
  )
}
