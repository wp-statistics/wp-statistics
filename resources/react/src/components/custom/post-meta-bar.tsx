import { __ } from '@wordpress/i18n'
import { Calendar, User, FileText, RefreshCw } from 'lucide-react'
import { Fragment, type ReactNode } from 'react'

import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import { cn } from '@/lib/utils'
import type { TermInfo } from '@/services/content-analytics/get-single-content'

interface PostMetaBarProps {
  authorName?: string | null
  postTypeLabel?: string | null
  publishedDate?: string | null
  modifiedDate?: string | null
  terms?: TermInfo[]
  className?: string
}

/**
 * Format date string for display
 */
function formatDisplayDate(dateString: string | null | undefined): string | null {
  if (!dateString) return null
  try {
    const date = new Date(dateString)
    if (isNaN(date.getTime())) return null
    return date.toLocaleDateString(undefined, {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    })
  } catch {
    return null
  }
}

/**
 * Check if two dates are different (ignoring time)
 */
function areDatesDifferent(date1: string | null | undefined, date2: string | null | undefined): boolean {
  if (!date1 || !date2) return false
  const d1 = date1.split(' ')[0] || date1.split('T')[0]
  const d2 = date2.split(' ')[0] || date2.split('T')[0]
  return d1 !== d2
}

/**
 * Group terms by their taxonomy
 */
function groupTermsByTaxonomy(terms: TermInfo[]): Record<string, TermInfo[]> {
  return terms.reduce(
    (acc, term) => {
      const taxonomy = term.taxonomy
      if (!acc[taxonomy]) acc[taxonomy] = []
      acc[taxonomy].push(term)
      return acc
    },
    {} as Record<string, TermInfo[]>
  )
}

/**
 * Get human-readable taxonomy label
 */
function getTaxonomyLabel(taxonomy: string): string {
  const labels: Record<string, string> = {
    category: __('Categories', 'wp-statistics'),
    post_tag: __('Tags', 'wp-statistics'),
  }
  // Fallback: capitalize and replace underscores
  return labels[taxonomy] || taxonomy.charAt(0).toUpperCase() + taxonomy.slice(1).replace(/_/g, ' ')
}

/**
 * Compact metadata bar for single content report header
 */
export function PostMetaBar({
  authorName,
  postTypeLabel,
  publishedDate,
  modifiedDate,
  terms = [],
  className,
}: PostMetaBarProps) {
  const formattedPublished = formatDisplayDate(publishedDate)
  const formattedModified = formatDisplayDate(modifiedDate)
  const showModified = areDatesDifferent(publishedDate, modifiedDate) && formattedModified

  const groupedTerms = groupTermsByTaxonomy(terms)
  const hasTerms = terms.length > 0

  // Collect visible meta items
  const metaItems: ReactNode[] = []

  if (postTypeLabel) {
    metaItems.push(
      <div key="post-type" className="flex items-center gap-1.5">
        <FileText className="h-3.5 w-3.5" />
        <span>{postTypeLabel}</span>
      </div>
    )
  }

  if (authorName) {
    metaItems.push(
      <div key="author" className="flex items-center gap-1.5">
        <User className="h-3.5 w-3.5" />
        <span>{authorName}</span>
      </div>
    )
  }

  if (formattedPublished) {
    metaItems.push(
      <div key="published" className="flex items-center gap-1.5">
        <Calendar className="h-3.5 w-3.5" />
        <span>
          {__('Published:', 'wp-statistics')} {formattedPublished}
        </span>
      </div>
    )
  }

  if (showModified) {
    metaItems.push(
      <div key="modified" className="flex items-center gap-1.5">
        <RefreshCw className="h-3.5 w-3.5" />
        <span>
          {__('Updated:', 'wp-statistics')} {formattedModified}
        </span>
      </div>
    )
  }

  if (hasTerms) {
    metaItems.push(
      <div key="terms" className="flex flex-wrap items-center gap-x-3 gap-y-1">
        {Object.entries(groupedTerms).map(([taxonomy, taxonomyTerms]) => (
          <div key={taxonomy} className="flex items-center gap-1.5">
            <span className="text-muted-foreground">{getTaxonomyLabel(taxonomy)}:</span>
            <div className="flex flex-wrap gap-1">
              {taxonomyTerms.map((term) => (
                <Badge key={term.term_id} variant="secondary" className="text-xs py-0 px-1.5">
                  {term.name}
                </Badge>
              ))}
            </div>
          </div>
        ))}
      </div>
    )
  }

  // Don't render if no items
  if (metaItems.length === 0) {
    return null
  }

  return (
    <div className={cn('flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-muted-foreground', className)}>
      {metaItems.map((item, index) => (
        <Fragment key={index}>
          {index > 0 && <Separator orientation="vertical" className="h-4" />}
          {item}
        </Fragment>
      ))}
    </div>
  )
}
