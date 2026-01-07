import { __ } from '@wordpress/i18n'
import { ChevronRight, Filter } from 'lucide-react'
import { useState } from 'react'

import type { Filter as AppliedFilter } from '@/components/custom/filter-bar'
import { FilterPanel, generateFilterId } from '@/components/custom/filter-panel'
import {
  type FilterField,
  type FilterRowData,
  type FilterValue,
  getArrayValue,
  getOperatorLabel,
  getOperatorType,
  getRangeValue,
  getSingleValue,
  isRangeValue,
  type RangeValue,
} from '@/components/custom/filter-row'
import { Button } from '@/components/ui/button'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { cn } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'

export interface FilterButtonProps {
  fields: FilterField[]
  appliedFilters: AppliedFilter[]
  onApplyFilters: (filters: AppliedFilter[]) => void
  className?: string
}

// Get display string for operator from WordPress localized data
const getOperatorDisplay = (operator: FilterOperator): string => {
  try {
    const operators = WordPress.getInstance().getFilterOperators()
    if (operators[operator]?.label) {
      return operators[operator].label
    }
  } catch {
    // Fallback if WordPress context not available (e.g., in Storybook)
  }
  // Fallback to getOperatorLabel from filter-row
  return getOperatorLabel(operator)
}

// Format value for display in filter chip
const formatValueForDisplay = (
  value: FilterValue,
  operator: FilterOperator,
  valueLabels?: Record<string, string>
): string => {
  const operatorType = getOperatorType(operator)

  // Helper to get display label for a value
  const getDisplayLabel = (val: string) => valueLabels?.[val] || val

  if (operatorType === 'range' && isRangeValue(value)) {
    return `${value.min} - ${value.max}`
  }

  if (operatorType === 'multiple' && Array.isArray(value)) {
    return value.map(getDisplayLabel).join(', ')
  }

  return getDisplayLabel(getSingleValue(value))
}

// Check if filter has a valid value
const hasValidValue = (value: FilterValue, operator: FilterOperator): boolean => {
  // Operators that don't require a value are always valid
  if (operator === 'is_null' || operator === 'is_not_null') {
    return true
  }

  const operatorType = getOperatorType(operator)

  if (operatorType === 'range') {
    const range = getRangeValue(value)
    return range.min !== '' || range.max !== ''
  }

  if (operatorType === 'multiple') {
    const arr = getArrayValue(value)
    return arr.length > 0
  }

  // For 'single' type, handle various value formats
  if (isRangeValue(value)) {
    // Value is RangeValue but operator is single - extract min or max
    return value.min !== '' || value.max !== ''
  }

  return getSingleValue(value) !== ''
}

function FilterButton({ fields, appliedFilters, onApplyFilters, className }: FilterButtonProps) {
  const [open, setOpen] = useState(false)
  const [pendingFilters, setPendingFilters] = useState<FilterRowData[]>([])

  // Convert applied filters back to FilterRowData when opening
  const handleOpenChange = (isOpen: boolean) => {
    if (isOpen) {
      // Convert applied filters to pending filters
      const converted: FilterRowData[] = appliedFilters.map((af) => {
        // Find the field that matches this filter's label
        const field = fields.find((f) => f.label === af.label)
        const fieldName = field?.name || (af.label.toLowerCase().replace(/\s+/g, '_') as FilterFieldName)

        // Use rawOperator if available, otherwise fall back to display operator
        const operator = (af.rawOperator || af.operator) as FilterOperator
        const operatorType = getOperatorType(operator)

        // Convert rawValue back to FilterValue based on operator type
        let value: FilterValue
        if (operatorType === 'range' && Array.isArray(af.rawValue) && af.rawValue.length === 2) {
          // Convert [min, max] array back to RangeValue
          value = { min: af.rawValue[0] || '', max: af.rawValue[1] || '' }
        } else if (af.rawValue !== undefined) {
          value = af.rawValue
        } else {
          value = String(af.value)
        }

        // Try to restore valueLabels from field options if not available
        let valueLabels = af.valueLabels
        if (!valueLabels && field?.options) {
          const rawVal = typeof value === 'string' ? value : Array.isArray(value) ? value : String(value)
          const values = Array.isArray(rawVal) ? rawVal : [rawVal]
          valueLabels = {}
          for (const val of values) {
            const option = field.options.find((o) => String(o.value) === val)
            if (option) {
              valueLabels[val] = option.label
            }
          }
          if (Object.keys(valueLabels).length === 0) {
            valueLabels = undefined
          }
        }

        return {
          id: af.id,
          fieldName,
          operator,
          value,
          valueLabels,
        }
      })
      setPendingFilters(converted.length > 0 ? converted : [])
    }
    setOpen(isOpen)
  }

  const handleApply = () => {
    // Convert pending filters to applied filters
    const newAppliedFilters: AppliedFilter[] = pendingFilters
      .filter((f) => hasValidValue(f.value, f.operator))
      .map((f) => {
        const field = fields.find((field) => field.name === f.fieldName)
        const operatorType = getOperatorType(f.operator)

        // Get raw value based on operator type for API
        // - 'range' (between) → [min, max] array
        // - 'multiple' (in, not_in) → string[]
        // - 'single' (is, gt, contains, etc.) → string
        let rawValue: string | string[]
        if (operatorType === 'range') {
          const range = getRangeValue(f.value)
          rawValue = [range.min, range.max]
        } else if (operatorType === 'multiple') {
          rawValue = getArrayValue(f.value)
        } else {
          // For 'single' type operators, extract the value properly
          // Handle case where value might be RangeValue (e.g., user switched operators)
          if (isRangeValue(f.value)) {
            rawValue = f.value.min || f.value.max || ''
          } else {
            rawValue = Array.isArray(f.value) ? f.value[0] || '' : getSingleValue(f.value)
          }
        }

        // Try to get labels from field options if valueLabels is not set
        let valueLabels = f.valueLabels
        if (!valueLabels && field?.options) {
          const values = Array.isArray(rawValue) ? rawValue : [rawValue]
          valueLabels = {}
          for (const val of values) {
            const option = field.options.find((o) => String(o.value) === val)
            if (option) {
              valueLabels[val] = option.label
            }
          }
          if (Object.keys(valueLabels).length === 0) {
            valueLabels = undefined
          }
        }

        return {
          id: `${f.fieldName}-${f.id}`,
          label: field?.label || f.fieldName,
          operator: getOperatorDisplay(f.operator),
          rawOperator: f.operator,
          value: formatValueForDisplay(f.value, f.operator, valueLabels),
          rawValue,
          valueLabels,
        }
      })

    onApplyFilters(newAppliedFilters)
    setOpen(false)
  }

  const handleClearAll = () => {
    // Immediately apply empty filters and close the popover
    onApplyFilters([])
    setOpen(false)
  }

  const filterCount = appliedFilters.length

  return (
    <Popover open={open} onOpenChange={handleOpenChange}>
      <PopoverTrigger asChild>
        <Button
          variant="outline"
          className={cn('h-8 text-xs border-neutral-200 hover:bg-neutral-50', { 'border-indigo-200 bg-indigo-50 text-primary': !!filterCount }, className)}
          size="sm"
        >
          <Filter className="h-3.5 w-3.5" />
          {__('Filters', 'wp-statistics')}
          {filterCount > 0 && (
            <span className="rounded-full bg-primary px-1.5 py-0.5 text-[10px] text-primary-foreground">{filterCount}</span>
          )}
          <ChevronRight className="h-3.5 w-3.5" />
        </Button>
      </PopoverTrigger>
      <PopoverContent className="w-auto p-0" align="start">
        <FilterPanel
          filters={pendingFilters}
          fields={fields}
          onFiltersChange={setPendingFilters}
          onApply={handleApply}
          onClearAll={handleClearAll}
        />
      </PopoverContent>
    </Popover>
  )
}

export { FilterButton, formatValueForDisplay, generateFilterId, getOperatorDisplay, getOperatorLabel, hasValidValue }
export type { FilterField, FilterRowData, FilterValue, RangeValue }
