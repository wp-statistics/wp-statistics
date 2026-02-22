import type { Meta, StoryObj } from '@storybook/react'
import { expect, within } from 'storybook/test'

import { Progress } from './progress'

const meta = {
  title: 'UI/Progress',
  component: Progress,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
  decorators: [
    (Story) => (
      <div className="w-[400px]">
        <Story />
      </div>
    ),
  ],
  argTypes: {
    value: {
      control: { type: 'range', min: 0, max: 100 },
      description: 'Current progress value',
    },
    max: {
      control: 'number',
      description: 'Maximum value (default: 100)',
    },
  },
} satisfies Meta<typeof Progress>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    value: 50,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    const progressbar = canvas.getByRole('progressbar')
    await expect(progressbar).toHaveAttribute('aria-valuenow', '50')
    await expect(progressbar).toHaveAttribute('aria-valuemin', '0')
    await expect(progressbar).toHaveAttribute('aria-valuemax', '100')
  },
}

export const Empty: Story = {
  args: {
    value: 0,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    const progressbar = canvas.getByRole('progressbar')
    await expect(progressbar).toHaveAttribute('aria-valuenow', '0')
  },
}

export const Quarter: Story = {
  args: {
    value: 25,
  },
}

export const Half: Story = {
  args: {
    value: 50,
  },
}

export const ThreeQuarters: Story = {
  args: {
    value: 75,
  },
}

export const Complete: Story = {
  args: {
    value: 100,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    const progressbar = canvas.getByRole('progressbar')
    await expect(progressbar).toHaveAttribute('aria-valuenow', '100')
  },
}

export const CustomMax: Story = {
  args: {
    value: 150,
    max: 200,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    const progressbar = canvas.getByRole('progressbar')
    await expect(progressbar).toHaveAttribute('aria-valuemax', '200')
    await expect(progressbar).toHaveAttribute('aria-valuenow', '150')
  },
}

export const ProgressSteps: Story = {
  render: () => (
    <div className="space-y-6">
      <div>
        <p className="text-sm text-muted-foreground mb-2">Step 1 of 5 - Getting started</p>
        <Progress value={20} />
      </div>
      <div>
        <p className="text-sm text-muted-foreground mb-2">Step 3 of 5 - Configuration</p>
        <Progress value={60} />
      </div>
      <div>
        <p className="text-sm text-muted-foreground mb-2">Step 5 of 5 - Complete</p>
        <Progress value={100} />
      </div>
    </div>
  ),
}

export const WithLabels: Story = {
  render: () => (
    <div className="space-y-6">
      <div>
        <div className="flex justify-between text-sm mb-1">
          <span>Upload progress</span>
          <span>33%</span>
        </div>
        <Progress value={33} />
      </div>
      <div>
        <div className="flex justify-between text-sm mb-1">
          <span>Processing</span>
          <span>67%</span>
        </div>
        <Progress value={67} />
      </div>
      <div>
        <div className="flex justify-between text-sm mb-1">
          <span>Finalizing</span>
          <span>100%</span>
        </div>
        <Progress value={100} />
      </div>
    </div>
  ),
}

export const AllValues: Story = {
  render: () => (
    <div className="space-y-4">
      {[0, 10, 25, 50, 75, 90, 100].map((value) => (
        <div key={value}>
          <p className="text-xs text-muted-foreground mb-1">{value}%</p>
          <Progress value={value} />
        </div>
      ))}
    </div>
  ),
}
