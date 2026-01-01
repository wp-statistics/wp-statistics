export interface CityData {
  city_id: number
  city_name: string
  city_region_code: string | null
  city_region_name: string
  country_code: string
  country_name: string
  visitors?: number
  views?: number
  // Coordinates - will be provided by backend later
  latitude?: number
  longitude?: number
  // Coordinate tuple [longitude, latitude] for map rendering
  coordinates?: [number, number]
}

export interface MetricOption {
  value: 'visitors' | 'views'
  label: string
}

export type MapViewMode = 'countries' | 'cities'

export interface MapPosition {
  coordinates: [number, number]
  zoom: number
}

export interface CityPolygonFeature {
  type: 'Feature'
  geometry: {
    type: 'Polygon' | 'MultiPolygon'
    coordinates: number[][][] | number[][][][]
  }
  properties: CityData
}

export interface CityPolygonCollection {
  type: 'FeatureCollection'
  features: CityPolygonFeature[]
}
