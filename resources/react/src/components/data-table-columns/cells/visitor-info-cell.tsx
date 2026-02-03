/**
 * VisitorInfoCell - Displays visitor information with country flag, OS, browser, and user/identifier
 * Includes linking to single visitor report page.
 */

import { Link, useLocation } from '@tanstack/react-router'
import { memo } from 'react'

import { Badge } from '@/components/ui/badge'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'

import type { VisitorInfoConfig, VisitorInfoData } from '../types'

interface VisitorInfoCellProps {
  data: VisitorInfoData
  config: VisitorInfoConfig
  /** Disable linking to single visitor page (e.g., when already on that page) */
  disableLink?: boolean
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

  // hashEnabled = true → show first 6 chars of hash
  // hashEnabled = false → show full IP address
  return hashEnabled ? formatHashDisplay(identifier) : identifier
}

/**
 * Get the link destination for a visitor based on available identifiers.
 * Priority: user_id > ip_address > visitor_hash
 */
function getVisitorLink(
  data: VisitorInfoData
): { type: 'user' | 'ip' | 'hash'; id: string } | null {
  if (data.user?.id) {
    return { type: 'user', id: String(data.user.id) }
  }
  if (data.ipAddress) {
    return { type: 'ip', id: data.ipAddress }
  }
  if (data.visitorHash) {
    return { type: 'hash', id: data.visitorHash }
  }
  return null
}

export const VisitorInfoCell = memo(function VisitorInfoCell({ data, config, disableLink = false }: VisitorInfoCellProps) {
  const { country, os, browser, user, identifier } = data
  const { pluginUrl, hashEnabled } = config

  // Get current route pathname for back navigation
  const { pathname } = useLocation()

  // Get visitor link info
  const visitorLink = !disableLink ? getVisitorLink(data) : null

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

      {/* Row 2: User Badge or Identifier (clickable link to single visitor page) */}
      {showUserBadge ? (
        visitorLink ? (
          <Link
            to="/visitor/$type/$id"
            params={{ type: visitorLink.type, id: visitorLink.id }}
            search={{ from: pathname }}
            className="w-fit"
          >
            <Badge
              variant="secondary"
              className="text-[11px] font-normal py-0 px-1 h-4 w-fit cursor-pointer hover:bg-neutral-200 transition-colors"
            >
              {user!.username} #{user!.id}
            </Badge>
          </Link>
        ) : (
          <Badge variant="secondary" className="text-[11px] font-normal py-0 px-1 h-4 w-fit">
            {user!.username} #{user!.id}
          </Badge>
        )
      ) : identifierDisplay ? (
        visitorLink ? (
          <Link
            to="/visitor/$type/$id"
            params={{ type: visitorLink.type, id: visitorLink.id }}
            search={{ from: pathname }}
            className="text-[11px] text-neutral-500 font-mono tracking-wide hover:text-primary hover:underline cursor-pointer"
          >
            {identifierDisplay}
          </Link>
        ) : (
          <span className="text-[11px] text-neutral-500 font-mono tracking-wide">{identifierDisplay}</span>
        )
      ) : null}
    </div>
  )
})
