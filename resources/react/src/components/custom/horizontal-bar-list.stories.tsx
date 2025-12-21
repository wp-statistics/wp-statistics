import type { Meta, StoryObj } from '@storybook/react'
import { expect, within } from 'storybook/test'

import { HorizontalBarList } from './horizontal-bar-list'

const meta = {
  title: 'Custom/HorizontalBarList',
  component: HorizontalBarList,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
  decorators: [
    (Story) => (
      <div style={{ width: '50vw', padding: '2rem', backgroundColor: '#f5f5f5' }}>
        <Story />
      </div>
    ),
  ],
} satisfies Meta<typeof HorizontalBarList>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    title: 'Top Referrers',
    items: [
      {
        icon: '🇫🇷',
        label: 'France',
        value: '17K',
        percentage: '8.3',
        isNegative: false,
      },
      {
        icon: '🇬🇧',
        label: 'United Kingdom',
        value: '7K',
        percentage: '45',
        isNegative: false,
      },
      {
        icon: '🇳🇱',
        label: 'Netherlands',
        value: '5K',
        percentage: '34',
        isNegative: false,
      },
      {
        icon: '🇩🇪',
        label: 'Germany',
        value: '2K',
        percentage: '20',
        isNegative: false,
      },
      {
        icon: '🇬🇪',
        label: 'Georgia',
        value: '1K',
        percentage: '15',
        isNegative: true,
      },
    ],
    link: {
      title: 'View Referees',
      action: () => {},
    },
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify title is displayed
    await expect(canvas.getByText('Top Referrers')).toBeInTheDocument()

    // Verify items are displayed
    await expect(canvas.getByText('France')).toBeInTheDocument()
    await expect(canvas.getByText('United Kingdom')).toBeInTheDocument()
    await expect(canvas.getByText('Germany')).toBeInTheDocument()

    // Verify link is displayed
    await expect(canvas.getByText('View Referees')).toBeInTheDocument()
  },
}

export const WithTooltips: Story = {
  args: {
    title: 'Top Entry Pages',
    items: [
      {
        icon: '🇫🇷',
        label: 'France',
        value: '17K',
        percentage: '8.3',
        isNegative: false,
        tooltipTitle: 'Oct 29, 2025 vs Sep 29, 2025',
        tooltipSubtitle: 'Top Entry Page',
      },
      {
        icon: '🇬🇧',
        label: 'United Kingdom',
        value: '7K',
        percentage: '45',
        isNegative: false,
        tooltipTitle: 'Oct 29, 2025 vs Sep 29, 2025',
        tooltipSubtitle: 'Top Entry Page',
      },
      {
        icon: '🇳🇱',
        label: 'Netherlands',
        value: '5K',
        percentage: '34',
        isNegative: false,
        tooltipTitle: 'Oct 29, 2025 vs Sep 29, 2025',
        tooltipSubtitle: 'Top Entry Page',
      },
    ],
    link: {
      title: 'View Entry Pages',
      action: () => {},
    },
  },
}
