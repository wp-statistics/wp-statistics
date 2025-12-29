import { Funnel } from 'lucide-react'
import * as React from 'react'

import { FilterChip } from '@/components/custom/filter-chip'
import { cn } from '@/lib/utils'

export interface Filter {
  id: string
  label: string
  operator: string // Display operator (e.g., "=", "!=")
  rawOperator?: string // Actual operator for API (e.g., "is", "is_not")
  value: string | number // Display value (labels for searchable filters)
  rawValue?: string | string[] // Actual value for API (e.g., "5" instead of "Firefox")
  valueLabels?: Record<string, string> // Maps rawValue to display label
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
        <Funnel className="h-4 w-4 stroke-neutral-400 fill-neutral-400" />
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
