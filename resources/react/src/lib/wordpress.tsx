export class WordPress {
  private static instance: WordPress
  private readonly data: NonNullable<typeof window.wps_react>

  private constructor() {
    if (!window.wps_react) {
      throw new Error('wps_react not available. Make sure the plugin is properly initialized.')
    }
    this.data = window.wps_react
  }

  public static getInstance(): WordPress {
    if (!WordPress.instance) {
      WordPress.instance = new WordPress()
    }
    return WordPress.instance
  }

  public getNonce(): string {
    return this.data.globals.nonce
  }

  public getAjaxUrl(): string {
    return this.data.globals.ajaxUrl
  }

  public getIsPremium(): boolean | null {
    return this.data.globals.isPremium
  }

  public getHeaders(): HeadersInit {
    return {
      'Content-Type': 'application/json',
      'X-WP-Nonce': this.getNonce(),
    }
  }

  public getSidebarConfig() {
    return this.data.layout.sidebar
  }
}
