/**
 * Type-safe icon registry for dynamically loading Lucide icons by name.
 * This avoids the `as any` cast when accessing icons dynamically.
 */

import {
  BarChart3,
  CircleDot,
  Compass,
  ExternalLink,
  Eye,
  FileText,
  Globe2,
  Home,
  Laptop,
  LayoutDashboard,
  type LucideIcon,
  MonitorSmartphone,
  MousePointerClick,
  Search,
  Settings,
  Share2,
  Tag,
  TrendingUp,
  User,
  Users,
  type LucideProps,
  Circle,
} from 'lucide-react'

/**
 * Registry of icon names to their components.
 * Add icons here as needed - only commonly used icons are included.
 */
export const iconRegistry: Record<string, LucideIcon> = {
  BarChart3,
  CircleDot,
  Compass,
  ExternalLink,
  Eye,
  FileText,
  Globe2,
  Home,
  Laptop,
  LayoutDashboard,
  MonitorSmartphone,
  MousePointerClick,
  Search,
  Settings,
  Share2,
  Tag,
  TrendingUp,
  User,
  Users,
  Circle,
} as const

/**
 * Get an icon component by name with type safety.
 * Returns Circle as fallback if icon is not found.
 *
 * @param iconName - The Lucide icon name (e.g., "BarChart3", "Users")
 * @returns The icon component
 *
 * @example
 * ```tsx
 * const Icon = getIcon('BarChart3')
 * return <Icon className="h-4 w-4" />
 * ```
 */
export function getIcon(iconName: string): LucideIcon {
  return iconRegistry[iconName] || Circle
}

/**
 * Type guard to check if an icon name is valid
 */
export function isValidIconName(name: string): name is keyof typeof iconRegistry {
  return name in iconRegistry
}

/**
 * Get all available icon names
 */
export function getAvailableIconNames(): string[] {
  return Object.keys(iconRegistry)
}

// Re-export LucideIcon type for convenience
export type { LucideIcon, LucideProps }
