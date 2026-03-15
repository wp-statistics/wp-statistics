/* eslint-disable react-refresh/only-export-components */

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

export type WidgetSize = 4 | 6 | 8 | 12

export interface WidgetConfig {
  id: string
  label: string
  defaultVisible?: boolean
  defaultSize?: WidgetSize
  allowedSizes?: WidgetSize[]
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
  widgetOrder: string[]
  widgetSizes: Record<string, number>
  widgetPresets: Record<string, string>
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

  // Widget order, size & preset actions
  setWidgetOrder: (order: string[]) => void
  setWidgetSize: (widgetId: string, size: WidgetSize) => void
  getWidgetSize: (widgetId: string) => WidgetSize
  setWidgetPreset: (widgetId: string, preset: string) => void
  getWidgetPreset: (widgetId: string) => string
  getOrderedVisibleWidgets: () => WidgetConfig[]

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

  const defaultWidgetOrder = useMemo(() => widgetConfigs.map((c) => c.id), [widgetConfigs])

  const defaultWidgetSizes = useMemo(() => {
    const defaults: Record<string, number> = {}
    widgetConfigs.forEach((c) => {
      defaults[c.id] = c.defaultSize ?? 12
    })
    return defaults
  }, [widgetConfigs])

  // Initialize state from cache or defaults
  const [state, setState] = useState<PageOptionsState>(() => {
    const cached = getCachedPageOptions(pageId)
    if (cached) {
      return {
        pageId,
        widgets: { ...defaultWidgets, ...cached.widgets },
        metrics: { ...defaultMetrics, ...cached.metrics },
        widgetOrder: cached.widgetOrder ?? defaultWidgetOrder,
        widgetSizes: { ...defaultWidgetSizes, ...cached.widgetSizes },
        widgetPresets: cached.widgetPresets ?? {},
        isInitialized: true,
      }
    }
    return {
      pageId,
      widgets: defaultWidgets,
      metrics: defaultMetrics,
      widgetOrder: defaultWidgetOrder,
      widgetSizes: defaultWidgetSizes,
      widgetPresets: {},
      isInitialized: true,
    }
  })

  // Debounced save to backend
  const debouncedSave = useRef(
    debounce((prefs: { widgets: Record<string, boolean>; metrics: Record<string, boolean>; widgetOrder: string[]; widgetSizes: Record<string, number>; widgetPresets: Record<string, string> }) => {
      savePageOptionsPreferences(pageId, prefs)
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
    (prefs: { widgets: Record<string, boolean>; metrics: Record<string, boolean>; widgetOrder: string[]; widgetSizes: Record<string, number>; widgetPresets: Record<string, string> }) => {
      setCachedPageOptions(pageId, prefs)
      debouncedSave(prefs)
    },
    [pageId, debouncedSave]
  )

  // Helper to build prefs object from state
  const buildPrefs = useCallback(
    (s: PageOptionsState) => ({
      widgets: s.widgets,
      metrics: s.metrics,
      widgetOrder: s.widgetOrder,
      widgetSizes: s.widgetSizes,
      widgetPresets: s.widgetPresets,
    }),
    []
  )

  // Widget actions
  const setWidgetVisibility = useCallback(
    (widgetId: string, visible: boolean) => {
      setState((prev) => {
        const next = { ...prev, widgets: { ...prev.widgets, [widgetId]: visible } }
        savePreferences(buildPrefs(next))
        return next
      })
    },
    [savePreferences, buildPrefs]
  )

  const toggleWidget = useCallback(
    (widgetId: string) => {
      setState((prev) => {
        const currentVisible = prev.widgets[widgetId] !== false
        const next = { ...prev, widgets: { ...prev.widgets, [widgetId]: !currentVisible } }
        savePreferences(buildPrefs(next))
        return next
      })
    },
    [savePreferences, buildPrefs]
  )

  const isWidgetVisible = useCallback(
    (widgetId: string) => {
      return state.widgets[widgetId] !== false
    },
    [state.widgets]
  )

  const getHiddenWidgetCount = useCallback(() => {
    return widgetConfigs.filter((config) => {
      const isVisible = state.widgets[config.id] !== false
      const defaultVisible = defaultWidgets[config.id] !== false
      return isVisible !== defaultVisible
    }).length
  }, [widgetConfigs, state.widgets, defaultWidgets])

  // Metric actions
  const setMetricVisibility = useCallback(
    (metricId: string, visible: boolean) => {
      setState((prev) => {
        const next = { ...prev, metrics: { ...prev.metrics, [metricId]: visible } }
        savePreferences(buildPrefs(next))
        return next
      })
    },
    [savePreferences, buildPrefs]
  )

  const toggleMetric = useCallback(
    (metricId: string) => {
      setState((prev) => {
        const currentVisible = prev.metrics[metricId] !== false
        const next = { ...prev, metrics: { ...prev.metrics, [metricId]: !currentVisible } }
        savePreferences(buildPrefs(next))
        return next
      })
    },
    [savePreferences, buildPrefs]
  )

  const isMetricVisible = useCallback(
    (metricId: string) => {
      return state.metrics[metricId] !== false
    },
    [state.metrics]
  )

  const getHiddenMetricCount = useCallback(() => {
    return metricConfigs.filter((config) => {
      const isVisible = state.metrics[config.id] !== false
      const defaultVisible = defaultMetrics[config.id] !== false
      return isVisible !== defaultVisible
    }).length
  }, [metricConfigs, state.metrics, defaultMetrics])

  // Widget order & size actions
  const setWidgetOrder = useCallback(
    (order: string[]) => {
      setState((prev) => {
        const next = { ...prev, widgetOrder: order }
        savePreferences(buildPrefs(next))
        return next
      })
    },
    [savePreferences, buildPrefs]
  )

  const setWidgetSize = useCallback(
    (widgetId: string, size: WidgetSize) => {
      setState((prev) => {
        const next = { ...prev, widgetSizes: { ...prev.widgetSizes, [widgetId]: size } }
        savePreferences(buildPrefs(next))
        return next
      })
    },
    [savePreferences, buildPrefs]
  )

  const getWidgetSize = useCallback(
    (widgetId: string): WidgetSize => {
      return (state.widgetSizes[widgetId] ?? 12) as WidgetSize
    },
    [state.widgetSizes]
  )

  const setWidgetPreset = useCallback(
    (widgetId: string, preset: string) => {
      setState((prev) => {
        const next = { ...prev, widgetPresets: { ...prev.widgetPresets, [widgetId]: preset } }
        savePreferences(buildPrefs(next))
        return next
      })
    },
    [savePreferences, buildPrefs]
  )

  const getWidgetPreset = useCallback(
    (widgetId: string): string => {
      return state.widgetPresets[widgetId] ?? 'last30'
    },
    [state.widgetPresets]
  )

  const getOrderedVisibleWidgets = useCallback((): WidgetConfig[] => {
    const configMap = new Map(widgetConfigs.map((c) => [c.id, c]))
    return state.widgetOrder
      .filter((id) => state.widgets[id] !== false && configMap.has(id))
      .map((id) => configMap.get(id)!)
  }, [widgetConfigs, state.widgetOrder, state.widgets])

  // Reset to defaults
  const resetToDefaults = useCallback(() => {
    cancelPendingSave()
    clearCachedPageOptions(pageId)
    resetPageOptionsPreferences(pageId)

    setState({
      pageId,
      widgets: defaultWidgets,
      metrics: defaultMetrics,
      widgetOrder: defaultWidgetOrder,
      widgetSizes: defaultWidgetSizes,
      widgetPresets: {},
      isInitialized: true,
    })
  }, [pageId, defaultWidgets, defaultMetrics, defaultWidgetOrder, defaultWidgetSizes])

  const value: PageOptionsContextValue = useMemo(
    () => ({
      ...state,
      setWidgetVisibility,
      toggleWidget,
      isWidgetVisible,
      getHiddenWidgetCount,
      setWidgetOrder,
      setWidgetSize,
      getWidgetSize,
      setWidgetPreset,
      getWidgetPreset,
      getOrderedVisibleWidgets,
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
      setWidgetOrder,
      setWidgetSize,
      getWidgetSize,
      setWidgetPreset,
      getWidgetPreset,
      getOrderedVisibleWidgets,
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
