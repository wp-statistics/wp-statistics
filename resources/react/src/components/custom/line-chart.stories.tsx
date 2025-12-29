import type { Meta, StoryObj } from '@storybook/react'
import { expect, fn, userEvent, within } from 'storybook/test'

import type { LineChartDataPoint, LineChartMetric } from './line-chart'
import { LineChart } from './line-chart'

// Generate sample data for demonstration
const generateChartData = (): LineChartDataPoint[] => {
  const data: LineChartDataPoint[] = []
  const startDate = new Date('2025-04-01')

  for (let i = 0; i < 28; i++) {
    const date = new Date(startDate)
    date.setDate(startDate.getDate() + i)

    data.push({
      date: date.toISOString().split('T')[0],
      visitors: Math.floor(Math.random() * 2 + 3),
      visitorsPrevious: Math.floor(Math.random() * 2 + 2),
      views: Math.floor(Math.random() * 5 + 10),
      viewsPrevious: Math.floor(Math.random() * 5 + 8),
    })
  }

  return data
}

const sampleMetrics: LineChartMetric[] = [
  {
    key: 'visitors',
    label: 'Visitors',
    enabled: true,
    value: '668K',
    previousValue: '590K',
  },
  {
    key: 'views',
    label: 'Views',
    enabled: true,
    value: '705K',
    previousValue: '690K',
  },
]

const meta = {
  title: 'Custom/LineChart',
  component: LineChart,
  parameters: {
    layout: 'padded',
  },
  tags: ['autodocs'],
  argTypes: {
    title: {
      control: 'text',
      description: 'Chart title',
    },
    showPreviousPeriod: {
      control: 'boolean',
      description: 'Show/hide previous period comparison',
    },
    timeframe: {
      control: 'select',
      options: ['Daily', 'Weekly', 'Monthly'],
      description: 'Time period selection',
    },
  },
} satisfies Meta<typeof LineChart>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    data: generateChartData(),
    metrics: sampleMetrics,
    onTimeframeChange: fn(),
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify metric labels are displayed
    await expect(canvas.getByText('Visitors')).toBeInTheDocument()
    await expect(canvas.getByText('Views')).toBeInTheDocument()

    // Verify metric values are displayed
    await expect(canvas.getByText('668K')).toBeInTheDocument()
    await expect(canvas.getByText('705K')).toBeInTheDocument()

    // Verify chart container exists
    const chartContainer = canvasElement.querySelector('.recharts-wrapper')
    await expect(chartContainer).toBeInTheDocument()
  },
}

export const WithTitle: Story = {
  args: {
    data: generateChartData(),
    metrics: sampleMetrics,
    title: 'Traffic Trends',
    onTimeframeChange: fn(),
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify title is displayed
    await expect(canvas.getByText('Traffic Trends')).toBeInTheDocument()
  },
}

export const WithoutPreviousPeriod: Story = {
  args: {
    data: generateChartData(),
    metrics: sampleMetrics,
    title: 'Traffic Trends',
    showPreviousPeriod: false,
    onTimeframeChange: fn(),
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify Previous Period toggle is not checked or hidden
    const previousPeriodToggle = canvas.queryByRole('switch')
    if (previousPeriodToggle) {
      await expect(previousPeriodToggle).not.toBeChecked()
    }
  },
}

export const WithoutLegend: Story = {
  args: {
    data: generateChartData(),
    metrics: sampleMetrics,
    title: 'Traffic Trends',
    onTimeframeChange: fn(),
  },
}

export const WeeklyView: Story = {
  args: {
    data: generateChartData(),
    metrics: sampleMetrics,
    title: 'Traffic Trends',
    timeframe: 'Weekly',
    onTimeframeChange: fn(),
  },
}

export const MonthlyView: Story = {
  args: {
    data: generateChartData(),
    metrics: sampleMetrics,
    title: 'Traffic Trends',
    timeframe: 'Monthly',
    onTimeframeChange: fn(),
  },
}

export const MultipleMetrics: Story = {
  args: {
    data: generateChartData().map((item) => ({
      ...item,
      pageviews: Math.floor(Math.random() * 10 + 15),
      pageviewsPrevious: Math.floor(Math.random() * 10 + 12),
      bounceRate: Math.floor(Math.random() * 20 + 30),
      bounceRatePrevious: Math.floor(Math.random() * 20 + 25),
    })),
    metrics: [
      { key: 'visitors', label: 'Visitors', value: '668K', previousValue: '590K' },
      { key: 'views', label: 'Views', value: '705K', previousValue: '690K' },
      { key: 'pageviews', label: 'Page Views', value: '1.2M', previousValue: '1.1M' },
      { key: 'bounceRate', label: 'Bounce Rate', value: '45%', previousValue: '48%' },
    ],
    title: 'Complete Analytics',
    onTimeframeChange: fn(),
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify all metric labels are displayed
    await expect(canvas.getByText('Visitors')).toBeInTheDocument()
    await expect(canvas.getByText('Views')).toBeInTheDocument()
    await expect(canvas.getByText('Page Views')).toBeInTheDocument()
    await expect(canvas.getByText('Bounce Rate')).toBeInTheDocument()
  },
}

export const CustomColors: Story = {
  args: {
    data: generateChartData(),
    metrics: [
      { key: 'visitors', label: 'Visitors', color: '#3B82F6', value: '668K', previousValue: '590K' },
      { key: 'views', label: 'Views', color: '#10B981', value: '705K', previousValue: '690K' },
    ],
    title: 'Traffic Trends',
    onTimeframeChange: fn(),
  },
}
