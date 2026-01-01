import type { Meta, StoryObj } from '@storybook/react'
import { expect, within } from 'storybook/test'

import { NumericCell } from './numeric-cell'

const meta = {
  title: 'DataTable/Cells/NumericCell',
  component: NumericCell,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
} satisfies Meta<typeof NumericCell>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    value: 1234,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify formatted number is displayed
    await expect(canvas.getByText('1,234')).toBeInTheDocument()
  },
}

export const WithSuffix: Story = {
  args: {
    value: 85,
    suffix: '%',
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify number with suffix is displayed
    await expect(canvas.getByText('85%')).toBeInTheDocument()
  },
}

export const WithDecimals: Story = {
  args: {
    value: 17.5,
    decimals: 1,
    suffix: '%',
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify decimal value is displayed
    await expect(canvas.getByText('17.5%')).toBeInTheDocument()
  },
}

export const TrailingZeroRemoved: Story = {
  args: {
    value: 100,
    decimals: 1,
    suffix: '%',
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify trailing .0 is removed (100 not 100.0)
    await expect(canvas.getByText('100%')).toBeInTheDocument()
  },
}

export const LargeNumber: Story = {
  args: {
    value: 1234567,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify large number is formatted with commas
    await expect(canvas.getByText('1,234,567')).toBeInTheDocument()
  },
}

export const Zero: Story = {
  args: {
    value: 0,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    await expect(canvas.getByText('0')).toBeInTheDocument()
  },
}
