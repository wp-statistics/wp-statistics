/**
 * Design Tokens - Centralized semantic color classes
 *
 * This file provides semantic class names that map to Tailwind CSS classes
 * or CSS variables, ensuring consistent styling across the application.
 *
 * Usage:
 * - Import the semantic class you need
 * - Use with cn() utility for combining with other classes
 *
 * @example
 * import { semanticColors } from '@/constants/design-tokens'
 * <span className={cn(semanticColors.error, 'text-sm')}>Error message</span>
 */

/**
 * Semantic color classes for consistent theming
 *
 * Maps semantic meaning to Tailwind/CSS variable classes.
 * Update the values here to change colors application-wide.
 */
export const semanticColors = {
  // Status colors - use these instead of hardcoded red/green/yellow
  error: 'text-destructive', // For error messages (replaces text-red-500/600)
  errorBg: 'bg-destructive', // For error backgrounds
  errorLight: 'text-destructive/80', // For lighter error text

  success: 'text-emerald-600', // For success indicators
  successBg: 'bg-emerald-600', // For success backgrounds
  successLight: 'text-emerald-400', // For lighter success text

  warning: 'text-amber-600', // For warning messages
  warningBg: 'bg-amber-600', // For warning backgrounds

  // Trend indicators - for percentage changes
  trendPositive: 'text-emerald-600', // Positive change (up arrow)
  trendPositiveLight: 'text-emerald-400', // Positive change (light, for dark backgrounds)
  trendNegative: 'text-red-600', // Negative change (down arrow)
  trendNegativeLight: 'text-red-400', // Negative change (light, for dark backgrounds)
  trendNeutral: 'text-neutral-400', // No change (0%)

  // Interactive states
  primary: 'text-primary', // Primary action color
  primaryBg: 'bg-primary', // Primary background
  muted: 'text-muted-foreground', // Muted/secondary text
  mutedBg: 'bg-muted', // Muted backgrounds

  // Status indicators (e.g., online/offline)
  online: 'bg-red-500', // Live/online indicator (pulsing dot)
  offline: 'bg-neutral-400', // Offline indicator
} as const

/**
 * Semantic spacing values (matches Tailwind spacing scale)
 * Use these for consistent padding/margin/gap values
 */
export const spacing = {
  none: '0',
  xs: '0.25rem', // 1 (4px)
  sm: '0.5rem', // 2 (8px)
  md: '1rem', // 4 (16px)
  lg: '1.5rem', // 6 (24px)
  xl: '2rem', // 8 (32px)
} as const

/**
 * Chart colors - use CSS variables for consistency with shadcn charts
 */
export const chartColors = {
  chart1: 'var(--chart-1)', // Blue
  chart2: 'var(--chart-2)', // Green
  chart3: 'var(--chart-3)', // Amber
  chart4: 'var(--chart-4)', // Red
  chart5: 'var(--chart-5)', // Purple
} as const

/**
 * Helper to get trend color class based on value
 */
export function getTrendColorClass(value: number, isNegative = false): string {
  if (value === 0) return semanticColors.trendNeutral
  if (isNegative) return semanticColors.trendNegative
  return semanticColors.trendPositive
}

/**
 * Typography tokens - Semantic classes for consistent text styling
 *
 * Standards:
 * - Minimum font size: 11px (text-[11px] or text-xs)
 * - NO uppercase text anywhere - use sentence case for better i18n
 * - Use these tokens instead of inline Tailwind classes
 *
 * Usage:
 * import { typography } from '@/constants/design-tokens'
 * <h1 className={typography.h1}>Page Title</h1>
 * <p className={typography.body}>Body text</p>
 */
export const typography = {
  // Headings - sentence case, no uppercase
  h1: 'text-2xl font-semibold leading-tight text-neutral-900',
  h2: 'text-xl font-semibold leading-tight text-neutral-800',
  h3: 'text-base font-semibold leading-snug text-neutral-800',
  h4: 'text-sm font-semibold leading-snug text-neutral-700',

  // Body text
  bodyLarge: 'text-base leading-normal text-neutral-700',
  body: 'text-sm leading-normal text-neutral-600',
  bodySmall: 'text-xs leading-normal text-neutral-500',

  // Labels - sentence case (no uppercase for better i18n)
  label: 'text-xs font-medium text-neutral-500',
  labelTiny: 'text-[11px] font-medium text-neutral-400',

  // Metrics
  metricValue: 'text-xl font-medium tabular-nums leading-none text-neutral-800',
  metricValueSmall: 'text-lg font-medium tabular-nums leading-none text-neutral-800',
  metricLabel: 'text-xs font-medium text-neutral-400',

  // Tables - sentence case headers
  tableHeader: 'text-xs font-medium text-neutral-500',
  tableCell: 'text-sm text-neutral-700',
  tableCellSecondary: 'text-xs text-neutral-500',

  // Interactive elements
  button: 'text-sm font-medium',
  buttonSmall: 'text-xs font-medium',
  link: 'text-sm font-medium',

  // Badges and pills
  badge: 'text-xs font-semibold',
  badgeSmall: 'text-[11px] font-medium',

  // Tooltips
  tooltip: 'text-xs leading-snug',
} as const

/**
 * Type exports for TypeScript usage
 */
export type SemanticColor = keyof typeof semanticColors
export type ChartColor = keyof typeof chartColors
export type TypographyToken = keyof typeof typography
