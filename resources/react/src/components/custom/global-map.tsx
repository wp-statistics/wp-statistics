import { Button } from '@components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@components/ui/card'
import { Tabs, TabsList, TabsTrigger } from '@components/ui/tabs'
import { getCityCoordinates } from '@lib/city-coordinates'
import { getCountryCenter, getCountryZoomLevel } from '@lib/country-centers'
import { cn } from '@lib/utils'
import { getCitiesDataQueryOptions } from '@services/geographic/get-cities-data'
import { useQuery } from '@tanstack/react-query'
import { ArrowLeft, Loader2, Minus, Plus } from 'lucide-react'
import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { ComposableMap, Geographies, Geography, ZoomableGroup } from 'react-simple-maps'

import { COLOR_SCALE, FAKE_CITY_DATA, MAP_URLS } from '@/constants/map-data'
import type { MapViewMode, MetricOption } from '@/types/geographic'

export interface CountryData {
  code: string
  name: string
  flag?: string
  visitors: number
}

export interface GlobalMapData {
  countries: CountryData[]
}

export interface GlobalMapProps {
  title?: string
  data: GlobalMapData
  metric?: string
  showZoomControls?: boolean
  showLegend?: boolean
  pluginUrl?: string
  className?: string
  // New interactive features
  enableCityDrilldown?: boolean
  enableMetricToggle?: boolean
  availableMetrics?: MetricOption[]
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

  // Interactive features state
  const [viewMode, setViewMode] = useState<MapViewMode>('countries')
  const [selectedCountry, setSelectedCountry] = useState<{ code: string; name: string } | null>(null)
  const [selectedMetric, setSelectedMetric] = useState<'visitors' | 'views'>('visitors')
  const [provincesLoading, setProvincesLoading] = useState(false)
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
    const duration = 400 // ms - reduced for better performance

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

  // Fetch city data when a country is selected and animation is complete
  const shouldFetchCities = !!selectedCountry && enableCityDrilldown && !isAnimating

  const {
    data: citiesData,
    isLoading: citiesLoading,
    isError: citiesError,
  } = useQuery({
    ...getCitiesDataQueryOptions({
      countryCode: selectedCountry?.code || '',
      metric: selectedMetric,
      date_from: dateFrom,
      date_to: dateTo,
    }),
    enabled: shouldFetchCities,
    retry: false, // Don't retry on failure
  })

  const countryLookup = useMemo(() => {
    const m = new Map<string, CountryData>()
    ;(data.countries || []).forEach((c) => {
      const code = String(c.code).toUpperCase()
      m.set(code, c)
    })
    return m
  }, [data])

  // Calculate max value for color scale
  const maxVisitors = useMemo(() => {
    let max = 0
    data.countries?.forEach((c) => {
      if (c.visitors > max) max = c.visitors
    })
    return max
  }, [data])

  const getColorForValue = (value: number | null): string => {
    if (value == null || value === 0) return '#e5e7eb' // gray-200 for no data

    const normalized = value / maxVisitors
    if (normalized < 0.2) return COLOR_SCALE[0]
    if (normalized < 0.4) return COLOR_SCALE[1]
    if (normalized < 0.6) return COLOR_SCALE[2]
    if (normalized < 0.8) return COLOR_SCALE[3]
    if (normalized < 0.9) return COLOR_SCALE[4]
    return COLOR_SCALE[5]
  }

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
        <div className="text-sm">
          <div className="font-semibold">{geoName}</div>
          <div className="mt-1 text-xs text-muted-foreground">No data</div>
        </div>
      )
    }

    const metricLabel = selectedMetric === 'visitors' ? 'Visitors' : 'Views'
    const metricValue = countryData.visitors // Currently only visitors data available

    return (
      <div className="text-sm">
        <div className="flex items-center gap-2 mb-2">
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
          <div className="font-semibold">{countryData.name}</div>
        </div>
        <div className="text-xs">
          {metricLabel}: <span className="font-semibold">{metricValue.toLocaleString()}</span>
        </div>
        {enableCityDrilldown && viewMode === 'countries' && (
          <div className="text-xs text-muted-foreground mt-2 pt-2 border-t border-border">Click to view cities</div>
        )}
      </div>
    )
  }

  const handleZoomIn = () => {
    if (position.zoom >= 50) return
    setTargetPosition({ coordinates: position.coordinates as [number, number], zoom: position.zoom * 1.5 })
  }

  const handleZoomOut = () => {
    if (position.zoom <= 1) return
    setTargetPosition({ coordinates: position.coordinates as [number, number], zoom: position.zoom / 1.5 })
  }

  const handleMoveEnd = (newPosition: { coordinates: [number, number]; zoom: number }) => {
    // Only update if not animating
    if (!targetPosition) {
      setPosition(newPosition)
    }
  }

  // Calculate zoom level based on country's geographic bounds
  const calculateZoomForBounds = useCallback((geo: any): number => {
    try {
      // Get the bounding box of the geometry
      let minLon = Infinity,
        maxLon = -Infinity
      let minLat = Infinity,
        maxLat = -Infinity

      const processCoordinates = (coords: any) => {
        if (typeof coords[0] === 'number') {
          // This is a point [lon, lat]
          minLon = Math.min(minLon, coords[0])
          maxLon = Math.max(maxLon, coords[0])
          minLat = Math.min(minLat, coords[1])
          maxLat = Math.max(maxLat, coords[1])
        } else {
          // This is an array of coordinates, recurse
          coords.forEach(processCoordinates)
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

      // Calculate zoom based on dimension (adjust these values for better fit)
      // Larger countries have bigger dimensions, so they need less zoom
      if (maxDimension > 60) return 3.5 // Very large countries (Russia, Canada, USA, China)
      if (maxDimension > 40) return 4.5 // Large countries (Brazil, Australia)
      if (maxDimension > 25) return 6 // Medium-large (Iran, Algeria, Saudi Arabia)
      if (maxDimension > 15) return 7.5 // Medium (France, Spain, Turkey)
      if (maxDimension > 8) return 9 // Small (UK, Germany, Japan)
      if (maxDimension > 4) return 11 // Very small (Netherlands, Belgium)
      return 14 // Tiny (Singapore, Luxembourg)
    } catch (error) {
      console.warn('Error calculating zoom for country bounds:', error)
      return 6 // Default fallback
    }
  }, [])

  const handleCountryClick = (countryCode: string, countryName: string, geo?: any) => {
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
    setTargetPosition({ coordinates: [0, 0], zoom: 1 })
    setTooltip({ visible: false, x: 0, y: 0, content: '' })
  }

  const handleMetricChange = (metric: string) => {
    setSelectedMetric(metric as 'visitors' | 'views')
    setTooltip({ visible: false, x: 0, y: 0, content: '' }) // Hide tooltip on metric change
  }

  // Get city data items with fallback to fake data
  const realCityItems = citiesData?.data?.data?.items || []

  // Use real API data if available, otherwise fallback to FAKE_CITY_DATA for demonstration
  const cityItems =
    realCityItems.length > 0
      ? realCityItems
      : selectedCountry && FAKE_CITY_DATA[selectedCountry.code.toUpperCase()]
        ? FAKE_CITY_DATA[selectedCountry.code.toUpperCase()]
        : []

  // Filter cities with coordinates
  const citiesWithCoords = useMemo(
    () =>
      cityItems
        .map((city) => ({
          ...city,
          coordinates: getCityCoordinates(city.city_name, city.country_code),
        }))
        .filter((city) => city.coordinates !== null),
    [cityItems]
  )

  // Cities without coordinates (for fallback list)
  const citiesWithoutCoords = useMemo(
    () => cityItems.filter((city) => !getCityCoordinates(city.city_name, city.country_code)),
    [cityItems]
  )

  // Aggregate city data by region for province view
  const regionData = useMemo(() => {
    const regions = new Map<string, { name: string; visitors: number; views: number; cities: string[] }>()
    cityItems.forEach((city) => {
      const regionName = city.city_region_name || 'Unknown'
      const existing = regions.get(regionName) || { name: regionName, visitors: 0, views: 0, cities: [] }
      existing.visitors += city.visitors || 0
      existing.views += city.views || 0
      existing.cities.push(city.city_name)
      regions.set(regionName, existing)
    })
    return regions
  }, [cityItems])

  // Total values for percentage calculation
  const totalRegionValue = useMemo(
    () => cityItems.reduce((sum, c) => sum + (c[selectedMetric] || 0), 0) || 1,
    [cityItems, selectedMetric]
  )

  // Helper to match province name from GeoJSON to our region data
  const getRegionMatch = (
    provinceName: string
  ): { name: string; visitors: number; views: number; cities: string[] } | null => {
    // Direct match
    if (regionData.has(provinceName)) {
      return regionData.get(provinceName)!
    }
    // Try case-insensitive match
    for (const [key, value] of regionData.entries()) {
      if (key.toLowerCase() === provinceName.toLowerCase()) {
        return value
      }
      // Partial match (province name might be longer)
      if (
        provinceName.toLowerCase().includes(key.toLowerCase()) ||
        key.toLowerCase().includes(provinceName.toLowerCase())
      ) {
        return value
      }
    }
    return null
  }

  const makeRegionTooltip = (
    provinceName: string,
    region: { name: string; visitors: number; views: number; cities: string[] } | null
  ) => {
    const value = region ? region[selectedMetric] : 0
    const percentage = (value / totalRegionValue) * 100

    return (
      <div className="text-sm">
        <div className="text-xs text-muted-foreground">{provinceName} (Province)</div>
        <div className="font-semibold mb-2">{region?.name || provinceName}</div>
        <div className="border-t border-border pt-2 space-y-1">
          <div className="text-xs flex justify-between gap-4">
            <span className="text-muted-foreground uppercase">{selectedMetric}</span>
            <span className="font-semibold">{(value || 0).toLocaleString()}</span>
          </div>
          <div className="text-xs text-muted-foreground">{percentage.toFixed(1)}% of total</div>
        </div>
      </div>
    )
  }

  const makeCityTooltip = (city: (typeof citiesWithCoords)[0]) => {
    const totalCountryValue = cityItems.reduce((sum, c) => sum + (c[selectedMetric] || 0), 0) || 1
    const percentage = ((city[selectedMetric] || 0) / totalCountryValue) * 100

    return (
      <div className="text-sm">
        <div className="font-semibold">{city.city_name}</div>
        <div className="text-xs text-muted-foreground mb-2">
          {city.city_region_name}
          {city.city_region_name && city.country_name ? ', ' : ''}
          {city.country_name}
        </div>
        <div className="border-t border-border pt-2 space-y-1">
          <div className="text-xs flex justify-between">
            <span>Visitors:</span>
            <span className="font-semibold">{(city.visitors || 0).toLocaleString()}</span>
          </div>
          <div className="text-xs flex justify-between">
            <span>Views:</span>
            <span className="font-semibold">{(city.views || 0).toLocaleString()}</span>
          </div>
          <div className="text-xs flex justify-between text-muted-foreground">
            <span>% of Country:</span>
            <span>{percentage.toFixed(1)}%</span>
          </div>
        </div>
      </div>
    )
  }

  return (
    <Card className={cn('h-full flex flex-col', className)}>
      {title && (
        <CardHeader>
          <CardTitle>{title}</CardTitle>
        </CardHeader>
      )}
      <CardContent className="flex-1 flex flex-col">
        <div
          ref={containerRef}
          className="flex-1 relative bg-muted/10 rounded-lg overflow-hidden"
          style={{ minHeight: '400px' }}
        >
          {/* Zoom Controls */}
          {showZoomControls && (
            <div className="absolute left-4 top-4 z-10 flex flex-col gap-2">
              <Button
                variant="outline"
                size="icon"
                className="h-8 w-8 bg-white shadow-sm"
                onClick={handleZoomIn}
                disabled={position.zoom >= 50}
              >
                <Plus className="h-4 w-4" />
              </Button>
              <Button
                variant="outline"
                size="icon"
                className="h-8 w-8 bg-white shadow-sm"
                onClick={handleZoomOut}
                disabled={position.zoom <= 1}
              >
                <Minus className="h-4 w-4" />
              </Button>
            </div>
          )}

          {/* Metric Toggle */}
          {enableMetricToggle && availableMetrics.length > 1 && (
            <div className="absolute right-4 top-4 z-10">
              <Tabs value={selectedMetric} onValueChange={handleMetricChange}>
                <TabsList className="bg-white shadow-sm">
                  {availableMetrics.map((m) => (
                    <TabsTrigger key={m.value} value={m.value}>
                      {m.label}
                    </TabsTrigger>
                  ))}
                </TabsList>
              </Tabs>
            </div>
          )}

          {/* Back Button with Country Flag */}
          {viewMode === 'cities' && selectedCountry && (
            <div className="absolute left-4 bottom-4 z-10">
              <Button variant="outline" size="sm" className="bg-white shadow-sm gap-2" onClick={handleBackToWorld}>
                <ArrowLeft className="h-4 w-4" />
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

          {/* Loading State for Cities/Provinces */}
          {viewMode === 'cities' && (citiesLoading || provincesLoading) && (
            <div className="absolute inset-0 flex items-center justify-center bg-white/60 z-20">
              <div className="bg-white rounded-lg shadow-lg p-4 flex items-center gap-3">
                <Loader2 className="h-5 w-5 animate-spin text-primary" />
                <span className="text-sm">Loading regions...</span>
              </div>
            </div>
          )}

          {/* No Cities Available */}
          {viewMode === 'cities' && !citiesLoading && !citiesError && cityItems.length === 0 && (
            <div className="absolute inset-0 flex items-center justify-center z-20">
              <div className="bg-white rounded-lg shadow-lg p-6 max-w-sm text-center">
                <p className="text-sm font-medium mb-2">No city data available</p>
                <p className="text-xs text-muted-foreground mb-3">
                  No cities found for {selectedCountry?.name} in the selected date range.
                </p>
                <Button size="sm" variant="outline" onClick={handleBackToWorld}>
                  Back to World View
                </Button>
              </div>
            </div>
          )}

          <ComposableMap
            projection="geoEqualEarth"
            projectionConfig={{
              rotate: [0, 0, 0],
              center: [15, 15],
              scale: 160,
            }}
            width={800}
            height={400}
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
              filterZoomEvent={(evt) => !evt.ctrlKey}
            >
              <Geographies geography={MAP_URLS.countries}>
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
                      const visitors = countryData?.visitors ?? null
                      const fill = getColorForValue(visitors)
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
                                fill: viewMode === 'cities' ? '#e5e7eb' : fill,
                                outline: 'none',
                                stroke: '#ffffff',
                                strokeWidth: viewMode === 'cities' ? 0.3 : 0.5,
                                transition: 'fill 200ms ease',
                                pointerEvents: isInteractive ? 'all' : 'none',
                              },
                              hover: {
                                fill: isInteractive
                                  ? visitors == null
                                    ? '#d1d5db'
                                    : '#4338ca'
                                  : viewMode === 'cities'
                                    ? '#e5e7eb'
                                    : fill,
                                outline: 'none',
                                cursor: isInteractive && enableCityDrilldown && countryData ? 'pointer' : 'default',
                                strokeWidth: isInteractive ? 1 : viewMode === 'cities' ? 0.3 : 0.5,
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
              {viewMode === 'cities' && selectedCountry && !citiesLoading && !isAnimating && (
                <Geographies geography={MAP_URLS.provinces}>
                  {({ geographies }) => {
                    // Set provincesLoading to false once geographies are loaded
                    if (geographies.length > 0 && provincesLoading) {
                      setTimeout(() => setProvincesLoading(false), 100)
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
                                fill: hasData ? '#c7d2fe' : '#f3f4f6', // indigo-200 for data, gray-100 for no data
                                stroke: '#d1d5db', // gray-300 - lighter border
                                strokeWidth: 0.15,
                                outline: 'none',
                                transition: 'none', // Disable transition for better performance
                              },
                              hover: {
                                fill: hasData ? '#a5b4fc' : '#e5e7eb', // indigo-300 or gray-200 on hover
                                stroke: '#9ca3af', // gray-400 on hover
                                strokeWidth: 0.25,
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

          {/* Fallback: Cities without coordinates */}
          {viewMode === 'cities' && !citiesLoading && !citiesError && citiesWithoutCoords.length > 0 && (
            <div className="absolute right-4 top-20 w-72 max-h-96 overflow-auto bg-white shadow-lg rounded-lg p-4 z-10">
              <h3 className="font-semibold text-sm mb-3">
                Cities in {selectedCountry?.name}
                <span className="text-xs text-muted-foreground font-normal ml-2">
                  ({citiesWithoutCoords.length} without map coordinates)
                </span>
              </h3>
              <div className="space-y-2">
                {citiesWithoutCoords.map((city) => (
                  <div key={city.city_id} className="py-2 border-b last:border-0">
                    <div className="font-medium text-sm">{city.city_name}</div>
                    <div className="text-xs text-muted-foreground">{city.city_region_name}</div>
                    <div className="text-xs mt-1 flex gap-3">
                      <span>Visitors: {(city.visitors || 0).toLocaleString()}</span>
                      <span>Views: {(city.views || 0).toLocaleString()}</span>
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Tooltip */}
          {tooltip.visible && (
            <div
              className="pointer-events-none absolute z-50 max-w-xs p-3 rounded-lg shadow-lg bg-popover text-popover-foreground border"
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
          <div className="mt-6 flex justify-start">
            {viewMode === 'cities' && selectedCountry ? (
              // Region legend - show data/no data colors
              <div className="flex items-center gap-6">
                <div className="flex items-center gap-2">
                  <div
                    className="w-4 h-3 rounded-sm"
                    style={{ backgroundColor: '#f3f4f6', border: '1px solid #9ca3af' }}
                  ></div>
                  <span className="text-xs text-muted-foreground">No data</span>
                </div>
                <div className="flex items-center gap-2">
                  <div
                    className="w-4 h-3 rounded-sm"
                    style={{ backgroundColor: '#c7d2fe', border: '1px solid #9ca3af' }}
                  ></div>
                  <span className="text-xs text-muted-foreground">Has {selectedMetric}</span>
                </div>
              </div>
            ) : maxVisitors === 0 || data.countries.length === 0 ? (
              <div className="text-sm text-muted-foreground">No data available for the selected period</div>
            ) : (
              <div className="flex items-center gap-2 w-1/2">
                <span className="text-sm text-muted-foreground">0</span>
                <div className="flex-1 h-2 rounded-full overflow-hidden flex">
                  {COLOR_SCALE.map((color, i) => (
                    <div key={i} className="flex-1" style={{ backgroundColor: color }} />
                  ))}
                </div>
                <span className="text-sm text-muted-foreground">
                  {maxVisitors > 0 ? `${(maxVisitors / 1000).toFixed(0)}k` : '0'}
                </span>
              </div>
            )}
          </div>
        )}
      </CardContent>
    </Card>
  )
}
