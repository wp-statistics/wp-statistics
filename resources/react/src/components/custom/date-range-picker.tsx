'use client'

import { ChevronDown } from 'lucide-react'
import { useEffect, useRef, useState } from 'react'

import { Button } from '@/components/ui/button'
import { Calendar } from '@/components/ui/calendar'
import { Checkbox } from '@/components/ui/checkbox'
import { DateInput } from '@/components/ui/date-input'
import { Label } from '@/components/ui/label'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { cn } from '@/lib/utils'

export interface DateRange {
  from: Date
  to: Date | undefined
}

export interface DateRangePickerProps {
  /** Click handler for applying the updates from DateRangePicker. */
  onUpdate?: (values: {
    range: DateRange
    rangeCompare?: DateRange
    period?: string
    comparisonMode?: ComparisonMode
  }) => void
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
  /** Initial period preset name (e.g., 'yesterday', 'last30') */
  initialPeriod?: string
  /** Initial comparison mode (e.g., 'previous_period', 'same_period_last_year') */
  initialComparisonMode?: ComparisonMode
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

export interface Preset {
  name: string
  label: string
}

export const PRESETS: Preset[] = [
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

/**
 * Comparison mode types for period-over-period comparison.
 */
export type ComparisonMode = 'previous_period' | 'previous_period_dow' | 'same_period_last_year' | 'custom'

export interface ComparisonModeOption {
  name: ComparisonMode
  label: string
}

export const COMPARISON_MODES: ComparisonModeOption[] = [
  { name: 'previous_period', label: 'Previous period' },
  { name: 'previous_period_dow', label: 'Previous period (match day of week)' },
  { name: 'same_period_last_year', label: 'Same period last year' },
  { name: 'custom', label: 'Custom' },
]

/**
 * Check if a comparison mode is valid.
 */
export const isValidComparisonMode = (mode: string | undefined): mode is ComparisonMode => {
  if (!mode) return false
  return COMPARISON_MODES.some((m) => m.name === mode)
}

/**
 * Calculate the comparison date range based on the selected mode.
 */
export const calculateComparisonRange = (range: DateRange, mode: ComparisonMode): DateRange | undefined => {
  if (!range.from || !range.to) return undefined

  // Normalize dates to midnight to avoid time-based calculation errors
  const from = new Date(range.from)
  from.setHours(0, 0, 0, 0)
  const to = new Date(range.to)
  to.setHours(0, 0, 0, 0)
  const daysDiff = Math.round((to.getTime() - from.getTime()) / (1000 * 60 * 60 * 24))

  switch (mode) {
    case 'previous_period': {
      // Same duration, immediately before current period
      const prevTo = new Date(from)
      prevTo.setDate(prevTo.getDate() - 1)
      const prevFrom = new Date(prevTo)
      prevFrom.setDate(prevFrom.getDate() - daysDiff)
      return { from: prevFrom, to: prevTo }
    }

    case 'previous_period_dow': {
      // Shift by full weeks to preserve weekday alignment
      const weeksToShift = Math.ceil((daysDiff + 1) / 7)
      const daysToShift = weeksToShift * 7
      const prevFrom = new Date(from)
      prevFrom.setDate(prevFrom.getDate() - daysToShift)
      const prevTo = new Date(to)
      prevTo.setDate(prevTo.getDate() - daysToShift)
      return { from: prevFrom, to: prevTo }
    }

    case 'same_period_last_year': {
      // Exact dates last year
      const prevFrom = new Date(from)
      prevFrom.setFullYear(prevFrom.getFullYear() - 1)
      const prevTo = new Date(to)
      prevTo.setFullYear(prevTo.getFullYear() - 1)
      return { from: prevFrom, to: prevTo }
    }

    case 'custom':
    default:
      // Don't auto-calculate for custom mode
      return undefined
  }
}

/**
 * Get dates for a preset period.
 * Exported so it can be used by GlobalFiltersContext to resolve saved periods.
 */
export const getPresetRange = (presetName: string): DateRange => {
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

/**
 * Check if a preset name is valid.
 */
export const isValidPreset = (presetName: string | undefined): boolean => {
  if (!presetName) return false
  return PRESETS.some((preset) => preset.name === presetName)
}

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
  initialPeriod,
  initialComparisonMode = 'previous_period',
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

  const [comparisonMode, setComparisonMode] = useState<ComparisonMode>(
    isValidComparisonMode(initialComparisonMode) ? initialComparisonMode : 'previous_period'
  )

  const openedRangeRef = useRef<DateRange | undefined>()
  const openedRangeCompareRef = useRef<DateRange | undefined>()
  const openedComparisonModeRef = useRef<ComparisonMode | undefined>()

  const [selectedPreset, setSelectedPreset] = useState<string | undefined>(
    initialPeriod && isValidPreset(initialPeriod) ? initialPeriod : undefined
  )

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
      const newCompareTo = initialCompareTo ? new Date(new Date(initialCompareTo).setHours(0, 0, 0, 0)) : newCompareFrom

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

  // Sync selectedPreset when initialPeriod changes
  useEffect(() => {
    if (initialPeriod && isValidPreset(initialPeriod)) {
      setSelectedPreset(initialPeriod)
    }
  }, [initialPeriod])

  // Sync comparisonMode when initialComparisonMode changes
  useEffect(() => {
    if (initialComparisonMode && isValidComparisonMode(initialComparisonMode)) {
      setComparisonMode(initialComparisonMode)
    }
  }, [initialComparisonMode])

  useEffect(() => {
    const handleResize = (): void => {
      setIsSmallScreen(window.innerWidth < 960)
    }

    window.addEventListener('resize', handleResize)
    return () => {
      window.removeEventListener('resize', handleResize)
    }
  }, [])

  const setPreset = (preset: string): void => {
    const newRange = getPresetRange(preset)
    setRange(newRange)
    if (rangeCompare && comparisonMode !== 'custom') {
      const newRangeCompare = calculateComparisonRange(newRange, comparisonMode)
      if (newRangeCompare) {
        setRangeCompare(newRangeCompare)
      }
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
    setComparisonMode(isValidComparisonMode(initialComparisonMode) ? initialComparisonMode : 'previous_period')
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
      openedComparisonModeRef.current = comparisonMode
    }
  }, [isOpen])

  const handleCompareToggle = (checked: boolean) => {
    if (checked) {
      const rangeWithTo = !range.to ? { from: range.from, to: range.from } : range
      if (!range.to) {
        setRange(rangeWithTo)
      }
      const newRangeCompare = calculateComparisonRange(rangeWithTo, comparisonMode)
      if (newRangeCompare) {
        setRangeCompare(newRangeCompare)
      }
    } else {
      setRangeCompare(undefined)
    }
  }

  const handleComparisonModeChange = (mode: ComparisonMode) => {
    setComparisonMode(mode)
    if (mode !== 'custom' && range.from && range.to) {
      const newRangeCompare = calculateComparisonRange(range, mode)
      if (newRangeCompare) {
        setRangeCompare(newRangeCompare)
      }
    }
  }

  const handleCompareDateChange = () => {
    // When user manually edits compare dates, switch to custom mode
    if (comparisonMode !== 'custom') {
      setComparisonMode('custom')
    }
  }

  /**
   * Auto-update comparison range when main range changes.
   * Only updates if:
   * - Comparison is enabled (rangeCompare exists)
   * - Mode is not "custom" (user hasn't manually set dates)
   * - The new range has both from and to dates (selection is complete)
   */
  const autoUpdateComparisonRange = (newRange: DateRange) => {
    if (!rangeCompare || comparisonMode === 'custom') return

    // Only recalculate when we have a complete range (both from and to)
    if (!newRange.from || !newRange.to) return

    const newRangeCompare = calculateComparisonRange(newRange, comparisonMode)
    if (newRangeCompare) {
      setRangeCompare(newRangeCompare)
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
        <Button
          variant="outline"
          className="h-8 px-3 text-xs font-medium border-neutral-200 hover:bg-neutral-50"
        >
          <div className="flex items-center gap-2">
            {/* Preset badge */}
            {selectedPreset && (
              <span className="px-1.5 py-0.5 rounded bg-neutral-100 text-neutral-600 font-medium text-[11px]">
                {PRESETS.find((p) => p.name === selectedPreset)?.label}
              </span>
            )}

            {/* Main date range */}
            <span className="text-neutral-700 font-medium">
              {formatDate(range.from, locale)} - {range.to ? formatDate(range.to, locale) : formatDate(range.from, locale)}
            </span>

            {/* Comparison indicator badge */}
            {rangeCompare && (
              <span className="text-[10px] text-primary font-medium px-1.5 py-0.5 rounded bg-primary/10">
                vs
              </span>
            )}

            <ChevronDown className="h-3.5 w-3.5 text-neutral-400" />
          </div>
        </Button>
      </PopoverTrigger>
      <PopoverContent
        align={align}
        className="w-auto max-h-[var(--radix-popover-content-available-height,calc(100vh-2rem))] overflow-auto p-0"
      >
        {/* Body */}
        <div className="flex">
          {/* Presets sidebar - LEFT side */}
          {!isSmallScreen && (
            <div className="w-[150px] p-3 border-r border-neutral-100 bg-neutral-50/50">
              <div className="flex flex-col gap-0.5">
                {PRESETS.map((preset) => (
                  <button
                    key={preset.name}
                    type="button"
                    onClick={() => setPreset(preset.name)}
                    aria-label={`Select date range: ${preset.label}`}
                    aria-pressed={selectedPreset === preset.name}
                    className={cn(
                      'flex items-center gap-2 px-2.5 py-1.5 text-xs text-left rounded-md transition-colors cursor-pointer',
                      selectedPreset === preset.name
                        ? 'bg-primary/10 text-primary font-medium'
                        : 'text-neutral-600 hover:bg-neutral-100'
                    )}
                  >
                    <span
                      className={cn(
                        'w-2 h-2 rounded-full border-2 transition-colors shrink-0',
                        selectedPreset === preset.name
                          ? 'border-primary bg-primary'
                          : 'border-neutral-300 bg-transparent'
                      )}
                    />
                    {preset.label}
                  </button>
                ))}
              </div>
            </div>
          )}

          {/* Main content - RIGHT side */}
          <div className="flex-1 p-4">
            {/* Mobile preset select */}
            {isSmallScreen && (
              <div className="mb-4">
                <Select defaultValue={selectedPreset} onValueChange={(value) => setPreset(value)}>
                  <SelectTrigger className="h-9 text-xs">
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

            {/* Date inputs */}
            <div className="flex items-center gap-3 mb-4">
              <DateInput
                value={range.from}
                onChange={(date) => {
                  const toDate = range.to == null || date > range.to ? date : range.to
                  const newRange = { from: date, to: toDate }
                  setRange(newRange)
                  autoUpdateComparisonRange(newRange)
                }}
                className="h-9 text-sm flex-1"
              />
              <span className="text-neutral-400">→</span>
              <DateInput
                value={range.to}
                onChange={(date) => {
                  const fromDate = date < range.from ? date : range.from
                  const newRange = { from: fromDate, to: date }
                  setRange(newRange)
                  autoUpdateComparisonRange(newRange)
                }}
                className="h-9 text-sm flex-1"
              />
            </div>

            {/* Compare section */}
            {showCompare && (
              <div className="p-3 rounded-lg bg-neutral-50 border border-neutral-100 mb-4">
                <div className="flex items-center gap-2">
                  <Checkbox
                    checked={Boolean(rangeCompare)}
                    onCheckedChange={handleCompareToggle}
                    id="compare-checkbox"
                  />
                  <Label
                    htmlFor="compare-checkbox"
                    className="text-xs font-medium text-neutral-600 cursor-pointer"
                  >
                    Compare to
                  </Label>
                </div>

                {rangeCompare != null && (
                  <div className="flex items-center gap-2 mt-3">
                    <Select value={comparisonMode} onValueChange={handleComparisonModeChange}>
                      <SelectTrigger className="h-8 text-xs w-[130px] shrink-0">
                        <SelectValue placeholder="Select..." />
                      </SelectTrigger>
                      <SelectContent>
                        {COMPARISON_MODES.map((mode) => (
                          <SelectItem key={mode.name} value={mode.name} className="text-xs">
                            {mode.label}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    <DateInput
                      value={rangeCompare?.from}
                      onChange={(date) => {
                        handleCompareDateChange()
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
                      className="h-8 text-xs flex-1"
                    />
                    <span className="text-neutral-400 text-xs">→</span>
                    <DateInput
                      value={rangeCompare?.to}
                      onChange={(date) => {
                        handleCompareDateChange()
                        if (rangeCompare && rangeCompare.from) {
                          const compareFromDate = date < rangeCompare.from ? date : rangeCompare.from
                          setRangeCompare({
                            ...rangeCompare,
                            from: compareFromDate,
                            to: date,
                          })
                        }
                      }}
                      className="h-8 text-xs flex-1"
                    />
                  </div>
                )}
              </div>
            )}

            {/* Calendar */}
            <Calendar
              mode="range"
              onSelect={(value: { from?: Date; to?: Date } | undefined) => {
                if (value?.from != null) {
                  const newRange = { from: value.from, to: value.to }
                  setRange(newRange)
                  // Auto-update comparison when selection is complete (both from and to)
                  if (newRange.to) {
                    autoUpdateComparisonRange(newRange as DateRange)
                  }
                }
              }}
              selected={range}
              numberOfMonths={isSmallScreen ? 1 : 2}
              defaultMonth={new Date(new Date().setMonth(new Date().getMonth() - (isSmallScreen ? 0 : 1)))}
              cellSize="1.875rem"
              className="p-0"
              compareRange={rangeCompare}
              showOutsideDays={false}
            />
          </div>
        </div>

        {/* Footer */}
        <div className="flex items-center justify-end gap-2 px-4 py-3 border-t border-neutral-100">
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
                onUpdate?.({ range, rangeCompare, period: selectedPreset, comparisonMode })
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
