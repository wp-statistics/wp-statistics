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

export interface FilterButtonProps {
  fields: FilterField[]
  appliedFilters: AppliedFilter[]
  onApplyFilters: (filters: AppliedFilter[]) => void
  className?: string
}

// Get display string for operator
const getOperatorDisplay = (operator: FilterOperator): string => {
  const displayMap: Partial<Record<FilterOperator, string>> = {
    gt: '>',
    gte: '>=',
    lt: '<',
    lte: '<=',
    is: '=',
    is_not: '!=',
    between: __('between', 'wp-statistics'),
    in: __('in', 'wp-statistics'),
    not_in: __('not in', 'wp-statistics'),
  }
  return displayMap[operator] ?? getOperatorLabel(operator)
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
  const operatorType = getOperatorType(operator)

  if (operatorType === 'range') {
    const range = getRangeValue(value)
    return range.min !== '' || range.max !== ''
  }

  if (operatorType === 'multiple') {
    const arr = getArrayValue(value)
    return arr.length > 0
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

        // Use rawValue if available, otherwise fall back to display value
        const value = af.rawValue !== undefined ? af.rawValue : String(af.value)
        // Use rawOperator if available, otherwise fall back to display operator
        const operator = (af.rawOperator || af.operator) as FilterOperator

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
        // Get raw value (actual value for API)
        const rawValue = Array.isArray(f.value) ? f.value : getSingleValue(f.value)

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

  const filterCount = appliedFilters.length

  return (
    <Popover open={open} onOpenChange={handleOpenChange}>
      <PopoverTrigger asChild>
        <Button
          variant="outline"
          className={cn({ 'border-indigo-200 bg-indigo-50 text-primary': !!filterCount }, className)}
          size="lg"
        >
          <Filter className="h-4 w-4" />
          {__('Filters', 'wp-statistics')}
          {filterCount > 0 && (
            <span className="rounded-full bg-primary px-1.5 py-0.5 text-xs text-primary-foreground">{filterCount}</span>
          )}
          <ChevronRight className="h-4 w-4" />
        </Button>
      </PopoverTrigger>
      <PopoverContent className="w-auto p-0" align="start">
        <FilterPanel
          filters={pendingFilters}
          fields={fields}
          onFiltersChange={setPendingFilters}
          onApply={handleApply}
        />
      </PopoverContent>
    </Popover>
  )
}

export { FilterButton, formatValueForDisplay, generateFilterId, getOperatorDisplay, getOperatorLabel, hasValidValue }
export type { FilterField, FilterRowData, FilterValue, RangeValue }
