import { Trash2 } from 'lucide-react'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'

export type FilterOperator = 'greater_than' | 'less_than' | 'equal_to' | 'not_equal' | 'contains' | 'is'

export interface FilterField {
  id: string
  label: string
  operators: FilterOperator[]
  type?: 'text' | 'number' | 'select'
  options?: { value: string; label: string }[]
}

export interface FilterRowData {
  id: string
  fieldId: string
  operator: FilterOperator
  value: string
}

export interface FilterRowProps {
  filter: FilterRowData
  fields: FilterField[]
  onUpdate: (filter: FilterRowData) => void
  onRemove: (id: string) => void
}

const operatorLabels: Record<FilterOperator, string> = {
  greater_than: 'Greater than',
  less_than: 'Less than',
  equal_to: 'Equal to',
  not_equal: 'Not equal',
  contains: 'Contains',
  is: 'Is',
}

function FilterRow({ filter, fields, onUpdate, onRemove }: FilterRowProps) {
  const selectedField = fields.find((f) => f.id === filter.fieldId)
  const availableOperators = selectedField?.operators || []

  const handleFieldChange = (fieldId: string) => {
    const newField = fields.find((f) => f.id === fieldId)
    const newOperator = newField?.operators[0] || 'equal_to'
    onUpdate({ ...filter, fieldId, operator: newOperator, value: '' })
  }

  const handleOperatorChange = (operator: FilterOperator) => {
    onUpdate({ ...filter, operator })
  }

  const handleValueChange = (value: string) => {
    onUpdate({ ...filter, value })
  }

  return (
    <div className="flex items-center gap-2">
      {/* Field Select */}
      <Select value={filter.fieldId} onValueChange={handleFieldChange}>
        <SelectTrigger className="w-[140px]">
          <SelectValue placeholder="Select field" />
        </SelectTrigger>
        <SelectContent>
          {fields.map((field) => (
            <SelectItem key={field.id} value={field.id}>
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
              {operatorLabels[op]}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>

      {/* Value Input or Select */}
      {selectedField?.type === 'select' && selectedField.options ? (
        <Select value={filter.value} onValueChange={handleValueChange}>
          <SelectTrigger className="w-[160px]">
            <SelectValue placeholder="Select value" />
          </SelectTrigger>
          <SelectContent>
            {selectedField.options.map((option) => (
              <SelectItem key={option.value} value={option.value}>
                {option.label}
              </SelectItem>
            ))}
          </SelectContent>
        </Select>
      ) : (
        <Input
          type={selectedField?.type === 'number' ? 'number' : 'text'}
          value={filter.value}
          onChange={(e) => handleValueChange(e.target.value)}
          placeholder="Value"
          className="w-[160px]"
        />
      )}

      {/* Remove Button */}
      <Button variant="ghost" size="icon" onClick={() => onRemove(filter.id)} className="shrink-0">
        <Trash2 className="h-4 w-4" />
      </Button>
    </div>
  )
}

export { FilterRow, operatorLabels }
