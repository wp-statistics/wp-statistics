import type { Meta, StoryObj } from '@storybook/react'
import { expect, fn, userEvent, within } from 'storybook/test'

import { DateRangePicker } from './date-range-picker'

const meta = {
  title: 'Custom/DateRangePicker',
  component: DateRangePicker,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
  argTypes: {
    align: {
      control: 'select',
      options: ['start', 'center', 'end'],
    },
    showCompare: {
      control: 'boolean',
    },
    locale: {
      control: 'text',
    },
  },
  args: {
    onUpdate: fn(),
  },
} satisfies Meta<typeof DateRangePicker>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    initialDateFrom: new Date(),
    initialDateTo: new Date(),
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Find and click the trigger button to open the picker
    const triggerButton = canvas.getByRole('button')
    await expect(triggerButton).toBeInTheDocument()
    await userEvent.click(triggerButton)

    // Verify popover content is visible (wait for portal to render)
    const body = within(document.body)
    const todayButton = await body.findByRole('button', { name: /select date range: today/i })
    await expect(todayButton).toBeInTheDocument()

    // Close by clicking cancel
    await userEvent.click(body.getByRole('button', { name: /cancel/i }))
  },
}

export const WithDateRange: Story = {
  args: {
    initialDateFrom: '2024-01-01',
    initialDateTo: '2024-01-31',
  },
}

export const Last30Days: Story = {
  args: {
    initialDateFrom: new Date(new Date().setDate(new Date().getDate() - 30)),
    initialDateTo: new Date(),
  },
  play: async ({ canvasElement, args }) => {
    const canvas = within(canvasElement)

    // Open picker
    const triggerButton = canvas.getByRole('button')
    await userEvent.click(triggerButton)

    const body = within(document.body)

    // Click "Last 7 Days" preset
    const last7DaysButton = body.getByRole('button', { name: /last 7 days/i })
    await userEvent.click(last7DaysButton)

    // Click Apply to confirm
    const applyButton = body.getByRole('button', { name: /apply/i })
    await userEvent.click(applyButton)

    // Verify onUpdate was called
    await expect(args.onUpdate).toHaveBeenCalled()
  },
}

export const WithCompare: Story = {
  args: {
    initialDateFrom: '2024-06-01',
    initialDateTo: '2024-06-30',
    initialCompareFrom: '2023-06-01',
    initialCompareTo: '2023-06-30',
    showCompare: true,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Open picker
    const triggerButton = canvas.getByRole('button')
    await userEvent.click(triggerButton)

    const body = within(document.body)

    // Find the compare toggle checkbox
    const compareCheckbox = body.getByRole('checkbox')
    await expect(compareCheckbox).toBeInTheDocument()

    // Toggle compare off
    await userEvent.click(compareCheckbox)

    // Toggle compare back on
    await userEvent.click(compareCheckbox)

    // Cancel to close
    await userEvent.click(body.getByRole('button', { name: /cancel/i }))
  },
}

export const WithoutCompare: Story = {
  args: {
    initialDateFrom: '2024-01-01',
    initialDateTo: '2024-12-31',
    showCompare: false,
  },
}

export const AlignStart: Story = {
  args: {
    initialDateFrom: new Date(),
    initialDateTo: new Date(),
    align: 'start',
  },
}

export const AlignCenter: Story = {
  args: {
    initialDateFrom: new Date(),
    initialDateTo: new Date(),
    align: 'center',
  },
}

export const CustomLocale: Story = {
  args: {
    initialDateFrom: new Date(),
    initialDateTo: new Date(),
    locale: 'de-DE',
  },
}

export const SixMonthRange: Story = {
  args: {
    initialDateFrom: new Date(new Date().setMonth(new Date().getMonth() - 6)),
    initialDateTo: new Date(),
    showCompare: true,
  },
}
