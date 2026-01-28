import { useState } from 'react'

import type { LockedFilter } from '@/components/custom/filter-panel'
import {
  type MetricConfig,
  PageOptionsProvider,
  type WidgetConfig,
} from '@/contexts/page-options-context'
import { usePageOptions } from '@/hooks/use-page-options'

import { DateRangeDetailView, DateRangeMenuEntry } from './date-range-section'
import { FiltersDetailView, FiltersMenuEntry } from './filters-section'
import { MetricsDetailView, MetricsMenuEntry } from './metrics-section'
import { OptionsDrawer } from './options-drawer'
import { type PageFilterConfig,PageFiltersDetailView, PageFiltersMenuEntry } from './page-filters-section'
import { WidgetsDetailView, WidgetsMenuEntry } from './widgets-section'

/**
 * Configuration for overview pages (visitors-overview, page-insights-overview, etc.)
 */
export interface OverviewOptionsConfig {
  pageId: string
  filterGroup: string
  widgetConfigs: WidgetConfig[]
  metricConfigs: MetricConfig[]
  lockedFilters?: LockedFilter[]
  /** Hide the filters section (for pages that don't use filtering) */
  hideFilters?: boolean
  /** Page-specific filter dropdowns */
  pageFilters?: PageFilterConfig[]
}

/**
 * Hook that provides everything needed for the Options button and drawer on overview pages.
 * Must be used within a PageOptionsProvider.
 */
export function useOverviewOptions(config: OverviewOptionsConfig) {
  const [isOpen, setIsOpen] = useState(false)
  const { getHiddenWidgetCount, getHiddenMetricCount, resetToDefaults } = usePageOptions()

  const hiddenWidgets = getHiddenWidgetCount()
  const hiddenMetrics = getHiddenMetricCount()
  const isActive = hiddenWidgets > 0 || hiddenMetrics > 0

  return {
    isOpen,
    setIsOpen,
    config, // Return config for drawer - single source of truth
    isActive,
    hiddenWidgets,
    hiddenMetrics,
    resetToDefaults,
    triggerProps: {
      onClick: () => setIsOpen(true),
      isActive,
    },
  }
}

/**
 * Return type of useOverviewOptions hook for use with OverviewOptionsDrawer
 */
export type OverviewOptionsReturn = ReturnType<typeof useOverviewOptions>

export interface OverviewOptionsDrawerProps {
  config: OverviewOptionsConfig
  isOpen: boolean
  setIsOpen: (open: boolean) => void
  resetToDefaults: () => void
}

/**
 * Props for OverviewOptionsDrawer when spreading from useOverviewOptions return
 */
export type OverviewOptionsDrawerSpreadProps = Pick<
  OverviewOptionsReturn,
  'config' | 'isOpen' | 'setIsOpen' | 'resetToDefaults'
>

/**
 * Pre-configured Options drawer for overview pages.
 * Includes: DateRange, Filters, Widgets (Premium), Metrics (Premium)
 *
 * Can be used with explicit props or by spreading useOverviewOptions return:
 * @example
 * const options = useOverviewOptions(config)
 * <OverviewOptionsDrawer {...options} />
 */
export function OverviewOptionsDrawer({
  config,
  isOpen,
  setIsOpen,
  resetToDefaults,
}: OverviewOptionsDrawerProps | OverviewOptionsDrawerSpreadProps) {
  return (
    <OptionsDrawer open={isOpen} onOpenChange={setIsOpen} onReset={resetToDefaults}>
      {/* Main menu entries */}
      <DateRangeMenuEntry />
      {config.pageFilters && config.pageFilters.length > 0 && (
        <PageFiltersMenuEntry filters={config.pageFilters} />
      )}
      {!config.hideFilters && (
        <FiltersMenuEntry filterGroup={config.filterGroup} lockedFilters={config.lockedFilters} />
      )}
      <WidgetsMenuEntry />
      <MetricsMenuEntry />

      {/* Detail views */}
      <DateRangeDetailView />
      {config.pageFilters && config.pageFilters.length > 0 && (
        <PageFiltersDetailView filters={config.pageFilters} />
      )}
      {!config.hideFilters && (
        <FiltersDetailView filterGroup={config.filterGroup} lockedFilters={config.lockedFilters} />
      )}
      <WidgetsDetailView />
      <MetricsDetailView />
    </OptionsDrawer>
  )
}

export interface OverviewOptionsProviderProps {
  config: OverviewOptionsConfig
  children: React.ReactNode
}

/**
 * Provider wrapper that sets up PageOptionsProvider with the correct configs.
 * Use this to wrap overview page content.
 */
export function OverviewOptionsProvider({ config, children }: OverviewOptionsProviderProps) {
  return (
    <PageOptionsProvider
      pageId={config.pageId}
      widgetConfigs={config.widgetConfigs}
      metricConfigs={config.metricConfigs}
    >
      {children}
    </PageOptionsProvider>
  )
}
