import { useNavigate } from '@tanstack/react-router'
import { Bell,Menu, ShieldCheck } from 'lucide-react'

import { Logo } from '@/components/ui/logo'
import { useSidebar } from '@/components/ui/sidebar'
import { useBreakpoint } from '@/hooks/use-breakpoint'

// Placeholder states - will be connected to actual data later
const privacyStatus: 'compliant' | 'warning' = 'compliant'
const hasNotifications = true

export function Header() {
  const { isMobileOrTablet } = useBreakpoint()
  const { toggleSidebar } = useSidebar()
  const navigate = useNavigate()

  return (
    <header className="bg-header h-[var(--header-height)] px-3 lg:px-4 flex items-center gap-3 shrink-0 border-b border-sidebar-border">
      {/* Mobile/Tablet menu trigger */}
      {isMobileOrTablet && (
        <button
          className="flex items-center justify-center text-sidebar-foreground hover:bg-sidebar-hover shrink-0 h-10 w-10 rounded-lg transition-colors"
          onClick={toggleSidebar}
          aria-label="Toggle navigation menu"
        >
          <Menu className="h-5 w-5" />
        </button>
      )}

      {/* Logo */}
      <div className="flex gap-1 items-center text-sidebar-foreground font-medium italic text-xl">
        <Logo />
      </div>

      {/* Right section - icons */}
      <div className="ml-auto flex items-center gap-0.5">
        {/* Privacy Status Icon */}
        <button
          className={`
            group relative flex items-center justify-center h-8 w-8 rounded-md transition-colors
            ${privacyStatus === 'compliant'
              ? 'text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/10 dark:hover:bg-emerald-400/10'
              : 'text-amber-500 dark:text-amber-400 hover:bg-amber-500/10 dark:hover:bg-amber-400/10'
            }
          `}
          aria-label={`Privacy status: ${privacyStatus}`}
          onClick={() => void navigate({ to: '/tools/privacy-audit' })}
        >
          <ShieldCheck className="h-[18px] w-[18px] transition-transform group-hover:scale-105" />
        </button>

        {/* Notifications Icon */}
        <button
          className="group relative flex items-center justify-center h-8 w-8 rounded-md text-muted-foreground hover:text-sidebar-foreground hover:bg-sidebar-hover transition-colors"
          aria-label="Notifications"
        >
          <Bell className="h-[18px] w-[18px] transition-transform group-hover:scale-105" />
          {hasNotifications && (
            <span className="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-red-500 ring-2 ring-header animate-pulse" />
          )}
        </button>
      </div>
    </header>
  )
}
