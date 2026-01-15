import type { Meta, StoryObj } from '@storybook/react'
import { expect, fn, userEvent, within } from 'storybook/test'

import { ApiError, ApiErrorInline } from './api-error'

const meta = {
  title: 'UI/ApiError',
  component: ApiError,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
  argTypes: {
    size: {
      control: 'select',
      options: ['sm', 'md', 'lg'],
      description: 'Size variant of the error display',
    },
    title: {
      control: 'text',
      description: 'Error title text',
    },
    error: {
      control: 'text',
      description: 'Error message or Error object',
    },
  },
  args: {
    onRetry: fn(),
  },
} satisfies Meta<typeof ApiError>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    error: 'Failed to fetch data from the server',
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('Failed to load data')).toBeInTheDocument()
    await expect(canvas.getByText('Failed to fetch data from the server')).toBeInTheDocument()
  },
}

export const WithRetry: Story = {
  args: {
    error: 'Network connection failed',
    onRetry: fn(),
  },
  play: async ({ canvasElement, args }) => {
    const canvas = within(canvasElement)
    const retryButton = canvas.getByRole('button', { name: /try again/i })
    await expect(retryButton).toBeInTheDocument()
    await userEvent.click(retryButton)
    await expect(args.onRetry).toHaveBeenCalledTimes(1)
  },
}

export const WithCustomTitle: Story = {
  args: {
    title: 'Connection Error',
    error: 'Unable to reach the server. Please check your internet connection.',
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('Connection Error')).toBeInTheDocument()
  },
}

export const WithErrorObject: Story = {
  args: {
    error: new Error('API rate limit exceeded'),
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('API rate limit exceeded')).toBeInTheDocument()
  },
}

export const Small: Story = {
  args: {
    size: 'sm',
    error: 'Small error message',
  },
}

export const Medium: Story = {
  args: {
    size: 'md',
    error: 'Medium error message',
  },
}

export const Large: Story = {
  args: {
    size: 'lg',
    error: 'Large error message',
  },
}

export const SizeComparison: Story = {
  render: () => (
    <div className="flex flex-col gap-8">
      <div>
        <p className="text-xs text-muted-foreground mb-2">Small</p>
        <ApiError size="sm" error="Small size error" />
      </div>
      <div>
        <p className="text-xs text-muted-foreground mb-2">Medium (default)</p>
        <ApiError size="md" error="Medium size error" />
      </div>
      <div>
        <p className="text-xs text-muted-foreground mb-2">Large</p>
        <ApiError size="lg" error="Large size error" />
      </div>
    </div>
  ),
}

export const WithoutError: Story = {
  args: {
    title: 'Something went wrong',
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('Something went wrong')).toBeInTheDocument()
  },
}

// Inline variant stories
export const Inline: Story = {
  render: () => (
    <div className="p-4 border rounded w-[400px]">
      <p className="mb-2 text-sm">Some content above the error</p>
      <ApiErrorInline error="Something went wrong" onRetry={fn()} />
      <p className="mt-2 text-sm">Some content below the error</p>
    </div>
  ),
}

export const InlineWithoutRetry: Story = {
  render: () => (
    <div className="p-4 border rounded w-[400px]">
      <ApiErrorInline error="Unable to load comments" />
    </div>
  ),
}
