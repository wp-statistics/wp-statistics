/**
 * ReferrerCell - Displays referrer domain with category
 * Shows domain link with category below, or just category for direct traffic
 */

import { memo } from 'react'

import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'

import type { ReferrerData } from '../types'

interface ReferrerCellProps {
  data: ReferrerData
  maxLength?: number
}

/**
 * Truncate domain while preserving suffix
 */
function truncateDomain(domain: string, maxLength: number = 22): string {
  if (domain.length <= maxLength) return domain
  const parts = domain.split('.')
  const suffix = parts.length > 1 ? `.${parts[parts.length - 1]}` : ''
  const maxPrefixLength = maxLength - suffix.length - 1
  return `${domain.substring(0, maxPrefixLength)}…${suffix}`
}

/**
 * Convert string to title case (handles UPPERCASE and lowercase)
 */
function toTitleCase(str: string): string {
  return str
    .toLowerCase()
    .split(' ')
    .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ')
}

export const ReferrerCell = memo(function ReferrerCell({ data, maxLength = 22 }: ReferrerCellProps) {
  const { domain, category } = data
  const formattedCategory = toTitleCase(category)

  // Direct traffic - no domain, show category as muted text
  if (!domain) {
    return <span className="text-xs text-neutral-500">{formattedCategory}</span>
  }

  return (
    <div className="flex flex-col">
      <Tooltip>
        <TooltipTrigger asChild>
          <a
            href={`https://${domain}`}
            target="_blank"
            rel="noopener noreferrer"
            className="text-xs font-medium text-neutral-700 truncate hover:underline"
          >
            {truncateDomain(domain, maxLength)}
          </a>
        </TooltipTrigger>
        <TooltipContent>{domain}</TooltipContent>
      </Tooltip>
      <span className="text-xs text-neutral-500">{formattedCategory}</span>
    </div>
  )
})
