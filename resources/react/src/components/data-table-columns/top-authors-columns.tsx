/**
 * Column definitions for the Top Authors data table.
 * Author-based analytics grouped by author.
 */

import { Link } from '@tanstack/react-router'
import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { ExternalLink, User } from 'lucide-react'

import { DataTableColumnHeader } from '@/components/custom/data-table-column-header'
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
export const TOP_AUTHORS_DEFAULT_HIDDEN_COLUMNS: string[] = ['viewsPerContent', 'bounceRate', 'timeOnPage']

/**
 * Column configuration for API column optimization
 */
export const TOP_AUTHORS_COLUMN_CONFIG: ColumnConfig = {
  baseColumns: ['author_id', 'author_name', 'author_posts_url'],
  columnDependencies: {
    authorName: ['author_id', 'author_name', 'author_avatar', 'author_posts_url'],
    visitors: ['visitors'],
    views: ['views'],
    published: ['published_content'],
    viewsPerContent: ['views', 'published_content'],
    bounceRate: ['bounce_rate'],
    timeOnPage: ['avg_time_on_page'],
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
 * Author Name Cell - Shows avatar and links to Individual Author page with optional external link on hover
 */
function AuthorNameCell({
  authorId,
  authorName,
  authorAvatar,
  authorPostsUrl,
  maxLength = 35,
}: {
  authorId: number
  authorName: string
  authorAvatar: string
  authorPostsUrl?: string
  maxLength?: number
}) {
  const truncatedName = authorName.length > maxLength ? `${authorName.substring(0, maxLength - 3)}...` : authorName
  const needsTruncation = authorName.length > maxLength

  const linkContent = (
    <Link
      to="/individual-author"
      search={{ author_id: authorId }}
      className="flex items-center gap-2 text-xs text-neutral-700 hover:text-primary no-underline"
    >
      <Avatar className="h-8 w-8 flex-shrink-0">
        <AvatarImage src={authorAvatar} alt={authorName} />
        <AvatarFallback>
          <User className="h-4 w-4" />
        </AvatarFallback>
      </Avatar>
      <span className="truncate hover:underline">{truncatedName}</span>
    </Link>
  )

  const content = (
    <div className="group flex items-center gap-2">
      {needsTruncation ? (
        <Tooltip>
          <TooltipTrigger asChild>{linkContent}</TooltipTrigger>
          <TooltipContent>{authorName}</TooltipContent>
        </Tooltip>
      ) : (
        linkContent
      )}
      {authorPostsUrl && (
        <Tooltip>
          <TooltipTrigger asChild>
            <a
              href={authorPostsUrl}
              target="_blank"
              rel="noopener noreferrer"
              className="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity text-neutral-400 hover:text-neutral-600"
              onClick={(e) => e.stopPropagation()}
            >
              <ExternalLink className="h-3.5 w-3.5" />
            </a>
          </TooltipTrigger>
          <TooltipContent>{__('View author posts', 'wp-statistics')}</TooltipContent>
        </Tooltip>
      )}
    </div>
  )

  return <div className="max-w-[200px]">{content}</div>
}

/**
 * Create column definitions for the Top Authors table
 */
export function createTopAuthorsColumns(): ColumnDef<TopAuthor>[] {
  return [
    {
      accessorKey: 'authorName',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} />,
      cell: ({ row }) => (
        <AuthorNameCell
          authorId={row.original.authorId}
          authorName={row.original.authorName}
          authorAvatar={row.original.authorAvatar}
          authorPostsUrl={row.original.authorPostsUrl}
        />
      ),
      meta: {
        title: __('Author', 'wp-statistics'),
        priority: 'primary',
        cardPosition: 'header',
      },
    },
    {
      accessorKey: 'visitors',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.views,
      cell: ({ row }) => <NumericCell value={row.original.visitors} />,
      meta: {
        title: __('Visitors', 'wp-statistics'),
        priority: 'primary',
        cardPosition: 'body',
      },
    },
    {
      accessorKey: 'views',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.views,
      cell: ({ row }) => <NumericCell value={row.original.views} />,
      meta: {
        title: __('Views', 'wp-statistics'),
        priority: 'primary',
        cardPosition: 'body',
      },
    },
    {
      accessorKey: 'published',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.views,
      cell: ({ row }) => <NumericCell value={row.original.published} />,
      meta: {
        title: __('Published', 'wp-statistics'),
        priority: 'primary',
        cardPosition: 'body',
      },
    },
    {
      accessorKey: 'viewsPerContent',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: 100,
      cell: ({ row }) => {
        const vpc = row.original.published > 0 ? row.original.views / row.original.published : 0
        return <NumericCell value={vpc} decimals={1} />
      },
      meta: {
        title: __('Views/Content', 'wp-statistics'),
        priority: 'primary',
        cardPosition: 'body',
        mobileLabel: __('V/Content', 'wp-statistics'),
      },
    },
    {
      accessorKey: 'bounceRate',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.bounceRate,
      cell: ({ row }) => <NumericCell value={row.original.bounceRate} suffix="%" />,
      meta: {
        title: __('Bounce Rate', 'wp-statistics'),
        priority: 'secondary',
        mobileLabel: __('Bounce', 'wp-statistics'),
      },
    },
    {
      accessorKey: 'timeOnPage',
      header: ({ column, table }) => <DataTableColumnHeader column={column} table={table} className="text-right" />,
      size: COLUMN_SIZES.duration,
      cell: ({ row }) => <DurationCell seconds={row.original.timeOnPage} />,
      meta: {
        title: __('Time on Page', 'wp-statistics'),
        priority: 'secondary',
        mobileLabel: __('Time', 'wp-statistics'),
      },
    },
  ]
}
