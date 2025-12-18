import type { Meta, StoryObj } from '@storybook/react'
import { expect, userEvent, within } from '@storybook/test'
import { Metrics } from './metrics'
import type { MetricItem } from './metrics'

// Google Analytics Icon component for demo
const GoogleIcon = () => (
  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path
      d="M22.5 12.5c0-1.58-.875-2.95-2.148-3.6.154-.435.238-.905.238-1.4 0-2.21-1.71-3.998-3.818-3.998-.47 0-.92.084-1.336.25C14.818 2.415 13.51 1.5 12 1.5c-1.51 0-2.818.915-3.437 2.25-.415-.165-.866-.25-1.336-.25-2.11 0-3.818 1.79-3.818 4 0 .494.083.964.237 1.403-1.272.65-2.147 2.018-2.147 3.597 0 1.39.69 2.61 1.73 3.35-.04.29-.06.59-.06.9 0 2.21 1.71 4 3.818 4 .47 0 .92-.086 1.335-.25.62 1.334 1.926 2.25 3.437 2.25 1.51 0 2.817-.916 3.437-2.25.415.163.865.248 1.336.248 2.11 0 3.818-1.79 3.818-4 0-.31-.02-.61-.058-.897 1.04-.74 1.73-1.96 1.73-3.353z"
      fill="#F9AB00"
    />
    <path
      d="M12 3.735c1.035 0 1.875 1.09 1.875 2.432 0 1.343-.84 2.433-1.875 2.433s-1.875-1.09-1.875-2.433c0-1.342.84-2.432 1.875-2.432z"
      fill="#E37400"
    />
    <circle cx="7.5" cy="14.424" r="2.5" fill="#E37400" />
    <circle cx="17" cy="14.424" r="2.5" fill="#E37400" />
  </svg>
)

const sampleMetrics: MetricItem[] = [
  {
    label: 'Visitors',
    value: '3,202',
    percentage: '1.2',
    isNegative: true,
    tooltipContent: 'Total number of unique visitors',
  },
  {
    label: 'Views',
    value: '3,940',
    percentage: '1.2',
    isNegative: true,
    icon: <GoogleIcon />,
    tooltipContent: 'Total page views',
  },
  {
    label: 'Exit',
    value: '3,416',
    percentage: '8.3',
    isNegative: false,
    icon: <GoogleIcon />,
    tooltipContent: 'Total exit events',
  },
  {
    label: 'Exit Rate',
    value: '5:56',
    percentage: '1.2',
    isNegative: true,
    icon: <GoogleIcon />,
    tooltipContent: 'Average time users spend on your site before leaving',
  },
  {
    label: 'View',
    value: '86%',
    percentage: '8.3',
    isNegative: false,
    tooltipContent: 'Percentage of page views',
  },
  {
    label: 'Sessions',
    value: '1,30',
    percentage: '1.2',
    isNegative: true,
    icon: <GoogleIcon />,
    tooltipContent: 'Total number of sessions',
  },
  {
    label: 'Average Session Duration',
    value: '45',
    percentage: '1.2',
    isNegative: true,
    icon: <GoogleIcon />,
    tooltipContent: 'Average duration of a user session on your site',
  },
  {
    label: 'Views Per Session',
    value: '$2,050',
    percentage: '8.3',
    isNegative: false,
    tooltipContent: 'Average views per session',
  },
  {
    label: 'Refunds',
    value: '359.1K',
    percentage: '8.3',
    isNegative: false,
    icon: <GoogleIcon />,
    tooltipContent: 'Total refunds processed',
  },
]

const meta = {
  title: 'Custom/Metrics',
  component: Metrics,
  parameters: {
    layout: 'padded',
  },
  tags: ['autodocs'],
  argTypes: {
    columns: {
      control: 'select',
      options: [1, 2, 3, 4, 6, 12],
      description: 'Number of columns in the grid',
    },
  },
} satisfies Meta<typeof Metrics>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    metrics: sampleMetrics.slice(0, 6),
    columns: 3,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify metrics are displayed
    await expect(canvas.getByText('Visitors')).toBeInTheDocument()
    await expect(canvas.getByText('3,202')).toBeInTheDocument()
    await expect(canvas.getByText('Views')).toBeInTheDocument()
    await expect(canvas.getByText('3,940')).toBeInTheDocument()

    // Verify percentage indicators exist
    await expect(canvas.getByText('1.2%')).toBeInTheDocument()
  },
}

export const ExactDesign: Story = {
  name: 'Exact Design (9 Metrics)',
  args: {
    metrics: sampleMetrics,
    columns: 3,
  },
}

export const TwoColumns: Story = {
  args: {
    metrics: sampleMetrics.slice(0, 6),
    columns: 2,
  },
}

export const FourColumns: Story = {
  args: {
    metrics: sampleMetrics.slice(0, 8),
    columns: 4,
  },
}

export const SingleColumn: Story = {
  args: {
    metrics: sampleMetrics.slice(0, 4),
    columns: 1,
  },
}

export const WithoutIcons: Story = {
  args: {
    metrics: [
      {
        label: 'Visitors',
        value: '3,202',
        percentage: '1.2',
        isNegative: true,
      },
      {
        label: 'Views',
        value: '3,940',
        percentage: '1.2',
        isNegative: true,
      },
      {
        label: 'Exit',
        value: '3,416',
        percentage: '8.3',
        isNegative: false,
      },
      {
        label: 'Sessions',
        value: '1,30',
        percentage: '1.2',
        isNegative: true,
      },
      {
        label: 'Bounce Rate',
        value: '45%',
        percentage: '2.1',
        isNegative: false,
      },
      {
        label: 'Page Views',
        value: '$2,050',
        percentage: '8.3',
        isNegative: false,
      },
    ],
    columns: 3,
  },
}

export const WithoutPercentages: Story = {
  args: {
    metrics: [
      {
        label: 'Visitors',
        value: '3,202',
        icon: <GoogleIcon />,
      },
      {
        label: 'Views',
        value: '3,940',
        icon: <GoogleIcon />,
      },
      {
        label: 'Sessions',
        value: '1,30',
        icon: <GoogleIcon />,
      },
      {
        label: 'Bounce Rate',
        value: '45%',
      },
      {
        label: 'Page Views',
        value: '$2,050',
      },
      {
        label: 'Avg Duration',
        value: '5:56',
      },
    ],
    columns: 3,
  },
}

export const MinimalMetrics: Story = {
  args: {
    metrics: [
      {
        label: 'Visitors',
        value: '3,202',
      },
      {
        label: 'Views',
        value: '3,940',
      },
      {
        label: 'Sessions',
        value: '1,30',
      },
    ],
    columns: 3,
  },
}

export const MaximumMetrics: Story = {
  name: 'Maximum (12 Metrics)',
  args: {
    metrics: [
      ...sampleMetrics,
      {
        label: 'Conversion Rate',
        value: '3.2%',
        percentage: '0.5',
        isNegative: false,
        tooltipContent: 'Percentage of visitors who convert',
      },
      {
        label: 'Revenue',
        value: '$12.5K',
        percentage: '12.3',
        isNegative: false,
        icon: <GoogleIcon />,
        tooltipContent: 'Total revenue generated',
      },
      {
        label: 'Orders',
        value: '245',
        percentage: '5.7',
        isNegative: false,
        tooltipContent: 'Total number of orders',
      },
    ],
    columns: 3,
  },
}

export const LargeValues: Story = {
  args: {
    metrics: [
      {
        label: 'Total Visitors',
        value: '1.2M',
        percentage: '15.3',
        isNegative: false,
        icon: <GoogleIcon />,
      },
      {
        label: 'Page Views',
        value: '5.8M',
        percentage: '22.1',
        isNegative: false,
        icon: <GoogleIcon />,
      },
      {
        label: 'Revenue',
        value: '$458.9K',
        percentage: '18.7',
        isNegative: false,
        icon: <GoogleIcon />,
      },
    ],
    columns: 3,
  },
}

export const MixedStates: Story = {
  args: {
    metrics: [
      {
        label: 'Visitors',
        value: '3,202',
        percentage: '1.2',
        isNegative: true,
      },
      {
        label: 'Views',
        value: '3,940',
        percentage: '8.3',
        isNegative: false,
        icon: <GoogleIcon />,
      },
      {
        label: 'Sessions',
        value: '1,30',
      },
      {
        label: 'Bounce Rate',
        value: '45%',
        percentage: '2.1',
        isNegative: true,
        tooltipContent: 'Percentage of single-page sessions',
      },
      {
        label: 'Page Views',
        value: '$2,050',
        icon: <GoogleIcon />,
      },
      {
        label: 'Conversions',
        value: '89',
        percentage: '12.5',
        isNegative: false,
        icon: <GoogleIcon />,
        tooltipContent: 'Total number of conversions',
      },
    ],
    columns: 3,
  },
}
