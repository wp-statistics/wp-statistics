import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { cn } from '@/lib/utils'

import { SettingsField } from './settings-field'

interface SettingsSelectFieldProps {
  id: string
  label: string
  description?: string
  layout?: 'inline' | 'stacked'
  nested?: boolean
  value: string
  onValueChange: (value: string) => void
  placeholder?: string
  options: Array<{ value: string; label: string }>
}

export function SettingsSelectField({
  id,
  label,
  description,
  layout = 'inline',
  nested,
  value,
  onValueChange,
  placeholder,
  options,
}: SettingsSelectFieldProps) {
  return (
    <SettingsField id={id} label={label} description={description} layout={layout} nested={nested}>
      <Select value={value} onValueChange={onValueChange}>
        <SelectTrigger id={id} className={cn(layout === 'inline' ? 'w-[200px]' : 'w-full max-w-sm')}>
          <SelectValue placeholder={placeholder} />
        </SelectTrigger>
        <SelectContent>
          {options.map((option) => (
            <SelectItem key={option.value} value={option.value}>
              {option.label}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>
    </SettingsField>
  )
}
