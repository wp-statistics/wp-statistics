import { CheckCircle2 } from 'lucide-react'

import { cn } from '@/lib/utils'

export interface RadioCardOption {
  value: string
  icon?: React.ReactNode | ((props: { isSelected: boolean }) => React.ReactNode)
  label: string
  description?: string
  badge?: string
}

export interface RadioCardGroupProps {
  name: string
  value: string
  onValueChange: (value: string) => void
  options: RadioCardOption[]
  indicator?: 'radio' | 'check'
  variant?: 'list' | 'compact'
  className?: string
}

export function RadioCardGroup({
  name,
  value,
  onValueChange,
  options,
  indicator = 'radio',
  variant = 'list',
  className,
}: RadioCardGroupProps) {
  return (
    <div className={cn('grid gap-3', className)}>
      {options.map((option) => {
        const isSelected = value === option.value
        const resolvedIcon = typeof option.icon === 'function' ? option.icon({ isSelected }) : option.icon

        if (variant === 'compact') {
          return (
            <label
              key={option.value}
              className={cn(
                'relative flex flex-col items-center gap-2 rounded-lg border-2 p-4 cursor-pointer transition-all hover:bg-muted/50',
                isSelected ? 'border-primary bg-primary/5' : 'border-muted hover:border-muted-foreground/30'
              )}
            >
              <input
                type="radio"
                name={name}
                value={option.value}
                checked={isSelected}
                onChange={() => onValueChange(option.value)}
                className="sr-only"
              />
              {resolvedIcon}
              <span className="text-xs font-medium text-center leading-tight">{option.label}</span>
              {isSelected && <CheckCircle2 className="absolute top-2 right-2 h-4 w-4 text-primary" />}
            </label>
          )
        }

        return (
          <label
            key={option.value}
            className={cn(
              'relative flex items-start gap-4 rounded-lg border p-4 cursor-pointer transition-all text-left',
              isSelected ? 'border-primary bg-primary/5' : 'border-border hover:bg-muted/50'
            )}
          >
            <input
              type="radio"
              name={name}
              value={option.value}
              checked={isSelected}
              onChange={() => onValueChange(option.value)}
              className="sr-only"
            />
            {resolvedIcon}
            <div className="flex-1">
              <div className="flex items-center gap-2">
                <span className={cn('font-medium', isSelected && indicator === 'radio' && 'text-primary')}>
                  {option.label}
                </span>
                {option.badge && (
                  <span className="rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">
                    {option.badge}
                  </span>
                )}
              </div>
              {option.description && <p className="mt-1 text-sm text-muted-foreground">{option.description}</p>}
            </div>
            {indicator === 'radio' && (
              <div
                className={cn(
                  'mt-1 h-4 w-4 shrink-0 rounded-full border-2',
                  isSelected ? 'border-primary bg-primary' : 'border-muted-foreground/50'
                )}
              >
                {isSelected && (
                  <div className="flex h-full w-full items-center justify-center">
                    <div className="h-1.5 w-1.5 rounded-full bg-white" />
                  </div>
                )}
              </div>
            )}
            {indicator === 'check' && isSelected && (
              <CheckCircle2 className="absolute top-4 right-4 h-5 w-5 text-primary" />
            )}
          </label>
        )
      })}
    </div>
  )
}
