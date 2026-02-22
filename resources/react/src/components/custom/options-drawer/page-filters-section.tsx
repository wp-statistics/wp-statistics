import { __ } from '@wordpress/i18n'
import { SlidersHorizontal } from 'lucide-react'

import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

import { OptionsMenuItem, useOptionsDrawer } from './options-drawer'

/**
 * Configuration for a single page filter dropdown
 */
export interface PageFilterConfig {
  /** Unique identifier for the filter */
  id: string
  /** Display label shown above the dropdown */
  label: string
  /** Current selected value */
  value: string
  /** Available options for the dropdown */
  options: { value: string; label: string }[]
  /** Callback when value changes - applied immediately */
  onChange: (value: string) => void
}

interface PageFiltersProps {
  filters: PageFilterConfig[]
}

/**
 * Menu entry for page filters - shows in main Options menu
 */
export function PageFiltersMenuEntry({ filters }: PageFiltersProps) {
  const { currentView, setCurrentView } = useOptionsDrawer()

  if (currentView !== 'main' || filters.length === 0) {
    return null
  }

  // Show current selections as summary
  const summary = filters
    .map((f) => f.options.find((o) => o.value === f.value)?.label)
    .filter(Boolean)
    .join(', ')

  return (
    <OptionsMenuItem
      icon={<SlidersHorizontal className="h-4 w-4" />}
      title={__('Page Filters', 'wp-statistics')}
      summary={summary}
      onClick={() => setCurrentView('page-filters')}
    />
  )
}

/**
 * Detail view for page filters - shows filter dropdowns
 */
export function PageFiltersDetailView({ filters }: PageFiltersProps) {
  const { currentView } = useOptionsDrawer()

  if (currentView !== 'page-filters' || filters.length === 0) {
    return null
  }

  return (
    <div className="flex flex-col h-full">
      <p className="text-xs text-neutral-500 px-4 py-3 border-b border-neutral-100 bg-neutral-50/30">
        {__('Configure page-specific filters', 'wp-statistics')}
      </p>

      <div className="flex-1 overflow-y-auto px-4 py-3 space-y-4">
        {filters.map((filter) => (
          <div key={filter.id} className="space-y-1.5">
            <label className="text-sm font-medium text-neutral-700">{filter.label}</label>
            <Select value={filter.value} onValueChange={filter.onChange}>
              <SelectTrigger className="w-full h-9">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                {filter.options.map((option) => (
                  <SelectItem key={option.value} value={option.value}>
                    {option.label}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
        ))}
      </div>
    </div>
  )
}
