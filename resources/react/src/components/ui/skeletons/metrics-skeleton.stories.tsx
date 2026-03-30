import type { Meta, StoryObj } from '@storybook/react'

import { MetricsSkeleton } from './metrics-skeleton'

const meta = {
  title: 'UI/Skeletons/MetricsSkeleton',
  component: MetricsSkeleton,
  parameters: {
    layout: 'padded',
  },
  tags: ['autodocs'],
  argTypes: {
    count: {
      control: { type: 'range', min: 1, max: 12 },
      description: 'Number of metric items',
    },
    columns: {
      control: 'select',
      options: [1, 2, 3, 4, 6],
      description: 'Number of grid columns',
    },
  },
} satisfies Meta<typeof MetricsSkeleton>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    count: 4,
    columns: 4,
  },
}

export const TwoColumns: Story = {
  args: {
    count: 4,
    columns: 2,
  },
}

export const ThreeColumns: Story = {
  args: {
    count: 6,
    columns: 3,
  },
}

export const SingleColumn: Story = {
  args: {
    count: 4,
    columns: 1,
  },
}

export const SixColumns: Story = {
  args: {
    count: 6,
    columns: 6,
  },
}

export const EightMetrics: Story = {
  args: {
    count: 8,
    columns: 4,
  },
}

export const AllVariants: Story = {
  render: () => (
    <div className="space-y-8">
      <div>
        <p className="text-xs text-muted-foreground mb-4">4 items, 4 columns (default)</p>
        <div className="border rounded-lg p-4">
          <MetricsSkeleton count={4} columns={4} />
        </div>
      </div>
      <div>
        <p className="text-xs text-muted-foreground mb-4">6 items, 3 columns</p>
        <div className="border rounded-lg p-4">
          <MetricsSkeleton count={6} columns={3} />
        </div>
      </div>
      <div>
        <p className="text-xs text-muted-foreground mb-4">4 items, 2 columns</p>
        <div className="border rounded-lg p-4">
          <MetricsSkeleton count={4} columns={2} />
        </div>
      </div>
    </div>
  ),
}
