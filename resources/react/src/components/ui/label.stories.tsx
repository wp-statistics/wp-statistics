import type { Meta, StoryObj } from '@storybook/react'

import { Checkbox } from './checkbox'
import { Input } from './input'
import { Label } from './label'

const meta = {
  title: 'UI/Label',
  component: Label,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
} satisfies Meta<typeof Label>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    children: 'Email address',
  },
}

export const WithInput: Story = {
  render: () => (
    <div className="grid w-full max-w-sm items-center gap-1.5">
      <Label htmlFor="email">Email</Label>
      <Input type="email" id="email" placeholder="Email" />
    </div>
  ),
}

export const WithCheckbox: Story = {
  render: () => (
    <div className="flex items-center space-x-2">
      <Checkbox id="terms" />
      <Label htmlFor="terms">Accept terms and conditions</Label>
    </div>
  ),
}

export const DisabledState: Story = {
  render: () => (
    <div className="grid w-full max-w-sm items-center gap-1.5">
      <Label htmlFor="disabled-input">Disabled field</Label>
      <Input type="text" id="disabled-input" placeholder="Disabled" disabled />
    </div>
  ),
}

export const RequiredField: Story = {
  render: () => (
    <div className="grid w-full max-w-sm items-center gap-1.5">
      <Label htmlFor="required-input">
        Username <span className="text-destructive">*</span>
      </Label>
      <Input type="text" id="required-input" placeholder="Enter username" required />
    </div>
  ),
}
