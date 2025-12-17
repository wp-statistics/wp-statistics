import { createLazyFileRoute, Link } from '@tanstack/react-router'
import { DataTable } from '@components/custom/data-table'
import { DataTableColumnHeaderSortable } from '@/components/custom/data-table-column-header-sortable'
import type { ColumnDef } from '@tanstack/react-table'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@components/ui/tooltip'
import { Info } from 'lucide-react'
import { Badge } from '@components/ui/badge'
import { WordPress } from '@/lib/wordpress'
import { __ } from '@wordpress/i18n'

export const Route = createLazyFileRoute('/(visitor-insights)/views')({
  component: RouteComponent,
})

type ViewData = {
  lastVisit: string
  visitorInfo: {
    country: { code: string; name: string; region: string; city: string }
    os: { icon: string; name: string }
    browser: { icon: string; name: string; version: string }
    user?: { username: string; id: number; email: string; role: string }
    identifier: string // IP or hash
  }
  page: {
    title: string
    url: string
  }
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
  totalViews: number
}

const createColumns = (pluginUrl: string): ColumnDef<ViewData>[] => [
  {
    accessorKey: 'lastVisit',
    header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Last Visit" />,
    cell: ({ row }) => {
      const date = new Date(row.getValue('lastVisit'))
      const formattedDate = date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
      })
      const formattedTime = date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
      })
      return (
        <div className="whitespace-nowrap">
          {formattedDate}, {formattedTime}
        </div>
      )
    },
  },
  {
    accessorKey: 'visitorInfo',
    header: 'Visitor Information',
    cell: ({ row }) => {
      const visitorInfo = row.getValue('visitorInfo') as ViewData['visitorInfo']
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
    accessorKey: 'page',
    header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Page" />,
    cell: ({ row }) => {
      const page = row.getValue('page') as ViewData['page']
      const displayTitle = page.title.length > 35 ? `${page.title.substring(0, 35)}…` : page.title
      return (
        <TooltipProvider>
          <Tooltip>
            <TooltipTrigger asChild>
              <div className="cursor-pointer max-w-md inline-flex">{displayTitle}</div>
            </TooltipTrigger>
            <TooltipContent>
              <p>{page.url}</p>
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>
      )
    },
  },
  {
    accessorKey: 'referrer',
    header: 'Referrer',
    cell: ({ row }) => {
      const referrer = row.getValue('referrer') as ViewData['referrer']
      const truncateDomain = (domain: string) => {
        if (domain.length <= 25) return domain
        // Preserve the suffix (e.g., ".com") - extract last part after the last dot
        const parts = domain.split('.')
        const suffix = parts.length > 1 ? `.${parts[parts.length - 1]}` : ''
        const maxLength = 25 - suffix.length - 1 // -1 for the ellipsis
        return `${domain.substring(0, maxLength)}…${suffix}`
      }
      return (
        <div className="inline-flex flex-col items-start">
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
      const entryPage = row.getValue('entryPage') as ViewData['entryPage']
      const displayTitle = entryPage.title.length > 35 ? `${entryPage.title.substring(0, 35)}…` : entryPage.title
      return (
        <div className="max-w-md inline-flex flex-col items-start">
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
    accessorKey: 'totalViews',
    header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Total Views" />,
    cell: ({ row }) => {
      const totalViews = row.getValue('totalViews') as number
      const formattedViews = totalViews.toLocaleString()
      return (
        <div className="text-right pr-4">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <span className="cursor-pointer">{formattedViews}</span>
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
]

const fakeData: ViewData[] = [
  {
    lastVisit: '2025-01-27T14:23:15',
    visitorInfo: {
      country: { code: 'us', name: 'United States', region: 'California', city: 'San Francisco' },
      os: { icon: 'windows', name: 'Windows 11' },
      browser: { icon: 'chrome', name: 'Google Chrome', version: '120' },
      user: { username: 'john_doe', id: 123, email: 'john@example.com', role: 'Administrator' },
      identifier: '192.168.1.1',
    },
    page: {
      title: 'Getting Started with WordPress',
      url: '/blog/getting-started-with-wordpress',
    },
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
    totalViews: 1234,
  },
  {
    lastVisit: '2025-01-27T14:20:42',
    visitorInfo: {
      country: { code: 'gb', name: 'United Kingdom', region: 'England', city: 'London' },
      os: { icon: 'mac_os', name: 'macOS Sonoma' },
      browser: { icon: 'safari', name: 'Safari', version: '17' },
      identifier: 'a3f5c9',
    },
    page: {
      title: 'WP Statistics Pro - Premium Analytics',
      url: '/products/wp-statistics-pro',
    },
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
    totalViews: 23,
  },
  {
    lastVisit: '2025-01-27T14:18:30',
    visitorInfo: {
      country: { code: 'de', name: 'Germany', region: 'Bavaria', city: 'Munich' },
      os: { icon: 'linux', name: 'Ubuntu 22.04' },
      browser: { icon: 'firefox', name: 'Firefox', version: '121' },
      identifier: '10.0.0.45',
    },
    page: {
      title: 'API Reference Documentation',
      url: '/documentation/api-reference',
    },
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
    totalViews: 147,
  },
  {
    lastVisit: '2025-01-27T14:15:18',
    visitorInfo: {
      country: { code: 'fr', name: 'France', region: 'Île-de-France', city: 'Paris' },
      os: { icon: 'windows', name: 'Windows 10' },
      browser: { icon: 'edge', name: 'Edge', version: '120' },
      user: { username: 'marie_claire', id: 456, email: 'marie@example.fr', role: 'Editor' },
      identifier: '172.16.0.1',
    },
    page: {
      title: 'WordPress Performance Optimization Tips and Best Practices Guide',
      url: '/blog/wordpress-performance-tips',
    },
    referrer: {
      category: 'DIRECT TRAFFIC',
    },
    entryPage: {
      title: 'Blog Homepage',
      url: '/blog',
      hasQueryString: false,
    },
    totalViews: 5,
  },
  {
    lastVisit: '2025-01-27T14:12:05',
    visitorInfo: {
      country: { code: 'ca', name: 'Canada', region: 'Ontario', city: 'Toronto' },
      os: { icon: 'ios', name: 'iOS 17' },
      browser: { icon: 'safari', name: 'Safari', version: '17' },
      identifier: 'b7e2d1',
    },
    page: {
      title: 'Pricing Plans',
      url: '/pricing',
    },
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
    totalViews: 89,
  },
  {
    lastVisit: '2025-01-27T14:08:52',
    visitorInfo: {
      country: { code: 'au', name: 'Australia', region: 'New South Wales', city: 'Sydney' },
      os: { icon: 'windows', name: 'Windows 11' },
      browser: { icon: 'chrome', name: 'Google Chrome', version: '120' },
      identifier: '203.0.113.5',
    },
    page: {
      title: 'SEO Best Practices for WordPress',
      url: '/blog/seo-best-practices',
    },
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
    totalViews: 456,
  },
  {
    lastVisit: '2025-01-27T14:05:33',
    visitorInfo: {
      country: { code: 'in', name: 'India', region: 'Maharashtra', city: 'Mumbai' },
      os: { icon: 'android', name: 'Android 14' },
      browser: { icon: 'chrome', name: 'Google Chrome', version: '120' },
      user: { username: 'admin', id: 1, email: 'admin@site.com', role: 'Administrator' },
      identifier: '198.51.100.10',
    },
    page: {
      title: 'Contact Support',
      url: '/support/contact',
    },
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
    totalViews: 12567,
  },
  {
    lastVisit: '2025-01-27T14:02:19',
    visitorInfo: {
      country: { code: 'jp', name: 'Japan', region: 'Tokyo', city: 'Tokyo' },
      os: { icon: 'mac_os', name: 'macOS Ventura' },
      browser: { icon: 'firefox', name: 'Firefox', version: '121' },
      identifier: 'f9a8c4',
    },
    page: {
      title: 'Features Overview',
      url: '/features',
    },
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
    totalViews: 342,
  },
  {
    lastVisit: '2025-01-27T13:58:47',
    visitorInfo: {
      country: { code: 'br', name: 'Brazil', region: 'São Paulo', city: 'São Paulo' },
      os: { icon: 'android', name: 'Android 13' },
      browser: { icon: 'chrome', name: 'Google Chrome', version: '119' },
      identifier: '192.0.2.15',
    },
    page: {
      title: 'Complete WordPress Security Guide',
      url: '/blog/wordpress-security-guide',
    },
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
    totalViews: 78,
  },
  {
    lastVisit: '2025-01-27T13:55:12',
    visitorInfo: {
      country: { code: 'kr', name: 'South Korea', region: 'Seoul', city: 'Seoul' },
      os: { icon: 'windows', name: 'Windows 11' },
      browser: { icon: 'edge', name: 'Edge', version: '120' },
      user: { username: 'kim_subscriber', id: 789, email: 'kim@example.kr', role: 'Subscriber' },
      identifier: '203.0.113.20',
    },
    page: {
      title: 'Download WP Statistics',
      url: '/download',
    },
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
    totalViews: 2,
  },
]

function RouteComponent() {
  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()
  const columns = createColumns(pluginUrl)

  return (
    <div className="min-w-0">
      <div className="flex items-center justify-between p-4 bg-white border-b border-input">
        <h1 className="text-2xl font-medium text-neutral-700">{__('Views', 'wp-statistics')}</h1>
      </div>

      <div className="p-4">
        <DataTable
          columns={columns}
          data={fakeData}
          defaultSort="lastVisit"
          rowLimit={50}
          showColumnManagement={true}
          showPagination={true}
          emptyStateMessage={__('No views found for the selected period', 'wp-statistics')}
        />
      </div>
    </div>
  )
}
