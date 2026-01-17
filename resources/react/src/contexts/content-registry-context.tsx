/**
 * Content Registry Context
 *
 * Provides a unified registration system for premium content in both:
 * - Overview Widgets: Small components on overview pages (e.g., Top Entry Pages widget)
 * - Report Pages: Full page content (e.g., Entry Pages report)
 * - Config-based Reports: Simple table reports with minimal config
 *
 * Core provides infrastructure, Premium provides content.
 *
 * Supports both:
 * 1. React context-based registration (via useContentRegistry hook)
 * 2. Global API registration (via window.wpsContentRegistry) for external plugins
 *
 * Three levels of page registration:
 * - Level 1: registerReport() - Config-based table reports (minimal code)
 * - Level 2: registerReport() with slots - Config + custom components
 * - Level 3: registerPageContent() - Full custom component
 */

import {
  createContext,
  type ReactNode,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
} from 'react'
import type { NavigateFunction } from '@tanstack/react-router'
import type { ReportConfig } from '@/components/report-page-renderer'

// Widget registration types
export interface WidgetRenderProps {
  data: unknown[]
  totals: Record<string, unknown>
  isCompareEnabled: boolean
  comparisonDateLabel: string
  navigate: NavigateFunction
  getTotalFromResponse: (totals: Record<string, unknown> | undefined, key: string) => number
}

export interface RegisteredWidget {
  id: string
  label: string
  defaultVisible: boolean
  queryId: string
  render: (props: WidgetRenderProps) => React.ReactNode
  link?: { title: string; to: string }
}

// Page content registration types
export interface PageContentProps {
  // Props can be extended as needed by specific page content
  [key: string]: unknown
}

export interface RegisteredPageContent {
  pageId: string
  render: (props?: PageContentProps) => React.ReactNode
}

// Report registration types (Level 1 & 2)
export interface RegisteredReport<TData = unknown, TRecord = unknown> {
  pageId: string
  config: ReportConfig<TData, TRecord>
}

// Combined registry context interface
export interface ContentRegistryContextValue {
  // Widget registration
  registerWidget: (pageId: string, widget: RegisteredWidget) => void
  unregisterWidget: (pageId: string, widgetId: string) => void
  getWidgetsForPage: (pageId: string) => RegisteredWidget[]

  // Page content registration (Level 3: Full custom component)
  registerPageContent: (pageId: string, content: Omit<RegisteredPageContent, 'pageId'>) => void
  unregisterPageContent: (pageId: string) => void
  getPageContent: (pageId: string) => RegisteredPageContent | null

  // Report registration (Level 1 & 2: Config-based reports)
  registerReport: <TData = unknown, TRecord = unknown>(pageId: string, config: ReportConfig<TData, TRecord>) => void
  unregisterReport: (pageId: string) => void
  getReport: <TData = unknown, TRecord = unknown>(pageId: string) => RegisteredReport<TData, TRecord> | null
}

// Global registry storage (accessible outside React)
const globalWidgetRegistry = new Map<string, Map<string, RegisteredWidget>>()
const globalPageContentRegistry = new Map<string, RegisteredPageContent>()
const globalReportRegistry = new Map<string, RegisteredReport>()
const globalListeners = new Set<() => void>()

// Global API for external registration (premium plugin)
function globalRegisterWidget(pageId: string, widget: RegisteredWidget): void {
  if (!globalWidgetRegistry.has(pageId)) {
    globalWidgetRegistry.set(pageId, new Map())
  }
  globalWidgetRegistry.get(pageId)!.set(widget.id, widget)
  globalListeners.forEach((listener) => listener())
}

function globalUnregisterWidget(pageId: string, widgetId: string): void {
  globalWidgetRegistry.get(pageId)?.delete(widgetId)
  globalListeners.forEach((listener) => listener())
}

function globalGetWidgetsForPage(pageId: string): RegisteredWidget[] {
  const pageWidgets = globalWidgetRegistry.get(pageId)
  if (!pageWidgets) return []
  return Array.from(pageWidgets.values())
}

function globalRegisterPageContent(pageId: string, content: Omit<RegisteredPageContent, 'pageId'>): void {
  globalPageContentRegistry.set(pageId, { ...content, pageId })
  globalListeners.forEach((listener) => listener())
  // Dispatch event for components to react
  window.dispatchEvent(new CustomEvent('wps:content-registered', { detail: { pageId } }))
}

function globalUnregisterPageContent(pageId: string): void {
  globalPageContentRegistry.delete(pageId)
  globalListeners.forEach((listener) => listener())
}

function globalGetPageContent(pageId: string): RegisteredPageContent | null {
  return globalPageContentRegistry.get(pageId) || null
}

// Global API for report registration (Level 1 & 2)
function globalRegisterReport<TData = unknown, TRecord = unknown>(
  pageId: string,
  config: ReportConfig<TData, TRecord>
): void {
  globalReportRegistry.set(pageId, { pageId, config: config as ReportConfig })
  globalListeners.forEach((listener) => listener())
  // Dispatch event for components to react
  window.dispatchEvent(new CustomEvent('wps:report-registered', { detail: { pageId } }))
}

function globalUnregisterReport(pageId: string): void {
  globalReportRegistry.delete(pageId)
  globalListeners.forEach((listener) => listener())
}

function globalGetReport<TData = unknown, TRecord = unknown>(
  pageId: string
): RegisteredReport<TData, TRecord> | null {
  return (globalReportRegistry.get(pageId) as RegisteredReport<TData, TRecord>) || null
}

// Expose global API on window for premium plugin
declare global {
  interface Window {
    wpsContentRegistry?: {
      registerWidget: typeof globalRegisterWidget
      unregisterWidget: typeof globalUnregisterWidget
      getWidgetsForPage: typeof globalGetWidgetsForPage
      registerPageContent: typeof globalRegisterPageContent
      unregisterPageContent: typeof globalUnregisterPageContent
      getPageContent: typeof globalGetPageContent
      registerReport: typeof globalRegisterReport
      unregisterReport: typeof globalUnregisterReport
      getReport: typeof globalGetReport
    }
  }
}

// Initialize global API immediately
window.wpsContentRegistry = {
  registerWidget: globalRegisterWidget,
  unregisterWidget: globalUnregisterWidget,
  getWidgetsForPage: globalGetWidgetsForPage,
  registerPageContent: globalRegisterPageContent,
  unregisterPageContent: globalUnregisterPageContent,
  getPageContent: globalGetPageContent,
  registerReport: globalRegisterReport,
  unregisterReport: globalUnregisterReport,
  getReport: globalGetReport,
}

const ContentRegistryContext = createContext<ContentRegistryContextValue | undefined>(undefined)

export interface ContentRegistryProviderProps {
  children: ReactNode
}

export function ContentRegistryProvider({ children }: ContentRegistryProviderProps) {
  // State to trigger re-renders when global registry changes
  const [, forceUpdate] = useState(0)

  // Subscribe to global registry changes
  useEffect(() => {
    const listener = () => forceUpdate((n) => n + 1)
    globalListeners.add(listener)
    return () => {
      globalListeners.delete(listener)
    }
  }, [])

  // Methods that delegate to global registry
  const registerWidget = useCallback((pageId: string, widget: RegisteredWidget) => {
    globalRegisterWidget(pageId, widget)
  }, [])

  const unregisterWidget = useCallback((pageId: string, widgetId: string) => {
    globalUnregisterWidget(pageId, widgetId)
  }, [])

  const getWidgetsForPage = useCallback((pageId: string): RegisteredWidget[] => {
    return globalGetWidgetsForPage(pageId)
  }, [])

  const registerPageContent = useCallback((pageId: string, content: Omit<RegisteredPageContent, 'pageId'>) => {
    globalRegisterPageContent(pageId, content)
  }, [])

  const unregisterPageContent = useCallback((pageId: string) => {
    globalUnregisterPageContent(pageId)
  }, [])

  const getPageContent = useCallback((pageId: string): RegisteredPageContent | null => {
    return globalGetPageContent(pageId)
  }, [])

  // Report registration methods (Level 1 & 2)
  const registerReport = useCallback(<TData = unknown, TRecord = unknown>(
    pageId: string,
    config: ReportConfig<TData, TRecord>
  ) => {
    globalRegisterReport(pageId, config)
  }, [])

  const unregisterReport = useCallback((pageId: string) => {
    globalUnregisterReport(pageId)
  }, [])

  const getReport = useCallback(<TData = unknown, TRecord = unknown>(
    pageId: string
  ): RegisteredReport<TData, TRecord> | null => {
    return globalGetReport(pageId)
  }, [])

  const value: ContentRegistryContextValue = useMemo(
    () => ({
      registerWidget,
      unregisterWidget,
      getWidgetsForPage,
      registerPageContent,
      unregisterPageContent,
      getPageContent,
      registerReport,
      unregisterReport,
      getReport,
    }),
    [
      registerWidget,
      unregisterWidget,
      getWidgetsForPage,
      registerPageContent,
      unregisterPageContent,
      getPageContent,
      registerReport,
      unregisterReport,
      getReport,
    ]
  )

  return (
    <ContentRegistryContext.Provider value={value}>
      {children}
    </ContentRegistryContext.Provider>
  )
}

/**
 * Hook to access the content registry
 */
export function useContentRegistry(): ContentRegistryContextValue {
  const context = useContext(ContentRegistryContext)
  if (!context) {
    throw new Error('useContentRegistry must be used within a ContentRegistryProvider')
  }
  return context
}
