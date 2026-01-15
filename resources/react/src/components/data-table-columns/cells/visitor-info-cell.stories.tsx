import type { Meta, StoryObj } from '@storybook/react'
import { expect, within } from 'storybook/test'

import { VisitorInfoCell } from './visitor-info-cell'
import type { VisitorInfoConfig, VisitorInfoData } from '../types'

const defaultConfig: VisitorInfoConfig = {
  pluginUrl: '/public/images/',
  trackLoggedInEnabled: true,
  hashEnabled: false,
}

const meta = {
  title: 'DataTable/Cells/VisitorInfoCell',
  component: VisitorInfoCell,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
  args: {
    config: defaultConfig,
  },
} satisfies Meta<typeof VisitorInfoCell>

export default meta
type Story = StoryObj<typeof meta>

export const AnonymousVisitorWithIP: Story = {
  args: {
    data: {
      country: { code: 'US', name: 'United States', city: 'New York' },
      os: { icon: 'windows', name: 'Windows' },
      browser: { icon: 'chrome', name: 'Chrome', version: '120' },
      identifier: '192.168.1.1',
    },
    config: { ...defaultConfig, hashEnabled: false },
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('Chrome · Windows')).toBeInTheDocument()
    await expect(canvas.getByText('192.168.1.1')).toBeInTheDocument()
  },
}

export const AnonymousVisitorWithHash: Story = {
  args: {
    data: {
      country: { code: 'GB', name: 'United Kingdom', city: 'London' },
      os: { icon: 'macos', name: 'macOS' },
      browser: { icon: 'safari', name: 'Safari', version: '17' },
      identifier: '#hash#abc123def456',
    },
    config: { ...defaultConfig, hashEnabled: true },
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('Safari · macOS')).toBeInTheDocument()
    // Should show first 6 chars of hash
    await expect(canvas.getByText('abc123')).toBeInTheDocument()
  },
}

export const LoggedInUser: Story = {
  args: {
    data: {
      country: { code: 'CA', name: 'Canada', region: 'Ontario', city: 'Toronto' },
      os: { icon: 'linux', name: 'Linux' },
      browser: { icon: 'firefox', name: 'Firefox', version: '121' },
      user: { id: 42, username: 'johndoe', email: 'john@example.com', role: 'Administrator' },
    },
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('Firefox · Linux')).toBeInTheDocument()
    await expect(canvas.getByText('johndoe #42')).toBeInTheDocument()
  },
}

export const LoggedInUserWithRole: Story = {
  args: {
    data: {
      country: { code: 'DE', name: 'Germany', city: 'Berlin' },
      os: { icon: 'windows', name: 'Windows' },
      browser: { icon: 'edge', name: 'Edge', version: '120' },
      user: { id: 15, username: 'editor_user', role: 'Editor' },
    },
  },
}

export const MobileVisitor: Story = {
  args: {
    data: {
      country: { code: 'JP', name: 'Japan', city: 'Tokyo' },
      os: { icon: 'ios', name: 'iOS' },
      browser: { icon: 'safari', name: 'Safari Mobile', version: '17' },
      identifier: '10.0.0.1',
    },
  },
}

export const AndroidVisitor: Story = {
  args: {
    data: {
      country: { code: 'IN', name: 'India', city: 'Mumbai' },
      os: { icon: 'android', name: 'Android' },
      browser: { icon: 'chrome', name: 'Chrome Mobile', version: '120' },
      identifier: '#hash#xyz789abc123',
    },
    config: { ...defaultConfig, hashEnabled: true },
  },
}

export const CountryOnly: Story = {
  args: {
    data: {
      country: { code: 'AU', name: 'Australia' },
      os: { icon: 'macos', name: 'macOS' },
      browser: { icon: 'chrome', name: 'Chrome' },
      identifier: '172.16.0.1',
    },
  },
}

export const AllVariants: Story = {
  render: () => {
    const visitors: Array<{ label: string; data: VisitorInfoData; config: VisitorInfoConfig }> = [
      {
        label: 'Anonymous (IP)',
        data: {
          country: { code: 'US', name: 'United States', city: 'San Francisco' },
          os: { icon: 'windows', name: 'Windows' },
          browser: { icon: 'chrome', name: 'Chrome', version: '120' },
          identifier: '192.168.1.100',
        },
        config: { ...defaultConfig, hashEnabled: false },
      },
      {
        label: 'Anonymous (Hash)',
        data: {
          country: { code: 'FR', name: 'France', city: 'Paris' },
          os: { icon: 'macos', name: 'macOS' },
          browser: { icon: 'safari', name: 'Safari' },
          identifier: '#hash#def456ghi789',
        },
        config: { ...defaultConfig, hashEnabled: true },
      },
      {
        label: 'Logged in user',
        data: {
          country: { code: 'GB', name: 'United Kingdom', city: 'London' },
          os: { icon: 'linux', name: 'Linux' },
          browser: { icon: 'firefox', name: 'Firefox' },
          user: { id: 1, username: 'admin', role: 'Administrator' },
        },
        config: defaultConfig,
      },
    ]

    return (
      <div className="space-y-4">
        {visitors.map((visitor) => (
          <div key={visitor.label} className="flex items-start gap-4">
            <span className="w-32 text-xs text-muted-foreground pt-1">{visitor.label}:</span>
            <VisitorInfoCell data={visitor.data} config={visitor.config} />
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
          <th className="p-2 text-left text-sm font-medium">#</th>
          <th className="p-2 text-left text-sm font-medium">Visitor</th>
        </tr>
      </thead>
      <tbody>
        <tr className="border-b">
          <td className="p-2 text-sm align-top">1</td>
          <td className="p-2">
            <VisitorInfoCell
              data={{
                country: { code: 'US', name: 'United States', city: 'New York' },
                os: { icon: 'windows', name: 'Windows' },
                browser: { icon: 'chrome', name: 'Chrome', version: '120' },
                identifier: '192.168.1.1',
              }}
              config={{ ...defaultConfig, hashEnabled: false }}
            />
          </td>
        </tr>
        <tr className="border-b">
          <td className="p-2 text-sm align-top">2</td>
          <td className="p-2">
            <VisitorInfoCell
              data={{
                country: { code: 'DE', name: 'Germany', city: 'Berlin' },
                os: { icon: 'macos', name: 'macOS' },
                browser: { icon: 'safari', name: 'Safari' },
                user: { id: 5, username: 'editor', role: 'Editor' },
              }}
              config={defaultConfig}
            />
          </td>
        </tr>
        <tr className="border-b">
          <td className="p-2 text-sm align-top">3</td>
          <td className="p-2">
            <VisitorInfoCell
              data={{
                country: { code: 'JP', name: 'Japan', city: 'Tokyo' },
                os: { icon: 'ios', name: 'iOS' },
                browser: { icon: 'safari', name: 'Safari Mobile' },
                identifier: '#hash#abc123xyz789',
              }}
              config={{ ...defaultConfig, hashEnabled: true }}
            />
          </td>
        </tr>
        <tr>
          <td className="p-2 text-sm align-top">4</td>
          <td className="p-2">
            <VisitorInfoCell
              data={{
                country: { code: 'BR', name: 'Brazil', city: 'São Paulo' },
                os: { icon: 'android', name: 'Android' },
                browser: { icon: 'chrome', name: 'Chrome Mobile' },
                user: { id: 12, username: 'subscriber', role: 'Subscriber' },
              }}
              config={defaultConfig}
            />
          </td>
        </tr>
      </tbody>
    </table>
  ),
}
