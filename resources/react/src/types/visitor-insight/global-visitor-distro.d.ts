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
