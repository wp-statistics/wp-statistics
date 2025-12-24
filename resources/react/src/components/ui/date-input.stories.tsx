import type { Meta, StoryObj } from '@storybook/react'
import { fn } from 'storybook/test'

import { DateInput } from './date-input'

const meta = {
  title: 'UI/DateInput',
  component: DateInput,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
  args: {
    onChange: fn(),
  },
} satisfies Meta<typeof DateInput>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    value: new Date(),
  },
}

export const WithSpecificDate: Story = {
  args: {
    value: new Date('2024-06-15'),
  },
}

export const StartOfYear: Story = {
  args: {
    value: new Date('2024-01-01'),
  },
}

export const EndOfYear: Story = {
  args: {
    value: new Date('2024-12-31'),
  },
}
