import { useState, useEffect, useMemo } from 'react'
import { Trash2, Loader2 } from 'lucide-react'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { WordPress } from '@/lib/wordpress'
import { useQuery } from '@tanstack/react-query'
import { getSearchableFilterOptionsQueryOptions } from '@/services/filters/get-searchable-filter-options'
import { useDebounce } from '@/hooks/use-debounce'
import { __ } from '@wordpress/i18n'

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
}

export interface FilterRowProps {
  filter: FilterRowData
  fields: FilterField[]
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

function FilterRow({ filter, fields, onUpdate, onRemove }: FilterRowProps) {
  const selectedField = fields.find((f) => f.name === filter.fieldName)
  const availableOperators = selectedField?.supportedOperators || []
  const operatorType = getOperatorType(filter.operator)

  // State for searchable input
  const [searchTerm, setSearchTerm] = useState('')
  const debouncedSearchTerm = useDebounce(searchTerm, 300)

  // Query for searchable filter options
  const { data: searchResults, isLoading: isSearching } = useQuery({
    ...getSearchableFilterOptionsQueryOptions({
      filter: filter.fieldName,
      search: debouncedSearchTerm,
      limit: 20,
    }),
    enabled: selectedField?.inputType === 'searchable' && !!filter.fieldName,
  })

  const searchOptions = useMemo(() => {
    return searchResults?.data?.data?.options ?? []
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

    onUpdate({ ...filter, fieldName: fieldName as FilterFieldName, operator: newOperator, value: initialValue })
    setSearchTerm('')
  }

  const handleOperatorChange = (operator: string) => {
    const newOperatorType = getOperatorType(operator as FilterOperator)
    const currentOperatorType = operatorType

    // Reset value when operator type changes
    let newValue: FilterValue = filter.value
    if (newOperatorType !== currentOperatorType) {
      if (newOperatorType === 'range') {
        newValue = { min: '', max: '' }
      } else if (newOperatorType === 'multiple') {
        newValue = []
      } else {
        newValue = ''
      }
    }

    onUpdate({ ...filter, operator: operator as FilterOperator, value: newValue })
  }

  const handleSingleValueChange = (value: string) => {
    onUpdate({ ...filter, value })
  }

  const handleRangeValueChange = (field: 'min' | 'max', value: string) => {
    const currentRange = getRangeValue(filter.value)
    onUpdate({ ...filter, value: { ...currentRange, [field]: value } })
  }

  const handleMultipleValueChange = (value: string) => {
    const currentValues = getArrayValue(filter.value)
    if (currentValues.includes(value)) {
      onUpdate({ ...filter, value: currentValues.filter((v) => v !== value) })
    } else {
      onUpdate({ ...filter, value: [...currentValues, value] })
    }
  }

  const handleSearchableSelect = (value: string) => {
    if (operatorType === 'multiple') {
      handleMultipleValueChange(value)
    } else {
      handleSingleValueChange(value)
    }
    setSearchTerm('')
  }

  // Render range input (two inputs for min/max)
  const renderRangeInput = () => {
    const rangeValue = getRangeValue(filter.value)
    const inputType = selectedField?.inputType === 'number' ? 'number' : selectedField?.inputType === 'date' ? 'date' : 'text'

    return (
      <div className="flex items-center gap-1">
        <Input
          type={inputType}
          value={rangeValue.min}
          onChange={(e) => handleRangeValueChange('min', e.target.value)}
          placeholder={__('Min', 'wp-statistics')}
          className="w-[80px]"
        />
        <span className="text-muted-foreground">{__('to', 'wp-statistics')}</span>
        <Input
          type={inputType}
          value={rangeValue.max}
          onChange={(e) => handleRangeValueChange('max', e.target.value)}
          placeholder={__('Max', 'wp-statistics')}
          className="w-[80px]"
        />
      </div>
    )
  }

  // Render searchable input with dropdown
  const renderSearchableInput = () => {
    const currentValue = operatorType === 'multiple' ? getArrayValue(filter.value) : getSingleValue(filter.value)

    return (
      <div className="relative w-[200px]">
        <div className="relative">
          <Input
            type="text"
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            placeholder={operatorType === 'multiple' ? __('Search & select...', 'wp-statistics') : __('Search...', 'wp-statistics')}
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
              <span
                key={val}
                className="inline-flex items-center gap-1 rounded bg-primary/10 px-2 py-0.5 text-xs"
              >
                {val}
                <button
                  type="button"
                  onClick={() => handleMultipleValueChange(val)}
                  className="hover:text-destructive"
                >
                  ×
                </button>
              </span>
            ))}
          </div>
        )}

        {/* Display single selected value */}
        {operatorType !== 'multiple' && typeof currentValue === 'string' && currentValue && !searchTerm && (
          <div className="mt-1 text-xs text-muted-foreground">
            {__('Selected:', 'wp-statistics')} {currentValue}
          </div>
        )}

        {/* Search results dropdown */}
        {searchTerm && searchOptions.length > 0 && (
          <div className="absolute z-50 mt-1 max-h-[200px] w-full overflow-auto rounded-md border bg-popover p-1 shadow-md">
            {searchOptions.map((option) => (
              <button
                key={option.value}
                type="button"
                onClick={() => handleSearchableSelect(option.value)}
                className="flex w-full items-center rounded-sm px-2 py-1.5 text-sm hover:bg-accent hover:text-accent-foreground"
              >
                {operatorType === 'multiple' && Array.isArray(currentValue) && (
                  <span className="mr-2">
                    {currentValue.includes(option.value) ? '✓' : '○'}
                  </span>
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
          className="w-[160px]"
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
      case 'dropdown':
        return (
          <Select
            value={getSingleValue(filter.value)}
            onValueChange={handleSingleValueChange}
          >
            <SelectTrigger className="w-[160px]">
              <SelectValue placeholder={__('Select value', 'wp-statistics')} />
            </SelectTrigger>
            <SelectContent>
              {selectedField.options?.map((option) => (
                <SelectItem key={String(option.value)} value={String(option.value)}>
                  {option.label}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        )
      case 'number':
        return (
          <Input
            type="number"
            value={getSingleValue(filter.value)}
            onChange={(e) => handleSingleValueChange(e.target.value)}
            placeholder={__('Value', 'wp-statistics')}
            className="w-[160px]"
          />
        )
      case 'date':
        return (
          <Input
            type="date"
            value={getSingleValue(filter.value)}
            onChange={(e) => handleSingleValueChange(e.target.value)}
            placeholder={__('Select date', 'wp-statistics')}
            className="w-[160px]"
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
            className="w-[160px]"
          />
        )
    }
  }

  return (
    <div className="flex items-center gap-2">
      {/* Field Select */}
      <Select value={filter.fieldName} onValueChange={handleFieldChange}>
        <SelectTrigger className="w-[140px]">
          <SelectValue placeholder={__('Select field', 'wp-statistics')} />
        </SelectTrigger>
        <SelectContent>
          {fields.map((field) => (
            <SelectItem key={field.name} value={field.name}>
              {field.label}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>

      {/* Operator Select */}
      <Select value={filter.operator} onValueChange={handleOperatorChange}>
        <SelectTrigger className="w-[140px]">
          <SelectValue placeholder={__('Select operator', 'wp-statistics')} />
        </SelectTrigger>
        <SelectContent>
          {availableOperators.map((op) => (
            <SelectItem key={op} value={op}>
              {getOperatorLabel(op)}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>

      {/* Value Input */}
      {renderValueInput()}

      {/* Remove Button */}
      <Button variant="ghost" size="icon" onClick={() => onRemove(filter.id)} className="shrink-0">
        <Trash2 className="h-4 w-4" />
      </Button>
    </div>
  )
}

export { FilterRow, getOperatorLabel, getOperatorType, isRangeValue, getSingleValue, getArrayValue, getRangeValue }
