import { type ClassValue, clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export const getToday = (): string => {
  const today = new Date()
  return today.toISOString().split('T')[0]
}
