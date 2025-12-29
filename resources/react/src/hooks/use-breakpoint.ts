import * as React from 'react'

// Breakpoint values matching Tailwind defaults
const BREAKPOINTS = {
  md: 768,  // Mobile/Tablet boundary
  lg: 1024, // Tablet/Desktop boundary
} as const

type Breakpoint = 'mobile' | 'tablet' | 'desktop'

interface BreakpointState {
  breakpoint: Breakpoint
  isMobile: boolean // < 768px
  isTablet: boolean // 768px - 1023px
  isDesktop: boolean // >= 1024px
  isMobileOrTablet: boolean // < 1024px (for sidebar Sheet behavior)
  width: number
}

function getBreakpointState(width: number): BreakpointState {
  const isMobile = width < BREAKPOINTS.md
  const isTablet = width >= BREAKPOINTS.md && width < BREAKPOINTS.lg
  const isDesktop = width >= BREAKPOINTS.lg

  let breakpoint: Breakpoint = 'desktop'
  if (isMobile) breakpoint = 'mobile'
  else if (isTablet) breakpoint = 'tablet'

  return {
    breakpoint,
    isMobile,
    isTablet,
    isDesktop,
    isMobileOrTablet: isMobile || isTablet,
    width,
  }
}

export function useBreakpoint(): BreakpointState {
  const [state, setState] = React.useState<BreakpointState>(() =>
    getBreakpointState(typeof window !== 'undefined' ? window.innerWidth : 1280)
  )

  React.useEffect(() => {
    const updateBreakpoint = () => {
      setState(getBreakpointState(window.innerWidth))
    }

    // Use matchMedia for better performance
    const mqMobile = window.matchMedia(`(max-width: ${BREAKPOINTS.md - 1}px)`)
    const mqTablet = window.matchMedia(
      `(min-width: ${BREAKPOINTS.md}px) and (max-width: ${BREAKPOINTS.lg - 1}px)`
    )

    const handleChange = () => updateBreakpoint()

    mqMobile.addEventListener('change', handleChange)
    mqTablet.addEventListener('change', handleChange)

    // Initial update
    updateBreakpoint()

    return () => {
      mqMobile.removeEventListener('change', handleChange)
      mqTablet.removeEventListener('change', handleChange)
    }
  }, [])

  return state
}

// Maintain backward compatibility with existing useIsMobile hook
export function useIsMobile(): boolean {
  const { isMobile } = useBreakpoint()
  return isMobile
}

// Additional convenience hooks
export function useIsTablet(): boolean {
  const { isTablet } = useBreakpoint()
  return isTablet
}

export function useIsDesktop(): boolean {
  const { isDesktop } = useBreakpoint()
  return isDesktop
}

export function useIsMobileOrTablet(): boolean {
  const { isMobileOrTablet } = useBreakpoint()
  return isMobileOrTablet
}
