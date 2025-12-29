import { Menu } from 'lucide-react'

import { Button } from '@/components/ui/button'
import { Logo } from '@/components/ui/logo'
import { useSidebar } from '@/components/ui/sidebar'
import { useBreakpoint } from '@/hooks/use-breakpoint'

export function Header() {
  const { isMobileOrTablet } = useBreakpoint()
  const { toggleSidebar } = useSidebar()

  return (
    <header className="bg-header h-[var(--header-height)] px-3 lg:px-4 flex items-center gap-3 shrink-0">
      {/* Mobile/Tablet menu trigger */}
      {isMobileOrTablet && (
        <Button
          variant="ghost"
          size="icon"
          className="text-white hover:bg-white/10 shrink-0 h-11 w-11"
          onClick={toggleSidebar}
          aria-label="Toggle navigation menu"
        >
          <Menu className="h-5 w-5" />
        </Button>
      )}

      {/* Logo */}
      <div className="flex gap-1 items-center text-white font-medium italic text-xl">
        <Logo />
      </div>
    </header>
  )
}
