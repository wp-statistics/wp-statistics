/**
 * LocationCell - Displays location with flag icon and smart text fallback
 * Format: Flag + "City, Country" | "Region, Country" | "Country"
 */

import { Link } from '@tanstack/react-router'
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
  /** Optional link destination (e.g., '/country/$countryCode') */
  linkTo?: string
  /** Optional link params (e.g., { countryCode: 'US' }) */
  linkParams?: Record<string, string>
  /** Optional search params to append to the link (e.g., { from: '/european-countries' }) */
  linkSearch?: Record<string, string>
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

export const LocationCell = memo(function LocationCell({ data, pluginUrl, linkTo, linkParams, linkSearch }: LocationCellProps) {
  const locationText = getLocationText(data)
  const tooltipText = getTooltipText(data)
  const flagPath = `${pluginUrl}public/images/flags/${data.countryCode || '000'}.svg`

  const content = (
    <div className={`flex items-center gap-1.5 ${linkTo ? 'cursor-pointer' : 'cursor-default'}`}>
      <img
        src={flagPath}
        alt={data.countryName}
        className="w-3.5 h-3.5 object-contain shrink-0"
      />
      <span className={`text-xs truncate ${linkTo ? 'text-neutral-700 hover:text-primary hover:underline cursor-pointer' : 'text-muted-foreground'}`}>
        {locationText}
      </span>
    </div>
  )

  return (
    <Tooltip>
      <TooltipTrigger asChild>
        {linkTo ? (
          // eslint-disable-next-line @typescript-eslint/no-explicit-any
          <Link to={linkTo as any} params={linkParams} search={linkSearch}>
            {content}
          </Link>
        ) : (
          content
        )}
      </TooltipTrigger>
      <TooltipContent>{tooltipText}</TooltipContent>
    </Tooltip>
  )
})
