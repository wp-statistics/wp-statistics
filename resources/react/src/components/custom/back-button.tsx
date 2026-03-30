import { Link, useSearch } from '@tanstack/react-router'
import { ArrowLeft } from 'lucide-react'

interface BackButtonProps {
  defaultTo: string
  label: string
}

/**
 * Back button that reads a `from` search parameter to determine the return route.
 * Falls back to `defaultTo` when `from` is absent or not a valid internal path.
 */
export function BackButton({ defaultTo, label }: BackButtonProps) {
  const search = useSearch({ strict: false }) as { from?: string }
  const from = search.from

  // Only use `from` if it looks like an internal path (starts with /)
  const to = from && from.startsWith('/') ? from : defaultTo

  return (
    <Link
      to={to}
      className="p-1.5 -ml-1.5 rounded-md hover:bg-neutral-100 transition-colors shrink-0"
      aria-label={label}
    >
      <ArrowLeft className="h-5 w-5 text-neutral-500" />
    </Link>
  )
}
