import type { Meta, StoryObj } from '@storybook/react'
import { expect, userEvent, within } from 'storybook/test'

import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from './dialog'
import { Button } from './button'
import { Input } from './input'
import { Label } from './label'

const meta = {
  title: 'UI/Dialog',
  component: Dialog,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
} satisfies Meta<typeof Dialog>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  render: () => (
    <Dialog>
      <DialogTrigger asChild>
        <Button variant="outline">Open Dialog</Button>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Dialog Title</DialogTitle>
          <DialogDescription>This is a description of what the dialog is for.</DialogDescription>
        </DialogHeader>
        <div className="py-4">Dialog content goes here</div>
        <DialogFooter>
          <Button type="submit">Save changes</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  ),
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    const trigger = canvas.getByRole('button', { name: /open dialog/i })
    await userEvent.click(trigger)

    // Check dialog opened
    const dialog = await within(document.body).findByRole('dialog')
    await expect(dialog).toBeInTheDocument()
    await expect(within(dialog).getByText('Dialog Title')).toBeInTheDocument()
  },
}

export const Confirmation: Story = {
  render: () => (
    <Dialog>
      <DialogTrigger asChild>
        <Button variant="destructive">Delete Item</Button>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Are you sure?</DialogTitle>
          <DialogDescription>This action cannot be undone. This will permanently delete the item.</DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button variant="outline">Cancel</Button>
          <Button variant="destructive">Delete</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  ),
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    const trigger = canvas.getByRole('button', { name: /delete item/i })
    await userEvent.click(trigger)

    const dialog = await within(document.body).findByRole('dialog')
    await expect(within(dialog).getByText('Are you sure?')).toBeInTheDocument()
    await expect(within(dialog).getByRole('button', { name: /cancel/i })).toBeInTheDocument()
    await expect(within(dialog).getByRole('button', { name: /delete/i })).toBeInTheDocument()
  },
}

export const WithForm: Story = {
  render: () => (
    <Dialog>
      <DialogTrigger asChild>
        <Button>Edit Profile</Button>
      </DialogTrigger>
      <DialogContent className="sm:max-w-[425px]">
        <DialogHeader>
          <DialogTitle>Edit profile</DialogTitle>
          <DialogDescription>Make changes to your profile here. Click save when done.</DialogDescription>
        </DialogHeader>
        <div className="grid gap-4 py-4">
          <div className="grid grid-cols-4 items-center gap-4">
            <Label htmlFor="name" className="text-right">
              Name
            </Label>
            <Input id="name" defaultValue="John Doe" className="col-span-3" />
          </div>
          <div className="grid grid-cols-4 items-center gap-4">
            <Label htmlFor="username" className="text-right">
              Username
            </Label>
            <Input id="username" defaultValue="@johndoe" className="col-span-3" />
          </div>
        </div>
        <DialogFooter>
          <Button type="submit">Save changes</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  ),
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await userEvent.click(canvas.getByRole('button', { name: /edit profile/i }))

    const dialog = await within(document.body).findByRole('dialog')
    await expect(within(dialog).getByLabelText(/name/i)).toBeInTheDocument()
    await expect(within(dialog).getByLabelText(/username/i)).toBeInTheDocument()
  },
}

export const InformationalDialog: Story = {
  render: () => (
    <Dialog>
      <DialogTrigger asChild>
        <Button variant="secondary">Learn More</Button>
      </DialogTrigger>
      <DialogContent>
        <DialogHeader>
          <DialogTitle>About Analytics</DialogTitle>
          <DialogDescription>
            WP Statistics provides comprehensive analytics for your WordPress site without relying on external services.
          </DialogDescription>
        </DialogHeader>
        <div className="space-y-4 py-4">
          <p className="text-sm text-muted-foreground">
            Track page views, visitors, referrers, and more while keeping your data private and GDPR compliant.
          </p>
          <ul className="text-sm text-muted-foreground list-disc list-inside space-y-1">
            <li>Real-time statistics</li>
            <li>Privacy-focused tracking</li>
            <li>No external dependencies</li>
          </ul>
        </div>
        <DialogFooter>
          <Button variant="outline">Got it</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  ),
}

export const LongContentDialog: Story = {
  render: () => (
    <Dialog>
      <DialogTrigger asChild>
        <Button variant="outline">View Terms</Button>
      </DialogTrigger>
      <DialogContent className="max-h-[80vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Terms of Service</DialogTitle>
          <DialogDescription>Please read our terms of service carefully.</DialogDescription>
        </DialogHeader>
        <div className="space-y-4 py-4">
          {Array.from({ length: 5 }).map((_, i) => (
            <div key={i}>
              <h4 className="font-medium mb-2">Section {i + 1}</h4>
              <p className="text-sm text-muted-foreground">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et
                dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                ex ea commodo consequat.
              </p>
            </div>
          ))}
        </div>
        <DialogFooter>
          <Button variant="outline">Decline</Button>
          <Button>Accept</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  ),
}
