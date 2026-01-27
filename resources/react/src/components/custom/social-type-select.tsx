/**
 * Social Type Select Component
 * A dropdown for selecting social type (All/Organic/Paid).
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
import type { SocialType } from '@/hooks/use-social-type-filter'

export interface SocialTypeSelectProps {
  /** Current selected value */
  value: SocialType
  /** Handler for value changes */
  onValueChange: (value: SocialType) => void
  /** Available options */
  options?: { value: SocialType; label: string }[]
  /** Additional CSS classes */
  className?: string
}

/**
 * Dropdown select for filtering social media traffic by type.
 *
 * @example
 * ```tsx
 * const { value, onChange, options } = useSocialTypeFilter()
 *
 * <SocialTypeSelect
 *   value={value}
 *   onValueChange={onChange}
 *   options={options}
 * />
 * ```
 */
export function SocialTypeSelect({
  value,
  onValueChange,
  options = [
    { value: 'all', label: __('All', 'wp-statistics') },
    { value: 'organic', label: __('Organic', 'wp-statistics') },
    { value: 'paid', label: __('Paid', 'wp-statistics') },
  ],
  className,
}: SocialTypeSelectProps) {
  return (
    <Select value={value} onValueChange={onValueChange}>
      <SelectTrigger className={cn(
        'h-8 px-3 text-xs font-medium',
        'bg-background border border-neutral-200 rounded-md',
        'hover:bg-neutral-50',
        'focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
        className
      )}>
        <SelectValue placeholder={__('Social Type', 'wp-statistics')} />
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
