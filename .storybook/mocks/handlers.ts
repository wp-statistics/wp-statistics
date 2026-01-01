import { http, HttpResponse, delay } from 'msw'

// Mock region data for GlobalMap stories
const mockRegionData: Record<string, Array<{ region_name: string; region_code: string; country_code: string; country_name: string; visitors: number; views: number }>> = {
  US: [
    { region_name: 'California', region_code: 'CA', country_code: 'US', country_name: 'United States', visitors: 5200, views: 12500 },
    { region_name: 'Texas', region_code: 'TX', country_code: 'US', country_name: 'United States', visitors: 3800, views: 9200 },
    { region_name: 'New York', region_code: 'NY', country_code: 'US', country_name: 'United States', visitors: 3500, views: 8100 },
    { region_name: 'Florida', region_code: 'FL', country_code: 'US', country_name: 'United States', visitors: 2900, views: 6800 },
    { region_name: 'Illinois', region_code: 'IL', country_code: 'US', country_name: 'United States', visitors: 1800, views: 4200 },
    { region_name: 'Pennsylvania', region_code: 'PA', country_code: 'US', country_name: 'United States', visitors: 1500, views: 3600 },
    { region_name: 'Ohio', region_code: 'OH', country_code: 'US', country_name: 'United States', visitors: 1200, views: 2900 },
    { region_name: 'Georgia', region_code: 'GA', country_code: 'US', country_name: 'United States', visitors: 1100, views: 2600 },
    { region_name: 'Washington', region_code: 'WA', country_code: 'US', country_name: 'United States', visitors: 950, views: 2200 },
    { region_name: 'Massachusetts', region_code: 'MA', country_code: 'US', country_name: 'United States', visitors: 850, views: 2000 },
  ],
  IR: [
    { region_name: 'Tehran', region_code: 'THR', country_code: 'IR', country_name: 'Iran', visitors: 4500, views: 11000 },
    { region_name: 'Isfahan', region_code: 'ISF', country_code: 'IR', country_name: 'Iran', visitors: 1800, views: 4200 },
    { region_name: 'Fars', region_code: 'FAR', country_code: 'IR', country_name: 'Iran', visitors: 1200, views: 2800 },
    { region_name: 'Khorasan Razavi', region_code: 'KHR', country_code: 'IR', country_name: 'Iran', visitors: 900, views: 2100 },
    { region_name: 'East Azerbaijan', region_code: 'EAZ', country_code: 'IR', country_name: 'Iran', visitors: 750, views: 1800 },
    { region_name: 'Khuzestan', region_code: 'KHU', country_code: 'IR', country_name: 'Iran', visitors: 600, views: 1400 },
    { region_name: 'Mazandaran', region_code: 'MAZ', country_code: 'IR', country_name: 'Iran', visitors: 500, views: 1200 },
    { region_name: 'Alborz', region_code: 'ALB', country_code: 'IR', country_name: 'Iran', visitors: 450, views: 1100 },
  ],
  GB: [
    { region_name: 'England', region_code: 'ENG', country_code: 'GB', country_name: 'United Kingdom', visitors: 12000, views: 28000 },
    { region_name: 'Scotland', region_code: 'SCT', country_code: 'GB', country_name: 'United Kingdom', visitors: 2500, views: 6000 },
    { region_name: 'Wales', region_code: 'WLS', country_code: 'GB', country_name: 'United Kingdom', visitors: 1800, views: 4200 },
    { region_name: 'Northern Ireland', region_code: 'NIR', country_code: 'GB', country_name: 'United Kingdom', visitors: 700, views: 1600 },
  ],
  DE: [
    { region_name: 'Bavaria', region_code: 'BY', country_code: 'DE', country_name: 'Germany', visitors: 3200, views: 7500 },
    { region_name: 'North Rhine-Westphalia', region_code: 'NW', country_code: 'DE', country_name: 'Germany', visitors: 2800, views: 6500 },
    { region_name: 'Baden-Württemberg', region_code: 'BW', country_code: 'DE', country_name: 'Germany', visitors: 2400, views: 5600 },
    { region_name: 'Lower Saxony', region_code: 'NI', country_code: 'DE', country_name: 'Germany', visitors: 1500, views: 3500 },
    { region_name: 'Hesse', region_code: 'HE', country_code: 'DE', country_name: 'Germany', visitors: 1200, views: 2800 },
    { region_name: 'Berlin', region_code: 'BE', country_code: 'DE', country_name: 'Germany', visitors: 1100, views: 2600 },
  ],
  FR: [
    { region_name: 'Île-de-France', region_code: 'IDF', country_code: 'FR', country_name: 'France', visitors: 8500, views: 20000 },
    { region_name: 'Auvergne-Rhône-Alpes', region_code: 'ARA', country_code: 'FR', country_name: 'France', visitors: 3200, views: 7500 },
    { region_name: 'Provence-Alpes-Côte d\'Azur', region_code: 'PAC', country_code: 'FR', country_name: 'France', visitors: 2800, views: 6500 },
    { region_name: 'Occitanie', region_code: 'OCC', country_code: 'FR', country_name: 'France', visitors: 2100, views: 4900 },
    { region_name: 'Nouvelle-Aquitaine', region_code: 'NAQ', country_code: 'FR', country_name: 'France', visitors: 1800, views: 4200 },
  ],
  RU: [
    { region_name: 'Moscow', region_code: 'MOW', country_code: 'RU', country_name: 'Russia', visitors: 5500, views: 13000 },
    { region_name: 'Saint Petersburg', region_code: 'SPE', country_code: 'RU', country_name: 'Russia', visitors: 2200, views: 5200 },
    { region_name: 'Moscow Oblast', region_code: 'MOS', country_code: 'RU', country_name: 'Russia', visitors: 1800, views: 4200 },
    { region_name: 'Krasnodar Krai', region_code: 'KDA', country_code: 'RU', country_name: 'Russia', visitors: 1200, views: 2800 },
  ],
  CN: [
    { region_name: 'Guangdong', region_code: 'GD', country_code: 'CN', country_name: 'China', visitors: 3800, views: 9000 },
    { region_name: 'Beijing', region_code: 'BJ', country_code: 'CN', country_name: 'China', visitors: 2500, views: 5800 },
    { region_name: 'Shanghai', region_code: 'SH', country_code: 'CN', country_name: 'China', visitors: 2200, views: 5200 },
    { region_name: 'Zhejiang', region_code: 'ZJ', country_code: 'CN', country_name: 'China', visitors: 1500, views: 3500 },
  ],
  NL: [
    { region_name: 'North Holland', region_code: 'NH', country_code: 'NL', country_name: 'Netherlands', visitors: 2200, views: 5200 },
    { region_name: 'South Holland', region_code: 'ZH', country_code: 'NL', country_name: 'Netherlands', visitors: 1800, views: 4200 },
    { region_name: 'North Brabant', region_code: 'NB', country_code: 'NL', country_name: 'Netherlands', visitors: 600, views: 1400 },
  ],
  SG: [
    { region_name: 'Central Region', region_code: 'CR', country_code: 'SG', country_name: 'Singapore', visitors: 280, views: 650 },
    { region_name: 'East Region', region_code: 'ER', country_code: 'SG', country_name: 'Singapore', visitors: 85, views: 200 },
    { region_name: 'West Region', region_code: 'WR', country_code: 'SG', country_name: 'Singapore', visitors: 55, views: 130 },
  ],
}

// Mock data for searchable filters
const mockSearchableData: Record<string, Array<{ value: string; label: string }>> = {
  country: [
    { value: 'us', label: 'United States' },
    { value: 'gb', label: 'United Kingdom' },
    { value: 'de', label: 'Germany' },
    { value: 'fr', label: 'France' },
    { value: 'jp', label: 'Japan' },
    { value: 'ca', label: 'Canada' },
    { value: 'au', label: 'Australia' },
    { value: 'ir', label: 'Iran' },
    { value: 'in', label: 'India' },
    { value: 'br', label: 'Brazil' },
    { value: 'it', label: 'Italy' },
    { value: 'es', label: 'Spain' },
    { value: 'nl', label: 'Netherlands' },
    { value: 'se', label: 'Sweden' },
    { value: 'ch', label: 'Switzerland' },
  ],
  browser: [
    { value: 'chrome', label: 'Chrome' },
    { value: 'firefox', label: 'Firefox' },
    { value: 'safari', label: 'Safari' },
    { value: 'edge', label: 'Edge' },
    { value: 'opera', label: 'Opera' },
    { value: 'brave', label: 'Brave' },
    { value: 'vivaldi', label: 'Vivaldi' },
    { value: 'arc', label: 'Arc' },
  ],
  os: [
    { value: 'windows', label: 'Windows' },
    { value: 'macos', label: 'macOS' },
    { value: 'linux', label: 'Linux' },
    { value: 'android', label: 'Android' },
    { value: 'ios', label: 'iOS' },
    { value: 'chromeos', label: 'Chrome OS' },
    { value: 'ubuntu', label: 'Ubuntu' },
  ],
}

export const handlers = [
  // Handle WordPress AJAX requests for analytics (GlobalMap regions)
  http.post('*/admin-ajax.php', async ({ request }) => {
    const url = new URL(request.url)
    const action = url.searchParams.get('action')

    // Handle analytics API for GlobalMap regions
    if (action === 'wp_statistics_analytics') {
      const body = await request.json() as { group_by?: string[]; filters?: { country?: { is?: string } } }
      const { group_by, filters } = body

      // Check if this is a region request
      if (group_by?.includes('region') && filters?.country?.is) {
        const countryCode = filters.country.is.toUpperCase()
        const regions = mockRegionData[countryCode] || []

        // Simulate network delay
        await delay(200)

        return HttpResponse.json({
          success: true,
          data: {
            rows: regions,
            totals: {
              visitors: regions.reduce((sum, r) => sum + r.visitors, 0),
              views: regions.reduce((sum, r) => sum + r.views, 0),
            },
          },
          meta: {
            total_rows: regions.length,
            total_pages: 1,
            page: 1,
            per_page: 100,
          },
        })
      }

      // Return empty data for other analytics requests
      return HttpResponse.json({
        success: true,
        data: { rows: [] },
        meta: { total_rows: 0, total_pages: 0, page: 1, per_page: 100 },
      })
    }

    if (action === 'wp_statistics_get_filter_options') {
      const body = await request.json() as { filter?: string; search?: string; limit?: number }
      const { filter, search, limit = 20 } = body

      // Simulate network delay
      await delay(150)

      const allOptions = mockSearchableData[filter || ''] || []
      const filteredOptions = search
        ? allOptions.filter((opt) => opt.label.toLowerCase().includes(search.toLowerCase()))
        : allOptions

      return HttpResponse.json({
        success: true,
        options: filteredOptions.slice(0, limit),
      })
    }

    // Return error for unhandled actions
    return HttpResponse.json({ success: false, message: 'Unknown action' }, { status: 400 })
  }),
]
