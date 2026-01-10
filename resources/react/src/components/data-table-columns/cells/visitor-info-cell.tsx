/**
 * VisitorInfoCell - Displays visitor information with country flag, OS, browser, and user/identifier
 */

import { memo } from 'react'

import { Badge } from '@/components/ui/badge'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'

import type { VisitorInfoConfig, VisitorInfoData } from '../types'

interface VisitorInfoCellProps {
  data: VisitorInfoData
  config: VisitorInfoConfig
}

/**
 * Format hash display: strip #hash# prefix and show first 6 chars
 */
function formatHashDisplay(value: string): string {
  const cleanHash = value.replace(/^#hash#/i, '')
  return cleanHash.substring(0, 6)
}

/**
 * Get identifier display based on hash settings
 */
function getIdentifierDisplay(identifier: string | undefined, hashEnabled: boolean): string | undefined {
  if (!identifier) return undefined

  if (hashEnabled) {
    // hashEnabled = true → show first 6 chars of hash
    if (identifier.startsWith('#hash#')) {
      return formatHashDisplay(identifier)
    }
    return formatHashDisplay(identifier)
  }
  // hashEnabled = false → show full IP address
  return identifier
}

export const VisitorInfoCell = memo(function VisitorInfoCell({ data, config }: VisitorInfoCellProps) {
  const { country, os, browser, user, identifier } = data
  const { pluginUrl, trackLoggedInEnabled, hashEnabled } = config

  // Build location text for tooltip
  const locationParts = [country.city, country.name].filter(Boolean)
  const locationText = locationParts.join(', ') || country.name

  // Determine what to show
  const showUserBadge = trackLoggedInEnabled && user
  const identifierDisplay = getIdentifierDisplay(identifier, hashEnabled)

  return (
    <div className="flex flex-col gap-0.5 group/visitor">
      {/* Row 1: Icons - muted by default, reveal on hover */}
      <div className="flex items-center gap-1">
        {/* Country Flag */}
        <Tooltip>
          <TooltipTrigger asChild>
            <button
              className="flex items-center opacity-70 grayscale-[30%] group-hover/visitor:opacity-100 group-hover/visitor:grayscale-0 transition-all duration-150"
              aria-label={`Country: ${country.name}`}
            >
              <img
                src={`${pluginUrl}public/images/flags/${country.code || '000'}.svg`}
                alt={country.name}
                className="w-3.5 h-3.5 object-contain"
              />
            </button>
          </TooltipTrigger>
          <TooltipContent>{locationText}</TooltipContent>
        </Tooltip>

        {/* OS Icon */}
        <Tooltip>
          <TooltipTrigger asChild>
            <button
              className="flex items-center opacity-60 grayscale-[40%] group-hover/visitor:opacity-100 group-hover/visitor:grayscale-0 transition-all duration-150"
              aria-label={`Operating system: ${os.name}`}
            >
              <img
                src={`${pluginUrl}public/images/operating-system/${os.icon}.svg`}
                alt={os.name}
                className="w-3 h-3 object-contain"
              />
            </button>
          </TooltipTrigger>
          <TooltipContent>{os.name}</TooltipContent>
        </Tooltip>

        {/* Browser Icon */}
        <Tooltip>
          <TooltipTrigger asChild>
            <button
              className="flex items-center opacity-60 grayscale-[40%] group-hover/visitor:opacity-100 group-hover/visitor:grayscale-0 transition-all duration-150"
              aria-label={`Browser: ${browser.name} ${browser.version}`}
            >
              <img
                src={`${pluginUrl}public/images/browser/${browser.icon}.svg`}
                alt={browser.name}
                className="w-3 h-3 object-contain"
              />
            </button>
          </TooltipTrigger>
          <TooltipContent>
            {browser.name} {browser.version}
          </TooltipContent>
        </Tooltip>
      </div>

      {/* Row 2: User Badge or Identifier */}
      {showUserBadge ? (
        <Tooltip>
          <TooltipTrigger asChild>
            <Badge variant="secondary" className="text-[11px] font-normal py-0 px-1 h-4 w-fit">
              {user!.username} #{user!.id}
            </Badge>
          </TooltipTrigger>
          <TooltipContent>
            {user!.email}
            {user!.role && ` · ${user!.role}`}
          </TooltipContent>
        </Tooltip>
      ) : (
        identifierDisplay && (
          <span className="text-[11px] text-neutral-400 font-mono tracking-wide">{identifierDisplay}</span>
        )
      )}
    </div>
  )
})
