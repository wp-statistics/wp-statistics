import type { ReactNode } from 'react'
import { useMemo } from 'react'

import { DateRangePicker } from '@/components/custom/date-range-picker'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import type { LockedFilter } from '@/components/custom/filter-panel'
import { OptionsDrawerTrigger } from '@/components/custom/options-drawer'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { getCompatibleFilters } from '@/lib/filter-utils'
import { WordPress } from '@/lib/wordpress'

export interface ReportPageHeaderProps {
  /** Page title displayed on the left */
  title: string
  /** Filter group ID for fetching filter fields (e.g., 'visitors', 'views', 'referrals') */
  filterGroup: FilterGroup
  /** Props for the options drawer trigger button */
  optionsTriggerProps: {
    onClick: () => void
    isActive: boolean
  }
  /** Whether to show the compare toggle in date picker. Defaults to true */
  showCompare?: boolean
  /** Whether to show the filter button. Defaults to true */
  showFilterButton?: boolean
  /** Custom filter fields to use instead of fetching by filterGroup */
  customFilterFields?: FilterField[]
  /** Locked filters displayed as read-only rows in the filter panel */
  lockedFilters?: LockedFilter[]
  /** Additional controls to render in the header (e.g., taxonomy selector) */
  children?: ReactNode
}

/**
 * Standardized header component for report pages.
 * Includes title, filter button, date range picker, and options drawer trigger.
 *
 * @example
 * ```tsx
 * const options = useTableOptions(config)
 *
 * <ReportPageHeader
 *   title={__('Visitors', 'wp-statistics')}
 *   filterGroup="visitors"
 *   optionsTriggerProps={options.triggerProps}
 * />
 * ```
 */
export function ReportPageHeader({
  title,
  filterGroup,
  optionsTriggerProps,
  showCompare = true,
  showFilterButton = true,
  customFilterFields,
  lockedFilters,
  children,
}: ReportPageHeaderProps) {
  const {
    dateFrom,
    dateTo,
    compareDateFrom,
    compareDateTo,
    period,
    filters: appliedFilters,
    handleDateRangeUpdate,
    applyFilters,
    isInitialized,
  } = useGlobalFilters()

  const wp = WordPress.getInstance()

  // Get filter fields from WordPress or use custom fields
  const filterFields = useMemo<FilterField[]>(() => {
    if (customFilterFields) {
      return customFilterFields
    }
    return wp.getFilterFieldsByGroup(filterGroup) as FilterField[]
  }, [wp, filterGroup, customFilterFields])

  // Filter to only include compatible filters for this page
  const compatibleFilters = useMemo(() => {
    return getCompatibleFilters(appliedFilters || [], filterFields)
  }, [appliedFilters, filterFields])

  return (
    <div className="flex items-center justify-between px-4 py-3">
      <h1 className="text-2xl font-semibold text-neutral-800">{title}</h1>
      <div className="flex items-center gap-3">
        {children && <div className="hidden lg:flex">{children}</div>}
        {showFilterButton && (
          <div className="hidden lg:flex">
            {filterFields.length > 0 && isInitialized && (
              <FilterButton
                fields={filterFields}
                appliedFilters={compatibleFilters}
                onApplyFilters={applyFilters}
                filterGroup={filterGroup}
                lockedFilters={lockedFilters}
              />
            )}
          </div>
        )}
        <DateRangePicker
          initialDateFrom={dateFrom}
          initialDateTo={dateTo}
          initialCompareFrom={compareDateFrom}
          initialCompareTo={compareDateTo}
          initialPeriod={period}
          showCompare={showCompare}
          onUpdate={handleDateRangeUpdate}
          align="end"
        />
        <OptionsDrawerTrigger {...optionsTriggerProps} />
      </div>
    </div>
  )
}
