import { __ } from '@wordpress/i18n'
import { Check, Lock } from 'lucide-react'

import type { LockedFilter } from '@/components/custom/filter-panel'
import type { FilterRowData } from '@/components/custom/filter-row'
import type { QuickFilterDefinition } from '@/config/quick-filter-definitions'
import { cn } from '@/lib/utils'

export interface QuickFiltersProps {
  definitions: QuickFilterDefinition[]
  activeFilters: FilterRowData[]
  onToggle: (definition: QuickFilterDefinition) => void
  /** Locked filters - quick filters matching these will show as always-on locked */
  lockedFilters?: LockedFilter[]
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
 * Check if a quick filter matches a locked filter.
 * Matches are based on the filter field name (e.g., 'logged_in').
 */
function isQuickFilterLocked(definition: QuickFilterDefinition, lockedFilters: LockedFilter[]): boolean {
  // Match by comparing the quick filter's fieldName with locked filter patterns
  // The locked filter's label corresponds to the field, but we need to match by field name
  // For now, we match if the quick filter's value matches the locked filter's value
  // and they represent the same concept (e.g., logged_in = 1 = "Logged-in")
  return lockedFilters.some((locked) => {
    // Match by field name pattern - the quick filter fieldName should match the locked concept
    // For logged_in field: valueLabel "Logged-in" matches locked value "Logged-in"
    return definition.valueLabel === String(locked.value)
  })
}

/**
 * QuickFilters component renders a row of toggle chips for common filter presets.
 * Clicking a chip toggles the filter on/off.
 * Quick filters that match locked filters are shown as always-on with a lock icon.
 */
function QuickFilters({ definitions, activeFilters, onToggle, lockedFilters = [] }: QuickFiltersProps) {
  if (definitions.length === 0) {
    return null
  }

  return (
    <div className="flex flex-wrap gap-2">
      {definitions.map((definition) => {
        const isLocked = isQuickFilterLocked(definition, lockedFilters)
        const isActive = isLocked || isQuickFilterActive(definition, activeFilters)
        const Icon = definition.icon

        return (
          <button
            key={definition.id}
            type="button"
            onClick={() => !isLocked && onToggle(definition)}
            disabled={isLocked}
            title={isLocked ? __('This filter is always applied to this report', 'wp-statistics') : undefined}
            className={cn(
              'inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-md border transition-all',
              isLocked
                ? 'bg-neutral-100 text-neutral-500 border-neutral-200 cursor-default'
                : isActive
                  ? 'bg-primary text-primary-foreground border-primary hover:bg-primary/90 cursor-pointer'
                  : 'bg-white text-neutral-700 border-neutral-200 hover:bg-neutral-50 hover:border-neutral-300 cursor-pointer'
            )}
          >
            {isLocked ? (
              <Lock className="h-3 w-3" />
            ) : isActive ? (
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

export { isQuickFilterActive,QuickFilters }
