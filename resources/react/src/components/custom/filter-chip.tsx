import { X } from 'lucide-react'
import { cn } from '@/lib/utils'

export interface FilterChipProps {
  label: string
  operator: string
  value: string | number
  onRemove: () => void
  className?: string
}

function FilterChip({ label, operator, value, onRemove, className }: FilterChipProps) {
  return (
    <div
      className={cn(
        'inline-flex items-center gap-1.5 rounded-sm bg-chip px-2.5 py-2 text-xs text-secondary-foreground font-normal border border-input hover:bg-chip/80',
        className
      )}
    >
      <span>{label}</span>
      <span className="text-slate-400">|</span>
      <span className="font-medium">{operator}</span>
      <span className="font-medium">{value}</span>
      <button
        type="button"
        onClick={onRemove}
        className="flex h-4 w-4 items-center justify-center rounded-full text-secondary-foreground cursor-pointer"
        aria-label={`Remove ${label} filter`}
      >
        <X className="h-3 w-3" />
      </button>
    </div>
  )
}

export { FilterChip }
