import { Link, useRouter } from '@tanstack/react-router'
import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { Info } from 'lucide-react'
import { useMemo } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { Badge } from '@/components/ui/badge'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import { WordPress } from '@/lib/wordpress'
import type { TopVisitorsData } from '@/services/visitor-insight/get-visitor-overview'

type TopVisitorData = {
  visitorInfo: {
    country: { code: string; name: string; region: string; city: string }
    os: { icon: string; name: string }
    browser: { icon: string; name: string; version: string }
    user?: { username: string; id: number; email: string; role: string }
    ipAddress?: string
    hash?: string
  }
  totalViews: number
  referrer: {
    domain?: string
    fullUrl?: string
    category: string
  }
  entryPage: {
    title: string
    url: string
    hasQueryString: boolean
    queryString?: string
    utmCampaign?: string
  }
  exitPage: {
    title: string
    url: string
  }
}

interface VisitorInfoColumnConfig {
  pluginUrl: string
  trackLoggedInEnabled: boolean
  hashEnabled: boolean
}

interface OverviewTopVisitorsProps {
  data?: TopVisitorsData['rows']
}

export const OverviewTopVisitors = ({ data }: OverviewTopVisitorsProps) => {
  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()
  const router = useRouter()

  // Transform API data to component format
  const transformedData = useMemo<TopVisitorData[]>(() => {
    if (!data || data.length === 0) {
      return []
    }

    return data.map((visitor) => ({
      visitorInfo: {
        country: {
          code: visitor.country_code?.toLowerCase() || '000',
          name: visitor.country_name || 'Unknown',
          region: visitor.region_name || '',
          city: visitor.city_name || '',
        },
        os: {
          icon: (visitor.os_name || 'unknown').toLowerCase().replace(/\s+/g, '_'),
          name: visitor.os_name || 'Unknown',
        },
        browser: {
          icon: (visitor.browser_name || 'unknown').toLowerCase().replace(/\s+/g, '_'),
          name: visitor.browser_name || 'Unknown',
          version: visitor.browser_version || '',
        },
        ...(visitor.user_id && visitor.user_login
          ? {
              user: {
                username: visitor.user_login,
                id: visitor.user_id,
                email: visitor.user_email || '',
                role: visitor.user_role || '',
              },
            }
          : {}),
        ipAddress: visitor.ip_address || undefined,
        hash: visitor.visitor_hash || undefined,
      },
      totalViews: visitor.total_views || 0,
      referrer: {
        domain: visitor.referrer_domain || undefined,
        fullUrl: visitor.referrer_domain ? `https://${visitor.referrer_domain}` : undefined,
        category: visitor.referrer_channel || 'DIRECT TRAFFIC',
      },
      entryPage: {
        title: visitor.entry_page_title || visitor.entry_page || 'Home',
        url: visitor.entry_page || '/',
        hasQueryString: (visitor.entry_page || '').includes('?'),
        queryString: (visitor.entry_page || '').includes('?') ? (visitor.entry_page || '').split('?')[1] : undefined,
      },
      exitPage: {
        title: visitor.exit_page_title || visitor.exit_page || 'Home',
        url: visitor.exit_page || '/',
      },
    }))
  }, [data])

  // Get settings for visitor info display
  const trackLoggedInEnabled = wp.isTrackLoggedInEnabled()
  const hashEnabled = wp.isHashEnabled()

  const columns: ColumnDef<TopVisitorData>[] = [
    {
      accessorKey: 'visitorInfo',
      header: 'Visitor Information',
      cell: ({ row }) => {
        const visitorInfo = row.getValue('visitorInfo') as TopVisitorData['visitorInfo']

        // Determine what to show for identifier based on settings
        const showUserBadge = trackLoggedInEnabled && visitorInfo.user

        // Format hash display: strip #hash# prefix and show first 6 chars
        const formatHashDisplay = (value: string): string => {
          const cleanHash = value.replace(/^#hash#/i, '')
          return cleanHash.substring(0, 6)
        }

        // Determine identifier display based on settings and available data
        const getIdentifierDisplay = (): string | undefined => {
          if (hashEnabled) {
            // hashEnabled = true → show first 6 chars of hash
            if (visitorInfo.hash) return formatHashDisplay(visitorInfo.hash)
            if (visitorInfo.ipAddress?.startsWith('#hash#')) return formatHashDisplay(visitorInfo.ipAddress)
          }
          // hashEnabled = false → show full IP address
          return visitorInfo.ipAddress
        }
        const identifierDisplay = getIdentifierDisplay()

        return (
          <div className="flex items-center gap-2">
            {/* Country Flag */}
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <button className="cursor-pointer flex items-center">
                    <img
                      src={`${pluginUrl}public/images/flags/${visitorInfo.country.code || '000'}.svg`}
                      alt={visitorInfo.country.name}
                      className="w-5 h-5 object-contain"
                    />
                  </button>
                </TooltipTrigger>
                <TooltipContent>
                  <p>
                    {visitorInfo.country.name}, {visitorInfo.country.region}, {visitorInfo.country.city}
                  </p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>

            {/* OS Icon */}
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <button className="cursor-pointer flex items-center">
                    <img
                      src={`${pluginUrl}public/images/operating-system/${visitorInfo.os.icon}.svg`}
                      alt={visitorInfo.os.name}
                      className="w-4 h-4 object-contain"
                    />
                  </button>
                </TooltipTrigger>
                <TooltipContent>
                  <p>{visitorInfo.os.name}</p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>

            {/* Browser Icon */}
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <button className="cursor-pointer flex items-center">
                    <img
                      src={`${pluginUrl}public/images/browser/${visitorInfo.browser.icon}.svg`}
                      alt={visitorInfo.browser.name}
                      className="w-4 h-4 object-contain"
                    />
                  </button>
                </TooltipTrigger>
                <TooltipContent>
                  <p>
                    {visitorInfo.browser.name} {visitorInfo.browser.version ? `v${visitorInfo.browser.version}` : ''}
                  </p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>

            {/* User Badge (only if trackLoggedInEnabled AND user exists) */}
            {showUserBadge ? (
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Badge variant="secondary" className="text-xs font-normal">
                      {visitorInfo.user!.username} #{visitorInfo.user!.id}
                    </Badge>
                  </TooltipTrigger>
                  <TooltipContent>
                    <p>
                      {visitorInfo.user!.email || ''} {visitorInfo.user!.role ? `(${visitorInfo.user!.role})` : ''}
                    </p>
                  </TooltipContent>
                </Tooltip>
              </TooltipProvider>
            ) : (
              /* IP or Hash (only when user badge is not shown) */
              identifierDisplay && (
                <span className="text-xs text-muted-foreground font-mono">{identifierDisplay}</span>
              )
            )}
          </div>
        )
      },
    },
    {
      accessorKey: 'totalViews',
      header: 'Total Views',
      cell: ({ row }) => {
        const totalViews = row.getValue('totalViews') as number
        const formattedViews = totalViews.toLocaleString()
        return (
          <div className="text-right">
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <span className="cursor-pointer pr-4">{formattedViews}</span>
                </TooltipTrigger>
                <TooltipContent>
                  <p>{formattedViews} Page Views from this visitor in selected period</p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
          </div>
        )
      },
    },
    {
      accessorKey: 'referrer',
      header: 'Referrer',
      cell: ({ row }) => {
        const referrer = row.getValue('referrer') as TopVisitorData['referrer']
        const truncateDomain = (domain: string) => {
          if (domain.length <= 25) return domain
          const parts = domain.split('.')
          const suffix = parts.length > 1 ? `.${parts[parts.length - 1]}` : ''
          const maxLength = 25 - suffix.length - 1
          return `${domain.substring(0, maxLength)}…${suffix}`
        }
        return (
          <div>
            {referrer.domain && (
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <Link
                      to={referrer.fullUrl || `https://${referrer.domain}`}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="hover:underline block"
                    >
                      {truncateDomain(referrer.domain)}
                    </Link>
                  </TooltipTrigger>
                  <TooltipContent>
                    <p>{referrer.fullUrl || `https://${referrer.domain}`}</p>
                  </TooltipContent>
                </Tooltip>
              </TooltipProvider>
            )}
            <Badge variant="outline" className="text-[8px] text-[#636363] uppercase mt-1">
              {referrer.category}
            </Badge>
          </div>
        )
      },
    },
    {
      accessorKey: 'entryPage',
      header: 'Entry Page',
      cell: ({ row }) => {
        const entryPage = row.getValue('entryPage') as TopVisitorData['entryPage']
        const displayTitle = entryPage.title.length > 35 ? `${entryPage.title.substring(0, 35)}…` : entryPage.title
        return (
          <div className="max-w-md">
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <div className="flex items-center gap-1 cursor-pointer">
                    <span className="truncate">{displayTitle}</span>
                    {entryPage.hasQueryString && <Info className="h-3 w-3 text-[#636363] shrink-0" />}
                  </div>
                </TooltipTrigger>
                <TooltipContent>
                  {entryPage.hasQueryString && entryPage.queryString ? (
                    <p>{entryPage.queryString}</p>
                  ) : (
                    <p>{entryPage.url}</p>
                  )}
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
            {entryPage.utmCampaign && (
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <div className="text-[9px] text-[#636363] mt-1 cursor-pointer">{entryPage.utmCampaign}</div>
                  </TooltipTrigger>
                  <TooltipContent>
                    <p>Campaign: {entryPage.utmCampaign}</p>
                  </TooltipContent>
                </Tooltip>
              </TooltipProvider>
            )}
          </div>
        )
      },
    },
    {
      accessorKey: 'exitPage',
      header: 'Exit Page',
      cell: ({ row }) => {
        const exitPage = row.getValue('exitPage') as TopVisitorData['exitPage']
        const displayTitle = exitPage.title.length > 35 ? `${exitPage.title.substring(0, 35)}…` : exitPage.title
        return (
          <div className="max-w-md">
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <span className="cursor-pointer truncate">{displayTitle}</span>
                </TooltipTrigger>
                <TooltipContent>
                  <p>{exitPage.url}</p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
          </div>
        )
      },
    },
  ]

  return (
    <DataTable
      title={__('Top Visitors', 'wp-statistics')}
      columns={columns}
      data={transformedData}
      rowLimit={10}
      showPagination={false}
      showColumnManagement={false}
      fullReportLink={{
        text: __('View All Top Visitors'),
        action: () => {
          router.navigate({
            from: '/visitors-overview',
            to: '/top-visitors',
          })
        },
      }}
    />
  )
}
