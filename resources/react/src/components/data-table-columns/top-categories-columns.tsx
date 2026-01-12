/**
 * Column definitions for the Top Categories data table.
 * Taxonomy-based analytics grouped by term.
 */

import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { ExternalLink } from 'lucide-react'
import { Link } from '@tanstack/react-router'

import { DataTableColumnHeader } from '@/components/custom/data-table-column-header'
import { DurationCell, NumericCell, ViewPageCell } from '@/components/data-table-columns'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import { type ColumnConfig, getDefaultApiColumns } from '@/lib/column-utils'
import type { TopCategoryRecord } from '@/services/content-analytics/get-top-categories'

/**
 * Context identifier for user preferences
 */
export const TOP_CATEGORIES_CONTEXT = 'top_categories_data_table'

/**
 * Columns hidden by default (can be shown via column management)
 */
export const TOP_CATEGORIES_DEFAULT_HIDDEN_COLUMNS: string[] = ['viewsPerContent', 'bounceRate', 'timeOnPage']

/**
 * Column configuration for API column optimization
 */
export const TOP_CATEGORIES_COLUMN_CONFIG: ColumnConfig = {
  baseColumns: ['term_id', 'term_name', 'term_slug', 'taxonomy_type'],
  columnDependencies: {
    termName: ['term_id', 'term_name', 'term_slug', 'taxonomy_type', 'taxonomy_label'],
    visitors: ['visitors'],
    views: ['views'],
    published: ['published_content'],
    viewsPerContent: ['views', 'published_content'],
    bounceRate: ['bounce_rate'],
    timeOnPage: ['avg_time_on_page'],
    viewPage: ['term_link'],
  },
  context: TOP_CATEGORIES_CONTEXT,
}

/**
 * Default API columns when no preferences are set
 */
export const TOP_CATEGORIES_DEFAULT_API_COLUMNS = getDefaultApiColumns(TOP_CATEGORIES_COLUMN_CONFIG)

/**
 * Top Category data interface for the table
 */
export interface TopCategory {
  id: string
  termId: number
  termName: string
  termSlug: string
  taxonomyType: string
  taxonomyLabel: string
  termLink: string
  visitors: number
  views: number
  published: number
  bounceRate: number
  timeOnPage: number
}

/**
 * Transform API response to TopCategory interface
 */
export function transformTopCategoryData(record: TopCategoryRecord): TopCategory {
  return {
    id: `term-${record.term_id}`,
    termId: record.term_id,
    termName: record.term_name || 'Unknown',
    termSlug: record.term_slug || '',
    taxonomyType: record.taxonomy_type || 'category',
    taxonomyLabel: record.taxonomy_label || record.taxonomy_type || 'Category',
    termLink: record.term_link || '',
    visitors: Number(record.visitors) || 0,
    views: Number(record.views) || 0,
    published: Number(record.published_content) || 0,
    bounceRate: Math.round(Number(record.bounce_rate) || 0),
    timeOnPage: Math.round(Number(record.avg_time_on_page) || 0),
  }
}

/**
 * Term Name Cell - Links to Individual Category page
 */
function TermNameCell({ termId, termName, maxLength = 35 }: { termId: number; termName: string; maxLength?: number }) {
  const truncatedName = termName.length > maxLength ? `${termName.substring(0, maxLength - 3)}...` : termName
  const needsTruncation = termName.length > maxLength

  const linkContent = (
    <Link
      to="/individual-category"
      search={{ term_id: termId }}
      className="text-xs text-neutral-700 hover:text-primary hover:underline truncate"
    >
      {truncatedName}
    </Link>
  )

  if (needsTruncation) {
    return (
      <div className="max-w-[200px]">
        <Tooltip>
          <TooltipTrigger asChild>{linkContent}</TooltipTrigger>
          <TooltipContent>{termName}</TooltipContent>
        </Tooltip>
      </div>
    )
  }

  return <div className="max-w-[200px]">{linkContent}</div>
}

/**
 * Create column definitions for the Top Categories table
 */
export function createTopCategoriesColumns(): ColumnDef<TopCategory>[] {
  return [
    {
      accessorKey: 'termName',
      header: ({ column, table }) => (
        <DataTableColumnHeader column={column} table={table} title={__('Term Name', 'wp-statistics')} />
      ),
      cell: ({ row }) => <TermNameCell termId={row.original.termId} termName={row.original.termName} />,
      enableHiding: false,
      meta: {
        priority: 'primary',
        cardPosition: 'header',
        mobileLabel: __('Term', 'wp-statistics'),
      },
    },
    {
      accessorKey: 'visitors',
      header: ({ column, table }) => (
        <DataTableColumnHeader
          column={column}
          table={table}
          title={__('Visitors', 'wp-statistics')}
          className="text-right"
        />
      ),
      size: COLUMN_SIZES.views,
      cell: ({ row }) => <NumericCell value={row.original.visitors} />,
      meta: {
        priority: 'primary',
        cardPosition: 'body',
        mobileLabel: __('Visitors', 'wp-statistics'),
      },
    },
    {
      accessorKey: 'views',
      header: ({ column, table }) => (
        <DataTableColumnHeader
          column={column}
          table={table}
          title={__('Views', 'wp-statistics')}
          className="text-right"
        />
      ),
      size: COLUMN_SIZES.views,
      cell: ({ row }) => <NumericCell value={row.original.views} />,
      meta: {
        priority: 'primary',
        cardPosition: 'body',
        mobileLabel: __('Views', 'wp-statistics'),
      },
    },
    {
      accessorKey: 'published',
      header: ({ column, table }) => (
        <DataTableColumnHeader
          column={column}
          table={table}
          title={__('Published', 'wp-statistics')}
          className="text-right"
        />
      ),
      size: COLUMN_SIZES.views,
      cell: ({ row }) => <NumericCell value={row.original.published} />,
      meta: {
        priority: 'primary',
        cardPosition: 'body',
        mobileLabel: __('Published', 'wp-statistics'),
      },
    },
    {
      accessorKey: 'viewsPerContent',
      header: ({ column, table }) => (
        <DataTableColumnHeader
          column={column}
          table={table}
          title={__('Views/Content', 'wp-statistics')}
          className="text-right"
        />
      ),
      size: 100,
      cell: ({ row }) => {
        const vpc = row.original.published > 0 ? row.original.views / row.original.published : 0
        return <NumericCell value={vpc} decimals={1} />
      },
      meta: {
        priority: 'primary',
        cardPosition: 'body',
        mobileLabel: __('V/Content', 'wp-statistics'),
      },
    },
    {
      accessorKey: 'bounceRate',
      header: ({ column, table }) => (
        <DataTableColumnHeader
          column={column}
          table={table}
          title={__('Bounce Rate', 'wp-statistics')}
          className="text-right"
        />
      ),
      size: COLUMN_SIZES.bounceRate,
      cell: ({ row }) => <NumericCell value={row.original.bounceRate} suffix="%" />,
      meta: {
        priority: 'secondary',
        mobileLabel: __('Bounce', 'wp-statistics'),
      },
    },
    {
      accessorKey: 'timeOnPage',
      header: ({ column, table }) => (
        <DataTableColumnHeader
          column={column}
          table={table}
          title={__('Time on Page', 'wp-statistics')}
          className="text-right"
        />
      ),
      size: COLUMN_SIZES.duration,
      cell: ({ row }) => <DurationCell seconds={row.original.timeOnPage} />,
      meta: {
        priority: 'secondary',
        mobileLabel: __('Time', 'wp-statistics'),
      },
    },
    {
      accessorKey: 'viewPage',
      header: '',
      size: 50,
      enableHiding: false,
      enableSorting: false,
      cell: ({ row }) =>
        row.original.termLink ? (
          <a
            href={row.original.termLink}
            target="_blank"
            rel="noopener noreferrer"
            className="flex items-center justify-center text-muted-foreground hover:text-foreground"
            title={__('View term archive', 'wp-statistics')}
          >
            <ExternalLink className="h-4 w-4" />
          </a>
        ) : null,
      meta: {
        priority: 'primary',
      },
    },
  ]
}
