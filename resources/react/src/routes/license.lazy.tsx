import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { createLazyFileRoute, useNavigate } from '@tanstack/react-router'
import { __ } from '@wordpress/i18n'
import {
  CircleCheck,
  CircleX,
  Download,
  KeyRound,
  Loader2,
  Puzzle,
  ShieldCheck,
  ShieldX,
  TriangleAlert,
} from 'lucide-react'
import { useEffect, useState } from 'react'

import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { WordPress } from '@/lib/wordpress'

export const Route = createLazyFileRoute('/license')({
  component: LicenseRoute,
})

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

interface InstallFeaturesResult {
  installed: string[]
  failed: Array<{ slug: string; error: string }>
  total: number
}

function installFeatures(): Promise<InstallFeaturesResult> {
  return callLicenseApi<InstallFeaturesResult>('install_features')
}

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
  const moduleData = wp.getData<{ license: { activated: boolean; valid: boolean; details: LicenseDetails | null } }>('modules')
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
    <LicenseStatus data={data!} queryClient={queryClient} />
  ) : (
    <ActivationForm queryClient={queryClient} />
  )
}

function ActivationForm({ queryClient }: { queryClient: ReturnType<typeof useQueryClient> }) {
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

      {/* Activation Card */}
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
    </div>
  )
}

function LicenseStatus({
  data,
  queryClient,
}: {
  data: LicenseStatusResponse
  queryClient: ReturnType<typeof useQueryClient>
}) {
  const details = data.details
  const [showConfirm, setShowConfirm] = useState(false)
  const [installResult, setInstallResult] = useState<InstallFeaturesResult | null>(null)

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

  if (!details) {
    return null
  }

  const isExpired = details.expires_at ? new Date(details.expires_at) < new Date() : false
  const isValid = data.valid

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

      {/* Features Card */}
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
            <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
              {details.features.map((feature) => (
                <div
                  key={feature}
                  className="flex items-center gap-2 rounded-md border px-3 py-2 text-sm"
                >
                  <CircleCheck className="h-4 w-4 shrink-0 text-emerald-600" />
                  <span className="capitalize">{feature.replace(/-/g, ' ')}</span>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
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
