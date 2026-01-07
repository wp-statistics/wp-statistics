import * as React from 'react'
import { useEffect, useRef, useState } from 'react'

import { cn } from '@/lib/utils'

interface DateInputProps {
  value?: Date
  onChange: (date: Date) => void
  className?: string
}

interface DateParts {
  day: number | string
  month: number | string
  year: number | string
}

const DateInput: React.FC<DateInputProps> = ({ value, onChange, className }) => {
  const [date, setDate] = useState<DateParts>(() => {
    const d = value ? new Date(value) : new Date()
    return {
      day: d.getDate(),
      month: d.getMonth() + 1,
      year: d.getFullYear(),
    }
  })

  const monthRef = useRef<HTMLInputElement | null>(null)
  const dayRef = useRef<HTMLInputElement | null>(null)
  const yearRef = useRef<HTMLInputElement | null>(null)
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

    if (e.key === 'ArrowRight') {
      if (
        e.currentTarget.selectionStart === e.currentTarget.value.length ||
        (e.currentTarget.selectionStart === 0 && e.currentTarget.selectionEnd === e.currentTarget.value.length)
      ) {
        e.preventDefault()
        if (field === 'month') dayRef.current?.focus()
        if (field === 'day') yearRef.current?.focus()
      }
    } else if (e.key === 'ArrowLeft') {
      if (
        e.currentTarget.selectionStart === 0 ||
        (e.currentTarget.selectionStart === 0 && e.currentTarget.selectionEnd === e.currentTarget.value.length)
      ) {
        e.preventDefault()
        if (field === 'day') monthRef.current?.focus()
        if (field === 'year') dayRef.current?.focus()
      }
    }
  }

  return (
    <div className={cn('flex items-center rounded-md border bg-background px-1 text-sm', className)}>
      <input
        type="text"
        ref={monthRef}
        maxLength={2}
        value={date.month.toString()}
        onChange={handleInputChange('month')}
        onKeyDown={handleKeyDown('month')}
        onBlur={handleBlur('month')}
        className="w-6 border-none bg-transparent p-0 text-center outline-none"
        placeholder="M"
      />
      <span className="-mx-px opacity-20">/</span>
      <input
        type="text"
        ref={dayRef}
        maxLength={2}
        value={date.day.toString()}
        onChange={handleInputChange('day')}
        onKeyDown={handleKeyDown('day')}
        onBlur={handleBlur('day')}
        className="w-7 border-none bg-transparent p-0 text-center outline-none"
        placeholder="D"
      />
      <span className="-mx-px opacity-20">/</span>
      <input
        type="text"
        ref={yearRef}
        maxLength={4}
        value={date.year.toString()}
        onChange={handleInputChange('year')}
        onKeyDown={handleKeyDown('year')}
        onBlur={handleBlur('year')}
        className="w-12 border-none bg-transparent p-0 text-center outline-none"
        placeholder="YYYY"
      />
    </div>
  )
}

DateInput.displayName = 'DateInput'

export { DateInput }
