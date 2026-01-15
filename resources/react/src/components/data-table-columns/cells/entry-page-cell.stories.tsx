import type { Meta, StoryObj } from '@storybook/react'
import { expect, within } from 'storybook/test'

import { EntryPageCell } from './entry-page-cell'
import type { PageData } from '../types'

const meta = {
  title: 'DataTable/Cells/EntryPageCell',
  component: EntryPageCell,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
  argTypes: {
    maxLength: {
      control: 'number',
      description: 'Maximum characters before truncation',
    },
  },
} satisfies Meta<typeof EntryPageCell>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    data: {
      title: 'Getting Started Guide',
      url: '/docs/getting-started',
    },
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('Getting Started Guide')).toBeInTheDocument()
  },
}

export const WithQueryString: Story = {
  args: {
    data: {
      title: 'Search Results',
      url: '/search',
      hasQueryString: true,
      queryString: '?q=analytics&category=plugins',
    },
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('Search Results')).toBeInTheDocument()
    // Info icon should be present for query string
  },
}

export const WithUTMCampaign: Story = {
  args: {
    data: {
      title: 'Premium Features',
      url: '/premium',
      utmCampaign: 'summer_sale_2025',
    },
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText(/Premium Features/)).toBeInTheDocument()
    await expect(canvas.getByText('summer_sale_2025')).toBeInTheDocument()
  },
}

export const WithQueryStringAndUTM: Story = {
  args: {
    data: {
      title: 'Landing Page',
      url: '/landing',
      hasQueryString: true,
      queryString: '?ref=newsletter&source=email',
      utmCampaign: 'newsletter_jan_2025',
    },
  },
}

export const LongTitleTruncated: Story = {
  args: {
    data: {
      title: 'This is a very long page title that should be truncated',
      url: '/long-page',
    },
    maxLength: 28,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText(/\.\.\.$/)).toBeInTheDocument()
  },
}

export const ShortTitle: Story = {
  args: {
    data: {
      title: 'Home',
      url: '/',
    },
  },
}

export const CustomMaxLength: Story = {
  args: {
    data: {
      title: 'Documentation Page',
      url: '/docs',
    },
    maxLength: 12,
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('Documentati...')).toBeInTheDocument()
  },
}

export const AllVariants: Story = {
  render: () => {
    const pages: Array<{ label: string; data: PageData }> = [
      {
        label: 'Simple page',
        data: { title: 'About Us', url: '/about' },
      },
      {
        label: 'With query string',
        data: { title: 'Products', url: '/products', hasQueryString: true, queryString: '?category=new' },
      },
      {
        label: 'With UTM',
        data: { title: 'Campaign', url: '/promo', utmCampaign: 'black_friday' },
      },
      {
        label: 'Full featured',
        data: {
          title: 'Special Offer',
          url: '/offer',
          hasQueryString: true,
          queryString: '?ref=ads',
          utmCampaign: 'google_ads_q1',
        },
      },
    ]

    return (
      <div className="space-y-4">
        {pages.map((page) => (
          <div key={page.label} className="flex items-center gap-4">
            <span className="w-32 text-xs text-muted-foreground">{page.label}:</span>
            <EntryPageCell data={page.data} />
          </div>
        ))}
      </div>
    )
  },
}

export const InTableContext: Story = {
  render: () => (
    <table className="w-full">
      <thead>
        <tr className="border-b">
          <th className="p-2 text-left text-sm font-medium">Visitor</th>
          <th className="p-2 text-left text-sm font-medium">Entry Page</th>
        </tr>
      </thead>
      <tbody>
        <tr className="border-b">
          <td className="p-2 text-sm">Visitor #1</td>
          <td className="p-2">
            <EntryPageCell data={{ title: 'Home', url: '/' }} />
          </td>
        </tr>
        <tr className="border-b">
          <td className="p-2 text-sm">Visitor #2</td>
          <td className="p-2">
            <EntryPageCell
              data={{ title: 'Blog Post', url: '/blog/post', hasQueryString: true, queryString: '?ref=twitter' }}
            />
          </td>
        </tr>
        <tr className="border-b">
          <td className="p-2 text-sm">Visitor #3</td>
          <td className="p-2">
            <EntryPageCell data={{ title: 'Products', url: '/products', utmCampaign: 'facebook_ads' }} />
          </td>
        </tr>
        <tr>
          <td className="p-2 text-sm">Visitor #4</td>
          <td className="p-2">
            <EntryPageCell
              data={{
                title: 'Landing Page',
                url: '/landing',
                hasQueryString: true,
                queryString: '?utm_source=google',
                utmCampaign: 'ppc_campaign',
              }}
            />
          </td>
        </tr>
      </tbody>
    </table>
  ),
}
