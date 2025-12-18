import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { ComposableMap, Geographies, Geography, ZoomableGroup } from 'react-simple-maps'
import { Minus, Plus, ArrowLeft, Loader2 } from 'lucide-react'
import { useQuery } from '@tanstack/react-query'

import { Card, CardContent, CardHeader, CardTitle } from '@components/ui/card'
import { Button } from '@components/ui/button'
import { Tabs, TabsList, TabsTrigger } from '@components/ui/tabs'
import { cn } from '@lib/utils'
import { getCitiesDataQueryOptions } from '@services/geographic/get-cities-data'
import { getCityCoordinates } from '@lib/city-coordinates'
import { getCountryCenter, getCountryZoomLevel } from '@lib/country-centers'
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

const mapUrl =
  'https://raw.githubusercontent.com/nvkelso/natural-earth-vector/master/geojson/ne_110m_admin_0_countries.geojson'

// Province/state level boundaries for detailed country views
const provinceMapUrl =
  'https://raw.githubusercontent.com/nvkelso/natural-earth-vector/master/geojson/ne_10m_admin_1_states_provinces.geojson'

// Indigo color scale from Tailwind CSS
const indigoScale = [
  '#e0e7ff', // indigo-100
  '#c7d2fe', // indigo-200
  '#a5b4fc', // indigo-300
  '#818cf8', // indigo-400
  '#6366f1', // indigo-500
  '#4f46e5', // indigo-600
]

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

  // Smooth zoom animation
  useEffect(() => {
    if (!targetPosition) return

    const startPosition = { ...position }
    const startTime = performance.now()
    const duration = 600 // ms

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
      }
    }

    animationRef.current = requestAnimationFrame(animate)

    return () => {
      if (animationRef.current) {
        cancelAnimationFrame(animationRef.current)
      }
    }
  }, [targetPosition])

  // Fetch city data when a country is selected
  const shouldFetchCities = !!selectedCountry && enableCityDrilldown

  const { data: citiesData, isLoading: citiesLoading, isError: citiesError } = useQuery({
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
    if (normalized < 0.2) return indigoScale[0]
    if (normalized < 0.4) return indigoScale[1]
    if (normalized < 0.6) return indigoScale[2]
    if (normalized < 0.8) return indigoScale[3]
    if (normalized < 0.9) return indigoScale[4]
    return indigoScale[5]
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
          <div className="text-xs text-muted-foreground mt-2 pt-2 border-t border-border">
            Click to view cities
          </div>
        )}
      </div>
    )
  }

  const handleZoomIn = () => {
    if (position.zoom >= 4) return
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

  const handleCountryClick = (countryCode: string, countryName: string) => {
    if (!enableCityDrilldown) return

    const center = getCountryCenter(countryCode)
    const zoom = getCountryZoomLevel(countryCode)

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

  // Fake city data for demonstration when API fails
  const fakeCityData: Record<string, any[]> = {
    US: [
      { city_id: 1, city_name: 'New York', city_region_name: 'New York', country_code: 'US', country_name: 'United States', visitors: 5000, views: 12000 },
      { city_id: 2, city_name: 'Los Angeles', city_region_name: 'California', country_code: 'US', country_name: 'United States', visitors: 4500, views: 11000 },
      { city_id: 3, city_name: 'Chicago', city_region_name: 'Illinois', country_code: 'US', country_name: 'United States', visitors: 3500, views: 9000 },
      { city_id: 4, city_name: 'Houston', city_region_name: 'Texas', country_code: 'US', country_name: 'United States', visitors: 3000, views: 7500 },
      { city_id: 5, city_name: 'San Francisco', city_region_name: 'California', country_code: 'US', country_name: 'United States', visitors: 2800, views: 7000 },
    ],
    GB: [
      { city_id: 6, city_name: 'London', city_region_name: 'England', country_code: 'GB', country_name: 'United Kingdom', visitors: 8000, views: 18000 },
      { city_id: 7, city_name: 'Manchester', city_region_name: 'England', country_code: 'GB', country_name: 'United Kingdom', visitors: 2000, views: 5000 },
      { city_id: 8, city_name: 'Birmingham', city_region_name: 'England', country_code: 'GB', country_name: 'United Kingdom', visitors: 1500, views: 3500 },
    ],
    DE: [
      { city_id: 9, city_name: 'Berlin', city_region_name: 'Berlin', country_code: 'DE', country_name: 'Germany', visitors: 4000, views: 9000 },
      { city_id: 10, city_name: 'Munich', city_region_name: 'Bavaria', country_code: 'DE', country_name: 'Germany', visitors: 3500, views: 8000 },
      { city_id: 11, city_name: 'Hamburg', city_region_name: 'Hamburg', country_code: 'DE', country_name: 'Germany', visitors: 2500, views: 6000 },
    ],
    FR: [
      { city_id: 12, city_name: 'Paris', city_region_name: 'Île-de-France', country_code: 'FR', country_name: 'France', visitors: 7000, views: 15000 },
      { city_id: 13, city_name: 'Lyon', city_region_name: 'Auvergne-Rhône-Alpes', country_code: 'FR', country_name: 'France', visitors: 2000, views: 4500 },
      { city_id: 14, city_name: 'Marseille', city_region_name: 'Provence-Alpes-Côte d\'Azur', country_code: 'FR', country_name: 'France', visitors: 1800, views: 4000 },
    ],
    CA: [
      { city_id: 15, city_name: 'Toronto', city_region_name: 'Ontario', country_code: 'CA', country_name: 'Canada', visitors: 4500, views: 10000 },
      { city_id: 16, city_name: 'Vancouver', city_region_name: 'British Columbia', country_code: 'CA', country_name: 'Canada', visitors: 3000, views: 7000 },
      { city_id: 17, city_name: 'Montreal', city_region_name: 'Quebec', country_code: 'CA', country_name: 'Canada', visitors: 2500, views: 6000 },
    ],
    AU: [
      { city_id: 18, city_name: 'Sydney', city_region_name: 'New South Wales', country_code: 'AU', country_name: 'Australia', visitors: 5000, views: 11000 },
      { city_id: 19, city_name: 'Melbourne', city_region_name: 'Victoria', country_code: 'AU', country_name: 'Australia', visitors: 3500, views: 8000 },
      { city_id: 20, city_name: 'Brisbane', city_region_name: 'Queensland', country_code: 'AU', country_name: 'Australia', visitors: 1500, views: 3500 },
    ],
    JP: [
      { city_id: 21, city_name: 'Tokyo', city_region_name: 'Tokyo', country_code: 'JP', country_name: 'Japan', visitors: 4000, views: 9500 },
      { city_id: 22, city_name: 'Osaka', city_region_name: 'Osaka', country_code: 'JP', country_name: 'Japan', visitors: 2500, views: 6000 },
      { city_id: 23, city_name: 'Kyoto', city_region_name: 'Kyoto', country_code: 'JP', country_name: 'Japan', visitors: 1500, views: 3500 },
    ],
    IN: [
      { city_id: 24, city_name: 'Mumbai', city_region_name: 'Maharashtra', country_code: 'IN', country_name: 'India', visitors: 3000, views: 7000 },
      { city_id: 25, city_name: 'Delhi', city_region_name: 'Delhi', country_code: 'IN', country_name: 'India', visitors: 2800, views: 6500 },
      { city_id: 26, city_name: 'Bangalore', city_region_name: 'Karnataka', country_code: 'IN', country_name: 'India', visitors: 2000, views: 4500 },
    ],
    BR: [
      { city_id: 27, city_name: 'São Paulo', city_region_name: 'São Paulo', country_code: 'BR', country_name: 'Brazil', visitors: 3500, views: 8000 },
      { city_id: 28, city_name: 'Rio de Janeiro', city_region_name: 'Rio de Janeiro', country_code: 'BR', country_name: 'Brazil', visitors: 2500, views: 6000 },
      { city_id: 29, city_name: 'Brasília', city_region_name: 'Federal District', country_code: 'BR', country_name: 'Brazil', visitors: 1500, views: 3500 },
    ],
    IT: [
      { city_id: 30, city_name: 'Rome', city_region_name: 'Lazio', country_code: 'IT', country_name: 'Italy', visitors: 3000, views: 7500 },
      { city_id: 31, city_name: 'Milan', city_region_name: 'Lombardy', country_code: 'IT', country_name: 'Italy', visitors: 2500, views: 6000 },
      { city_id: 32, city_name: 'Florence', city_region_name: 'Tuscany', country_code: 'IT', country_name: 'Italy', visitors: 1500, views: 3500 },
    ],
    ES: [
      { city_id: 33, city_name: 'Madrid', city_region_name: 'Community of Madrid', country_code: 'ES', country_name: 'Spain', visitors: 2800, views: 6500 },
      { city_id: 34, city_name: 'Barcelona', city_region_name: 'Catalonia', country_code: 'ES', country_name: 'Spain', visitors: 2500, views: 6000 },
      { city_id: 35, city_name: 'Valencia', city_region_name: 'Valencian Community', country_code: 'ES', country_name: 'Spain', visitors: 1200, views: 2800 },
    ],
    MX: [
      { city_id: 36, city_name: 'Mexico City', city_region_name: 'Mexico City', country_code: 'MX', country_name: 'Mexico', visitors: 3000, views: 7000 },
      { city_id: 37, city_name: 'Guadalajara', city_region_name: 'Jalisco', country_code: 'MX', country_name: 'Mexico', visitors: 1800, views: 4200 },
      { city_id: 38, city_name: 'Monterrey', city_region_name: 'Nuevo León', country_code: 'MX', country_name: 'Mexico', visitors: 1200, views: 2800 },
    ],
    NL: [
      { city_id: 39, city_name: 'Amsterdam', city_region_name: 'North Holland', country_code: 'NL', country_name: 'Netherlands', visitors: 3000, views: 7000 },
      { city_id: 40, city_name: 'Rotterdam', city_region_name: 'South Holland', country_code: 'NL', country_name: 'Netherlands', visitors: 1500, views: 3500 },
      { city_id: 41, city_name: 'The Hague', city_region_name: 'South Holland', country_code: 'NL', country_name: 'Netherlands', visitors: 1000, views: 2300 },
    ],
    SE: [
      { city_id: 42, city_name: 'Stockholm', city_region_name: 'Stockholm County', country_code: 'SE', country_name: 'Sweden', visitors: 2500, views: 5800 },
      { city_id: 43, city_name: 'Gothenburg', city_region_name: 'Västra Götaland', country_code: 'SE', country_name: 'Sweden', visitors: 1500, views: 3500 },
      { city_id: 44, city_name: 'Malmö', city_region_name: 'Skåne', country_code: 'SE', country_name: 'Sweden', visitors: 1000, views: 2300 },
    ],
    CH: [
      { city_id: 45, city_name: 'Zurich', city_region_name: 'Zurich', country_code: 'CH', country_name: 'Switzerland', visitors: 2000, views: 4800 },
      { city_id: 46, city_name: 'Geneva', city_region_name: 'Geneva', country_code: 'CH', country_name: 'Switzerland', visitors: 1500, views: 3500 },
      { city_id: 47, city_name: 'Basel', city_region_name: 'Basel-Stadt', country_code: 'CH', country_name: 'Switzerland', visitors: 1000, views: 2300 },
    ],
    IR: [
      { city_id: 48, city_name: 'Tehran', city_region_name: 'Tehran', country_code: 'IR', country_name: 'Iran', visitors: 4500, views: 10500 },
      { city_id: 49, city_name: 'Isfahan', city_region_name: 'Isfahan', country_code: 'IR', country_name: 'Iran', visitors: 2000, views: 4800 },
      { city_id: 50, city_name: 'Shiraz', city_region_name: 'Fars', country_code: 'IR', country_name: 'Iran', visitors: 1500, views: 3500 },
      { city_id: 51, city_name: 'Mashhad', city_region_name: 'Razavi Khorasan', country_code: 'IR', country_name: 'Iran', visitors: 1800, views: 4200 },
    ],
  }

  // Use real data if available, otherwise use fake data for selected country
  const cityItems = realCityItems.length > 0
    ? realCityItems
    : (selectedCountry && fakeCityData[selectedCountry.code.toUpperCase()]
        ? fakeCityData[selectedCountry.code.toUpperCase()]
        : [])

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
    () =>
      cityItems.filter((city) => !getCityCoordinates(city.city_name, city.country_code)),
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
  const getRegionMatch = (provinceName: string): { name: string; visitors: number; views: number; cities: string[] } | null => {
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
      if (provinceName.toLowerCase().includes(key.toLowerCase()) ||
          key.toLowerCase().includes(provinceName.toLowerCase())) {
        return value
      }
    }
    return null
  }

  const makeRegionTooltip = (provinceName: string, region: { name: string; visitors: number; views: number; cities: string[] } | null) => {
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
          <div className="text-xs text-muted-foreground">
            {percentage.toFixed(1)}% of total
          </div>
        </div>
      </div>
    )
  }

  const makeCityTooltip = (city: (typeof citiesWithCoords)[0]) => {
    const totalCountryValue =
      cityItems.reduce((sum, c) => sum + (c[selectedMetric] || 0), 0) || 1
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
                disabled={position.zoom >= 4}
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
              <Button
                variant="outline"
                size="sm"
                className="bg-white shadow-sm gap-2"
                onClick={handleBackToWorld}
              >
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
              translateExtent={[[-Infinity, -Infinity], [Infinity, Infinity]]}
              filterZoomEvent={(evt) => !evt.ctrlKey}
            >
              <Geographies geography={mapUrl}>
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
                      return iso !== 'ATA' && !name.includes('antarctica')
                    })
                    .map((geo) => {
                      const { data: countryData } = getCountryMatch(geo)
                      const visitors = countryData?.visitors ?? null
                      const fill = getColorForValue(visitors)
                      const name = geo.properties.NAME || geo.properties.name || 'Unknown'
                      const key = geo.rsmKey

                      return (
                        <g
                          key={key}
                          onMouseEnter={(e: React.MouseEvent) => {
                            if (!containerRef.current) return
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
                            if (!containerRef.current || !tooltip.visible) return
                            const rect = containerRef.current.getBoundingClientRect()
                            setTooltip((t) => ({
                              ...t,
                              x: e.clientX - rect.left,
                              y: e.clientY - rect.top,
                            }))
                          }}
                          onMouseLeave={() => setTooltip({ visible: false, x: 0, y: 0, content: '' })}
                          onClick={() => {
                            if (countryData && enableCityDrilldown) {
                              handleCountryClick(countryData.code, countryData.name)
                            }
                          }}
                        >
                          <Geography
                            geography={geo}
                            style={{
                              default: {
                                fill,
                                outline: 'none',
                                stroke: '#ffffff',
                                strokeWidth: 0.5,
                                transition: 'fill 200ms ease',
                                pointerEvents: 'all',
                              },
                              hover: {
                                fill: visitors == null ? '#d1d5db' : '#4338ca',
                                outline: 'none',
                                cursor: enableCityDrilldown && countryData ? 'pointer' : 'default',
                                strokeWidth: 1,
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
              {viewMode === 'cities' && selectedCountry && !citiesLoading && (
                <Geographies geography={provinceMapUrl}>
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
                        return isoA2 === selectedCode || adm0A3 === selectedCode ||
                               (selectedCode === 'IR' && (isoA2 === 'IR' || adm0A3 === 'IRN'))
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
                              if (!containerRef.current) return
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
                              if (!containerRef.current || !tooltip.visible) return
                              const rect = containerRef.current.getBoundingClientRect()
                              setTooltip((t) => ({
                                ...t,
                                x: e.clientX - rect.left,
                                y: e.clientY - rect.top,
                              }))
                            }}
                            onMouseLeave={() => {
                              setTooltip({ visible: false, x: 0, y: 0, content: '' })
                            }}
                            style={{
                              default: {
                                fill: hasData ? '#c7d2fe' : '#f3f4f6', // indigo-200 for data, gray-100 for no data
                                stroke: '#d1d5db', // gray-300 - lighter border
                                strokeWidth: 0.15,
                                outline: 'none',
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
          {viewMode === 'cities' &&
            !citiesLoading &&
            !citiesError &&
            citiesWithoutCoords.length > 0 && (
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
                  <div className="w-4 h-3 rounded-sm" style={{ backgroundColor: '#f3f4f6', border: '1px solid #9ca3af' }}></div>
                  <span className="text-xs text-muted-foreground">No data</span>
                </div>
                <div className="flex items-center gap-2">
                  <div className="w-4 h-3 rounded-sm" style={{ backgroundColor: '#c7d2fe', border: '1px solid #9ca3af' }}></div>
                  <span className="text-xs text-muted-foreground">Has {selectedMetric}</span>
                </div>
              </div>
            ) : maxVisitors === 0 || data.countries.length === 0 ? (
              <div className="text-sm text-muted-foreground">No data available for the selected period</div>
            ) : (
              <div className="flex items-center gap-2 w-1/2">
                <span className="text-sm text-muted-foreground">0</span>
                <div className="flex-1 h-2 rounded-full overflow-hidden flex">
                  {indigoScale.map((color, i) => (
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
