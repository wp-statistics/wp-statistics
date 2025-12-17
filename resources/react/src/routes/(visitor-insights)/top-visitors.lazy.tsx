import { createLazyFileRoute } from '@tanstack/react-router'
import type { ColumnDef } from '@tanstack/react-table'
import { DataTable } from '@/components/custom/data-table'
import { Badge } from '@/components/ui/badge'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import { Info } from 'lucide-react'
import { WordPress } from '@/lib/wordpress'
import { __ } from '@wordpress/i18n'

export const Route = createLazyFileRoute('/(visitor-insights)/top-visitors')({
  component: RouteComponent,
})

interface TopVisitor {
  id: string
  lastVisit: Date
  country: string
  countryCode: string
  region: string
  city: string
  os: string
  browser: string
  browserVersion: string
  userId?: string
  username?: string
  email?: string
  userRole?: string
  ipAddress?: string
  hash?: string
  referrerDomain?: string
  referrerCategory: string
  entryPage: string
  entryPageTitle: string
  entryPageHasQuery?: boolean
  entryPageQueryString?: string
  utmCampaign?: string
  exitPage: string
  exitPageTitle: string
  totalViews: number
  totalSessions: number
  sessionDuration: number // in seconds
  viewsPerSession: number
  bounceRate: number
  visitorStatus: 'new' | 'returning'
  firstVisit: Date
}

const createColumns = (pluginUrl: string): ColumnDef<TopVisitor>[] => [
  {
    accessorKey: 'lastVisit',
    header: 'Last Visit',
    cell: ({ row }) => {
      const date = row.original.lastVisit
      const formatted = date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
      })
      const time = date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
      })
      return (
        <div className="whitespace-nowrap">
          {formatted}, {time}
        </div>
      )
    },
  },
  {
    accessorKey: 'visitorInfo',
    header: 'Visitor Info',
    cell: ({ row }) => {
      const visitor = row.original
      const locationText = `${visitor.country}, ${visitor.region}, ${visitor.city}`

      return (
        <div className="flex items-center gap-2">
          {/* Country Flag */}
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <button className="flex items-center">
                  <img
                    src={`${pluginUrl}public/images/flags/${visitor.countryCode}.svg`}
                    alt={visitor.country}
                    className="w-5 h-5 object-contain"
                  />
                </button>
              </TooltipTrigger>
              <TooltipContent>
                <p>{locationText}</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>

          {/* OS Icon */}
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <button className="flex items-center">
                  <img
                    src={`${pluginUrl}public/images/operating-system/${visitor.os}.svg`}
                    alt={visitor.os}
                    className="w-4 h-4 object-contain"
                  />
                </button>
              </TooltipTrigger>
              <TooltipContent>
                <p className="capitalize">{visitor.os.replace(/_/g, ' ')}</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>

          {/* Browser Icon */}
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <button className="flex items-center">
                  <img
                    src={`${pluginUrl}public/images/browser/${visitor.browser}.svg`}
                    alt={visitor.browser}
                    className="w-4 h-4 object-contain"
                  />
                </button>
              </TooltipTrigger>
              <TooltipContent>
                <p className="capitalize">
                  {visitor.browser} v{visitor.browserVersion}
                </p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>

          {/* User Badge or IP/Hash */}
          {visitor.userId ? (
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <Badge variant="secondary" className="text-xs font-normal">
                    {visitor.username} #{visitor.userId}
                  </Badge>
                </TooltipTrigger>
                <TooltipContent>
                  <p>{visitor.email}</p>
                  <p className="text-xs text-muted-foreground">{visitor.userRole}</p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
          ) : (
            <span className="text-xs text-muted-foreground font-mono">
              {visitor.hash ? visitor.hash.substring(0, 6) : visitor.ipAddress}
            </span>
          )}
        </div>
      )
    },
  },
  {
    accessorKey: 'referrer',
    header: 'Referrer',
    cell: ({ row }) => {
      const visitor = row.original
      return (
        <div className="flex flex-col items-start gap-1">
          {visitor.referrerDomain && (
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <a
                    href={`https://${visitor.referrerDomain}`}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="hover:underline max-w-[200px] truncate block"
                  >
                    {visitor.referrerDomain.length > 25
                      ? `${visitor.referrerDomain.substring(0, 22)}...${visitor.referrerDomain.split('.').pop()}`
                      : visitor.referrerDomain}
                  </a>
                </TooltipTrigger>
                <TooltipContent>
                  <p>https://{visitor.referrerDomain}</p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
          )}
          <Badge variant="outline" className="text-[8px] text-[#636363] uppercase mt-1">
            {visitor.referrerCategory}
          </Badge>
        </div>
      )
    },
  },
  {
    accessorKey: 'entryPage',
    header: 'Entry Page',
    cell: ({ row }) => {
      const visitor = row.original
      const truncatedTitle =
        visitor.entryPageTitle.length > 35 ? `${visitor.entryPageTitle.substring(0, 35)}...` : visitor.entryPageTitle

      return (
        <div className="max-w-md inline-flex flex-col items-start">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <div className="flex items-center gap-1 cursor-pointer">
                  <span className="truncate">{truncatedTitle}</span>
                  {visitor.entryPageHasQuery && <Info className="h-3 w-3 text-[#636363] shrink-0" />}
                </div>
              </TooltipTrigger>
              <TooltipContent>
                {visitor.entryPageHasQuery && visitor.entryPageQueryString ? (
                  <p>{visitor.entryPageQueryString}</p>
                ) : (
                  <p>{visitor.entryPage}</p>
                )}
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
          {visitor.utmCampaign && (
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <div className="text-[9px] text-[#636363] mt-1 cursor-pointer">{visitor.utmCampaign}</div>
                </TooltipTrigger>
                <TooltipContent>
                  <p>Campaign: {visitor.utmCampaign}</p>
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
      const visitor = row.original
      const truncatedTitle =
        visitor.exitPageTitle.length > 35 ? `${visitor.exitPageTitle.substring(0, 35)}...` : visitor.exitPageTitle

      return (
        <div className="max-w-md inline-flex flex-col items-start">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <span className="cursor-pointer truncate">{truncatedTitle}</span>
              </TooltipTrigger>
              <TooltipContent>
                <p>{visitor.exitPage}</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      )
    },
  },
  {
    accessorKey: 'totalViews',
    header: 'Total Views',
    cell: ({ row }) => {
      const views = row.original.totalViews
      return (
        <div className="text-right pr-4">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <span className="cursor-pointer">{views.toLocaleString()}</span>
              </TooltipTrigger>
              <TooltipContent>
                <p>{views.toLocaleString()} Page Views from this visitor in selected period</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      )
    },
  },
  {
    accessorKey: 'totalSessions',
    header: 'Total Sessions',
    cell: ({ row }) => {
      const sessions = row.original.totalSessions
      return (
        <div className="text-right pr-4">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <span className="cursor-pointer">{sessions.toLocaleString()}</span>
              </TooltipTrigger>
              <TooltipContent>
                <p>
                  {sessions.toLocaleString()} {sessions === 1 ? 'session' : 'sessions'} in selected period
                </p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      )
    },
  },
  {
    accessorKey: 'sessionDuration',
    header: 'Session Duration',
    cell: ({ row }) => {
      const seconds = row.original.sessionDuration
      const hours = Math.floor(seconds / 3600)
      const minutes = Math.floor((seconds % 3600) / 60)
      const secs = seconds % 60
      const formatted = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`

      return (
        <div className="text-right pr-4">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <span className="cursor-pointer">{formatted}</span>
              </TooltipTrigger>
              <TooltipContent>
                <p>Average session duration: {formatted}</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      )
    },
  },
  {
    accessorKey: 'viewsPerSession',
    header: 'Views Per Session',
    enableHiding: true,
    cell: ({ row }) => {
      const value = row.original.viewsPerSession
      return (
        <div className="text-right pr-4">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <span className="cursor-pointer">{value.toFixed(1)}</span>
              </TooltipTrigger>
              <TooltipContent>
                <p>{value.toFixed(1)} average page views per session</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      )
    },
  },
  {
    accessorKey: 'bounceRate',
    header: 'Bounce Rate',
    enableHiding: true,
    cell: ({ row }) => {
      const rate = row.original.bounceRate
      return (
        <div className="text-right pr-4">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <span className="cursor-pointer">{rate}%</span>
              </TooltipTrigger>
              <TooltipContent>
                <p>{rate}% of sessions viewed only one page</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      )
    },
  },
  {
    accessorKey: 'visitorStatus',
    header: 'Status',
    enableHiding: true,
    cell: ({ row }) => {
      const visitor = row.original
      const isNew = visitor.visitorStatus === 'new'
      const firstVisitDate = visitor.firstVisit.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
      })

      return (
        <TooltipProvider>
          <Tooltip>
            <TooltipTrigger asChild>
              <Badge variant={isNew ? 'default' : 'secondary'} className="text-xs font-normal capitalize">
                {visitor.visitorStatus}
              </Badge>
            </TooltipTrigger>
            <TooltipContent>
              <p>{isNew ? `First visit: ${firstVisitDate}` : `Returning visitor since ${firstVisitDate}`}</p>
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>
      )
    },
  },
]

// Generate fake top visitor data (visitors with Total Views > 5)
const generateFakeTopVisitors = (): TopVisitor[] => {
  const countries = [
    { name: 'United States', code: 'us', region: 'California', city: 'San Francisco' },
    { name: 'United Kingdom', code: 'gb', region: 'England', city: 'London' },
    { name: 'Canada', code: 'ca', region: 'Ontario', city: 'Toronto' },
    { name: 'Germany', code: 'de', region: 'Bavaria', city: 'Munich' },
    { name: 'France', code: 'fr', region: 'Île-de-France', city: 'Paris' },
    { name: 'Japan', code: 'jp', region: 'Tokyo', city: 'Tokyo' },
    { name: 'Australia', code: 'au', region: 'New South Wales', city: 'Sydney' },
    { name: 'Brazil', code: 'br', region: 'São Paulo', city: 'São Paulo' },
  ]

  const browsers = [
    { name: 'chrome', version: '120' },
    { name: 'firefox', version: '121' },
    { name: 'safari', version: '17' },
    { name: 'edge', version: '120' },
  ]

  const operatingSystems = ['windows', 'mac_os', 'linux', 'android', 'ios']

  const referrers = [
    { domain: 'google.com', category: 'SEARCH' },
    { domain: 'facebook.com', category: 'SOCIAL' },
    { domain: 'twitter.com', category: 'SOCIAL' },
    { domain: 'linkedin.com', category: 'SOCIAL' },
    { domain: null, category: 'DIRECT TRAFFIC' },
    { domain: 'example.com', category: 'REFERRAL' },
  ]

  const pages = [
    { path: '/', title: 'Home' },
    { path: '/blog', title: 'Blog' },
    { path: '/about', title: 'About Us' },
    { path: '/contact', title: 'Contact' },
    { path: '/products', title: 'Products' },
    { path: '/services', title: 'Our Services' },
    { path: '/blog/how-to-improve-website-performance', title: 'How to Improve Your Website Performance' },
    { path: '/blog/best-practices-for-seo', title: 'Best Practices for SEO in 2025' },
  ]

  const users = [
    { id: '1', username: 'admin', email: 'admin@example.com', role: 'Administrator' },
    { id: '2', username: 'editor', email: 'editor@example.com', role: 'Editor' },
    null,
    null,
    null,
    null,
  ]

  const visitors: TopVisitor[] = []

  for (let i = 0; i < 75; i++) {
    const country = countries[Math.floor(Math.random() * countries.length)]
    const browser = browsers[Math.floor(Math.random() * browsers.length)]
    const os = operatingSystems[Math.floor(Math.random() * operatingSystems.length)]
    const referrer = referrers[Math.floor(Math.random() * referrers.length)]
    const entryPageData = pages[Math.floor(Math.random() * pages.length)]
    const exitPageData = pages[Math.floor(Math.random() * pages.length)]
    const user = users[Math.floor(Math.random() * users.length)]

    // Top visitors have Total Views > 5, so generate between 6 and 100 views
    const totalViews = Math.floor(Math.random() * 95) + 6
    const totalSessions = Math.floor(Math.random() * 20) + 1
    const sessionDuration = Math.floor(Math.random() * 3600) + 60
    const viewsPerSession = parseFloat((totalViews / totalSessions).toFixed(1))
    const singlePageSessions = Math.floor(Math.random() * totalSessions)
    const bounceRate = Math.round((singlePageSessions / totalSessions) * 100)

    const lastVisit = new Date()
    lastVisit.setHours(lastVisit.getHours() - Math.floor(Math.random() * 72))

    const firstVisit = new Date(lastVisit)
    const visitorStatus = Math.random() > 0.2 ? 'returning' : 'new'
    if (visitorStatus === 'returning') {
      firstVisit.setDate(firstVisit.getDate() - Math.floor(Math.random() * 180))
    }

    const hasQueryString = Math.random() > 0.7
    const hasCampaign = Math.random() > 0.8

    visitors.push({
      id: `visitor-${i + 1}`,
      lastVisit,
      country: country.name,
      countryCode: country.code,
      region: country.region,
      city: country.city,
      os,
      browser: browser.name,
      browserVersion: browser.version,
      userId: user?.id,
      username: user?.username,
      email: user?.email,
      userRole: user?.role,
      ipAddress: user ? undefined : `192.168.${Math.floor(Math.random() * 255)}.${Math.floor(Math.random() * 255)}`,
      hash: user ? undefined : `abc${Math.random().toString(36).substring(2, 8)}`,
      referrerDomain: referrer.domain || undefined,
      referrerCategory: referrer.category,
      entryPage: entryPageData.path,
      entryPageTitle: entryPageData.title,
      entryPageHasQuery: hasQueryString,
      entryPageQueryString: hasQueryString ? '?utm_source=google&utm_medium=cpc' : undefined,
      utmCampaign: hasCampaign ? 'Summer Sale 2025' : undefined,
      exitPage: exitPageData.path,
      exitPageTitle: exitPageData.title,
      totalViews,
      totalSessions,
      sessionDuration,
      viewsPerSession,
      bounceRate,
      visitorStatus,
      firstVisit,
    })
  }

  // Sort by totalViews descending (default for Top Visitors)
  return visitors.sort((a, b) => b.totalViews - a.totalViews)
}

function RouteComponent() {
  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()

  const fakeVisitors = generateFakeTopVisitors()

  return (
    <div className="min-w-0">
      <div className="flex items-center justify-between p-4 bg-white border-b border-input">
        <h1 className="text-2xl font-medium text-neutral-700">{__('Top Visitors', 'wp-statistics')}</h1>
      </div>

      <div className="p-4">
        <DataTable
          columns={createColumns(pluginUrl)}
          data={fakeVisitors}
          defaultSort="lastVisit"
          rowLimit={50}
          showColumnManagement={true}
          showPagination={true}
          hiddenColumns={['viewsPerSession', 'bounceRate', 'visitorStatus']}
          emptyStateMessage={__('No visitors found for the selected period', 'wp-statistics')}
        />
      </div>
    </div>
  )
}
