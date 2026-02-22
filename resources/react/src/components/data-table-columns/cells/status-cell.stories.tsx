import type { Meta, StoryObj } from '@storybook/react'
import { expect, within } from 'storybook/test'

import { StatusCell } from './status-cell'

const meta = {
  title: 'DataTable/Cells/StatusCell',
  component: StatusCell,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
  argTypes: {
    status: {
      control: 'select',
      options: ['new', 'returning'],
      description: 'Visitor status type',
    },
    firstVisit: {
      control: 'date',
      description: 'Date of first visit',
    },
  },
} satisfies Meta<typeof StatusCell>

export default meta
type Story = StoryObj<typeof meta>

export const NewVisitor: Story = {
  args: {
    status: 'new',
    firstVisit: new Date('2025-01-15'),
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('new')).toBeInTheDocument()
  },
}

export const ReturningVisitor: Story = {
  args: {
    status: 'returning',
    firstVisit: new Date('2024-06-01'),
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('returning')).toBeInTheDocument()
  },
}

export const NewVisitorToday: Story = {
  args: {
    status: 'new',
    firstVisit: new Date(),
  },
}

export const ReturningVisitorLongTime: Story = {
  args: {
    status: 'returning',
    firstVisit: new Date('2020-01-01'),
  },
}

export const Comparison: Story = {
  render: () => (
    <div className="flex flex-col gap-4">
      <div className="flex items-center gap-4">
        <span className="w-32 text-xs text-muted-foreground">New visitor:</span>
        <StatusCell status="new" firstVisit={new Date('2025-01-15')} />
      </div>
      <div className="flex items-center gap-4">
        <span className="w-32 text-xs text-muted-foreground">Returning visitor:</span>
        <StatusCell status="returning" firstVisit={new Date('2024-06-01')} />
      </div>
    </div>
  ),
}

export const InTableContext: Story = {
  render: () => (
    <table className="w-full">
      <thead>
        <tr className="border-b">
          <th className="p-2 text-left text-sm font-medium">Visitor</th>
          <th className="p-2 text-left text-sm font-medium">Status</th>
        </tr>
      </thead>
      <tbody>
        <tr className="border-b">
          <td className="p-2 text-sm">Visitor #1</td>
          <td className="p-2">
            <StatusCell status="new" firstVisit={new Date('2025-01-15')} />
          </td>
        </tr>
        <tr className="border-b">
          <td className="p-2 text-sm">Visitor #2</td>
          <td className="p-2">
            <StatusCell status="returning" firstVisit={new Date('2024-03-20')} />
          </td>
        </tr>
        <tr className="border-b">
          <td className="p-2 text-sm">Visitor #3</td>
          <td className="p-2">
            <StatusCell status="new" firstVisit={new Date()} />
          </td>
        </tr>
        <tr>
          <td className="p-2 text-sm">Visitor #4</td>
          <td className="p-2">
            <StatusCell status="returning" firstVisit={new Date('2023-01-01')} />
          </td>
        </tr>
      </tbody>
    </table>
  ),
}
