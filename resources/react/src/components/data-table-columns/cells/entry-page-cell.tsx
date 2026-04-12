/**
 * EntryPageCell - Displays entry page with query string indicator and UTM campaign
 */

import { Link, useLocation } from '@tanstack/react-router'
import { Info } from 'lucide-react'
import { memo, useMemo } from 'react'

import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { getAnalyticsRoute } from '@/lib/url-utils'

import type { PageData } from '../types'

interface EntryPageCellProps {
  data: PageData
  maxLength?: number
  /** Route override — skips auto-resolution from PageData routing fields. */
  internalLinkTo?: string
  /** Params for the route override */
  internalLinkParams?: Record<string, string>
}

export const EntryPageCell = memo(function EntryPageCell({
  data,
  maxLength = 28,
  internalLinkTo,
  internalLinkParams,
}: EntryPageCellProps) {
  const { pathname } = useLocation()
  const { title, url, hasQueryString, queryString, utmCampaign } = data
  const truncatedTitle = title.length > maxLength ? `${title.substring(0, maxLength - 3)}...` : title

  // Auto-resolve route from PageData when explicit link props not provided
  const resolvedRoute = useMemo(() => {
    if (internalLinkTo) return { to: internalLinkTo, params: internalLinkParams }
    if (data.pageType) return getAnalyticsRoute(data.pageType, data.pageWpId, undefined, data.resourceId)
    return null
  }, [internalLinkTo, internalLinkParams, data.pageType, data.pageWpId, data.resourceId])

  // Title content - either plain text or internal link
  const titleContent = resolvedRoute?.to ? (
    <Link
      to={resolvedRoute.to}
      params={resolvedRoute.params}
      search={{ from: pathname }}
      className="truncate text-xs text-foreground hover:text-primary hover:underline"
    >
      {truncatedTitle}
    </Link>
  ) : (
    <span className="truncate text-xs text-foreground">{truncatedTitle}</span>
  )

  return (
    <div className="max-w-[180px]">
      <div className="flex items-center gap-2">
        <Tooltip>
          <TooltipTrigger asChild>{titleContent}</TooltipTrigger>
          <TooltipContent>{hasQueryString && queryString ? queryString : url}</TooltipContent>
        </Tooltip>
        {hasQueryString && <Info className="h-3.5 w-3.5 text-muted-foreground/70 shrink-0" />}
      </div>
      {utmCampaign && <span className="text-xs text-muted-foreground block mt-0.5">{utmCampaign}</span>}
    </div>
  )
})
