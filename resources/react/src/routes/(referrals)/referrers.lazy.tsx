import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { useMemo } from 'react'

import { DataTable } from '@/components/custom/data-table'
import { ErrorMessage } from '@/components/custom/error-message'
import { DetailOptionsDrawer, useDetailOptions } from '@/components/custom/options-drawer'
import { ReportPageHeader } from '@/components/custom/report-page-header'
import { NoticeContainer } from '@/components/ui/notice-container'
import { PanelSkeleton, TableSkeleton } from '@/components/ui/skeletons'
import { useGlobalFilters } from '@/hooks/use-global-filters'
import { usePercentageCalc } from '@/hooks/use-percentage-calc'
import { useUrlSortSync } from '@/hooks/use-url-sort-sync'
import { extractMeta, extractRows } from '@/lib/response-helpers'
import { formatCompactNumber, formatDecimal, formatDuration } from '@/lib/utils'
import { getReferrersQueryOptions, type ReferrerRow } from '@/services/referral/get-referrers'

const PER_PAGE = 20

export const Route = createLazyFileRoute('/(referrals)/referrers')({
  component: RouteComponent,
})

function RouteComponent() {
  const {
    filters: appliedFilters,
    page,
    setPage,
    handlePageChange,
    isInitialized,
    apiDateParams,
    isCompareEnabled,
  } = useGlobalFilters()

  const { sorting, handleSortingChange, orderBy, order } = useUrlSortSync({
    defaultSort: [{ id: 'visitors', desc: true }],
    onPageReset: () => setPage(1),
  })

  const calcPercentage = usePercentageCalc()

  // Options drawer - config is passed once and returned for drawer
  const options = useDetailOptions({ filterGroup: 'referrals' })

  // Fetch referrers data
  const {
    data: response,
    isLoading,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getReferrersQueryOptions({
      dateFrom: apiDateParams.date_from,
      dateTo: apiDateParams.date_to,
      compareDateFrom: apiDateParams.previous_date_from,
      compareDateTo: apiDateParams.previous_date_to,
      page,
      perPage: PER_PAGE,
      orderBy,
      order: order.toUpperCase() as 'ASC' | 'DESC',
      filters: appliedFilters || [],
    }),
    placeholderData: keepPreviousData,
    enabled: isInitialized,
  })

  // Define columns inline
  const columns = useMemo<ColumnDef<ReferrerRow>[]>(
    () => [
      {
        accessorKey: 'referrer_domain',
        header: __('Domain', 'wp-statistics'),
        enableSorting: true,
        cell: ({ row }) => {
          const domain = row.original.referrer_domain
          const name = row.original.referrer_name || domain
          const channel = row.original.referrer_channel

          return (
            <div className="flex items-center gap-2 min-w-0">
              <img
                src={`https://www.google.com/s2/favicons?domain=${domain}&sz=32`}
                alt=""
                className="w-4 h-4 shrink-0"
                loading="lazy"
              />
              <div className="min-w-0">
                <div className="text-xs font-medium text-neutral-700 truncate">{name}</div>
                <div className="text-xs text-muted-foreground truncate">
                  {domain}
                  {channel && ` · ${channel}`}
                </div>
              </div>
            </div>
          )
        },
      },
      {
        accessorKey: 'visitors',
        header: __('Referrals', 'wp-statistics'),
        enableSorting: true,
        meta: { align: 'right' },
        cell: ({ row }) => {
          const current = Number(row.original.visitors)
          const previous = row.original.previous?.visitors ? Number(row.original.previous.visitors) : undefined

          const { percentage, isNegative } =
            previous !== undefined ? calcPercentage(current, previous) : { percentage: '', isNegative: false }

          return (
            <div className="text-right">
              <span className="text-xs font-medium text-neutral-700 tabular-nums">{formatCompactNumber(current)}</span>
              {isCompareEnabled && previous !== undefined && (
                <span className={`ml-2 text-xs ${isNegative ? 'text-red-500' : 'text-green-500'}`}>
                  {isNegative ? '↓' : '↑'}
                  {percentage}%
                </span>
              )}
            </div>
          )
        },
      },
      {
        accessorKey: 'views',
        header: __('Views', 'wp-statistics'),
        enableSorting: true,
        meta: { align: 'right' },
        cell: ({ row }) => {
          const current = Number(row.original.views)
          const previous = row.original.previous?.views ? Number(row.original.previous.views) : undefined

          const { percentage, isNegative } =
            previous !== undefined ? calcPercentage(current, previous) : { percentage: '', isNegative: false }

          return (
            <div className="text-right">
              <span className="text-xs font-medium text-neutral-700 tabular-nums">{formatCompactNumber(current)}</span>
              {isCompareEnabled && previous !== undefined && (
                <span className={`ml-2 text-xs ${isNegative ? 'text-red-500' : 'text-green-500'}`}>
                  {isNegative ? '↓' : '↑'}
                  {percentage}%
                </span>
              )}
            </div>
          )
        },
      },
      {
        accessorKey: 'avg_session_duration',
        header: __('Avg. Duration', 'wp-statistics'),
        enableSorting: true,
        meta: { align: 'right' },
        cell: ({ row }) => {
          const current = Number(row.original.avg_session_duration)
          return (
            <div className="text-right">
              <span className="text-xs font-medium text-neutral-700 tabular-nums">{formatDuration(current)}</span>
            </div>
          )
        },
      },
      {
        accessorKey: 'bounce_rate',
        header: __('Bounce Rate', 'wp-statistics'),
        enableSorting: true,
        meta: { align: 'right' },
        cell: ({ row }) => {
          const current = Number(row.original.bounce_rate)
          return (
            <div className="text-right">
              <span className="text-xs font-medium text-neutral-700 tabular-nums">{formatDecimal(current)}%</span>
            </div>
          )
        },
      },
      {
        accessorKey: 'pages_per_session',
        header: __('Pages/Session', 'wp-statistics'),
        enableSorting: true,
        meta: { align: 'right' },
        cell: ({ row }) => {
          const current = Number(row.original.pages_per_session)
          return (
            <div className="text-right">
              <span className="text-xs font-medium text-neutral-700 tabular-nums">{formatDecimal(current)}</span>
            </div>
          )
        },
      },
    ],
    [calcPercentage, isCompareEnabled]
  )

  // Transform data
  const tableData = useMemo(() => {
    return extractRows<ReferrerRow>(response)
  }, [response])

  // Get pagination info
  const meta = extractMeta(response)
  const totalRows = meta?.totalRows ?? 0
  const totalPages = meta?.totalPages ?? 1

  // Loading states
  const showSkeleton = isLoading && !response

  return (
    <div className="min-w-0">
      <ReportPageHeader
        title={__('Referrers', 'wp-statistics')}
        filterGroup="referrals"
        optionsTriggerProps={options.triggerProps}
      />

      {/* Options Drawer */}
      <DetailOptionsDrawer {...options} />

      <div className="p-3">
        <NoticeContainer className="mb-2" currentRoute="referrers" />

        {isError ? (
          <div className="p-2 text-center">
            <ErrorMessage message={__('Failed to load referrers', 'wp-statistics')} />
            <p className="text-sm text-muted-foreground">{error?.message}</p>
          </div>
        ) : showSkeleton ? (
          <PanelSkeleton titleWidth="w-24">
            <TableSkeleton rows={10} columns={6} />
          </PanelSkeleton>
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
            showColumnManagement={false}
            showPagination={true}
            isFetching={isFetching}
            emptyStateMessage={__('No referrers found for the selected period', 'wp-statistics')}
            stickyHeader={true}
            borderless
          />
        )}
      </div>
    </div>
  )
}
