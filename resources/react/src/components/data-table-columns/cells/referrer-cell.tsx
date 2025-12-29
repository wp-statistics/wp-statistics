/**
 * ReferrerCell - Displays referrer domain with link and category badge
 * Always shows category badge, with optional domain link above it
 */

import { Badge } from '@/components/ui/badge'
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

export function ReferrerCell({ data, maxLength = 22 }: ReferrerCellProps) {
  const { domain, category } = data

  return (
    <div className="flex flex-col items-start">
      {domain && (
        <Tooltip>
          <TooltipTrigger asChild>
            <a
              href={`https://${domain}`}
              target="_blank"
              rel="noopener noreferrer"
              className="hover:underline max-w-[180px] truncate block text-xs text-neutral-700"
            >
              {truncateDomain(domain, maxLength)}
            </a>
          </TooltipTrigger>
          <TooltipContent>{domain}</TooltipContent>
        </Tooltip>
      )}
      <Badge variant="outline" className="text-[10px] text-neutral-500 uppercase mt-1">
        {category}
      </Badge>
    </div>
  )
}
