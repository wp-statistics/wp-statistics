import type { Meta, StoryObj } from '@storybook/react'

import { TableSkeleton } from './table-skeleton'

const meta = {
  title: 'UI/Skeletons/TableSkeleton',
  component: TableSkeleton,
  parameters: {
    layout: 'padded',
  },
  tags: ['autodocs'],
  argTypes: {
    rows: {
      control: { type: 'range', min: 1, max: 20 },
      description: 'Number of rows',
    },
    columns: {
      control: { type: 'range', min: 1, max: 8 },
      description: 'Number of columns',
    },
    showHeader: {
      control: 'boolean',
      description: 'Show header row',
    },
  },
} satisfies Meta<typeof TableSkeleton>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    rows: 5,
    columns: 4,
    showHeader: true,
  },
}

export const WithoutHeader: Story = {
  args: {
    rows: 5,
    columns: 4,
    showHeader: false,
  },
}

export const ManyRows: Story = {
  args: {
    rows: 10,
    columns: 4,
    showHeader: true,
  },
}

export const ManyColumns: Story = {
  args: {
    rows: 5,
    columns: 6,
    showHeader: true,
  },
}

export const Minimal: Story = {
  args: {
    rows: 3,
    columns: 2,
    showHeader: true,
  },
}

export const AllVariants: Story = {
  render: () => (
    <div className="space-y-8">
      <div>
        <p className="text-xs text-muted-foreground mb-4">Default (5 rows, 4 columns)</p>
        <div className="border rounded-lg p-4">
          <TableSkeleton rows={5} columns={4} showHeader={true} />
        </div>
      </div>
      <div>
        <p className="text-xs text-muted-foreground mb-4">Without header</p>
        <div className="border rounded-lg p-4">
          <TableSkeleton rows={5} columns={4} showHeader={false} />
        </div>
      </div>
      <div>
        <p className="text-xs text-muted-foreground mb-4">10 rows, 6 columns</p>
        <div className="border rounded-lg p-4">
          <TableSkeleton rows={10} columns={6} showHeader={true} />
        </div>
      </div>
    </div>
  ),
}
