/**
 * EntryPageCell - Displays entry page with query string indicator and UTM campaign
 */

import { Link } from '@tanstack/react-router'
import { Info } from 'lucide-react'
import { memo } from 'react'

import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'

import type { PageData } from '../types'

interface EntryPageCellProps {
  data: PageData
  maxLength?: number
  /** Optional internal link to single content report */
  internalLinkTo?: string
  /** Optional params for internal link (e.g., { postId: '123' }) */
  internalLinkParams?: Record<string, string>
}

export const EntryPageCell = memo(function EntryPageCell({
  data,
  maxLength = 28,
  internalLinkTo,
  internalLinkParams,
}: EntryPageCellProps) {
  const { title, url, hasQueryString, queryString, utmCampaign } = data
  const truncatedTitle = title.length > maxLength ? `${title.substring(0, maxLength - 3)}...` : title

  // Title content - either plain text or internal link
  const titleContent = internalLinkTo ? (
    <Link
      to={internalLinkTo}
      params={internalLinkParams}
      className="truncate text-xs text-neutral-700 hover:text-primary hover:underline"
    >
      {truncatedTitle}
    </Link>
  ) : (
    <span className="truncate text-xs text-neutral-700">{truncatedTitle}</span>
  )

  return (
    <div className="max-w-[140px]">
      <Tooltip>
        <TooltipTrigger asChild>
          <div className="flex items-center gap-1 cursor-pointer">
            {titleContent}
            {hasQueryString && <Info className="h-3.5 w-3.5 text-neutral-400 shrink-0" />}
          </div>
        </TooltipTrigger>
        <TooltipContent>{hasQueryString && queryString ? queryString : url}</TooltipContent>
      </Tooltip>
      {utmCampaign && <span className="text-xs text-neutral-500 block mt-0.5">{utmCampaign}</span>}
    </div>
  )
})
