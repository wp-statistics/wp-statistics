import type { Meta, StoryObj } from '@storybook/react'

import { ChartSkeleton } from './chart-skeleton'

const meta = {
  title: 'UI/Skeletons/ChartSkeleton',
  component: ChartSkeleton,
  parameters: {
    layout: 'padded',
  },
  tags: ['autodocs'],
  argTypes: {
    height: {
      control: { type: 'range', min: 100, max: 500 },
      description: 'Chart height in pixels',
    },
    showTitle: {
      control: 'boolean',
      description: 'Show title placeholder',
    },
  },
} satisfies Meta<typeof ChartSkeleton>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    height: 256,
    showTitle: true,
  },
}

export const WithoutTitle: Story = {
  args: {
    height: 256,
    showTitle: false,
  },
}

export const Small: Story = {
  args: {
    height: 150,
    showTitle: true,
  },
}

export const Large: Story = {
  args: {
    height: 400,
    showTitle: true,
  },
}

export const AllVariants: Story = {
  render: () => (
    <div className="space-y-8">
      <div>
        <p className="text-xs text-muted-foreground mb-4">Default (256px with title)</p>
        <div className="border rounded-lg p-4">
          <ChartSkeleton height={256} showTitle={true} />
        </div>
      </div>
      <div>
        <p className="text-xs text-muted-foreground mb-4">Without title</p>
        <div className="border rounded-lg p-4">
          <ChartSkeleton height={200} showTitle={false} />
        </div>
      </div>
      <div>
        <p className="text-xs text-muted-foreground mb-4">Small chart (150px)</p>
        <div className="border rounded-lg p-4">
          <ChartSkeleton height={150} showTitle={true} />
        </div>
      </div>
    </div>
  ),
}
