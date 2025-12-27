import { __ } from '@wordpress/i18n'
import { ChevronRight, Filter } from 'lucide-react'
import { useState } from 'react'

import type { Filter as AppliedFilter } from '@/components/custom/filter-bar'
import { FilterBar } from '@/components/custom/filter-bar'
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
} from '@/components/custom/filter-row'
import { Button } from '@/components/ui/button'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { WordPress } from '@/lib/wordpress'

export interface FilterPopoverProps {
  fields: FilterField[]
  appliedFilters: AppliedFilter[]
  onApplyFilters: (filters: AppliedFilter[]) => void
  className?: string
}

// Get operator display label from WordPress localized data
const getOperatorDisplay = (operator: FilterOperator): string => {
  try {
    const operators = WordPress.getInstance().getFilterOperators()
    if (operators[operator]?.label) {
      return operators[operator].label
    }
  } catch {
    // Fallback if WordPress context not available (e.g., in Storybook)
  }
  return getOperatorLabel(operator)
}

function FilterPopover({ fields, appliedFilters, onApplyFilters, className }: FilterPopoverProps) {
  const [open, setOpen] = useState(false)
  const [pendingFilters, setPendingFilters] = useState<FilterRowData[]>([])

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

    if (isRangeValue(value)) {
      return value.min !== '' || value.max !== ''
    }

    return getSingleValue(value) !== ''
  }

  // Convert applied filters back to FilterRowData when opening
  const handleOpenChange = (isOpen: boolean) => {
    if (isOpen) {
      // Convert applied filters to pending filters
      const converted: FilterRowData[] = appliedFilters.map((af) => {
        // Use rawOperator if available, otherwise use 'is' as default
        const operator = (af.rawOperator || 'is') as FilterOperator
        const operatorType = getOperatorType(operator)

        // Convert rawValue back to FilterValue based on operator type
        let value: FilterValue
        if (operatorType === 'range' && Array.isArray(af.rawValue) && af.rawValue.length === 2) {
          value = { min: af.rawValue[0] || '', max: af.rawValue[1] || '' }
        } else if (af.rawValue !== undefined) {
          value = af.rawValue
        } else {
          value = String(af.value)
        }

        return {
          id: af.id,
          fieldName: (af.id.split('-')[0] || af.id) as FilterFieldName,
          operator,
          value,
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
        let rawValue: string | string[]
        if (operatorType === 'range') {
          const range = getRangeValue(f.value)
          rawValue = [range.min, range.max]
        } else if (operatorType === 'multiple') {
          rawValue = getArrayValue(f.value)
        } else {
          if (isRangeValue(f.value)) {
            rawValue = f.value.min || f.value.max || ''
          } else {
            rawValue = Array.isArray(f.value) ? f.value[0] || '' : getSingleValue(f.value)
          }
        }

        // Get display value
        let displayValue: string
        if (operatorType === 'range') {
          const range = getRangeValue(f.value)
          displayValue = `${range.min} - ${range.max}`
        } else if (operatorType === 'multiple') {
          displayValue = getArrayValue(f.value).join(', ')
        } else {
          displayValue = getSingleValue(f.value)
        }

        return {
          id: `${f.fieldName}-${f.id}`,
          label: field?.label || f.fieldName,
          operator: getOperatorDisplay(f.operator),
          rawOperator: f.operator,
          value: displayValue,
          rawValue,
        }
      })

    onApplyFilters(newAppliedFilters)
    setOpen(false)
  }

  const handleRemoveAppliedFilter = (filterId: string) => {
    onApplyFilters(appliedFilters.filter((f) => f.id !== filterId))
  }

  const filterCount = appliedFilters.length

  return (
    <div className={className}>
      {/* Filter Button with Popover */}
      <div className="flex items-center gap-4">
        <Popover open={open} onOpenChange={handleOpenChange}>
          <PopoverTrigger asChild>
            <Button variant="outline" className="gap-2">
              <Filter className="h-4 w-4" />
              {__('Filters', 'wp-statistics')}
              {filterCount > 0 && (
                <span className="ml-1 rounded-full bg-primary px-1.5 py-0.5 text-xs text-primary-foreground">
                  {filterCount}
                </span>
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

        {/* Applied Filters Bar */}
        {appliedFilters.length > 0 && <FilterBar filters={appliedFilters} onRemoveFilter={handleRemoveAppliedFilter} />}
      </div>
    </div>
  )
}

export { FilterPopover, generateFilterId, getOperatorDisplay }
export type { FilterField, FilterRowData }
