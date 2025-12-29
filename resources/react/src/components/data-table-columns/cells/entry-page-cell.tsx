/**
 * EntryPageCell - Displays entry page with query string indicator and UTM campaign
 */

import { Info } from 'lucide-react'

import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'

import type { PageData } from '../types'

interface EntryPageCellProps {
  data: PageData
  maxLength?: number
}

export function EntryPageCell({ data, maxLength = 28 }: EntryPageCellProps) {
  const { title, url, hasQueryString, queryString, utmCampaign } = data
  const truncatedTitle = title.length > maxLength ? `${title.substring(0, maxLength - 3)}...` : title

  return (
    <div className="max-w-[200px]">
      <TooltipProvider>
        <Tooltip>
          <TooltipTrigger asChild>
            <div className="flex items-center gap-1 cursor-pointer">
              <span className="truncate text-[13px]">{truncatedTitle}</span>
              {hasQueryString && <Info className="h-3 w-3 text-muted-foreground shrink-0" />}
            </div>
          </TooltipTrigger>
          <TooltipContent>
            {hasQueryString && queryString ? queryString : url}
          </TooltipContent>
        </Tooltip>
      </TooltipProvider>
      {utmCampaign && (
        <span className="text-[10px] text-muted-foreground block mt-0.5">{utmCampaign}</span>
      )}
    </div>
  )
}
