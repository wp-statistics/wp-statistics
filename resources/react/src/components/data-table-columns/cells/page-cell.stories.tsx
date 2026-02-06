import type { Meta, StoryObj } from '@storybook/react'
import { expect, userEvent, within } from 'storybook/test'

import { PageCell } from './page-cell'

const meta = {
  title: 'DataTable/Cells/PageCell',
  component: PageCell,
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
} satisfies Meta<typeof PageCell>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    data: {
      title: 'Home Page',
      url: 'https://example.com/',
    },
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify title is displayed
    await expect(canvas.getByText('Home Page')).toBeInTheDocument()
  },
}

export const LongTitle: Story = {
  args: {
    data: {
      title: 'This is a very long page title that should be truncated',
      url: 'https://example.com/very-long-page-url',
    },
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify truncated title is displayed (default maxLength is 28)
    await expect(canvas.getByText('This is a very long page ...')).toBeInTheDocument()
  },
}

export const WithTooltip: Story = {
  args: {
    data: {
      title: 'Product Details',
      url: 'https://example.com/products/123',
    },
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    const trigger = canvas.getByText('Product Details')
    await userEvent.hover(trigger)

    // Tooltip should show the full URL
    const tooltip = await within(document.body).findByRole('tooltip')
    await expect(tooltip).toHaveTextContent('https://example.com/products/123')

    await userEvent.unhover(trigger)
  },
}

export const CustomMaxLength: Story = {
  args: {
    data: {
      title: 'Short Title Page',
      url: 'https://example.com/short',
    },
    maxLength: 10,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify truncation with custom max length
    await expect(canvas.getByText('Short T...')).toBeInTheDocument()
  },
}

export const ShortTitle: Story = {
  args: {
    data: {
      title: 'Blog',
      url: 'https://example.com/blog',
    },
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Short titles should not be truncated
    await expect(canvas.getByText('Blog')).toBeInTheDocument()
  },
}
