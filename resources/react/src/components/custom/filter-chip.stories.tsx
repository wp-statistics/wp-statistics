import type { Meta, StoryObj } from '@storybook/react'

import { FilterChip } from './filter-chip'

const meta = {
  title: 'Custom/FilterChip',
  component: FilterChip,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
  argTypes: {
    operator: {
      control: 'select',
      options: ['<', '>', '=', '!=', '<=', '>=', 'Contains', 'Starts with', 'Ends with'],
    },
  },
  args: {
    onRemove: () => {},
  },
} satisfies Meta<typeof FilterChip>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    label: 'Views',
    operator: '<',
    value: 10,
  },
}

export const VisitorGrowth: Story = {
  args: {
    label: 'Visitor Growth',
    operator: '<',
    value: 2,
  },
}

export const GreaterThan: Story = {
  args: {
    label: 'Bounce Rate',
    operator: '>',
    value: 4,
  },
}

export const Equals: Story = {
  args: {
    label: 'Exits',
    operator: '=',
    value: 5,
  },
}

export const Contains: Story = {
  args: {
    label: 'URL',
    operator: 'Contains',
    value: 'blog',
  },
}

export const StringValue: Story = {
  args: {
    label: 'Country',
    operator: '=',
    value: 'United States',
  },
}
