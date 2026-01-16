import { useState } from 'react'

import type { LockedFilter } from '@/components/custom/filter-panel'
import { useGlobalFilters } from '@/hooks/use-global-filters'

import { DateRangeDetailView, DateRangeMenuEntry } from './date-range-section'
import { FiltersDetailView, FiltersMenuEntry } from './filters-section'
import { OptionsDrawer } from './options-drawer'

/**
 * Configuration for detail pages (individual-content, individual-category, etc.)
 */
export interface DetailOptionsConfig {
  filterGroup: string
  lockedFilters?: LockedFilter[]
  /** Hide the filters section (for pages that don't use filtering) */
  hideFilters?: boolean
}

/**
 * Hook that provides everything needed for the Options button and drawer on detail pages.
 */
export function useDetailOptions(config: DetailOptionsConfig) {
  const [isOpen, setIsOpen] = useState(false)
  const { filters } = useGlobalFilters()

  const appliedFilterCount = filters?.length ?? 0
  const isActive = appliedFilterCount > 0

  return {
    isOpen,
    setIsOpen,
    isActive,
    appliedFilterCount,
    triggerProps: {
      onClick: () => setIsOpen(true),
      isActive,
    },
  }
}

export interface DetailOptionsDrawerProps {
  config: DetailOptionsConfig
  isOpen: boolean
  setIsOpen: (open: boolean) => void
}

/**
 * Pre-configured Options drawer for detail pages.
 * Includes: DateRange, Filters only
 */
export function DetailOptionsDrawer({
  config,
  isOpen,
  setIsOpen,
}: DetailOptionsDrawerProps) {
  return (
    <OptionsDrawer open={isOpen} onOpenChange={setIsOpen}>
      {/* Main menu entries */}
      <DateRangeMenuEntry />
      {!config.hideFilters && (
        <FiltersMenuEntry filterGroup={config.filterGroup} lockedFilters={config.lockedFilters} />
      )}

      {/* Detail views */}
      <DateRangeDetailView />
      {!config.hideFilters && (
        <FiltersDetailView filterGroup={config.filterGroup} lockedFilters={config.lockedFilters} />
      )}
    </OptionsDrawer>
  )
}
