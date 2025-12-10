import { DataTable } from '@/components/custom/data-table'
import { Badge } from '@/components/ui/badge'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import { Link, useRouter } from '@tanstack/react-router'
import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { Info } from 'lucide-react'
import { WordPress } from '@/lib/wordpress'

type TopVisitorData = {
  visitorInfo: {
    country: { code: string; name: string; region: string; city: string }
    os: { icon: string; name: string }
    browser: { icon: string; name: string; version: string }
    user?: { username: string; id: number; email: string; role: string }
    identifier: string
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

const topVisitorsData: TopVisitorData[] = [
  {
    visitorInfo: {
      country: { code: 'us', name: 'United States', region: 'California', city: 'San Francisco' },
      os: { icon: 'windows', name: 'Windows 11' },
      browser: { icon: 'chrome', name: 'Google Chrome', version: '120' },
      user: { username: 'john_doe', id: 123, email: 'john@example.com', role: 'Administrator' },
      identifier: '192.168.1.1',
    },
    totalViews: 12567,
    referrer: {
      domain: 'google.com',
      fullUrl: 'https://google.com/search?q=wordpress',
      category: 'ORGANIC SEARCH',
    },
    entryPage: {
      title: 'Home',
      url: '/',
      hasQueryString: false,
    },
    exitPage: {
      title: 'Contact Support',
      url: '/support/contact',
    },
  },
  {
    visitorInfo: {
      country: { code: 'gb', name: 'United Kingdom', region: 'England', city: 'London' },
      os: { icon: 'mac_os', name: 'macOS Sonoma' },
      browser: { icon: 'safari', name: 'Safari', version: '17' },
      identifier: 'a3f5c9',
    },
    totalViews: 1234,
    referrer: {
      domain: 'twitter.com',
      fullUrl: 'https://twitter.com/some-user/status/123456',
      category: 'SOCIAL MEDIA',
    },
    entryPage: {
      title: 'Special Offer Landing Page',
      url: '/special-offer',
      hasQueryString: true,
      queryString: '?utm_source=twitter&utm_medium=social&utm_campaign=spring-sale',
      utmCampaign: 'Spring Sale 2025',
    },
    exitPage: {
      title: 'Pricing Plans',
      url: '/pricing',
    },
  },
  {
    visitorInfo: {
      country: { code: 'de', name: 'Germany', region: 'Bavaria', city: 'Munich' },
      os: { icon: 'linux', name: 'Ubuntu 22.04' },
      browser: { icon: 'firefox', name: 'Firefox', version: '121' },
      identifier: '10.0.0.45',
    },
    totalViews: 456,
    referrer: {
      domain: 'github.com',
      fullUrl: 'https://github.com/wp-statistics/wp-statistics',
      category: 'REFERRAL TRAFFIC',
    },
    entryPage: {
      title: 'Documentation Overview',
      url: '/documentation',
      hasQueryString: false,
    },
    exitPage: {
      title: 'API Reference Documentation',
      url: '/documentation/api-reference',
    },
  },
  {
    visitorInfo: {
      country: { code: 'fr', name: 'France', region: 'Île-de-France', city: 'Paris' },
      os: { icon: 'windows', name: 'Windows 10' },
      browser: { icon: 'edge', name: 'Edge', version: '120' },
      user: { username: 'marie_claire', id: 456, email: 'marie@example.fr', role: 'Editor' },
      identifier: '172.16.0.1',
    },
    totalViews: 342,
    referrer: {
      category: 'DIRECT TRAFFIC',
    },
    entryPage: {
      title: 'Blog Homepage',
      url: '/blog',
      hasQueryString: false,
    },
    exitPage: {
      title: 'WordPress Performance Optimization Tips and Best Practices Guide',
      url: '/blog/wordpress-performance-tips',
    },
  },
  {
    visitorInfo: {
      country: { code: 'ca', name: 'Canada', region: 'Ontario', city: 'Toronto' },
      os: { icon: 'ios', name: 'iOS 17' },
      browser: { icon: 'safari', name: 'Safari', version: '17' },
      identifier: 'b7e2d1',
    },
    totalViews: 147,
    referrer: {
      domain: 'bing.com',
      fullUrl: 'https://bing.com/search?q=wp+statistics+pricing',
      category: 'ORGANIC SEARCH',
    },
    entryPage: {
      title: 'Pricing Plans',
      url: '/pricing',
      hasQueryString: true,
      queryString: '?ref=email&discount=SAVE20',
      utmCampaign: 'Email Newsletter Discount',
    },
    exitPage: {
      title: 'Pricing Plans',
      url: '/pricing',
    },
  },
  {
    visitorInfo: {
      country: { code: 'au', name: 'Australia', region: 'New South Wales', city: 'Sydney' },
      os: { icon: 'windows', name: 'Windows 11' },
      browser: { icon: 'chrome', name: 'Google Chrome', version: '120' },
      identifier: '203.0.113.5',
    },
    totalViews: 89,
    referrer: {
      domain: 'linkedin.com',
      fullUrl: 'https://linkedin.com/in/some-profile',
      category: 'SOCIAL MEDIA',
    },
    entryPage: {
      title: 'Home',
      url: '/',
      hasQueryString: false,
    },
    exitPage: {
      title: 'SEO Best Practices for WordPress',
      url: '/blog/seo-best-practices',
    },
  },
  {
    visitorInfo: {
      country: { code: 'in', name: 'India', region: 'Maharashtra', city: 'Mumbai' },
      os: { icon: 'android', name: 'Android 14' },
      browser: { icon: 'chrome', name: 'Google Chrome', version: '120' },
      user: { username: 'admin', id: 1, email: 'admin@site.com', role: 'Administrator' },
      identifier: '198.51.100.10',
    },
    totalViews: 78,
    referrer: {
      domain: 'wordpress.org',
      fullUrl: 'https://wordpress.org/plugins/wp-statistics/',
      category: 'REFERRAL TRAFFIC',
    },
    entryPage: {
      title: 'Support Center',
      url: '/support',
      hasQueryString: false,
    },
    exitPage: {
      title: 'Contact Support',
      url: '/support/contact',
    },
  },
  {
    visitorInfo: {
      country: { code: 'jp', name: 'Japan', region: 'Tokyo', city: 'Tokyo' },
      os: { icon: 'mac_os', name: 'macOS Ventura' },
      browser: { icon: 'firefox', name: 'Firefox', version: '121' },
      identifier: 'f9a8c4',
    },
    totalViews: 23,
    referrer: {
      domain: 'duckduckgo.com',
      fullUrl: 'https://duckduckgo.com/?q=wordpress+analytics',
      category: 'ORGANIC SEARCH',
    },
    entryPage: {
      title: 'Features Comparison',
      url: '/features/compare',
      hasQueryString: true,
      queryString: '?plan=pro&billing=annual',
    },
    exitPage: {
      title: 'Features Overview',
      url: '/features',
    },
  },
  {
    visitorInfo: {
      country: { code: 'br', name: 'Brazil', region: 'São Paulo', city: 'São Paulo' },
      os: { icon: 'android', name: 'Android 13' },
      browser: { icon: 'chrome', name: 'Google Chrome', version: '119' },
      identifier: '192.0.2.15',
    },
    totalViews: 12,
    referrer: {
      domain: 'facebook.com',
      fullUrl: 'https://facebook.com/groups/wordpress',
      category: 'SOCIAL MEDIA',
    },
    entryPage: {
      title: 'Security Best Practices',
      url: '/security',
      hasQueryString: false,
    },
    exitPage: {
      title: 'Complete WordPress Security Guide',
      url: '/blog/wordpress-security-guide',
    },
  },
  {
    visitorInfo: {
      country: { code: 'kr', name: 'South Korea', region: 'Seoul', city: 'Seoul' },
      os: { icon: 'windows', name: 'Windows 11' },
      browser: { icon: 'edge', name: 'Edge', version: '120' },
      user: { username: 'kim_subscriber', id: 789, email: 'kim@example.kr', role: 'Subscriber' },
      identifier: '203.0.113.20',
    },
    totalViews: 5,
    referrer: {
      domain: 'google.co.kr',
      fullUrl: 'https://google.co.kr/search?q=wp+statistics+download',
      category: 'ORGANIC SEARCH',
    },
    entryPage: {
      title: 'Download Page',
      url: '/download',
      hasQueryString: true,
      queryString: '?version=14.0&lang=ko',
    },
    exitPage: {
      title: 'Download WP Statistics',
      url: '/download',
    },
  },
]

export const OverviewTopVisitors = () => {
  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()
  const router = useRouter()

  const columns: ColumnDef<TopVisitorData>[] = [
    {
      accessorKey: 'visitorInfo',
      header: 'Visitor Information',
      cell: ({ row }) => {
        const visitorInfo = row.getValue('visitorInfo') as TopVisitorData['visitorInfo']
        return (
          <div className="flex items-center gap-1">
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <button className="cursor-pointer flex items-center">
                    <img
                      src={`${pluginUrl}public/images/flags/${visitorInfo.country.code}.svg`}
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
                    {visitorInfo.browser.name} v{visitorInfo.browser.version}
                  </p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>

            {visitorInfo.user ? (
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <span className="cursor-pointer">
                      {visitorInfo.user.username} #{visitorInfo.user.id}
                    </span>
                  </TooltipTrigger>
                  <TooltipContent>
                    <p>
                      {visitorInfo.user.email} ({visitorInfo.user.role})
                    </p>
                  </TooltipContent>
                </Tooltip>
              </TooltipProvider>
            ) : (
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger asChild>
                    <span className="cursor-pointer">{visitorInfo.identifier}</span>
                  </TooltipTrigger>
                  <TooltipContent>
                    <p>Single Visitor Report</p>
                  </TooltipContent>
                </Tooltip>
              </TooltipProvider>
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
      data={topVisitorsData}
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
