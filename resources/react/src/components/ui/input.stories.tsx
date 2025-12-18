import type { Meta, StoryObj } from '@storybook/react'
import { expect, userEvent, within } from '@storybook/test'

import { Input } from './input'

const meta = {
  title: 'UI/Input',
  component: Input,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
  argTypes: {
    type: {
      control: 'select',
      options: ['text', 'email', 'password', 'number', 'search', 'tel', 'url'],
    },
  },
} satisfies Meta<typeof Input>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    type: 'text',
    placeholder: 'Enter text...',
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    const input = canvas.getByPlaceholderText('Enter text...')

    await expect(input).toBeInTheDocument()
    await expect(input).toHaveValue('')

    await userEvent.type(input, 'Hello World')
    await expect(input).toHaveValue('Hello World')

    await userEvent.clear(input)
    await expect(input).toHaveValue('')
  },
}

export const Email: Story = {
  args: {
    type: 'email',
    placeholder: 'email@example.com',
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    const input = canvas.getByPlaceholderText('email@example.com')

    await userEvent.type(input, 'test@example.com')
    await expect(input).toHaveValue('test@example.com')
  },
}

export const Password: Story = {
  args: {
    type: 'password',
    placeholder: 'Enter password',
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    const input = canvas.getByPlaceholderText('Enter password')

    await expect(input).toHaveAttribute('type', 'password')
    await userEvent.type(input, 'secret123')
    await expect(input).toHaveValue('secret123')
  },
}

export const Disabled: Story = {
  args: {
    disabled: true,
    placeholder: 'Disabled input',
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    const input = canvas.getByPlaceholderText('Disabled input')

    await expect(input).toBeDisabled()
  },
}

export const WithLabel: Story = {
  render: () => (
    <div className="grid w-full max-w-sm items-center gap-1.5">
      <label htmlFor="email" className="text-sm font-medium">
        Email
      </label>
      <Input type="email" id="email" placeholder="Email" />
    </div>
  ),
}

export const WithHelperText: Story = {
  render: () => (
    <div className="grid w-full max-w-sm items-center gap-1.5">
      <label htmlFor="email" className="text-sm font-medium">
        Email
      </label>
      <Input type="email" id="email" placeholder="Email" />
      <p className="text-sm text-muted-foreground">Enter your email address.</p>
    </div>
  ),
}

export const File: Story = {
  render: () => (
    <div className="grid w-full max-w-sm items-center gap-1.5">
      <label htmlFor="picture" className="text-sm font-medium">
        Picture
      </label>
      <Input id="picture" type="file" />
    </div>
  ),
}

export const Search: Story = {
  args: {
    type: 'search',
    placeholder: 'Search...',
    className: 'w-[300px]',
  },
}
