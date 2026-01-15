import type { Meta, StoryObj } from '@storybook/react'
import { expect, within } from 'storybook/test'

import { UriCell } from './uri-cell'

const meta = {
  title: 'DataTable/Cells/UriCell',
  component: UriCell,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
  argTypes: {
    uri: {
      control: 'text',
      description: 'The URI path to display',
    },
    maxLength: {
      control: 'number',
      description: 'Maximum characters before truncation (0 = no truncation)',
    },
  },
} satisfies Meta<typeof UriCell>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    uri: '/blog/my-post',
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('/blog/my-post')).toBeInTheDocument()
  },
}

export const RootPath: Story = {
  args: {
    uri: '/',
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('/')).toBeInTheDocument()
  },
}

export const EmptyPath: Story = {
  args: {
    uri: '',
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    // Empty path defaults to '/'
    await expect(canvas.getByText('/')).toBeInTheDocument()
  },
}

export const ShortPath: Story = {
  args: {
    uri: '/about',
  },
}

export const MediumPath: Story = {
  args: {
    uri: '/blog/2024/my-awesome-blog-post',
  },
}

export const LongPathTruncated: Story = {
  args: {
    uri: '/very/long/path/to/some/deeply/nested/page/in/the/site/structure/with-a-long-filename',
    maxLength: 50,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    // Should be truncated with ellipsis
    await expect(canvas.getByText(/\.\.\.$/)).toBeInTheDocument()
  },
}

export const CustomMaxLength: Story = {
  args: {
    uri: '/short/path/here',
    maxLength: 10,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('/short/pat...')).toBeInTheDocument()
  },
}

export const NoTruncation: Story = {
  args: {
    uri: '/a-very-long-uri-that-should-not-be-truncated-at-all',
    maxLength: 0,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('/a-very-long-uri-that-should-not-be-truncated-at-all')).toBeInTheDocument()
  },
}

export const PathWithQueryParams: Story = {
  args: {
    uri: '/search?q=test&page=2',
  },
}

export const PathWithHash: Story = {
  args: {
    uri: '/docs/api#authentication',
  },
}

export const AllVariants: Story = {
  render: () => (
    <div className="space-y-4">
      <div className="flex items-center gap-4">
        <span className="w-24 text-xs text-muted-foreground">Root:</span>
        <UriCell uri="/" />
      </div>
      <div className="flex items-center gap-4">
        <span className="w-24 text-xs text-muted-foreground">Short:</span>
        <UriCell uri="/about" />
      </div>
      <div className="flex items-center gap-4">
        <span className="w-24 text-xs text-muted-foreground">Medium:</span>
        <UriCell uri="/blog/my-post" />
      </div>
      <div className="flex items-center gap-4">
        <span className="w-24 text-xs text-muted-foreground">Long:</span>
        <UriCell uri="/very/long/path/to/some/deeply/nested/page/index.html" />
      </div>
      <div className="flex items-center gap-4">
        <span className="w-24 text-xs text-muted-foreground">Query:</span>
        <UriCell uri="/search?q=analytics&category=plugins" />
      </div>
    </div>
  ),
}
