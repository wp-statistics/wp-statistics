declare global {
  interface wpsReact {
    layout: {
      sidebar: Record<
        string,
        {
          icon: string
          label: string
          slug: string
          subPages?: Record<
            string,
            {
              label: string
              slug: string
            }
          >
        }
      >
    }
    globals: {
      isPremium: null | boolean
      ajaxUrl: string
      nonce: string
    }
  }
}

export {}
