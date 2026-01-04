/**
 * Logger Service
 *
 * Centralized logging service that provides:
 * - Environment-aware logging (disabled in production)
 * - Consistent log formatting
 * - Type-safe error handling
 *
 * Usage:
 * import { logger } from '@/lib/logger'
 *
 * logger.error('Failed to save settings', error)
 * logger.warn('Deprecated feature used')
 * logger.debug('Filter state:', filters)
 * logger.info('Settings saved successfully')
 */

type LogLevel = 'debug' | 'info' | 'warn' | 'error'

interface LoggerOptions {
  /** Prefix for all log messages */
  prefix?: string
  /** Whether logging is enabled (defaults to development mode) */
  enabled?: boolean
}

const isDevelopment = typeof window !== 'undefined' && window.location.hostname === 'localhost'

/**
 * Creates a logger instance with optional configuration
 */
function createLogger(options: LoggerOptions = {}) {
  const { prefix = '[WP Statistics]', enabled = isDevelopment } = options

  const formatMessage = (level: LogLevel, message: string): string => {
    return `${prefix} [${level.toUpperCase()}] ${message}`
  }

  return {
    /**
     * Log debug information (only in development)
     */
    debug: (message: string, ...args: unknown[]): void => {
      if (!enabled) return
      // eslint-disable-next-line no-console
      console.debug(formatMessage('debug', message), ...args)
    },

    /**
     * Log informational messages
     */
    info: (message: string, ...args: unknown[]): void => {
      if (!enabled) return
      // eslint-disable-next-line no-console
      console.info(formatMessage('info', message), ...args)
    },

    /**
     * Log warning messages
     */
    warn: (message: string, ...args: unknown[]): void => {
      if (!enabled) return
      // eslint-disable-next-line no-console
      console.warn(formatMessage('warn', message), ...args)
    },

    /**
     * Log error messages
     * Note: Errors are logged even in production for debugging purposes
     * In a production app, you might want to send these to an error reporting service
     */
    error: (message: string, error?: unknown, ...args: unknown[]): void => {
      // Always log errors, even in production (or send to error reporting)
      const errorDetails = error instanceof Error ? error.message : error
      // eslint-disable-next-line no-console
      console.error(formatMessage('error', message), errorDetails, ...args)
    },

    /**
     * Create a child logger with a custom prefix
     */
    child: (childPrefix: string): ReturnType<typeof createLogger> => {
      return createLogger({
        prefix: `${prefix}[${childPrefix}]`,
        enabled,
      })
    },
  }
}

/**
 * Default logger instance
 */
export const logger = createLogger()

/**
 * Export factory for custom loggers
 */
export { createLogger }

/**
 * Type exports
 */
export type Logger = ReturnType<typeof createLogger>
