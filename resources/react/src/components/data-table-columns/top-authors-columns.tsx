/**
 * Column definitions for the Top Authors data table.
 * Author-based analytics grouped by author.
 */

import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { ExternalLink, User } from 'lucide-react'
import { Link } from '@tanstack/react-router'

import { DataTableColumnHeaderSortable } from '@/components/custom/data-table-column-header-sortable'
import { DurationCell, NumericCell } from '@/components/data-table-columns'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import { type ColumnConfig, getDefaultApiColumns } from '@/lib/column-utils'
import type { TopAuthorRecord } from '@/services/content-analytics/get-top-authors'

/**
 * Context identifier for user preferences
 */
export const TOP_AUTHORS_CONTEXT = 'top_authors_data_table'

/**
 * Columns hidden by default (can be shown via column management)
 */
export const TOP_AUTHORS_DEFAULT_HIDDEN_COLUMNS: string[] = ['bounceRate', 'timeOnPage']

/**
 * Column configuration for API column optimization
 */
export const TOP_AUTHORS_COLUMN_CONFIG: ColumnConfig = {
  baseColumns: ['author_id', 'author_name'],
  columnDependencies: {
    authorName: ['author_id', 'author_name', 'author_avatar'],
    visitors: ['visitors'],
    views: ['views'],
    published: ['published_content'],
    viewsPerContent: ['views', 'published_content'],
    bounceRate: ['bounce_rate'],
    timeOnPage: ['avg_time_on_page'],
    viewPage: ['author_posts_url'],
  },
  context: TOP_AUTHORS_CONTEXT,
}

/**
 * Default API columns when no preferences are set
 */
export const TOP_AUTHORS_DEFAULT_API_COLUMNS = getDefaultApiColumns(TOP_AUTHORS_COLUMN_CONFIG)

/**
 * Top Author data interface for the table
 */
export interface TopAuthor {
  id: string
  authorId: number
  authorName: string
  authorAvatar: string
  authorPostsUrl: string
  visitors: number
  views: number
  published: number
  bounceRate: number
  timeOnPage: number
}

/**
 * Transform API response to TopAuthor interface
 */
export function transformTopAuthorData(record: TopAuthorRecord): TopAuthor {
  return {
    id: `author-${record.author_id}`,
    authorId: record.author_id,
    authorName: record.author_name || __('Unknown', 'wp-statistics'),
    authorAvatar: record.author_avatar || '',
    authorPostsUrl: record.author_posts_url || '',
    visitors: Number(record.visitors) || 0,
    views: Number(record.views) || 0,
    published: Number(record.published_content) || 0,
    bounceRate: Math.round(Number(record.bounce_rate) || 0),
    timeOnPage: Math.round(Number(record.avg_time_on_page) || 0),
  }
}

/**
 * Author Name Cell - Shows avatar and links to Individual Author page
 */
function AuthorNameCell({
  authorId,
  authorName,
  authorAvatar,
  maxLength = 35,
}: {
  authorId: number
  authorName: string
  authorAvatar: string
  maxLength?: number
}) {
  const truncatedName = authorName.length > maxLength ? `${authorName.substring(0, maxLength - 3)}...` : authorName
  const needsTruncation = authorName.length > maxLength

  const content = (
    <Link
      to="/individual-author"
      search={{ author_id: authorId }}
      className="flex items-center gap-2 text-xs text-neutral-700 hover:text-primary no-underline group"
    >
      <Avatar className="h-8 w-8 flex-shrink-0">
        <AvatarImage src={authorAvatar} alt={authorName} />
        <AvatarFallback>
          <User className="h-4 w-4" />
        </AvatarFallback>
      </Avatar>
      <span className="truncate group-hover:underline">{truncatedName}</span>
    </Link>
  )

  if (needsTruncation) {
    return (
      <div className="max-w-[200px]">
        <Tooltip>
          <TooltipTrigger asChild>{content}</TooltipTrigger>
          <TooltipContent>{authorName}</TooltipContent>
        </Tooltip>
      </div>
    )
  }

  return <div className="max-w-[200px]">{content}</div>
}

/**
 * Create column definitions for the Top Authors table
 */
export function createTopAuthorsColumns(): ColumnDef<TopAuthor>[] {
  return [
    {
      accessorKey: 'authorName',
      header: ({ column }) => <DataTableColumnHeaderSortable column={column} title={__('Author', 'wp-statistics')} />,
      cell: ({ row }) => (
        <AuthorNameCell
          authorId={row.original.authorId}
          authorName={row.original.authorName}
          authorAvatar={row.original.authorAvatar}
        />
      ),
      enableHiding: false,
      meta: {
        priority: 'primary',
        cardPosition: 'header',
        mobileLabel: __('Author', 'wp-statistics'),
      },
    },
    {
      accessorKey: 'visitors',
      header: ({ column }) => (
        <DataTableColumnHeaderSortable column={column} title={__('Visitors', 'wp-statistics')} className="text-right" />
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
      header: ({ column }) => (
        <DataTableColumnHeaderSortable column={column} title={__('Views', 'wp-statistics')} className="text-right" />
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
      header: ({ column }) => (
        <DataTableColumnHeaderSortable column={column} title={__('Published', 'wp-statistics')} className="text-right" />
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
      header: ({ column }) => (
        <DataTableColumnHeaderSortable
          column={column}
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
      header: ({ column }) => (
        <DataTableColumnHeaderSortable
          column={column}
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
      header: ({ column }) => (
        <DataTableColumnHeaderSortable
          column={column}
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
        row.original.authorPostsUrl ? (
          <a
            href={row.original.authorPostsUrl}
            target="_blank"
            rel="noopener noreferrer"
            className="flex items-center justify-center text-muted-foreground hover:text-foreground"
            title={__('View author posts', 'wp-statistics')}
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
