import { SettingsField, SettingsSelectField, SettingsToggleField } from '@/components/settings-ui'
import { evaluateVisibleWhen } from '@/components/settings-ui/visible-when'
import { Input } from '@/components/ui/input'
import { NoticeBanner } from '@/components/ui/notice-banner'
import { Textarea } from '@/components/ui/textarea'
import type { UseSettingsReturn } from '@/hooks/use-settings'
import { getSettingsComponent } from '@/registry/settings-registry'
import type { SettingsFieldConfig } from '@/services/settings-config'

// ── Individual field renderers ───────────────────────────────────────

interface FieldProps {
  field: SettingsFieldConfig & { id: string }
  settings: UseSettingsReturn
}

function ToggleField({ field, settings }: FieldProps) {
  const key = field.setting_key!
  const raw = settings.getValue(key, field.default ?? false)
  const checked = field.inverted ? !raw : !!raw

  return (
    <SettingsToggleField
      id={field.id}
      label={field.label ?? ''}
      description={field.description}
      checked={checked}
      onCheckedChange={(v) => settings.setValue(key, field.inverted ? !v : v)}
      nested={field.nested}
    />
  )
}

function SelectField({ field, settings }: FieldProps) {
  const key = field.setting_key!
  const value = settings.getValue(key, field.default ?? '') as string

  return (
    <SettingsSelectField
      id={field.id}
      label={field.label ?? ''}
      description={field.description}
      layout={field.layout}
      nested={field.nested}
      value={value}
      onValueChange={(v) => settings.setValue(key, v)}
      placeholder={field.placeholder}
      options={field.options ?? []}
    />
  )
}

function InputField({ field, settings }: FieldProps) {
  const key = field.setting_key!
  const value = settings.getValue(key, field.default ?? '') as string

  return (
    <SettingsField
      id={field.id}
      label={field.label ?? ''}
      description={field.description}
      layout={field.layout ?? 'stacked'}
      nested={field.nested}
    >
      <Input
        id={field.id}
        type="text"
        placeholder={field.placeholder}
        value={value}
        onChange={(e) => settings.setValue(key, e.target.value)}
      />
    </SettingsField>
  )
}

function TextareaField({ field, settings }: FieldProps) {
  const key = field.setting_key!
  const value = settings.getValue(key, field.default ?? '') as string

  return (
    <SettingsField
      id={field.id}
      label={field.label ?? ''}
      description={field.description}
      layout={field.layout ?? 'stacked'}
      nested={field.nested}
    >
      <Textarea
        id={field.id}
        placeholder={field.placeholder}
        value={value}
        onChange={(e) => settings.setValue(key, e.target.value)}
        rows={field.rows ?? 4}
      />
    </SettingsField>
  )
}

function NumberField({ field, settings }: FieldProps) {
  const key = field.setting_key!
  const value = settings.getValue(key, field.default ?? 0) as number

  return (
    <SettingsField
      id={field.id}
      label={field.label ?? ''}
      description={field.description}
      layout={field.layout ?? 'stacked'}
      nested={field.nested}
    >
      <Input
        id={field.id}
        type="number"
        min={field.min}
        max={field.max}
        value={value}
        onChange={(e) => settings.setValue(key, parseInt(e.target.value) || 0)}
        className="w-32"
      />
    </SettingsField>
  )
}

function NoticeField({ field }: FieldProps) {
  return (
    <NoticeBanner
      id={field.id}
      type={field.notice_type ?? 'warning'}
      message={field.message ?? ''}
      helpUrl={field.help_url}
      dismissible={false}
    />
  )
}

function ComponentField({ field, settings }: FieldProps) {
  if (!field.component) return null
  const Component = getSettingsComponent(field.component)
  if (!Component) return null
  return <Component settings={settings} field={field} />
}

// ── Main dispatcher ──────────────────────────────────────────────────

export function FieldRenderer({ field, settings }: FieldProps) {
  // Check visibility
  if (!evaluateVisibleWhen(field.visible_when, settings)) {
    return null
  }

  switch (field.type) {
    case 'toggle':
      return <ToggleField field={field} settings={settings} />
    case 'select':
      return <SelectField field={field} settings={settings} />
    case 'input':
      return <InputField field={field} settings={settings} />
    case 'textarea':
      return <TextareaField field={field} settings={settings} />
    case 'number':
      return <NumberField field={field} settings={settings} />
    case 'notice':
      return <NoticeField field={field} settings={settings} />
    case 'component':
      return <ComponentField field={field} settings={settings} />
    default:
      return null
  }
}
