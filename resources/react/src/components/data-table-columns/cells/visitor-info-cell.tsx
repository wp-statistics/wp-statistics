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

  // Build full tooltip content
  const browserInfo = browser.version ? `${browser.name} ${browser.version}` : browser.name
  const tooltipContent = [locationText, browserInfo, os.name].filter(Boolean).join(' · ')

  // Determine what to show - always show user badge if user exists (has user_id)
  const showUserBadge = !!user
  const identifierDisplay = getIdentifierDisplay(identifier, hashEnabled)

  return (
    <div className="flex flex-col gap-0.5">
      {/* Row 1: Flag + Browser/OS text with single tooltip */}
      <Tooltip>
        <TooltipTrigger asChild>
          <div className="flex items-center gap-1.5 cursor-default">
            <img
              src={`${pluginUrl}public/images/flags/${country.code || '000'}.svg`}
              alt={country.name}
              className="w-3.5 h-3.5 object-contain shrink-0"
            />
            <span className="text-xs text-muted-foreground truncate">
              {browser.name} · {os.name}
            </span>
          </div>
        </TooltipTrigger>
        <TooltipContent>
          <div className="flex flex-col">
            <span>{tooltipContent}</span>
            {user?.role && <span>{user.role}</span>}
          </div>
        </TooltipContent>
      </Tooltip>

      {/* Row 2: User Badge or Identifier */}
      {showUserBadge ? (
        <Badge variant="secondary" className="text-[11px] font-normal py-0 px-1 h-4 w-fit">
          {user!.username} #{user!.id}
        </Badge>
      ) : (
        identifierDisplay && (
          <span className="text-[11px] text-neutral-500 font-mono tracking-wide">{identifierDisplay}</span>
        )
      )}
    </div>
  )
})
