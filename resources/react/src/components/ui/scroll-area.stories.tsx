import type { Meta, StoryObj } from '@storybook/react'

import { ScrollArea, ScrollBar } from './scroll-area'
import { Separator } from './separator'

const meta = {
  title: 'UI/ScrollArea',
  component: ScrollArea,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
} satisfies Meta<typeof ScrollArea>

export default meta
type Story = StoryObj<typeof meta>

const tags = Array.from({ length: 50 }).map((_, i, a) => `v1.2.0-beta.${a.length - i}`)

export const Vertical: Story = {
  render: () => (
    <ScrollArea className="h-72 w-48 rounded-md border">
      <div className="p-4">
        <h4 className="mb-4 text-sm font-medium leading-none">Tags</h4>
        {tags.map((tag) => (
          <div key={tag}>
            <div className="text-sm">{tag}</div>
            <Separator className="my-2" />
          </div>
        ))}
      </div>
    </ScrollArea>
  ),
}

export const Horizontal: Story = {
  render: () => (
    <ScrollArea className="w-96 whitespace-nowrap rounded-md border">
      <div className="flex w-max space-x-4 p-4">
        {Array.from({ length: 20 }).map((_, i) => (
          <figure key={i} className="shrink-0">
            <div className="flex h-24 w-32 items-center justify-center rounded-md bg-muted">
              <span className="text-xs">Image {i + 1}</span>
            </div>
            <figcaption className="pt-2 text-xs text-muted-foreground">Photo by Artist {i + 1}</figcaption>
          </figure>
        ))}
      </div>
      <ScrollBar orientation="horizontal" />
    </ScrollArea>
  ),
}

export const BothDirections: Story = {
  render: () => (
    <ScrollArea className="h-[200px] w-[350px] rounded-md border p-4">
      <div className="w-[500px]">
        {Array.from({ length: 20 }).map((_, i) => (
          <div key={i} className="py-2">
            This is a long line of text that will scroll horizontally. Item number {i + 1}. Lorem ipsum dolor sit amet.
          </div>
        ))}
      </div>
      <ScrollBar orientation="horizontal" />
    </ScrollArea>
  ),
}

export const ShortContent: Story = {
  render: () => (
    <ScrollArea className="h-72 w-48 rounded-md border">
      <div className="p-4">
        <h4 className="mb-4 text-sm font-medium leading-none">Short List</h4>
        {['Item 1', 'Item 2', 'Item 3'].map((item) => (
          <div key={item}>
            <div className="text-sm">{item}</div>
            <Separator className="my-2" />
          </div>
        ))}
      </div>
    </ScrollArea>
  ),
}

export const WithMixedContent: Story = {
  render: () => (
    <ScrollArea className="h-96 w-72 rounded-md border">
      <div className="p-4">
        <h4 className="mb-4 text-sm font-medium leading-none">Dashboard</h4>

        <div className="mb-4">
          <h5 className="text-xs font-medium text-muted-foreground mb-2">Statistics</h5>
          {['Page Views', 'Visitors', 'Bounce Rate', 'Session Duration'].map((stat) => (
            <div key={stat} className="flex justify-between py-1">
              <span className="text-sm">{stat}</span>
              <span className="text-sm text-muted-foreground">{Math.floor(Math.random() * 1000)}</span>
            </div>
          ))}
        </div>

        <Separator className="my-4" />

        <div className="mb-4">
          <h5 className="text-xs font-medium text-muted-foreground mb-2">Top Pages</h5>
          {Array.from({ length: 15 }).map((_, i) => (
            <div key={i} className="py-1">
              <span className="text-sm">/page-{i + 1}</span>
            </div>
          ))}
        </div>

        <Separator className="my-4" />

        <div>
          <h5 className="text-xs font-medium text-muted-foreground mb-2">Recent Activity</h5>
          {Array.from({ length: 10 }).map((_, i) => (
            <div key={i} className="py-1 text-xs text-muted-foreground">
              User visited page {i + 1} - {Math.floor(Math.random() * 60)} min ago
            </div>
          ))}
        </div>
      </div>
    </ScrollArea>
  ),
}

export const CardLayout: Story = {
  render: () => (
    <ScrollArea className="h-80 w-full max-w-md rounded-md border">
      <div className="p-4 space-y-4">
        {Array.from({ length: 8 }).map((_, i) => (
          <div key={i} className="rounded-lg border p-4">
            <h4 className="font-medium mb-1">Card Title {i + 1}</h4>
            <p className="text-sm text-muted-foreground">
              This is a card with some content inside a scrollable area.
            </p>
          </div>
        ))}
      </div>
    </ScrollArea>
  ),
}

export const TableContent: Story = {
  render: () => (
    <ScrollArea className="h-64 w-full max-w-lg rounded-md border">
      <table className="w-full">
        <thead className="sticky top-0 bg-background">
          <tr className="border-b">
            <th className="p-2 text-left text-sm font-medium">Name</th>
            <th className="p-2 text-left text-sm font-medium">Email</th>
            <th className="p-2 text-left text-sm font-medium">Status</th>
          </tr>
        </thead>
        <tbody>
          {Array.from({ length: 20 }).map((_, i) => (
            <tr key={i} className="border-b">
              <td className="p-2 text-sm">User {i + 1}</td>
              <td className="p-2 text-sm">user{i + 1}@example.com</td>
              <td className="p-2 text-sm">{i % 2 === 0 ? 'Active' : 'Inactive'}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </ScrollArea>
  ),
}
