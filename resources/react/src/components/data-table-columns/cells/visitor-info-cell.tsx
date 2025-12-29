/**
 * VisitorInfoCell - Displays visitor information with country flag, OS, browser, and user/identifier
 */

import { Badge } from '@/components/ui/badge'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'

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
function getIdentifierDisplay(
  identifier: string | undefined,
  hashEnabled: boolean
): string | undefined {
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

export function VisitorInfoCell({ data, config }: VisitorInfoCellProps) {
  const { country, os, browser, user, identifier } = data
  const { pluginUrl, trackLoggedInEnabled, hashEnabled } = config

  // Build location text for tooltip
  const locationParts = [country.city, country.name].filter(Boolean)
  const locationText = locationParts.join(', ') || country.name

  // Determine what to show
  const showUserBadge = trackLoggedInEnabled && user
  const identifierDisplay = getIdentifierDisplay(identifier, hashEnabled)

  return (
    <div className="flex items-center gap-1.5">
      {/* Country Flag */}
      <TooltipProvider>
        <Tooltip>
          <TooltipTrigger asChild>
            <button className="flex items-center">
              <img
                src={`${pluginUrl}public/images/flags/${country.code || '000'}.svg`}
                alt={country.name}
                className="w-4 h-4 object-contain"
              />
            </button>
          </TooltipTrigger>
          <TooltipContent>{locationText}</TooltipContent>
        </Tooltip>
      </TooltipProvider>

      {/* OS Icon */}
      <TooltipProvider>
        <Tooltip>
          <TooltipTrigger asChild>
            <button className="flex items-center">
              <img
                src={`${pluginUrl}public/images/operating-system/${os.icon}.svg`}
                alt={os.name}
                className="w-3.5 h-3.5 object-contain"
              />
            </button>
          </TooltipTrigger>
          <TooltipContent>{os.name}</TooltipContent>
        </Tooltip>
      </TooltipProvider>

      {/* Browser Icon */}
      <TooltipProvider>
        <Tooltip>
          <TooltipTrigger asChild>
            <button className="flex items-center">
              <img
                src={`${pluginUrl}public/images/browser/${browser.icon}.svg`}
                alt={browser.name}
                className="w-3.5 h-3.5 object-contain"
              />
            </button>
          </TooltipTrigger>
          <TooltipContent>
            {browser.name} {browser.version}
          </TooltipContent>
        </Tooltip>
      </TooltipProvider>

      {/* User Badge or Identifier */}
      {showUserBadge ? (
        <TooltipProvider>
          <Tooltip>
            <TooltipTrigger asChild>
              <Badge variant="secondary" className="text-[10px] font-normal py-0 px-1.5 h-5">
                {user!.username} #{user!.id}
              </Badge>
            </TooltipTrigger>
            <TooltipContent>
              {user!.email}
              {user!.role && ` · ${user!.role}`}
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>
      ) : (
        identifierDisplay && (
          <span className="text-[10px] text-muted-foreground font-mono">{identifierDisplay}</span>
        )
      )}
    </div>
  )
}
