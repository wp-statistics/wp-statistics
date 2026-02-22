import type { Meta, StoryObj } from '@storybook/react'
import { expect, within } from 'storybook/test'

import { StaticSortIndicator } from './static-sort-indicator'

const meta = {
  title: 'Custom/StaticSortIndicator',
  component: StaticSortIndicator,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
  argTypes: {
    title: {
      control: 'text',
      description: 'Column title',
    },
    direction: {
      control: 'select',
      options: ['asc', 'desc'],
      description: 'Sort direction',
    },
  },
} satisfies Meta<typeof StaticSortIndicator>

export default meta
type Story = StoryObj<typeof meta>

export const Descending: Story = {
  args: {
    title: 'Views',
    direction: 'desc',
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('Views')).toBeInTheDocument()
  },
}

export const Ascending: Story = {
  args: {
    title: 'Date',
    direction: 'asc',
  },
}

export const Comparison: Story = {
  render: () => (
    <div className="space-y-4">
      <div className="flex items-center gap-4">
        <span className="w-24 text-xs text-muted-foreground">Descending:</span>
        <StaticSortIndicator title="Views" direction="desc" />
      </div>
      <div className="flex items-center gap-4">
        <span className="w-24 text-xs text-muted-foreground">Ascending:</span>
        <StaticSortIndicator title="Date" direction="asc" />
      </div>
    </div>
  ),
}

export const InTableHeader: Story = {
  render: () => (
    <table className="w-full border rounded-lg">
      <thead className="bg-neutral-50">
        <tr className="border-b">
          <th className="p-3 text-left text-sm font-medium">Page</th>
          <th className="p-3 text-left text-sm font-medium">
            <StaticSortIndicator title="Views" direction="desc" />
          </th>
          <th className="p-3 text-left text-sm font-medium">Visitors</th>
        </tr>
      </thead>
      <tbody>
        <tr className="border-b">
          <td className="p-3 text-sm">/home</td>
          <td className="p-3 text-sm">10,234</td>
          <td className="p-3 text-sm">5,120</td>
        </tr>
        <tr className="border-b">
          <td className="p-3 text-sm">/about</td>
          <td className="p-3 text-sm">5,678</td>
          <td className="p-3 text-sm">2,890</td>
        </tr>
        <tr>
          <td className="p-3 text-sm">/contact</td>
          <td className="p-3 text-sm">1,234</td>
          <td className="p-3 text-sm">890</td>
        </tr>
      </tbody>
    </table>
  ),
}

export const AllTitles: Story = {
  render: () => (
    <div className="space-y-4">
      <StaticSortIndicator title="Page Views" direction="desc" />
      <StaticSortIndicator title="Visitors" direction="desc" />
      <StaticSortIndicator title="Bounce Rate" direction="asc" />
      <StaticSortIndicator title="Duration" direction="desc" />
      <StaticSortIndicator title="Date" direction="asc" />
    </div>
  ),
}
