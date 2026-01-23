/**
 * JourneyCell - Displays entry → exit page flow in a compact stacked format
 */

import { Link } from '@tanstack/react-router'
import { ArrowDown, CornerDownLeft, Flag, MapPin } from 'lucide-react'
import { memo } from 'react'

import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'

interface JourneyCellProps {
  data: {
    entryPage: { title: string; url: string; utmCampaign?: string }
    exitPage: { title: string; url: string }
    isBounce: boolean
  }
  maxLength?: number
  /** Optional internal link for entry page */
  entryLinkTo?: string
  /** Optional params for entry page link */
  entryLinkParams?: Record<string, string>
  /** Optional internal link for exit page */
  exitLinkTo?: string
  /** Optional params for exit page link */
  exitLinkParams?: Record<string, string>
}

export const JourneyCell = memo(function JourneyCell({
  data,
  maxLength = 20,
  entryLinkTo,
  entryLinkParams,
  exitLinkTo,
  exitLinkParams,
}: JourneyCellProps) {
  const { entryPage, exitPage, isBounce } = data

  const truncate = (text: string) => (text.length > maxLength ? `${text.substring(0, maxLength - 3)}...` : text)

  // Entry title content - either plain text or internal link
  const entryTitleContent = entryLinkTo ? (
    <Link
      to={entryLinkTo}
      params={entryLinkParams}
      className="text-xs text-neutral-700 truncate hover:text-primary hover:underline"
    >
      {truncate(entryPage.title)}
    </Link>
  ) : (
    <span className="text-xs text-neutral-700 truncate">{truncate(entryPage.title)}</span>
  )

  // Exit title content - either plain text or internal link
  const exitTitleContent = exitLinkTo ? (
    <Link
      to={exitLinkTo}
      params={exitLinkParams}
      className="text-xs text-neutral-700 truncate hover:text-primary hover:underline"
    >
      {truncate(exitPage.title)}
    </Link>
  ) : (
    <span className="text-xs text-neutral-700 truncate">{truncate(exitPage.title)}</span>
  )

  // Bounce: show single page with bounce indicator
  if (isBounce) {
    const bounceTitleContent = entryLinkTo ? (
      <Link
        to={entryLinkTo}
        params={entryLinkParams}
        className="text-xs text-neutral-500 truncate hover:text-primary hover:underline"
      >
        {truncate(entryPage.title)}
      </Link>
    ) : (
      <span className="text-xs text-neutral-500 truncate">{truncate(entryPage.title)}</span>
    )

    return (
      <div className="flex flex-col gap-0.5 group/journey max-w-[160px]">
        <Tooltip>
          <TooltipTrigger asChild>
            <div className="flex items-center gap-1.5 cursor-pointer">
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
          <span className="text-[11px] text-neutral-500 truncate pl-4">{entryPage.utmCampaign}</span>
        )}
      </div>
    )
  }

  // Normal flow: entry → exit
  return (
    <div className="flex flex-col gap-0 group/journey max-w-[160px]">
      {/* Entry Page */}
      <Tooltip>
        <TooltipTrigger asChild>
          <div className="flex items-center gap-1.5 cursor-pointer">
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
          <div className="flex items-center gap-1.5 cursor-pointer">
            <Flag className="w-3 h-3 text-neutral-400 shrink-0 opacity-60 group-hover/journey:opacity-100 transition-opacity" />
            {exitTitleContent}
          </div>
        </TooltipTrigger>
        <TooltipContent>{exitPage.url}</TooltipContent>
      </Tooltip>

      {/* UTM Campaign */}
      {entryPage.utmCampaign && (
        <span className="text-[11px] text-neutral-500 truncate pl-4 mt-0.5">{entryPage.utmCampaign}</span>
      )}
    </div>
  )
})
