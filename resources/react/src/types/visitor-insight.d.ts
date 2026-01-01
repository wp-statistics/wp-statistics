interface TopCountriesResponse {
  success: true
  data: {
    items: {
      icon: string
      label: string
      value: number
      previous_value: number
    }[]
  }
}

interface DevicesTypeResponse {
  success: true
  data: {
    items: {
      icon: string
      label: string
      value: number
      previous_value: number
    }[]
  }
}

interface OSSResponse {
  success: true
  data: {
    items: {
      icon: string
      label: string
      value: number
      previous_value: number
    }[]
  }
}

interface TopCountriesItem {
  icon: string
  label: string
  value: number
  previous_value: number
}

interface GlobalVisitorDistributionResponse {
  success: boolean
  data: {
    items: {
      name: string
      code: string
      visitors: string
    }[]
  }
}

interface TrafficTrendsResponse {
  success: boolean
  data: {
    items: {
      date: string // ex. '2025-11-03'
      visitors: number
      visitorsPrevious: number
      views: number
      viewsPrevious: number
    }[]
  }
}

interface TrafficTrendsParams {
  range: 'monthly' | 'weekly' | 'daily'
  hasPreviousData?: boolean
}
