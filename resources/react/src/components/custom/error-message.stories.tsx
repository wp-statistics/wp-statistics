import type { Meta, StoryObj } from '@storybook/react'
import { expect, within } from 'storybook/test'

import { ErrorMessage } from './error-message'

const meta = {
  title: 'Custom/ErrorMessage',
  component: ErrorMessage,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
  argTypes: {
    message: {
      control: 'text',
      description: 'The error message to display',
    },
    showIcon: {
      control: 'boolean',
      description: 'Whether to show an icon',
    },
  },
} satisfies Meta<typeof ErrorMessage>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    message: 'Something went wrong',
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('Something went wrong')).toBeInTheDocument()
  },
}

export const WithIcon: Story = {
  args: {
    message: 'Failed to load data',
    showIcon: true,
  },
}

export const WithoutIcon: Story = {
  args: {
    message: 'An error occurred',
    showIcon: false,
  },
}

export const LongMessage: Story = {
  args: {
    message: 'The request could not be completed. Please check your network connection and try again.',
    showIcon: true,
  },
}

export const InFormContext: Story = {
  render: () => (
    <div className="w-80 space-y-4 p-4 border rounded-lg">
      <div className="space-y-2">
        <label className="text-sm font-medium" htmlFor="email">
          Email
        </label>
        <input
          id="email"
          type="email"
          className="w-full px-3 py-2 border border-destructive rounded-md text-sm"
          defaultValue="invalid-email"
        />
        <ErrorMessage message="Please enter a valid email address" showIcon />
      </div>
      <div className="space-y-2">
        <label className="text-sm font-medium" htmlFor="password">
          Password
        </label>
        <input id="password" type="password" className="w-full px-3 py-2 border rounded-md text-sm" />
      </div>
    </div>
  ),
}

export const MultipleErrors: Story = {
  render: () => (
    <div className="w-80 space-y-2 p-4 border rounded-lg">
      <p className="text-sm font-medium">Please fix the following errors:</p>
      <ErrorMessage message="Email is required" showIcon />
      <ErrorMessage message="Password must be at least 8 characters" showIcon />
      <ErrorMessage message="Please accept the terms and conditions" showIcon />
    </div>
  ),
}

export const Comparison: Story = {
  render: () => (
    <div className="space-y-4">
      <div className="flex items-center gap-4">
        <span className="w-24 text-xs text-muted-foreground">Without icon:</span>
        <ErrorMessage message="Error message" />
      </div>
      <div className="flex items-center gap-4">
        <span className="w-24 text-xs text-muted-foreground">With icon:</span>
        <ErrorMessage message="Error message" showIcon />
      </div>
    </div>
  ),
}

export const WithCustomClassName: Story = {
  args: {
    message: 'Custom styled error',
    showIcon: true,
    className: 'text-lg font-semibold',
  },
}
