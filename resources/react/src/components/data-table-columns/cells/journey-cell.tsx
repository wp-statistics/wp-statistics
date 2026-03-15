/**
 * JourneyCell - Displays entry → exit page flow in a compact stacked format
 */

import { Link, useLocation } from '@tanstack/react-router'
import { ArrowDown, CornerDownLeft, Flag, MapPin } from 'lucide-react'
import { memo, useMemo } from 'react'

import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { getAnalyticsRoute } from '@/lib/url-utils'

import type { PageData } from '../types'

interface JourneyCellProps {
  data: {
    entryPage: PageData
    exitPage: PageData
    isBounce: boolean
  }
  maxLength?: number
  /** Route override for entry page — skips auto-resolution from PageData routing fields. */
  entryLinkTo?: string
  /** Params for entry page route override */
  entryLinkParams?: Record<string, string>
  /** Route override for exit page — skips auto-resolution from PageData routing fields. */
  exitLinkTo?: string
  /** Params for exit page route override */
  exitLinkParams?: Record<string, string>
}

export const JourneyCell = memo(function JourneyCell({
  data,
  maxLength = 28,
  entryLinkTo,
  entryLinkParams,
  exitLinkTo,
  exitLinkParams,
}: JourneyCellProps) {
  const { pathname } = useLocation()
  const { entryPage, exitPage, isBounce } = data

  const truncate = (text: string) => (text.length > maxLength ? `${text.substring(0, maxLength - 3)}...` : text)

  // Auto-resolve routes from page data when explicit link props not provided
  const resolvedEntryRoute = useMemo(() => {
    if (entryLinkTo) return { to: entryLinkTo, params: entryLinkParams }
    if (entryPage.pageType) return getAnalyticsRoute(entryPage.pageType, entryPage.pageWpId, undefined, entryPage.resourceId)
    return null
  }, [entryLinkTo, entryLinkParams, entryPage.pageType, entryPage.pageWpId, entryPage.resourceId])

  const resolvedExitRoute = useMemo(() => {
    if (exitLinkTo) return { to: exitLinkTo, params: exitLinkParams }
    if (exitPage.pageType) return getAnalyticsRoute(exitPage.pageType, exitPage.pageWpId, undefined, exitPage.resourceId)
    return null
  }, [exitLinkTo, exitLinkParams, exitPage.pageType, exitPage.pageWpId, exitPage.resourceId])

  // Entry title content - either plain text or internal link
  const entryTitleContent = resolvedEntryRoute?.to ? (
    <Link
      to={resolvedEntryRoute.to}
      params={resolvedEntryRoute.params}
      search={{ from: pathname }}
      className="text-xs text-neutral-700 truncate hover:text-primary hover:underline"
    >
      {truncate(entryPage.title)}
    </Link>
  ) : (
    <span className="text-xs text-neutral-700 truncate">{truncate(entryPage.title)}</span>
  )

  // Exit title content - either plain text or internal link
  const exitTitleContent = resolvedExitRoute?.to ? (
    <Link
      to={resolvedExitRoute.to}
      params={resolvedExitRoute.params}
      search={{ from: pathname }}
      className="text-xs text-neutral-700 truncate hover:text-primary hover:underline"
    >
      {truncate(exitPage.title)}
    </Link>
  ) : (
    <span className="text-xs text-neutral-700 truncate">{truncate(exitPage.title)}</span>
  )

  // Bounce: show single page with bounce indicator
  if (isBounce) {
    const bounceTitleContent = resolvedEntryRoute?.to ? (
      <Link
        to={resolvedEntryRoute.to}
        params={resolvedEntryRoute.params}
        search={{ from: pathname }}
        className="text-xs text-neutral-500 truncate hover:text-primary hover:underline"
      >
        {truncate(entryPage.title)}
      </Link>
    ) : (
      <span className="text-xs text-neutral-500 truncate">{truncate(entryPage.title)}</span>
    )

    return (
      <div className="flex flex-col gap-0.5 group/journey max-w-[180px]">
        <Tooltip>
          <TooltipTrigger asChild>
            <div className="flex items-center gap-1.5">
              <CornerDownLeft className="w-3 h-3 text-neutral-400 shrink-0" />
              {bounceTitleContent}
            </div>
          </TooltipTrigger>
          <TooltipContent>
            <div className="text-xs">
              <div className="font-medium">Bounced</div>
              <div className="text-neutral-500">{entryPage.url}</div>
            </div>
          </TooltipContent>
        </Tooltip>
        {entryPage.utmCampaign && (
          <span className="text-xs text-neutral-500 block mt-0.5">{entryPage.utmCampaign}</span>
        )}
      </div>
    )
  }

  // Normal flow: entry → exit
  return (
    <div className="flex flex-col gap-0 group/journey max-w-[180px]">
      {/* Entry Page */}
      <Tooltip>
        <TooltipTrigger asChild>
          <div className="flex items-center gap-1.5">
            <MapPin className="w-3 h-3 text-neutral-400 shrink-0 opacity-60 group-hover/journey:opacity-100 transition-opacity" />
            {entryTitleContent}
          </div>
        </TooltipTrigger>
        <TooltipContent>{entryPage.url}</TooltipContent>
      </Tooltip>

      {/* Arrow indicator */}
      <div className="flex items-center gap-1.5 py-0.5">
        <ArrowDown className="w-3 h-3 text-neutral-300 shrink-0 ml-0" />
      </div>

      {/* Exit Page */}
      <Tooltip>
        <TooltipTrigger asChild>
          <div className="flex items-center gap-1.5">
            <Flag className="w-3 h-3 text-neutral-400 shrink-0 opacity-60 group-hover/journey:opacity-100 transition-opacity" />
            {exitTitleContent}
          </div>
        </TooltipTrigger>
        <TooltipContent>{exitPage.url}</TooltipContent>
      </Tooltip>

      {/* UTM Campaign */}
      {entryPage.utmCampaign && (
        <span className="text-xs text-neutral-500 block mt-0.5">{entryPage.utmCampaign}</span>
      )}
    </div>
  )
})
