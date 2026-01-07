import type { Meta, StoryObj } from '@storybook/react'
import { BarChart3, LineChart, Megaphone, Shield, Zap } from 'lucide-react'

import { AddonPromo } from './addon-promo'

const meta = {
  title: 'Custom/AddonPromo',
  component: AddonPromo,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
  decorators: [
    (Story) => (
      <div className="w-[600px]">
        <Story />
      </div>
    ),
  ],
} satisfies Meta<typeof AddonPromo>

export default meta
type Story = StoryObj<typeof meta>

export const Marketing: Story = {
  args: {
    title: 'Marketing Campaigns',
    description:
      'Track your marketing campaigns with detailed UTM reports. Monitor campaign performance, measure ROI, and optimize your marketing strategy.',
    addonName: 'Marketing',
    learnMoreUrl:
      'https://wp-statistics.com/product/wp-statistics-marketing/?utm_source=plugin&utm_medium=link&utm_campaign=campaigns',
    icon: Megaphone,
  },
}

export const DataPlus: Story = {
  args: {
    title: 'Advanced Data Insights',
    description:
      'Unlock powerful data analysis features including custom reports, advanced filtering, and extended data retention.',
    addonName: 'Data Plus',
    learnMoreUrl: 'https://wp-statistics.com/product/wp-statistics-data-plus/',
    icon: BarChart3,
  },
}

export const RealTimeStats: Story = {
  args: {
    title: 'Real-Time Analytics',
    description:
      'See who is visiting your website right now with live updates. Monitor traffic spikes and visitor behavior in real-time.',
    addonName: 'Real-Time Stats',
    learnMoreUrl: 'https://wp-statistics.com/product/wp-statistics-realtime-stats/',
    icon: Zap,
  },
}

export const AdvancedReporting: Story = {
  args: {
    title: 'Advanced Reporting',
    description:
      'Generate comprehensive reports with scheduled email delivery. Export data in multiple formats and create custom dashboards.',
    addonName: 'Advanced Reporting',
    learnMoreUrl: 'https://wp-statistics.com/product/wp-statistics-advanced-reporting/',
    icon: LineChart,
  },
}

export const CustomIcon: Story = {
  args: {
    title: 'Security Analytics',
    description: 'Monitor security events and detect suspicious activity on your website with advanced threat detection.',
    addonName: 'Security',
    learnMoreUrl: 'https://wp-statistics.com/',
    icon: Shield,
  },
}

export const DefaultIcon: Story = {
  args: {
    title: 'Premium Feature',
    description: 'This is a premium feature that requires an addon. The default Megaphone icon is used when no icon is specified.',
    addonName: 'Premium',
    learnMoreUrl: 'https://wp-statistics.com/',
  },
}
