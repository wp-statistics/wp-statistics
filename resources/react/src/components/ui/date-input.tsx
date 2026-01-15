import * as React from 'react'
import { useEffect, useMemo, useRef, useState } from 'react'

import { cn } from '@/lib/utils'
import { parseDateFormat, getFieldConfigs, getNavigationFields, type DateFieldConfig } from '@/lib/date-format'
import { WordPress } from '@/lib/wordpress'

interface DateInputProps {
  value?: Date
  onChange: (date: Date) => void
  className?: string
  onFocus?: () => void
}

interface DateParts {
  day: number | string
  month: number | string
  year: number | string
}

const DateInput: React.FC<DateInputProps> = ({ value, onChange, className, onFocus }) => {
  const [date, setDate] = useState<DateParts>(() => {
    const d = value ? new Date(value) : new Date()
    return {
      day: d.getDate(),
      month: d.getMonth() + 1,
      year: d.getFullYear(),
    }
  })

  // Get date format from WordPress settings (only order, separator is always '-')
  const dateFormat = WordPress.getInstance().getDateFormat()
  const { order } = useMemo(() => parseDateFormat(dateFormat), [dateFormat])
  const fieldConfigs = useMemo(() => getFieldConfigs(order), [order])

  // Create refs for all three fields
  const fieldRefs = useRef<Record<string, HTMLInputElement | null>>({
    day: null,
    month: null,
    year: null,
  })
  const initialDate = useRef<DateParts>(date)

  useEffect(() => {
    const d = value ? new Date(value) : new Date()
    const newDate = {
      day: d.getDate(),
      month: d.getMonth() + 1,
      year: d.getFullYear(),
    }
    setDate(newDate)
    initialDate.current = newDate
  }, [value])

  const validateDate = (field: keyof DateParts, newValue: number): boolean => {
    if (
      (field === 'day' && (newValue < 1 || newValue > 31)) ||
      (field === 'month' && (newValue < 1 || newValue > 12)) ||
      (field === 'year' && (newValue < 1000 || newValue > 9999))
    ) {
      return false
    }

    const newDate = { ...date, [field]: newValue }
    const d = new Date(Number(newDate.year), Number(newDate.month) - 1, Number(newDate.day))
    return (
      d.getFullYear() === Number(newDate.year) &&
      d.getMonth() + 1 === Number(newDate.month) &&
      d.getDate() === Number(newDate.day)
    )
  }

  const handleInputChange = (field: keyof DateParts) => (e: React.ChangeEvent<HTMLInputElement>) => {
    const newValue = e.target.value ? Number(e.target.value) : ''
    const isValid = typeof newValue === 'number' && validateDate(field, newValue)

    const newDate = { ...date, [field]: newValue }
    setDate(newDate)

    if (isValid) {
      onChange(new Date(Number(newDate.year), Number(newDate.month) - 1, Number(newDate.day)))
    }
  }

  const handleBlur =
    (field: keyof DateParts) =>
    (e: React.FocusEvent<HTMLInputElement>): void => {
      if (!e.target.value) {
        setDate(initialDate.current)
        return
      }

      const newValue = Number(e.target.value)
      const isValid = validateDate(field, newValue)

      if (!isValid) {
        setDate(initialDate.current)
      } else {
        initialDate.current = { ...date, [field]: newValue }
      }
    }

  const handleKeyDown = (field: keyof DateParts) => (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.metaKey || e.ctrlKey) {
      return
    }

    if (
      !/^[0-9]$/.test(e.key) &&
      !['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Delete', 'Tab', 'Backspace', 'Enter'].includes(e.key)
    ) {
      e.preventDefault()
      return
    }

    if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
      e.preventDefault()
      const currentValue = Number(date[field]) || 0
      const delta = e.key === 'ArrowUp' ? 1 : -1
      const newValue = currentValue + delta

      if (validateDate(field, newValue)) {
        const newDate = { ...date, [field]: newValue }
        setDate(newDate)
        onChange(new Date(Number(newDate.year), Number(newDate.month) - 1, Number(newDate.day)))
      }
    }

    // Dynamic navigation based on field order
    const { prev, next } = getNavigationFields(field as 'day' | 'month' | 'year', order)

    if (e.key === 'ArrowRight') {
      if (
        e.currentTarget.selectionStart === e.currentTarget.value.length ||
        (e.currentTarget.selectionStart === 0 && e.currentTarget.selectionEnd === e.currentTarget.value.length)
      ) {
        e.preventDefault()
        if (next) fieldRefs.current[next]?.focus()
      }
    } else if (e.key === 'ArrowLeft') {
      if (
        e.currentTarget.selectionStart === 0 ||
        (e.currentTarget.selectionStart === 0 && e.currentTarget.selectionEnd === e.currentTarget.value.length)
      ) {
        e.preventDefault()
        if (prev) fieldRefs.current[prev]?.focus()
      }
    }
  }

  const handleContainerFocus = () => {
    onFocus?.()
  }

  const renderField = (config: DateFieldConfig, index: number) => (
    <React.Fragment key={config.field}>
      {index > 0 && <span className="-mx-px opacity-20">-</span>}
      <input
        type="text"
        ref={(el) => {
          fieldRefs.current[config.field] = el
        }}
        maxLength={config.maxLength}
        value={date[config.field].toString()}
        onChange={handleInputChange(config.field)}
        onKeyDown={handleKeyDown(config.field)}
        onBlur={handleBlur(config.field)}
        onFocus={handleContainerFocus}
        className={cn(config.width, 'border-none bg-transparent p-0 text-center outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-1 rounded-sm')}
        placeholder={config.placeholder}
        aria-label={config.field}
      />
    </React.Fragment>
  )

  return (
    <div className={cn('flex items-center rounded-md border bg-background px-1 text-sm', className)}>
      {fieldConfigs.map((config, index) => renderField(config, index))}
    </div>
  )
}

DateInput.displayName = 'DateInput'

export { DateInput }
