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
