import type { Meta, StoryObj } from '@storybook/react'
import { fn } from 'storybook/test'

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
}

export const WithCompare: Story = {
  args: {
    initialDateFrom: '2024-06-01',
    initialDateTo: '2024-06-30',
    initialCompareFrom: '2023-06-01',
    initialCompareTo: '2023-06-30',
    showCompare: true,
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
