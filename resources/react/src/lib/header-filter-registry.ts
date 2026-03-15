/**
 * Header Filter Registry
 *
 * Extensible registry for header filter renderers used by PhpReportRoute.
 * Premium can register custom filter types without modifying the free repo.
 */

import type { ComponentType } from 'react'

import type { ReportConfig } from '@/components/report-page-renderer'

/** Props passed to every header filter renderer. */
export interface HeaderFilterRendererProps {
  config: ReportConfig
  headerFilter: PhpHeaderFilter
}

/** Registry of header filter renderers, keyed by filter type string. */
const HEADER_FILTER_RENDERERS: Record<string, ComponentType<HeaderFilterRendererProps>> = {}

/**
 * Register a header filter renderer for a given type.
 */
export function registerHeaderFilter(type: string, component: ComponentType<HeaderFilterRendererProps>) {
  HEADER_FILTER_RENDERERS[type] = component
}

/**
 * Look up the renderer for a header filter type. Returns undefined if not registered.
 */
export function getHeaderFilterRenderer(type: string): ComponentType<HeaderFilterRendererProps> | undefined {
  return HEADER_FILTER_RENDERERS[type]
}
