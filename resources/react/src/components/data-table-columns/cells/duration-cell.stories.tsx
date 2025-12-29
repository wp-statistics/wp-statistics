import type { Meta, StoryObj } from '@storybook/react'
import { expect, within } from 'storybook/test'

import { DurationCell } from './duration-cell'

const meta = {
  title: 'DataTable/Cells/DurationCell',
  component: DurationCell,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
} satisfies Meta<typeof DurationCell>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    seconds: 330, // 5:30
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify duration is formatted as m:ss
    await expect(canvas.getByText('5:30')).toBeInTheDocument()
  },
}

export const UnderOneMinute: Story = {
  args: {
    seconds: 45,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify short duration
    await expect(canvas.getByText('0:45')).toBeInTheDocument()
  },
}

export const WithHours: Story = {
  args: {
    seconds: 3930, // 1:05:30
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify hours format h:mm:ss
    await expect(canvas.getByText('1:05:30')).toBeInTheDocument()
  },
}

export const ExactMinutes: Story = {
  args: {
    seconds: 120, // 2:00
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    await expect(canvas.getByText('2:00')).toBeInTheDocument()
  },
}

export const Zero: Story = {
  args: {
    seconds: 0,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    await expect(canvas.getByText('0:00')).toBeInTheDocument()
  },
}

export const MultipleHours: Story = {
  args: {
    seconds: 7265, // 2:01:05
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    await expect(canvas.getByText('2:01:05')).toBeInTheDocument()
  },
}
