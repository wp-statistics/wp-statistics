import type { Meta, StoryObj } from '@storybook/react'
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
    fullReportLink: {
      control: 'object',
      description: 'Link to full report (boolean for default, object for custom text/url)',
    },
    hiddenColumns: {
      control: 'object',
      description: 'Array of column IDs to hide by default (users can toggle them via column management)',
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
}
