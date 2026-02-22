import type { Meta, StoryObj } from '@storybook/react'
import { expect, within } from 'storybook/test'

import { ComparisonTooltipHeader } from './comparison-tooltip-header'

const meta = {
  title: 'Custom/ComparisonTooltipHeader',
  component: ComparisonTooltipHeader,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
  decorators: [
    (Story) => (
      <div className="bg-neutral-900 text-white p-4 rounded-lg max-w-md">
        <Story />
      </div>
    ),
  ],
  argTypes: {
    label: {
      control: 'text',
      description: 'Pre-formatted label from useComparisonDateLabel()',
    },
  },
} satisfies Meta<typeof ComparisonTooltipHeader>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    label: 'Dec 16, 2025 - Jan 12, 2026 vs. Nov 18, 2025 - Dec 15, 2025',
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText(/Dec 16, 2025/)).toBeInTheDocument()
  },
}

export const ShortDateRange: Story = {
  args: {
    label: 'Jan 1 - Jan 7 vs. Dec 25 - Dec 31',
  },
}

export const MonthComparison: Story = {
  args: {
    label: 'January 2026 vs. December 2025',
  },
}

export const YearComparison: Story = {
  args: {
    label: '2026 vs. 2025',
  },
}

export const NoLabel: Story = {
  args: {
    label: undefined,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    // Component should render nothing when no label
    const container = canvas.queryByText(/vs\./)
    await expect(container).not.toBeInTheDocument()
  },
}

export const InTooltipContext: Story = {
  render: () => (
    <div className="bg-neutral-900 text-white p-4 rounded-lg">
      <ComparisonTooltipHeader label="Dec 16, 2025 - Jan 12, 2026 vs. Nov 18, 2025 - Dec 15, 2025" />
      <div className="space-y-1 text-sm">
        <div className="flex justify-between">
          <span className="text-neutral-400">Current:</span>
          <span className="font-medium">12,450</span>
        </div>
        <div className="flex justify-between">
          <span className="text-neutral-400">Previous:</span>
          <span className="font-medium">10,230</span>
        </div>
        <div className="flex justify-between text-green-400">
          <span>Change:</span>
          <span>+21.7%</span>
        </div>
      </div>
    </div>
  ),
}

export const MultipleTooltips: Story = {
  render: () => (
    <div className="space-y-4">
      <div className="bg-neutral-900 text-white p-4 rounded-lg">
        <ComparisonTooltipHeader label="This week vs. Last week" />
        <div className="text-sm">
          <span className="text-neutral-400">Views: </span>
          <span>5,230</span>
        </div>
      </div>
      <div className="bg-neutral-900 text-white p-4 rounded-lg">
        <ComparisonTooltipHeader label="This month vs. Last month" />
        <div className="text-sm">
          <span className="text-neutral-400">Views: </span>
          <span>22,100</span>
        </div>
      </div>
    </div>
  ),
}
