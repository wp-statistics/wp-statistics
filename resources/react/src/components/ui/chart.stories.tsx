import type { Meta, StoryObj } from '@storybook/react'
import { Bar, BarChart, Line, LineChart, XAxis, YAxis } from 'recharts'

import { type ChartConfig,ChartContainer, ChartLegend, ChartLegendContent, ChartTooltip, ChartTooltipContent } from './chart'

const meta = {
  title: 'UI/Chart',
  component: ChartContainer,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
} satisfies Meta<typeof ChartContainer>

export default meta
type Story = StoryObj<typeof meta>

const sampleData = [
  { month: 'Jan', visitors: 186, pageViews: 305 },
  { month: 'Feb', visitors: 305, pageViews: 420 },
  { month: 'Mar', visitors: 237, pageViews: 380 },
  { month: 'Apr', visitors: 173, pageViews: 290 },
  { month: 'May', visitors: 209, pageViews: 350 },
  { month: 'Jun', visitors: 214, pageViews: 380 },
]

const chartConfig: ChartConfig = {
  visitors: {
    label: 'Visitors',
    color: 'hsl(var(--chart-1))',
  },
  pageViews: {
    label: 'Page Views',
    color: 'hsl(var(--chart-2))',
  },
}

export const BarChartExample: Story = {
  render: () => (
    <ChartContainer config={chartConfig} className="h-[300px] w-[500px]">
      <BarChart data={sampleData}>
        <XAxis dataKey="month" />
        <YAxis />
        <ChartTooltip content={<ChartTooltipContent />} />
        <ChartLegend content={<ChartLegendContent />} />
        <Bar dataKey="visitors" fill="var(--color-visitors)" radius={4} />
        <Bar dataKey="pageViews" fill="var(--color-pageViews)" radius={4} />
      </BarChart>
    </ChartContainer>
  ),
}

export const LineChartExample: Story = {
  render: () => (
    <ChartContainer config={chartConfig} className="h-[300px] w-[500px]">
      <LineChart data={sampleData}>
        <XAxis dataKey="month" />
        <YAxis />
        <ChartTooltip content={<ChartTooltipContent />} />
        <ChartLegend content={<ChartLegendContent />} />
        <Line type="monotone" dataKey="visitors" stroke="var(--color-visitors)" strokeWidth={2} />
        <Line type="monotone" dataKey="pageViews" stroke="var(--color-pageViews)" strokeWidth={2} />
      </LineChart>
    </ChartContainer>
  ),
}

export const SingleBarChart: Story = {
  render: () => {
    const singleConfig: ChartConfig = {
      visitors: {
        label: 'Visitors',
        color: 'hsl(var(--chart-1))',
      },
    }
    return (
      <ChartContainer config={singleConfig} className="h-[300px] w-[500px]">
        <BarChart data={sampleData}>
          <XAxis dataKey="month" />
          <YAxis />
          <ChartTooltip content={<ChartTooltipContent />} />
          <Bar dataKey="visitors" fill="var(--color-visitors)" radius={4} />
        </BarChart>
      </ChartContainer>
    )
  },
}

export const WithDashedIndicator: Story = {
  render: () => (
    <ChartContainer config={chartConfig} className="h-[300px] w-[500px]">
      <LineChart data={sampleData}>
        <XAxis dataKey="month" />
        <YAxis />
        <ChartTooltip content={<ChartTooltipContent indicator="dashed" />} />
        <Line type="monotone" dataKey="visitors" stroke="var(--color-visitors)" strokeWidth={2} />
      </LineChart>
    </ChartContainer>
  ),
}
