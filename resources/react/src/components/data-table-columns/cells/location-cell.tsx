/**
 * LocationCell - Displays location with flag icon and smart text fallback
 * Format: Flag + "City, Country" | "Region, Country" | "Country"
 */

import { memo } from 'react'

import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'

export interface LocationData {
  countryCode: string
  countryName: string
  regionName?: string
  cityName?: string
}

interface LocationCellProps {
  data: LocationData
  pluginUrl: string
}

/**
 * Build location display text with smart fallback
 */
function getLocationText(data: LocationData): string {
  const { cityName, regionName, countryName } = data

  if (cityName) {
    return `${cityName}, ${countryName}`
  }
  if (regionName) {
    return `${regionName}, ${countryName}`
  }
  return countryName
}

/**
 * Build full tooltip text showing all available location parts
 */
function getTooltipText(data: LocationData): string {
  const parts = [data.cityName, data.regionName, data.countryName].filter(Boolean)
  return parts.join(', ') || 'Unknown'
}

export const LocationCell = memo(function LocationCell({ data, pluginUrl }: LocationCellProps) {
  const locationText = getLocationText(data)
  const tooltipText = getTooltipText(data)
  const flagPath = `${pluginUrl}public/images/flags/${data.countryCode || '000'}.svg`

  return (
    <Tooltip>
      <TooltipTrigger asChild>
        <div className="flex items-center gap-1.5 cursor-default">
          <img
            src={flagPath}
            alt={data.countryName}
            className="w-3.5 h-3.5 object-contain shrink-0"
          />
          <span className="text-xs text-muted-foreground truncate">{locationText}</span>
        </div>
      </TooltipTrigger>
      <TooltipContent>{tooltipText}</TooltipContent>
    </Tooltip>
  )
})
