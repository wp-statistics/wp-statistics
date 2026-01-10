import { useRouter } from '@tanstack/react-router'
import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { useMemo } from 'react'

import { DataTable } from '@/components/custom/data-table'
import {
  EntryPageCell,
  NumericCell,
  PageCell,
  ReferrerCell,
  VisitorInfoCell,
  type VisitorInfoConfig,
} from '@/components/data-table-columns'
import { COLUMN_SIZES } from '@/lib/column-sizes'
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

  // Config for visitor info display
  const config: VisitorInfoConfig = useMemo(
    () => ({
      pluginUrl,
      trackLoggedInEnabled: wp.isTrackLoggedInEnabled(),
      hashEnabled: wp.isHashEnabled(),
    }),
    [pluginUrl, wp]
  )

  const columns: ColumnDef<TopVisitorData>[] = [
    {
      accessorKey: 'visitorInfo',
      header: 'Visitor Info',
      size: COLUMN_SIZES.visitorInfo,
      cell: ({ row }) => {
        const visitorInfo = row.getValue('visitorInfo') as TopVisitorData['visitorInfo']
        return (
          <VisitorInfoCell
            data={{
              country: {
                code: visitorInfo.country.code,
                name: visitorInfo.country.name,
                region: visitorInfo.country.region,
                city: visitorInfo.country.city,
              },
              os: { icon: visitorInfo.os.icon, name: visitorInfo.os.name },
              browser: {
                icon: visitorInfo.browser.icon,
                name: visitorInfo.browser.name,
                version: visitorInfo.browser.version,
              },
              user: visitorInfo.user
                ? {
                    id: visitorInfo.user.id,
                    username: visitorInfo.user.username,
                    email: visitorInfo.user.email,
                    role: visitorInfo.user.role,
                  }
                : undefined,
              identifier: visitorInfo.hash || visitorInfo.ipAddress,
            }}
            config={config}
          />
        )
      },
    },
    {
      accessorKey: 'totalViews',
      header: 'Total Views',
      size: COLUMN_SIZES.totalViews,
      meta: { align: 'right' },
      cell: ({ row }) => <NumericCell value={row.getValue('totalViews') as number} />,
    },
    {
      accessorKey: 'referrer',
      header: 'Referrer',
      size: COLUMN_SIZES.referrer,
      cell: ({ row }) => {
        const referrer = row.getValue('referrer') as TopVisitorData['referrer']
        return (
          <ReferrerCell
            data={{
              domain: referrer.domain,
              category: referrer.category,
            }}
            maxLength={25}
          />
        )
      },
    },
    {
      accessorKey: 'entryPage',
      header: 'Entry Page',
      size: COLUMN_SIZES.entryPage,
      cell: ({ row }) => {
        const entryPage = row.getValue('entryPage') as TopVisitorData['entryPage']
        return (
          <EntryPageCell
            data={{
              title: entryPage.title,
              url: entryPage.url,
              hasQueryString: entryPage.hasQueryString,
              queryString: entryPage.queryString,
              utmCampaign: entryPage.utmCampaign,
            }}
            maxLength={35}
          />
        )
      },
    },
    {
      accessorKey: 'exitPage',
      header: 'Exit Page',
      size: COLUMN_SIZES.exitPage,
      cell: ({ row }) => {
        const exitPage = row.getValue('exitPage') as TopVisitorData['exitPage']
        return <PageCell data={{ title: exitPage.title, url: exitPage.url }} maxLength={35} />
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
