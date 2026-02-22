/**
 * TermCell - Displays taxonomy term name with optional link
 */

import { Link } from '@tanstack/react-router'
import { memo } from 'react'

export interface TermCellProps {
  /** Term ID for linking to category page */
  termId: number
  /** Term display name */
  termName: string
  /** Whether to render the name as a link (default: true) */
  linkEnabled?: boolean
}

export const TermCell = memo(function TermCell({
  termId,
  termName,
  linkEnabled = true,
}: TermCellProps) {
  return (
    <div className="flex items-center gap-2 min-w-0">
      {linkEnabled ? (
        <Link
          to="/category/$categoryId"
          params={{ categoryId: String(termId) }}
          className="text-sm font-medium text-neutral-800 hover:text-primary truncate"
        >
          {termName}
        </Link>
      ) : (
        <span className="text-sm font-medium text-neutral-800 truncate">
          {termName}
        </span>
      )}
    </div>
  )
})
