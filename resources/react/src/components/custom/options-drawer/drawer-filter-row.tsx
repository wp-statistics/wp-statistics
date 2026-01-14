import { useQuery } from '@tanstack/react-query'
import { __ } from '@wordpress/i18n'
import { Loader2, Trash2Icon } from 'lucide-react'
import { useMemo, useState } from 'react'

import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { useDebounce } from '@/hooks/use-debounce'
import { WordPress } from '@/lib/wordpress'
import { cn } from '@/lib/utils'
import { getSearchableFilterOptionsQueryOptions } from '@/services/filters/get-searchable-filter-options'
import {
  type FilterField,
  type FilterRowData,
  type FilterValue,
  type RangeValue,
  getArrayValue,
  getOperatorLabel,
  getOperatorType,
  getRangeValue,
  getSingleValue,
  isRangeValue,
} from '@/components/custom/filter-row'

interface DrawerFilterRowProps {
  filter: FilterRowData
  fields: FilterField[]
  usedFieldNames?: FilterFieldName[]
  onUpdate: (filter: FilterRowData) => void
  onRemove: () => void
}

export function DrawerFilterRow({ filter, fields, usedFieldNames = [], onUpdate, onRemove }: DrawerFilterRowProps) {
  const selectedField = fields.find((f) => f.name === filter.fieldName)
  const availableOperators = selectedField?.supportedOperators || []
  const operatorType = getOperatorType(filter.operator)

  // Filter out fields that are already used by other filters
  const availableFields = fields.filter(
    (field) => field.name === filter.fieldName || !usedFieldNames.includes(field.name)
  )

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
    enabled: selectedField?.inputType === 'searchable' && !!filter.fieldName && debouncedSearchTerm.length > 0,
  })

  const searchOptions = useMemo(() => {
    return searchResults?.data?.options ?? []
  }, [searchResults])

  const handleFieldChange = (fieldName: string) => {
    const newField = fields.find((f) => f.name === fieldName)
    const newOperator = newField?.supportedOperators[0] || 'is'
    const newOperatorType = getOperatorType(newOperator)

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

  const handleSearchableSelect = (value: string, label: string) => {
    const currentLabels = filter.valueLabels || {}

    if (operatorType === 'multiple') {
      const currentValues = getArrayValue(filter.value)
      if (currentValues.includes(value)) {
        const newLabels = { ...currentLabels }
        delete newLabels[value]
        onUpdate({
          ...filter,
          value: currentValues.filter((v) => v !== value),
          valueLabels: newLabels,
        })
      } else {
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

  // Render value input based on field type
  const renderValueInput = () => {
    if (!selectedField) {
      const rawValue = getSingleValue(filter.value)
      const displayValue = filter.valueLabels?.[rawValue] || rawValue
      return (
        <Input
          type="text"
          value={displayValue}
          disabled
          className="h-9 text-sm bg-neutral-50 border-neutral-200"
        />
      )
    }

    // Range input
    if (operatorType === 'range') {
      const rangeValue = getRangeValue(filter.value)
      const inputType = selectedField.inputType === 'number' ? 'number' : selectedField.inputType === 'date' ? 'date' : 'text'

      return (
        <div className="flex items-center gap-2">
          <Input
            type={inputType}
            value={rangeValue.min}
            onChange={(e) => handleRangeValueChange('min', e.target.value)}
            placeholder={__('Min', 'wp-statistics')}
            className="h-9 text-sm flex-1 bg-white border-neutral-200"
          />
          <span className="text-xs text-neutral-400 shrink-0">{__('to', 'wp-statistics')}</span>
          <Input
            type={inputType}
            value={rangeValue.max}
            onChange={(e) => handleRangeValueChange('max', e.target.value)}
            placeholder={__('Max', 'wp-statistics')}
            className="h-9 text-sm flex-1 bg-white border-neutral-200"
          />
        </div>
      )
    }

    // Searchable input
    if (selectedField.inputType === 'searchable') {
      const currentValue = operatorType === 'multiple' ? getArrayValue(filter.value) : getSingleValue(filter.value)
      const valueLabels = filter.valueLabels || {}
      const isMultiple = operatorType === 'multiple'
      const hasValues = isMultiple
        ? Array.isArray(currentValue) && currentValue.length > 0
        : typeof currentValue === 'string' && currentValue

      return (
        <div className="relative">
          <div className="flex flex-wrap items-center gap-1.5 min-h-[36px] px-3 py-1.5 bg-white rounded-md border border-neutral-200">
            {hasValues && (
              <>
                {isMultiple && Array.isArray(currentValue) ? (
                  currentValue.map((val) => (
                    <span
                      key={val}
                      className="inline-flex items-center gap-1 rounded bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary"
                    >
                      {valueLabels[val] || val}
                      <button
                        type="button"
                        onClick={() => handleSearchableSelect(val, valueLabels[val] || val)}
                        className="hover:text-destructive cursor-pointer"
                      >
                        ×
                      </button>
                    </span>
                  ))
                ) : (
                  <span className="inline-flex items-center gap-1 rounded bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary">
                    {valueLabels[currentValue as string] || currentValue}
                    <button
                      type="button"
                      onClick={() => onUpdate({ ...filter, value: '', valueLabels: undefined })}
                      className="hover:text-destructive cursor-pointer"
                    >
                      ×
                    </button>
                  </span>
                )}
              </>
            )}

            {(isMultiple || !hasValues) && (
              <input
                type="text"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                placeholder={hasValues ? '' : __('Search...', 'wp-statistics')}
                className="flex-1 min-w-[80px] h-6 text-sm bg-transparent border-0 outline-none"
              />
            )}

            {isSearching && <Loader2 className="h-4 w-4 animate-spin text-neutral-400 shrink-0" />}
          </div>

          {searchTerm && searchOptions.length > 0 && (
            <div className="absolute left-0 right-0 z-50 mt-1 max-h-[200px] overflow-auto rounded-md border bg-white py-1">
              {searchOptions.map((option) => (
                <button
                  key={option.value}
                  type="button"
                  onClick={() => handleSearchableSelect(option.value, option.label)}
                  className="flex w-full items-center px-3 py-2 text-sm text-left hover:bg-neutral-50 cursor-pointer"
                >
                  {isMultiple && Array.isArray(currentValue) && (
                    <span className="mr-2 text-primary">{currentValue.includes(option.value) ? '✓' : '○'}</span>
                  )}
                  {option.label}
                </button>
              ))}
            </div>
          )}
        </div>
      )
    }

    // Dropdown
    if (selectedField.inputType === 'dropdown') {
      if (operatorType === 'multiple') {
        const currentValues = getArrayValue(filter.value)
        const valueLabels = filter.valueLabels || {}

        const handleMultiSelect = (value: string, label: string) => {
          const currentLabels = filter.valueLabels || {}
          if (currentValues.includes(value)) {
            const newLabels = { ...currentLabels }
            delete newLabels[value]
            onUpdate({
              ...filter,
              value: currentValues.filter((v) => v !== value),
              valueLabels: Object.keys(newLabels).length > 0 ? newLabels : undefined,
            })
          } else {
            onUpdate({
              ...filter,
              value: [...currentValues, value],
              valueLabels: { ...currentLabels, [value]: label },
            })
          }
        }

        return (
          <div className="flex flex-wrap items-center gap-1.5 min-h-[36px] px-3 py-1.5 bg-white rounded-md border border-neutral-200">
            {currentValues.map((val) => (
              <span
                key={val}
                className="inline-flex items-center gap-1 rounded bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary"
              >
                {valueLabels[val] || selectedField.options?.find((o) => String(o.value) === val)?.label || val}
                <button
                  type="button"
                  onClick={() => {
                    const option = selectedField.options?.find((o) => String(o.value) === val)
                    handleMultiSelect(val, option?.label || val)
                  }}
                  className="hover:text-destructive cursor-pointer"
                >
                  ×
                </button>
              </span>
            ))}

            <Select
              value=""
              onValueChange={(value) => {
                const option = selectedField.options?.find((o) => String(o.value) === value)
                handleMultiSelect(value, option?.label || value)
              }}
            >
              <SelectTrigger className="h-6 text-xs border-0 bg-transparent min-w-[80px] flex-1 focus:ring-0 px-0">
                <SelectValue placeholder={currentValues.length > 0 ? __('Add...', 'wp-statistics') : __('Select...', 'wp-statistics')} />
              </SelectTrigger>
              <SelectContent className="max-h-[200px]">
                {selectedField.options?.map((option) => (
                  <SelectItem key={String(option.value)} value={String(option.value)}>
                    <span className="flex items-center gap-2">
                      <span className="text-primary">{currentValues.includes(String(option.value)) ? '✓' : '○'}</span>
                      {option.label}
                    </span>
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
        )
      }

      // Single dropdown
      return (
        <Select
          value={getSingleValue(filter.value)}
          onValueChange={(value) => {
            const option = selectedField.options?.find((o) => String(o.value) === value)
            handleSingleValueChange(value, option?.label)
          }}
        >
          <SelectTrigger className="h-9 text-sm bg-white border-neutral-200">
            <SelectValue placeholder={__('Select...', 'wp-statistics')} />
          </SelectTrigger>
          <SelectContent className="max-h-[200px]">
            {selectedField.options?.map((option) => (
              <SelectItem key={String(option.value)} value={String(option.value)}>
                {option.label}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
      )
    }

    // Number input
    if (selectedField.inputType === 'number') {
      return (
        <Input
          type="number"
          value={getSingleValue(filter.value)}
          onChange={(e) => handleSingleValueChange(e.target.value)}
          placeholder={__('Value', 'wp-statistics')}
          className="h-9 text-sm bg-white border-neutral-200"
        />
      )
    }

    // Date input
    if (selectedField.inputType === 'date') {
      return (
        <Input
          type="date"
          value={getSingleValue(filter.value)}
          onChange={(e) => handleSingleValueChange(e.target.value)}
          className="h-9 text-sm bg-white border-neutral-200"
        />
      )
    }

    // Text input (default)
    return (
      <Input
        type="text"
        value={getSingleValue(filter.value)}
        onChange={(e) => handleSingleValueChange(e.target.value)}
        placeholder={__('Value', 'wp-statistics')}
        className="h-9 text-sm bg-white border-neutral-200"
      />
    )
  }

  const isUnavailableField = !selectedField && filter.fieldName

  return (
    <div className="rounded-lg bg-neutral-50 border border-neutral-200 p-3">
      {/* Row 1: Field and Operator */}
      <div className="flex items-center gap-2 mb-2">
        {/* Field Select */}
        {isUnavailableField ? (
          <div className="h-9 flex-1 text-sm bg-neutral-100 border border-neutral-200 rounded-md flex items-center px-3 text-neutral-500">
            {filter.fieldName}
          </div>
        ) : (
          <Select value={filter.fieldName} onValueChange={handleFieldChange}>
            <SelectTrigger className="h-9 text-sm bg-white border-neutral-200 flex-1">
              <SelectValue placeholder={__('Field', 'wp-statistics')} />
            </SelectTrigger>
            <SelectContent className="max-h-[200px]">
              {availableFields.map((field) => (
                <SelectItem key={field.name} value={field.name}>
                  {field.label}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        )}

        {/* Operator Select */}
        {isUnavailableField ? (
          <div className="h-9 w-[100px] text-sm bg-neutral-100 border border-neutral-200 rounded-md flex items-center px-3 text-neutral-500 shrink-0">
            {getOperatorLabel(filter.operator)}
          </div>
        ) : (
          <Select value={filter.operator} onValueChange={handleOperatorChange}>
            <SelectTrigger className="h-9 text-sm bg-white border-neutral-200 w-[100px] shrink-0">
              <SelectValue placeholder={__('Operator', 'wp-statistics')} />
            </SelectTrigger>
            <SelectContent className="max-h-[200px]">
              {availableOperators.map((op) => (
                <SelectItem key={op} value={op}>
                  {getOperatorLabel(op)}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        )}

        {/* Remove Button */}
        <button
          type="button"
          onClick={onRemove}
          className="h-9 w-9 flex items-center justify-center text-neutral-400 hover:text-destructive hover:bg-destructive/10 rounded-md transition-colors shrink-0 cursor-pointer"
          aria-label={__('Remove filter', 'wp-statistics')}
        >
          <Trash2Icon className="h-4 w-4" />
        </button>
      </div>

      {/* Row 2: Value Input (only if operator needs a value) */}
      {filter.operator !== 'is_null' && filter.operator !== 'is_not_null' && (
        <div>{renderValueInput()}</div>
      )}
    </div>
  )
}
