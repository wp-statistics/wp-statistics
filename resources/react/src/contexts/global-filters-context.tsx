/* eslint-disable react-refresh/only-export-components */

/**
 * Global Filters Context
 *
 * Provides global state management for date ranges and filters across all pages.
 * Implements a hybrid URL + User Preferences approach:
 * - URL params take priority when present (for shareable links)
 * - User preferences serve as fallback (stored in DB)
 * - Manual selection updates both URL and saves to DB
 * - URL param navigation does NOT update preferences
 *
 * Performance optimizations:
 * - Uses refs for comparison values to avoid effect re-registration
 * - Debounces rapid URL changes (browser back/forward)
 * - Batches preference saves with debouncing
 */

import { useLocation, useNavigate, useSearch } from '@tanstack/react-router'
import { createContext, type ReactNode,useCallback, useEffect, useMemo, useRef, useState } from 'react'

import {
  type ComparisonMode,
  type DateRange,
  getPresetRange,
  isValidComparisonMode,
  isValidPreset,
} from '@/components/custom/date-range-picker'
import type { Filter } from '@/components/custom/filter-bar'
import type { FilterField } from '@/components/custom/filter-row'
import { debounce } from '@/lib/debounce'
import {
  filtersToUrlFilters,
  parseBracketFiltersFromParams,
  serializeFiltersToBracketParams,
  type UrlFilter,
  urlFiltersToFilters,
} from '@/lib/filter-utils'
import { formatDateForAPI } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { resetGlobalFiltersPreferences,saveGlobalFiltersPreferences } from '@/services/global-filters-preferences'

/**
 * Source of the current filter/date values
 * - 'url': Values came from URL parameters (shared link)
 * - 'manual': User manually selected values from the UI
 * - 'preferences': Values loaded from saved user preferences
 * - 'defaults': No URL params, no preferences - using system defaults
 */
export type FilterSource = 'url' | 'manual' | 'preferences' | 'defaults'

export interface GlobalFiltersState {
  dateFrom: Date
  dateTo: Date
  compareDateFrom?: Date
  compareDateTo?: Date
  /** Period preset name (e.g., 'yesterday', 'last30') for dynamic date resolution */
  period?: string
  /** Comparison mode for previous period calculation */
  comparisonMode?: ComparisonMode
  filters: Filter[]
  page: number
  source: FilterSource
  isInitialized: boolean
}

/**
 * Parameters for handleDateRangeUpdate callback
 */
export interface DateRangeUpdateValues {
  range: DateRange
  rangeCompare?: DateRange
  period?: string
  comparisonMode?: string
}

export interface GlobalFiltersContextValue extends GlobalFiltersState {
  // Actions
  setDateRange: (range: DateRange, compare?: DateRange, period?: string, comparisonMode?: ComparisonMode) => void
  setFilters: (filters: Filter[]) => void
  setPage: (page: number) => void
  removeFilter: (filterId: string) => void
  applyFilters: (filters: Filter[]) => void
  resetAll: () => void

  // Pre-built callbacks for common patterns (reduces boilerplate in pages)
  /** Pre-built callback for DateRangePicker onUpdate - can be passed directly */
  handleDateRangeUpdate: (values: DateRangeUpdateValues) => void
  /** Pre-built callback for pagination - can be passed directly to onPageChange */
  handlePageChange: (newPage: number) => void

  // Computed values
  /** Whether comparison/previous period is enabled (both compare dates are set) */
  isCompareEnabled: boolean

  // Computed values for API requests
  apiDateParams: {
    date_from: string
    date_to: string
    previous_date_from?: string
    previous_date_to?: string
    comparison_mode?: ComparisonMode
  }
}

// Default date range: last 30 days
const getDefaultDateRange = (): { from: Date; to: Date } => {
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  const thirtyDaysAgo = new Date()
  thirtyDaysAgo.setDate(today.getDate() - 29)
  thirtyDaysAgo.setHours(0, 0, 0, 0)
  return { from: thirtyDaysAgo, to: today }
}

// Parse date string (YYYY-MM-DD) to Date object
const parseDate = (dateString: string | undefined): Date | undefined => {
  if (!dateString) return undefined
  if (!/^\d{4}-\d{2}-\d{2}$/.test(dateString)) return undefined
  const parts = dateString.split('-').map((part) => parseInt(part, 10))
  const date = new Date(parts[0], parts[1] - 1, parts[2])
  date.setHours(0, 0, 0, 0)
  return date
}

// Parse hash params directly (before router is ready)
// This is needed because hash router may not have parsed URL params on first render
// Returns both regular params and parsed bracket notation filters
const parseHashParams = (): Record<string, string> & { _bracketFilters?: UrlFilter[] } => {
  const hash = window.location.hash
  const queryStart = hash.indexOf('?')
  if (queryStart === -1) return {}

  const queryString = hash.substring(queryStart + 1)
  const urlParams = new URLSearchParams(queryString)
  const params: Record<string, string> & { _bracketFilters?: UrlFilter[] } = {}

  urlParams.forEach((value, key) => {
    params[key] = value
  })

  // Parse bracket notation filters
  const bracketFilters = parseBracketFiltersFromParams(urlParams)
  if (bracketFilters.length > 0) {
    params._bracketFilters = bracketFilters
  }

  return params
}

// Create context with undefined default (must be used within provider)
export const GlobalFiltersContext = createContext<GlobalFiltersContextValue | undefined>(undefined)

export interface GlobalFiltersProviderProps {
  children: ReactNode
  filterFields?: FilterField[]
}

export function GlobalFiltersProvider({ children, filterFields = [] }: GlobalFiltersProviderProps) {
  const navigate = useNavigate()

  // Get URL search params (this works at the router level)
  // We use a try-catch because useSearch might fail outside of a route context
  // Note: filters are stored as bracket notation params (filter[field]=op:value), not as 'filters' array
  let urlParams: {
    date_from?: string
    date_to?: string
    previous_date_from?: string
    previous_date_to?: string
    page?: number
    [key: `filter[${string}]`]: string
  } = {}

  try {
    // eslint-disable-next-line react-hooks/rules-of-hooks
    urlParams = useSearch({ strict: false }) as typeof urlParams
  } catch {
    // Not in a route context yet, will initialize from preferences or defaults
  }

  // Track whether we've initialized
  const hasInitialized = useRef(false)
  const lastSyncedRef = useRef<string | null>(null)

  // Debounced preference saving (300ms delay to batch rapid changes)
  const debouncedSavePrefs = useRef(
    debounce((prefs: Parameters<typeof saveGlobalFiltersPreferences>[0]) => {
      saveGlobalFiltersPreferences(prefs)
    }, 300)
  ).current

  // Cleanup debounced save on unmount
  useEffect(() => {
    return () => {
      debouncedSavePrefs.cancel()
    }
  }, [debouncedSavePrefs])

  // State - initialize from preferences synchronously to avoid timing issues with DateRangePicker
  const [state, setState] = useState<GlobalFiltersState>(() => {
    const defaults = getDefaultDateRange()

    // First check if URL has date params (highest priority)
    // Parse hash directly since router might not be ready yet
    const hashParams = parseHashParams()
    if (hashParams.date_from && hashParams.date_to) {
      const urlDateFrom = parseDate(hashParams.date_from)
      const urlDateTo = parseDate(hashParams.date_to)
      if (urlDateFrom && urlDateTo) {
        const urlCompareFrom = parseDate(hashParams.previous_date_from)
        const urlCompareTo = parseDate(hashParams.previous_date_to)
        // URL params don't include period - it's a custom date range shared via URL
        const urlComparisonMode = hashParams.comparison_mode as ComparisonMode | undefined
        return {
          dateFrom: urlDateFrom,
          dateTo: urlDateTo,
          compareDateFrom: urlCompareFrom,
          compareDateTo: urlCompareTo,
          period: undefined,
          comparisonMode: isValidComparisonMode(urlComparisonMode) ? urlComparisonMode : undefined,
          filters: [], // Filters loaded in effect (need filterFields prop)
          page: parseInt(hashParams.page, 10) || 1,
          source: 'url',
          isInitialized: false, // Effect still needs to handle filters
        }
      }
    }

    // Try to load preferences synchronously
    try {
      const wp = WordPress.getInstance()
      const prefs = wp.getGlobalFiltersPreferences()

      if (prefs && (prefs.period || prefs.date_from)) {
        let prefDateFrom: Date | undefined
        let prefDateTo: Date | undefined
        let period: string | undefined

        // If a period is saved, resolve it to current dates
        if (prefs.period && isValidPreset(prefs.period)) {
          period = prefs.period
          const resolved = getPresetRange(period)
          prefDateFrom = resolved.from
          prefDateTo = resolved.to ?? resolved.from
        } else {
          // Fall back to saved dates
          prefDateFrom = parseDate(prefs.date_from)
          prefDateTo = parseDate(prefs.date_to)
        }

        const prefCompareFrom = parseDate(prefs.previous_date_from)
        const prefCompareTo = parseDate(prefs.previous_date_to)
        const prefComparisonMode = prefs.comparison_mode as ComparisonMode | undefined

        return {
          dateFrom: prefDateFrom || defaults.from,
          dateTo: prefDateTo || defaults.to,
          compareDateFrom: prefCompareFrom,
          compareDateTo: prefCompareTo,
          period,
          comparisonMode: isValidComparisonMode(prefComparisonMode) ? prefComparisonMode : undefined,
          filters: [], // Filters loaded in effect (need filterFields prop)
          page: 1,
          source: 'preferences',
          isInitialized: false, // Effect still needs to handle filters
        }
      }
    } catch {
      // WordPress instance not ready yet, fall through to defaults
    }

    return {
      dateFrom: defaults.from,
      dateTo: defaults.to,
      compareDateFrom: undefined,
      compareDateTo: undefined,
      period: undefined,
      comparisonMode: undefined,
      filters: [],
      page: 1,
      source: 'defaults',
      isInitialized: false,
    }
  })

  // Initialize from URL params or preferences
  useEffect(() => {
    if (hasInitialized.current) return

    const wp = WordPress.getInstance()
    const prefs = wp.getGlobalFiltersPreferences()

    // Check URL params first (highest priority)
    // Prioritize hashParams over urlParams because useSearch() can have parsing issues
    // with hash-based routing when main URL also has query params
    const hashParams = parseHashParams()
    // Use hashParams first (more reliable), fall back to urlParams
    const effectiveDateFrom = hashParams.date_from || urlParams.date_from
    const effectiveDateTo = hashParams.date_to || urlParams.date_to
    const effectiveCompareFrom = hashParams.previous_date_from || urlParams.previous_date_from
    const effectiveCompareTo = hashParams.previous_date_to || urlParams.previous_date_to
    const effectivePage = parseInt(hashParams.page, 10) || urlParams.page || 1

    const urlDateFrom = parseDate(effectiveDateFrom)
    const urlDateTo = parseDate(effectiveDateTo)
    const hasUrlDates = urlDateFrom && urlDateTo

    if (hasUrlDates) {
      // Initialize from URL
      const urlCompareFrom = parseDate(effectiveCompareFrom)
      const urlCompareTo = parseDate(effectiveCompareTo)

      // Parse filters from bracket notation params (hashParams has priority)
      // urlParams now contains bracket notation params directly (e.g., 'filter[country]': 'eq:US')
      const effectiveUrlFilters = hashParams._bracketFilters || parseBracketFiltersFromParams(urlParams)
      const urlFilters = urlFiltersToFilters(effectiveUrlFilters, filterFields)
      const effectiveComparisonMode = hashParams.comparison_mode as ComparisonMode | undefined

      // Serialize bracket params for lastSyncedRef comparison
      const bracketParams = serializeFiltersToBracketParams(filtersToUrlFilters(urlFilters))

      setState({
        dateFrom: urlDateFrom,
        dateTo: urlDateTo,
        compareDateFrom: urlCompareFrom,
        compareDateTo: urlCompareTo,
        period: undefined, // URL params don't include period
        comparisonMode: isValidComparisonMode(effectiveComparisonMode) ? effectiveComparisonMode : undefined,
        filters: urlFilters,
        page: effectivePage,
        source: 'url',
        isInitialized: true,
      })

      lastSyncedRef.current = JSON.stringify({
        date_from: effectiveDateFrom,
        date_to: effectiveDateTo,
        previous_date_from: effectiveCompareFrom,
        previous_date_to: effectiveCompareTo,
        comparison_mode: effectiveComparisonMode,
        ...bracketParams,
        page: effectivePage,
      })

      hasInitialized.current = true
      return
    }

    // Check preferences (medium priority)
    if (prefs && (prefs.period || prefs.date_from || prefs.filters?.length)) {
      let prefDateFrom: Date | undefined
      let prefDateTo: Date | undefined
      let period: string | undefined

      // If a period is saved, resolve it to current dates
      if (prefs.period && isValidPreset(prefs.period)) {
        period = prefs.period
        const resolved = getPresetRange(period)
        prefDateFrom = resolved.from
        prefDateTo = resolved.to ?? resolved.from
      } else {
        // Fall back to saved dates
        prefDateFrom = parseDate(prefs.date_from)
        prefDateTo = parseDate(prefs.date_to)
      }

      const prefCompareFrom = parseDate(prefs.previous_date_from)
      const prefCompareTo = parseDate(prefs.previous_date_to)
      const prefFilters = urlFiltersToFilters(prefs.filters, filterFields)
      const prefComparisonMode = prefs.comparison_mode as ComparisonMode | undefined

      const defaults = getDefaultDateRange()

      setState({
        dateFrom: prefDateFrom || defaults.from,
        dateTo: prefDateTo || defaults.to,
        compareDateFrom: prefCompareFrom,
        compareDateTo: prefCompareTo,
        period,
        comparisonMode: isValidComparisonMode(prefComparisonMode) ? prefComparisonMode : undefined,
        filters: prefFilters,
        page: 1,
        source: 'preferences',
        isInitialized: true,
      })

      hasInitialized.current = true
      return
    }

    // Use defaults (lowest priority)
    const defaults = getDefaultDateRange()
    setState({
      dateFrom: defaults.from,
      dateTo: defaults.to,
      compareDateFrom: undefined,
      compareDateTo: undefined,
      period: undefined,
      comparisonMode: undefined,
      filters: [],
      page: 1,
      source: 'defaults',
      isInitialized: true,
    })

    hasInitialized.current = true
  }, [urlParams, filterFields])

  // Track previous URL params for comparison (avoids reacting to own state→URL syncs)
  const prevUrlParamsRef = useRef<string>('')

  // Watch for URL param changes during SPA navigation (after initialization)
  // Uses TanStack Router's reactive useSearch instead of hashchange events,
  // because TanStack Router doesn't fire native hashchange during SPA navigation.
  useEffect(() => {
    if (!state.isInitialized) return

    // Skip if route-change effect just cleared filters (urlParams may be stale)
    if (skipUrlWatchRef.current) {
      skipUrlWatchRef.current = false
      return
    }

    // Parse current URL filters from bracket notation
    const currentUrlFilters = parseBracketFiltersFromParams(urlParams)

    // Create a stable string for comparison
    const urlParamsString = JSON.stringify({
      date_from: urlParams.date_from,
      date_to: urlParams.date_to,
      previous_date_from: urlParams.previous_date_from,
      previous_date_to: urlParams.previous_date_to,
      filters: currentUrlFilters,
    })

    // Skip if URL params haven't changed
    if (urlParamsString === prevUrlParamsRef.current) return
    prevUrlParamsRef.current = urlParamsString

    // Skip if no date params in URL
    if (!urlParams.date_from || !urlParams.date_to) return

    const urlDateFrom = parseDate(urlParams.date_from)
    const urlDateTo = parseDate(urlParams.date_to)
    if (!urlDateFrom || !urlDateTo) return

    // Check if this is actually a change from current state
    const datesMatch = formatDateForAPI(state.dateFrom) === urlParams.date_from &&
                       formatDateForAPI(state.dateTo) === urlParams.date_to
    const currentFiltersString = JSON.stringify(filtersToUrlFilters(state.filters))
    const newFiltersString = JSON.stringify(currentUrlFilters)
    const filtersMatch = currentFiltersString === newFiltersString

    if (datesMatch && filtersMatch) return

    // URL has different dates or filters - update state
    const urlCompareFrom = parseDate(urlParams.previous_date_from)
    const urlCompareTo = parseDate(urlParams.previous_date_to)
    const urlFilters = urlFiltersToFilters(currentUrlFilters, filterFields)
    const effectivePage = parseInt(String(urlParams.page), 10) || 1
    const urlComparisonMode = (urlParams as Record<string, string>).comparison_mode as ComparisonMode | undefined

    // Serialize bracket params for lastSyncedRef comparison
    const bracketParams = serializeFiltersToBracketParams(filtersToUrlFilters(urlFilters))

    setState({
      dateFrom: urlDateFrom,
      dateTo: urlDateTo,
      compareDateFrom: urlCompareFrom,
      compareDateTo: urlCompareTo,
      period: undefined,
      comparisonMode: isValidComparisonMode(urlComparisonMode) ? urlComparisonMode : undefined,
      filters: urlFilters,
      page: effectivePage,
      source: 'url',
      isInitialized: true,
    })

    lastSyncedRef.current = JSON.stringify({
      date_from: urlParams.date_from,
      date_to: urlParams.date_to,
      previous_date_from: urlParams.previous_date_from,
      previous_date_to: urlParams.previous_date_to,
      comparison_mode: urlComparisonMode,
      ...bracketParams,
      page: effectivePage,
    })
  }, [state.isInitialized, urlParams, filterFields, state.filters, state.dateFrom, state.dateTo])

  // Clear URL-sourced filters when route path changes
  // URL-sourced filters are page-specific (e.g., "show cities for Germany")
  // and should not persist when navigating to a different page
  const location = useLocation()
  const prevPathRef = useRef(location.pathname)
  const skipUrlSyncRef = useRef(false)
  const skipUrlWatchRef = useRef(false)

  useEffect(() => {
    if (!state.isInitialized) return

    const prevPath = prevPathRef.current
    prevPathRef.current = location.pathname

    if (prevPath === location.pathname) return
    if (state.source !== 'url') return
    if (state.filters.length === 0) return

    // Reset prevUrlParamsRef so the URL-watching effect can re-evaluate for the new page
    prevUrlParamsRef.current = ''

    // Prevent the URL sync and URL-watching effects from writing stale filters
    // during the same React commit (before setState takes effect)
    skipUrlSyncRef.current = true
    skipUrlWatchRef.current = true
    setState(prev => ({
      ...prev,
      filters: [],
      source: 'preferences',
    }))
  }, [location.pathname, state.isInitialized, state.source, state.filters.length])

  // Sync state to URL when source is 'url' or 'manual'
  useEffect(() => {
    if (!state.isInitialized) return

    // Reset skip flag regardless of source, so it doesn't get stuck
    if (skipUrlSyncRef.current) {
      skipUrlSyncRef.current = false
      return
    }

    if (state.source === 'preferences' || state.source === 'defaults') {
      // Don't add date params to URL when loaded from preferences or defaults
      return
    }

    const urlFilterData = filtersToUrlFilters(state.filters)
    const bracketParams = serializeFiltersToBracketParams(urlFilterData)
    const currentState = JSON.stringify({
      date_from: formatDateForAPI(state.dateFrom),
      date_to: formatDateForAPI(state.dateTo),
      previous_date_from: state.compareDateFrom ? formatDateForAPI(state.compareDateFrom) : undefined,
      previous_date_to: state.compareDateTo ? formatDateForAPI(state.compareDateTo) : undefined,
      comparison_mode: state.comparisonMode,
      ...bracketParams,
      page: state.page > 1 ? state.page : undefined,
    })

    // Only sync if actually changed
    if (currentState === lastSyncedRef.current) return

    lastSyncedRef.current = currentState

    navigate({
      search: (prev) => {
        // Remove old filter keys (both bracket notation and legacy JSON format)
        const cleaned = Object.fromEntries(
          Object.entries(prev as Record<string, unknown>).filter(
            ([key]) => !key.startsWith('filter[') && key !== 'filters'
          )
        )

        // Build result object without 'filters' key - TanStack Router may serialize undefined values
        const result: Record<string, unknown> = {
          ...cleaned,
          date_from: formatDateForAPI(state.dateFrom),
          date_to: formatDateForAPI(state.dateTo),
          ...bracketParams,
        }

        // Only add optional params if they have values
        if (state.compareDateFrom) {
          result.previous_date_from = formatDateForAPI(state.compareDateFrom)
        }
        if (state.compareDateTo) {
          result.previous_date_to = formatDateForAPI(state.compareDateTo)
        }
        if (state.comparisonMode) {
          result.comparison_mode = state.comparisonMode
        }
        if (state.page > 1) {
          result.page = state.page
        }

        // Ensure legacy 'filters' key is removed (in case it was in prev)
        delete result.filters

        return result
      },
      replace: true,
    })
  }, [state, navigate])

  // Set date range (manual action)
  const setDateRange = useCallback(
    (range: DateRange, compare?: DateRange, period?: string, comparisonMode?: ComparisonMode) => {
      setState((prev) => {
        const newState = {
          ...prev,
          dateFrom: range.from,
          dateTo: range.to || range.from,
          compareDateFrom: compare?.from,
          compareDateTo: compare?.to,
          period: period && isValidPreset(period) ? period : undefined,
          comparisonMode: isValidComparisonMode(comparisonMode) ? comparisonMode : prev.comparisonMode,
          page: 1, // Reset to first page when dates change
          source: 'manual' as FilterSource,
        }

        // Save to preferences (debounced to batch rapid changes)
        // If a period preset is selected, save the period name for dynamic resolution
        // Otherwise, save the actual dates
        // Don't save filters that came from URL navigation - only save manually-set filters
        const shouldSaveFilters = prev.source !== 'url'
        const urlFilters = shouldSaveFilters ? filtersToUrlFilters(prev.filters) : []
        debouncedSavePrefs({
          date_from: formatDateForAPI(newState.dateFrom),
          date_to: formatDateForAPI(newState.dateTo),
          previous_date_from: newState.compareDateFrom ? formatDateForAPI(newState.compareDateFrom) : undefined,
          previous_date_to: newState.compareDateTo ? formatDateForAPI(newState.compareDateTo) : undefined,
          period: newState.period, // Save period name for dynamic date resolution
          comparison_mode: newState.comparisonMode,
          filters: urlFilters.length > 0 ? urlFilters : undefined,
        })

        return newState
      })
    },
    [debouncedSavePrefs]
  )

  // Set filters (manual action)
  const setFilters = useCallback(
    (filters: Filter[]) => {
      setState((prev) => {
        const newState = {
          ...prev,
          filters,
          page: 1, // Reset to first page when filters change
          source: 'manual' as FilterSource,
        }

        // Save to preferences (debounced to batch rapid changes)
        const urlFilters = filtersToUrlFilters(filters)
        debouncedSavePrefs({
          date_from: formatDateForAPI(prev.dateFrom),
          date_to: formatDateForAPI(prev.dateTo),
          previous_date_from: prev.compareDateFrom ? formatDateForAPI(prev.compareDateFrom) : undefined,
          previous_date_to: prev.compareDateTo ? formatDateForAPI(prev.compareDateTo) : undefined,
          period: prev.period, // Preserve period when changing filters
          comparison_mode: prev.comparisonMode, // Preserve comparison mode when changing filters
          filters: urlFilters.length > 0 ? urlFilters : undefined,
        })

        return newState
      })
    },
    [debouncedSavePrefs]
  )

  // Apply filters (same as setFilters, for API consistency)
  const applyFilters = useCallback(
    (filters: Filter[]) => {
      setFilters(filters)
    },
    [setFilters]
  )

  // Remove a single filter
  const removeFilter = useCallback(
    (filterId: string) => {
      setState((prev) => {
        const newFilters = prev.filters.filter((f) => f.id !== filterId)
        const newState = {
          ...prev,
          filters: newFilters,
          page: 1,
          source: 'manual' as FilterSource,
        }

        // Save to preferences (debounced to batch rapid changes)
        const urlFilters = filtersToUrlFilters(newFilters)
        debouncedSavePrefs({
          date_from: formatDateForAPI(prev.dateFrom),
          date_to: formatDateForAPI(prev.dateTo),
          previous_date_from: prev.compareDateFrom ? formatDateForAPI(prev.compareDateFrom) : undefined,
          previous_date_to: prev.compareDateTo ? formatDateForAPI(prev.compareDateTo) : undefined,
          period: prev.period, // Preserve period when removing filters
          comparison_mode: prev.comparisonMode, // Preserve comparison mode when removing filters
          filters: urlFilters.length > 0 ? urlFilters : undefined,
        })

        return newState
      })
    },
    [debouncedSavePrefs]
  )

  // Set page (does not change source, does not save to preferences)
  const setPage = useCallback((page: number) => {
    setState((prev) => ({
      ...prev,
      page,
    }))
  }, [])

  // Reset all to defaults
  const resetAll = useCallback(() => {
    const defaults = getDefaultDateRange()

    // Reset preferences in DB
    resetGlobalFiltersPreferences()

    // Clear URL params (including bracket notation filter keys)
    navigate({
      search: (prev) => {
        // Remove date params, legacy filters, page, and all bracket notation filter keys
        return Object.fromEntries(
          Object.entries(prev as Record<string, unknown>).filter(
            ([key]) =>
              !['date_from', 'date_to', 'previous_date_from', 'previous_date_to', 'comparison_mode', 'filters', 'page'].includes(key) &&
              !key.startsWith('filter[')
          )
        )
      },
      replace: true,
    })

    // Reset state
    setState({
      dateFrom: defaults.from,
      dateTo: defaults.to,
      compareDateFrom: undefined,
      compareDateTo: undefined,
      period: undefined,
      comparisonMode: undefined,
      filters: [],
      page: 1,
      source: 'defaults',
      isInitialized: true,
    })

    lastSyncedRef.current = null
  }, [navigate])

  // Pre-built callback for DateRangePicker onUpdate
  // This reduces boilerplate in pages - can be passed directly to DateRangePicker
  const handleDateRangeUpdate = useCallback(
    (values: DateRangeUpdateValues) => {
      setDateRange(values.range, values.rangeCompare, values.period, values.comparisonMode as ComparisonMode)
    },
    [setDateRange]
  )

  // Pre-built callback for pagination
  // This reduces boilerplate in pages - can be passed directly to onPageChange
  const handlePageChange = useCallback(
    (newPage: number) => {
      setPage(newPage)
    },
    [setPage]
  )

  // Computed: whether comparison/previous period is enabled
  const isCompareEnabled = useMemo(
    () => !!(state.compareDateFrom && state.compareDateTo),
    [state.compareDateFrom, state.compareDateTo]
  )

  // Computed API date params
  const apiDateParams = useMemo(
    () => ({
      date_from: formatDateForAPI(state.dateFrom),
      date_to: formatDateForAPI(state.dateTo),
      previous_date_from: state.compareDateFrom ? formatDateForAPI(state.compareDateFrom) : undefined,
      previous_date_to: state.compareDateTo ? formatDateForAPI(state.compareDateTo) : undefined,
      comparison_mode: state.comparisonMode,
    }),
    [state.dateFrom, state.dateTo, state.compareDateFrom, state.compareDateTo, state.comparisonMode]
  )

  const value: GlobalFiltersContextValue = useMemo(
    () => ({
      ...state,
      setDateRange,
      setFilters,
      setPage,
      removeFilter,
      applyFilters,
      resetAll,
      handleDateRangeUpdate,
      handlePageChange,
      isCompareEnabled,
      apiDateParams,
    }),
    [state, setDateRange, setFilters, setPage, removeFilter, applyFilters, resetAll, handleDateRangeUpdate, handlePageChange, isCompareEnabled, apiDateParams]
  )

  return <GlobalFiltersContext.Provider value={value}>{children}</GlobalFiltersContext.Provider>
}
