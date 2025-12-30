/**
 * JourneyCell - Displays entry → exit page flow in a compact stacked format
 */

import { ArrowDown, CornerDownLeft, MapPin, Flag } from 'lucide-react'

import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'

interface JourneyCellProps {
  data: {
    entryPage: { title: string; url: string; utmCampaign?: string }
    exitPage: { title: string; url: string }
    isBounce: boolean
  }
  maxLength?: number
}

export function JourneyCell({ data, maxLength = 20 }: JourneyCellProps) {
  const { entryPage, exitPage, isBounce } = data

  const truncate = (text: string) =>
    text.length > maxLength ? `${text.substring(0, maxLength - 3)}...` : text

  // Bounce: show single page with bounce indicator
  if (isBounce) {
    return (
      <div className="flex flex-col gap-0.5 group/journey max-w-[160px]">
        <Tooltip>
          <TooltipTrigger asChild>
            <div className="flex items-center gap-1.5 cursor-pointer">
              <CornerDownLeft className="w-3 h-3 text-neutral-400 shrink-0" />
              <span className="text-xs text-neutral-500 truncate">{truncate(entryPage.title)}</span>
            </div>
          </TooltipTrigger>
          <TooltipContent>
            <div className="text-xs">
              <div className="font-medium">Bounced</div>
              <div className="text-neutral-400">{entryPage.url}</div>
            </div>
          </TooltipContent>
        </Tooltip>
        {entryPage.utmCampaign && (
          <span className="text-[10px] text-neutral-400 truncate pl-4">{entryPage.utmCampaign}</span>
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
            <span className="text-xs text-neutral-700 truncate">{truncate(entryPage.title)}</span>
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
            <span className="text-xs text-neutral-700 truncate">{truncate(exitPage.title)}</span>
          </div>
        </TooltipTrigger>
        <TooltipContent>{exitPage.url}</TooltipContent>
      </Tooltip>

      {/* UTM Campaign */}
      {entryPage.utmCampaign && (
        <span className="text-[10px] text-neutral-400 truncate pl-4 mt-0.5">{entryPage.utmCampaign}</span>
      )}
    </div>
  )
}
