import type { Meta, StoryObj } from '@storybook/react'
import { expect, within } from 'storybook/test'

import { LastVisitCell } from './last-visit-cell'

const meta = {
  title: 'DataTable/Cells/LastVisitCell',
  component: LastVisitCell,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
} satisfies Meta<typeof LastVisitCell>

export default meta
type Story = StoryObj<typeof meta>

// Create consistent dates for testing
const testDate = new Date('2025-01-15T14:30:00')
const morningDate = new Date('2025-06-22T09:15:00')
const eveningDate = new Date('2025-12-01T22:45:00')

export const Default: Story = {
  args: {
    date: testDate,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify date is displayed (Jan 15)
    await expect(canvas.getByText('Jan 15')).toBeInTheDocument()

    // Verify time is displayed (2:30 PM)
    await expect(canvas.getByText('2:30 PM')).toBeInTheDocument()
  },
}

export const MorningTime: Story = {
  args: {
    date: morningDate,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify date (Jun 22)
    await expect(canvas.getByText('Jun 22')).toBeInTheDocument()

    // Verify time (9:15 AM)
    await expect(canvas.getByText('9:15 AM')).toBeInTheDocument()
  },
}

export const EveningTime: Story = {
  args: {
    date: eveningDate,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify date (Dec 1)
    await expect(canvas.getByText('Dec 1')).toBeInTheDocument()

    // Verify time (10:45 PM)
    await expect(canvas.getByText('10:45 PM')).toBeInTheDocument()
  },
}

export const Midnight: Story = {
  args: {
    date: new Date('2025-03-10T00:05:00'),
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    await expect(canvas.getByText('Mar 10')).toBeInTheDocument()
    await expect(canvas.getByText('12:05 AM')).toBeInTheDocument()
  },
}

export const Noon: Story = {
  args: {
    date: new Date('2025-07-04T12:00:00'),
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    await expect(canvas.getByText('Jul 4')).toBeInTheDocument()
    await expect(canvas.getByText('12:00 PM')).toBeInTheDocument()
  },
}
