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

import { useNavigate, useSearch } from '@tanstack/react-router'
import { createContext, useCallback, useEffect, useMemo, useRef, useState, type ReactNode } from 'react'

import type { Filter } from '@/components/custom/filter-bar'
import { getPresetRange, isValidPreset, type DateRange } from '@/components/custom/date-range-picker'
import type { FilterField } from '@/components/custom/filter-row'
import { filtersToUrlFilters, urlFiltersToFilters, type UrlFilter } from '@/lib/filter-utils'
import { formatDateForAPI } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import { saveGlobalFiltersPreferences, resetGlobalFiltersPreferences } from '@/services/global-filters-preferences'

// Debounce helper for performance optimization
function debounce<T extends (...args: Parameters<T>) => void>(fn: T, delay: number): T & { cancel: () => void } {
  let timeoutId: ReturnType<typeof setTimeout> | null = null

  const debounced = ((...args: Parameters<T>) => {
    if (timeoutId) clearTimeout(timeoutId)
    timeoutId = setTimeout(() => {
      fn(...args)
      timeoutId = null
    }, delay)
  }) as T & { cancel: () => void }

  debounced.cancel = () => {
    if (timeoutId) {
      clearTimeout(timeoutId)
      timeoutId = null
    }
  }

  return debounced
}

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
  filters: Filter[]
  page: number
  source: FilterSource
  isInitialized: boolean
}

export interface GlobalFiltersContextValue extends GlobalFiltersState {
  // Actions
  setDateRange: (range: DateRange, compare?: DateRange, period?: string) => void
  setFilters: (filters: Filter[]) => void
  setPage: (page: number) => void
  removeFilter: (filterId: string) => void
  applyFilters: (filters: Filter[]) => void
  resetAll: () => void

  // Computed values for API requests
  apiDateParams: {
    date_from: string
    date_to: string
    previous_date_from?: string
    previous_date_to?: string
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
const parseHashParams = (): Record<string, string> => {
  const hash = window.location.hash
  const queryStart = hash.indexOf('?')
  if (queryStart === -1) return {}

  const queryString = hash.substring(queryStart + 1)
  const params: Record<string, string> = {}
  new URLSearchParams(queryString).forEach((value, key) => {
    params[key] = value
  })
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
  let urlParams: {
    date_from?: string
    date_to?: string
    previous_date_from?: string
    previous_date_to?: string
    filters?: UrlFilter[]
    page?: number
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

  // Performance: Use refs for values needed in hashchange handler
  // This prevents re-registering the event listener on every state change
  const currentDatesRef = useRef({ from: '', to: '' })
  const filterFieldsRef = useRef(filterFields)
  const urlParamsFiltersRef = useRef(urlParams.filters)

  // Keep refs in sync with current values
  useEffect(() => {
    filterFieldsRef.current = filterFields
  }, [filterFields])

  useEffect(() => {
    urlParamsFiltersRef.current = urlParams.filters
  }, [urlParams.filters])

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
        return {
          dateFrom: urlDateFrom,
          dateTo: urlDateTo,
          compareDateFrom: urlCompareFrom,
          compareDateTo: urlCompareTo,
          period: undefined,
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

        return {
          dateFrom: prefDateFrom || defaults.from,
          dateTo: prefDateTo || defaults.to,
          compareDateFrom: prefCompareFrom,
          compareDateTo: prefCompareTo,
          period,
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
      const urlFilters = urlFiltersToFilters(urlParams.filters, filterFields)

      setState({
        dateFrom: urlDateFrom,
        dateTo: urlDateTo,
        compareDateFrom: urlCompareFrom,
        compareDateTo: urlCompareTo,
        period: undefined, // URL params don't include period
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
        filters: urlParams.filters,
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

      const defaults = getDefaultDateRange()

      setState({
        dateFrom: prefDateFrom || defaults.from,
        dateTo: prefDateTo || defaults.to,
        compareDateFrom: prefCompareFrom,
        compareDateTo: prefCompareTo,
        period,
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
      filters: [],
      page: 1,
      source: 'defaults',
      isInitialized: true,
    })

    hasInitialized.current = true
  }, [urlParams, filterFields])

  // Keep currentDatesRef in sync (for hashchange comparison)
  useEffect(() => {
    currentDatesRef.current = {
      from: formatDateForAPI(state.dateFrom),
      to: formatDateForAPI(state.dateTo),
    }
  }, [state.dateFrom, state.dateTo])

  // Watch for URL param changes during SPA navigation (after initialization)
  // Performance optimizations:
  // - Uses refs for comparison to avoid effect re-registration
  // - Debounces rapid hash changes (browser back/forward)
  // - Only registers once after initialization
  useEffect(() => {
    if (!state.isInitialized) return

    const processHashChange = () => {
      const hashParams = parseHashParams()

      // Only process if URL has date params
      if (!hashParams.date_from || !hashParams.date_to) return

      const urlDateFrom = parseDate(hashParams.date_from)
      const urlDateTo = parseDate(hashParams.date_to)

      if (!urlDateFrom || !urlDateTo) return

      // Use ref for comparison (avoids effect re-registration)
      if (hashParams.date_from === currentDatesRef.current.from && hashParams.date_to === currentDatesRef.current.to) {
        // Dates match, no update needed
        return
      }

      // URL has different dates - update state
      const urlCompareFrom = parseDate(hashParams.previous_date_from)
      const urlCompareTo = parseDate(hashParams.previous_date_to)
      // Use refs for filterFields and urlParams.filters
      const urlFilters = urlFiltersToFilters(urlParamsFiltersRef.current, filterFieldsRef.current)
      const effectivePage = parseInt(hashParams.page, 10) || 1

      setState({
        dateFrom: urlDateFrom,
        dateTo: urlDateTo,
        compareDateFrom: urlCompareFrom,
        compareDateTo: urlCompareTo,
        period: undefined, // URL navigation clears period (explicit dates in URL)
        filters: urlFilters,
        page: effectivePage,
        source: 'url',
        isInitialized: true,
      })

      lastSyncedRef.current = JSON.stringify({
        date_from: hashParams.date_from,
        date_to: hashParams.date_to,
        previous_date_from: hashParams.previous_date_from,
        previous_date_to: hashParams.previous_date_to,
        filters: urlParamsFiltersRef.current,
        page: effectivePage,
      })
    }

    // Debounce to handle rapid back/forward navigation
    const debouncedHandler = debounce(processHashChange, 50)

    // Listen for hash changes (SPA navigation)
    window.addEventListener('hashchange', debouncedHandler)

    // Check immediately in case URL changed before this effect ran
    processHashChange()

    return () => {
      window.removeEventListener('hashchange', debouncedHandler)
      debouncedHandler.cancel()
    }
  }, [state.isInitialized]) // Only depends on isInitialized - refs handle the rest

  // Sync state to URL when source is 'url' or 'manual'
  useEffect(() => {
    if (!state.isInitialized) return
    if (state.source === 'preferences' || state.source === 'defaults') {
      // Don't add date params to URL when loaded from preferences or defaults
      return
    }

    const urlFilterData = filtersToUrlFilters(state.filters)
    const currentState = JSON.stringify({
      date_from: formatDateForAPI(state.dateFrom),
      date_to: formatDateForAPI(state.dateTo),
      previous_date_from: state.compareDateFrom ? formatDateForAPI(state.compareDateFrom) : undefined,
      previous_date_to: state.compareDateTo ? formatDateForAPI(state.compareDateTo) : undefined,
      filters: urlFilterData.length > 0 ? urlFilterData : undefined,
      page: state.page > 1 ? state.page : undefined,
    })

    // Only sync if actually changed
    if (currentState === lastSyncedRef.current) return

    lastSyncedRef.current = currentState

    navigate({
      search: (prev) => ({
        ...prev,
        date_from: formatDateForAPI(state.dateFrom),
        date_to: formatDateForAPI(state.dateTo),
        previous_date_from: state.compareDateFrom ? formatDateForAPI(state.compareDateFrom) : undefined,
        previous_date_to: state.compareDateTo ? formatDateForAPI(state.compareDateTo) : undefined,
        filters: urlFilterData.length > 0 ? urlFilterData : undefined,
        page: state.page > 1 ? state.page : undefined,
      }),
      replace: true,
    })
  }, [state, navigate])

  // Set date range (manual action)
  const setDateRange = useCallback(
    (range: DateRange, compare?: DateRange, period?: string) => {
      setState((prev) => {
        const newState = {
          ...prev,
          dateFrom: range.from,
          dateTo: range.to || range.from,
          compareDateFrom: compare?.from,
          compareDateTo: compare?.to,
          period: period && isValidPreset(period) ? period : undefined,
          page: 1, // Reset to first page when dates change
          source: 'manual' as FilterSource,
        }

        // Save to preferences (debounced to batch rapid changes)
        // If a period preset is selected, save the period name for dynamic resolution
        // Otherwise, save the actual dates
        const urlFilters = filtersToUrlFilters(prev.filters)
        debouncedSavePrefs({
          date_from: formatDateForAPI(newState.dateFrom),
          date_to: formatDateForAPI(newState.dateTo),
          previous_date_from: newState.compareDateFrom ? formatDateForAPI(newState.compareDateFrom) : undefined,
          previous_date_to: newState.compareDateTo ? formatDateForAPI(newState.compareDateTo) : undefined,
          period: newState.period, // Save period name for dynamic date resolution
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

    // Clear URL params
    navigate({
      search: (prev) => {
        // eslint-disable-next-line @typescript-eslint/no-unused-vars
        const { date_from, date_to, previous_date_from, previous_date_to, filters, page, ...rest } = prev as Record<
          string,
          unknown
        >
        return rest
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
      filters: [],
      page: 1,
      source: 'defaults',
      isInitialized: true,
    })

    lastSyncedRef.current = null
  }, [navigate])

  // Computed API date params
  const apiDateParams = useMemo(
    () => ({
      date_from: formatDateForAPI(state.dateFrom),
      date_to: formatDateForAPI(state.dateTo),
      previous_date_from: state.compareDateFrom ? formatDateForAPI(state.compareDateFrom) : undefined,
      previous_date_to: state.compareDateTo ? formatDateForAPI(state.compareDateTo) : undefined,
    }),
    [state.dateFrom, state.dateTo, state.compareDateFrom, state.compareDateTo]
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
      apiDateParams,
    }),
    [state, setDateRange, setFilters, setPage, removeFilter, applyFilters, resetAll, apiDateParams]
  )

  return <GlobalFiltersContext.Provider value={value}>{children}</GlobalFiltersContext.Provider>
}
