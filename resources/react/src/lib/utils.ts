import { type ClassValue, clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export const getToday = (): string => {
  const today = new Date()
  return today.toISOString().split('T')[0]
}

export const formatDateForAPI = (date: Date): string => {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

/**
 * Format seconds as duration string
 * - Shows MM:SS when hours is 0 (e.g., "10:34")
 * - Shows HH:MM:SS when hours > 0 (e.g., "01:10:34")
 */
export function formatDuration(seconds: number): string {
  const hours = Math.floor(seconds / 3600)
  const mins = Math.floor((seconds % 3600) / 60)
  const secs = Math.floor(seconds % 60)

  if (hours === 0) {
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
  }
  return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
}

/**
 * Format a number with decimals, removing unnecessary trailing .0
 * Examples: 100.0 → "100", 17.5 → "17.5", 0.0 → "0"
 */
export function formatDecimal(value: number, decimals: number = 1): string {
  if (!Number.isFinite(value)) {
    return String(value)
  }
  const fixed = value.toFixed(decimals)
  return fixed.endsWith('.0') ? fixed.slice(0, -2) : fixed
}

/**
 * Format numbers in compact notation (k, M) for display
 * Examples: 999 → "999", 1500 → "1.5k", 2340000 → "2.34M"
 */
export function formatCompactNumber(num: number): string {
  if (!Number.isFinite(num)) return String(num)
  if (num >= 1000000) return `${formatDecimal(num / 1000000)}M`
  if (num >= 1000) return `${formatDecimal(num / 1000)}k`
  return num.toLocaleString()
}
