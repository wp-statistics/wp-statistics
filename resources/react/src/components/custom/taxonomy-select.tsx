/**
 * Taxonomy Select Component
 * A dropdown for selecting taxonomy types (category, post_tag, custom taxonomies).
 */

import { __ } from '@wordpress/i18n'

import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { cn } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'

interface TaxonomySelectProps {
  value: string
  onValueChange: (value: string) => void
  className?: string
}

export function TaxonomySelect({ value, onValueChange, className }: TaxonomySelectProps) {
  const wordpress = WordPress.getInstance()
  const taxonomies = wordpress.getTaxonomies()

  return (
    <Select value={value} onValueChange={onValueChange}>
      <SelectTrigger className={cn(
        'h-8 px-3 text-xs font-medium',
        'bg-background border border-neutral-200 rounded-md',
        'hover:bg-neutral-50',
        'focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
        className
      )}>
        <SelectValue placeholder={__('Select taxonomy', 'wp-statistics')} />
      </SelectTrigger>
      <SelectContent>
        {taxonomies.map((taxonomy) => (
          <SelectItem key={taxonomy.value} value={taxonomy.value}>
            {taxonomy.label}
          </SelectItem>
        ))}
      </SelectContent>
    </Select>
  )
}
