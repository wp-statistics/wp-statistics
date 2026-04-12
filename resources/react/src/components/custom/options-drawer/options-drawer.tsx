/* eslint-disable react-refresh/only-export-components */

import { __ } from '@wordpress/i18n'
import { ChevronLeftIcon, LockIcon } from 'lucide-react'
import { createContext, type ReactNode, useContext,useEffect, useState } from 'react'

import { cn } from '@/lib/utils'

export type OptionsView = 'main' | 'widgets' | 'metrics' | 'filters' | 'columns' | 'date-range' | 'page-filters' | 'export'

interface OptionsDrawerProps {
  open: boolean
  onOpenChange: (open: boolean) => void
  children: ReactNode
  onReset?: () => void
}

interface OptionsDrawerContextValue {
  currentView: OptionsView
  setCurrentView: (view: OptionsView) => void
  goBack: () => void
  closeDrawer: () => void
}

// Context for drill-down navigation
const OptionsDrawerContext = createContext<OptionsDrawerContextValue | undefined>(undefined)

export function useOptionsDrawer() {
  const context = useContext(OptionsDrawerContext)
  if (!context) {
    throw new Error('useOptionsDrawer must be used within OptionsDrawer')
  }
  return context
}

// Get view title for header
const getViewTitle = (view: OptionsView): string => {
  switch (view) {
    case 'widgets':
      return __('Widgets', 'wp-statistics')
    case 'metrics':
      return __('Metrics', 'wp-statistics')
    case 'filters':
      return __('Filters', 'wp-statistics')
    case 'columns':
      return __('Show/hide columns', 'wp-statistics')
    case 'date-range':
      return __('Date Range', 'wp-statistics')
    case 'page-filters':
      return __('Page Filters', 'wp-statistics')
    case 'export':
      return __('Export', 'wp-statistics')
    default:
      return __('Options', 'wp-statistics')
  }
}

export function OptionsDrawer({ open, onOpenChange, children, onReset }: OptionsDrawerProps) {
  const [currentView, setCurrentView] = useState<OptionsView>('main')
  const [isVisible, setIsVisible] = useState(false)
  const [isAnimating, setIsAnimating] = useState(false)

  const goBack = () => setCurrentView('main')

  // Handle open/close with animation
  useEffect(() => {
    if (open) {
      setIsVisible(true)
      // Small delay to trigger animation
      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          setIsAnimating(true)
        })
      })
    } else {
      setIsAnimating(false)
      // Wait for animation to complete before hiding
      const timer = setTimeout(() => {
        setIsVisible(false)
        setCurrentView('main')
      }, 200)
      return () => clearTimeout(timer)
    }
  }, [open])

  // Handle close
  const handleClose = () => {
    onOpenChange(false)
  }

  // Handle overlay click
  const handleOverlayClick = (e: React.MouseEvent) => {
    if (e.target === e.currentTarget) {
      handleClose()
    }
  }

  // Handle escape key
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape' && open) {
        onOpenChange(false)
      }
    }

    if (open) {
      document.addEventListener('keydown', handleKeyDown)
      return () => document.removeEventListener('keydown', handleKeyDown)
    }
  }, [open, onOpenChange])

  if (!isVisible) return null

  return (
    <OptionsDrawerContext.Provider value={{ currentView, setCurrentView, goBack, closeDrawer: handleClose }}>
      {/* Overlay - positioned under WP admin bar + plugin header */}
      <div
        className={cn(
          'fixed inset-0 z-[100000] bg-black/20',
          'top-[calc(var(--wp-admin-bar-height)+var(--header-height))]',
          'transition-opacity duration-200 ease-out',
          isAnimating ? 'opacity-100' : 'opacity-0'
        )}
        onClick={handleOverlayClick}
        role="presentation"
        aria-hidden="true"
      />

      {/* Drawer panel - positioned under WP admin bar + plugin header */}
      <div
        className={cn(
          'fixed right-0 bottom-0 z-[100001]',
          'top-[calc(var(--wp-admin-bar-height)+var(--header-height))]',
          'w-[380px] sm:w-[400px]',
          'bg-card border-l border-border',
          'flex flex-col',
          'transition-transform duration-200 ease-out',
          isAnimating ? 'translate-x-0' : 'translate-x-full'
        )}
        role="dialog"
        aria-modal="true"
        aria-label={getViewTitle(currentView)}
      >
        {/* Header */}
        <div className="flex items-center justify-between h-12 px-4 border-b border-border/50 bg-muted/50 shrink-0">
          {currentView === 'main' ? (
            <>
              <h2 className="text-sm font-semibold text-foreground">
                {__('Options', 'wp-statistics')}
              </h2>
              {onReset && (
                <button
                  type="button"
                  onClick={onReset}
                  className="text-xs text-muted-foreground hover:text-foreground transition-colors"
                >
                  {__('Reset all', 'wp-statistics')}
                </button>
              )}
            </>
          ) : (
            <>
              <div className="flex items-center gap-2">
                <button
                  type="button"
                  onClick={goBack}
                  className="flex items-center justify-center w-6 h-6 -ml-1 rounded hover:bg-muted transition-colors text-muted-foreground hover:text-foreground"
                  aria-label={__('Back', 'wp-statistics')}
                >
                  <ChevronLeftIcon className="h-4 w-4" />
                </button>
                <h2 className="text-sm font-semibold text-foreground">
                  {getViewTitle(currentView)}
                </h2>
              </div>
              <button
                type="button"
                onClick={handleClose}
                className="text-xs text-muted-foreground hover:text-foreground transition-colors"
              >
                {__('Done', 'wp-statistics')}
              </button>
            </>
          )}
        </div>

        {/* Content - scrollable with view transition */}
        <div className="flex-1 overflow-y-auto overflow-x-hidden">
          <div
            className={cn(
              'transition-opacity duration-150 ease-out',
              isAnimating ? 'opacity-100' : 'opacity-0'
            )}
          >
            {children}
          </div>
        </div>
      </div>
    </OptionsDrawerContext.Provider>
  )
}

// Menu item component for main view
interface OptionsMenuItemProps {
  icon: ReactNode
  title: string
  summary?: string
  onClick: () => void
  className?: string
}

export function OptionsMenuItem({ icon, title, summary, onClick, className }: OptionsMenuItemProps) {
  return (
    <button
      type="button"
      onClick={onClick}
      className={cn(
        'flex w-full items-center justify-between px-4 py-3',
        'hover:bg-muted/50 active:bg-muted transition-colors',
        'border-b border-border/50',
        'group',
        className
      )}
    >
      <div className="flex items-center gap-3">
        <span className="text-muted-foreground/70 group-hover:text-muted-foreground transition-colors">{icon}</span>
        <span className="text-sm font-medium text-foreground">{title}</span>
      </div>
      <div className="flex items-center gap-2">
        {summary && (
          <span className="text-xs text-muted-foreground bg-muted px-2 py-0.5 rounded-full">
            {summary}
          </span>
        )}
        <ChevronLeftIcon className="h-4 w-4 text-muted-foreground/50 rotate-180 group-hover:text-muted-foreground/70 transition-colors" />
      </div>
    </button>
  )
}

// Detail view wrapper with description
interface OptionsDetailViewProps {
  description?: string
  children: ReactNode
  className?: string
}

export function OptionsDetailView({ description, children, className }: OptionsDetailViewProps) {
  return (
    <div className={cn('', className)}>
      {description && (
        <p className="text-xs text-muted-foreground px-4 py-3 border-b border-border/50 bg-muted/30">
          {description}
        </p>
      )}
      <div className="px-4 py-2">
        {children}
      </div>
    </div>
  )
}

// Toggle item for detail views
interface OptionsToggleItemProps {
  icon?: ReactNode
  label: string
  checked: boolean
  onCheckedChange: (checked: boolean) => void
  disabled?: boolean
}

export function OptionsToggleItem({ icon, label, checked, onCheckedChange, disabled }: OptionsToggleItemProps) {
  return (
    <button
      type="button"
      onClick={() => !disabled && onCheckedChange(!checked)}
      disabled={disabled}
      className={cn(
        'flex w-full items-center justify-between py-2.5',
        'border-b border-border/50 last:border-b-0',
        'disabled:cursor-not-allowed disabled:opacity-50',
        'hover:bg-muted/50 -mx-4 px-4 transition-colors'
      )}
    >
      <div className="flex items-center gap-3">
        {icon && <span className="text-muted-foreground/70">{icon}</span>}
        <span className={cn(
          'text-sm',
          checked ? 'text-foreground' : 'text-muted-foreground'
        )}>
          {label}
        </span>
      </div>
      {/* Custom toggle switch */}
      <div
        role="switch"
        aria-checked={checked}
        aria-label={label}
        className={cn(
          'relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition-colors',
          checked ? 'bg-primary' : 'bg-muted'
        )}
      >
        <span
          className={cn(
            'pointer-events-none block h-4 w-4 rounded-full bg-card transition-transform',
            checked ? 'translate-x-[18px]' : 'translate-x-0.5'
          )}
        />
      </div>
    </button>
  )
}

// Locked menu item for premium features
interface LockedMenuItemProps {
  icon: ReactNode
  label: string
}

export function LockedMenuItem({ icon, label }: LockedMenuItemProps) {
  const { currentView } = useOptionsDrawer()

  // Only show on main view
  if (currentView !== 'main') {
    return null
  }

  return (
    <div
      className={cn(
        'flex w-full items-center justify-between px-4 py-3',
        'border-b border-border/50',
        'opacity-50 cursor-not-allowed'
      )}
    >
      <div className="flex items-center gap-3">
        <span className="text-muted-foreground/70">{icon}</span>
        <span className="text-sm font-medium text-muted-foreground">{label}</span>
      </div>
      <div className="flex items-center gap-1.5">
        <LockIcon className="h-3 w-3 text-muted-foreground/70" />
        <span className="text-xs text-muted-foreground/70">{__('Premium', 'wp-statistics')}</span>
      </div>
    </div>
  )
}
