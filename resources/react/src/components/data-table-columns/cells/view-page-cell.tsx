/**
 * ViewPageCell - External link button to view the page on the frontend
 */

import { ExternalLink } from 'lucide-react'
import { memo } from 'react'

import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { WordPress } from '@/lib/wordpress'

interface ViewPageCellProps {
  url: string
}

export const ViewPageCell = memo(function ViewPageCell({ url }: ViewPageCellProps) {
  const wp = WordPress.getInstance()
  const siteUrl = wp.getSiteUrl()

  // Case-insensitive protocol check and normalize slashes when joining
  const isAbsoluteUrl = url.toLowerCase().startsWith('http')
  const fullUrl = isAbsoluteUrl ? url : `${siteUrl.replace(/\/+$/, '')}/${url.replace(/^\/+/, '')}`

  return (
    <div className="flex justify-center">
      <Tooltip>
        <TooltipTrigger asChild>
          <a
            href={fullUrl}
            target="_blank"
            rel="noopener noreferrer"
            className="inline-flex items-center justify-center p-1.5 rounded hover:bg-neutral-100 text-neutral-500 hover:text-neutral-700 transition-colors"
          >
            <ExternalLink className="h-4 w-4" />
          </a>
        </TooltipTrigger>
        <TooltipContent>View page</TooltipContent>
      </Tooltip>
    </div>
  )
})
