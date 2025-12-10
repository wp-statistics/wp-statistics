import { useState } from 'react'
import { Filter, ChevronRight } from 'lucide-react'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { Button } from '@/components/ui/button'
import { FilterPanel, generateFilterId } from '@/components/custom/filter-panel'
import {
  operatorLabels,
  type FilterField,
  type FilterRowData,
  type FilterOperator,
} from '@/components/custom/filter-row'
import type { Filter as AppliedFilter } from '@/components/custom/filter-bar'
import { __ } from '@wordpress/i18n'
import { cn } from '@/lib/utils'

export interface FilterButtonProps {
  fields: FilterField[]
  appliedFilters: AppliedFilter[]
  onApplyFilters: (filters: AppliedFilter[]) => void
  className?: string
}

// Map internal operator to display operator
const operatorDisplayMap: Record<FilterOperator, string> = {
  greater_than: '>',
  less_than: '<',
  equal_to: '=',
  not_equal: '!=',
  contains: 'Contains',
  is: 'Is',
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
        const fieldId = field?.id || af.label.toLowerCase().replace(/\s+/g, '-')

        // Find the operator key from the display value
        const operatorEntry = Object.entries(operatorDisplayMap).find(([, display]) => display === af.operator)
        const operatorKey = (operatorEntry?.[0] as FilterOperator) || 'equal_to'

        return {
          id: af.id,
          fieldId,
          operator: operatorKey,
          value: String(af.value),
        }
      })
      setPendingFilters(converted.length > 0 ? converted : [])
    }
    setOpen(isOpen)
  }

  const handleApply = () => {
    // Convert pending filters to applied filters
    const newAppliedFilters: AppliedFilter[] = pendingFilters
      .filter((f) => f.value !== '') // Only include filters with values
      .map((f) => {
        const field = fields.find((field) => field.id === f.fieldId)
        return {
          id: `${f.fieldId}-${f.id}`,
          label: field?.label || f.fieldId,
          operator: operatorDisplayMap[f.operator] as AppliedFilter['operator'],
          value: f.value,
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

export { FilterButton, generateFilterId, operatorDisplayMap, operatorLabels }
export type { FilterField, FilterRowData, FilterOperator }
