import { __ } from '@wordpress/i18n'
import { Plus } from 'lucide-react'

import {
  type FilterField,
  FilterRow,
  type FilterRowData,
  type FilterValue,
  getOperatorType,
  hasFilterErrors,
} from '@/components/custom/filter-row'
import { Button } from '@/components/ui/button'

export interface FilterPanelProps {
  filters: FilterRowData[]
  fields: FilterField[]
  onFiltersChange: (filters: FilterRowData[]) => void
  onApply: () => void
  onClearAll?: () => void
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

function FilterPanel({ filters, fields, onFiltersChange, onApply, onClearAll }: FilterPanelProps) {
  // Check if any filters have validation errors
  const hasErrors = hasFilterErrors(filters, fields)

  // Get list of all field names currently used by filters
  const usedFieldNames = filters.map((f) => f.fieldName)

  // Get available fields (not yet used in any filter)
  const availableFields = fields.filter((field) => !usedFieldNames.includes(field.name))

  const handleAddFilter = () => {
    // Don't add filter if no available fields
    if (availableFields.length === 0) return

    // Select the first available (unused) field
    const defaultField = availableFields[0]
    const defaultOperator = defaultField.supportedOperators[0] || 'is'
    const newFilter: FilterRowData = {
      id: generateFilterId(),
      fieldName: defaultField.name,
      operator: defaultOperator,
      value: getInitialValue(defaultOperator),
    }
    onFiltersChange([...filters, newFilter])
  }

  // Get used field names for a specific filter row (excludes the row's own field)
  const getUsedFieldNamesForRow = (filterId: string) => {
    return filters.filter((f) => f.id !== filterId).map((f) => f.fieldName)
  }

  const handleUpdateFilter = (updatedFilter: FilterRowData) => {
    onFiltersChange(filters.map((f) => (f.id === updatedFilter.id ? updatedFilter : f)))
  }

  const handleRemoveFilter = (id: string) => {
    onFiltersChange(filters.filter((f) => f.id !== id))
  }

  const handleClearAll = () => {
    onFiltersChange([])
    // If onClearAll is provided, call it to immediately apply the cleared filters
    if (onClearAll) {
      onClearAll()
    }
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
            usedFieldNames={getUsedFieldNamesForRow(filter.id)}
            onUpdate={handleUpdateFilter}
            onRemove={handleRemoveFilter}
          />
        ))}
      </div>

      {/* Add Another Condition - only show if there are unused fields available */}
      {availableFields.length > 0 && (
        <button
          type="button"
          onClick={handleAddFilter}
          className="flex items-center gap-1.5 text-sm text-primary hover:text-primary/80 mt-4 cursor-pointer"
        >
          <Plus className="h-4 w-4" />
          {__('Add another condition', 'wp-statistics')}
        </button>
      )}

      {/* Apply Button */}
      <div className="mt-4">
        <Button onClick={onApply} className="w-auto" disabled={hasErrors}>
          {__('Apply', 'wp-statistics')}
        </Button>
      </div>
    </div>
  )
}

export { FilterPanel, generateFilterId }
