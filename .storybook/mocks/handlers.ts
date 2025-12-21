import { http, HttpResponse, delay } from 'msw'

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
  // Handle WordPress AJAX requests for filter options
  http.post('*/admin-ajax.php', async ({ request }) => {
    const url = new URL(request.url)
    const action = url.searchParams.get('action')

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
