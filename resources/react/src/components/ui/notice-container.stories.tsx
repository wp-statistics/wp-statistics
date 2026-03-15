import type { Meta, StoryObj } from '@storybook/react'
import { expect, fn, userEvent, within } from 'storybook/test'

import { NoticeBanner } from './notice-banner'

// Note: NoticeContainer itself depends on WordPress.getInstance() which is not available in Storybook.
// Instead, we showcase the underlying NoticeBanner component which NoticeContainer renders.

const meta = {
  title: 'UI/NoticeContainer',
  component: NoticeBanner,
  parameters: {
    layout: 'padded',
  },
  tags: ['autodocs'],
  argTypes: {
    type: {
      control: 'select',
      options: ['info', 'warning', 'error', 'success'],
      description: 'Type of notice which determines the styling',
    },
    message: {
      control: 'text',
      description: 'Main message text',
    },
    dismissible: {
      control: 'boolean',
      description: 'Whether the notice can be dismissed',
    },
  },
  args: {
    onDismiss: fn(),
  },
} satisfies Meta<typeof NoticeBanner>

export default meta
type Story = StoryObj<typeof meta>

export const Info: Story = {
  args: {
    id: 'info-notice',
    type: 'info',
    message: 'This is an informational notice about your analytics setup.',
    dismissible: true,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText(/informational notice/i)).toBeInTheDocument()
  },
}

export const Warning: Story = {
  args: {
    id: 'warning-notice',
    type: 'warning',
    message: 'Your tracking code might not be installed correctly.',
    dismissible: true,
  },
}

export const Error: Story = {
  args: {
    id: 'error-notice',
    type: 'error',
    message: 'Failed to connect to the database. Please check your configuration.',
    dismissible: true,
  },
}

export const Success: Story = {
  args: {
    id: 'success-notice',
    type: 'success',
    message: 'Settings saved successfully!',
    dismissible: true,
  },
}

export const WithAction: Story = {
  args: {
    id: 'action-notice',
    type: 'info',
    message: 'New features are available in the premium version.',
    actionUrl: '#',
    actionLabel: 'Upgrade Now',
    dismissible: true,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByRole('link', { name: /upgrade now/i })).toBeInTheDocument()
  },
}

export const WithHelpLink: Story = {
  args: {
    id: 'help-notice',
    type: 'warning',
    message: 'Some visitors may not be tracked due to ad blockers.',
    helpUrl: '#',
    dismissible: true,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByRole('link', { name: /learn more/i })).toBeInTheDocument()
  },
}

export const NonDismissible: Story = {
  args: {
    id: 'permanent-notice',
    type: 'error',
    message: 'Critical: Your license has expired. Please renew to continue receiving updates.',
    dismissible: false,
    actionUrl: '#',
    actionLabel: 'Renew License',
  },
}

export const Dismissible: Story = {
  args: {
    id: 'dismissible-notice',
    type: 'info',
    message: 'Click the X button to dismiss this notice.',
    dismissible: true,
  },
  play: async ({ canvasElement, args }) => {
    const canvas = within(canvasElement)
    const dismissButton = canvas.getByRole('button')
    await expect(dismissButton).toBeInTheDocument()
    await userEvent.click(dismissButton)
    await expect(args.onDismiss).toHaveBeenCalledWith('dismissible-notice')
  },
}

export const AllTypes: Story = {
  render: () => (
    <div className="flex flex-col gap-3">
      <NoticeBanner
        id="info-1"
        type="info"
        message="Info: This is an informational message."
        dismissible={true}
        onDismiss={fn()}
      />
      <NoticeBanner
        id="success-1"
        type="success"
        message="Success: Your changes have been saved."
        dismissible={true}
        onDismiss={fn()}
      />
      <NoticeBanner
        id="warning-1"
        type="warning"
        message="Warning: Some features may not work as expected."
        dismissible={true}
        onDismiss={fn()}
      />
      <NoticeBanner
        id="error-1"
        type="error"
        message="Error: Something went wrong. Please try again."
        dismissible={true}
        onDismiss={fn()}
      />
    </div>
  ),
}

export const FullFeatured: Story = {
  render: () => (
    <div className="flex flex-col gap-3">
      <NoticeBanner
        id="full-1"
        type="info"
        message="Premium features available! Upgrade to unlock advanced analytics."
        actionUrl="#"
        actionLabel="Upgrade Now"
        helpUrl="#"
        dismissible={true}
        onDismiss={fn()}
      />
      <NoticeBanner
        id="full-2"
        type="warning"
        message="Your data export is ready. Download it before it expires."
        actionUrl="#"
        actionLabel="Download"
        dismissible={true}
        onDismiss={fn()}
      />
    </div>
  ),
}

export const LongMessage: Story = {
  args: {
    id: 'long-notice',
    type: 'info',
    message:
      'This is a much longer notice message that demonstrates how the component handles text wrapping. It contains important information that the user should read carefully before taking any action. The notice should display properly regardless of its length.',
    dismissible: true,
    actionUrl: '#',
    actionLabel: 'Learn More',
  },
}
