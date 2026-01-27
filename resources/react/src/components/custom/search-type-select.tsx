/**
 * Search Type Select Component
 * A dropdown for selecting search type (All/Organic/Paid).
 */

import { __ } from '@wordpress/i18n'

import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { cn } from '@/lib/utils'
import type { SearchType } from '@/hooks/use-search-type-filter'

export interface SearchTypeSelectProps {
  /** Current selected value */
  value: SearchType
  /** Handler for value changes */
  onValueChange: (value: SearchType) => void
  /** Available options */
  options?: { value: SearchType; label: string }[]
  /** Additional CSS classes */
  className?: string
}

/**
 * Dropdown select for filtering search engine traffic by type.
 *
 * @example
 * ```tsx
 * const { value, onChange, options } = useSearchTypeFilter()
 *
 * <SearchTypeSelect
 *   value={value}
 *   onValueChange={onChange}
 *   options={options}
 * />
 * ```
 */
export function SearchTypeSelect({
  value,
  onValueChange,
  options = [
    { value: 'all', label: __('All', 'wp-statistics') },
    { value: 'organic', label: __('Organic', 'wp-statistics') },
    { value: 'paid', label: __('Paid', 'wp-statistics') },
  ],
  className,
}: SearchTypeSelectProps) {
  return (
    <Select value={value} onValueChange={onValueChange}>
      <SelectTrigger className={cn(
        'h-8 px-3 text-xs font-medium',
        'bg-background border border-neutral-200 rounded-md',
        'hover:bg-neutral-50',
        'focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
        className
      )}>
        <SelectValue placeholder={__('Search Type', 'wp-statistics')} />
      </SelectTrigger>
      <SelectContent>
        {options.map((option) => (
          <SelectItem key={option.value} value={option.value}>
            {option.label}
          </SelectItem>
        ))}
      </SelectContent>
    </Select>
  )
}
