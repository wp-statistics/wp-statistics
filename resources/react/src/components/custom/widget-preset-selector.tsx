import { getPresetLabel, PRESETS } from '@/components/custom/date-range-picker'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { usePageOptions } from '@/hooks/use-page-options'

interface WidgetPresetSelectorProps {
  widgetId: string
}

export function WidgetPresetSelector({ widgetId }: WidgetPresetSelectorProps) {
  const { getWidgetPreset, setWidgetPreset } = usePageOptions()
  const current = getWidgetPreset(widgetId)

  return (
    <Select value={current} onValueChange={(v) => setWidgetPreset(widgetId, v)}>
      <SelectTrigger className="h-7 w-auto gap-1 border-none bg-transparent px-2 text-xs text-muted-foreground hover:text-foreground">
        <SelectValue>{getPresetLabel(current)}</SelectValue>
      </SelectTrigger>
      <SelectContent>
        {PRESETS.map((p) => (
          <SelectItem key={p.name} value={p.name}>
            {getPresetLabel(p.name)}
          </SelectItem>
        ))}
      </SelectContent>
    </Select>
  )
}
