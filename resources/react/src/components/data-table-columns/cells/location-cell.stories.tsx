import type { Meta, StoryObj } from '@storybook/react'
import { expect, within } from 'storybook/test'

import { LocationCell, type LocationData } from './location-cell'

const meta = {
  title: 'DataTable/Cells/LocationCell',
  component: LocationCell,
  parameters: {
    layout: 'centered',
  },
  tags: ['autodocs'],
  args: {
    pluginUrl: '/public/images/',
  },
} satisfies Meta<typeof LocationCell>

export default meta
type Story = StoryObj<typeof meta>

export const CityAndCountry: Story = {
  args: {
    data: {
      countryCode: 'US',
      countryName: 'United States',
      regionName: 'California',
      cityName: 'San Francisco',
    },
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('San Francisco, United States')).toBeInTheDocument()
  },
}

export const RegionAndCountry: Story = {
  args: {
    data: {
      countryCode: 'CA',
      countryName: 'Canada',
      regionName: 'Ontario',
    },
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('Ontario, Canada')).toBeInTheDocument()
  },
}

export const CountryOnly: Story = {
  args: {
    data: {
      countryCode: 'DE',
      countryName: 'Germany',
    },
  },
  play: async ({ canvasElement }) => {
    const canvas = within(canvasElement)
    await expect(canvas.getByText('Germany')).toBeInTheDocument()
  },
}

export const UnitedKingdom: Story = {
  args: {
    data: {
      countryCode: 'GB',
      countryName: 'United Kingdom',
      regionName: 'England',
      cityName: 'London',
    },
  },
}

export const Japan: Story = {
  args: {
    data: {
      countryCode: 'JP',
      countryName: 'Japan',
      regionName: 'Tokyo',
      cityName: 'Shibuya',
    },
  },
}

export const Brazil: Story = {
  args: {
    data: {
      countryCode: 'BR',
      countryName: 'Brazil',
      regionName: 'São Paulo',
      cityName: 'São Paulo',
    },
  },
}

export const Australia: Story = {
  args: {
    data: {
      countryCode: 'AU',
      countryName: 'Australia',
      regionName: 'New South Wales',
      cityName: 'Sydney',
    },
  },
}

export const AllLocationFormats: Story = {
  render: () => {
    const locations: Array<{ label: string; data: LocationData }> = [
      {
        label: 'Full location',
        data: { countryCode: 'US', countryName: 'United States', regionName: 'California', cityName: 'San Francisco' },
      },
      {
        label: 'Region + Country',
        data: { countryCode: 'FR', countryName: 'France', regionName: 'Île-de-France' },
      },
      {
        label: 'Country only',
        data: { countryCode: 'IT', countryName: 'Italy' },
      },
    ]

    return (
      <div className="space-y-4">
        {locations.map((loc) => (
          <div key={loc.label} className="flex items-center gap-4">
            <span className="w-32 text-xs text-muted-foreground">{loc.label}:</span>
            <LocationCell data={loc.data} pluginUrl="/public/images/" />
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
          <th className="p-2 text-left text-sm font-medium">Location</th>
        </tr>
      </thead>
      <tbody>
        <tr className="border-b">
          <td className="p-2 text-sm">Visitor #1</td>
          <td className="p-2">
            <LocationCell
              data={{ countryCode: 'US', countryName: 'United States', cityName: 'New York' }}
              pluginUrl="/public/images/"
            />
          </td>
        </tr>
        <tr className="border-b">
          <td className="p-2 text-sm">Visitor #2</td>
          <td className="p-2">
            <LocationCell data={{ countryCode: 'GB', countryName: 'United Kingdom' }} pluginUrl="/public/images/" />
          </td>
        </tr>
        <tr className="border-b">
          <td className="p-2 text-sm">Visitor #3</td>
          <td className="p-2">
            <LocationCell
              data={{ countryCode: 'JP', countryName: 'Japan', regionName: 'Tokyo', cityName: 'Tokyo' }}
              pluginUrl="/public/images/"
            />
          </td>
        </tr>
        <tr>
          <td className="p-2 text-sm">Visitor #4</td>
          <td className="p-2">
            <LocationCell
              data={{ countryCode: 'DE', countryName: 'Germany', regionName: 'Bavaria', cityName: 'Munich' }}
              pluginUrl="/public/images/"
            />
          </td>
        </tr>
      </tbody>
    </table>
  ),
}
