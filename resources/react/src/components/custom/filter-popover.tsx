import { __ } from '@wordpress/i18n'
import { ChevronRight,Filter } from 'lucide-react'
import { useState } from 'react'

import type { Filter as AppliedFilter } from '@/components/custom/filter-bar'
import { FilterBar } from '@/components/custom/filter-bar'
import { FilterPanel, generateFilterId } from '@/components/custom/filter-panel'
import { type FilterField, type FilterOperator,type FilterRowData, operatorLabels } from '@/components/custom/filter-row'
import { Button } from '@/components/ui/button'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'

export interface FilterPopoverProps {
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

function FilterPopover({ fields, appliedFilters, onApplyFilters, className }: FilterPopoverProps) {
  const [open, setOpen] = useState(false)
  const [pendingFilters, setPendingFilters] = useState<FilterRowData[]>([])

  // Convert applied filters back to FilterRowData when opening
  const handleOpenChange = (isOpen: boolean) => {
    if (isOpen) {
      // Convert applied filters to pending filters
      const converted: FilterRowData[] = appliedFilters.map((af) => {
        // Find the operator key from the display value
        const operatorEntry = Object.entries(operatorDisplayMap).find(([, display]) => display === af.operator)
        const operatorKey = (operatorEntry?.[0] as FilterOperator) || 'equal_to'

        return {
          id: af.id,
          fieldId: af.id.split('-')[0] || af.id,
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
        {appliedFilters.length > 0 && (
          <FilterBar filters={appliedFilters} onRemoveFilter={handleRemoveAppliedFilter} />
        )}
      </div>
    </div>
  )
}

export { FilterPopover, generateFilterId, operatorDisplayMap, operatorLabels }
export type { FilterField, FilterOperator,FilterRowData }
