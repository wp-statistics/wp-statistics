/**
 * AuthorCell - Displays author avatar and name with optional link
 */

import { Link } from '@tanstack/react-router'
import { memo } from 'react'

export interface AuthorCellProps {
  /** Author ID for linking to author page */
  authorId: number
  /** Author display name */
  authorName: string
  /** Author avatar URL, null if no avatar */
  authorAvatar: string | null
  /** Whether to render the name as a link (default: true) */
  linkEnabled?: boolean
}

export const AuthorCell = memo(function AuthorCell({
  authorId,
  authorName,
  authorAvatar,
  linkEnabled = true,
}: AuthorCellProps) {
  return (
    <div className="flex items-center gap-2 min-w-0">
      {authorAvatar ? (
        <img
          src={authorAvatar}
          alt={authorName}
          className="h-6 w-6 rounded-full object-cover shrink-0"
        />
      ) : (
        <div className="h-6 w-6 rounded-full bg-neutral-200 shrink-0" />
      )}
      {linkEnabled ? (
        <Link
          to="/author/$authorId"
          params={{ authorId: String(authorId) }}
          className="text-sm font-medium text-neutral-800 hover:text-primary truncate"
        >
          {authorName}
        </Link>
      ) : (
        <span className="text-sm font-medium text-neutral-800 truncate">
          {authorName}
        </span>
      )}
    </div>
  )
})
