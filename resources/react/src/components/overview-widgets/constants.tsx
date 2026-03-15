import { getChannelDisplayName } from '@/components/data-table-columns/source-categories-columns'
import { WordPress } from '@/lib/wordpress'

const pluginUrl = WordPress.getInstance().getPluginUrl()

// Extract dynamic param name from a TanStack Router path (e.g., '/author/$authorId' → 'authorId')
export function extractRouteParamName(routePath: string): string {
  return routePath.match(/\$(\w+)/)?.[1] || 'id'
}

// Icon render functions by type
export const ICON_RENDERERS: Record<OverviewIconType, (item: Record<string, unknown>, slugField: string) => React.ReactNode> = {
  browser: (item, field) => {
    const slug = String(item[field] || 'unknown').toLowerCase().replace(/\s+/g, '_')
    return <img src={`${pluginUrl}public/images/browser/${slug}.svg`} alt={String(item[field] || '')} className="h-4 w-4" />
  },
  os: (item, field) => {
    const slug = String(item[field] || 'unknown').toLowerCase().replace(/[\s/]+/g, '_')
    return <img src={`${pluginUrl}public/images/operating-system/${slug}.svg`} alt={String(item[field] || '')} className="h-4 w-4" />
  },
  country: (item) => {
    const code = String(item.country_code || '000').toLowerCase()
    return <img src={`${pluginUrl}public/images/flags/${code}.svg`} alt={String(item.country_name || '')} className="w-4 h-3" />
  },
  device: (item, field) => {
    const slug = String(item[field] || 'desktop').toLowerCase()
    return <img src={`${pluginUrl}public/images/device/${slug}.svg`} alt={String(item[field] || '')} className="h-4 w-4" />
  },
}

// Label transform functions by type
export const LABEL_TRANSFORMS: Record<BarListLabelTransform, (value: string) => string> = {
  'source-category': getChannelDisplayName,
}
