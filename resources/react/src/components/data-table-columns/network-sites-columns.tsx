/**
 * Column definitions for the Network Sites data table.
 * Displays site information and statistics for WordPress multisite networks.
 */

import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { ExternalLink } from 'lucide-react'
import { memo } from 'react'

import { StaticSortIndicator } from '@/components/custom/static-sort-indicator'
import { NumericCell } from '@/components/data-table-columns'
import { Button } from '@/components/ui/button'
import { COLUMN_SIZES } from '@/lib/column-sizes'
import type { NetworkSiteStats } from '@/services/network/get-network-stats'

/**
 * Context identifier for user preferences
 */
export const NETWORK_SITES_CONTEXT = 'network_sites'

/**
 * SiteInfoCell - Displays site name and URL
 */
interface SiteInfoCellProps {
  name: string
  url: string
}

const SiteInfoCell = memo(function SiteInfoCell({ name, url }: SiteInfoCellProps) {
  return (
    <div>
      <div className="font-medium text-xs text-neutral-900">{name}</div>
      <a
        href={url}
        target="_blank"
        rel="noopener noreferrer"
        className="text-xs text-muted-foreground hover:text-primary hover:underline"
      >
        {url}
      </a>
    </div>
  )
})

/**
 * ErrorNumericCell - Shows error state or numeric value
 */
interface ErrorNumericCellProps {
  value: number
  hasError: boolean
  showDash?: boolean
}

const ErrorNumericCell = memo(function ErrorNumericCell({
  value,
  hasError,
  showDash = false,
}: ErrorNumericCellProps) {
  if (hasError) {
    return showDash ? (
      <div className="text-right">
        <span className="text-xs text-neutral-400">-</span>
      </div>
    ) : (
      <div className="text-right">
        <span className="text-xs text-destructive">{__('Error', 'wp-statistics')}</span>
      </div>
    )
  }
  return <NumericCell value={value} />
})

/**
 * SiteActionsCell - Dashboard link button
 */
interface SiteActionsCellProps {
  adminUrl: string
}

const SiteActionsCell = memo(function SiteActionsCell({ adminUrl }: SiteActionsCellProps) {
  return (
    <div className="text-right">
      <Button variant="outline" size="sm" asChild>
        <a href={adminUrl} target="_blank" rel="noopener noreferrer">
          <ExternalLink className="h-3.5 w-3.5 mr-1.5" />
          {__('Dashboard', 'wp-statistics')}
        </a>
      </Button>
    </div>
  )
})

/**
 * Create column definitions for the Network Sites table
 */
export function createNetworkSitesColumns(): ColumnDef<NetworkSiteStats>[] {
  return [
    {
      accessorKey: 'name',
      header: __('Site', 'wp-statistics'),
      cell: ({ row }) => <SiteInfoCell name={row.original.name} url={row.original.url} />,
      enableHiding: false,
      enableSorting: false,
      meta: {
        title: __('Site', 'wp-statistics'),
        priority: 'primary',
        cardPosition: 'header',
      },
    },
    {
      accessorKey: 'visitors',
      header: () => (
        <div className="text-right">
          <StaticSortIndicator title={__('Visitors', 'wp-statistics')} direction="desc" />
        </div>
      ),
      size: COLUMN_SIZES.views,
      cell: ({ row }) => (
        <ErrorNumericCell value={row.original.visitors} hasError={!!row.original.error} />
      ),
      enableSorting: false,
      meta: {
        title: __('Visitors', 'wp-statistics'),
        priority: 'primary',
        cardPosition: 'body',
      },
    },
    {
      accessorKey: 'views',
      header: () => (
        <div className="text-right">{__('Views', 'wp-statistics')}</div>
      ),
      size: COLUMN_SIZES.views,
      cell: ({ row }) => (
        <ErrorNumericCell value={row.original.views} hasError={!!row.original.error} showDash />
      ),
      enableSorting: false,
      meta: {
        title: __('Views', 'wp-statistics'),
        priority: 'primary',
        cardPosition: 'body',
      },
    },
    {
      accessorKey: 'sessions',
      header: () => (
        <div className="text-right">{__('Sessions', 'wp-statistics')}</div>
      ),
      size: COLUMN_SIZES.sessions,
      cell: ({ row }) => (
        <ErrorNumericCell value={row.original.sessions} hasError={!!row.original.error} showDash />
      ),
      enableSorting: false,
      meta: {
        title: __('Sessions', 'wp-statistics'),
        priority: 'secondary',
        cardPosition: 'body',
      },
    },
    {
      id: 'actions',
      header: () => <div className="text-right">{__('Actions', 'wp-statistics')}</div>,
      cell: ({ row }) => <SiteActionsCell adminUrl={row.original.admin_url} />,
      enableHiding: false,
      enableSorting: false,
      meta: {
        title: __('Actions', 'wp-statistics'),
        priority: 'primary',
      },
    },
  ]
}
