import type { Meta, StoryObj } from '@storybook/react'
import { DataTable } from './data-table'
import type { VisitorData } from './data-table-example-columns'
import { exampleColumns, exampleData } from './data-table-example-columns'

const meta = {
  title: 'Components/DataTable',
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
      description: 'Column ID to sort by default',
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
  },
} satisfies Meta<typeof DataTable<VisitorData, unknown>>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    columns: exampleColumns,
    data: exampleData,
  },
}

export const WithTitle: Story = {
  args: {
    columns: exampleColumns,
    data: exampleData,
    title: 'Top Visitors',
  },
}

export const WithDefaultSort: Story = {
  args: {
    columns: exampleColumns,
    data: exampleData,
    title: 'Top Visitors',
    defaultSort: 'totalViews',
  },
}

export const CustomRowLimit: Story = {
  args: {
    columns: exampleColumns,
    data: exampleData,
    title: 'Top Visitors',
    rowLimit: 5,
  },
}

export const WithoutColumnManagement: Story = {
  args: {
    columns: exampleColumns,
    data: exampleData,
    title: 'Top Visitors',
    showColumnManagement: false,
  },
}

export const WithoutPagination: Story = {
  args: {
    columns: exampleColumns,
    data: exampleData,
    title: 'Top Visitors',
    showPagination: false,
  },
}

export const EmptyState: Story = {
  args: {
    columns: exampleColumns,
    data: [],
    title: 'No Data Example',
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
    title: 'Visitor Analytics Dashboard',
    defaultSort: 'totalViews',
    rowLimit: 10,
    showColumnManagement: true,
    showPagination: true,
  },
}
