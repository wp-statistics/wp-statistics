import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { ExternalLink, Info } from 'lucide-react'
import { useMemo } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { Button } from '@/components/ui/button'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import type { TopEntryPageRow } from '@/services/visitor-insight/get-visitor-overview'

type EntryPageData = {
  pageTitle: string
  pageUri: string
  pagePath: string
  queryString: string
  pageType: string
  uniqueEntrances: number
  fullUrl: string
  utmCampaign?: string
}

interface OverviewTopEntryPagesProps {
  data?: TopEntryPageRow[]
}

// Parse URI to extract path and query string
const parseUri = (uri: string): { path: string; queryString: string } => {
  const queryIndex = uri.indexOf('?')
  if (queryIndex === -1) {
    return { path: uri, queryString: '' }
  }
  return {
    path: uri.substring(0, queryIndex),
    queryString: uri.substring(queryIndex),
  }
}

// Extract UTM campaign from query string if present
const getUtmCampaign = (queryString: string): string | undefined => {
  if (!queryString) return undefined
  const params = new URLSearchParams(queryString)
  return params.get('utm_campaign') || undefined
}

// Truncate text at specified length with ellipsis
const truncateText = (text: string, maxLength: number): string => {
  if (text.length <= maxLength) return text
  return `${text.substring(0, maxLength)}…`
}

export const OverviewTopEntryPages = ({ data }: OverviewTopEntryPagesProps) => {
  const siteUrl = typeof window !== 'undefined' ? window.location.origin : ''

  // Transform API data to component format
  const transformedData = useMemo<EntryPageData[]>(() => {
    if (!data || data.length === 0) {
      return []
    }

    return data.map((page) => {
      const uri = page.page_uri || '/'
      const { path, queryString } = parseUri(uri)
      const utmCampaign = getUtmCampaign(queryString)

      return {
        pageTitle: page.page_title || path || __('Unknown', 'wp-statistics'),
        pageUri: uri,
        pagePath: path,
        queryString,
        pageType: page.page_type || 'page',
        uniqueEntrances: Number(page.visitors) || 0,
        fullUrl: `${siteUrl}${uri}`,
        utmCampaign,
      }
    })
  }, [data, siteUrl])

  const columns: ColumnDef<EntryPageData>[] = [
    {
      accessorKey: 'pageTitle',
      header: __('Entry Page', 'wp-statistics'),
      cell: ({ row }) => {
        const pageTitle = row.getValue('pageTitle') as string
        const pagePath = row.original.pagePath
        const queryString = row.original.queryString
        const utmCampaign = row.original.utmCampaign
        const hasQueryString = queryString.length > 0
        const isTruncated = pageTitle.length > 35
        const displayTitle = truncateText(pageTitle, 35)

        // Build tooltip text based on what's available
        const getTooltipText = () => {
          if (hasQueryString && isTruncated) {
            return `${pageTitle}\n${pagePath}${queryString}`
          }
          if (hasQueryString) {
            return `${pagePath}${queryString}`
          }
          if (isTruncated) {
            return pageTitle
          }
          return pagePath
        }

        return (
          <div className="max-w-md text-start flex items-start gap-2">
            <Tooltip>
              <TooltipTrigger asChild>
                <div className="flex items-center gap-1 cursor-pointer">
                  <span className="truncate text-sm text-neutral-700">{displayTitle}</span>
                  {hasQueryString && <Info className="h-3.5 w-3.5 text-neutral-400 shrink-0" />}
                </div>
              </TooltipTrigger>
              <TooltipContent className="whitespace-pre-line">{getTooltipText()}</TooltipContent>
            </Tooltip>
            {utmCampaign && (
              <Tooltip>
                <TooltipTrigger asChild>
                  <span className="text-xs text-neutral-500 truncate cursor-pointer">{utmCampaign}</span>
                </TooltipTrigger>
                <TooltipContent>UTM: {utmCampaign}</TooltipContent>
              </Tooltip>
            )}
          </div>
        )
      },
    },
    {
      accessorKey: 'uniqueEntrances',
      header: __('Unique Entrances', 'wp-statistics'),
      cell: ({ row }) => {
        const uniqueEntrances = row.getValue('uniqueEntrances') as number
        return <div className="text-center text-sm tabular-nums text-neutral-700">{uniqueEntrances.toLocaleString()}</div>
      },
    },
    {
      id: 'viewPage',
      header: '',
      cell: ({ row }) => {
        const fullUrl = row.original.fullUrl

        return (
          <div className="text-right">
            <Tooltip>
              <TooltipTrigger asChild>
                <Button
                  variant="ghost"
                  size="sm"
                  className="h-8 w-8 p-0 text-neutral-500 hover:text-neutral-700"
                  onClick={() => window.open(fullUrl, '_blank', 'noopener,noreferrer')}
                >
                  <ExternalLink className="h-4 w-4" />
                </Button>
              </TooltipTrigger>
              <TooltipContent>{__('View page', 'wp-statistics')}</TooltipContent>
            </Tooltip>
          </div>
        )
      },
    },
  ]

  return (
    <DataTable
      title={__('Top Entry Pages', 'wp-statistics')}
      columns={columns}
      data={transformedData}
      rowLimit={5}
      showPagination={false}
      showColumnManagement={false}
      fullReportLink={{
        text: __('View All Entry Pages', 'wp-statistics'),
        action: () => {
          // TODO: Navigate to entry pages report
          console.log('View all entry pages')
        },
      }}
    />
  )
}
