import type { Meta, StoryObj } from '@storybook/react'
import { expect, userEvent, within } from 'storybook/test'

import { ReferrerCell } from './referrer-cell'

const meta = {
  title: 'DataTable/Cells/ReferrerCell',
  component: ReferrerCell,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
  decorators: [
    (Story) => (
      <div className="p-4">
        <Story />
      </div>
    ),
  ],
} satisfies Meta<typeof ReferrerCell>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    data: {
      domain: 'google.com',
      category: 'organic search',
    },
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify domain is displayed
    await expect(canvas.getByText('google.com')).toBeInTheDocument()

    // Verify category is displayed (component applies toTitleCase)
    await expect(canvas.getByText('Organic Search')).toBeInTheDocument()
  },
}

export const LongDomain: Story = {
  args: {
    data: {
      domain: 'subdomain.verylongdomainname.com',
      category: 'referral traffic',
    },
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify truncated domain (with ellipsis)
    await expect(canvas.getByText(/subdomain.*….*\.com/)).toBeInTheDocument()

    // Verify category (component applies toTitleCase)
    await expect(canvas.getByText('Referral Traffic')).toBeInTheDocument()
  },
}

export const DirectTraffic: Story = {
  args: {
    data: {
      category: 'direct traffic',
    },
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // No domain link for direct traffic
    await expect(canvas.queryByRole('link')).not.toBeInTheDocument()

    // Category should still be visible (component applies toTitleCase)
    await expect(canvas.getByText('Direct Traffic')).toBeInTheDocument()
  },
}

export const SocialMedia: Story = {
  args: {
    data: {
      domain: 'facebook.com',
      category: 'social media',
    },
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify link exists and is clickable
    const link = canvas.getByRole('link', { name: /facebook/i })
    await expect(link).toHaveAttribute('href', 'https://facebook.com')
    await expect(link).toHaveAttribute('target', '_blank')
  },
}

export const WithTooltip: Story = {
  args: {
    data: {
      domain: 'very-long-subdomain.example.com',
      category: 'referral traffic',
    },
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    const trigger = canvas.getByRole('link')
    await userEvent.hover(trigger)

    // Tooltip should show the full domain
    const tooltip = await within(document.body).findByRole('tooltip')
    await expect(tooltip).toHaveTextContent('very-long-subdomain.example.com')

    await userEvent.unhover(trigger)
  },
}
