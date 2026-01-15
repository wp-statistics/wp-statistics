/**
 * Page Options Context
 *
 * Manages widget and metrics visibility per page.
 * Preferences are persisted per page to localStorage and backend.
 */

import { createContext, type ReactNode,useCallback, useEffect, useMemo, useRef, useState } from 'react'

import { debounce } from '@/lib/debounce'
import {
  cancelPendingSave,
  clearCachedPageOptions,
  getCachedPageOptions,
  resetPageOptionsPreferences,
  savePageOptionsPreferences,
  setCachedPageOptions,
} from '@/services/page-options-preferences'

export interface WidgetConfig {
  id: string
  label: string
  defaultVisible?: boolean
}

export interface MetricConfig {
  id: string
  label: string
  defaultVisible?: boolean
}

export interface PageOptionsState {
  pageId: string
  widgets: Record<string, boolean>
  metrics: Record<string, boolean>
  isInitialized: boolean
}

export interface PageOptionsContextValue extends PageOptionsState {
  // Widget actions
  setWidgetVisibility: (widgetId: string, visible: boolean) => void
  toggleWidget: (widgetId: string) => void
  isWidgetVisible: (widgetId: string) => boolean
  getHiddenWidgetCount: () => number

  // Metric actions
  setMetricVisibility: (metricId: string, visible: boolean) => void
  toggleMetric: (metricId: string) => void
  isMetricVisible: (metricId: string) => boolean
  getHiddenMetricCount: () => number

  // General actions
  resetToDefaults: () => void

  // Config access (for UI rendering)
  widgetConfigs: WidgetConfig[]
  metricConfigs: MetricConfig[]
}

export const PageOptionsContext = createContext<PageOptionsContextValue | undefined>(undefined)

export interface PageOptionsProviderProps {
  children: ReactNode
  pageId: string
  widgetConfigs?: WidgetConfig[]
  metricConfigs?: MetricConfig[]
}

export function PageOptionsProvider({
  children,
  pageId,
  widgetConfigs = [],
  metricConfigs = [],
}: PageOptionsProviderProps) {
  // Compute default visibility from configs
  const defaultWidgets = useMemo(() => {
    const defaults: Record<string, boolean> = {}
    widgetConfigs.forEach((config) => {
      defaults[config.id] = config.defaultVisible !== false
    })
    return defaults
  }, [widgetConfigs])

  const defaultMetrics = useMemo(() => {
    const defaults: Record<string, boolean> = {}
    metricConfigs.forEach((config) => {
      defaults[config.id] = config.defaultVisible !== false
    })
    return defaults
  }, [metricConfigs])

  // Initialize state from cache or defaults
  const [state, setState] = useState<PageOptionsState>(() => {
    const cached = getCachedPageOptions(pageId)
    if (cached) {
      return {
        pageId,
        widgets: { ...defaultWidgets, ...cached.widgets },
        metrics: { ...defaultMetrics, ...cached.metrics },
        isInitialized: true,
      }
    }
    return {
      pageId,
      widgets: defaultWidgets,
      metrics: defaultMetrics,
      isInitialized: true,
    }
  })

  // Debounced save to backend
  const debouncedSave = useRef(
    debounce((widgets: Record<string, boolean>, metrics: Record<string, boolean>) => {
      savePageOptionsPreferences(pageId, { widgets, metrics })
    }, 500)
  ).current

  // Cleanup on unmount
  useEffect(() => {
    return () => {
      debouncedSave.cancel()
    }
  }, [debouncedSave])

  // Save to cache immediately and debounce backend save
  const savePreferences = useCallback(
    (widgets: Record<string, boolean>, metrics: Record<string, boolean>) => {
      setCachedPageOptions(pageId, { widgets, metrics })
      debouncedSave(widgets, metrics)
    },
    [pageId, debouncedSave]
  )

  // Widget actions
  const setWidgetVisibility = useCallback(
    (widgetId: string, visible: boolean) => {
      setState((prev) => {
        const newWidgets = { ...prev.widgets, [widgetId]: visible }
        savePreferences(newWidgets, prev.metrics)
        return { ...prev, widgets: newWidgets }
      })
    },
    [savePreferences]
  )

  const toggleWidget = useCallback(
    (widgetId: string) => {
      setState((prev) => {
        const currentVisible = prev.widgets[widgetId] !== false
        const newWidgets = { ...prev.widgets, [widgetId]: !currentVisible }
        savePreferences(newWidgets, prev.metrics)
        return { ...prev, widgets: newWidgets }
      })
    },
    [savePreferences]
  )

  const isWidgetVisible = useCallback(
    (widgetId: string) => {
      return state.widgets[widgetId] !== false
    },
    [state.widgets]
  )

  const getHiddenWidgetCount = useCallback(() => {
    return widgetConfigs.filter((config) => state.widgets[config.id] === false).length
  }, [widgetConfigs, state.widgets])

  // Metric actions
  const setMetricVisibility = useCallback(
    (metricId: string, visible: boolean) => {
      setState((prev) => {
        const newMetrics = { ...prev.metrics, [metricId]: visible }
        savePreferences(prev.widgets, newMetrics)
        return { ...prev, metrics: newMetrics }
      })
    },
    [savePreferences]
  )

  const toggleMetric = useCallback(
    (metricId: string) => {
      setState((prev) => {
        const currentVisible = prev.metrics[metricId] !== false
        const newMetrics = { ...prev.metrics, [metricId]: !currentVisible }
        savePreferences(prev.widgets, newMetrics)
        return { ...prev, metrics: newMetrics }
      })
    },
    [savePreferences]
  )

  const isMetricVisible = useCallback(
    (metricId: string) => {
      return state.metrics[metricId] !== false
    },
    [state.metrics]
  )

  const getHiddenMetricCount = useCallback(() => {
    return metricConfigs.filter((config) => state.metrics[config.id] === false).length
  }, [metricConfigs, state.metrics])

  // Reset to defaults
  const resetToDefaults = useCallback(() => {
    cancelPendingSave()
    clearCachedPageOptions(pageId)
    resetPageOptionsPreferences(pageId)

    setState({
      pageId,
      widgets: defaultWidgets,
      metrics: defaultMetrics,
      isInitialized: true,
    })
  }, [pageId, defaultWidgets, defaultMetrics])

  const value: PageOptionsContextValue = useMemo(
    () => ({
      ...state,
      setWidgetVisibility,
      toggleWidget,
      isWidgetVisible,
      getHiddenWidgetCount,
      setMetricVisibility,
      toggleMetric,
      isMetricVisible,
      getHiddenMetricCount,
      resetToDefaults,
      widgetConfigs,
      metricConfigs,
    }),
    [
      state,
      setWidgetVisibility,
      toggleWidget,
      isWidgetVisible,
      getHiddenWidgetCount,
      setMetricVisibility,
      toggleMetric,
      isMetricVisible,
      getHiddenMetricCount,
      resetToDefaults,
      widgetConfigs,
      metricConfigs,
    ]
  )

  return <PageOptionsContext.Provider value={value}>{children}</PageOptionsContext.Provider>
}
