import { DataTable } from '@components/custom/data-table'
import { keepPreviousData, useQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'
import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'
import { useCallback, useState } from 'react'

import { getToday } from '@/lib/utils'
import type { SearchTerm as APISearchTerm } from '@/services/visitor-insight/get-search-terms'
import { getSearchTermsQueryOptions } from '@/services/visitor-insight/get-search-terms'

export const Route = createLazyFileRoute('/(visitor-insights)/search-terms')({
  component: RouteComponent,
})

type SearchTermData = {
  searchTerm: string
  searches: number
}

// Transform API response to component interface
const transformSearchTermData = (apiSearchTerm: APISearchTerm): SearchTermData => {
  return {
    searchTerm: apiSearchTerm.search_term || '',
    searches: parseInt(apiSearchTerm.searches, 10) || 0,
  }
}

const columns: ColumnDef<SearchTermData>[] = [
  {
    accessorKey: 'searchTerm',
    header: 'Search Term',
    cell: ({ row }) => {
      const searchTerm = row.getValue('searchTerm') as string
      const displayTerm = searchTerm.length > 50 ? `${searchTerm.substring(0, 50)}…` : searchTerm
      return <div className="max-w-md min-h-7 align-middle flex items-center">{displayTerm}</div>
    },
  },
  {
    accessorKey: 'searches',
    header: () => <div className="text-right pr-4">Searches</div>,
    cell: ({ row }) => {
      const searches = row.getValue('searches') as number
      const formattedSearches = searches.toLocaleString()
      return <div className="text-right pr-4">{formattedSearches}</div>
    },
  },
]

const PER_PAGE = 20

function RouteComponent() {
  const [page, setPage] = useState(1)

  // Default date range to last 1 year
  // Temp date will replace with date picker
  const today = getToday()
  const oneYearAgo = new Date()
  oneYearAgo.setFullYear(oneYearAgo.getFullYear() - 1)
  const dateFrom = oneYearAgo.toISOString().split('T')[0]

  const {
    data: response,
    isFetching,
    isError,
    error,
  } = useQuery({
    ...getSearchTermsQueryOptions({
      page,
      per_page: PER_PAGE,
      date_from: dateFrom,
      date_to: today,
    }),
    placeholderData: keepPreviousData,
  })

  // Transform API data to component format
  const searchTerms = response?.data?.data?.rows?.map(transformSearchTermData) || []
  const total = response?.data?.meta?.total_rows ?? 0
  const totalPages = response?.data?.meta?.total_pages || Math.ceil(total / PER_PAGE) || 1

  // Handle page change
  const handlePageChange = useCallback((newPage: number) => {
    setPage(newPage)
  }, [])

  return (
    <div className="min-w-0">
      <div className="flex items-center justify-between p-4 bg-white border-b border-input">
        <h1 className="text-2xl font-medium text-neutral-700">{__('Search Terms', 'wp-statistics')}</h1>
      </div>

      <div className="p-4">
        {isError ? (
          <div className="p-4 text-center">
            <p className="text-red-500">{__('Failed to load search terms', 'wp-statistics')}</p>
            <p className="text-sm text-muted-foreground">{error?.message}</p>
          </div>
        ) : (
          <DataTable
            columns={columns}
            data={searchTerms}
            manualPagination={true}
            pageCount={totalPages}
            page={page}
            onPageChange={handlePageChange}
            totalRows={total}
            rowLimit={PER_PAGE}
            showColumnManagement={false}
            showPagination={true}
            isFetching={isFetching}
            emptyStateMessage={__('No data available for the selected period', 'wp-statistics')}
          />
        )}
      </div>
    </div>
  )
}
