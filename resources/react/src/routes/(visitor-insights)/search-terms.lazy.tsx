import { createLazyFileRoute } from '@tanstack/react-router'
import { DataTable } from '@components/custom/data-table'
import type { ColumnDef } from '@tanstack/react-table'
import { __ } from '@wordpress/i18n'

export const Route = createLazyFileRoute('/(visitor-insights)/search-terms')({
  component: RouteComponent,
})

type SearchTermData = {
  searchTerm: string
  searches: number
}
const columns: ColumnDef<SearchTermData>[] = [
  {
    accessorKey: 'searchTerm',
    header: 'Search Term',
    cell: ({ row }) => {
      const searchTerm = row.getValue('searchTerm') as string
      const displayTerm = searchTerm.length > 50 ? `${searchTerm.substring(0, 50)}…` : searchTerm
      return <div className="max-w-md">{displayTerm}</div>
    },
  },
  {
    accessorKey: 'searches',
    header: 'Searches',
    cell: ({ row }) => {
      const searches = row.getValue('searches') as number
      const formattedSearches = searches.toLocaleString()
      return <div className="text-right pr-4">{formattedSearches}</div>
    },
  },
]

const fakeData: SearchTermData[] = [
  {
    searchTerm: 'wordpress statistics plugin',
    searches: 12567,
  },
  {
    searchTerm: 'how to track visitors',
    searches: 8934,
  },
  {
    searchTerm: 'analytics dashboard',
    searches: 5421,
  },
  {
    searchTerm: 'visitor insights',
    searches: 4123,
  },
  {
    searchTerm: 'google analytics alternative',
    searches: 3456,
  },
  {
    searchTerm: 'privacy-friendly analytics',
    searches: 2789,
  },
  {
    searchTerm: 'wp statistics tutorial',
    searches: 2345,
  },
  {
    searchTerm: 'real-time visitor tracking',
    searches: 1987,
  },
  {
    searchTerm: 'gdpr compliant analytics',
    searches: 1654,
  },
  {
    searchTerm: 'page views counter',
    searches: 1432,
  },
  {
    searchTerm: 'how to install and configure wordpress statistics plugin for advanced visitor tracking',
    searches: 1234,
  },
  {
    searchTerm: 'site traffic analysis',
    searches: 987,
  },
  {
    searchTerm: 'visitor demographics',
    searches: 876,
  },
  {
    searchTerm: 'bounce rate optimization',
    searches: 765,
  },
  {
    searchTerm: 'seo analytics',
    searches: 654,
  },
  {
    searchTerm: 'referrer tracking',
    searches: 543,
  },
  {
    searchTerm: 'conversion tracking wordpress',
    searches: 432,
  },
  {
    searchTerm: 'heatmap plugin',
    searches: 321,
  },
  {
    searchTerm: 'user behavior tracking',
    searches: 234,
  },
  {
    searchTerm: 'export analytics data',
    searches: 147,
  },
  {
    searchTerm: 'visitor location map',
    searches: 89,
  },
  {
    searchTerm: 'custom reports',
    searches: 56,
  },
  {
    searchTerm: 'mobile analytics',
    searches: 34,
  },
  {
    searchTerm: 'search term analysis',
    searches: 23,
  },
  {
    searchTerm: 'performance metrics',
    searches: 12,
  },
]

function RouteComponent() {
  return (
    <div>
      <h1 className="text-2xl font-medium text-neutral-700 mb-6">{__('Search Terms', 'wp-statistics')}</h1>
      <DataTable
        columns={columns}
        data={fakeData}
        defaultSort="searches"
        rowLimit={20}
        showColumnManagement={false}
        showPagination={true}
      />
    </div>
  )
}
