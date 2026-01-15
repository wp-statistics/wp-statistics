import type { Meta, StoryObj } from '@storybook/react'

import { BarListSkeleton } from './bar-list-skeleton'

const meta = {
  title: 'UI/Skeletons/BarListSkeleton',
  component: BarListSkeleton,
  parameters: {
    layout: 'padded',
  },
  tags: ['autodocs'],
  argTypes: {
    items: {
      control: { type: 'range', min: 1, max: 15 },
      description: 'Number of items',
    },
    showIcon: {
      control: 'boolean',
      description: 'Show icon placeholder',
    },
  },
} satisfies Meta<typeof BarListSkeleton>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    items: 5,
    showIcon: false,
  },
}

export const WithIcons: Story = {
  args: {
    items: 5,
    showIcon: true,
  },
}

export const ThreeItems: Story = {
  args: {
    items: 3,
    showIcon: false,
  },
}

export const TenItems: Story = {
  args: {
    items: 10,
    showIcon: false,
  },
}

export const AllVariants: Story = {
  render: () => (
    <div className="space-y-8">
      <div>
        <p className="text-xs text-muted-foreground mb-4">Default (5 items, no icons)</p>
        <div className="border rounded-lg p-4 max-w-md">
          <BarListSkeleton items={5} showIcon={false} />
        </div>
      </div>
      <div>
        <p className="text-xs text-muted-foreground mb-4">With icons</p>
        <div className="border rounded-lg p-4 max-w-md">
          <BarListSkeleton items={5} showIcon={true} />
        </div>
      </div>
      <div>
        <p className="text-xs text-muted-foreground mb-4">10 items with icons</p>
        <div className="border rounded-lg p-4 max-w-md">
          <BarListSkeleton items={10} showIcon={true} />
        </div>
      </div>
    </div>
  ),
}
