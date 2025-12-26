import type { Meta, StoryObj } from '@storybook/react'

import type { CountryData, GlobalMapData } from './global-map'
import { GlobalMap } from './global-map'

// Sample data - flags will be loaded from server when pluginUrl is provided
const sampleCountries: CountryData[] = [
  { code: 'US', name: 'United States', visitors: 25000 },
  { code: 'FR', name: 'France', visitors: 23000 },
  { code: 'GB', name: 'United Kingdom', visitors: 18000 },
  { code: 'DE', name: 'Germany', visitors: 15000 },
  { code: 'CA', name: 'Canada', visitors: 12000 },
  { code: 'IR', name: 'Iran', visitors: 11000 },
  { code: 'AU', name: 'Australia', visitors: 10000 },
  { code: 'JP', name: 'Japan', visitors: 9000 },
  { code: 'IN', name: 'India', visitors: 8000 },
  { code: 'BR', name: 'Brazil', visitors: 7000 },
  { code: 'IT', name: 'Italy', visitors: 6500 },
  { code: 'ES', name: 'Spain', visitors: 6000 },
  { code: 'MX', name: 'Mexico', visitors: 5500 },
  { code: 'NL', name: 'Netherlands', visitors: 5000 },
  { code: 'SE', name: 'Sweden', visitors: 4500 },
  { code: 'CH', name: 'Switzerland', visitors: 4000 },
  { code: 'TR', name: 'Turkey', visitors: 3800 },
  { code: 'BE', name: 'Belgium', visitors: 3500 },
  { code: 'PL', name: 'Poland', visitors: 3000 },
  { code: 'AT', name: 'Austria', visitors: 2500 },
  { code: 'NO', name: 'Norway', visitors: 2000 },
  { code: 'DK', name: 'Denmark', visitors: 1800 },
  { code: 'FI', name: 'Finland', visitors: 1500 },
  { code: 'IE', name: 'Ireland', visitors: 1200 },
  { code: 'PT', name: 'Portugal', visitors: 1000 },
  { code: 'GR', name: 'Greece', visitors: 900 },
  { code: 'CZ', name: 'Czech Republic', visitors: 800 },
  { code: 'RO', name: 'Romania', visitors: 700 },
  { code: 'HU', name: 'Hungary', visitors: 600 },
  { code: 'NZ', name: 'New Zealand', visitors: 500 },
  { code: 'SG', name: 'Singapore', visitors: 450 },
  { code: 'ZA', name: 'South Africa', visitors: 400 },
]

const sampleData: GlobalMapData = {
  countries: sampleCountries,
}

const meta = {
  title: 'Custom/GlobalMap',
  component: GlobalMap,
  parameters: {
    layout: 'padded',
  },
  tags: ['autodocs'],
  argTypes: {
    title: {
      control: 'text',
      description: 'Optional title for the map',
    },
    metric: {
      control: 'text',
      description: 'The metric being visualized (e.g., Visitors, Views)',
    },
    showZoomControls: {
      control: 'boolean',
      description: 'Show/hide zoom in/out buttons',
    },
    showLegend: {
      control: 'boolean',
      description: 'Show/hide color scale legend',
    },
    pluginUrl: {
      control: 'text',
      description: 'Base URL for flag images',
    },
    enableCityDrilldown: {
      control: 'boolean',
      description: 'Enable clicking countries to view cities',
    },
    enableMetricToggle: {
      control: 'boolean',
      description: 'Enable metric toggle UI',
    },
    availableMetrics: {
      control: 'object',
      description: 'Available metrics for toggle',
    },
  },
} satisfies Meta<typeof GlobalMap>

export default meta
type Story = StoryObj<typeof meta>

export const Default: Story = {
  args: {
    data: sampleData,
  },
}

export const WithTitle: Story = {
  args: {
    data: sampleData,
    title: 'Global Visitor Distribution',
  },
}

export const CustomMetric: Story = {
  args: {
    data: sampleData,
    metric: 'Page Views',
    title: 'Global Page Views',
  },
}

export const WithoutZoomControls: Story = {
  args: {
    data: sampleData,
    showZoomControls: false,
  },
}

export const WithoutLegend: Story = {
  args: {
    data: sampleData,
    showLegend: false,
  },
}

export const MinimalConfiguration: Story = {
  args: {
    data: sampleData,
    showZoomControls: false,
    showLegend: false,
  },
}

export const FullFeatured: Story = {
  args: {
    data: sampleData,
    title: 'Global Visitor Distribution',
    metric: 'Visitors',
    showZoomControls: true,
    showLegend: true,
  },
}

export const EmptyData: Story = {
  args: {
    data: { countries: [] },
    title: 'No Data Available',
  },
}

export const LimitedData: Story = {
  args: {
    data: {
      countries: [
        { code: 'US', name: 'United States', visitors: 15000 },
        { code: 'FR', name: 'France', visitors: 8000 },
        { code: 'GB', name: 'United Kingdom', visitors: 5000 },
      ],
    },
    title: 'Top 3 Countries',
  },
}

/**
 * Interactive Features Stories
 */

export const WithCityDrilldown: Story = {
  args: {
    data: sampleData,
    title: 'Interactive Map with City Drilldown',
    enableCityDrilldown: true,
    enableMetricToggle: false,
    dateFrom: '2024-01-01',
    dateTo: '2024-12-31',
  },
  parameters: {
    docs: {
      description: {
        story:
          'Click on a country to zoom in and view regions. Note: Region data is fetched from API when a country is clicked. In Storybook, the API call will fail, but the interaction behavior can be tested.',
      },
    },
  },
}

export const WithMetricToggle: Story = {
  args: {
    data: sampleData,
    title: 'Map with Metric Toggle',
    enableCityDrilldown: false,
    enableMetricToggle: true,
    availableMetrics: [
      { value: 'visitors', label: 'Visitors' },
      { value: 'views', label: 'Views' },
    ],
  },
  parameters: {
    docs: {
      description: {
        story:
          'Toggle between different metrics (Visitors, Views). The metric toggle allows switching between different data views.',
      },
    },
  },
}

export const FullyInteractive: Story = {
  args: {
    data: sampleData,
    title: 'Fully Interactive Global Map',
    enableCityDrilldown: true,
    enableMetricToggle: true,
    availableMetrics: [
      { value: 'visitors', label: 'Visitors' },
      { value: 'views', label: 'Views' },
    ],
    showZoomControls: true,
    showLegend: true,
    dateFrom: '2024-01-01',
    dateTo: '2024-12-31',
  },
  parameters: {
    docs: {
      description: {
        story:
          'Combines all interactive features: region drilldown, metric toggle, zoom controls, and legend. This is the recommended configuration for maximum interactivity.',
      },
    },
  },
}

export const DisabledInteractivity: Story = {
  args: {
    data: sampleData,
    title: 'Static Map (No Interactivity)',
    enableCityDrilldown: false,
    enableMetricToggle: false,
    showZoomControls: true,
    showLegend: true,
  },
  parameters: {
    docs: {
      description: {
        story:
          'Map with interactivity disabled. Countries can still be hovered for tooltips, but clicking does nothing.',
      },
    },
  },
}

export const SingleMetric: Story = {
  args: {
    data: sampleData,
    title: 'Map with Single Metric',
    enableMetricToggle: true,
    availableMetrics: [{ value: 'visitors', label: 'Visitors' }],
  },
  parameters: {
    docs: {
      description: {
        story: 'When only one metric is available, the metric toggle is automatically hidden.',
      },
    },
  },
}

export const WithProvinceView: Story = {
  args: {
    data: sampleData,
    title: 'Province/Region Drilldown',
    enableCityDrilldown: true,
    enableMetricToggle: true,
    availableMetrics: [
      { value: 'visitors', label: 'Visitors' },
      { value: 'views', label: 'Views' },
    ],
    showZoomControls: true,
    showLegend: true,
    dateFrom: '2024-01-01',
    dateTo: '2024-12-31',
  },
  parameters: {
    docs: {
      description: {
        story:
          'Click on any country (e.g., Iran, US, Germany) to zoom in and view province/region boundaries. Each province shows visitor data on hover. The back button includes the country flag for easy navigation. Province boundaries are loaded from Natural Earth data.',
      },
    },
  },
}

/**
 * Countries Only View - No city drilldown
 */
export const CountriesOnlyView: Story = {
  args: {
    data: sampleData,
    title: 'Countries Overview',
    enableCityDrilldown: false,
    enableMetricToggle: true,
    availableMetrics: [
      { value: 'visitors', label: 'Visitors' },
      { value: 'views', label: 'Views' },
    ],
    showZoomControls: true,
    showLegend: true,
  },
  parameters: {
    docs: {
      description: {
        story:
          'Shows only the world map with countries. City drilldown is disabled - clicking on countries will not zoom in. Useful for high-level geographic overview without city-level detail.',
      },
    },
  },
}

/**
 * Pre-zoomed to show a specific country's cities/regions
 * Note: In real usage, clicking a country triggers the zoom. This story demonstrates the zoomed-in state.
 */
export const CountryCitiesView: Story = {
  args: {
    data: {
      countries: [{ code: 'IR', name: 'Iran', visitors: 11000 }],
    },
    title: 'Iran - Regional View',
    enableCityDrilldown: true,
    enableMetricToggle: true,
    availableMetrics: [
      { value: 'visitors', label: 'Visitors' },
      { value: 'views', label: 'Views' },
    ],
    showZoomControls: true,
    showLegend: true,
    dateFrom: '2024-01-01',
    dateTo: '2024-12-31',
  },
  parameters: {
    docs: {
      description: {
        story:
          'Click on Iran to see province boundaries with visitor data. Each province (Tehran, Isfahan, Fars, etc.) can be hovered to see statistics. The back button shows the Iranian flag for easy navigation back to world view.',
      },
    },
  },
}

export const USRegionalView: Story = {
  args: {
    data: {
      countries: [{ code: 'US', name: 'United States', visitors: 25000 }],
    },
    title: 'United States - State View',
    enableCityDrilldown: true,
    enableMetricToggle: true,
    availableMetrics: [
      { value: 'visitors', label: 'Visitors' },
      { value: 'views', label: 'Views' },
    ],
    showZoomControls: true,
    showLegend: true,
    dateFrom: '2024-01-01',
    dateTo: '2024-12-31',
  },
  parameters: {
    docs: {
      description: {
        story:
          'Click on United States to see state boundaries. Hover over states like California, New York, Texas to see visitor statistics for each state.',
      },
    },
  },
}

/**
 * Dynamic Zoom Feature
 * Demonstrates how the map automatically calculates optimal zoom levels based on country size
 */
export const DynamicZoom: Story = {
  args: {
    data: {
      countries: [
        { code: 'RU', name: 'Russia', visitors: 15000 },
        { code: 'CN', name: 'China', visitors: 12000 },
        { code: 'IR', name: 'Iran', visitors: 11000 },
        { code: 'NL', name: 'Netherlands', visitors: 5000 },
        { code: 'SG', name: 'Singapore', visitors: 450 },
      ],
    },
    title: 'Dynamic Zoom Demonstration',
    enableCityDrilldown: true,
    enableMetricToggle: false,
    showZoomControls: true,
    showLegend: true,
    dateFrom: '2024-01-01',
    dateTo: '2024-12-31',
  },
  parameters: {
    docs: {
      description: {
        story:
          "Click on different countries to see how the map automatically adjusts zoom levels based on geographic size. The component uses `calculateZoomForBounds` to analyze each country's bounding box dimensions and apply appropriate zoom:\n\n" +
          '- **Russia** (very large, >60° dimension): Zooms to 3.5x - shows the entire country without filling viewport\n' +
          '- **China** (large, 40-60° dimension): Zooms to 4.5x - balanced view of the country\n' +
          '- **Iran** (medium, 25-40° dimension): Zooms to 6x - comfortable regional view\n' +
          '- **Netherlands** (small, 10-25° dimension): Zooms to 8x - detailed view without over-zooming\n' +
          '- **Singapore** (very small, <5° dimension): Zooms to 14x - maximum detail for tiny countries\n\n' +
          'This dynamic approach eliminates the need for hardcoded country IDs and ensures every country displays at an optimal zoom level, preventing issues like countries filling the entire viewport or being too zoomed out.',
      },
    },
  },
}
