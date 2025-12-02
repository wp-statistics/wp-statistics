import { createLazyFileRoute } from '@tanstack/react-router'
import type { ColumnDef } from '@tanstack/react-table'
import * as React from 'react'
import { DataTable } from '@/components/custom/data-table'
import { DataTableColumnHeaderSortable } from '@/components/custom/data-table-column-header-sortable'
import { LineChart } from '@/components/custom/line-chart'
import { Badge } from '@/components/ui/badge'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import { Info } from 'lucide-react'
import { WordPress } from '@/lib/wordpress'
import { __ } from '@wordpress/i18n'

export const Route = createLazyFileRoute('/(visitor-insights)/logged-in-users')({
  component: RouteComponent,
})

interface LoggedInUser {
  id: string
  lastVisit: Date
  country: string
  countryCode: string
  region: string
  city: string
  os: string
  browser: string
  browserVersion: string
  userId: string
  username: string
  email: string
  userRole: string
  referrerDomain?: string
  referrerCategory: string
  entryPage: string
  entryPageTitle: string
  entryPageHasQuery?: boolean
  entryPageQueryString?: string
  page: string
  pageTitle: string
  totalViews: number
}

interface TrafficTrendItem {
  date: string
  userVisitors: number
  userVisitorsPrevious: number
  anonymousVisitors: number
  anonymousVisitorsPrevious: number
  [key: string]: string | number
}

const createColumns = (pluginUrl: string): ColumnDef<LoggedInUser>[] => [
  {
    accessorKey: 'visitorInfo',
    header: 'Visitor Info',
    cell: ({ row }) => {
      const user = row.original
      const locationText = `${user.country}, ${user.region}, ${user.city}`

      return (
        <div className="flex items-center gap-2">
          {/* Country Flag */}
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <button className="flex items-center">
                  <img
                    src={`https://flagcdn.com/w20/${user.countryCode}.png`}
                    alt={user.country}
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
                    src={`${pluginUrl}public/images/operating-system/${user.os}.svg`}
                    alt={user.os}
                    className="w-4 h-4 object-contain"
                  />
                </button>
              </TooltipTrigger>
              <TooltipContent>
                <p className="capitalize">{user.os.replace(/_/g, ' ')}</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>

          {/* Browser Icon */}
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <button className="flex items-center">
                  <img
                    src={`${pluginUrl}public/images/browser/${user.browser}.svg`}
                    alt={user.browser}
                    className="w-4 h-4 object-contain"
                  />
                </button>
              </TooltipTrigger>
              <TooltipContent>
                <p className="capitalize">
                  {user.browser} v{user.browserVersion}
                </p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>

          {/* User Badge */}
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <Badge variant="secondary" className="text-xs font-normal">
                  {user.username} #{user.userId}
                </Badge>
              </TooltipTrigger>
              <TooltipContent>
                <p>{user.email}</p>
                <p className="text-xs text-muted-foreground">{user.userRole}</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
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
  {
    accessorKey: 'page',
    header: 'Page',
    cell: ({ row }) => {
      const user = row.original
      const truncatedTitle = user.pageTitle.length > 35 ? `${user.pageTitle.substring(0, 35)}...` : user.pageTitle

      return (
        <div className="max-w-md">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <span className="cursor-pointer truncate">{truncatedTitle}</span>
              </TooltipTrigger>
              <TooltipContent>
                <p>{user.page}</p>
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
      const user = row.original
      return (
        <div className="flex flex-col items-start gap-1">
          {user.referrerDomain && (
            <TooltipProvider>
              <Tooltip>
                <TooltipTrigger asChild>
                  <a
                    href={`https://${user.referrerDomain}`}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="hover:underline max-w-[200px] truncate block"
                  >
                    {user.referrerDomain.length > 25
                      ? `${user.referrerDomain.substring(0, 22)}...${user.referrerDomain.split('.').pop()}`
                      : user.referrerDomain}
                  </a>
                </TooltipTrigger>
                <TooltipContent>
                  <p>https://{user.referrerDomain}</p>
                </TooltipContent>
              </Tooltip>
            </TooltipProvider>
          )}
          <Badge variant="outline" className="text-[8px] text-[#636363] uppercase mt-1">
            {user.referrerCategory}
          </Badge>
        </div>
      )
    },
  },
  {
    accessorKey: 'entryPage',
    header: () => 'Entry Page',
    cell: ({ row }) => {
      const user = row.original
      const truncatedTitle =
        user.entryPageTitle.length > 35 ? `${user.entryPageTitle.substring(0, 35)}...` : user.entryPageTitle

      return (
        <div className="max-w-md">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger asChild>
                <div className="flex items-center gap-1 cursor-pointer">
                  <span className="truncate">{truncatedTitle}</span>
                  {user.entryPageHasQuery && <Info className="h-3 w-3 text-[#636363] shrink-0" />}
                </div>
              </TooltipTrigger>
              <TooltipContent>
                {user.entryPageHasQuery && user.entryPageQueryString ? (
                  <p>{user.entryPageQueryString}</p>
                ) : (
                  <p>{user.entryPage}</p>
                )}
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
                <p>{views.toLocaleString()} Page Views from this user in selected period</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
        </div>
      )
    },
  },
]

// Generate fake logged-in user data
const generateFakeUsers = (): LoggedInUser[] => {
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
    { id: '3', username: 'author', email: 'author@example.com', role: 'Author' },
    { id: '4', username: 'contributor', email: 'contributor@example.com', role: 'Contributor' },
    { id: '5', username: 'subscriber', email: 'subscriber@example.com', role: 'Subscriber' },
    { id: '6', username: 'johndoe', email: 'john.doe@example.com', role: 'Subscriber' },
    { id: '7', username: 'janedoe', email: 'jane.doe@example.com', role: 'Editor' },
    { id: '8', username: 'mikebrown', email: 'mike.brown@example.com', role: 'Author' },
  ]

  const loggedInUsers: LoggedInUser[] = []

  for (let i = 0; i < 40; i++) {
    const country = countries[Math.floor(Math.random() * countries.length)]
    const browser = browsers[Math.floor(Math.random() * browsers.length)]
    const os = operatingSystems[Math.floor(Math.random() * operatingSystems.length)]
    const referrer = referrers[Math.floor(Math.random() * referrers.length)]
    const entryPageData = pages[Math.floor(Math.random() * pages.length)]
    const pageData = pages[Math.floor(Math.random() * pages.length)]
    const user = users[Math.floor(Math.random() * users.length)]

    const totalViews = Math.floor(Math.random() * 50) + 1

    const lastVisit = new Date()
    lastVisit.setHours(lastVisit.getHours() - Math.floor(Math.random() * 72))

    const hasQueryString = Math.random() > 0.7

    loggedInUsers.push({
      id: `user-${i + 1}`,
      lastVisit,
      country: country.name,
      countryCode: country.code,
      region: country.region,
      city: country.city,
      os,
      browser: browser.name,
      browserVersion: browser.version,
      userId: user.id,
      username: user.username,
      email: user.email,
      userRole: user.role,
      referrerDomain: referrer.domain || undefined,
      referrerCategory: referrer.category,
      entryPage: entryPageData.path,
      entryPageTitle: entryPageData.title,
      entryPageHasQuery: hasQueryString,
      entryPageQueryString: hasQueryString ? '?utm_source=google&utm_medium=cpc' : undefined,
      page: pageData.path,
      pageTitle: pageData.title,
      totalViews,
    })
  }

  return loggedInUsers.sort((a, b) => b.lastVisit.getTime() - a.lastVisit.getTime())
}

// Generate fake traffic trends data based on timeframe
const generateTrafficTrendsData = (timeframe: 'daily' | 'weekly' | 'monthly'): TrafficTrendItem[] => {
  const data: TrafficTrendItem[] = []
  const now = new Date()

  if (timeframe === 'monthly') {
    // Generate 12 months of data
    for (let i = 11; i >= 0; i--) {
      const date = new Date(now.getFullYear(), now.getMonth() - i, 1)

      const userVisitors = Math.floor(Math.random() * 1500) + 500
      const userVisitorsPrevious = Math.floor(Math.random() * 1500) + 500
      const anonymousVisitors = Math.floor(Math.random() * 5000) + 2000
      const anonymousVisitorsPrevious = Math.floor(Math.random() * 5000) + 2000

      data.push({
        date: date.toISOString().split('T')[0],
        userVisitors,
        userVisitorsPrevious,
        anonymousVisitors,
        anonymousVisitorsPrevious,
      })
    }
  } else if (timeframe === 'weekly') {
    // Generate 8 weeks of data
    for (let i = 7; i >= 0; i--) {
      const date = new Date(now)
      date.setDate(date.getDate() - i * 7)

      const userVisitors = Math.floor(Math.random() * 500) + 200
      const userVisitorsPrevious = Math.floor(Math.random() * 500) + 200
      const anonymousVisitors = Math.floor(Math.random() * 2000) + 800
      const anonymousVisitorsPrevious = Math.floor(Math.random() * 2000) + 800

      data.push({
        date: date.toISOString().split('T')[0],
        userVisitors,
        userVisitorsPrevious,
        anonymousVisitors,
        anonymousVisitorsPrevious,
      })
    }
  } else {
    // Daily: Generate 30 days of data
    for (let i = 29; i >= 0; i--) {
      const date = new Date(now)
      date.setDate(date.getDate() - i)

      const userVisitors = Math.floor(Math.random() * 150) + 50
      const userVisitorsPrevious = Math.floor(Math.random() * 150) + 50
      const anonymousVisitors = Math.floor(Math.random() * 500) + 200
      const anonymousVisitorsPrevious = Math.floor(Math.random() * 500) + 200

      data.push({
        date: date.toISOString().split('T')[0],
        userVisitors,
        userVisitorsPrevious,
        anonymousVisitors,
        anonymousVisitorsPrevious,
      })
    }
  }

  return data
}

function RouteComponent() {
  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()

  const [timeframe, setTimeframe] = React.useState<'daily' | 'weekly' | 'monthly'>('daily')

  const fakeUsers = generateFakeUsers()
  const trafficTrendsData = React.useMemo(() => generateTrafficTrendsData(timeframe), [timeframe])

  // Calculate totals for current and previous period
  const totalUserVisitors = trafficTrendsData.reduce((sum, item) => sum + item.userVisitors, 0)
  const totalUserVisitorsPrevious = trafficTrendsData.reduce((sum, item) => sum + item.userVisitorsPrevious, 0)
  const totalAnonymousVisitors = trafficTrendsData.reduce((sum, item) => sum + item.anonymousVisitors, 0)
  const totalAnonymousVisitorsPrevious = trafficTrendsData.reduce(
    (sum, item) => sum + item.anonymousVisitorsPrevious,
    0
  )

  const trafficTrendsMetrics = [
    {
      key: 'userVisitors',
      label: 'User Visitors',
      color: 'var(--chart-1)',
      enabled: true,
      value: totalUserVisitors >= 1000 ? `${(totalUserVisitors / 1000).toFixed(1)}k` : totalUserVisitors.toString(),
      previousValue:
        totalUserVisitorsPrevious >= 1000
          ? `${(totalUserVisitorsPrevious / 1000).toFixed(1)}k`
          : totalUserVisitorsPrevious.toString(),
    },
    {
      key: 'anonymousVisitors',
      label: 'Anonymous Visitors',
      color: 'var(--chart-4)',
      enabled: true,
      value:
        totalAnonymousVisitors >= 1000
          ? `${(totalAnonymousVisitors / 1000).toFixed(1)}k`
          : totalAnonymousVisitors.toString(),
      previousValue:
        totalAnonymousVisitorsPrevious >= 1000
          ? `${(totalAnonymousVisitorsPrevious / 1000).toFixed(1)}k`
          : totalAnonymousVisitorsPrevious.toString(),
    },
  ]

  return (
    <div className="min-w-0 grid gap-6">
      <h1 className="text-2xl font-medium text-neutral-700">{__('Latest Views', 'wp-statistics')}</h1>

      <LineChart
        title={__('Traffic Trends', 'wp-statistics')}
        data={trafficTrendsData}
        metrics={trafficTrendsMetrics}
        showPreviousPeriod={true}
        timeframe={timeframe}
        onTimeframeChange={setTimeframe}
      />

      <DataTable
        title={__('Latest Views', 'wp-statistics')}
        columns={createColumns(pluginUrl)}
        data={fakeUsers}
        defaultSort="lastVisit"
        rowLimit={50}
        showColumnManagement={true}
        showPagination={true}
      />
    </div>
  )
}
