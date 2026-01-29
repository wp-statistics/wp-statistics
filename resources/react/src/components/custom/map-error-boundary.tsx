import { Button } from '@components/ui/button'
import { AlertTriangle, RefreshCw } from 'lucide-react'
import { Component, type ErrorInfo, type ReactNode } from 'react'

interface Props {
  children: ReactNode
  fallbackTitle?: string
  fallbackMessage?: string
  onReset?: () => void
}

interface State {
  hasError: boolean
  error: Error | null
}

/**
 * Error boundary for map visualization components.
 *
 * Catches rendering errors in the map and displays a user-friendly
 * fallback UI with retry option. This prevents map errors from
 * crashing the entire application.
 */
export class MapErrorBoundary extends Component<Props, State> {
  constructor(props: Props) {
    super(props)
    this.state = { hasError: false, error: null }
  }

  static getDerivedStateFromError(error: Error): State {
    return { hasError: true, error }
  }

  componentDidCatch(error: Error, errorInfo: ErrorInfo): void {
    // Log error for debugging
    console.error('Map visualization error:', error)
    console.error('Component stack:', errorInfo.componentStack)
  }

  handleReset = (): void => {
    this.setState({ hasError: false, error: null })
    this.props.onReset?.()
  }

  render(): ReactNode {
    if (this.state.hasError) {
      const { fallbackTitle = 'Map Error', fallbackMessage = 'Unable to display the map visualization.' } = this.props

      return (
        <div className="flex flex-col items-center justify-center p-8 bg-muted/10 rounded-lg min-h-[300px]">
          <div className="flex flex-col items-center text-center max-w-sm">
            <div className="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center mb-4">
              <AlertTriangle className="w-6 h-6 text-amber-600" />
            </div>
            <h3 className="text-base font-medium text-neutral-900 mb-2">{fallbackTitle}</h3>
            <p className="text-sm text-neutral-500 mb-4">{fallbackMessage}</p>
            {process.env.NODE_ENV === 'development' && this.state.error && (
              <details className="text-left w-full mb-4">
                <summary className="text-xs text-neutral-500 hover:text-neutral-600">
                  Technical details
                </summary>
                <pre className="mt-2 p-2 bg-neutral-100 rounded text-xs text-neutral-600 overflow-auto max-h-32">
                  {this.state.error.message}
                  {'\n\n'}
                  {this.state.error.stack}
                </pre>
              </details>
            )}
            <Button variant="outline" size="sm" onClick={this.handleReset} className="gap-2">
              <RefreshCw className="w-4 h-4" />
              Try Again
            </Button>
          </div>
        </div>
      )
    }

    return this.props.children
  }
}
