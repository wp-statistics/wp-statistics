import { Trash2 } from 'lucide-react'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { WordPress } from '@/lib/wordpress'

// Local filter field type that matches the localized data structure
export interface FilterField {
  name: FilterFieldName
  label: string
  inputType: FilterInputType
  supportedOperators: FilterOperator[]
  groups: FilterGroup[]
  options?: FilterOption[]
}

export interface FilterRowData {
  id: string
  fieldName: FilterFieldName
  operator: FilterOperator
  value: string | string[]
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

function FilterRow({ filter, fields, onUpdate, onRemove }: FilterRowProps) {
  const selectedField = fields.find((f) => f.name === filter.fieldName)
  const availableOperators = selectedField?.supportedOperators || []

  const handleFieldChange = (fieldName: string) => {
    const newField = fields.find((f) => f.name === fieldName)
    const newOperator = newField?.supportedOperators[0] || 'is'
    onUpdate({ ...filter, fieldName: fieldName as FilterFieldName, operator: newOperator, value: '' })
  }

  const handleOperatorChange = (operator: string) => {
    onUpdate({ ...filter, operator: operator as FilterOperator })
  }

  const handleValueChange = (value: string) => {
    onUpdate({ ...filter, value })
  }

  const renderValueInput = () => {
    if (!selectedField) {
      return (
        <Input
          type="text"
          value={typeof filter.value === 'string' ? filter.value : ''}
          onChange={(e) => handleValueChange(e.target.value)}
          placeholder="Value"
          className="w-[160px]"
        />
      )
    }

    switch (selectedField.inputType) {
      case 'dropdown':
        return (
          <Select
            value={typeof filter.value === 'string' ? filter.value : ''}
            onValueChange={handleValueChange}
          >
            <SelectTrigger className="w-[160px]">
              <SelectValue placeholder="Select value" />
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
            value={typeof filter.value === 'string' ? filter.value : ''}
            onChange={(e) => handleValueChange(e.target.value)}
            placeholder="Value"
            className="w-[160px]"
          />
        )
      case 'date':
        return (
          <Input
            type="date"
            value={typeof filter.value === 'string' ? filter.value : ''}
            onChange={(e) => handleValueChange(e.target.value)}
            placeholder="Select date"
            className="w-[160px]"
          />
        )
      case 'searchable':
      case 'text':
      default:
        return (
          <Input
            type="text"
            value={typeof filter.value === 'string' ? filter.value : ''}
            onChange={(e) => handleValueChange(e.target.value)}
            placeholder="Value"
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
          <SelectValue placeholder="Select field" />
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
          <SelectValue placeholder="Select operator" />
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

export { FilterRow, getOperatorLabel }
