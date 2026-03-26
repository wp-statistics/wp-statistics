import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { createLazyFileRoute, useNavigate } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import {
  ArrowUpCircle,
  CircleCheck,
  CircleDashed,
  CircleX,
  Download,
  KeyRound,
  Loader2,
  LogIn,
  Puzzle,
  RefreshCw,
  ShieldCheck,
  ShieldX,
  TriangleAlert,
} from 'lucide-react'
import { Suspense, useEffect, useState } from 'react'

import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { WordPress } from '@/lib/wordpress'
import { getSettingsComponent } from '@/registry/settings-registry'

export const Route = createLazyFileRoute('/license')({
  component: LicenseRoute,
})

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

interface LicenseDetails {
  status: string
  license_type: string
  plan_name: string
  expires_at: string
  max_activations: number
  activation_count: number
  customer_name: string
  customer_email: string
  features: string[]
  activated_at: number
  last_validated_at: number
}

interface LicenseStatusResponse {
  activated: boolean
  valid: boolean
  details: LicenseDetails | null
}

interface InstalledFeatureMap {
  [slug: string]: string
}

interface FeatureUpdateInfo {
  slug: string
  name: string
  current_version: string
  latest_version: string
  changelog?: string
}

interface CombinedUpdateCheckResult {
  feature_updates: {
    updates_available: boolean
    updates: FeatureUpdateInfo[]
  }
  base_update: {
    success: boolean
    update_available: boolean
    version?: string
    changelog?: string
    error?: string
  }
  checked_at: number
}

interface InstallFeaturesResult {
  installed: string[]
  failed: Array<{ slug: string; error: string }>
  total: number
}

interface UpdateFeatureResult {
  installed: boolean
  slug: string
  version: string | null
  message?: string
}

interface LicenseModuleData {
  license: {
    activated: boolean
    valid: boolean
    details: LicenseDetails | null
    plugin_version?: string
    installed_features?: InstalledFeatureMap
    cached_updates?: CombinedUpdateCheckResult | null
  }
}

// ---------------------------------------------------------------------------
// API helpers
// ---------------------------------------------------------------------------

const wp = WordPress.getInstance()

async function callLicenseApi<T>(subAction: string, params: Record<string, string> = {}): Promise<T> {
  const formData = new FormData()
  formData.append('action', 'wp_statistics_license')
  formData.append('sub_action', subAction)
  formData.append('wps_nonce', wp.getNonce())

  for (const [key, value] of Object.entries(params)) {
    if (value !== undefined && value !== '') {
      formData.append(key, value)
    }
  }

  const response = await fetch(wp.getAjaxUrl(), {
    method: 'POST',
    body: formData,
    credentials: 'same-origin',
  })

  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`)
  }

  const json = await response.json()

  if (!json.success) {
    throw new Error(json.data?.message || 'Request failed')
  }

  return json.data as T
}

function activateLicense(licenseKey: string): Promise<void> {
  return callLicenseApi('activate', { license_key: licenseKey })
}

function deactivateLicense(): Promise<void> {
  return callLicenseApi('deactivate')
}

function getLicenseStatus(): Promise<LicenseStatusResponse> {
  return callLicenseApi<LicenseStatusResponse>('get_status')
}

function installFeatures(): Promise<InstallFeaturesResult> {
  return callLicenseApi<InstallFeaturesResult>('install_features')
}

function checkForUpdates(): Promise<CombinedUpdateCheckResult> {
  return callLicenseApi<CombinedUpdateCheckResult>('check_updates')
}

function updateSingleFeature(slug: string): Promise<UpdateFeatureResult> {
  return callLicenseApi<UpdateFeatureResult>('update_feature', { feature_slug: slug })
}

// ---------------------------------------------------------------------------
// Route component
// ---------------------------------------------------------------------------

function LicenseRoute() {
  const navigate = useNavigate()
  const premium = wp.getData<{ active?: boolean }>('premium')

  useEffect(() => {
    if (!premium?.active) {
      void navigate({ to: '/premium', replace: true })
    }
  }, [premium, navigate])

  if (!premium?.active) {
    return null
  }

  return <LicensePage />
}

function LicensePage() {
  const moduleData = wp.getData<LicenseModuleData>('modules')
  const initialData = moduleData?.license
  const queryClient = useQueryClient()

  const { data, isLoading } = useQuery<LicenseStatusResponse>({
    queryKey: ['license', 'status'],
    queryFn: getLicenseStatus,
    initialData: initialData ?? undefined,
  })

  const isActivated = data?.activated ?? initialData?.activated ?? false

  if (isLoading && !initialData) {
    return (
      <div className="flex items-center justify-center py-24">
        <Loader2 className="h-6 w-6 animate-spin text-muted-foreground" />
      </div>
    )
  }

  return isActivated ? (
    <LicenseStatus
      data={data!}
      queryClient={queryClient}
      pluginVersion={initialData?.plugin_version ?? ''}
      initialInstalledFeatures={initialData?.installed_features ?? {}}
      initialUpdateInfo={initialData?.cached_updates ?? null}
    />
  ) : (
    <ActivationForm queryClient={queryClient} />
  )
}

// ---------------------------------------------------------------------------
// Activation form (with optional login tab from premium)
// ---------------------------------------------------------------------------

function ActivationForm({ queryClient }: { queryClient: ReturnType<typeof useQueryClient> }) {
  const LoginForm = getSettingsComponent('AccountLoginForm')
  const [mode, setMode] = useState<'login' | 'key'>(LoginForm ? 'login' : 'key')

  return (
    <div className="mx-auto max-w-2xl px-6 py-16">
      {/* Hero */}
      <div className="mb-8 text-center">
        <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-primary/10">
          <KeyRound className="h-7 w-7 text-primary" />
        </div>
        <h1 className="text-2xl font-semibold tracking-tight">
          {__('Activate Your License', 'wp-statistics-premium')}
        </h1>
        <p className="mt-2 text-sm text-muted-foreground">
          {__('Enter your license key to unlock premium analytics features.', 'wp-statistics-premium')}
        </p>
      </div>

      {/* Mode switcher (only shown if premium provides LoginForm) */}
      {LoginForm && (
        <div className="mb-6 flex justify-center">
          <div className="inline-flex rounded-lg border p-1">
            <button
              type="button"
              className={`flex items-center gap-1.5 rounded-md px-4 py-2 text-sm font-medium transition-colors ${
                mode === 'login'
                  ? 'bg-primary text-primary-foreground shadow-sm'
                  : 'text-muted-foreground hover:text-foreground'
              }`}
              onClick={() => setMode('login')}
            >
              <LogIn className="h-4 w-4" />
              {__('Login with Account', 'wp-statistics-premium')}
            </button>
            <button
              type="button"
              className={`flex items-center gap-1.5 rounded-md px-4 py-2 text-sm font-medium transition-colors ${
                mode === 'key'
                  ? 'bg-primary text-primary-foreground shadow-sm'
                  : 'text-muted-foreground hover:text-foreground'
              }`}
              onClick={() => setMode('key')}
            >
              <KeyRound className="h-4 w-4" />
              {__('Enter License Key', 'wp-statistics-premium')}
            </button>
          </div>
        </div>
      )}

      {/* Render active mode */}
      {mode === 'login' && LoginForm ? (
        <Suspense
          fallback={
            <div className="flex items-center justify-center py-12">
              <Loader2 className="h-6 w-6 animate-spin text-muted-foreground" />
            </div>
          }
        >
          <LoginForm />
        </Suspense>
      ) : (
        <ManualKeyForm queryClient={queryClient} />
      )}
    </div>
  )
}

/**
 * Manual license key entry form (original activation flow, extracted as a component).
 */
function ManualKeyForm({ queryClient }: { queryClient: ReturnType<typeof useQueryClient> }) {
  const [licenseKey, setLicenseKey] = useState('')
  const [error, setError] = useState<string | null>(null)

  const mutation = useMutation({
    mutationFn: activateLicense,
    onSuccess: () => {
      setError(null)
      queryClient.invalidateQueries({ queryKey: ['license'] })
    },
    onError: (err: Error) => setError(err.message),
  })

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    const trimmed = licenseKey.trim()
    if (!trimmed) {
      setError(__('Please enter a license key.', 'wp-statistics-premium'))
      return
    }
    mutation.mutate(trimmed)
  }

  return (
    <Card>
      <form onSubmit={handleSubmit}>
        <CardContent className="pb-4">
          {error && (
            <div className="mb-4 flex items-center gap-2 rounded-md bg-red-50 p-3 text-sm text-red-800">
              <CircleX className="h-4 w-4 shrink-0" />
              {error}
            </div>
          )}

          <div className="space-y-2">
            <Label htmlFor="license-key">{__('License Key', 'wp-statistics-premium')}</Label>
            <Input
              id="license-key"
              type="text"
              value={licenseKey}
              onChange={(e) => setLicenseKey(e.target.value)}
              placeholder="XXXX-XXXX-XXXX-XXXX"
              className="font-mono"
              disabled={mutation.isPending}
              autoComplete="off"
            />
            <p className="text-xs text-muted-foreground">
              {__('Find your license key in your purchase confirmation email or account dashboard.', 'wp-statistics-premium')}
            </p>
          </div>
        </CardContent>

        <CardFooter className="flex items-center justify-between border-t px-6 py-4">
          <a
            href="https://wp-statistics.com/pricing/"
            target="_blank"
            rel="noopener noreferrer"
            className="text-sm text-primary hover:underline"
          >
            {__('Purchase a license', 'wp-statistics-premium')}
          </a>
          <Button type="submit" disabled={mutation.isPending || !licenseKey.trim()}>
            {mutation.isPending && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
            {__('Activate License', 'wp-statistics-premium')}
          </Button>
        </CardFooter>
      </form>
    </Card>
  )
}

// ---------------------------------------------------------------------------
// License status (main dashboard)
// ---------------------------------------------------------------------------

function LicenseStatus({
  data,
  queryClient,
  pluginVersion,
  initialInstalledFeatures,
  initialUpdateInfo,
}: {
  data: LicenseStatusResponse
  queryClient: ReturnType<typeof useQueryClient>
  pluginVersion: string
  initialInstalledFeatures: InstalledFeatureMap
  initialUpdateInfo: CombinedUpdateCheckResult | null
}) {
  const details = data.details
  const [showConfirm, setShowConfirm] = useState(false)
  const [installResult, setInstallResult] = useState<InstallFeaturesResult | null>(null)
  const [installedFeatures, setInstalledFeatures] = useState<InstalledFeatureMap>(initialInstalledFeatures)
  const [updateInfo, setUpdateInfo] = useState<CombinedUpdateCheckResult | null>(initialUpdateInfo)
  const [updatingSlug, setUpdatingSlug] = useState<string | null>(null)

  const deactivateMutation = useMutation({
    mutationFn: deactivateLicense,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ['license'] }),
  })

  const installMutation = useMutation({
    mutationFn: installFeatures,
    onSuccess: (result) => {
      setInstallResult(result)
    },
  })

  const updateCheckMutation = useMutation({
    mutationFn: checkForUpdates,
    onSuccess: (result) => {
      setUpdateInfo(result)
    },
  })

  const updateFeatureMutation = useMutation({
    mutationFn: updateSingleFeature,
    onSuccess: (result) => {
      setUpdatingSlug(null)

      if (result.installed && result.version) {
        // Update local installed features map
        setInstalledFeatures((prev) => ({
          ...prev,
          [result.slug]: result.version!,
        }))

        // Remove updated feature from the update list
        setUpdateInfo((prev) => {
          if (!prev) return prev
          const remaining = prev.feature_updates.updates.filter((u) => u.slug !== result.slug)
          return {
            ...prev,
            feature_updates: {
              ...prev.feature_updates,
              updates: remaining,
              updates_available: remaining.length > 0,
            },
          }
        })
      }
    },
    onError: () => {
      setUpdatingSlug(null)
    },
  })

  if (!details) {
    return null
  }

  const isExpired = details.expires_at ? new Date(details.expires_at) < new Date() : false
  const isValid = data.valid
  const featureUpdates = updateInfo?.feature_updates?.updates ?? []

  const handleUpdateFeature = (slug: string) => {
    setUpdatingSlug(slug)
    updateFeatureMutation.mutate(slug)
  }

  const AccountStatusCard = getSettingsComponent('AccountStatusCard')

  return (
    <div className="mx-auto max-w-3xl space-y-6 px-6 py-12">
      {/* Page Heading */}
      <div>
        <h1 className="text-2xl font-semibold tracking-tight">
          {__('License Management', 'wp-statistics-premium')}
        </h1>
        <p className="mt-1 text-sm text-muted-foreground">
          {__('Manage your WP Statistics Premium license and view entitled features.', 'wp-statistics-premium')}
        </p>
      </div>

      {/* Account status (rendered by premium if logged in) */}
      {AccountStatusCard && (
        <Suspense fallback={null}>
          <AccountStatusCard />
        </Suspense>
      )}

      {/* License Info Card */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            {isValid ? (
              <ShieldCheck className="h-5 w-5 text-emerald-600" />
            ) : (
              <ShieldX className="h-5 w-5 text-red-600" />
            )}
            {__('License Information', 'wp-statistics-premium')}
          </CardTitle>
        </CardHeader>

        <CardContent>
          <dl className="grid gap-4 sm:grid-cols-2">
            <InfoItem label={__('Plan', 'wp-statistics-premium')}>
              <span className="flex items-center gap-2">
                {details.plan_name || '\u2014'}
                {details.license_type && (
                  <Badge variant="secondary" className="capitalize">
                    {details.license_type}
                  </Badge>
                )}
              </span>
            </InfoItem>

            <InfoItem label={__('Status', 'wp-statistics-premium')}>
              <StatusBadge isExpired={isExpired} isValid={isValid} />
            </InfoItem>

            {details.expires_at && (
              <InfoItem label={__('Expires', 'wp-statistics-premium')}>
                {new Date(details.expires_at).toLocaleDateString(undefined, {
                  year: 'numeric',
                  month: 'long',
                  day: 'numeric',
                })}
              </InfoItem>
            )}

            {details.max_activations > 0 && (
              <InfoItem label={__('Activations', 'wp-statistics-premium')}>
                {`${details.activation_count} ${__('of', 'wp-statistics-premium')} ${details.max_activations} ${__('sites', 'wp-statistics-premium')}`}
              </InfoItem>
            )}

            {details.customer_name && (
              <InfoItem label={__('Licensed To', 'wp-statistics-premium')}>
                {details.customer_name}
              </InfoItem>
            )}

            {details.customer_email && (
              <InfoItem label={__('Email', 'wp-statistics-premium')}>
                {details.customer_email}
              </InfoItem>
            )}
          </dl>
        </CardContent>
      </Card>

      {/* Features Card with FeatureGrid */}
      {details.features.length > 0 && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Puzzle className="h-5 w-5" />
              {__('Licensed Features', 'wp-statistics-premium')}
            </CardTitle>
            <CardDescription>
              {`${details.features.length} ${details.features.length === 1 ? __('feature', 'wp-statistics-premium') : __('features', 'wp-statistics-premium')} ${__('included in your plan.', 'wp-statistics-premium')}`}
            </CardDescription>
          </CardHeader>

          <CardContent>
            <FeatureGrid
              features={details.features}
              installedFeatures={installedFeatures}
              featureUpdates={featureUpdates}
              updatingSlug={updatingSlug}
              onUpdateFeature={handleUpdateFeature}
            />
          </CardContent>
        </Card>
      )}

      {/* Updates Section */}
      {data.valid && (
        <UpdatesSection
          pluginVersion={pluginVersion}
          updateInfo={updateInfo}
          isChecking={updateCheckMutation.isPending}
          checkError={updateCheckMutation.isError ? (updateCheckMutation.error as Error).message : null}
          onCheckUpdates={() => updateCheckMutation.mutate()}
          featureUpdates={featureUpdates}
          onUpdateAll={() => installMutation.mutate()}
          isUpdatingAll={installMutation.isPending}
        />
      )}

      {/* Download Features */}
      {details.features.length > 0 && data.valid && (
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Download className="h-5 w-5" />
              {__('Download Features', 'wp-statistics-premium')}
            </CardTitle>
            <CardDescription>
              {__('Download and install all features included in your license. Already installed features will be skipped.', 'wp-statistics-premium')}
            </CardDescription>
          </CardHeader>

          <CardContent className="space-y-4">
            {installResult && (
              <div className="space-y-2">
                {installResult.installed.length > 0 && (
                  <div className="flex items-start gap-2 rounded-md bg-emerald-50 p-3 text-sm text-emerald-800">
                    <CircleCheck className="mt-0.5 h-4 w-4 shrink-0" />
                    <span>
                      {`${__('Successfully installed:', 'wp-statistics-premium')} ${installResult.installed.join(', ')}`}
                    </span>
                  </div>
                )}
                {installResult.failed.length > 0 && (
                  <div className="flex items-start gap-2 rounded-md bg-red-50 p-3 text-sm text-red-800">
                    <CircleX className="mt-0.5 h-4 w-4 shrink-0" />
                    <div>
                      <p>{__('Some features failed to install:', 'wp-statistics-premium')}</p>
                      <ul className="mt-1 list-inside list-disc">
                        {installResult.failed.map((f) => (
                          <li key={f.slug}>
                            {f.slug}: {f.error}
                          </li>
                        ))}
                      </ul>
                    </div>
                  </div>
                )}
                {installResult.installed.length > 0 && installResult.failed.length === 0 && (
                  <p className="text-sm text-muted-foreground">
                    {__('Refresh the page to activate the installed features.', 'wp-statistics-premium')}
                  </p>
                )}
              </div>
            )}

            {installMutation.isError && (
              <div className="flex items-center gap-2 rounded-md bg-red-50 p-3 text-sm text-red-800">
                <CircleX className="h-4 w-4 shrink-0" />
                {(installMutation.error as Error).message}
              </div>
            )}
          </CardContent>

          <CardFooter className="flex items-center gap-3 border-t px-6 py-4">
            {installResult && installResult.installed.length > 0 && installResult.failed.length === 0 ? (
              <Button onClick={() => window.location.reload()}>
                {__('Refresh Page', 'wp-statistics-premium')}
              </Button>
            ) : (
              <Button onClick={() => installMutation.mutate()} disabled={installMutation.isPending}>
                {installMutation.isPending && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                {installMutation.isPending
                  ? __('Installing Features...', 'wp-statistics-premium')
                  : __('Download & Install Features', 'wp-statistics-premium')}
              </Button>
            )}
          </CardFooter>
        </Card>
      )}

      {/* Danger Zone */}
      <Card className="border-red-200">
        <CardHeader>
          <CardTitle className="flex items-center gap-2 text-red-700">
            <TriangleAlert className="h-5 w-5" />
            {__('Danger Zone', 'wp-statistics-premium')}
          </CardTitle>
          <CardDescription>
            {__('Deactivating your license will disable premium features on this site and free up an activation slot.', 'wp-statistics-premium')}
          </CardDescription>
        </CardHeader>

        <CardFooter className="flex flex-col items-start gap-3">
          {!showConfirm ? (
            <Button variant="destructive" onClick={() => setShowConfirm(true)}>
              {__('Deactivate License', 'wp-statistics-premium')}
            </Button>
          ) : (
            <div className="flex items-center gap-3">
              <span className="text-sm text-muted-foreground">
                {__('Are you sure?', 'wp-statistics-premium')}
              </span>
              <Button
                variant="destructive"
                onClick={() => deactivateMutation.mutate()}
                disabled={deactivateMutation.isPending}
              >
                {deactivateMutation.isPending && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                {__('Yes, Deactivate', 'wp-statistics-premium')}
              </Button>
              <Button variant="outline" onClick={() => setShowConfirm(false)}>
                {__('Cancel', 'wp-statistics-premium')}
              </Button>
            </div>
          )}

          {deactivateMutation.isError && (
            <div className="flex items-center gap-2 rounded-md bg-red-50 p-3 text-sm text-red-800">
              <CircleX className="h-4 w-4 shrink-0" />
              {(deactivateMutation.error as Error).message}
            </div>
          )}
        </CardFooter>
      </Card>
    </div>
  )
}

// ---------------------------------------------------------------------------
// Feature grid — shows each feature with install/update status
// ---------------------------------------------------------------------------

function FeatureGrid({
  features,
  installedFeatures,
  featureUpdates,
  updatingSlug,
  onUpdateFeature,
}: {
  features: string[]
  installedFeatures: InstalledFeatureMap
  featureUpdates: FeatureUpdateInfo[]
  updatingSlug: string | null
  onUpdateFeature: (slug: string) => void
}) {
  return (
    <div className="grid gap-2 sm:grid-cols-2">
      {features.map((slug) => {
        const installedVersion = installedFeatures[slug]
        const update = featureUpdates.find((u) => u.slug === slug)
        const isUpdating = updatingSlug === slug

        return (
          <FeatureItem
            key={slug}
            slug={slug}
            installedVersion={installedVersion}
            update={update}
            isUpdating={isUpdating}
            onUpdate={() => onUpdateFeature(slug)}
          />
        )
      })}
    </div>
  )
}

function FeatureItem({
  slug,
  installedVersion,
  update,
  isUpdating,
  onUpdate,
}: {
  slug: string
  installedVersion?: string
  update?: FeatureUpdateInfo
  isUpdating: boolean
  onUpdate: () => void
}) {
  const displayName = slug.replace(/-/g, ' ')

  // State: updating
  if (isUpdating) {
    return (
      <div className="flex items-center justify-between gap-2 rounded-md border border-amber-200 bg-amber-50/50 px-3 py-2 text-sm">
        <span className="flex items-center gap-2">
          <Loader2 className="h-4 w-4 shrink-0 animate-spin text-amber-600" />
          <span className="capitalize">{displayName}</span>
        </span>
        <Badge variant="secondary" className="text-xs">
          {__('Updating...', 'wp-statistics-premium')}
        </Badge>
      </div>
    )
  }

  // State: installed with update available
  if (installedVersion && update) {
    return (
      <div className="flex items-center justify-between gap-2 rounded-md border border-amber-200 bg-amber-50/50 px-3 py-2 text-sm">
        <span className="flex items-center gap-2">
          <ArrowUpCircle className="h-4 w-4 shrink-0 text-amber-600" />
          <span className="capitalize">{displayName}</span>
          <Badge variant="outline" className="text-xs font-mono">
            v{installedVersion}
          </Badge>
        </span>
        <Button variant="outline" size="sm" className="h-6 px-2 text-xs" onClick={onUpdate}>
          {`${__('Update', 'wp-statistics-premium')} → v${update.latest_version}`}
        </Button>
      </div>
    )
  }

  // State: installed, up to date
  if (installedVersion) {
    return (
      <div className="flex items-center justify-between gap-2 rounded-md border px-3 py-2 text-sm">
        <span className="flex items-center gap-2">
          <CircleCheck className="h-4 w-4 shrink-0 text-emerald-600" />
          <span className="capitalize">{displayName}</span>
        </span>
        <Badge variant="outline" className="text-xs font-mono">
          v{installedVersion}
        </Badge>
      </div>
    )
  }

  // State: not installed
  return (
    <div className="flex items-center justify-between gap-2 rounded-md border border-dashed px-3 py-2 text-sm text-muted-foreground">
      <span className="flex items-center gap-2">
        <CircleDashed className="h-4 w-4 shrink-0" />
        <span className="capitalize">{displayName}</span>
      </span>
      <Badge variant="secondary" className="text-xs">
        {__('Not Installed', 'wp-statistics-premium')}
      </Badge>
    </div>
  )
}

// ---------------------------------------------------------------------------
// Updates section — base plugin + feature update checking
// ---------------------------------------------------------------------------

function UpdatesSection({
  pluginVersion,
  updateInfo,
  isChecking,
  checkError,
  onCheckUpdates,
  featureUpdates,
  onUpdateAll,
  isUpdatingAll,
}: {
  pluginVersion: string
  updateInfo: CombinedUpdateCheckResult | null
  isChecking: boolean
  checkError: string | null
  onCheckUpdates: () => void
  featureUpdates: FeatureUpdateInfo[]
  onUpdateAll: () => void
  isUpdatingAll: boolean
}) {
  const baseUpdate = updateInfo?.base_update
  const hasFeatureUpdates = featureUpdates.length > 0
  const hasBaseUpdate = baseUpdate?.update_available === true
  const hasAnyUpdate = hasFeatureUpdates || hasBaseUpdate
  const checkedAt = updateInfo?.checked_at

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <RefreshCw className="h-5 w-5" />
          {__('Updates', 'wp-statistics-premium')}
        </CardTitle>
        <CardDescription>
          {pluginVersion && `WP Statistics v${pluginVersion}`}
          {pluginVersion && hasBaseUpdate && baseUpdate?.version && (
            <span className="ml-1 text-amber-700">
              {` — v${baseUpdate.version} ${__('available', 'wp-statistics-premium')}`}
            </span>
          )}
        </CardDescription>
      </CardHeader>

      <CardContent className="space-y-3">
        {checkError && (
          <div className="flex items-center gap-2 rounded-md bg-red-50 p-3 text-sm text-red-800">
            <CircleX className="h-4 w-4 shrink-0" />
            {checkError}
          </div>
        )}

        {updateInfo && !hasAnyUpdate && (
          <div className="flex items-center gap-2 rounded-md bg-emerald-50 p-3 text-sm text-emerald-800">
            <CircleCheck className="h-4 w-4 shrink-0" />
            {__('Everything is up to date!', 'wp-statistics-premium')}
          </div>
        )}

        {hasFeatureUpdates && (
          <div className="flex items-start gap-2 rounded-md bg-amber-50 p-3 text-sm text-amber-800">
            <ArrowUpCircle className="mt-0.5 h-4 w-4 shrink-0" />
            <span>
              {featureUpdates.length === 1
                ? __('1 feature update available.', 'wp-statistics-premium')
                : `${featureUpdates.length} ${__('feature updates available.', 'wp-statistics-premium')}`}
            </span>
          </div>
        )}

        {checkedAt && (
          <p className="text-xs text-muted-foreground">
            {`${__('Last checked:', 'wp-statistics-premium')} ${new Date(checkedAt * 1000).toLocaleString()}`}
          </p>
        )}
      </CardContent>

      <CardFooter className="flex items-center gap-3 border-t px-6 py-4">
        <Button variant="outline" onClick={onCheckUpdates} disabled={isChecking}>
          {isChecking && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
          {isChecking
            ? __('Checking...', 'wp-statistics-premium')
            : __('Check for Updates', 'wp-statistics-premium')}
        </Button>

        {hasFeatureUpdates && featureUpdates.length > 1 && (
          <Button onClick={onUpdateAll} disabled={isUpdatingAll}>
            {isUpdatingAll && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
            {isUpdatingAll
              ? __('Updating All...', 'wp-statistics-premium')
              : __('Update All', 'wp-statistics-premium')}
          </Button>
        )}
      </CardFooter>
    </Card>
  )
}

// ---------------------------------------------------------------------------
// Shared UI primitives
// ---------------------------------------------------------------------------

function StatusBadge({ isExpired, isValid }: { isExpired: boolean; isValid: boolean }) {
  if (isExpired) {
    return <Badge variant="destructive">{__('Expired', 'wp-statistics-premium')}</Badge>
  }

  if (isValid) {
    return (
      <Badge className="bg-emerald-100 text-emerald-800 hover:bg-emerald-100">
        {__('Active', 'wp-statistics-premium')}
      </Badge>
    )
  }

  return <Badge variant="destructive">{__('Invalid', 'wp-statistics-premium')}</Badge>
}

function InfoItem({ label, children }: { label: string; children: React.ReactNode }) {
  return (
    <div>
      <dt className="text-sm font-medium text-muted-foreground">{label}</dt>
      <dd className="mt-1 text-sm">{children}</dd>
    </div>
  )
}
