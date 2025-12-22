import { __ } from '@wordpress/i18n'
import { Plus } from 'lucide-react'

import {
  type FilterField,
  FilterRow,
  type FilterRowData,
  type FilterValue,
  getOperatorType,
} from '@/components/custom/filter-row'
import { Button } from '@/components/ui/button'

export interface FilterPanelProps {
  filters: FilterRowData[]
  fields: FilterField[]
  onFiltersChange: (filters: FilterRowData[]) => void
  onApply: () => void
}

function generateFilterId(): string {
  return `filter-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`
}

// Get initial value based on operator type
function getInitialValue(operator: FilterOperator): FilterValue {
  const operatorType = getOperatorType(operator)
  if (operatorType === 'range') {
    return { min: '', max: '' }
  }
  if (operatorType === 'multiple') {
    return []
  }
  return ''
}

function FilterPanel({ filters, fields, onFiltersChange, onApply }: FilterPanelProps) {
  const handleAddFilter = () => {
    const defaultField = fields[0]
    const defaultOperator = defaultField?.supportedOperators[0] || 'is'
    const newFilter: FilterRowData = {
      id: generateFilterId(),
      fieldName: defaultField?.name || ('country' as FilterFieldName),
      operator: defaultOperator,
      value: getInitialValue(defaultOperator),
    }
    onFiltersChange([...filters, newFilter])
  }

  const handleUpdateFilter = (updatedFilter: FilterRowData) => {
    onFiltersChange(filters.map((f) => (f.id === updatedFilter.id ? updatedFilter : f)))
  }

  const handleRemoveFilter = (id: string) => {
    onFiltersChange(filters.filter((f) => f.id !== id))
  }

  const handleClearAll = () => {
    onFiltersChange([])
  }

  return (
    <div className="w-full min-w-[500px] p-4">
      {/* Header */}
      <div className="flex items-center justify-between mb-4">
        <h3 className="text-base font-medium">{__('Filters', 'wp-statistics')}</h3>
        {filters.length > 0 && (
          <Button variant="outline" size="sm" onClick={handleClearAll}>
            {__('Clear All', 'wp-statistics')}
          </Button>
        )}
      </div>

      {/* Filter Rows */}
      <div className="space-y-3">
        {filters.map((filter) => (
          <FilterRow
            key={filter.id}
            filter={filter}
            fields={fields}
            onUpdate={handleUpdateFilter}
            onRemove={handleRemoveFilter}
          />
        ))}
      </div>

      {/* Add Another Condition */}
      <button
        type="button"
        onClick={handleAddFilter}
        className="flex items-center gap-1.5 text-sm text-primary hover:text-primary/80 mt-4 cursor-pointer"
      >
        <Plus className="h-4 w-4" />
        {__('Add another condition', 'wp-statistics')}
      </button>

      {/* Apply Button */}
      <div className="mt-4">
        <Button onClick={onApply} className="w-auto">
          {__('Apply', 'wp-statistics')}
        </Button>
      </div>
    </div>
  )
}

export { FilterPanel, generateFilterId }
