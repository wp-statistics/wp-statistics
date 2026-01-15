import type { Meta, StoryObj } from '@storybook/react'
import { expect, userEvent, within } from 'storybook/test'

import { Textarea } from './textarea'
import { Label } from './label'

const meta = {
  title: 'UI/Textarea',
  component: Textarea,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
  decorators: [
    (Story) => (
      <div className="w-[400px]">
        <Story />
      </div>
    ),
  ],
  argTypes: {
    placeholder: {
      control: 'text',
      description: 'Placeholder text',
    },
    disabled: {
      control: 'boolean',
      description: 'Whether the textarea is disabled',
    },
    rows: {
      control: 'number',
      description: 'Number of visible text lines',
    },
  },
} satisfies Meta<typeof Textarea>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    placeholder: 'Type your message here...',
    'aria-label': 'Message',
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    const textarea = canvas.getByPlaceholderText(/type your message/i)
    await expect(textarea).toBeInTheDocument()

    await userEvent.type(textarea, 'Hello, World!')
    await expect(textarea).toHaveValue('Hello, World!')
  },
}

export const WithLabel: Story = {
  render: () => (
    <div className="grid gap-2">
      <Label htmlFor="message">Your message</Label>
      <Textarea id="message" placeholder="Type your message here..." />
    </div>
  ),
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByLabelText(/your message/i)).toBeInTheDocument()
  },
}

export const Disabled: Story = {
  args: {
    placeholder: 'Disabled textarea',
    disabled: true,
    'aria-label': 'Disabled input',
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    const textarea = canvas.getByPlaceholderText(/disabled/i)
    await expect(textarea).toBeDisabled()
  },
}

export const WithDefaultValue: Story = {
  args: {
    defaultValue: 'This is pre-filled content that can be edited.',
    'aria-label': 'Editable content',
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    const textarea = canvas.getByDisplayValue(/pre-filled content/i)
    await expect(textarea).toBeInTheDocument()
  },
}

export const WithRows: Story = {
  args: {
    placeholder: 'Larger textarea with 8 rows',
    rows: 8,
    'aria-label': 'Large text input',
  },
}

export const SmallTextarea: Story = {
  args: {
    placeholder: 'Small textarea with 2 rows',
    rows: 2,
    'aria-label': 'Small text input',
  },
}

export const WithMaxLength: Story = {
  render: () => (
    <div className="grid gap-2">
      <Label htmlFor="limited">Limited to 100 characters</Label>
      <Textarea id="limited" placeholder="Type here..." maxLength={100} />
      <p className="text-xs text-muted-foreground">Maximum 100 characters</p>
    </div>
  ),
}

export const Required: Story = {
  render: () => (
    <div className="grid gap-2">
      <Label htmlFor="required">
        Required field <span className="text-destructive">*</span>
      </Label>
      <Textarea id="required" placeholder="This field is required" required />
    </div>
  ),
}

export const FormExample: Story = {
  render: () => (
    <form className="space-y-4">
      <div className="grid gap-2">
        <Label htmlFor="feedback">Feedback</Label>
        <Textarea id="feedback" placeholder="Share your thoughts..." rows={4} />
      </div>
      <div className="grid gap-2">
        <Label htmlFor="notes">Additional Notes</Label>
        <Textarea id="notes" placeholder="Any additional information..." rows={3} />
      </div>
    </form>
  ),
}

export const WithHelperText: Story = {
  render: () => (
    <div className="grid gap-2">
      <Label htmlFor="bio">Bio</Label>
      <Textarea id="bio" placeholder="Write a short bio..." rows={4} />
      <p className="text-xs text-muted-foreground">Write a few sentences about yourself. This will be shown publicly.</p>
    </div>
  ),
}

export const ReadOnly: Story = {
  args: {
    defaultValue: 'This content is read-only and cannot be edited.',
    readOnly: true,
    'aria-label': 'Read-only content',
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    const textarea = canvas.getByDisplayValue(/read-only/i)
    await expect(textarea).toHaveAttribute('readonly')
  },
}
