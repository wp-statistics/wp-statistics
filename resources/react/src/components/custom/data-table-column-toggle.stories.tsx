import type { Meta, StoryObj } from '@storybook/react'
import { getCoreRowModel, useReactTable } from '@tanstack/react-table'
import { useState } from 'react'
import { expect, fn, userEvent, within } from 'storybook/test'

import { DataTableColumnToggle } from './data-table-column-toggle'

// Example data type
interface SampleData {
  id: number
  visitorInfo: string
  totalViews: number
  entryPage: string
  exitPage: string
  referrer: string
  lastVisit: string
}

// Sample data for the table
const sampleData: SampleData[] = [
  {
    id: 1,
    visitorInfo: 'User 1',
    totalViews: 100,
    entryPage: '/home',
    exitPage: '/about',
    referrer: 'google.com',
    lastVisit: '2025-01-15',
  },
  {
    id: 2,
    visitorInfo: 'User 2',
    totalViews: 50,
    entryPage: '/products',
    exitPage: '/checkout',
    referrer: 'facebook.com',
    lastVisit: '2025-01-14',
  },
]

// Wrapper component that creates a table instance
function DataTableColumnToggleWrapper({
  data = sampleData,
  initialColumnOrder,
  defaultHiddenColumns = [],
  onColumnVisibilityChange,
  onColumnOrderChange,
  onReset,
}: {
  data?: SampleData[]
  initialColumnOrder?: string[]
  defaultHiddenColumns?: string[]
  onColumnVisibilityChange?: (visibility: Record<string, boolean>) => void
  onColumnOrderChange?: (order: string[]) => void
  onReset?: () => void
}) {
  const [columnVisibility, setColumnVisibility] = useState<Record<string, boolean>>({})

  const columns = [
    { accessorKey: 'visitorInfo', header: 'Visitor Info', enableHiding: true },
    { accessorKey: 'totalViews', header: 'Total Views', enableHiding: true },
    { accessorKey: 'entryPage', header: 'Entry Page', enableHiding: true },
    { accessorKey: 'exitPage', header: 'Exit Page', enableHiding: true },
    { accessorKey: 'referrer', header: 'Referrer', enableHiding: true },
    { accessorKey: 'lastVisit', header: 'Last Visit', enableHiding: true },
  ]

  const table = useReactTable({
    data,
    columns,
    getCoreRowModel: getCoreRowModel(),
    state: {
      columnVisibility,
    },
    onColumnVisibilityChange: setColumnVisibility,
  })

  return (
    <div className="flex flex-col gap-4">
      <div className="flex items-center justify-between">
        <span className="text-sm text-muted-foreground">Click the menu to manage columns</span>
        <DataTableColumnToggle
          table={table}
          initialColumnOrder={initialColumnOrder}
          defaultHiddenColumns={defaultHiddenColumns}
          onColumnVisibilityChange={onColumnVisibilityChange}
          onColumnOrderChange={onColumnOrderChange}
          onReset={onReset}
        />
      </div>
      <div className="p-4 bg-muted rounded-md">
        <p className="text-sm font-medium mb-2">Visible Columns:</p>
        <ul className="text-sm space-y-1">
          {table
            .getAllColumns()
            .filter((col) => col.getIsVisible())
            .map((col) => (
              <li key={col.id}>• {col.id}</li>
            ))}
        </ul>
      </div>
    </div>
  )
}

const meta = {
  title: 'Custom/DataTableColumnToggle',
  component: DataTableColumnToggle,
  parameters: {
    layout: 'padded',
  },
  tags: ['autodocs'],
  args: {
    onColumnVisibilityChange: fn(),
    onColumnOrderChange: fn(),
    onReset: fn(),
  },
} satisfies Meta<typeof DataTableColumnToggle>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  render: (args) => (
    <DataTableColumnToggleWrapper
      onColumnVisibilityChange={args.onColumnVisibilityChange}
      onColumnOrderChange={args.onColumnOrderChange}
      onReset={args.onReset}
    />
  ),
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Find and click the column toggle button
    const toggleButton = canvas.getByRole('button')
    await expect(toggleButton).toBeInTheDocument()
    await userEvent.click(toggleButton)

    // Verify dropdown menu is open with column options
    const body = within(document.body)
    await expect(body.getByText('Visitor Info')).toBeInTheDocument()
    await expect(body.getByText('Total Views')).toBeInTheDocument()

    // Close by clicking outside or pressing Escape
    await userEvent.keyboard('{Escape}')
  },
}

export const WithHiddenColumns: Story = {
  render: (args) => (
    <DataTableColumnToggleWrapper
      defaultHiddenColumns={['exitPage', 'referrer']}
      onColumnVisibilityChange={args.onColumnVisibilityChange}
      onColumnOrderChange={args.onColumnOrderChange}
      onReset={args.onReset}
    />
  ),
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify hidden columns are not in the visible list
    await expect(canvas.queryByText('• exitPage')).not.toBeInTheDocument()
    await expect(canvas.queryByText('• referrer')).not.toBeInTheDocument()

    // Verify visible columns are shown
    await expect(canvas.getByText('• visitorInfo')).toBeInTheDocument()
    await expect(canvas.getByText('• totalViews')).toBeInTheDocument()
  },
}

export const WithCustomOrder: Story = {
  render: (args) => (
    <DataTableColumnToggleWrapper
      initialColumnOrder={['totalViews', 'visitorInfo', 'lastVisit', 'entryPage', 'exitPage', 'referrer']}
      onColumnVisibilityChange={args.onColumnVisibilityChange}
      onColumnOrderChange={args.onColumnOrderChange}
      onReset={args.onReset}
    />
  ),
}

export const WithHiddenAndCustomOrder: Story = {
  render: (args) => (
    <DataTableColumnToggleWrapper
      initialColumnOrder={['totalViews', 'visitorInfo', 'lastVisit', 'entryPage', 'exitPage', 'referrer']}
      defaultHiddenColumns={['exitPage', 'referrer']}
      onColumnVisibilityChange={args.onColumnVisibilityChange}
      onColumnOrderChange={args.onColumnOrderChange}
      onReset={args.onReset}
    />
  ),
}
