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
import { WordPress } from '@/lib/wordpress'

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

const isCurrentYear = (date: Date): boolean => {
  return date.getFullYear() === new Date().getFullYear()
}

const formatDate = (date: Date, locale: string = 'en-us', includeYear: boolean = true): string => {
  return date.toLocaleDateString(locale, {
    month: 'short',
    day: 'numeric',
    ...(includeYear && { year: 'numeric' }),
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
  { name: 'thisWeek', label: 'This week' },
  { name: 'last7', label: 'Last 7 days' },
  { name: 'lastWeek', label: 'Last week' },
  { name: 'last28', label: 'Last 28 days' },
  { name: 'last30', label: 'Last 30 days' },
  { name: 'thisMonth', label: 'This month' },
  { name: 'lastMonth', label: 'Last month' },
  { name: 'last90', label: 'Last 90 days' },
  { name: 'quarterToDate', label: 'Quarter to date' },
  { name: 'thisYear', label: 'This year' },
]

/**
 * Comparison mode types for period-over-period comparison.
 */
export type ComparisonMode = 'previous_period' | 'previous_period_dow' | 'same_period_last_month' | 'custom'

export interface ComparisonModeOption {
  name: ComparisonMode
  label: string
}

export const COMPARISON_MODES: ComparisonModeOption[] = [
  { name: 'previous_period', label: 'Previous period' },
  { name: 'previous_period_dow', label: 'Previous period (match day of week)' },
  { name: 'same_period_last_month', label: 'Same period last month' },
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

    case 'same_period_last_month': {
      // Exact dates one month ago
      const prevFrom = new Date(from)
      prevFrom.setMonth(prevFrom.getMonth() - 1)
      const prevTo = new Date(to)
      prevTo.setMonth(prevTo.getMonth() - 1)
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

  const startOfWeek = WordPress.getInstance().getStartOfWeek()
  const from = new Date()
  const to = new Date()

  switch (preset.name) {
    case 'today':
      // Today only
      break

    case 'yesterday':
      from.setDate(from.getDate() - 1)
      to.setDate(to.getDate() - 1)
      break

    case 'thisWeek': {
      // From start of current week to today
      const daysSinceWeekStart = (from.getDay() - startOfWeek + 7) % 7
      from.setDate(from.getDate() - daysSinceWeekStart)
      break
    }

    case 'last7':
      // Last 7 days including today
      from.setDate(from.getDate() - 6)
      break

    case 'lastWeek': {
      // Full previous week (start to end based on startOfWeek)
      const currentDayOfWeek = to.getDay()
      const daysToLastWeekEnd = (currentDayOfWeek - startOfWeek + 7) % 7 + 1
      to.setDate(to.getDate() - daysToLastWeekEnd)
      from.setDate(to.getDate() - 6)
      break
    }

    case 'last28':
      // Last 28 days including today
      from.setDate(from.getDate() - 27)
      break

    case 'last30':
      // Last 30 days including today
      from.setDate(from.getDate() - 29)
      break

    case 'thisMonth':
      // First day of current month to today
      from.setDate(1)
      break

    case 'lastMonth':
      // Full previous month
      from.setMonth(from.getMonth() - 1)
      from.setDate(1)
      to.setDate(0) // Last day of previous month
      break

    case 'last90':
      // Last 90 days including today
      from.setDate(from.getDate() - 89)
      break

    case 'quarterToDate': {
      // First day of current quarter to today
      const quarter = Math.floor(from.getMonth() / 3)
      from.setMonth(quarter * 3, 1)
      break
    }

    case 'thisYear':
      // Jan 1 of current year to today
      from.setMonth(0, 1)
      break
  }

  from.setHours(0, 0, 0, 0)
  to.setHours(23, 59, 59, 999)
  return { from, to }
}

/**
 * Get day name abbreviation from day index (0 = Sunday, 1 = Monday, etc.)
 */
const getDayName = (dayIndex: number): string => {
  const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
  return days[dayIndex]
}

/**
 * Get dynamic label for a preset (shows day names based on WordPress start of week setting).
 */
export const getPresetLabel = (presetName: string): string => {
  const preset = PRESETS.find(({ name }) => name === presetName)
  if (!preset) return ''

  const startOfWeek = WordPress.getInstance().getStartOfWeek()
  const endOfWeek = (startOfWeek + 6) % 7
  const startDay = getDayName(startOfWeek)
  const endDay = getDayName(endOfWeek)

  switch (preset.name) {
    case 'thisWeek':
      return `This week (${startDay}-Today)`
    case 'lastWeek':
      return `Last week (${startDay}-${endDay})`
    case 'thisYear':
      return 'This year (Jan - Today)'
    default:
      return preset.label
  }
}

/**
 * Check if a preset name is valid.
 */
export const isValidPreset = (presetName: string | undefined): boolean => {
  if (!presetName) return false
  return PRESETS.some((preset) => preset.name === presetName)
}

/**
 * Props for DateRangePickerContent - the reusable inner content
 */
export interface DateRangePickerContentProps {
  /** Current date range */
  range: DateRange
  /** Handler for range changes */
  onRangeChange: (range: DateRange) => void
  /** Current comparison range (if enabled) */
  rangeCompare?: DateRange
  /** Handler for comparison range changes */
  onRangeCompareChange: (range: DateRange | undefined) => void
  /** Current selected preset name */
  selectedPreset?: string
  /** Handler for preset selection */
  onPresetSelect: (preset: string) => void
  /** Current comparison mode */
  comparisonMode: ComparisonMode
  /** Handler for comparison mode changes */
  onComparisonModeChange: (mode: ComparisonMode) => void
  /** Whether to show comparison feature */
  showCompare?: boolean
  /** Number of months to show in calendar */
  numberOfMonths?: number
  /** Additional className for the container */
  className?: string
  /** Handler for apply action */
  onApply: () => void
  /** Handler for cancel action (optional - for popover mode) */
  onCancel?: () => void
  /** CSS class for SelectContent (useful for z-index in modals/drawers) */
  selectContentClassName?: string
}

/**
 * DateRangePickerContent - Reusable inner content for date range selection
 * Used by both DateRangePicker (popover) and Options drawer
 */
export function DateRangePickerContent({
  range,
  onRangeChange,
  rangeCompare,
  onRangeCompareChange,
  selectedPreset,
  onPresetSelect,
  comparisonMode,
  onComparisonModeChange,
  showCompare = true,
  numberOfMonths = 1,
  className,
  onApply,
  onCancel,
  selectContentClassName,
}: DateRangePickerContentProps) {
  // Track which range is being edited
  type ActiveRangeTarget = 'main' | 'compare'
  const [activeRangeTarget, setActiveRangeTarget] = useState<ActiveRangeTarget>('main')
  const [awaitingCompareEndDate, setAwaitingCompareEndDate] = useState(false)

  // Reset activeRangeTarget when comparison is disabled
  useEffect(() => {
    if (!rangeCompare && activeRangeTarget === 'compare') {
      setActiveRangeTarget('main')
      setAwaitingCompareEndDate(false)
    }
  }, [rangeCompare, activeRangeTarget])

  // Reset compare selection state when switching to main mode
  useEffect(() => {
    if (activeRangeTarget === 'main') {
      setAwaitingCompareEndDate(false)
    }
  }, [activeRangeTarget])

  const handlePresetSelect = (preset: string) => {
    onPresetSelect(preset)
    const newRange = getPresetRange(preset)
    onRangeChange(newRange)
    if (rangeCompare && comparisonMode !== 'custom') {
      const newRangeCompare = calculateComparisonRange(newRange, comparisonMode)
      if (newRangeCompare) {
        onRangeCompareChange(newRangeCompare)
      }
    }
  }

  const handleCompareToggle = (checked: boolean) => {
    if (checked) {
      const rangeWithTo = !range.to ? { from: range.from, to: range.from } : range
      if (!range.to) {
        onRangeChange(rangeWithTo)
      }
      const newRangeCompare = calculateComparisonRange(rangeWithTo, comparisonMode)
      if (newRangeCompare) {
        onRangeCompareChange(newRangeCompare)
      }
    } else {
      onRangeCompareChange(undefined)
    }
  }

  const handleComparisonModeChange = (mode: ComparisonMode) => {
    onComparisonModeChange(mode)
    if (mode !== 'custom' && range.from && range.to) {
      const newRangeCompare = calculateComparisonRange(range, mode)
      if (newRangeCompare) {
        onRangeCompareChange(newRangeCompare)
      }
    }
  }

  const handleCompareDateChange = () => {
    if (comparisonMode !== 'custom') {
      onComparisonModeChange('custom')
    }
  }

  const autoUpdateComparisonRange = (newRange: DateRange) => {
    if (!rangeCompare || comparisonMode === 'custom') return
    if (!newRange.from || !newRange.to) return
    const newRangeCompare = calculateComparisonRange(newRange, comparisonMode)
    if (newRangeCompare) {
      onRangeCompareChange(newRangeCompare)
    }
  }

  return (
    <div className={cn('@container flex flex-col', className)}>
      {/* Body */}
      <div className="flex flex-col @lg:flex-row">
        {/* Presets sidebar - only on wide containers */}
        <div className="hidden @lg:block w-[185px] p-3 border-r border-neutral-100 bg-neutral-50/50">
            <div className="flex flex-col gap-0.5">
              {PRESETS.map((preset) => (
                <button
                  key={preset.name}
                  type="button"
                  onClick={() => handlePresetSelect(preset.name)}
                  aria-label={`Select date range: ${getPresetLabel(preset.name)}`}
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
                  {getPresetLabel(preset.name)}
                </button>
              ))}
            </div>
          </div>

        {/* Main content */}
        <div className="flex-1 p-4">
          {/* Preset dropdown - narrow containers */}
          <div className="mb-4 @lg:hidden">
            <Select value={selectedPreset || ''} onValueChange={handlePresetSelect}>
              <SelectTrigger className="h-9 text-sm">
                <SelectValue placeholder="Quick select...">
                  {selectedPreset ? getPresetLabel(selectedPreset) : null}
                </SelectValue>
              </SelectTrigger>
              <SelectContent className={selectContentClassName}>
                {PRESETS.map((preset) => (
                  <SelectItem key={preset.name} value={preset.name} className="text-sm">
                    {getPresetLabel(preset.name)}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          {/* Date inputs */}
          <div className="flex items-center gap-2 mb-4">
            <DateInput
              value={range.from}
              onChange={(date) => {
                const toDate = range.to == null || date > range.to ? date : range.to
                const newRange = { from: date, to: toDate }
                onRangeChange(newRange)
                autoUpdateComparisonRange(newRange)
              }}
              onFocus={() => setActiveRangeTarget('main')}
              className={cn(
                'h-9 text-sm w-[130px]',
                activeRangeTarget === 'main' && 'ring-2 ring-primary/30'
              )}
            />
            <span className="text-neutral-400">→</span>
            <DateInput
              value={range.to}
              onChange={(date) => {
                const fromDate = date < range.from ? date : range.from
                const newRange = { from: fromDate, to: date }
                onRangeChange(newRange)
                autoUpdateComparisonRange(newRange)
              }}
              onFocus={() => setActiveRangeTarget('main')}
              className={cn(
                'h-9 text-sm w-[130px]',
                activeRangeTarget === 'main' && 'ring-2 ring-primary/30'
              )}
            />
          </div>

          {/* Compare section */}
          {showCompare && (
            <div className="p-3 rounded-lg bg-neutral-50 border border-neutral-100 mb-4">
              <div className="flex items-center gap-2">
                <Checkbox
                  checked={Boolean(rangeCompare)}
                  onCheckedChange={handleCompareToggle}
                  id="compare-checkbox-content"
                />
                <Label
                  htmlFor="compare-checkbox-content"
                  className="text-xs font-medium text-neutral-600 cursor-pointer"
                >
                  Compare to
                </Label>
              </div>

              {rangeCompare != null && (
                <div className="mt-3 space-y-2">
                  <Select value={comparisonMode} onValueChange={handleComparisonModeChange}>
                    <SelectTrigger className="h-8 text-xs">
                      <SelectValue placeholder="Select..." />
                    </SelectTrigger>
                    <SelectContent className={selectContentClassName}>
                      {COMPARISON_MODES.map((mode) => (
                        <SelectItem key={mode.name} value={mode.name} className="text-xs">
                          {mode.label}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                  <div className="flex items-center gap-2">
                    <DateInput
                      value={rangeCompare?.from}
                      onChange={(date) => {
                        handleCompareDateChange()
                        const compareToDate = rangeCompare?.to == null || date > rangeCompare.to ? date : rangeCompare.to
                        onRangeCompareChange({ from: date, to: compareToDate })
                      }}
                      onFocus={() => setActiveRangeTarget('compare')}
                      className={cn(
                        'h-8 text-xs w-[130px]',
                        activeRangeTarget === 'compare' && 'ring-2 ring-amber-500/30'
                      )}
                    />
                    <span className="text-neutral-400 text-xs">→</span>
                    <DateInput
                      value={rangeCompare?.to}
                      onChange={(date) => {
                        handleCompareDateChange()
                        if (rangeCompare?.from) {
                          const compareFromDate = date < rangeCompare.from ? date : rangeCompare.from
                          onRangeCompareChange({ from: compareFromDate, to: date })
                        }
                      }}
                      onFocus={() => setActiveRangeTarget('compare')}
                      className={cn(
                        'h-8 text-xs w-[130px]',
                        activeRangeTarget === 'compare' && 'ring-2 ring-amber-500/30'
                      )}
                    />
                  </div>
                </div>
              )}
            </div>
          )}

          {/* Compare selection indicator */}
          {activeRangeTarget === 'compare' && rangeCompare && (
            <div className="mb-2 px-1 flex items-center gap-2">
              <div className="w-2 h-2 rounded-full bg-amber-500 animate-pulse" />
              <span className="text-xs text-amber-600 font-medium">
                {awaitingCompareEndDate ? 'Select end date for comparison' : 'Select start date for comparison'}
              </span>
            </div>
          )}

          {/* Calendar - centered */}
          <div className="flex justify-center">
            <Calendar
              mode="range"
              onSelect={(value: { from?: Date; to?: Date } | undefined) => {
                if (activeRangeTarget === 'main' && value?.from != null) {
                  const newRange = { from: value.from, to: value.to }
                  onRangeChange(newRange)
                  if (newRange.to) {
                    autoUpdateComparisonRange(newRange as DateRange)
                  }
                }
              }}
              onDayClick={(day: Date) => {
                if (activeRangeTarget === 'compare') {
                  handleCompareDateChange()
                  if (!awaitingCompareEndDate) {
                    onRangeCompareChange({ from: day, to: day })
                    setAwaitingCompareEndDate(true)
                  } else {
                    const currentFrom = rangeCompare?.from ?? day
                    if (day >= currentFrom) {
                      onRangeCompareChange({ from: currentFrom, to: day })
                    } else {
                      onRangeCompareChange({ from: day, to: currentFrom })
                    }
                    setAwaitingCompareEndDate(false)
                  }
                }
              }}
              selected={range}
              numberOfMonths={numberOfMonths}
              defaultMonth={new Date(new Date().setMonth(new Date().getMonth() - (numberOfMonths > 1 ? 1 : 0)))}
              cellSize="2rem"
              className="p-0"
              compareRange={rangeCompare}
              selectionMode={activeRangeTarget}
              showOutsideDays={false}
            />
          </div>
        </div>
      </div>

      {/* Footer */}
      <div className="flex items-center justify-end gap-2 px-4 py-3 border-t border-neutral-100">
        {onCancel && (
          <Button variant="ghost" size="sm" className="text-xs" onClick={onCancel}>
            Cancel
          </Button>
        )}
        <Button size="sm" className="text-xs px-4" onClick={onApply}>
          Apply
        </Button>
      </div>
    </div>
  )
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

  // Track which range is being edited (for focus-based calendar selection)
  type ActiveRangeTarget = 'main' | 'compare'
  const [activeRangeTarget, setActiveRangeTarget] = useState<ActiveRangeTarget>('main')

  // Track compare range selection state (true = waiting for end date after selecting start)
  const [awaitingCompareEndDate, setAwaitingCompareEndDate] = useState(false)

  const openedRangeRef = useRef<DateRange | undefined>(undefined)
  const openedRangeCompareRef = useRef<DateRange | undefined>(undefined)
  const openedComparisonModeRef = useRef<ComparisonMode | undefined>(undefined)

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

  // Reset activeRangeTarget to 'main' if comparison is disabled
  useEffect(() => {
    if (!rangeCompare && activeRangeTarget === 'compare') {
      setActiveRangeTarget('main')
      setAwaitingCompareEndDate(false)
    }
  }, [rangeCompare, activeRangeTarget])

  // Reset compare selection state when switching to main mode
  useEffect(() => {
    if (activeRangeTarget === 'main') {
      setAwaitingCompareEndDate(false)
    }
  }, [activeRangeTarget])

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
        } else {
          // Reset to main range when opening
          setActiveRangeTarget('main')
          setAwaitingCompareEndDate(false)
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
                {getPresetLabel(selectedPreset)}
              </span>
            )}

            {/* Main date range - show year on end date only if all dates are in current year */}
            <span className="text-neutral-700 font-medium">
              {(() => {
                const allCurrentYear = isCurrentYear(range.from) && (!range.to || isCurrentYear(range.to))
                const toDate = range.to ?? range.from
                // If same day, show with year
                if (range.from.getTime() === toDate.getTime()) {
                  return formatDate(range.from, locale, true)
                }
                // Show year on both dates if any is not current year, otherwise only on end date
                return `${formatDate(range.from, locale, !allCurrentYear)} - ${formatDate(toDate, locale, true)}`
              })()}
            </span>

            {/* Comparison indicator badge */}
            {rangeCompare && (
              <span className="text-[11px] text-primary font-medium px-1.5 py-0.5 rounded bg-primary/10">
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
            <div className="w-[185px] p-3 border-r border-neutral-100 bg-neutral-50/50">
              <div className="flex flex-col gap-0.5">
                {PRESETS.map((preset) => (
                  <button
                    key={preset.name}
                    type="button"
                    onClick={() => setPreset(preset.name)}
                    aria-label={`Select date range: ${getPresetLabel(preset.name)}`}
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
                    {getPresetLabel(preset.name)}
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
                        {getPresetLabel(preset.name)}
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
                onFocus={() => setActiveRangeTarget('main')}
                className={cn(
                  'h-9 text-sm transition-shadow',
                  activeRangeTarget === 'main' && 'ring-2 ring-primary/30'
                )}
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
                onFocus={() => setActiveRangeTarget('main')}
                className={cn(
                  'h-9 text-sm transition-shadow',
                  activeRangeTarget === 'main' && 'ring-2 ring-primary/30'
                )}
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
                      onFocus={() => setActiveRangeTarget('compare')}
                      className={cn(
                        'h-8 text-xs transition-shadow',
                        activeRangeTarget === 'compare' && 'ring-2 ring-amber-500/30'
                      )}
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
                      onFocus={() => setActiveRangeTarget('compare')}
                      className={cn(
                        'h-8 text-xs transition-shadow',
                        activeRangeTarget === 'compare' && 'ring-2 ring-amber-500/30'
                      )}
                    />
                  </div>
                )}
              </div>
            )}

            {/* Compare selection indicator */}
            {activeRangeTarget === 'compare' && rangeCompare && (
              <div className="mb-2 px-1 flex items-center gap-2">
                <div className="w-2 h-2 rounded-full bg-amber-500 animate-pulse" />
                <span className="text-xs text-amber-600 font-medium">
                  {awaitingCompareEndDate ? 'Select end date for comparison' : 'Select start date for comparison'}
                </span>
              </div>
            )}

            {/* Calendar */}
            <Calendar
              mode="range"
              onSelect={(value: { from?: Date; to?: Date } | undefined) => {
                // Only handle main range selection via onSelect
                if (activeRangeTarget === 'main' && value?.from != null) {
                  const newRange = { from: value.from, to: value.to }
                  setRange(newRange)
                  // Auto-update comparison when selection is complete (both from and to)
                  if (newRange.to) {
                    autoUpdateComparisonRange(newRange as DateRange)
                  }
                }
              }}
              onDayClick={(day: Date) => {
                // Handle compare range selection via direct day clicks
                if (activeRangeTarget === 'compare') {
                  handleCompareDateChange() // Switch to custom mode

                  if (!awaitingCompareEndDate) {
                    // First click - set start date
                    setRangeCompare({ from: day, to: day })
                    setAwaitingCompareEndDate(true)
                  } else {
                    // Second click - set end date
                    const currentFrom = rangeCompare?.from ?? day
                    if (day >= currentFrom) {
                      // Clicked date is after or same as start - use as end
                      setRangeCompare({ from: currentFrom, to: day })
                    } else {
                      // Clicked date is before start - swap them
                      setRangeCompare({ from: day, to: currentFrom })
                    }
                    setAwaitingCompareEndDate(false)
                  }
                }
              }}
              selected={range}
              numberOfMonths={isSmallScreen ? 1 : 2}
              defaultMonth={new Date(new Date().setMonth(new Date().getMonth() - (isSmallScreen ? 0 : 1)))}
              cellSize="1.875rem"
              className="p-0"
              compareRange={rangeCompare}
              selectionMode={activeRangeTarget}
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
