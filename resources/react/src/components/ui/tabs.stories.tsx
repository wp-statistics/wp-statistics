import type { Meta, StoryObj } from '@storybook/react'
import { expect, userEvent, within } from 'storybook/test'

import { Tabs, TabsContent, TabsList, TabsTrigger } from './tabs'

const meta = {
  title: 'UI/Tabs',
  component: Tabs,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
} satisfies Meta<typeof Tabs>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  render: () => (
    <Tabs defaultValue="account" className="w-[400px]">
      <TabsList>
        <TabsTrigger value="account">Account</TabsTrigger>
        <TabsTrigger value="password">Password</TabsTrigger>
      </TabsList>
      <TabsContent value="account">
        <p className="text-sm text-muted-foreground">
          Make changes to your account here. Click save when you&apos;re done.
        </p>
      </TabsContent>
      <TabsContent value="password">
        <p className="text-sm text-muted-foreground">
          Change your password here. After saving, you&apos;ll be logged out.
        </p>
      </TabsContent>
    </Tabs>
  ),
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)

    // Verify account tab is active by default
    const accountTab = canvas.getByRole('tab', { name: /account/i })
    await expect(accountTab).toHaveAttribute('data-state', 'active')

    // Click password tab
    const passwordTab = canvas.getByRole('tab', { name: /password/i })
    await userEvent.click(passwordTab)

    // Verify password tab is now active
    await expect(passwordTab).toHaveAttribute('data-state', 'active')
    await expect(accountTab).toHaveAttribute('data-state', 'inactive')

    // Verify password content is visible
    await expect(canvas.getByText(/change your password here/i)).toBeInTheDocument()
  },
}

export const ThreeTabs: Story = {
  render: () => (
    <Tabs defaultValue="overview" className="w-[500px]">
      <TabsList>
        <TabsTrigger value="overview">Overview</TabsTrigger>
        <TabsTrigger value="analytics">Analytics</TabsTrigger>
        <TabsTrigger value="reports">Reports</TabsTrigger>
      </TabsList>
      <TabsContent value="overview">
        <div className="rounded-lg border p-4">
          <h3 className="font-medium">Overview</h3>
          <p className="text-sm text-muted-foreground mt-2">
            View a summary of your website statistics.
          </p>
        </div>
      </TabsContent>
      <TabsContent value="analytics">
        <div className="rounded-lg border p-4">
          <h3 className="font-medium">Analytics</h3>
          <p className="text-sm text-muted-foreground mt-2">
            Detailed analytics and visitor insights.
          </p>
        </div>
      </TabsContent>
      <TabsContent value="reports">
        <div className="rounded-lg border p-4">
          <h3 className="font-medium">Reports</h3>
          <p className="text-sm text-muted-foreground mt-2">
            Generate and download custom reports.
          </p>
        </div>
      </TabsContent>
    </Tabs>
  ),
}

export const DisabledTab: Story = {
  render: () => (
    <Tabs defaultValue="active" className="w-[400px]">
      <TabsList>
        <TabsTrigger value="active">Active</TabsTrigger>
        <TabsTrigger value="disabled" disabled>
          Disabled
        </TabsTrigger>
        <TabsTrigger value="other">Other</TabsTrigger>
      </TabsList>
      <TabsContent value="active">
        <p className="text-sm text-muted-foreground">This tab is active.</p>
      </TabsContent>
      <TabsContent value="disabled">
        <p className="text-sm text-muted-foreground">This content is not accessible.</p>
      </TabsContent>
      <TabsContent value="other">
        <p className="text-sm text-muted-foreground">Another tab content.</p>
      </TabsContent>
    </Tabs>
  ),
}

export const FullWidth: Story = {
  render: () => (
    <Tabs defaultValue="tab1" className="w-full max-w-[600px]">
      <TabsList className="w-full">
        <TabsTrigger value="tab1" className="flex-1">
          Tab 1
        </TabsTrigger>
        <TabsTrigger value="tab2" className="flex-1">
          Tab 2
        </TabsTrigger>
        <TabsTrigger value="tab3" className="flex-1">
          Tab 3
        </TabsTrigger>
      </TabsList>
      <TabsContent value="tab1">
        <p className="text-sm text-muted-foreground">Content for Tab 1</p>
      </TabsContent>
      <TabsContent value="tab2">
        <p className="text-sm text-muted-foreground">Content for Tab 2</p>
      </TabsContent>
      <TabsContent value="tab3">
        <p className="text-sm text-muted-foreground">Content for Tab 3</p>
      </TabsContent>
    </Tabs>
  ),
}

export const VerticalLayout: Story = {
  render: () => (
    <Tabs defaultValue="general" orientation="vertical" className="flex w-[500px] gap-4">
      <TabsList className="flex h-auto flex-col">
        <TabsTrigger value="general" className="w-full justify-start">
          General
        </TabsTrigger>
        <TabsTrigger value="privacy" className="w-full justify-start">
          Privacy
        </TabsTrigger>
        <TabsTrigger value="notifications" className="w-full justify-start">
          Notifications
        </TabsTrigger>
      </TabsList>
      <div className="flex-1">
        <TabsContent value="general" className="mt-0">
          <div className="rounded-lg border p-4">
            <h3 className="font-medium">General Settings</h3>
            <p className="text-sm text-muted-foreground mt-2">
              Configure your general preferences.
            </p>
          </div>
        </TabsContent>
        <TabsContent value="privacy" className="mt-0">
          <div className="rounded-lg border p-4">
            <h3 className="font-medium">Privacy Settings</h3>
            <p className="text-sm text-muted-foreground mt-2">
              Manage your privacy and data settings.
            </p>
          </div>
        </TabsContent>
        <TabsContent value="notifications" className="mt-0">
          <div className="rounded-lg border p-4">
            <h3 className="font-medium">Notification Settings</h3>
            <p className="text-sm text-muted-foreground mt-2">
              Control your notification preferences.
            </p>
          </div>
        </TabsContent>
      </div>
    </Tabs>
  ),
}
