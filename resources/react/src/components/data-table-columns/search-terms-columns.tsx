/**
 * Column definitions for the Search Terms data table.
 * Displays search terms and their search counts.
 */

import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { memo } from 'react'

import { StaticSortIndicator } from '@/components/custom/static-sort-indicator'
import { NumericCell } from '@/components/data-table-columns'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import { decodeText } from '@/lib/utils'
import type { SearchTerm as APISearchTerm } from '@/services/visitor-insight/get-search-terms'

/**
 * Context identifier for user preferences
 */
export const SEARCH_TERMS_CONTEXT = 'search_terms'

/**
 * Search term data interface for the table
 */
export interface SearchTermData {
  searchTerm: string
  searches: number
}

/**
 * Transform API response to SearchTermData interface
 */
export function transformSearchTermData(apiSearchTerm: APISearchTerm): SearchTermData {
  return {
    searchTerm: decodeText(apiSearchTerm.search_term) || '',
    searches: parseInt(apiSearchTerm.searches, 10) || 0,
  }
}

/**
 * SearchTermCell - Displays search term with truncation and tooltip
 */
interface SearchTermCellProps {
  term: string
  maxLength?: number
}

const SearchTermCell = memo(function SearchTermCell({ term, maxLength = 50 }: SearchTermCellProps) {
  const shouldTruncate = term.length > maxLength
  const displayTerm = shouldTruncate ? `${term.substring(0, maxLength)}…` : term

  if (shouldTruncate) {
    return (
      <Tooltip>
        <TooltipTrigger asChild>
          <div className="max-w-md min-h-7 align-middle flex items-center cursor-default">
            <span className="text-xs text-neutral-700">{displayTerm}</span>
          </div>
        </TooltipTrigger>
        <TooltipContent side="top" className="max-w-md break-all">
          {term}
        </TooltipContent>
      </Tooltip>
    )
  }

  return (
    <div className="max-w-md min-h-7 align-middle flex items-center">
      <span className="text-xs text-neutral-700">{displayTerm}</span>
    </div>
  )
})

/**
 * Create column definitions for the Search Terms table
 */
export function createSearchTermsColumns(): ColumnDef<SearchTermData>[] {
  return [
    {
      accessorKey: 'searchTerm',
      header: __('Search Term', 'wp-statistics'),
      cell: ({ row }) => <SearchTermCell term={row.original.searchTerm} />,
      enableSorting: false,
      meta: {
        title: __('Search Term', 'wp-statistics'),
        priority: 'primary',
        cardPosition: 'header',
      },
    },
    {
      accessorKey: 'searches',
      header: () => (
        <div className="text-right">
          <StaticSortIndicator title={__('Searches', 'wp-statistics')} direction="desc" />
        </div>
      ),
      size: COLUMN_SIZES.views,
      cell: ({ row }) => <NumericCell value={row.original.searches} />,
      enableSorting: false,
      meta: {
        title: __('Searches', 'wp-statistics'),
        priority: 'primary',
        cardPosition: 'body',
      },
    },
  ]
}
