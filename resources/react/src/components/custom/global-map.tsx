import { Button } from '@components/ui/button'
import { Panel, PanelContent, PanelHeader, PanelTitle } from '@components/ui/panel'
import { Tabs, TabsList, TabsTrigger } from '@components/ui/tabs'
import { getCountryCenter, getCountryZoomLevel } from '@lib/country-centers'
import { createRegionMatcher, type RegionData } from '@lib/region-matcher'
import { calcSharePercentage, cn, formatDecimal } from '@lib/utils'
import { getRegionsByCountryQueryOptions } from '@services/geographic/get-regions-by-country'
import { useQuery } from '@tanstack/react-query'
import { ArrowLeft, Loader2, Minus, Plus } from 'lucide-react'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { ComposableMap, Geographies, Geography, ZoomableGroup } from 'react-simple-maps'

import { MapErrorBoundary } from '@/components/custom/map-error-boundary'
import { extractRows } from '@/lib/response-helpers'
import {
  calculateZoomFromDimension,
  COLOR_SCALE_THRESHOLDS,
  getColorFromScale,
  MAP_ANIMATION,
  MAP_PROJECTION,
  MAP_STROKES,
  MAP_ZOOM,
  REGION_COLORS,
  WORLD_CENTER,
} from '@/constants/map-constants'
import { COLOR_SCALE, getMapUrl } from '@/constants/map-data'
import type { MapViewMode, MetricOption } from '@/types/geographic'

// GeoJSON geometry coordinate types
type GeoJSONCoordinate = number[]
type GeoJSONCoordinates = GeoJSONCoordinate | GeoJSONCoordinate[] | GeoJSONCoordinate[][] | GeoJSONCoordinate[][][]

// GeoJSON feature geometry interface
interface GeoJSONGeometry {
  type: string
  coordinates: GeoJSONCoordinates
}

// GeoJSON feature interface for map geography
interface GeoJSONFeature {
  geometry?: GeoJSONGeometry
  properties?: Record<string, unknown>
  rsmKey?: string
}

export interface CountryData {
  code: string
  name: string
  flag?: string
  visitors: number
  views?: number
}

export interface GlobalMapData {
  countries: CountryData[]
}

export interface GlobalMapProps {
  title?: string
  /** Country data from batch request */
  data: GlobalMapData
  metric?: string
  showZoomControls?: boolean
  showLegend?: boolean
  pluginUrl?: string
  className?: string
  /** Loading state for countries data */
  isLoading?: boolean
  // Interactive features
  enableCityDrilldown?: boolean
  enableMetricToggle?: boolean
  availableMetrics?: MetricOption[]
  // Date range for region API queries (when clicking a country)
  dateFrom?: string
  dateTo?: string
}

export function GlobalMap({
  title,
  data,
  metric = 'Visitors',
  showZoomControls = true,
  showLegend = true,
  pluginUrl = '',
  className,
  isLoading = false,
  enableCityDrilldown = true,
  enableMetricToggle = true,
  availableMetrics = [
    { value: 'visitors', label: 'Visitors' },
    { value: 'views', label: 'Views' },
  ],
  dateFrom,
  dateTo,
}: GlobalMapProps) {
  const containerRef = useRef<HTMLDivElement>(null)
  const [position, setPosition] = useState({ coordinates: [0, 0] as [number, number], zoom: 1 })
  const [targetPosition, setTargetPosition] = useState<{ coordinates: [number, number]; zoom: number } | null>(null)
  const animationRef = useRef<number | null>(null)
  const [tooltip, setTooltip] = useState<{
    visible: boolean
    x: number
    y: number
    content: React.ReactNode
  }>({ visible: false, x: 0, y: 0, content: '' })
  const [showScrollHint, setShowScrollHint] = useState(false)
  const scrollHintTimeoutRef = useRef<NodeJS.Timeout | null>(null)

  // Interactive features state
  const [viewMode, setViewMode] = useState<MapViewMode>('countries')
  const [selectedCountry, setSelectedCountry] = useState<{ code: string; name: string } | null>(null)
  const [selectedMetric, setSelectedMetric] = useState<'visitors' | 'views'>('visitors')
  const [provincesLoading, setProvincesLoading] = useState(false)
  const [provincesGeoLoaded, setProvincesGeoLoaded] = useState(false)
  const [isAnimating, setIsAnimating] = useState(false)

  // Smooth zoom animation
  useEffect(() => {
    if (!targetPosition) {
      setIsAnimating(false)
      return
    }

    setIsAnimating(true)
    const startPosition = { ...position }
    const startTime = performance.now()
    const duration = MAP_ANIMATION.DURATION_MS

    const easeOutCubic = (t: number): number => 1 - Math.pow(1 - t, 3)

    const animate = (currentTime: number) => {
      const elapsed = currentTime - startTime
      const progress = Math.min(elapsed / duration, 1)
      const eased = easeOutCubic(progress)

      const newCoordinates: [number, number] = [
        startPosition.coordinates[0] + (targetPosition.coordinates[0] - startPosition.coordinates[0]) * eased,
        startPosition.coordinates[1] + (targetPosition.coordinates[1] - startPosition.coordinates[1]) * eased,
      ]
      const newZoom = startPosition.zoom + (targetPosition.zoom - startPosition.zoom) * eased

      setPosition({ coordinates: newCoordinates, zoom: newZoom })

      if (progress < 1) {
        animationRef.current = requestAnimationFrame(animate)
      } else {
        setTargetPosition(null)
        setIsAnimating(false)
      }
    }

    animationRef.current = requestAnimationFrame(animate)

    return () => {
      if (animationRef.current) {
        cancelAnimationFrame(animationRef.current)
      }
    }
  }, [targetPosition])

  // Handle wheel event to show hint when user scrolls without Cmd/Ctrl
  const handleWheel = useCallback((e: React.WheelEvent) => {
    if (!e.ctrlKey && !e.metaKey) {
      setShowScrollHint(true)
      if (scrollHintTimeoutRef.current) {
        clearTimeout(scrollHintTimeoutRef.current)
      }
      scrollHintTimeoutRef.current = setTimeout(() => {
        setShowScrollHint(false)
      }, MAP_ANIMATION.SCROLL_HINT_TIMEOUT_MS)
    }
  }, [])

  // Cleanup timeout on unmount
  useEffect(() => {
    return () => {
      if (scrollHintTimeoutRef.current) {
        clearTimeout(scrollHintTimeoutRef.current)
      }
    }
  }, [])

  // Handle provincesLoading state change when GeoJSON has loaded
  useEffect(() => {
    if (provincesGeoLoaded && provincesLoading) {
      const timer = setTimeout(() => {
        setProvincesLoading(false)
      }, MAP_ANIMATION.PROVINCES_LOADING_DELAY_MS)
      return () => clearTimeout(timer)
    }
  }, [provincesGeoLoaded, provincesLoading])

  // Reset provincesGeoLoaded when switching to a new country
  useEffect(() => {
    if (viewMode === 'cities' && selectedCountry) {
      setProvincesGeoLoaded(false)
    }
  }, [viewMode, selectedCountry?.code])

  // Fetch regions data when a country is selected and animation is complete
  const shouldFetchRegions = !!selectedCountry && enableCityDrilldown && !isAnimating && !!dateFrom && !!dateTo

  const {
    data: regionsResponse,
    isLoading: regionsLoading,
    isError: regionsError,
  } = useQuery({
    ...getRegionsByCountryQueryOptions({
      countryCode: selectedCountry?.code.toUpperCase() || '',
      dateFrom: dateFrom || '',
      dateTo: dateTo || '',
      sources: enableMetricToggle ? ['visitors', 'views'] : [selectedMetric],
    }),
    enabled: shouldFetchRegions,
  })

  const countryLookup = useMemo(() => {
    const m = new Map<string, CountryData>()
    ;(data.countries || []).forEach((c) => {
      const code = String(c.code).toUpperCase()
      m.set(code, c)
    })
    return m
  }, [data])

  // Calculate max value for color scale based on selected metric
  const maxValue = useMemo(() => {
    let max = 0
    data.countries?.forEach((c) => {
      const value = selectedMetric === 'views' ? c.views || 0 : c.visitors
      if (value > max) max = value
    })
    return max
  }, [data, selectedMetric])

  const getColorForValue = useCallback(
    (value: number | null): string => {
      if (value == null || value === 0) return COLOR_SCALE_THRESHOLDS.NO_DATA_COLOR
      const normalized = value / maxValue
      return getColorFromScale(normalized, COLOR_SCALE)
    },
    [maxValue]
  )

  const getCountryMatch = (geo: {
    properties: Record<string, unknown>
    id?: string
  }): { iso: string; data: CountryData | null } => {
    const props = geo.properties || {}

    const iso2 = String(props.ISO_A2 || props.iso_a2 || '').toUpperCase()
    const iso3 = String(props.ISO_A3 || props.iso_a3 || '').toUpperCase()

    if (iso2 && iso2 !== '-99' && countryLookup.has(iso2)) {
      return { iso: iso2, data: countryLookup.get(iso2)! }
    }

    if (iso3 && iso3 !== '-99' && countryLookup.has(iso3)) {
      return { iso: iso3, data: countryLookup.get(iso3)! }
    }

    return { iso: iso2 || iso3 || '', data: null }
  }

  const makeTooltipContent = (countryData: CountryData | null, geoName: string) => {
    if (!countryData) {
      return (
        <div>
          <div className="font-medium text-neutral-100">{geoName}</div>
          <div className="mt-1 text-neutral-400">No data</div>
        </div>
      )
    }

    const metricLabel = selectedMetric === 'visitors' ? 'Visitors' : 'Views'
    const metricValue = selectedMetric === 'views' ? countryData.views || 0 : countryData.visitors

    return (
      <div>
        <div className="flex items-center gap-2 mb-1.5">
          {countryData.code && (
            <img
              src={
                pluginUrl
                  ? `${pluginUrl}public/images/flags/${countryData.code.toLowerCase()}.svg`
                  : `public/images/flags/${countryData.code.toLowerCase()}.svg`
              }
              alt={countryData.name}
              className="w-4 h-3"
            />
          )}
          <span className="font-medium text-neutral-100">{countryData.name}</span>
        </div>
        <div className="text-neutral-300">
          {metricLabel}: <span className="font-medium tabular-nums">{metricValue.toLocaleString()}</span>
        </div>
        {enableCityDrilldown && viewMode === 'countries' && (
          <div className="text-neutral-400 mt-1.5 pt-1.5 border-t border-neutral-700">Click to view regions</div>
        )}
      </div>
    )
  }

  const handleZoomIn = () => {
    if (position.zoom >= MAP_ZOOM.MAX) return
    setTargetPosition({
      coordinates: position.coordinates as [number, number],
      zoom: position.zoom * MAP_ZOOM.STEP_MULTIPLIER,
    })
  }

  const handleZoomOut = () => {
    if (position.zoom <= MAP_ZOOM.MIN) return
    setTargetPosition({
      coordinates: position.coordinates as [number, number],
      zoom: position.zoom / MAP_ZOOM.STEP_MULTIPLIER,
    })
  }

  const handleMoveEnd = (newPosition: { coordinates: [number, number]; zoom: number }) => {
    // Only update if not animating
    if (!targetPosition) {
      setPosition(newPosition)
    }
  }

  // Calculate zoom level based on country's geographic bounds
  const calculateZoomForBounds = useCallback((geo: GeoJSONFeature): number => {
    try {
      // Get the bounding box of the geometry
      let minLon = Infinity,
        maxLon = -Infinity
      let minLat = Infinity,
        maxLat = -Infinity

      const processCoordinates = (coords: GeoJSONCoordinates): void => {
        if (typeof coords[0] === 'number') {
          // This is a point [lon, lat]
          minLon = Math.min(minLon, coords[0] as number)
          maxLon = Math.max(maxLon, coords[0] as number)
          minLat = Math.min(minLat, coords[1] as number)
          maxLat = Math.max(maxLat, coords[1] as number)
        } else {
          // This is an array of coordinates, recurse
          ;(coords as GeoJSONCoordinates[]).forEach(processCoordinates)
        }
      }

      if (geo.geometry?.coordinates) {
        processCoordinates(geo.geometry.coordinates)
      }

      // Calculate the width and height of the bounding box
      const width = maxLon - minLon
      const height = maxLat - minLat

      // Use the larger dimension to determine zoom
      const maxDimension = Math.max(width, height)

      // Use utility function with centralized thresholds
      return calculateZoomFromDimension(maxDimension)
    } catch (error) {
      console.warn('Error calculating zoom for country bounds:', error)
      return MAP_ZOOM.FALLBACK
    }
  }, [])

  const handleCountryClick = (countryCode: string, countryName: string, geo?: GeoJSONFeature) => {
    if (!enableCityDrilldown) return

    const center = getCountryCenter(countryCode)
    // Calculate zoom dynamically based on country size
    const zoom = geo ? calculateZoomForBounds(geo) : getCountryZoomLevel(countryCode)

    // Hide tooltip immediately on click
    setTooltip({ visible: false, x: 0, y: 0, content: '' })

    setProvincesLoading(true)
    setSelectedCountry({ code: countryCode, name: countryName })
    setViewMode('cities')
    // Use animated zoom
    setTargetPosition({
      coordinates: center,
      zoom,
    })
  }

  const handleBackToWorld = () => {
    setViewMode('countries')
    setSelectedCountry(null)
    // Use animated zoom back to world
    setTargetPosition({ coordinates: WORLD_CENTER, zoom: MAP_ZOOM.DEFAULT })
    setTooltip({ visible: false, x: 0, y: 0, content: '' })
  }

  const handleMetricChange = (metric: string) => {
    setSelectedMetric(metric as 'visitors' | 'views')
    setTooltip({ visible: false, x: 0, y: 0, content: '' }) // Hide tooltip on metric change
  }

  // Get region data from API response using standardized helper
  const regionItems = useMemo(() => {
    return extractRows(regionsResponse)
  }, [regionsResponse])

  // Build optimized region matcher with O(n) lookup instead of O(n²)
  // Uses pre-built lookup maps for exact, case-insensitive, and partial matching
  const regionMatcher = useMemo(() => {
    return createRegionMatcher(regionItems)
  }, [regionItems])

  // Total values for percentage calculation - now uses matcher's total function
  const totalRegionValue = useMemo(() => regionMatcher.total(selectedMetric), [regionMatcher, selectedMetric])

  // Helper to match province name from GeoJSON to our region data - O(1) average
  const getRegionMatch = useCallback(
    (provinceName: string): RegionData | null => {
      return regionMatcher.match(provinceName)
    },
    [regionMatcher]
  )

  const makeRegionTooltip = (
    provinceName: string,
    region: { name: string; visitors: number; views: number } | null
  ) => {
    const value = region ? region[selectedMetric] : 0
    const percentage = calcSharePercentage(value, totalRegionValue)

    return (
      <div>
        <div className="text-neutral-400">{provinceName} (Region)</div>
        <div className="font-medium text-neutral-100 mb-1.5">{region?.name || provinceName}</div>
        <div className="border-t border-neutral-700 pt-1.5 space-y-0.5">
          <div className="flex justify-between gap-4">
            <span className="text-neutral-400">{selectedMetric}</span>
            <span className="font-medium text-neutral-100 tabular-nums">{(value || 0).toLocaleString()}</span>
          </div>
          <div className="text-neutral-400 tabular-nums">{formatDecimal(percentage)}% of total</div>
        </div>
      </div>
    )
  }

  return (
    <Panel className={cn('h-full flex flex-col', className)}>
      {title && (
        <PanelHeader>
          <PanelTitle>{title}</PanelTitle>
        </PanelHeader>
      )}
      <PanelContent className="flex-1 flex flex-col">
        <div
          ref={containerRef}
          onWheel={handleWheel}
          className="flex-1 relative bg-muted/10 rounded-lg overflow-hidden min-h-[280px] md:min-h-[350px] lg:min-h-[400px]"
        >
          {/* Hint Overlay */}
          {showScrollHint && (
            <div className="absolute inset-0 z-20 flex items-center justify-center pointer-events-none">
              <div className="bg-neutral-800/90 text-white px-4 py-3 rounded-lg text-sm font-medium shadow-lg text-center leading-relaxed">
                {typeof navigator !== 'undefined' && /Mac|iPhone|iPad/.test(navigator.platform) ? (
                  <>
                    Use <kbd className="px-1.5 py-0.5 bg-neutral-700 rounded text-xs">⌘</kbd> + scroll to zoom
                    <br />
                    <kbd className="px-1.5 py-0.5 bg-neutral-700 rounded text-xs">⌘</kbd> + drag to move
                  </>
                ) : (
                  <>
                    Use <kbd className="px-1.5 py-0.5 bg-neutral-700 rounded text-xs">Ctrl</kbd> + scroll to zoom
                    <br />
                    <kbd className="px-1.5 py-0.5 bg-neutral-700 rounded text-xs">Ctrl</kbd> + drag to move
                  </>
                )}
              </div>
            </div>
          )}

          {/* Zoom Controls */}
          {showZoomControls && (
            <div className="absolute left-2 md:left-4 top-2 md:top-4 z-10 flex flex-col gap-1.5 md:gap-2">
              <Button
                variant="outline"
                size="icon"
                className="h-10 w-10 md:h-8 md:w-8 bg-white shadow-sm"
                onClick={handleZoomIn}
                disabled={position.zoom >= MAP_ZOOM.MAX}
                aria-label="Zoom in"
              >
                <Plus className="h-4 w-4" />
              </Button>
              <Button
                variant="outline"
                size="icon"
                className="h-10 w-10 md:h-8 md:w-8 bg-white shadow-sm"
                onClick={handleZoomOut}
                disabled={position.zoom <= MAP_ZOOM.MIN}
                aria-label="Zoom out"
              >
                <Minus className="h-4 w-4" />
              </Button>
            </div>
          )}

          {/* Metric Toggle */}
          {enableMetricToggle && availableMetrics.length > 1 && (
            <div className="absolute right-2 md:right-4 top-2 md:top-4 z-10">
              <Tabs value={selectedMetric} onValueChange={handleMetricChange}>
                <TabsList className="h-10 md:h-8 bg-white shadow-sm">
                  {availableMetrics.map((m) => (
                    <TabsTrigger key={m.value} value={m.value} className="text-xs px-3 md:px-2.5 py-2 md:py-1">
                      {m.label}
                    </TabsTrigger>
                  ))}
                </TabsList>
              </Tabs>
            </div>
          )}

          {/* Back Button with Country Flag */}
          {viewMode === 'cities' && selectedCountry && (
            <div className="absolute left-2 md:left-4 bottom-2 md:bottom-4 z-10">
              <Button
                variant="outline"
                size="sm"
                className="bg-white shadow-sm gap-2 text-xs h-10 md:h-8 px-3 md:px-2"
                onClick={handleBackToWorld}
              >
                <ArrowLeft className="h-3.5 w-3.5" />
                <img
                  src={
                    pluginUrl
                      ? `${pluginUrl}public/images/flags/${selectedCountry.code.toLowerCase()}.svg`
                      : `public/images/flags/${selectedCountry.code.toLowerCase()}.svg`
                  }
                  alt={selectedCountry.name}
                  className="w-5 h-3.5 object-cover rounded-sm"
                />
                <span>{selectedCountry.name}</span>
              </Button>
            </div>
          )}

          {/* Loading State for Countries */}
          {viewMode === 'countries' && isLoading && (
            <div className="absolute inset-0 flex items-center justify-center bg-white/60 z-20">
              <div className="bg-white rounded-lg shadow-lg p-4 flex items-center gap-3">
                <Loader2 className="h-5 w-5 animate-spin text-primary" />
                <span className="text-sm">Loading countries...</span>
              </div>
            </div>
          )}

          {/* Loading State for Regions */}
          {viewMode === 'cities' && (regionsLoading || provincesLoading) && (
            <div className="absolute inset-0 flex items-center justify-center bg-white/60 z-20">
              <div className="bg-white rounded-lg shadow-lg p-4 flex items-center gap-3">
                <Loader2 className="h-5 w-5 animate-spin text-primary" />
                <span className="text-sm">Loading regions...</span>
              </div>
            </div>
          )}

          {/* No Regions Available */}
          {viewMode === 'cities' && !regionsLoading && !regionsError && regionItems.length === 0 && (
            <div className="absolute inset-0 flex items-center justify-center z-20">
              <div className="bg-white rounded-lg shadow-lg p-6 max-w-sm text-center">
                <p className="text-sm font-medium text-neutral-800 mb-2">No region data available</p>
                <p className="text-xs text-neutral-500 mb-3">
                  No regions found for {selectedCountry?.name} in the selected date range.
                </p>
                <Button size="sm" variant="outline" className="text-xs" onClick={handleBackToWorld}>
                  Back to World View
                </Button>
              </div>
            </div>
          )}

          <MapErrorBoundary
            fallbackTitle="Map Visualization Error"
            fallbackMessage="Unable to render the map. Please try refreshing the page."
            onReset={handleBackToWorld}
          >
            <ComposableMap
              projection={MAP_PROJECTION.TYPE}
              projectionConfig={{
                rotate: MAP_PROJECTION.ROTATE,
                center: MAP_PROJECTION.CENTER,
                scale: MAP_PROJECTION.SCALE,
              }}
              width={MAP_PROJECTION.WIDTH}
              height={MAP_PROJECTION.HEIGHT}
              style={{
                width: '100%',
                height: '100%',
              }}
            >
              <ZoomableGroup
                zoom={position.zoom}
                center={position.coordinates as [number, number]}
                onMoveEnd={handleMoveEnd}
                translateExtent={[
                  [-Infinity, -Infinity],
                  [Infinity, Infinity],
                ]}
                filterZoomEvent={(evt) => evt.ctrlKey || evt.metaKey}
              >
                <Geographies geography={getMapUrl(pluginUrl, 'countries')}>
                  {({ geographies }) =>
                    geographies
                      .filter((geo) => {
                        const iso = (
                          geo.properties.ISO_A3 ||
                          geo.properties.ADM0_A3 ||
                          geo.properties.iso_a3 ||
                          geo.id ||
                          ''
                        ).toUpperCase()
                        const name = (geo.properties.NAME || geo.properties.name || '').toLowerCase()

                        // Filter out Antarctica
                        if (iso === 'ATA' || name.includes('antarctica')) return false

                        // When in cities view, only show the selected country
                        if (viewMode === 'cities' && selectedCountry) {
                          const { data: countryData } = getCountryMatch(geo)
                          return countryData?.code.toUpperCase() === selectedCountry.code.toUpperCase()
                        }

                        return true
                      })
                      .map((geo) => {
                        const { data: countryData } = getCountryMatch(geo)
                        const metricValue = countryData
                          ? selectedMetric === 'views'
                            ? (countryData.views ?? null)
                            : (countryData.visitors ?? null)
                          : null
                        const fill = getColorForValue(metricValue)
                        const name = geo.properties.NAME || geo.properties.name || 'Unknown'
                        const key = geo.rsmKey

                        // Disable interactions when in cities view or during animation
                        const isInteractive = viewMode === 'countries' && !isAnimating

                        return (
                          <g
                            key={key}
                            onMouseEnter={(e: React.MouseEvent) => {
                              if (!isInteractive || !containerRef.current) return
                              const rect = containerRef.current.getBoundingClientRect()
                              const content = makeTooltipContent(countryData, name)
                              setTooltip({
                                visible: true,
                                x: e.clientX - rect.left,
                                y: e.clientY - rect.top,
                                content,
                              })
                            }}
                            onMouseMove={(e: React.MouseEvent) => {
                              if (!isInteractive || !containerRef.current || !tooltip.visible) return
                              const rect = containerRef.current.getBoundingClientRect()
                              setTooltip((t) => ({
                                ...t,
                                x: e.clientX - rect.left,
                                y: e.clientY - rect.top,
                              }))
                            }}
                            onMouseLeave={() => {
                              if (isInteractive) {
                                setTooltip({ visible: false, x: 0, y: 0, content: '' })
                              }
                            }}
                            onClick={() => {
                              if (isInteractive && countryData && enableCityDrilldown) {
                                handleCountryClick(countryData.code, countryData.name, geo)
                              }
                            }}
                          >
                            <Geography
                              geography={geo}
                              style={{
                                default: {
                                  fill: viewMode === 'cities' ? COLOR_SCALE_THRESHOLDS.NO_DATA_COLOR : fill,
                                  outline: 'none',
                                  stroke: '#ffffff',
                                  strokeWidth:
                                    viewMode === 'cities'
                                      ? MAP_STROKES.COUNTRY_REGION_VIEW
                                      : MAP_STROKES.COUNTRY_DEFAULT,
                                  transition: 'fill 200ms ease',
                                  pointerEvents: isInteractive ? 'all' : 'none',
                                },
                                hover: {
                                  fill: isInteractive
                                    ? metricValue == null
                                      ? COLOR_SCALE_THRESHOLDS.NO_DATA_HOVER_COLOR
                                      : COLOR_SCALE_THRESHOLDS.HIGHLIGHT_COLOR
                                    : viewMode === 'cities'
                                      ? COLOR_SCALE_THRESHOLDS.NO_DATA_COLOR
                                      : fill,
                                  outline: 'none',
                                  cursor: isInteractive && enableCityDrilldown && countryData ? 'pointer' : 'default',
                                  strokeWidth: isInteractive
                                    ? MAP_STROKES.COUNTRY_HOVER
                                    : viewMode === 'cities'
                                      ? MAP_STROKES.COUNTRY_REGION_VIEW
                                      : MAP_STROKES.COUNTRY_DEFAULT,
                                },
                                pressed: { outline: 'none' },
                              }}
                            />
                          </g>
                        )
                      })
                  }
                </Geographies>

                {/* Province/Region Boundaries - shown when viewing a country */}
                {viewMode === 'cities' && selectedCountry && !regionsLoading && !isAnimating && (
                  <Geographies geography={getMapUrl(pluginUrl, 'provinces')}>
                    {({ geographies }) => {
                      // Signal that provinces GeoJSON has loaded (handled by useEffect)
                      if (geographies.length > 0 && !provincesGeoLoaded) {
                        queueMicrotask(() => setProvincesGeoLoaded(true))
                      }
                      return geographies
                        .filter((geo) => {
                          // Filter to only show provinces of the selected country
                          const isoA2 = (geo.properties.iso_a2 || geo.properties.ISO_A2 || '').toUpperCase()
                          const adm0A3 = (geo.properties.adm0_a3 || geo.properties.ADM0_A3 || '').toUpperCase()
                          const selectedCode = selectedCountry.code.toUpperCase()
                          return (
                            isoA2 === selectedCode ||
                            adm0A3 === selectedCode ||
                            (selectedCode === 'IR' && (isoA2 === 'IR' || adm0A3 === 'IRN'))
                          )
                        })
                        .map((geo) => {
                          const provinceName = geo.properties.name || geo.properties.NAME || 'Unknown'
                          const region = getRegionMatch(provinceName)
                          const hasData = region && region[selectedMetric] > 0

                          return (
                            <Geography
                              key={geo.rsmKey}
                              geography={geo}
                              onMouseEnter={(e: React.MouseEvent) => {
                                if (!containerRef.current || isAnimating) return
                                const rect = containerRef.current.getBoundingClientRect()
                                const content = makeRegionTooltip(provinceName, region)
                                setTooltip({
                                  visible: true,
                                  x: e.clientX - rect.left,
                                  y: e.clientY - rect.top,
                                  content,
                                })
                              }}
                              onMouseMove={(e: React.MouseEvent) => {
                                if (!containerRef.current || !tooltip.visible || isAnimating) return
                                const rect = containerRef.current.getBoundingClientRect()
                                setTooltip((t) => ({
                                  ...t,
                                  x: e.clientX - rect.left,
                                  y: e.clientY - rect.top,
                                }))
                              }}
                              onMouseLeave={() => {
                                if (!isAnimating) {
                                  setTooltip({ visible: false, x: 0, y: 0, content: '' })
                                }
                              }}
                              style={{
                                default: {
                                  fill: hasData ? REGION_COLORS.HAS_DATA : REGION_COLORS.NO_DATA,
                                  stroke: REGION_COLORS.STROKE,
                                  strokeWidth: MAP_STROKES.PROVINCE_DEFAULT,
                                  outline: 'none',
                                  transition: 'none', // Disable transition for better performance
                                },
                                hover: {
                                  fill: hasData ? REGION_COLORS.HAS_DATA_HOVER : REGION_COLORS.NO_DATA_HOVER,
                                  stroke: REGION_COLORS.STROKE_HOVER,
                                  strokeWidth: MAP_STROKES.PROVINCE_HOVER,
                                  cursor: 'pointer',
                                  outline: 'none',
                                },
                                pressed: { outline: 'none' },
                              }}
                            />
                          )
                        })
                    }}
                  </Geographies>
                )}
              </ZoomableGroup>
            </ComposableMap>
          </MapErrorBoundary>

          {/* Tooltip */}
          {tooltip.visible && (
            <div
              className="pointer-events-none absolute z-50 max-w-xs px-2.5 py-2 rounded shadow-lg bg-neutral-800 text-neutral-100 text-[11px] leading-tight"
              style={{
                left: tooltip.x + 12,
                top: tooltip.y + 12,
                transform: 'translate(0, 0)',
              }}
            >
              {tooltip.content}
            </div>
          )}
        </div>

        {/* Legend */}
        {showLegend && (
          <div className="mt-4 md:mt-6 flex justify-start">
            {viewMode === 'cities' && selectedCountry ? (
              // Region legend - show data/no data colors
              <div className="flex items-center gap-6">
                <div className="flex items-center gap-2">
                  <div
                    className="w-4 h-3 rounded-sm"
                    style={{
                      backgroundColor: REGION_COLORS.NO_DATA,
                      border: `1px solid ${REGION_COLORS.STROKE_HOVER}`,
                    }}
                  ></div>
                  <span className="text-xs text-neutral-500">No data</span>
                </div>
                <div className="flex items-center gap-2">
                  <div
                    className="w-4 h-3 rounded-sm"
                    style={{
                      backgroundColor: REGION_COLORS.HAS_DATA,
                      border: `1px solid ${REGION_COLORS.STROKE_HOVER}`,
                    }}
                  ></div>
                  <span className="text-xs text-neutral-500">Has {selectedMetric}</span>
                </div>
              </div>
            ) : maxValue === 0 || data.countries.length === 0 ? (
              <div className="text-xs text-neutral-500">No data available for the selected period</div>
            ) : (
              <div className="flex items-center gap-2 w-full md:w-1/2">
                <span className="text-xs text-neutral-500 tabular-nums">0</span>
                <div className="flex-1 h-2 rounded-full overflow-hidden flex">
                  {COLOR_SCALE.map((color, i) => (
                    <div key={i} className="flex-1" style={{ backgroundColor: color }} />
                  ))}
                </div>
                <span className="text-xs text-neutral-500 tabular-nums">
                  {maxValue >= 1000 ? `${(maxValue / 1000).toFixed(0)}k` : maxValue.toLocaleString()}
                </span>
              </div>
            )}
          </div>
        )}
      </PanelContent>
    </Panel>
  )
}
