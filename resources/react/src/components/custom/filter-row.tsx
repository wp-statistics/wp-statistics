import { useQuery } from '@tanstack/react-query'
import { __ } from '@wordpress/i18n'
import { Loader2, Trash2 } from 'lucide-react'
import { useMemo, useState } from 'react'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { useDebounce } from '@/hooks/use-debounce'
import { WordPress } from '@/lib/wordpress'
import { getSearchableFilterOptionsQueryOptions } from '@/services/filters/get-searchable-filter-options'

// Local filter field type that matches the localized data structure
export interface FilterField {
  name: FilterFieldName
  label: string
  inputType: FilterInputType
  supportedOperators: FilterOperator[]
  groups: FilterGroup[]
  options?: FilterOption[]
}

// Range value type for 'between' operator
export interface RangeValue {
  min: string
  max: string
}

// Filter value can be single string, array of strings, or range
export type FilterValue = string | string[] | RangeValue

export interface FilterRowData {
  id: string
  fieldName: FilterFieldName
  operator: FilterOperator
  value: FilterValue
  valueLabels?: Record<string, string> // Maps value to label for searchable filters
}

export interface FilterRowProps {
  filter: FilterRowData
  fields: FilterField[]
  usedFieldNames?: FilterFieldName[] // Fields already used by other filters (excludes current row)
  onUpdate: (filter: FilterRowData) => void
  onRemove: (id: string) => void
}

// Get operator labels from localized data
const getOperatorLabel = (operator: FilterOperator): string => {
  const wp = WordPress.getInstance()
  const operators = wp.getFilterOperators()
  return operators[operator]?.label ?? operator
}

// Get operator type (single, multiple, range) from localized data
const getOperatorType = (operator: FilterOperator): OperatorType => {
  const wp = WordPress.getInstance()
  const operators = wp.getFilterOperators()
  return operators[operator]?.type ?? 'single'
}

// Check if value is a RangeValue
const isRangeValue = (value: FilterValue): value is RangeValue => {
  return typeof value === 'object' && !Array.isArray(value) && 'min' in value && 'max' in value
}

// Get single string value from FilterValue
const getSingleValue = (value: FilterValue): string => {
  if (typeof value === 'string') return value
  if (Array.isArray(value)) return value[0] || ''
  return ''
}

// Get array value from FilterValue
const getArrayValue = (value: FilterValue): string[] => {
  if (Array.isArray(value)) return value
  if (typeof value === 'string' && value) return [value]
  return []
}

// Get range value from FilterValue
const getRangeValue = (value: FilterValue): RangeValue => {
  if (isRangeValue(value)) return value
  return { min: '', max: '' }
}

function FilterRow({ filter, fields, usedFieldNames = [], onUpdate, onRemove }: FilterRowProps) {
  const selectedField = fields.find((f) => f.name === filter.fieldName)
  const availableOperators = selectedField?.supportedOperators || []
  const operatorType = getOperatorType(filter.operator)

  // Filter out fields that are already used by other filters
  // But always include the current field so it stays visible in the dropdown
  const availableFields = fields.filter(
    (field) => field.name === filter.fieldName || !usedFieldNames.includes(field.name)
  )

  // State for searchable input
  const [searchTerm, setSearchTerm] = useState('')
  const debouncedSearchTerm = useDebounce(searchTerm, 300)

  // Query for searchable filter options - only fetch when user is typing
  const { data: searchResults, isLoading: isSearching } = useQuery({
    ...getSearchableFilterOptionsQueryOptions({
      filter: filter.fieldName,
      search: debouncedSearchTerm,
      limit: 20,
    }),
    enabled: selectedField?.inputType === 'searchable' && !!filter.fieldName && debouncedSearchTerm.length > 0,
  })

  const searchOptions = useMemo(() => {
    return searchResults?.data?.options ?? []
  }, [searchResults])

  const handleFieldChange = (fieldName: string) => {
    const newField = fields.find((f) => f.name === fieldName)
    const newOperator = newField?.supportedOperators[0] || 'is'
    const newOperatorType = getOperatorType(newOperator)

    // Initialize value based on operator type
    let initialValue: FilterValue = ''
    if (newOperatorType === 'range') {
      initialValue = { min: '', max: '' }
    } else if (newOperatorType === 'multiple') {
      initialValue = []
    }

    onUpdate({
      ...filter,
      fieldName: fieldName as FilterFieldName,
      operator: newOperator,
      value: initialValue,
      valueLabels: undefined,
    })
    setSearchTerm('')
  }

  const handleOperatorChange = (operator: string) => {
    const newOperatorType = getOperatorType(operator as FilterOperator)
    const currentOperatorType = operatorType

    // Reset value when operator type changes
    let newValue: FilterValue = filter.value
    let newValueLabels = filter.valueLabels
    if (newOperatorType !== currentOperatorType) {
      if (newOperatorType === 'range') {
        newValue = { min: '', max: '' }
      } else if (newOperatorType === 'multiple') {
        newValue = []
      } else {
        newValue = ''
      }
      newValueLabels = undefined
    }

    onUpdate({ ...filter, operator: operator as FilterOperator, value: newValue, valueLabels: newValueLabels })
  }

  const handleSingleValueChange = (value: string, label?: string) => {
    if (label) {
      onUpdate({ ...filter, value, valueLabels: { [value]: label } })
    } else {
      onUpdate({ ...filter, value })
    }
  }

  const handleRangeValueChange = (field: 'min' | 'max', value: string) => {
    const currentRange = getRangeValue(filter.value)
    const newRange = { ...currentRange, [field]: value }
    onUpdate({ ...filter, value: newRange })
  }

  // Validate range values and return error message if invalid
  const getRangeError = (): string | null => {
    if (operatorType !== 'range') return null

    const range = getRangeValue(filter.value)
    if (range.min === '' || range.max === '') return null

    const inputType = selectedField?.inputType

    if (inputType === 'date') {
      const minDate = new Date(range.min)
      const maxDate = new Date(range.max)
      if (!isNaN(minDate.getTime()) && !isNaN(maxDate.getTime()) && minDate > maxDate) {
        return __('Start date must be before end date', 'wp-statistics')
      }
    } else if (inputType === 'number') {
      const minNum = parseFloat(range.min)
      const maxNum = parseFloat(range.max)
      if (!isNaN(minNum) && !isNaN(maxNum) && minNum > maxNum) {
        return __('Min value must be less than max value', 'wp-statistics')
      }
    }

    return null
  }

  const rangeError = getRangeError()

  const handleMultipleValueChange = (value: string) => {
    const currentValues = getArrayValue(filter.value)
    if (currentValues.includes(value)) {
      onUpdate({ ...filter, value: currentValues.filter((v) => v !== value) })
    } else {
      onUpdate({ ...filter, value: [...currentValues, value] })
    }
  }

  const handleSearchableSelect = (value: string, label: string) => {
    const currentLabels = filter.valueLabels || {}

    if (operatorType === 'multiple') {
      const currentValues = getArrayValue(filter.value)
      if (currentValues.includes(value)) {
        // Remove value and its label
        const newLabels = { ...currentLabels }
        delete newLabels[value]
        onUpdate({
          ...filter,
          value: currentValues.filter((v) => v !== value),
          valueLabels: newLabels,
        })
      } else {
        // Add value and its label
        onUpdate({
          ...filter,
          value: [...currentValues, value],
          valueLabels: { ...currentLabels, [value]: label },
        })
      }
    } else {
      onUpdate({
        ...filter,
        value,
        valueLabels: { [value]: label },
      })
    }
    setSearchTerm('')
  }

  // Render range input (two inputs for min/max)
  const renderRangeInput = () => {
    const rangeValue = getRangeValue(filter.value)
    const inputType =
      selectedField?.inputType === 'number' ? 'number' : selectedField?.inputType === 'date' ? 'date' : 'text'

    // Use wider inputs for date type to show full date (YYYY-MM-DD)
    const inputClassName = inputType === 'date' ? 'w-36' : 'w-20'
    const errorClassName = rangeError ? 'border-destructive focus-visible:ring-destructive' : ''

    return (
      <div className="flex flex-col gap-1">
        <div className="flex items-center gap-2">
          <Input
            type={inputType}
            value={rangeValue.min}
            onChange={(e) => handleRangeValueChange('min', e.target.value)}
            placeholder={__('Min', 'wp-statistics')}
            className={`${inputClassName} ${errorClassName} grow`}
          />
          <span className="text-muted-foreground pt-2">{__('to', 'wp-statistics')}</span>
          <Input
            type={inputType}
            value={rangeValue.max}
            onChange={(e) => handleRangeValueChange('max', e.target.value)}
            placeholder={__('Max', 'wp-statistics')}
            className={`${inputClassName} ${errorClassName} grow`}
          />
        </div>
        {rangeError && <span className="text-xs text-destructive">{rangeError}</span>}
      </div>
    )
  }

  // Render searchable input with dropdown
  const renderSearchableInput = () => {
    const currentValue = operatorType === 'multiple' ? getArrayValue(filter.value) : getSingleValue(filter.value)
    const valueLabels = filter.valueLabels || {}

    // Helper to get display label for a value
    const getDisplayLabel = (val: string) => valueLabels[val] || val

    // For single select, show the selected label in input when not searching
    const singleSelectedLabel =
      operatorType !== 'multiple' && typeof currentValue === 'string' && currentValue
        ? getDisplayLabel(currentValue)
        : ''

    return (
      <div className="relative min-w-[150px] flex-1">
        <div className="relative">
          <Input
            type="text"
            value={searchTerm || singleSelectedLabel}
            onChange={(e) => setSearchTerm(e.target.value)}
            onFocus={() => {
              // Clear input to allow searching when focused (only for single select with existing value)
              if (singleSelectedLabel && !searchTerm) {
                setSearchTerm('')
              }
            }}
            placeholder={
              operatorType === 'multiple' ? __('Search & select...', 'wp-statistics') : __('Search...', 'wp-statistics')
            }
            className="w-full pr-8"
          />
          {isSearching && (
            <Loader2 className="absolute right-2 top-1/2 h-4 w-4 -translate-y-1/2 animate-spin text-muted-foreground" />
          )}
        </div>

        {/* Display selected values for multiple */}
        {operatorType === 'multiple' && Array.isArray(currentValue) && currentValue.length > 0 && (
          <div className="mt-1 flex flex-wrap gap-1">
            {currentValue.map((val) => (
              <span key={val} className="inline-flex items-center gap-1 rounded bg-primary/10 px-2 py-0.5 text-xs">
                {getDisplayLabel(val)}
                <button
                  type="button"
                  onClick={() => handleSearchableSelect(val, getDisplayLabel(val))}
                  className="hover:text-destructive"
                >
                  ×
                </button>
              </span>
            ))}
          </div>
        )}

        {/* Search results dropdown */}
        {searchTerm && searchOptions.length > 0 && (
          <div className="absolute z-50 mt-1 max-h-[200px] min-w-full w-max overflow-auto rounded-md border bg-popover p-1 shadow-md">
            {searchOptions.map((option) => (
              <button
                key={option.value}
                type="button"
                onClick={() => handleSearchableSelect(option.value, option.label)}
                className="flex w-full items-center whitespace-nowrap rounded-sm px-2 py-1.5 text-sm text-left hover:bg-accent hover:text-accent-foreground"
              >
                {operatorType === 'multiple' && Array.isArray(currentValue) && (
                  <span className="mr-2">{currentValue.includes(option.value) ? '✓' : '○'}</span>
                )}
                {option.label}
              </button>
            ))}
          </div>
        )}

        {/* No results message */}
        {searchTerm && !isSearching && searchOptions.length === 0 && (
          <div className="absolute z-50 mt-1 w-full rounded-md border bg-popover p-2 text-sm text-muted-foreground shadow-md">
            {__('No results found', 'wp-statistics')}
          </div>
        )}
      </div>
    )
  }

  const renderValueInput = () => {
    if (!selectedField) {
      return (
        <Input
          type="text"
          value={getSingleValue(filter.value)}
          onChange={(e) => handleSingleValueChange(e.target.value)}
          placeholder={__('Value', 'wp-statistics')}
          className="min-w-[150px] flex-1"
        />
      )
    }

    // Handle range operator type first (regardless of input type)
    if (operatorType === 'range') {
      return renderRangeInput()
    }

    // Handle searchable input type
    if (selectedField.inputType === 'searchable') {
      return renderSearchableInput()
    }

    // Handle based on input type
    switch (selectedField.inputType) {
      case 'dropdown': {
        const handleDropdownChange = (value: string) => {
          const option = selectedField.options?.find((o) => String(o.value) === value)
          handleSingleValueChange(value, option?.label)
        }
        return (
          <Select value={getSingleValue(filter.value)} onValueChange={handleDropdownChange}>
            <SelectTrigger className="min-w-[150px] flex-1">
              <SelectValue placeholder={__('Select value', 'wp-statistics')} />
            </SelectTrigger>
            <SelectContent className="max-h-[200px] overflow-y-auto">
              {selectedField.options?.map((option) => (
                <SelectItem key={String(option.value)} value={String(option.value)}>
                  {option.label}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        )
      }
      case 'number':
        return (
          <Input
            type="number"
            value={getSingleValue(filter.value)}
            onChange={(e) => handleSingleValueChange(e.target.value)}
            placeholder={__('Value', 'wp-statistics')}
            className="min-w-[150px] flex-1"
          />
        )
      case 'date':
        return (
          <Input
            type="date"
            value={getSingleValue(filter.value)}
            onChange={(e) => handleSingleValueChange(e.target.value)}
            placeholder={__('Select date', 'wp-statistics')}
            className="min-w-[150px] flex-1"
          />
        )
      case 'text':
      default:
        return (
          <Input
            type="text"
            value={getSingleValue(filter.value)}
            onChange={(e) => handleSingleValueChange(e.target.value)}
            placeholder={__('Value', 'wp-statistics')}
            className="min-w-[150px] flex-1"
          />
        )
    }
  }

  return (
    <div className="flex items-start gap-2">
      {/* Field Select */}
      <Select value={filter.fieldName} onValueChange={handleFieldChange}>
        <SelectTrigger className="min-w-[120px] flex-1">
          <SelectValue placeholder={__('Select field', 'wp-statistics')} />
        </SelectTrigger>
        <SelectContent className="max-h-[200px] overflow-y-auto">
          {availableFields.map((field) => (
            <SelectItem key={field.name} value={field.name}>
              {field.label}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>

      {/* Operator Select */}
      <Select value={filter.operator} onValueChange={handleOperatorChange}>
        <SelectTrigger className="min-w-[120px] flex-1">
          <SelectValue placeholder={__('Select operator', 'wp-statistics')} />
        </SelectTrigger>
        <SelectContent className="max-h-[200px] overflow-y-auto">
          {availableOperators.map((op) => (
            <SelectItem key={op} value={op}>
              {getOperatorLabel(op)}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>

      {/* Value Input - hide for operators that don't need a value (is_null, is_not_null) */}
      {filter.operator !== 'is_null' && filter.operator !== 'is_not_null' && renderValueInput()}

      {/* Remove Button */}
      <Button variant="ghost" size="icon" onClick={() => onRemove(filter.id)} className="shrink-0">
        <Trash2 className="h-4 w-4" />
      </Button>
    </div>
  )
}

// Validate a filter for range errors (can be used by parent components)
const validateFilterRange = (filter: FilterRowData, fields: FilterField[]): string | null => {
  const operatorType = getOperatorType(filter.operator)
  if (operatorType !== 'range') return null

  const range = getRangeValue(filter.value)
  if (range.min === '' || range.max === '') return null

  const field = fields.find((f) => f.name === filter.fieldName)
  const inputType = field?.inputType

  if (inputType === 'date') {
    const minDate = new Date(range.min)
    const maxDate = new Date(range.max)
    if (!isNaN(minDate.getTime()) && !isNaN(maxDate.getTime()) && minDate > maxDate) {
      return 'date_range_error'
    }
  } else if (inputType === 'number') {
    const minNum = parseFloat(range.min)
    const maxNum = parseFloat(range.max)
    if (!isNaN(minNum) && !isNaN(maxNum) && minNum > maxNum) {
      return 'number_range_error'
    }
  }

  return null
}

// Check if any filters have validation errors
const hasFilterErrors = (filters: FilterRowData[], fields: FilterField[]): boolean => {
  return filters.some((filter) => validateFilterRange(filter, fields) !== null)
}

export {
  FilterRow,
  getArrayValue,
  getOperatorLabel,
  getOperatorType,
  getRangeValue,
  getSingleValue,
  hasFilterErrors,
  isRangeValue,
  validateFilterRange,
}
