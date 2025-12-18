import type { Meta, StoryObj } from '@storybook/react'
import { expect, fn, userEvent, within } from '@storybook/test'

import { FilterBar } from './filter-bar'

const meta = {
  title: 'Custom/FilterBar',
  component: FilterBar,
  parameters: {
    layout: 'padded',
  },
  tags: ['autodocs'],
  args: {
    onRemoveFilter: fn(),
  },
} satisfies Meta<typeof FilterBar>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    filters: [
      { id: 'visitor-growth', label: 'Visitor Growth', operator: '<', value: 2 },
      { id: 'views', label: 'Views', operator: '<', value: 10 },
      { id: 'exits', label: 'Exits', operator: '=', value: 5 },
      { id: 'bounce-rate', label: 'Bounce Rate', operator: '>', value: 4 },
      { id: 'url', label: 'URL', operator: 'Contains', value: 2 },
    ],
  },
  play: async ({ canvasElement, args }) => {
    const canvas = within(canvasElement)

    // Verify filters are displayed
    await expect(canvas.getByText('Visitor Growth')).toBeInTheDocument()
    await expect(canvas.getByText('Views')).toBeInTheDocument()
    await expect(canvas.getByText('Exits')).toBeInTheDocument()

    // Click remove on first filter
    const removeButtons = canvas.getAllByRole('button')
    await userEvent.click(removeButtons[0])
    await expect(args.onRemoveFilter).toHaveBeenCalledWith('visitor-growth')
  },
}

export const SingleFilter: Story = {
  args: {
    filters: [{ id: 'views', label: 'Views', operator: '>', value: 100 }],
  },
}

export const TwoFilters: Story = {
  args: {
    filters: [
      { id: 'country', label: 'Country', operator: '=', value: 'United States' },
      { id: 'bounce-rate', label: 'Bounce Rate', operator: '<', value: 50 },
    ],
  },
}

export const ManyFilters: Story = {
  args: {
    filters: [
      { id: 'visitor-growth', label: 'Visitor Growth', operator: '<', value: 2 },
      { id: 'views', label: 'Views', operator: '<', value: 10 },
      { id: 'exits', label: 'Exits', operator: '=', value: 5 },
      { id: 'bounce-rate', label: 'Bounce Rate', operator: '>', value: 4 },
      { id: 'url', label: 'URL', operator: 'Contains', value: 'blog' },
      { id: 'country', label: 'Country', operator: '=', value: 'US' },
      { id: 'browser', label: 'Browser', operator: '=', value: 'Chrome' },
      { id: 'os', label: 'OS', operator: '=', value: 'Windows' },
    ],
  },
}

export const EmptyFilters: Story = {
  args: {
    filters: [],
  },
}

export const StringOperators: Story = {
  args: {
    filters: [
      { id: 'url', label: 'URL', operator: 'Contains', value: '/blog' },
      { id: 'title', label: 'Title', operator: 'Starts with', value: 'How to' },
      { id: 'referrer', label: 'Referrer', operator: 'Ends with', value: '.com' },
    ],
  },
}

export const NumericComparisons: Story = {
  args: {
    filters: [
      { id: 'views', label: 'Views', operator: '>=', value: 100 },
      { id: 'sessions', label: 'Sessions', operator: '<=', value: 50 },
      { id: 'bounce-rate', label: 'Bounce Rate', operator: '!=', value: 0 },
    ],
  },
}
