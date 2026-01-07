import { __ } from '@wordpress/i18n'
import { Plus } from 'lucide-react'
import { useEffect } from 'react'

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
  onCancel?: () => void
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

function FilterPanel({ filters, fields, onFiltersChange, onApply, onClearAll, onCancel }: FilterPanelProps) {
  // Check if any filters have validation errors
  const hasErrors = hasFilterErrors(filters, fields)

  // Get list of all field names currently used by filters
  const usedFieldNames = filters.map((f) => f.fieldName)

  // Get available fields (not yet used in any filter)
  const availableFields = fields.filter((field) => !usedFieldNames.includes(field.name))

  // Add default empty filter when panel opens with no filters
  useEffect(() => {
    if (filters.length === 0 && fields.length > 0) {
      const defaultField = fields[0]
      const defaultOperator = defaultField.supportedOperators[0] || 'is'
      const newFilter: FilterRowData = {
        id: generateFilterId(),
        fieldName: defaultField.name,
        operator: defaultOperator,
        value: getInitialValue(defaultOperator),
      }
      onFiltersChange([newFilter])
    }
  }, []) // Only on mount

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
    <div className="w-full min-w-[520px]">
      {/* Header */}
      <div className="flex items-center justify-between px-4 py-2 border-b border-neutral-100 bg-neutral-50/50">
        <span className="text-sm font-semibold text-neutral-700 tracking-tight">{__('Filters', 'wp-statistics')}</span>
        {filters.length > 0 && (
          <button
            type="button"
            onClick={handleClearAll}
            aria-label={__('Clear all filters', 'wp-statistics')}
            className="text-xs text-neutral-500 hover:text-destructive transition-colors cursor-pointer"
          >
            {__('Clear all', 'wp-statistics')}
          </button>
        )}
      </div>

      {/* Filter Rows */}
      <div className="px-4 py-3">
        <div className="space-y-2">
          {filters.map((filter, index) => (
            <div key={filter.id} className="relative">
              {/* Row connector "and" label for multiple filters */}
              {index > 0 && (
                <div className="absolute -top-1.5 left-3 text-[10px] font-medium text-neutral-400 uppercase tracking-wider bg-white px-1">
                  {__('and', 'wp-statistics')}
                </div>
              )}
              <div className={index > 0 ? 'pt-2' : ''}>
                <FilterRow
                  filter={filter}
                  fields={fields}
                  usedFieldNames={getUsedFieldNamesForRow(filter.id)}
                  onUpdate={handleUpdateFilter}
                  onRemove={handleRemoveFilter}
                />
              </div>
            </div>
          ))}
        </div>

        {/* Add condition - only show if there are unused fields available */}
        {availableFields.length > 0 && (
          <button
            type="button"
            onClick={handleAddFilter}
            aria-label={__('Add filter condition', 'wp-statistics')}
            className="flex items-center gap-1.5 mt-3 py-1.5 text-xs font-medium text-neutral-500 hover:text-primary transition-colors group cursor-pointer"
          >
            <span className="flex items-center justify-center w-4 h-4 rounded-full border border-dashed border-neutral-300 group-hover:border-primary group-hover:bg-primary/5 transition-all">
              <Plus className="h-2.5 w-2.5" />
            </span>
            {__('Add condition', 'wp-statistics')}
          </button>
        )}
      </div>

      {/* Footer */}
      <div className="flex items-center justify-end gap-2 px-4 py-2 border-t border-neutral-100 bg-neutral-50/30">
        {onCancel && (
          <Button variant="ghost" size="sm" onClick={onCancel} className="text-xs">
            {__('Cancel', 'wp-statistics')}
          </Button>
        )}
        <Button size="sm" onClick={onApply} disabled={hasErrors} className="text-xs px-4">
          {__('Apply filters', 'wp-statistics')}
        </Button>
      </div>
    </div>
  )
}

export { FilterPanel, generateFilterId }
