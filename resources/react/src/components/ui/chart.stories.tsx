import type { Meta, StoryObj } from '@storybook/react'

import type { LineChartDataPoint, LineChartMetric } from '../custom/line-chart'
import { LineChart } from '../custom/line-chart'

const generateData = (): LineChartDataPoint[] => {
  const data: LineChartDataPoint[] = []
  const startDate = new Date('2025-04-01')
  for (let i = 0; i < 14; i++) {
    const date = new Date(startDate)
    date.setDate(startDate.getDate() + i)
    data.push({
      date: date.toISOString().split('T')[0],
      visitors: Math.floor(Math.random() * 100 + 150),
      visitorsPrevious: Math.floor(Math.random() * 80 + 120),
      views: Math.floor(Math.random() * 200 + 300),
      viewsPrevious: Math.floor(Math.random() * 180 + 250),
    })
  }
  return data
}

const metrics: LineChartMetric[] = [
  { key: 'visitors', label: 'Visitors', value: '2.4K', previousValue: '1.9K' },
  { key: 'views', label: 'Views', value: '5.1K', previousValue: '4.3K' },
]

const meta = {
  title: 'UI/Chart',
  component: LineChart,
  parameters: { layout: 'padded' },
  tags: ['autodocs'],
} satisfies Meta<typeof LineChart>

export default meta
type Story = StoryObj<typeof meta>

export const LineChartExample: Story = {
  args: {
    data: generateData(),
    metrics,
    title: 'Traffic Overview',
    showPreviousPeriod: true,
  },
}

export const BarChartExample: Story = {
  args: {
    data: generateData(),
    metrics: [
      { key: 'visitors', label: 'Visitors', type: 'bar', value: '2.4K' },
      { key: 'views', label: 'Views', value: '5.1K', previousValue: '4.3K' },
    ],
    title: 'Mixed Line + Bar',
    showPreviousPeriod: true,
  },
}

export const SingleMetric: Story = {
  args: {
    data: generateData(),
    metrics: [{ key: 'visitors', label: 'Visitors', value: '2.4K' }],
    title: 'Single Metric',
    showPreviousPeriod: false,
  },
}
