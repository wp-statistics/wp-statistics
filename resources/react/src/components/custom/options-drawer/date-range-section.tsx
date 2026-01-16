import { __ } from '@wordpress/i18n'
import { CalendarIcon } from 'lucide-react'
import { useEffect, useState } from 'react'

import { useGlobalFilters } from '@/hooks/use-global-filters'

import {
  type ComparisonMode,
  DateRangePickerContent,
  type DateRange,
  getPresetLabel,
  isValidComparisonMode,
  isValidPreset,
} from '../date-range-picker'
import { OptionsMenuItem, useOptionsDrawer } from './options-drawer'

/**
 * DateRangeMenuEntry - Shows in the main Options menu
 * Displays current date range and navigates to date-range detail view
 */
export function DateRangeMenuEntry() {
  const { currentView, setCurrentView } = useOptionsDrawer()
  const { period, dateFrom, dateTo, isCompareEnabled } = useGlobalFilters()

  if (currentView !== 'main') {
    return null
  }

  // Format summary text
  const getSummary = (): string => {
    if (period && isValidPreset(period)) {
      const label = getPresetLabel(period)
      if (isCompareEnabled) {
        return `${label} + Compare`
      }
      return label
    }

    // Format dates for custom range
    const formatDate = (date: Date): string => {
      return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
      })
    }

    const dateRangeStr = `${formatDate(dateFrom)} - ${formatDate(dateTo)}`
    if (isCompareEnabled) {
      return `${dateRangeStr} + Compare`
    }
    return dateRangeStr
  }

  return (
    <OptionsMenuItem
      icon={<CalendarIcon className="h-4 w-4" />}
      title={__('Date Range', 'wp-statistics')}
      summary={getSummary()}
      onClick={() => setCurrentView('date-range')}
    />
  )
}

/**
 * DateRangeDetailView - Full date picker UI inside the Options drawer
 * Reuses the DateRangePickerContent component for consistency
 */
export function DateRangeDetailView() {
  const { currentView, goBack } = useOptionsDrawer()
  const {
    dateFrom,
    dateTo,
    compareDateFrom,
    compareDateTo,
    period: currentPeriod,
    comparisonMode: currentComparisonMode,
    setDateRange,
  } = useGlobalFilters()

  // Local state for editing before applying
  const [range, setRange] = useState<DateRange>({ from: dateFrom, to: dateTo })
  const [rangeCompare, setRangeCompare] = useState<DateRange | undefined>(
    compareDateFrom && compareDateTo ? { from: compareDateFrom, to: compareDateTo } : undefined
  )
  const [selectedPreset, setSelectedPreset] = useState<string | undefined>(
    currentPeriod && isValidPreset(currentPeriod) ? currentPeriod : undefined
  )
  const [comparisonMode, setComparisonMode] = useState<ComparisonMode>(
    isValidComparisonMode(currentComparisonMode) ? currentComparisonMode : 'previous_period'
  )

  // Sync state when view opens
  useEffect(() => {
    if (currentView === 'date-range') {
      setRange({ from: dateFrom, to: dateTo })
      setRangeCompare(compareDateFrom && compareDateTo ? { from: compareDateFrom, to: compareDateTo } : undefined)
      setSelectedPreset(currentPeriod && isValidPreset(currentPeriod) ? currentPeriod : undefined)
      setComparisonMode(isValidComparisonMode(currentComparisonMode) ? currentComparisonMode : 'previous_period')
    }
  }, [currentView, dateFrom, dateTo, compareDateFrom, compareDateTo, currentPeriod, currentComparisonMode])

  if (currentView !== 'date-range') {
    return null
  }

  const handleApply = () => {
    setDateRange(range, rangeCompare, selectedPreset, comparisonMode)
    goBack()
  }

  return (
    <DateRangePickerContent
      range={range}
      onRangeChange={setRange}
      rangeCompare={rangeCompare}
      onRangeCompareChange={setRangeCompare}
      selectedPreset={selectedPreset}
      onPresetSelect={setSelectedPreset}
      comparisonMode={comparisonMode}
      onComparisonModeChange={setComparisonMode}
      showCompare={true}
      numberOfMonths={1}
      onApply={handleApply}
      selectContentClassName="z-[100002]"
    />
  )
}
