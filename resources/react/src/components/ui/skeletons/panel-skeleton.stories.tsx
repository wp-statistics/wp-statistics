import type { Meta, StoryObj } from '@storybook/react'

import { BarListSkeleton } from './bar-list-skeleton'
import { ChartSkeleton } from './chart-skeleton'
import { MetricsSkeleton } from './metrics-skeleton'
import { PanelSkeleton } from './panel-skeleton'
import { TableSkeleton } from './table-skeleton'

const meta = {
  title: 'UI/Skeletons/PanelSkeleton',
  component: PanelSkeleton,
  parameters: {
    layout: 'padded',
  },
  tags: ['autodocs'],
  argTypes: {
    showTitle: {
      control: 'boolean',
      description: 'Show title area',
    },
    titleWidth: {
      control: 'text',
      description: 'Title width class (e.g., "w-28", "w-32")',
    },
  },
} satisfies Meta<typeof PanelSkeleton>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    showTitle: true,
    titleWidth: 'w-28',
  },
  render: (args) => (
    <PanelSkeleton {...args}>
      <BarListSkeleton items={5} />
    </PanelSkeleton>
  ),
}

export const WithoutTitle: Story = {
  args: {
    showTitle: false,
  },
  render: (args) => (
    <PanelSkeleton {...args}>
      <BarListSkeleton items={5} />
    </PanelSkeleton>
  ),
}

export const WithBarList: Story = {
  render: () => (
    <PanelSkeleton showTitle={true}>
      <BarListSkeleton items={5} showIcon={true} />
    </PanelSkeleton>
  ),
}

export const WithChart: Story = {
  render: () => (
    <PanelSkeleton showTitle={true}>
      <ChartSkeleton height={200} showTitle={false} />
    </PanelSkeleton>
  ),
}

export const WithTable: Story = {
  render: () => (
    <PanelSkeleton showTitle={true}>
      <TableSkeleton rows={5} columns={4} showHeader={true} />
    </PanelSkeleton>
  ),
}

export const WithMetrics: Story = {
  render: () => (
    <PanelSkeleton showTitle={true}>
      <MetricsSkeleton count={4} columns={2} />
    </PanelSkeleton>
  ),
}

export const CustomTitleWidth: Story = {
  render: () => (
    <div className="space-y-4">
      <PanelSkeleton showTitle={true} titleWidth="w-20">
        <BarListSkeleton items={3} />
      </PanelSkeleton>
      <PanelSkeleton showTitle={true} titleWidth="w-40">
        <BarListSkeleton items={3} />
      </PanelSkeleton>
    </div>
  ),
}

export const AllCompositions: Story = {
  render: () => (
    <div className="grid grid-cols-2 gap-4">
      <PanelSkeleton showTitle={true}>
        <MetricsSkeleton count={4} columns={2} />
      </PanelSkeleton>
      <PanelSkeleton showTitle={true}>
        <BarListSkeleton items={5} showIcon={true} />
      </PanelSkeleton>
      <PanelSkeleton showTitle={true}>
        <ChartSkeleton height={150} showTitle={false} />
      </PanelSkeleton>
      <PanelSkeleton showTitle={true}>
        <TableSkeleton rows={4} columns={3} showHeader={true} />
      </PanelSkeleton>
    </div>
  ),
}

export const DashboardLayout: Story = {
  render: () => (
    <div className="space-y-4">
      {/* Metrics row */}
      <PanelSkeleton showTitle={false}>
        <MetricsSkeleton count={4} columns={4} />
      </PanelSkeleton>

      {/* Charts row */}
      <div className="grid grid-cols-2 gap-4">
        <PanelSkeleton showTitle={true}>
          <ChartSkeleton height={200} showTitle={false} />
        </PanelSkeleton>
        <PanelSkeleton showTitle={true}>
          <ChartSkeleton height={200} showTitle={false} />
        </PanelSkeleton>
      </div>

      {/* Lists row */}
      <div className="grid grid-cols-3 gap-4">
        <PanelSkeleton showTitle={true}>
          <BarListSkeleton items={5} showIcon={true} />
        </PanelSkeleton>
        <PanelSkeleton showTitle={true}>
          <BarListSkeleton items={5} showIcon={true} />
        </PanelSkeleton>
        <PanelSkeleton showTitle={true}>
          <TableSkeleton rows={5} columns={2} showHeader={false} />
        </PanelSkeleton>
      </div>
    </div>
  ),
}
