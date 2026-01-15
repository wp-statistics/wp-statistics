import type { Meta, StoryObj } from '@storybook/react'
import { BarChart3, FileSearch, Users } from 'lucide-react'
import { expect, within } from 'storybook/test'

import { Button } from './button'
import { EmptyState } from './empty-state'

const meta = {
  title: 'UI/EmptyState',
  component: EmptyState,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
  argTypes: {
    title: {
      control: 'text',
      description: 'Title text displayed in the empty state',
    },
    description: {
      control: 'text',
      description: 'Optional description text',
    },
  },
} satisfies Meta<typeof EmptyState>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {},
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('No data available')).toBeInTheDocument()
  },
}

export const WithCustomTitle: Story = {
  args: {
    title: 'No visitors found',
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('No visitors found')).toBeInTheDocument()
  },
}

export const WithDescription: Story = {
  args: {
    title: 'No results',
    description: 'Try adjusting your filters or search criteria',
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('No results')).toBeInTheDocument()
    await expect(canvas.getByText('Try adjusting your filters or search criteria')).toBeInTheDocument()
  },
}

export const WithCustomIcon: Story = {
  args: {
    icon: <Users className="h-12 w-12" />,
    title: 'No users',
    description: 'No users match your current filters',
  },
}

export const WithFileSearchIcon: Story = {
  args: {
    icon: <FileSearch className="h-12 w-12" />,
    title: 'No pages found',
    description: 'We could not find any pages matching your search',
  },
}

export const WithAction: Story = {
  args: {
    icon: <BarChart3 className="h-12 w-12" />,
    title: 'No analytics data',
    description: 'Start tracking to see your statistics',
    action: <Button size="sm">Start Tracking</Button>,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByRole('button', { name: /start tracking/i })).toBeInTheDocument()
  },
}

export const WithOutlineAction: Story = {
  args: {
    title: 'No data yet',
    description: 'Check back later for updates',
    action: (
      <Button variant="outline" size="sm">
        Refresh
      </Button>
    ),
  },
}

export const AllVariants: Story = {
  render: () => (
    <div className="grid grid-cols-2 gap-8">
      <div className="border rounded p-4">
        <p className="text-xs text-muted-foreground mb-2">Default</p>
        <EmptyState />
      </div>
      <div className="border rounded p-4">
        <p className="text-xs text-muted-foreground mb-2">With custom icon</p>
        <EmptyState icon={<FileSearch className="h-12 w-12" />} title="Search Results" description="No pages found" />
      </div>
      <div className="border rounded p-4">
        <p className="text-xs text-muted-foreground mb-2">With action</p>
        <EmptyState
          title="With Action"
          action={
            <Button variant="outline" size="sm">
              Refresh
            </Button>
          }
        />
      </div>
      <div className="border rounded p-4">
        <p className="text-xs text-muted-foreground mb-2">Full featured</p>
        <EmptyState
          icon={<Users className="h-12 w-12" />}
          title="No visitors"
          description="Your site has no visitors yet"
          action={<Button size="sm">View Demo</Button>}
        />
      </div>
    </div>
  ),
}
