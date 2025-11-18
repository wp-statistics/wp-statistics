import type { Meta, StoryObj } from '@storybook/react'
import { LineChart } from './line-chart'
import type { LineChartDataPoint, LineChartMetric } from './line-chart'

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
  },
  {
    key: 'views',
    label: 'Views',
    enabled: true,
  },
]

const meta = {
  title: 'Components/LineChart',
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
    showLegend: {
      control: 'boolean',
      description: 'Show/hide legend',
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
  },
}

export const WithTitle: Story = {
  args: {
    data: generateChartData(),
    metrics: sampleMetrics,
    title: 'Traffic Trends',
  },
}

export const WithoutPreviousPeriod: Story = {
  args: {
    data: generateChartData(),
    metrics: sampleMetrics,
    title: 'Traffic Trends',
    showPreviousPeriod: false,
  },
}

export const WithoutLegend: Story = {
  args: {
    data: generateChartData(),
    metrics: sampleMetrics,
    title: 'Traffic Trends',
    showLegend: false,
  },
}

export const WeeklyView: Story = {
  args: {
    data: generateChartData(),
    metrics: sampleMetrics,
    title: 'Traffic Trends',
    timeframe: 'Weekly',
  },
}

export const MonthlyView: Story = {
  args: {
    data: generateChartData(),
    metrics: sampleMetrics,
    title: 'Traffic Trends',
    timeframe: 'Monthly',
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
      { key: 'visitors', label: 'Visitors' },
      { key: 'views', label: 'Views' },
      { key: 'pageviews', label: 'Page Views' },
      { key: 'bounceRate', label: 'Bounce Rate' },
    ],
    title: 'Complete Analytics',
  },
}

export const CustomColors: Story = {
  args: {
    data: generateChartData(),
    metrics: [
      { key: 'visitors', label: 'Visitors', color: '#3B82F6' },
      { key: 'views', label: 'Views', color: '#10B981' },
    ],
    title: 'Traffic Trends',
  },
}
