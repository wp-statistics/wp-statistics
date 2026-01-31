import { __ } from '@wordpress/i18n'
import { Plus } from 'lucide-react'
import { useState } from 'react'

import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'
import { usePageOptions } from '@/hooks/use-page-options'

interface WidgetCategory {
  label: string
  widgets: string[]
}

interface WidgetCatalogProps {
  categories: WidgetCategory[]
}

export function WidgetCatalog({ categories }: WidgetCatalogProps) {
  const { widgetConfigs, isWidgetVisible, setWidgetVisibility, setWidgetOrder, widgetOrder } = usePageOptions()
  const [selected, setSelected] = useState<string[]>([])
  const [open, setOpen] = useState(false)

  const configMap = new Map(widgetConfigs.map((c) => [c.id, c]))

  const toggle = (id: string) => {
    setSelected((prev) =>
      prev.includes(id) ? prev.filter((s) => s !== id) : [...prev, id]
    )
  }

  const handleAdd = () => {
    // Make selected widgets visible
    selected.forEach((id) => setWidgetVisibility(id, true))
    // Add newly visible widgets to the end of order if not already present
    const orderSet = new Set(widgetOrder)
    const newIds = selected.filter((id) => !orderSet.has(id))
    if (newIds.length > 0) {
      setWidgetOrder([...widgetOrder, ...newIds])
    }
    setSelected([])
    setOpen(false)
  }

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <PopoverTrigger asChild>
        <Button variant="outline" size="sm" className="gap-1">
          <Plus className="h-4 w-4" />
          {__('Add widget', 'wp-statistics')}
        </Button>
      </PopoverTrigger>
      <PopoverContent className="w-72 p-0" align="end">
        <div className="p-3 pb-2">
          <p className="text-sm font-medium">{__('Add widget', 'wp-statistics')}</p>
        </div>
        <div className="max-h-80 overflow-y-auto px-3 pb-2">
          {categories.map((cat) => {
            // Only show categories with at least one hidden widget
            const hiddenWidgets = cat.widgets.filter(
              (id) => configMap.has(id) && !isWidgetVisible(id)
            )
            if (hiddenWidgets.length === 0) return null

            return (
              <div key={cat.label} className="mb-3">
                <p className="mb-1.5 text-xs font-medium text-muted-foreground">
                  {cat.label}
                </p>
                <div className="space-y-1.5">
                  {hiddenWidgets.map((id) => {
                    const config = configMap.get(id)
                    if (!config) return null
                    return (
                      <label
                        key={id}
                        className="flex cursor-pointer items-center gap-2 rounded px-1 py-1 text-sm hover:bg-accent"
                      >
                        <Checkbox
                          checked={selected.includes(id)}
                          onCheckedChange={() => toggle(id)}
                        />
                        {config.label}
                      </label>
                    )
                  })}
                </div>
              </div>
            )
          })}
          {categories.every((cat) =>
            cat.widgets.every((id) => !configMap.has(id) || isWidgetVisible(id))
          ) && (
            <p className="py-4 text-center text-sm text-muted-foreground">
              {__('All widgets are visible', 'wp-statistics')}
            </p>
          )}
        </div>
        {selected.length > 0 && (
          <div className="border-t p-3">
            <Button size="sm" className="w-full" onClick={handleAdd}>
              {__('Add selected', 'wp-statistics')} ({selected.length})
            </Button>
          </div>
        )}
      </PopoverContent>
    </Popover>
  )
}
