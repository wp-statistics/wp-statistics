import type { Meta, StoryObj } from '@storybook/react'
import { Bell,Shield, Stethoscope, Zap } from 'lucide-react'

import { NoticeBanner } from './notice-banner'

const meta = {
  title: 'UI/NoticeBanner',
  component: NoticeBanner,
  parameters: {
    layout: 'padded',
  },
  tags: ['autodocs'],
  argTypes: {
    type: {
      control: 'select',
      options: ['info', 'warning', 'error', 'success', 'neutral'],
      description: 'Visual style variant',
    },
    dismissible: {
      control: 'boolean',
      description: 'Whether the notice can be dismissed',
    },
    title: {
      control: 'text',
      description: 'Optional title above the message',
    },
    message: {
      control: 'text',
      description: 'The notice message',
    },
  },
} satisfies Meta<typeof NoticeBanner>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    id: 'default-notice',
    message: 'This is an informational notice.',
    type: 'info',
    dismissible: true,
  },
}

export const Error: Story = {
  args: {
    id: 'error-notice',
    message: '1 critical issue detected that may affect WP Statistics functionality.',
    type: 'error',
    actionUrl: '#/tools/diagnostics',
    actionLabel: 'View Diagnostics',
    helpUrl: 'https://wp-statistics.com/resources/diagnostics/',
    dismissible: true,
  },
}

export const Warning: Story = {
  args: {
    id: 'warning-notice',
    message: '2 configuration warnings detected that may affect performance.',
    type: 'warning',
    actionUrl: '#/tools/diagnostics',
    actionLabel: 'View Details',
    dismissible: true,
  },
}

export const Success: Story = {
  args: {
    id: 'success-notice',
    message: 'All diagnostic checks passed successfully.',
    type: 'success',
    dismissible: true,
  },
}

export const Info: Story = {
  args: {
    id: 'info-notice',
    message: 'New feature available: Custom events tracking is now supported.',
    type: 'info',
    actionUrl: '#/settings/tracking',
    actionLabel: 'Learn More',
    dismissible: true,
  },
}

export const Neutral: Story = {
  args: {
    title: 'System Diagnostics',
    message:
      'These checks help identify potential issues that may affect WP Statistics functionality. Lightweight checks run automatically, while others require manual execution to avoid performance impact.',
    type: 'neutral',
    icon: Stethoscope,
    dismissible: false,
  },
}

export const WithTitle: Story = {
  args: {
    id: 'titled-notice',
    title: 'Performance Alert',
    message: 'Your site is experiencing higher than normal traffic. Consider enabling caching.',
    type: 'warning',
    actionUrl: '#/settings/performance',
    actionLabel: 'Configure Caching',
    dismissible: true,
  },
}

export const WithCustomIcon: Story = {
  args: {
    title: 'Security Check',
    message: 'All security configurations are properly set up.',
    type: 'success',
    icon: Shield,
    dismissible: false,
  },
}

export const NonDismissible: Story = {
  args: {
    message: 'This notice cannot be dismissed and will remain visible.',
    type: 'info',
    dismissible: false,
  },
}

export const AllTypes: Story = {
  render: () => (
    <div className="flex flex-col gap-4">
      <NoticeBanner
        id="info"
        message="This is an informational notice with helpful tips."
        type="info"
        actionUrl="#"
        actionLabel="Learn More"
        dismissible
      />
      <NoticeBanner
        id="warning"
        message="Warning: Some configuration may need attention."
        type="warning"
        actionUrl="#"
        actionLabel="Review Settings"
        dismissible
      />
      <NoticeBanner
        id="error"
        message="Error: Critical issue detected that requires immediate attention."
        type="error"
        actionUrl="#"
        actionLabel="Fix Now"
        helpUrl="#"
        dismissible
      />
      <NoticeBanner
        id="success"
        message="Success: All operations completed without issues."
        type="success"
        dismissible
      />
      <NoticeBanner
        title="Informational Box"
        message="This is a neutral informational box used for contextual help and descriptions."
        type="neutral"
        icon={Bell}
        dismissible={false}
      />
    </div>
  ),
}

export const RealWorldExamples: Story = {
  render: () => (
    <div className="flex flex-col gap-4">
      <NoticeBanner
        id="diagnostic"
        message="1 critical issue and 2 warnings detected that may affect WP Statistics functionality."
        type="error"
        actionUrl="#/tools/diagnostics"
        actionLabel="View Diagnostics"
        helpUrl="https://wp-statistics.com/resources/diagnostics/"
        dismissible
      />
      <NoticeBanner
        title="System Diagnostics"
        message="These checks help identify potential issues that may affect WP Statistics functionality. Lightweight checks run automatically, while others require manual execution to avoid performance impact."
        type="neutral"
        icon={Stethoscope}
        dismissible={false}
      />
      <NoticeBanner
        id="update"
        title="Update Available"
        message="WP Statistics Pro 2.0 is now available with new features and improvements."
        type="info"
        icon={Zap}
        actionUrl="#"
        actionLabel="Update Now"
        dismissible
      />
    </div>
  ),
}
