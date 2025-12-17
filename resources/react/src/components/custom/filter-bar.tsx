import * as React from 'react'
import { FilterChip } from '@/components/custom/filter-chip'
import { cn } from '@/lib/utils'
import { Funnel } from 'lucide-react'

export interface Filter {
  id: string
  label: string
  operator: string
  value: string | number
}

export interface FilterBarProps {
  filters: Filter[]
  onRemoveFilter: (filterId: string) => void
  className?: string
}

function FilterBar({ filters, onRemoveFilter, className }: FilterBarProps) {
  if (filters.length === 0) {
    return null
  }

  return (
    <div className={cn('flex flex-wrap items-center gap-2', className)}>
      <div className="shrink-0">
        <Funnel className="stroke-muted-foreground fill-muted-foreground w-5 h-5" />
      </div>
      {filters.map((filter) => (
        <FilterChip
          key={filter.id}
          label={filter.label}
          operator={filter.operator}
          value={filter.value}
          onRemove={() => onRemoveFilter(filter.id)}
        />
      ))}
    </div>
  )
}

export { FilterBar }
