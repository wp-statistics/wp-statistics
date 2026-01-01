import type { Meta, StoryObj } from '@storybook/react'
import type { SortingState } from '@tanstack/react-table'
import { useState } from 'react'
import { expect, userEvent, within } from 'storybook/test'

import { DataTable } from './data-table'
import type { VisitorData } from './data-table-example-columns'
import { exampleColumns, exampleData } from './data-table-example-columns'

const meta = {
  title: 'Custom/DataTable',
  component: DataTable<VisitorData, unknown>,
  parameters: {
    layout: 'padded',
  },
  tags: ['autodocs'],
  argTypes: {
    title: {
      control: 'text',
      description: 'Optional title displayed above the table',
    },
    defaultSort: {
      control: 'text',
      description: 'Column ID to sort by default (for client-side sorting)',
    },
    rowLimit: {
      control: 'number',
      description: 'Number of rows per page',
    },
    showColumnManagement: {
      control: 'boolean',
      description: 'Show/hide column visibility toggle button',
    },
    showPagination: {
      control: 'boolean',
      description: 'Show/hide pagination controls',
    },
    fullReportLink: {
      control: 'object',
      description: 'Link to full report (boolean for default, object for custom text/url)',
    },
    hiddenColumns: {
      control: 'object',
      description: 'Array of column IDs to hide by default (users can toggle them via column management)',
    },
    emptyStateMessage: {
      control: 'text',
      description: 'Custom message to display when the table has no data',
    },
    // Server-side sorting props
    sorting: {
      control: 'object',
      description: 'External sorting state for server-side sorting',
    },
    onSortingChange: {
      action: 'sortingChanged',
      description: 'Callback when sorting changes (for server-side sorting)',
    },
    manualSorting: {
      control: 'boolean',
      description: 'Enable server-side sorting (disables client-side sorting)',
    },
    // Server-side pagination props
    manualPagination: {
      control: 'boolean',
      description: 'Enable server-side pagination (disables client-side pagination)',
    },
    pageCount: {
      control: 'number',
      description: 'Total number of pages (required for server-side pagination)',
    },
    page: {
      control: 'number',
      description: 'Current page (1-indexed, for server-side pagination)',
    },
    onPageChange: {
      action: 'pageChanged',
      description: 'Callback when page changes (for server-side pagination)',
    },
    totalRows: {
      control: 'number',
      description: 'Total number of rows across all pages (for server-side pagination display)',
    },
  },
} satisfies Meta<typeof DataTable<VisitorData, unknown>>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    columns: exampleColumns,
    data: exampleData,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify table is rendered
    const table = canvas.getByRole('table')
    await expect(table).toBeInTheDocument()

    // Verify column headers exist
    await expect(canvas.getByText('Visitor')).toBeInTheDocument()
    await expect(canvas.getByText('Total Views')).toBeInTheDocument()

    // Verify data rows exist
    const rows = canvas.getAllByRole('row')
    await expect(rows.length).toBeGreaterThan(1) // Header + data rows
  },
}

export const WithDefaultSort: Story = {
  args: {
    columns: exampleColumns,
    data: exampleData,
    defaultSort: 'totalViews',
  },
}

export const CustomRowLimit: Story = {
  args: {
    columns: exampleColumns,
    data: exampleData,
    rowLimit: 5,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify pagination is shown (since we have 10 items with 5 per page)
    await expect(canvas.getByText('1–5')).toBeInTheDocument()
    await expect(canvas.getByText(/of 10/)).toBeInTheDocument()

    // Find and click next page button
    const nextButton = canvas.getByRole('button', { name: /next/i })
    await expect(nextButton).toBeInTheDocument()
    await userEvent.click(nextButton)

    // Verify we're on page 2
    await expect(canvas.getByText('6–10')).toBeInTheDocument()

    // Click previous to go back
    const prevButton = canvas.getByRole('button', { name: /previous/i })
    await userEvent.click(prevButton)
    await expect(canvas.getByText('1–5')).toBeInTheDocument()
  },
}

export const WithoutColumnManagement: Story = {
  args: {
    columns: exampleColumns,
    data: exampleData,
    showColumnManagement: false,
  },
}

export const WithoutPagination: Story = {
  args: {
    columns: exampleColumns,
    data: exampleData,
    showPagination: false,
  },
}

export const EmptyState: Story = {
  args: {
    columns: exampleColumns,
    data: [],
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify empty state message is shown
    await expect(canvas.getByText('No data available')).toBeInTheDocument()

    // Table should still exist but be empty
    const table = canvas.getByRole('table')
    await expect(table).toBeInTheDocument()

    // Pagination should not be visible for empty data
    const paginationText = canvas.queryByText(/of 0/)
    await expect(paginationText).not.toBeInTheDocument()
  },
}

export const EmptyStateWithCustomMessage: Story = {
  args: {
    columns: exampleColumns,
    data: [],
    emptyStateMessage: 'No visitors found matching your criteria',
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify custom empty state message is shown
    await expect(canvas.getByText('No visitors found matching your criteria')).toBeInTheDocument()
  },
}

export const MinimalConfiguration: Story = {
  args: {
    columns: exampleColumns,
    data: exampleData,
    showColumnManagement: false,
    showPagination: false,
  },
}

export const FullFeatured: Story = {
  args: {
    columns: exampleColumns,
    data: exampleData,
    defaultSort: 'totalViews',
    rowLimit: 10,
    showColumnManagement: true,
    showPagination: true,
  },
}

export const WithFullReportLinkCustom: Story = {
  args: {
    columns: exampleColumns,
    data: exampleData,
    fullReportLink: {
      text: 'View Complete Analytics Report',
      action: () => {},
    },
  },
}

export const FullFeaturedWithReportLink: Story = {
  args: {
    columns: exampleColumns,
    data: exampleData,
    defaultSort: 'totalViews',
    rowLimit: 10,
    showColumnManagement: true,
    showPagination: true,
    fullReportLink: {
      text: 'See All Visitors',
      action: () => {},
    },
  },
}

export const WithTitle: Story = {
  args: {
    columns: exampleColumns,
    data: exampleData,
    title: 'Visitor Analytics',
  },
}

export const WithTitleAndFullFeatures: Story = {
  args: {
    columns: exampleColumns,
    data: exampleData,
    title: 'Top Visitors Report',
    defaultSort: 'totalViews',
    rowLimit: 10,
    showColumnManagement: true,
    showPagination: true,
    fullReportLink: {
      text: 'View Complete Report',
      action: () => {},
    },
  },
}

export const WithHiddenColumns: Story = {
  args: {
    columns: exampleColumns,
    data: exampleData,
    showColumnManagement: true,
    hiddenColumns: ['entryPage', 'exitPage'],
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Entry Page and Exit Page columns should be hidden initially
    await expect(canvas.queryByRole('columnheader', { name: /entry page/i })).not.toBeInTheDocument()
    await expect(canvas.queryByRole('columnheader', { name: /exit page/i })).not.toBeInTheDocument()

    // But other columns should be visible
    await expect(canvas.getByText('Total Views')).toBeInTheDocument()
    await expect(canvas.getByText('Referrer')).toBeInTheDocument()
  },
}

// Server-side sorting story with render function for state management
export const ServerSideSorting: Story = {
  render: function ServerSideSortingStory() {
    const [sorting, setSorting] = useState<SortingState>([{ id: 'totalViews', desc: true }])

    // Simulate server-side sorted data
    const sortedData = [...exampleData].sort((a, b) => {
      if (sorting.length === 0) return 0
      const { id, desc } = sorting[0]
      const aVal = a[id as keyof VisitorData]
      const bVal = b[id as keyof VisitorData]
      if (typeof aVal === 'number' && typeof bVal === 'number') {
        return desc ? bVal - aVal : aVal - bVal
      }
      return desc ? String(bVal).localeCompare(String(aVal)) : String(aVal).localeCompare(String(bVal))
    })

    return (
      <div>
        <div className="mb-4 p-4 bg-slate-100 rounded text-sm">
          <strong>Current Sort:</strong>{' '}
          {sorting.length > 0 ? `${sorting[0].id} (${sorting[0].desc ? 'DESC' : 'ASC'})` : 'None'}
        </div>
        <DataTable
          columns={exampleColumns}
          data={sortedData}
          sorting={sorting}
          onSortingChange={setSorting}
          manualSorting={true}
          showPagination={true}
          rowLimit={10}
        />
      </div>
    )
  },
}

// Server-side pagination story with render function for state management
export const ServerSidePagination: Story = {
  render: function ServerSidePaginationStory() {
    const [page, setPage] = useState(1)
    const perPage = 5
    const totalRows = exampleData.length
    const pageCount = Math.ceil(totalRows / perPage)

    // Simulate server-side paginated data
    const paginatedData = exampleData.slice((page - 1) * perPage, page * perPage)

    return (
      <div>
        <div className="mb-4 p-4 bg-slate-100 rounded text-sm">
          <strong>Current Page:</strong> {page} of {pageCount} | <strong>Total Rows:</strong> {totalRows}
        </div>
        <DataTable
          columns={exampleColumns}
          data={paginatedData}
          manualPagination={true}
          pageCount={pageCount}
          page={page}
          onPageChange={setPage}
          totalRows={totalRows}
          rowLimit={perPage}
          showPagination={true}
        />
      </div>
    )
  },
}

// Combined server-side sorting and pagination
export const ServerSideSortingAndPagination: Story = {
  render: function ServerSideSortingAndPaginationStory() {
    const [sorting, setSorting] = useState<SortingState>([{ id: 'totalViews', desc: true }])
    const [page, setPage] = useState(1)
    const perPage = 5
    const totalRows = exampleData.length
    const pageCount = Math.ceil(totalRows / perPage)

    // Handle sorting change - reset to page 1
    const handleSortingChange = (newSorting: SortingState) => {
      setSorting(newSorting)
      setPage(1)
    }

    // Simulate server-side sorted data
    const sortedData = [...exampleData].sort((a, b) => {
      if (sorting.length === 0) return 0
      const { id, desc } = sorting[0]
      const aVal = a[id as keyof VisitorData]
      const bVal = b[id as keyof VisitorData]
      if (typeof aVal === 'number' && typeof bVal === 'number') {
        return desc ? bVal - aVal : aVal - bVal
      }
      return desc ? String(bVal).localeCompare(String(aVal)) : String(aVal).localeCompare(String(bVal))
    })

    // Then paginate
    const paginatedData = sortedData.slice((page - 1) * perPage, page * perPage)

    return (
      <div>
        <div className="mb-4 p-4 bg-slate-100 rounded text-sm space-y-1">
          <div>
            <strong>Current Sort:</strong>{' '}
            {sorting.length > 0 ? `${sorting[0].id} (${sorting[0].desc ? 'DESC' : 'ASC'})` : 'None'}
          </div>
          <div>
            <strong>Current Page:</strong> {page} of {pageCount} | <strong>Total Rows:</strong> {totalRows}
          </div>
        </div>
        <DataTable
          columns={exampleColumns}
          data={paginatedData}
          sorting={sorting}
          onSortingChange={handleSortingChange}
          manualSorting={true}
          manualPagination={true}
          pageCount={pageCount}
          page={page}
          onPageChange={setPage}
          totalRows={totalRows}
          rowLimit={perPage}
          showPagination={true}
          showColumnManagement={true}
        />
      </div>
    )
  },
}
