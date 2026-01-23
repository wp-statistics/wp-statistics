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

  public getPluginUrl(): string {
    return this.data.globals.pluginUrl
  }

  public getSiteUrl(): string {
    return this.data.globals.siteUrl
  }

  public isTrackLoggedInEnabled(): boolean {
    return this.data.globals.trackLoggedInUsers
  }

  public isHashEnabled(): boolean {
    return this.data.globals.hashIps
  }

  public getAnalyticsAction(): string {
    return this.data.globals.analyticsAction || 'wp_statistics_analytics'
  }

  public getFilterAction(): string {
    return this.data.globals.filterAction || 'wp_statistics_get_filter_options'
  }

  public getUserPreferencesAction(): string {
    return this.data.globals.userPreferencesAction || 'wp_statistics_user_preferences'
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

  public getFilters() {
    return this.data.filters
  }

  public getFilterFields() {
    return this.data.filters.fields
  }

  public getFilterOperators() {
    return this.data.filters.operators
  }

  public getFilterFieldsByGroup(group: FilterGroup): FilterFields[keyof FilterFields][] {
    const fields = this.data?.filters?.fields
    if (!fields) return []
    return Object.values(fields).filter((field) => field.groups?.includes(group))
  }

  public getUserPreferences(): UserPreferences | undefined {
    return this.data.globals.userPreferences
  }

  public getGlobalFiltersPreferences(): GlobalFiltersPreferences | null | undefined {
    return this.data.globals.userPreferences?.globalFilters
  }

  public getNetworkData(): NetworkData {
    return this.data.network
  }

  public isMultisite(): boolean {
    return this.data.network?.isMultisite ?? false
  }

  public isNetworkAdmin(): boolean {
    return this.data.network?.isNetworkAdmin ?? false
  }

  public getNetworkSites(): NetworkSite[] {
    return this.data.network?.sites ?? []
  }

  public getNotices(): NoticeData | undefined {
    return this.data.notices
  }

  public getNoticeItems(): NoticeItem[] {
    return this.data.notices?.items ?? []
  }

  public getNoticeDismissUrl(): string {
    return this.data.notices?.dismissUrl ?? this.getAjaxUrl()
  }

  public getNoticeDismissNonce(): string {
    return this.data.notices?.nonce ?? ''
  }

  public getUserCountry(): string | undefined {
    return this.data.globals.userCountry
  }

  public getUserCountryName(): string | undefined {
    return this.data.globals.userCountryName
  }

  public getDateFormat(): string {
    return this.data.globals.dateFormat || 'Y-m-d'
  }

  public getStartOfWeek(): number {
    return this.data.globals.startOfWeek ?? 0
  }

  /**
   * Get list of available taxonomies.
   * Used for taxonomy filter dropdowns.
   */
  public getTaxonomies(): TaxonomyItem[] {
    return this.data.globals.taxonomies ?? [{ value: 'category', label: 'Category' }]
  }

  /**
   * Get arbitrary data from the localized data object.
   * Used by premium plugin to access premium-specific data.
   */
  public getData<T = unknown>(key: string): T | undefined {
    return (this.data as Record<string, unknown>)[key] as T | undefined
  }

  /**
   * Get list of queryable post types.
   * Extracts post type options from the post_type filter field.
   * Used for single content pages to filter by all possible content types.
   */
  public getQueryablePostTypes(): string[] {
    const postTypeField = this.data?.filters?.fields?.post_type
    if (!postTypeField?.options) {
      // Fallback to common post types if filter not available
      return ['post', 'page']
    }
    return postTypeField.options.map((opt) => opt.value as string)
  }
}

export interface TaxonomyItem {
  value: string
  label: string
}
