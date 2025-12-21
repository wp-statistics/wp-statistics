import type { Meta, StoryObj } from '@storybook/react'
import { expect, within } from 'storybook/test'

import { HorizontalBar } from './horizontal-bar'

const meta = {
  title: 'Custom/HorizontalBar',
  component: HorizontalBar,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
  argTypes: {
    isNegative: {
      control: 'boolean',
    },
  },
  decorators: [
    (Story) => (
      <div style={{ width: '35vw' }}>
        <Story />
      </div>
    ),
  ],
} satisfies Meta<typeof HorizontalBar>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    icon: '🇬🇪',
    label: 'Georgia',
    value: '1K',
    percentage: '15',
    isNegative: true,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify bar displays correct content
    await expect(canvas.getByText('Georgia')).toBeInTheDocument()
    await expect(canvas.getByText('1K')).toBeInTheDocument()
    await expect(canvas.getByText('15%')).toBeInTheDocument()
  },
}

export const WithTooltip: Story = {
  args: {
    icon: '🇫🇷',
    label: 'France',
    value: '1016',
    percentage: '45',
    isNegative: false,
    tooltipTitle: 'Oct 29, 2025 vs Sep 29, 2025 vs. Oct 29, 2025 vs Sep 29, 2025',
    tooltipSubtitle: 'Top Entry Page',
  },
}

export const HighPercentage: Story = {
  args: {
    icon: '🇬🇧',
    label: 'United Kingdom',
    value: '7K',
    percentage: '45',
    isNegative: false,
    tooltipTitle: 'Oct 29, 2025 vs Sep 29, 2025',
    tooltipSubtitle: 'Top Entry Page',
  },
}

export const MediumPercentage: Story = {
  args: {
    icon: '🇳🇱',
    label: 'Netherlands',
    value: '5K',
    percentage: '34',
    isNegative: false,
  },
}

export const LowPercentage: Story = {
  args: {
    icon: '🇩🇪',
    label: 'Germany',
    value: '2K',
    percentage: '20',
    isNegative: false,
  },
}

export const NegativeTrend: Story = {
  args: {
    icon: '🇦🇺',
    label: 'Australia',
    value: '2.1K',
    percentage: '12',
    isNegative: true,
    tooltipTitle: 'Oct 29, 2025 vs Sep 29, 2025',
    tooltipSubtitle: 'Declining Traffic',
  },
}
