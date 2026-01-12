import { __ } from '@wordpress/i18n'
import { Check } from 'lucide-react'

import type { QuickFilterDefinition } from '@/config/quick-filter-definitions'
import type { FilterRowData } from '@/components/custom/filter-row'
import { cn } from '@/lib/utils'

export interface QuickFiltersProps {
  definitions: QuickFilterDefinition[]
  activeFilters: FilterRowData[]
  onToggle: (definition: QuickFilterDefinition) => void
}

/**
 * Check if a quick filter is currently active in the filters array.
 * A quick filter is considered active if there's a filter with matching
 * fieldName, operator, and value.
 */
function isQuickFilterActive(definition: QuickFilterDefinition, filters: FilterRowData[]): boolean {
  return filters.some(
    (filter) =>
      filter.fieldName === definition.fieldName &&
      filter.operator === definition.operator &&
      String(filter.value) === definition.value
  )
}

/**
 * QuickFilters component renders a row of toggle chips for common filter presets.
 * Clicking a chip toggles the filter on/off.
 */
function QuickFilters({ definitions, activeFilters, onToggle }: QuickFiltersProps) {
  if (definitions.length === 0) {
    return null
  }

  return (
    <div className="flex flex-wrap gap-2">
      {definitions.map((definition) => {
        const isActive = isQuickFilterActive(definition, activeFilters)
        const Icon = definition.icon

        return (
          <button
            key={definition.id}
            type="button"
            onClick={() => onToggle(definition)}
            className={cn(
              'inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md border transition-all cursor-pointer',
              isActive
                ? 'bg-primary text-primary-foreground border-primary hover:bg-primary/90'
                : 'bg-white text-neutral-700 border-neutral-200 hover:bg-neutral-50 hover:border-neutral-300'
            )}
          >
            {isActive ? (
              <Check className="h-3 w-3" />
            ) : (
              <Icon className="h-3 w-3" />
            )}
            {definition.label}
          </button>
        )
      })}
    </div>
  )
}

export { QuickFilters, isQuickFilterActive }
