'use client'

import { Calendar as CalendarIcon, Check, ChevronDown } from 'lucide-react'
import { useEffect, useRef, useState } from 'react'

import { Button } from '@/components/ui/button'
import { Calendar } from '@/components/ui/calendar'
import { DateInput } from '@/components/ui/date-input'
import { Label } from '@/components/ui/label'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Switch } from '@/components/ui/switch'
import { cn } from '@/lib/utils'

export interface DateRange {
  from: Date
  to: Date | undefined
}

export interface DateRangePickerProps {
  /** Click handler for applying the updates from DateRangePicker. */
  onUpdate?: (values: { range: DateRange; rangeCompare?: DateRange }) => void
  /** Initial value for start date */
  initialDateFrom?: Date | string
  /** Initial value for end date */
  initialDateTo?: Date | string
  /** Initial value for start date for compare */
  initialCompareFrom?: Date | string
  /** Initial value for end date for compare */
  initialCompareTo?: Date | string
  /** Alignment of popover */
  align?: 'start' | 'center' | 'end'
  /** Option for locale */
  locale?: string
  /** Option for showing compare feature */
  showCompare?: boolean
}

const formatDate = (date: Date, locale: string = 'en-us'): string => {
  return date.toLocaleDateString(locale, {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}

const getDateAdjustedForTimezone = (dateInput: Date | string): Date => {
  if (typeof dateInput === 'string') {
    const parts = dateInput.split('-').map((part) => parseInt(part, 10))
    const date = new Date(parts[0], parts[1] - 1, parts[2])
    return date
  } else {
    return dateInput
  }
}

interface Preset {
  name: string
  label: string
}

const PRESETS: Preset[] = [
  { name: 'today', label: 'Today' },
  { name: 'yesterday', label: 'Yesterday' },
  { name: 'lastWeek', label: 'Last Week' },
  { name: 'last14', label: 'Last 14 days' },
  { name: 'last30', label: 'Last 30 days' },
  { name: 'lastMonth', label: 'Last Month' },
  { name: 'last3months', label: 'Last 3 Months' },
  { name: 'last6months', label: 'Last 6 Months' },
  { name: 'lastYear', label: 'Last Year' },
]

/** The DateRangePicker component allows a user to select a range of dates */
export const DateRangePicker = ({
  initialDateFrom = new Date(new Date().setHours(0, 0, 0, 0)),
  initialDateTo,
  initialCompareFrom,
  initialCompareTo,
  onUpdate,
  align = 'end',
  locale = 'en-US',
  showCompare = true,
}: DateRangePickerProps) => {
  const [isOpen, setIsOpen] = useState(false)

  const [range, setRange] = useState<DateRange>({
    from: getDateAdjustedForTimezone(initialDateFrom),
    to: initialDateTo ? getDateAdjustedForTimezone(initialDateTo) : getDateAdjustedForTimezone(initialDateFrom),
  })

  const [rangeCompare, setRangeCompare] = useState<DateRange | undefined>(
    initialCompareFrom
      ? {
          from: new Date(new Date(initialCompareFrom).setHours(0, 0, 0, 0)),
          to: initialCompareTo
            ? new Date(new Date(initialCompareTo).setHours(0, 0, 0, 0))
            : new Date(new Date(initialCompareFrom).setHours(0, 0, 0, 0)),
        }
      : undefined
  )

  const openedRangeRef = useRef<DateRange | undefined>()
  const openedRangeCompareRef = useRef<DateRange | undefined>()

  const [selectedPreset, setSelectedPreset] = useState<string | undefined>(undefined)

  const [isSmallScreen, setIsSmallScreen] = useState(typeof window !== 'undefined' ? window.innerWidth < 960 : false)

  // Sync internal state when initial props change (e.g., when context loads preferences)
  useEffect(() => {
    const newFrom = getDateAdjustedForTimezone(initialDateFrom)
    const newTo = initialDateTo ? getDateAdjustedForTimezone(initialDateTo) : newFrom

    // Only update if values actually changed
    if (newFrom.getTime() !== range.from.getTime() || newTo.getTime() !== (range.to?.getTime() ?? 0)) {
      setRange({ from: newFrom, to: newTo })
    }
  }, [initialDateFrom, initialDateTo])

  // Sync compare range when props change
  useEffect(() => {
    if (initialCompareFrom) {
      const newCompareFrom = new Date(new Date(initialCompareFrom).setHours(0, 0, 0, 0))
      const newCompareTo = initialCompareTo
        ? new Date(new Date(initialCompareTo).setHours(0, 0, 0, 0))
        : newCompareFrom

      if (
        !rangeCompare ||
        newCompareFrom.getTime() !== rangeCompare.from.getTime() ||
        newCompareTo.getTime() !== (rangeCompare.to?.getTime() ?? 0)
      ) {
        setRangeCompare({ from: newCompareFrom, to: newCompareTo })
      }
    } else if (rangeCompare && !initialCompareFrom) {
      // Props cleared compare, so clear internal state
      setRangeCompare(undefined)
    }
  }, [initialCompareFrom, initialCompareTo])

  useEffect(() => {
    const handleResize = (): void => {
      setIsSmallScreen(window.innerWidth < 960)
    }

    window.addEventListener('resize', handleResize)
    return () => {
      window.removeEventListener('resize', handleResize)
    }
  }, [])

  const getPresetRange = (presetName: string): DateRange => {
    const preset = PRESETS.find(({ name }) => name === presetName)
    if (!preset) throw new Error(`Unknown date range preset: ${presetName}`)
    const from = new Date()
    const to = new Date()

    switch (preset.name) {
      case 'today':
        from.setHours(0, 0, 0, 0)
        to.setHours(23, 59, 59, 999)
        break
      case 'yesterday':
        from.setDate(from.getDate() - 1)
        from.setHours(0, 0, 0, 0)
        to.setDate(to.getDate() - 1)
        to.setHours(23, 59, 59, 999)
        break
      case 'lastWeek':
        from.setDate(from.getDate() - 7 - from.getDay())
        to.setDate(to.getDate() - to.getDay() - 1)
        from.setHours(0, 0, 0, 0)
        to.setHours(23, 59, 59, 999)
        break
      case 'last14':
        from.setDate(from.getDate() - 13)
        from.setHours(0, 0, 0, 0)
        to.setHours(23, 59, 59, 999)
        break
      case 'last30':
        from.setDate(from.getDate() - 29)
        from.setHours(0, 0, 0, 0)
        to.setHours(23, 59, 59, 999)
        break
      case 'lastMonth':
        from.setMonth(from.getMonth() - 1)
        from.setDate(1)
        from.setHours(0, 0, 0, 0)
        to.setDate(0)
        to.setHours(23, 59, 59, 999)
        break
      case 'last3months':
        from.setMonth(from.getMonth() - 3)
        from.setHours(0, 0, 0, 0)
        to.setHours(23, 59, 59, 999)
        break
      case 'last6months':
        from.setMonth(from.getMonth() - 6)
        from.setHours(0, 0, 0, 0)
        to.setHours(23, 59, 59, 999)
        break
      case 'lastYear':
        from.setFullYear(from.getFullYear() - 1)
        from.setHours(0, 0, 0, 0)
        to.setHours(23, 59, 59, 999)
        break
    }

    return { from, to }
  }

  const setPreset = (preset: string): void => {
    const newRange = getPresetRange(preset)
    setRange(newRange)
    if (rangeCompare) {
      const newRangeCompare = {
        from: new Date(newRange.from.getFullYear() - 1, newRange.from.getMonth(), newRange.from.getDate()),
        to: newRange.to
          ? new Date(newRange.to.getFullYear() - 1, newRange.to.getMonth(), newRange.to.getDate())
          : undefined,
      }
      setRangeCompare(newRangeCompare)
    }
  }

  const checkPreset = (): void => {
    for (const preset of PRESETS) {
      const presetRange = getPresetRange(preset.name)

      const normalizedRangeFrom = new Date(range.from)
      normalizedRangeFrom.setHours(0, 0, 0, 0)
      const normalizedPresetFrom = new Date(presetRange.from.setHours(0, 0, 0, 0))

      const normalizedRangeTo = new Date(range.to ?? 0)
      normalizedRangeTo.setHours(0, 0, 0, 0)
      const normalizedPresetTo = new Date(presetRange.to?.setHours(0, 0, 0, 0) ?? 0)

      if (
        normalizedRangeFrom.getTime() === normalizedPresetFrom.getTime() &&
        normalizedRangeTo.getTime() === normalizedPresetTo.getTime()
      ) {
        setSelectedPreset(preset.name)
        return
      }
    }

    setSelectedPreset(undefined)
  }

  const resetValues = (): void => {
    setRange({
      from: typeof initialDateFrom === 'string' ? getDateAdjustedForTimezone(initialDateFrom) : initialDateFrom,
      to: initialDateTo
        ? typeof initialDateTo === 'string'
          ? getDateAdjustedForTimezone(initialDateTo)
          : initialDateTo
        : typeof initialDateFrom === 'string'
          ? getDateAdjustedForTimezone(initialDateFrom)
          : initialDateFrom,
    })
    setRangeCompare(
      initialCompareFrom
        ? {
            from:
              typeof initialCompareFrom === 'string'
                ? getDateAdjustedForTimezone(initialCompareFrom)
                : initialCompareFrom,
            to: initialCompareTo
              ? typeof initialCompareTo === 'string'
                ? getDateAdjustedForTimezone(initialCompareTo)
                : initialCompareTo
              : typeof initialCompareFrom === 'string'
                ? getDateAdjustedForTimezone(initialCompareFrom)
                : initialCompareFrom,
          }
        : undefined
    )
  }

  useEffect(() => {
    checkPreset()
  }, [range])

  const areRangesEqual = (a?: DateRange, b?: DateRange): boolean => {
    if (!a || !b) return a === b
    return a.from.getTime() === b.from.getTime() && (!a.to || !b.to || a.to.getTime() === b.to.getTime())
  }

  useEffect(() => {
    if (isOpen) {
      openedRangeRef.current = range
      openedRangeCompareRef.current = rangeCompare
    }
  }, [isOpen])

  const handleCompareToggle = (checked: boolean) => {
    if (checked) {
      if (!range.to) {
        setRange({
          from: range.from,
          to: range.from,
        })
      }
      setRangeCompare({
        from: new Date(range.from.getFullYear(), range.from.getMonth(), range.from.getDate() - 365),
        to: range.to
          ? new Date(range.to.getFullYear() - 1, range.to.getMonth(), range.to.getDate())
          : new Date(range.from.getFullYear() - 1, range.from.getMonth(), range.from.getDate()),
      })
    } else {
      setRangeCompare(undefined)
    }
  }

  return (
    <Popover
      modal={true}
      open={isOpen}
      onOpenChange={(open: boolean) => {
        if (!open) {
          resetValues()
        }
        setIsOpen(open)
      }}
    >
      <PopoverTrigger asChild>
        <Button variant="outline" className="h-8 text-xs font-medium border-neutral-200 hover:bg-neutral-50">
          <CalendarIcon className="h-3.5 w-3.5 mr-2 text-neutral-500" />
          <span className="text-neutral-700">
            {selectedPreset
              ? PRESETS.find((p) => p.name === selectedPreset)?.label
              : `${formatDate(range.from, locale)}${range.to ? ` – ${formatDate(range.to, locale)}` : ''}`}
          </span>
          {rangeCompare && (
            <span className="ml-2 px-1.5 py-0.5 rounded bg-primary/10 text-primary text-[10px] font-medium">vs</span>
          )}
          <ChevronDown className="h-3.5 w-3.5 ml-2 text-neutral-400" />
        </Button>
      </PopoverTrigger>
      <PopoverContent
        align={align}
        className="w-auto max-h-[var(--radix-popover-content-available-height,calc(100vh-2rem))] overflow-auto p-0"
      >
        {/* Header */}
        <div className="flex items-center justify-between px-4 py-2 border-b border-neutral-100 bg-neutral-50/50">
          <span className="text-sm font-semibold text-neutral-700 tracking-tight">Select Date Range</span>
          {showCompare && (
            <div className="flex items-center gap-2">
              <Switch
                checked={Boolean(rangeCompare)}
                onCheckedChange={handleCompareToggle}
                id="compare-mode"
                className="scale-90"
              />
              <Label htmlFor="compare-mode" className="text-xs text-neutral-600 cursor-pointer">
                Compare
              </Label>
            </div>
          )}
        </div>

        {/* Body */}
        <div className="flex">
          {/* Main content */}
          <div className="p-4">
            {/* Date inputs card */}
            <div className="p-3 rounded-lg bg-neutral-50/70 border border-neutral-100 mb-4">
              <div className="flex items-center gap-2">
                <DateInput
                  value={range.from}
                  onChange={(date) => {
                    const toDate = range.to == null || date > range.to ? date : range.to
                    setRange((prevRange) => ({
                      ...prevRange,
                      from: date,
                      to: toDate,
                    }))
                  }}
                  className="h-8 text-xs border-0 bg-white shadow-sm flex-1"
                />
                <span className="text-xs text-neutral-400">to</span>
                <DateInput
                  value={range.to}
                  onChange={(date) => {
                    const fromDate = date < range.from ? date : range.from
                    setRange((prevRange) => ({
                      ...prevRange,
                      from: fromDate,
                      to: date,
                    }))
                  }}
                  className="h-8 text-xs border-0 bg-white shadow-sm flex-1"
                />
              </div>

              {/* Compare dates */}
              {rangeCompare != null && (
                <>
                  <div className="flex items-center gap-1.5 my-2">
                    <div className="h-px flex-1 bg-neutral-200" />
                    <span className="text-[10px] font-medium text-neutral-400 uppercase tracking-wider">vs</span>
                    <div className="h-px flex-1 bg-neutral-200" />
                  </div>
                  <div className="flex items-center gap-2">
                    <DateInput
                      value={rangeCompare?.from}
                      onChange={(date) => {
                        if (rangeCompare) {
                          const compareToDate =
                            rangeCompare.to == null || date > rangeCompare.to ? date : rangeCompare.to
                          setRangeCompare((prevRangeCompare) =>
                            prevRangeCompare
                              ? {
                                  ...prevRangeCompare,
                                  from: date,
                                  to: compareToDate,
                                }
                              : undefined
                          )
                        } else {
                          setRangeCompare({
                            from: date,
                            to: new Date(),
                          })
                        }
                      }}
                      className="h-8 text-xs border-0 bg-white shadow-sm flex-1"
                    />
                    <span className="text-xs text-neutral-400">to</span>
                    <DateInput
                      value={rangeCompare?.to}
                      onChange={(date) => {
                        if (rangeCompare && rangeCompare.from) {
                          const compareFromDate = date < rangeCompare.from ? date : rangeCompare.from
                          setRangeCompare({
                            ...rangeCompare,
                            from: compareFromDate,
                            to: date,
                          })
                        }
                      }}
                      className="h-8 text-xs border-0 bg-white shadow-sm flex-1"
                    />
                  </div>
                </>
              )}
            </div>

            {/* Mobile preset select */}
            {isSmallScreen && (
              <div className="mb-4">
                <Select defaultValue={selectedPreset} onValueChange={(value) => setPreset(value)}>
                  <SelectTrigger className="h-8 text-xs border-0 bg-neutral-50 shadow-sm">
                    <SelectValue placeholder="Quick select..." />
                  </SelectTrigger>
                  <SelectContent>
                    {PRESETS.map((preset) => (
                      <SelectItem key={preset.name} value={preset.name} className="text-xs">
                        {preset.label}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>
            )}

            {/* Calendar */}
            <Calendar
              mode="range"
              onSelect={(value: { from?: Date; to?: Date } | undefined) => {
                if (value?.from != null) {
                  setRange({ from: value.from, to: value?.to })
                }
              }}
              selected={range}
              numberOfMonths={isSmallScreen ? 1 : 2}
              defaultMonth={new Date(new Date().setMonth(new Date().getMonth() - (isSmallScreen ? 0 : 1)))}
              cellSize="1.875rem"
              className="p-2"
            />
          </div>

          {/* Presets sidebar */}
          {!isSmallScreen && (
            <div className="w-[140px] p-3 border-l border-neutral-100 bg-neutral-50/30">
              <span className="text-[10px] font-medium text-neutral-400 uppercase tracking-wider mb-2 block">
                Quick Select
              </span>
              <div className="flex flex-col gap-0.5">
                {PRESETS.map((preset) => (
                  <button
                    key={preset.name}
                    type="button"
                    onClick={() => setPreset(preset.name)}
                    className={cn(
                      'px-2.5 py-1.5 text-xs text-left rounded-md transition-colors cursor-pointer',
                      selectedPreset === preset.name
                        ? 'bg-primary/10 text-primary font-medium'
                        : 'text-neutral-600 hover:bg-neutral-100'
                    )}
                  >
                    {selectedPreset === preset.name && <Check className="inline-block h-3 w-3 mr-1.5" />}
                    {preset.label}
                  </button>
                ))}
              </div>
            </div>
          )}
        </div>

        {/* Footer */}
        <div className="flex items-center justify-end gap-2 px-4 py-2 border-t border-neutral-100 bg-neutral-50/30 sticky bottom-0">
          <Button
            variant="ghost"
            size="sm"
            className="text-xs"
            onClick={() => {
              setIsOpen(false)
              resetValues()
            }}
          >
            Cancel
          </Button>
          <Button
            size="sm"
            className="text-xs px-4"
            onClick={() => {
              setIsOpen(false)
              if (
                !areRangesEqual(range, openedRangeRef.current) ||
                !areRangesEqual(rangeCompare, openedRangeCompareRef.current)
              ) {
                onUpdate?.({ range, rangeCompare })
              }
            }}
          >
            Apply
          </Button>
        </div>
      </PopoverContent>
    </Popover>
  )
}

DateRangePicker.displayName = 'DateRangePicker'
