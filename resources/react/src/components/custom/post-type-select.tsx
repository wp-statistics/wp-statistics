/**
 * Post Type Select Component
 * A dropdown for selecting post types (post, page, custom post types).
 */

import { __ } from '@wordpress/i18n'

import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { cn } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'

interface PostTypeSelectProps {
  value: string
  onValueChange: (value: string) => void
  className?: string
  /** Show "All Post Types" option */
  showAll?: boolean
}

export function PostTypeSelect({ value, onValueChange, className, showAll = true }: PostTypeSelectProps) {
  const wordpress = WordPress.getInstance()
  const postTypeField = wordpress.getFilterFields()?.post_type
  const options = postTypeField?.options ?? [
    { value: 'post', label: __('Post', 'wp-statistics') },
    { value: 'page', label: __('Page', 'wp-statistics') },
  ]

  return (
    <Select value={value} onValueChange={onValueChange}>
      <SelectTrigger className={cn(
        'h-8 px-3 text-xs font-medium',
        'bg-background border border-neutral-200 rounded-md',
        'hover:bg-neutral-50',
        'focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
        className
      )}>
        <SelectValue placeholder={__('Post Type', 'wp-statistics')} />
      </SelectTrigger>
      <SelectContent>
        {showAll && (
          <SelectItem value="all">
            {__('All Post Types', 'wp-statistics')}
          </SelectItem>
        )}
        {options.map((option) => (
          <SelectItem key={option.value} value={String(option.value)}>
            {option.label}
          </SelectItem>
        ))}
      </SelectContent>
    </Select>
  )
}
