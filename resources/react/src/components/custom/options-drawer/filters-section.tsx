import { __ } from '@wordpress/i18n'
import { FilterIcon, Plus } from 'lucide-react'
import { useEffect,useMemo, useState } from 'react'

import type { Filter as AppliedFilter } from '@/components/custom/filter-bar'
import {
  type LockedFilter,
} from '@/components/custom/filter-panel'
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
import { QuickFilters } from '@/components/custom/quick-filters'
import { Button } from '@/components/ui/button'
import { getQuickFiltersForGroup, type QuickFilterDefinition } from '@/config/quick-filter-definitions'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { cn } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'

import { DrawerFilterRow } from './drawer-filter-row'
import { OptionsMenuItem,useOptionsDrawer } from './options-drawer'

interface FiltersSectionProps {
  filterGroup?: string
  lockedFilters?: LockedFilter[]
}

// Generate unique filter ID
function generateFilterId(): string {
  return `filter-${Date.now()}-${Math.random().toString(36).substring(2, 9)}`
}

// Get display string for operator from WordPress localized data
const getOperatorDisplay = (operator: FilterOperator): string => {
  try {
    const operators = WordPress.getInstance().getFilterOperators()
    if (operators[operator]?.label) {
      return operators[operator].label
    }
  } catch {
    // Fallback if WordPress context not available
  }
  return getOperatorLabel(operator)
}

// Format value for display
const formatValueForDisplay = (
  value: FilterValue,
  operator: FilterOperator,
  valueLabels?: Record<string, string>
): string => {
  const operatorType = getOperatorType(operator)
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

export function FiltersMenuEntry({ filterGroup = 'visitors', lockedFilters }: FiltersSectionProps) {
  const { filters: appliedFilters } = useGlobalFilters()
  const { currentView, setCurrentView } = useOptionsDrawer()

  const wp = WordPress.getInstance()
  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup(filterGroup) as FilterField[]
  }, [wp, filterGroup])

  if (currentView !== 'main' || filterFields.length === 0) {
    return null
  }

  // Only count filters that belong to this page's filterGroup
  const relevantFilters = appliedFilters?.filter((af) => {
    return filterFields.some((f) => f.label === af.label)
  }) || []
  const filterCount = relevantFilters.length + (lockedFilters?.length || 0)
  const summary = filterCount > 0 ? `${filterCount} ${__('applied', 'wp-statistics')}` : undefined

  return (
    <OptionsMenuItem
      icon={<FilterIcon className="h-4 w-4" />}
      title={__('Filters', 'wp-statistics')}
      summary={summary}
      onClick={() => setCurrentView('filters')}
    />
  )
}

export function FiltersDetailView({ filterGroup = 'visitors', lockedFilters = [] }: FiltersSectionProps) {
  const { filters: appliedFilters, applyFilters } = useGlobalFilters()
  const [pendingFilters, setPendingFilters] = useState<FilterRowData[]>([])
  const { currentView, goBack } = useOptionsDrawer()

  const wp = WordPress.getInstance()
  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup(filterGroup) as FilterField[]
  }, [wp, filterGroup])

  // Get quick filter definitions for this group
  const quickFilterDefinitions = filterGroup ? getQuickFiltersForGroup(filterGroup) : []

  // Convert applied filters to pending filters when view opens
  useEffect(() => {
    if (currentView === 'filters' && appliedFilters) {
      const converted: FilterRowData[] = appliedFilters
        // Only include filters that belong to this page's filterGroup
        .filter((af) => {
          return filterFields.some((f) => f.label === af.label)
        })
        .map((af) => {
        const field = filterFields.find((f) => f.label === af.label)
        const fieldName = field?.name || (af.label.toLowerCase().replace(/\s+/g, '_') as FilterFieldName)
        const operator = (af.rawOperator || af.operator) as FilterOperator
        const operatorType = getOperatorType(operator)

        let value: FilterValue
        if (operatorType === 'range' && Array.isArray(af.rawValue) && af.rawValue.length === 2) {
          value = { min: af.rawValue[0] || '', max: af.rawValue[1] || '' }
        } else if (af.rawValue !== undefined) {
          value = af.rawValue
        } else {
          value = String(af.value)
        }

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
  }, [currentView, appliedFilters, filterFields])

  if (currentView !== 'filters' || filterFields.length === 0) {
    return null
  }

  // Get list of all field names currently used by filters
  const usedFieldNames = pendingFilters.map((f) => f.fieldName)

  // Get available fields (not yet used in any filter)
  const availableFields = filterFields.filter((field) => !usedFieldNames.includes(field.name))

  const handleAddFilter = () => {
    if (availableFields.length === 0) return

    const defaultField = availableFields[0]
    const defaultOperator = defaultField.supportedOperators[0] || 'is'
    const newFilter: FilterRowData = {
      id: generateFilterId(),
      fieldName: defaultField.name,
      operator: defaultOperator,
      value: getInitialValue(defaultOperator),
    }
    setPendingFilters([...pendingFilters, newFilter])
  }

  const handleUpdateFilter = (updatedFilter: FilterRowData) => {
    setPendingFilters(pendingFilters.map((f) => (f.id === updatedFilter.id ? updatedFilter : f)))
  }

  const handleRemoveFilter = (id: string) => {
    setPendingFilters(pendingFilters.filter((f) => f.id !== id))
  }

  const handleQuickFilterToggle = (definition: QuickFilterDefinition) => {
    const existingIndex = pendingFilters.findIndex(
      (filter) =>
        filter.fieldName === definition.fieldName &&
        filter.operator === definition.operator &&
        String(filter.value) === definition.value
    )

    if (existingIndex >= 0) {
      setPendingFilters(pendingFilters.filter((_, index) => index !== existingIndex))
    } else {
      const newFilter: FilterRowData = {
        id: generateFilterId(),
        fieldName: definition.fieldName,
        operator: definition.operator,
        value: definition.value,
        valueLabels: definition.valueLabel ? { [definition.value]: definition.valueLabel } : undefined,
      }
      setPendingFilters([...pendingFilters, newFilter])
    }
  }

  const handleApply = () => {
    const newAppliedFilters: AppliedFilter[] = pendingFilters
      .filter((f) => hasValidValue(f.value, f.operator))
      .map((f) => {
        const field = filterFields.find((field) => field.name === f.fieldName)
        const operatorType = getOperatorType(f.operator)

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

    applyFilters(newAppliedFilters)
    goBack()
  }

  const handleClearAll = () => {
    applyFilters([])
    setPendingFilters([])
    goBack()
  }

  // Get used field names for a specific filter row (excludes the row's own field)
  const getUsedFieldNamesForRow = (filterId: string) => {
    return pendingFilters.filter((f) => f.id !== filterId).map((f) => f.fieldName)
  }

  return (
    <div className="flex flex-col h-full">
      {/* Quick Filters */}
      {quickFilterDefinitions.length > 0 && (
        <div className="px-4 py-3 border-b border-neutral-100">
          <span className="text-xs font-medium text-neutral-500 mb-2 block">
            {__('Quick filters', 'wp-statistics')}
          </span>
          <QuickFilters
            definitions={quickFilterDefinitions}
            activeFilters={pendingFilters}
            onToggle={handleQuickFilterToggle}
            lockedFilters={lockedFilters}
          />
        </div>
      )}

      {/* Filter Rows */}
      <div className="flex-1 overflow-y-auto px-4 py-3">
        <div className="space-y-3">
          {/* Locked Filters */}
          {lockedFilters.map((lockedFilter) => (
            <div
              key={lockedFilter.id}
              className="flex items-center gap-2 p-2.5 rounded-lg bg-neutral-100 border border-neutral-200 text-sm"
            >
              <span className="text-neutral-600 font-medium">{lockedFilter.label}</span>
              <span className="text-neutral-500">{lockedFilter.operator}</span>
              <span className="text-neutral-600">{lockedFilter.value}</span>
              <span className="ml-auto text-neutral-500 text-xs">{__('Locked', 'wp-statistics')}</span>
            </div>
          ))}

          {/* Editable Filters */}
          {pendingFilters.map((filter, index) => (
            <div key={filter.id} className="relative">
              {(index > 0 || lockedFilters.length > 0) && (
                <div className="text-xs font-medium text-neutral-500 mb-1.5 ml-1">
                  {__('and', 'wp-statistics')}
                </div>
              )}
              <DrawerFilterRow
                filter={filter}
                fields={filterFields}
                usedFieldNames={getUsedFieldNamesForRow(filter.id)}
                onUpdate={handleUpdateFilter}
                onRemove={() => handleRemoveFilter(filter.id)}
              />
            </div>
          ))}

          {/* Empty state */}
          {pendingFilters.length === 0 && lockedFilters.length === 0 && (
            <div className="text-center py-6">
              <p className="text-sm text-neutral-500 mb-3">
                {__('No filters applied', 'wp-statistics')}
              </p>
            </div>
          )}
        </div>

        {/* Add filter button */}
        {availableFields.length > 0 && (
          <button
            type="button"
            onClick={handleAddFilter}
            className={cn(
              'flex items-center gap-2 mt-4 py-2 text-sm text-neutral-500 cursor-pointer',
              'hover:text-primary transition-colors group'
            )}
          >
            <span className="flex items-center justify-center w-5 h-5 rounded-full border border-dashed border-neutral-300 group-hover:border-primary transition-colors">
              <Plus className="h-3 w-3" />
            </span>
            {__('Add filter', 'wp-statistics')}
          </button>
        )}
      </div>

      {/* Footer */}
      <div className="flex items-center justify-between gap-2 px-4 py-3 border-t border-neutral-100 bg-neutral-50/50 shrink-0">
        <button
          type="button"
          onClick={handleClearAll}
          className={cn(
            'text-xs text-neutral-500 hover:text-destructive transition-colors cursor-pointer',
            pendingFilters.length === 0 && 'invisible'
          )}
        >
          {__('Clear all', 'wp-statistics')}
        </button>
        <Button size="sm" onClick={handleApply} className="text-xs px-4">
          {__('Apply filters', 'wp-statistics')}
        </Button>
      </div>
    </div>
  )
}
