/**
 * PageCell - Displays page title with tooltip for full URL
 */

import { memo } from 'react'

import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'

import type { PageData } from '../types'

interface PageCellProps {
  data: PageData
  maxLength?: number
}

export const PageCell = memo(function PageCell({ data, maxLength = 28 }: PageCellProps) {
  const { title, url } = data
  const truncatedTitle = title.length > maxLength ? `${title.substring(0, maxLength - 3)}...` : title

  return (
    <div className="max-w-[140px]">
      <Tooltip>
        <TooltipTrigger asChild>
          <span className="cursor-pointer truncate text-xs text-neutral-700">{truncatedTitle}</span>
        </TooltipTrigger>
        <TooltipContent>{url}</TooltipContent>
      </Tooltip>
    </div>
  )
})
