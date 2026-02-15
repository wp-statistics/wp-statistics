import { Label } from '@/components/ui/label'

interface SettingsActionFieldProps {
  label: string
  description?: string
  children: React.ReactNode
}

export function SettingsActionField({ label, description, children }: SettingsActionFieldProps) {
  return (
    <div className="border-t pt-4">
      <div className="flex items-center justify-between">
        <div className="space-y-0.5">
          <Label>{label}</Label>
          {description && <p className="text-xs text-muted-foreground">{description}</p>}
        </div>
        {children}
      </div>
    </div>
  )
}
