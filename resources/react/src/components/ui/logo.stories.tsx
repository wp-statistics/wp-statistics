import type { Meta, StoryObj } from '@storybook/react'

import { Logo } from './logo'

const meta = {
  title: 'UI/Logo',
  component: Logo,
  parameters: {
    layout: 'centered',
    backgrounds: {
      default: 'dark',
      values: [
        { name: 'dark', value: '#1a1a2e' },
        { name: 'primary', value: 'hsl(var(--primary))' },
      ],
    },
  },
  tags: ['autodocs'],
} satisfies Meta<typeof Logo>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {}

export const OnDarkBackground: Story = {
  parameters: {
    backgrounds: { default: 'dark' },
  },
}

export const OnPrimaryBackground: Story = {
  parameters: {
    backgrounds: { default: 'primary' },
  },
}

export const WithCustomSize: Story = {
  render: () => (
    <div className="flex flex-col items-center gap-4">
      <div className="scale-50">
        <Logo />
      </div>
      <Logo />
      <div className="scale-150">
        <Logo />
      </div>
    </div>
  ),
  parameters: {
    backgrounds: { default: 'dark' },
  },
}
