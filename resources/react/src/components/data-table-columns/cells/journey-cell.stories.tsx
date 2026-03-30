import type { Meta, StoryObj } from '@storybook/react'
import { expect, within } from 'storybook/test'

import { JourneyCell } from './journey-cell'

const meta = {
  title: 'DataTable/Cells/JourneyCell',
  component: JourneyCell,
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
} satisfies Meta<typeof JourneyCell>

export default meta
type Story = StoryObj<typeof meta>

export const NormalJourney: Story = {
  args: {
    data: {
      entryPage: { title: 'Home', url: '/' },
      exitPage: { title: 'Contact Us', url: '/contact' },
      isBounce: false,
    },
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('Home')).toBeInTheDocument()
    await expect(canvas.getByText('Contact Us')).toBeInTheDocument()
  },
}

export const BounceVisit: Story = {
  args: {
    data: {
      entryPage: { title: 'Blog Post', url: '/blog/my-post' },
      exitPage: { title: 'Blog Post', url: '/blog/my-post' },
      isBounce: true,
    },
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('Blog Post')).toBeInTheDocument()
  },
}

export const WithUTMCampaign: Story = {
  args: {
    data: {
      entryPage: { title: 'Landing Page', url: '/promo', utmCampaign: 'summer_sale_2025' },
      exitPage: { title: 'Checkout', url: '/checkout' },
      isBounce: false,
    },
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('summer_sale_2025')).toBeInTheDocument()
  },
}

export const BounceWithUTM: Story = {
  args: {
    data: {
      entryPage: { title: 'Promo Page', url: '/promo', utmCampaign: 'google_ads' },
      exitPage: { title: 'Promo Page', url: '/promo' },
      isBounce: true,
    },
  },
}

export const LongPageTitles: Story = {
  args: {
    data: {
      entryPage: { title: 'This is a very long entry page title', url: '/long-entry' },
      exitPage: { title: 'This is a very long exit page title', url: '/long-exit' },
      isBounce: false,
    },
    maxLength: 20,
  },
}

export const CustomMaxLength: Story = {
  args: {
    data: {
      entryPage: { title: 'Documentation Guide', url: '/docs' },
      exitPage: { title: 'API Reference', url: '/api' },
      isBounce: false,
    },
    maxLength: 12,
  },
}

export const AllVariants: Story = {
  render: () => (
    <div className="space-y-6">
      <div className="flex items-start gap-4">
        <span className="w-32 text-xs text-muted-foreground pt-1">Normal journey:</span>
        <JourneyCell
          data={{
            entryPage: { title: 'Home', url: '/' },
            exitPage: { title: 'Products', url: '/products' },
            isBounce: false,
          }}
        />
      </div>
      <div className="flex items-start gap-4">
        <span className="w-32 text-xs text-muted-foreground pt-1">With UTM:</span>
        <JourneyCell
          data={{
            entryPage: { title: 'Landing', url: '/landing', utmCampaign: 'email_campaign' },
            exitPage: { title: 'Signup', url: '/signup' },
            isBounce: false,
          }}
        />
      </div>
      <div className="flex items-start gap-4">
        <span className="w-32 text-xs text-muted-foreground pt-1">Bounce:</span>
        <JourneyCell
          data={{
            entryPage: { title: 'Blog Post', url: '/blog/post' },
            exitPage: { title: 'Blog Post', url: '/blog/post' },
            isBounce: true,
          }}
        />
      </div>
      <div className="flex items-start gap-4">
        <span className="w-32 text-xs text-muted-foreground pt-1">Bounce with UTM:</span>
        <JourneyCell
          data={{
            entryPage: { title: 'Promo', url: '/promo', utmCampaign: 'ppc_ad' },
            exitPage: { title: 'Promo', url: '/promo' },
            isBounce: true,
          }}
        />
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
          <th className="p-2 text-left text-sm font-medium">Journey</th>
        </tr>
      </thead>
      <tbody>
        <tr className="border-b">
          <td className="p-2 text-sm align-top">Visitor #1</td>
          <td className="p-2">
            <JourneyCell
              data={{
                entryPage: { title: 'Home', url: '/' },
                exitPage: { title: 'About', url: '/about' },
                isBounce: false,
              }}
            />
          </td>
        </tr>
        <tr className="border-b">
          <td className="p-2 text-sm align-top">Visitor #2</td>
          <td className="p-2">
            <JourneyCell
              data={{
                entryPage: { title: 'Blog', url: '/blog' },
                exitPage: { title: 'Blog', url: '/blog' },
                isBounce: true,
              }}
            />
          </td>
        </tr>
        <tr className="border-b">
          <td className="p-2 text-sm align-top">Visitor #3</td>
          <td className="p-2">
            <JourneyCell
              data={{
                entryPage: { title: 'Landing', url: '/landing', utmCampaign: 'facebook' },
                exitPage: { title: 'Checkout', url: '/checkout' },
                isBounce: false,
              }}
            />
          </td>
        </tr>
        <tr>
          <td className="p-2 text-sm align-top">Visitor #4</td>
          <td className="p-2">
            <JourneyCell
              data={{
                entryPage: { title: 'Products', url: '/products' },
                exitPage: { title: 'Cart', url: '/cart' },
                isBounce: false,
              }}
            />
          </td>
        </tr>
      </tbody>
    </table>
  ),
}
