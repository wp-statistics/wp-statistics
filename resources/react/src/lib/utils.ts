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
 * Format a number with decimals, removing unnecessary trailing .0
 * Examples: 100.0 → "100", 17.5 → "17.5", 0.0 → "0"
 */
export function formatDecimal(value: number, decimals: number = 1): string {
  const fixed = value.toFixed(decimals)
  return fixed.endsWith('.0') ? fixed.slice(0, -2) : fixed
}
