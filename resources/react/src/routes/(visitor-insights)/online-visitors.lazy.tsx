import { createLazyFileRoute } from '@tanstack/react-router'
import type { ColumnDef } from '@tanstack/react-table'
import { DataTable } from '@/components/custom/data-table'
import { DataTableColumnHeaderSortable } from '@/components/custom/data-table-column-header-sortable'
import { Badge } from '@/components/ui/badge'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import { Info } from 'lucide-react'
import { WordPress } from '@/lib/wordpress'
import { __ } from '@wordpress/i18n'

export const Route = createLazyFileRoute('/(visitor-insights)/online-visitors')({
  component: RouteComponent,
})

interface OnlineVisitor {
  id: string
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
  onlineFor: number // in seconds
  page: string
  pageTitle: string
  totalViews: number
  entryPage: string
  entryPageTitle: string
  entryPageHasQuery?: boolean
  entryPageQueryString?: string
  referrerDomain?: string
  referrerCategory: string
  lastVisit: Date
}

const createColumns = (pluginUrl: string): ColumnDef<OnlineVisitor>[] => [
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
                    src={`https://flagcdn.com/w20/${visitor.countryCode}.png`}
                    alt={visitor.country}
                    className="w-5 h-4 object-cover rounded-sm"
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
    accessorKey: 'onlineFor',
    header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Online For" />,
    cell: ({ row }) => {
      const seconds = row.original.onlineFor
      const hours = Math.floor(seconds / 3600)
      const minutes = Math.floor((seconds % 3600) / 60)
      const secs = seconds % 60
      const formatted = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`

      return <span className="font-mono">{formatted}</span>
    },
  },
  {
    accessorKey: 'page',
    header: 'Page',
    cell: ({ row }) => {
      const visitor = row.original
      const truncatedTitle =
        visitor.pageTitle.length > 35 ? `${visitor.pageTitle.substring(0, 35)}...` : visitor.pageTitle

      return (
        <div className="max-w-md">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <span className="cursor-pointer truncate">{truncatedTitle}</span>
              </TooltipTrigger>
              <TooltipContent>
                <p>{visitor.page}</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      )
    },
  },
  {
    accessorKey: 'totalViews',
    header: ({ column }) => (
      <DataTableColumnHeaderSortable column={column} title="Total Views" className="text-right" />
    ),
    cell: ({ row }) => {
      const views = row.original.totalViews
      return (
        <div className="text-right">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <span className="cursor-pointer pr-4">{views.toLocaleString()}</span>
              </TooltipTrigger>
              <TooltipContent>
                <p>{views.toLocaleString()} Page Views in current session</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
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
        <div className="max-w-md">
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
    accessorKey: 'lastVisit',
    header: ({ column }) => <DataTableColumnHeaderSortable column={column} title="Last Visit" />,
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
]

// Generate fake online visitor data
const generateFakeOnlineVisitors = (): OnlineVisitor[] => {
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
    { path: '/dashboard', title: 'User Dashboard' },
    { path: '/settings', title: 'Account Settings' },
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

  const onlineVisitors: OnlineVisitor[] = []

  // Generate 15-25 online visitors (realistic for "last 5 minutes" filter)
  const visitorCount = Math.floor(Math.random() * 11) + 15

  for (let i = 0; i < visitorCount; i++) {
    const country = countries[Math.floor(Math.random() * countries.length)]
    const browser = browsers[Math.floor(Math.random() * browsers.length)]
    const os = operatingSystems[Math.floor(Math.random() * operatingSystems.length)]
    const referrer = referrers[Math.floor(Math.random() * referrers.length)]
    const entryPageData = pages[Math.floor(Math.random() * pages.length)]
    const currentPageData = pages[Math.floor(Math.random() * pages.length)]
    const user = users[Math.floor(Math.random() * users.length)]

    // Online for: between 1 second and 30 minutes (realistic for online visitors)
    const onlineFor = Math.floor(Math.random() * 1800) + 1
    const totalViews = Math.floor(Math.random() * 15) + 1

    // Last visit within last 5 minutes
    const lastVisit = new Date()
    lastVisit.setSeconds(lastVisit.getSeconds() - Math.floor(Math.random() * 300))

    const hasQueryString = Math.random() > 0.7

    onlineVisitors.push({
      id: `visitor-${i + 1}`,
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
      onlineFor,
      page: currentPageData.path,
      pageTitle: currentPageData.title,
      totalViews,
      entryPage: entryPageData.path,
      entryPageTitle: entryPageData.title,
      entryPageHasQuery: hasQueryString,
      entryPageQueryString: hasQueryString ? '?utm_source=google&utm_medium=cpc' : undefined,
      referrerDomain: referrer.domain || undefined,
      referrerCategory: referrer.category,
      lastVisit,
    })
  }

  return onlineVisitors.sort((a, b) => b.lastVisit.getTime() - a.lastVisit.getTime())
}

function RouteComponent() {
  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()

  const fakeVisitors = generateFakeOnlineVisitors()

  return (
    <div className="min-w-0 grid gap-6">
      <h1 className="text-2xl font-medium text-neutral-700">{__('Online Visitors', 'wp-statistics')}</h1>

      <DataTable
        columns={createColumns(pluginUrl)}
        data={fakeVisitors}
        defaultSort="lastVisit"
        rowLimit={50}
        showColumnManagement={true}
        showPagination={true}
        emptyStateMessage={__('No visitors are currently online', 'wp-statistics')}
      />
    </div>
  )
}
