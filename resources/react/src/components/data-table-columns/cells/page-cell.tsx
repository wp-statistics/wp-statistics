/**
 * PageCell - Displays page title with tooltip for full URL and optional external link on hover
 */

import { Link } from '@tanstack/react-router'
import { ExternalLink } from 'lucide-react'
import { memo, useMemo } from 'react'

import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { getAnalyticsRoute } from '@/lib/url-utils'
import { WordPress } from '@/lib/wordpress'

import type { PageData } from '../types'

interface PageCellProps {
  data: PageData
  maxLength?: number
  /** Optional external URL - when provided, shows an external link icon on hover */
  externalUrl?: string
  /** Route override — skips auto-resolution from PageData routing fields. Only needed when the default route doesn't match (e.g., category-pages forcing /url/$resourceId). */
  internalLinkTo?: string
  /** Params for the route override */
  internalLinkParams?: Record<string, string>
}

export const PageCell = memo(function PageCell({
  data,
  maxLength = 28,
  externalUrl,
  internalLinkTo,
  internalLinkParams,
}: PageCellProps) {
  const { title, url } = data
  const truncatedTitle = title.length > maxLength ? `${title.substring(0, maxLength - 3)}...` : title

  // Auto-resolve route from PageData when explicit link props not provided
  const resolvedRoute = useMemo(() => {
    if (internalLinkTo) return { to: internalLinkTo, params: internalLinkParams }
    if (data.pageType) return getAnalyticsRoute(data.pageType, data.pageWpId, undefined, data.resourceId)
    return null
  }, [internalLinkTo, internalLinkParams, data.pageType, data.pageWpId, data.resourceId])

  // Build full URL for external link
  const wp = WordPress.getInstance()
  const siteUrl = wp.getSiteUrl()
  const isAbsoluteUrl = externalUrl?.toLowerCase().startsWith('http')
  const fullExternalUrl = externalUrl
    ? isAbsoluteUrl
      ? externalUrl
      : `${siteUrl.replace(/\/+$/, '')}/${externalUrl.replace(/^\/+/, '')}`
    : null

  // Title content - either plain text or internal link
  const titleContent = resolvedRoute?.to ? (
    <Link
      to={resolvedRoute.to}
      params={resolvedRoute.params}
      className="truncate text-xs text-neutral-700 hover:text-primary hover:underline"
    >
      {truncatedTitle}
    </Link>
  ) : (
    <span className="truncate text-xs text-neutral-700">{truncatedTitle}</span>
  )

  return (
    <div className="group flex items-center gap-2 max-w-[180px]">
      <Tooltip>
        <TooltipTrigger asChild>{titleContent}</TooltipTrigger>
        <TooltipContent>{url}</TooltipContent>
      </Tooltip>
      {fullExternalUrl && (
        <Tooltip>
          <TooltipTrigger asChild>
            <a
              href={fullExternalUrl}
              target="_blank"
              rel="noopener noreferrer"
              className="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity text-neutral-400 hover:text-neutral-600"
              onClick={(e) => e.stopPropagation()}
            >
              <ExternalLink className="h-3.5 w-3.5" />
            </a>
          </TooltipTrigger>
          <TooltipContent>View page</TooltipContent>
        </Tooltip>
      )}
    </div>
  )
})
