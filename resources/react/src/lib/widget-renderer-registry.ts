/**
 * Widget Renderer Registry
 *
 * Extensible registry for overview widget type renderers.
 * Premium can register custom widget types without modifying the free repo.
 */

import type { WidgetRenderContext } from '@/components/overview-widgets'

/** Options passed to each widget renderer function. */
export interface WidgetRenderOpts {
  colSpan: string
  contextMenu: React.ReactNode | undefined
  overviewMetrics: Array<Record<string, unknown>>
  ctx: WidgetRenderContext
}

/** A widget renderer returns ReactNode (or null to skip). */
export type WidgetRendererFn = (widget: PhpOverviewWidget, opts: WidgetRenderOpts) => React.ReactNode

/** Registry of widget type renderers. Keyed by PhpOverviewWidget.type. */
const WIDGET_RENDERERS: Record<string, WidgetRendererFn> = {}

/**
 * Register a widget renderer for a given type.
 */
export function registerWidgetRenderer(type: string, renderer: WidgetRendererFn) {
  WIDGET_RENDERERS[type] = renderer
}

/**
 * Look up the renderer for a widget type. Returns undefined if not registered.
 */
export function getWidgetRenderer(type: string): WidgetRendererFn | undefined {
  return WIDGET_RENDERERS[type]
}
