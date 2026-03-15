import { Component, type ErrorInfo, type ReactNode } from 'react'

import { Button } from '@/components/ui/button'
import { Panel, PanelContent, PanelHeader, PanelTitle } from '@/components/ui/panel'

export interface ErrorBoundaryProps {
  /**
   * Children to render
   */
  children: ReactNode
  /**
   * Custom fallback UI to render when an error occurs
   */
  fallback?: ReactNode
  /**
   * Called when an error is caught
   */
  onError?: (error: Error, errorInfo: ErrorInfo) => void
  /**
   * Custom title for the error message
   */
  title?: string
  /**
   * Custom message to display
   */
  message?: string
  /**
   * Whether to show reset button
   */
  showReset?: boolean
}

interface ErrorBoundaryState {
  hasError: boolean
  error: Error | null
}

/**
 * Error boundary component that catches JavaScript errors in child components.
 * Prevents the entire app from crashing and displays a fallback UI.
 *
 * @example
 * ```tsx
 * <ErrorBoundary
 *   title="Failed to load chart"
 *   message="There was an error loading the chart data."
 *   onError={(error) => console.error(error)}
 * >
 *   <LineChart data={data} />
 * </ErrorBoundary>
 * ```
 */
export class ErrorBoundary extends Component<ErrorBoundaryProps, ErrorBoundaryState> {
  constructor(props: ErrorBoundaryProps) {
    super(props)
    this.state = { hasError: false, error: null }
  }

  static getDerivedStateFromError(error: Error): ErrorBoundaryState {
    return { hasError: true, error }
  }

  componentDidCatch(error: Error, errorInfo: ErrorInfo): void {
    // Log error to console in development
    if (process.env.NODE_ENV === 'development') {
      console.error('ErrorBoundary caught an error:', error, errorInfo)
    }

    // Call optional error handler
    this.props.onError?.(error, errorInfo)
  }

  handleReset = (): void => {
    this.setState({ hasError: false, error: null })
  }

  render(): ReactNode {
    const { hasError, error } = this.state
    const { children, fallback, title = 'Something went wrong', message, showReset = true } = this.props

    if (hasError) {
      // Return custom fallback if provided
      if (fallback) {
        return fallback
      }

      // Default error UI
      return (
        <Panel className="border-destructive/50 bg-destructive/5">
          <PanelHeader>
            <PanelTitle className="text-destructive">{title}</PanelTitle>
          </PanelHeader>
          <PanelContent>
            <div className="flex flex-col gap-3">
              <p className="text-sm text-muted-foreground">
                {message || 'An unexpected error occurred. Please try again.'}
              </p>
              {process.env.NODE_ENV === 'development' && error && (
                <details className="text-xs text-muted-foreground">
                  <summary className="hover:text-foreground">Error details</summary>
                  <pre className="mt-2 overflow-auto rounded bg-muted p-2 text-xs">
                    {error.message}
                    {error.stack && (
                      <>
                        {'\n\n'}
                        {error.stack}
                      </>
                    )}
                  </pre>
                </details>
              )}
              {showReset && (
                <Button variant="outline" size="sm" onClick={this.handleReset} className="w-fit">
                  Try again
                </Button>
              )}
            </div>
          </PanelContent>
        </Panel>
      )
    }

    return children
  }
}

/**
 * Wrapper component for chart-specific error handling
 */
export function ChartErrorBoundary({ children }: { children: ReactNode }) {
  return (
    <ErrorBoundary
      title="Failed to load chart"
      message="There was an error rendering the chart. Please try refreshing the page."
    >
      {children}
    </ErrorBoundary>
  )
}

/**
 * Wrapper component for data table error handling
 */
export function TableErrorBoundary({ children }: { children: ReactNode }) {
  return (
    <ErrorBoundary
      title="Failed to load table"
      message="There was an error rendering the data table. Please try refreshing the page."
    >
      {children}
    </ErrorBoundary>
  )
}
