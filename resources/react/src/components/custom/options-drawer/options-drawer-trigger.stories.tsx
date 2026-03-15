import type { Meta, StoryObj } from '@storybook/react'
import { expect, fn, userEvent, within } from 'storybook/test'

import { OptionsDrawerTrigger } from './options-drawer-trigger'

const meta = {
  title: 'Custom/OptionsDrawer/Trigger',
  component: OptionsDrawerTrigger,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
  argTypes: {
    isActive: {
      control: 'boolean',
      description: 'Whether the trigger shows active state (e.g., when filters are applied)',
    },
  },
  args: {
    onClick: fn(),
  },
} satisfies Meta<typeof OptionsDrawerTrigger>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {},
  play: async ({ canvasElement, args }) => {
    const canvas = within(canvasElement)
    const button = canvas.getByRole('button')
    await expect(button).toBeInTheDocument()
    await expect(canvas.getByText('Options')).toBeInTheDocument()
    await userEvent.click(button)
    await expect(args.onClick).toHaveBeenCalledTimes(1)
  },
}

export const Active: Story = {
  args: {
    isActive: true,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    const button = canvas.getByRole('button')
    // Active state should have different styling
    await expect(button).toHaveClass('border-indigo-200')
  },
}

export const Inactive: Story = {
  args: {
    isActive: false,
  },
}

export const Comparison: Story = {
  render: () => (
    <div className="flex items-center gap-4">
      <div className="text-center">
        <OptionsDrawerTrigger onClick={fn()} isActive={false} />
        <p className="text-xs text-muted-foreground mt-2">Inactive</p>
      </div>
      <div className="text-center">
        <OptionsDrawerTrigger onClick={fn()} isActive={true} />
        <p className="text-xs text-muted-foreground mt-2">Active</p>
      </div>
    </div>
  ),
}

export const WithCustomClassName: Story = {
  args: {
    className: 'shadow-md',
  },
}

export const InToolbar: Story = {
  render: () => (
    <div className="flex items-center gap-2 p-4 border rounded-lg bg-neutral-50">
      <span className="text-sm text-muted-foreground">Toolbar:</span>
      <OptionsDrawerTrigger onClick={fn()} />
    </div>
  ),
}
