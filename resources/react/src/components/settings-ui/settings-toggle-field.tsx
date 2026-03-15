import { Switch } from '@/components/ui/switch'

import { SettingsField } from './settings-field'

interface SettingsToggleFieldProps {
  id: string
  label: string
  description?: string
  checked: boolean
  onCheckedChange: (checked: boolean) => void
  nested?: boolean
}

export function SettingsToggleField({ id, label, description, checked, onCheckedChange, nested }: SettingsToggleFieldProps) {
  return (
    <SettingsField id={id} label={label} description={description} layout="inline" nested={nested}>
      <Switch id={id} checked={checked} onCheckedChange={onCheckedChange} />
    </SettingsField>
  )
}
