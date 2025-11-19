import { useMemo, useRef, useState } from 'react'
import { ComposableMap, Geographies, Geography, ZoomableGroup } from 'react-simple-maps'
import { Minus, Plus } from 'lucide-react'

import { Card, CardContent } from '@components/ui/card'
import { Button } from '@components/ui/button'
import { cn } from '@lib/utils'

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
  showTimePeriod?: boolean
  timePeriod?: string
  onTimePeriodChange?: (period: string) => void
  className?: string
}

const mapUrl =
  'https://raw.githubusercontent.com/nvkelso/natural-earth-vector/master/geojson/ne_110m_admin_0_countries.geojson'

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
  data,
  metric = 'Visitors',
  showZoomControls = true,
  showLegend = true,
  showTimePeriod = true,
  timePeriod = 'Last 30 days',
  onTimePeriodChange,
  className,
}: GlobalMapProps) {
  const containerRef = useRef<HTMLDivElement>(null)
  const [position, setPosition] = useState({ coordinates: [0, 0], zoom: 1 })
  const [tooltip, setTooltip] = useState<{
    visible: boolean
    x: number
    y: number
    content: React.ReactNode
  }>({ visible: false, x: 0, y: 0, content: '' })

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

    return (
      <div className="text-sm">
        <div className="flex items-center gap-2 mb-2">
          {countryData.flag && <span className="text-base">{countryData.flag}</span>}
          <div className="font-semibold">{countryData.name}</div>
        </div>
        <div className="text-xs">
          {metric}: <span className="font-semibold">{countryData.visitors.toLocaleString()}</span>
        </div>
      </div>
    )
  }

  const handleZoomIn = () => {
    if (position.zoom >= 4) return
    setPosition((pos) => ({ ...pos, zoom: pos.zoom * 1.5 }))
  }

  const handleZoomOut = () => {
    if (position.zoom <= 1) return
    setPosition((pos) => ({ ...pos, zoom: pos.zoom / 1.5 }))
  }

  const handleMoveEnd = (position: { coordinates: [number, number]; zoom: number }) => {
    setPosition(position)
  }

  return (
    <Card className={cn('h-full flex flex-col', className)}>
      <CardContent className="flex-1 flex flex-col p-6">
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
                                cursor: 'pointer',
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
            </ZoomableGroup>
          </ComposableMap>

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
            {maxVisitors === 0 || data.countries.length === 0 ? (
              <div className="text-sm text-muted-foreground">No data available for the selected period</div>
            ) : (
              <div className="flex items-center gap-2 w-1/2">
                <span className="text-sm text-muted-foreground">0</span>
                <div className="flex-1 h-2 rounded-full overflow-hidden flex">
                  {indigoScale.map((color, i) => (
                    <div key={i} className="flex-1" style={{ backgroundColor: color }} />
                  ))}
                </div>
                <span className="text-sm text-muted-foreground">{maxVisitors > 0 ? `${(maxVisitors / 1000).toFixed(0)}k` : '0'}</span>
              </div>
            )}
          </div>
        )}

        {/* Time Period Selector */}
        {showTimePeriod && (
          <div className="mt-4 flex items-center justify-start">
            <select
              value={timePeriod}
              onChange={(e) => onTimePeriodChange?.(e.target.value)}
              className="text-sm text-muted-foreground bg-transparent border-0 focus:ring-0 cursor-pointer"
            >
              <option value="Last 7 days">Last 7 days</option>
              <option value="Last 30 days">Last 30 days</option>
              <option value="Last 90 days">Last 90 days</option>
              <option value="Last 12 months">Last 12 months</option>
            </select>
          </div>
        )}
      </CardContent>
    </Card>
  )
}
