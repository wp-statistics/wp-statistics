import type { Meta, StoryObj } from '@storybook/react'
import { useState } from 'react'

import { Calendar } from './calendar'

const meta = {
  title: 'UI/Calendar',
  component: Calendar,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
} satisfies Meta<typeof Calendar>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {},
}

export const SingleSelection: Story = {
  render: () => {
    const [date, setDate] = useState<Date | undefined>(new Date())
    return (
      <Calendar
        mode="single"
        selected={date}
        onSelect={setDate}
      />
    )
  },
}

export const RangeSelection: Story = {
  render: () => {
    const [range, setRange] = useState<{ from: Date; to?: Date } | undefined>({
      from: new Date(),
      to: new Date(new Date().setDate(new Date().getDate() + 7)),
    })
    return (
      <Calendar
        mode="range"
        selected={range}
        onSelect={setRange}
        numberOfMonths={2}
      />
    )
  },
}

export const MultipleSelection: Story = {
  render: () => {
    const [dates, setDates] = useState<Date[] | undefined>([
      new Date(),
      new Date(new Date().setDate(new Date().getDate() + 2)),
      new Date(new Date().setDate(new Date().getDate() + 5)),
    ])
    return (
      <Calendar
        mode="multiple"
        selected={dates}
        onSelect={setDates}
      />
    )
  },
}

export const WithDisabledDates: Story = {
  render: () => {
    const [date, setDate] = useState<Date | undefined>(new Date())
    return (
      <Calendar
        mode="single"
        selected={date}
        onSelect={setDate}
        disabled={(date) => date < new Date()}
      />
    )
  },
}

export const TwoMonths: Story = {
  args: {
    numberOfMonths: 2,
  },
}

export const WithDropdowns: Story = {
  args: {
    captionLayout: 'dropdown',
  },
}
