import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute, Link } from '@tanstack/react-router'
import type { ColumnDef, SortingState } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { Info } from 'lucide-react'
import { useCallback, useMemo, useState } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { DataTableColumnHeaderSortable } from '@/components/custom/data-table-column-header-sortable'
import { DateRangePicker, type DateRange } from '@/components/custom/date-range-picker'
import { type Filter, FilterBar } from '@/components/custom/filter-bar'
import { FilterButton, type FilterField } from '@/components/custom/filter-button'
import { Badge } from '@/components/ui/badge'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import { formatDateForAPI } from '@/lib/utils'
import { WordPress } from '@/lib/wordpress'
import type { ViewRecord } from '@/services/visitor-insight/get-views'
import { getViewsQueryOptions } from '@/services/visitor-insight/get-views'

const PER_PAGE = 50

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

// Transform API data to component interface
const transformViewData = (record: ViewRecord): ViewData => {
  // Determine identifier (IP or hash)
  const identifier = record.ip_address || record.visitor_hash || 'Unknown'

  // Parse entry page for query string
  const entryPageUrl = record.entry_page || '/'
  const hasQueryString = entryPageUrl.includes('?')
  const queryString = hasQueryString ? entryPageUrl.split('?')[1] : undefined

  // Extract UTM campaign if present
  let utmCampaign: string | undefined
  if (queryString) {
    const params = new URLSearchParams(queryString)
    utmCampaign = params.get('utm_campaign') || undefined
  }

  return {
    lastVisit: record.last_visit,
    visitorInfo: {
      country: {
        code: record.country_code?.toLowerCase() || '000',
        name: record.country_name || 'Unknown',
        region: record.region_name || '',
        city: record.city_name || '',
      },
      os: {
        icon: record.os_name?.toLowerCase().replace(/\s+/g, '_') || 'unknown',
        name: record.os_name || 'Unknown',
      },
      browser: {
        icon: record.browser_name?.toLowerCase().replace(/\s+/g, '_') || 'unknown',
        name: record.browser_name || 'Unknown',
        version: '',
      },
      user: record.user_id
        ? {
            username: record.user_login || 'user',
            id: record.user_id,
            email: '',
            role: '',
          }
        : undefined,
      identifier,
    },
    page: {
      title: record.entry_page_title || record.entry_page || 'Unknown',
      url: record.entry_page || '/',
    },
    referrer: {
      domain: record.referrer_domain || undefined,
      fullUrl: record.referrer_domain ? `https://${record.referrer_domain}` : undefined,
      category: record.referrer_channel?.toUpperCase() || 'DIRECT TRAFFIC',
    },
    entryPage: {
      title: record.entry_page_title || record.entry_page || 'Unknown',
      url: entryPageUrl,
      hasQueryString,
      queryString: hasQueryString ? `?${queryString}` : undefined,
      utmCampaign,
    },
    totalViews: record.total_views || 0,
  }
}

function RouteComponent() {
  const [appliedFilters, setAppliedFilters] = useState<Filter[]>([])
  const [page, setPage] = useState(1)
  const [sorting, setSorting] = useState<SortingState>([{ id: 'lastVisit', desc: true }])
  const [dateRange, setDateRange] = useState<DateRange>({
    from: new Date(),
    to: new Date(),
  })
  const [compareDateRange, setCompareDateRange] = useState<DateRange | undefined>(undefined)

  const wp = WordPress.getInstance()
  const pluginUrl = wp.getPluginUrl()
  const columns = createColumns(pluginUrl)

  const handleDateRangeUpdate = useCallback(
    (values: { range: DateRange; rangeCompare?: DateRange }) => {
      setDateRange(values.range)
      setCompareDateRange(values.rangeCompare)
      setPage(1)
    },
    []
  )

  // Determine sort parameters from sorting state
  const orderBy = sorting.length > 0 ? sorting[0].id : 'lastVisit'
  const order = sorting.length > 0 && sorting[0].desc ? 'desc' : 'asc'

  // Fetch data from API
  const {
    data: response,
    isFetching,
    isError,
  } = useQuery({
    ...getViewsQueryOptions({
      page,
      per_page: PER_PAGE,
      order_by: orderBy,
      order: order as 'asc' | 'desc',
      date_from: formatDateForAPI(dateRange.from),
      date_to: formatDateForAPI(dateRange.to || dateRange.from),
      ...(compareDateRange?.from && compareDateRange?.to && {
        previous_date_from: formatDateForAPI(compareDateRange.from),
        previous_date_to: formatDateForAPI(compareDateRange.to),
      }),
      filters: appliedFilters,
    }),
    placeholderData: keepPreviousData,
  })

  // Transform API data to component interface
  const tableData = useMemo(() => {
    if (!response?.data?.data?.rows) return []
    return response.data.data.rows.map(transformViewData)
  }, [response])

  // Get pagination info from meta
  const totalRows = response?.data?.meta?.total_rows ?? 0
  const totalPages = response?.data?.meta?.total_pages || Math.ceil(totalRows / PER_PAGE) || 1

  // Handle sorting changes
  const handleSortingChange = useCallback((newSorting: SortingState) => {
    setSorting(newSorting)
    setPage(1) // Reset to first page when sorting changes
  }, [])

  // Handle page changes
  const handlePageChange = useCallback((newPage: number) => {
    setPage(newPage)
  }, [])

  // Get filter fields for 'views' group from localized data
  const filterFields = useMemo<FilterField[]>(() => {
    return wp.getFilterFieldsByGroup('views') as FilterField[]
  }, [wp])

  const handleRemoveFilter = (filterId: string) => {
    setAppliedFilters((prev) => prev.filter((f) => f.id !== filterId))
    setPage(1) // Reset to first page when filters change
  }

  // Handle filter application with page reset
  const handleApplyFilters = useCallback((filters: Filter[]) => {
    setAppliedFilters(filters)
    setPage(1) // Reset to first page when filters change
  }, [])

  return (
    <div className="min-w-0">
      {/* Header row with title and filter button */}
      <div className="flex items-center justify-between p-4 bg-white border-b border-input">
        <h1 className="text-2xl font-medium text-neutral-700">{__('Views', 'wp-statistics')}</h1>
        <div className="flex items-center gap-2">
          {filterFields.length > 0 && (
            <FilterButton fields={filterFields} appliedFilters={appliedFilters} onApplyFilters={handleApplyFilters} />
          )}
          <DateRangePicker
            initialDateFrom={dateRange.from}
            initialDateTo={dateRange.to}
            onUpdate={handleDateRangeUpdate}
            showCompare={true}
            align="end"
          />
        </div>
      </div>

      <div className="p-4">
        {/* Applied filters row (separate from button) */}
        {appliedFilters.length > 0 && (
          <FilterBar filters={appliedFilters} onRemoveFilter={handleRemoveFilter} className="mb-4" />
        )}

        {isError ? (
          <div className="p-4 text-center text-destructive">
            {__('Failed to load views data. Please try again.', 'wp-statistics')}
          </div>
        ) : (
          <DataTable
            columns={columns}
            data={tableData}
            sorting={sorting}
            onSortingChange={handleSortingChange}
            manualSorting={true}
            manualPagination={true}
            pageCount={totalPages}
            page={page}
            onPageChange={handlePageChange}
            totalRows={totalRows}
            rowLimit={PER_PAGE}
            showColumnManagement={true}
            showPagination={true}
            isFetching={isFetching}
            emptyStateMessage={__('No views found for the selected period', 'wp-statistics')}
          />
        )}
      </div>
    </div>
  )
}
