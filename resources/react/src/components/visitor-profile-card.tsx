/**
 * Visitor Profile Card
 *
 * Displays different content based on visitor type (user, IP, or hash).
 * Used on the Single Visitor detail page.
 */

import { __ } from '@wordpress/i18n'
import { Globe, Hash } from 'lucide-react'

import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
import { Panel } from '@/components/ui/panel'
import { WordPress } from '@/lib/wordpress'

type VisitorType = 'user' | 'ip' | 'hash'

interface VisitorInfo {
  // Common
  first_visit?: string | null
  last_visit?: string | null
  total_sessions?: number
  total_views?: number
  browser_name?: string | null
  browser_version?: string | null
  os_name?: string | null
  device_type_name?: string | null
  country_code?: string | null
  country_name?: string | null
  region_name?: string | null
  city_name?: string | null
  // User type
  user_id?: number
  user_login?: string
  user_email?: string | null
  user_role?: string | null
  // IP type
  ip_address?: string
  // Hash type
  visitor_hash?: string
}

function DeviceInfo({
  browser,
  browserVersion,
  os,
  deviceType,
}: {
  browser: string | null | undefined
  browserVersion: string | null | undefined
  os: string | null | undefined
  deviceType: string | null | undefined
}) {
  const parts = [browser && browserVersion ? `${browser} ${browserVersion}` : browser, os, deviceType].filter(Boolean)
  if (parts.length === 0) return null
  return <span className="text-xs text-neutral-500">{parts.join(' · ')}</span>
}

function LocationInfo({
  countryCode,
  countryName,
  regionName,
  cityName,
}: {
  countryCode: string | null | undefined
  countryName: string | null | undefined
  regionName?: string | null
  cityName?: string | null
}) {
  const pluginUrl = WordPress.getInstance().getPluginUrl()
  if (!countryName) return null
  const parts = [cityName, regionName, countryName].filter(Boolean)
  return (
    <div className="flex items-center gap-1.5">
      {countryCode && (
        <img
          src={`${pluginUrl}public/images/flags/${countryCode}.svg`}
          alt={countryName}
          className="w-4 h-4 object-contain"
        />
      )}
      <span className="text-xs text-neutral-500">{parts.join(', ')}</span>
    </div>
  )
}

function ReturningBadge({ totalSessions }: { totalSessions: number | undefined }) {
  if (!totalSessions || totalSessions <= 1) return null
  return (
    <Badge variant="secondary" className="text-[10px] font-normal px-1.5 py-0">
      {__('Returning', 'wp-statistics')}
    </Badge>
  )
}

export function VisitorProfileCard({ type, visitorInfo }: { type: VisitorType; visitorInfo: VisitorInfo | undefined }) {
  if (!visitorInfo) {
    return (
      <Panel className="p-4">
        <div className="text-sm text-neutral-500">{__('Visitor information not available.', 'wp-statistics')}</div>
      </Panel>
    )
  }

  if (type === 'user') {
    const initials = (visitorInfo.user_login || 'U').substring(0, 2).toUpperCase()
    return (
      <Panel className="p-4">
        <div className="flex items-start gap-4">
          <Avatar className="h-16 w-16">
            <AvatarFallback className="text-lg">{initials}</AvatarFallback>
          </Avatar>
          <div className="flex-1 min-w-0">
            <div className="flex items-center gap-2 mb-1">
              <h2 className="text-lg font-semibold text-neutral-800 truncate">{visitorInfo.user_login}</h2>
              <ReturningBadge totalSessions={visitorInfo.total_sessions} />
            </div>
            <div className="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-neutral-500">
              {visitorInfo.user_email && <span>{visitorInfo.user_email}</span>}
              {visitorInfo.user_role && (
                <Badge variant="outline" className="text-[10px] font-normal capitalize px-1.5 py-0">
                  {visitorInfo.user_role}
                </Badge>
              )}
            </div>
            <div className="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2">
              <LocationInfo countryCode={visitorInfo.country_code} countryName={visitorInfo.country_name} regionName={visitorInfo.region_name} cityName={visitorInfo.city_name} />
              <DeviceInfo browser={visitorInfo.browser_name} browserVersion={visitorInfo.browser_version} os={visitorInfo.os_name} deviceType={visitorInfo.device_type_name} />
            </div>
          </div>
        </div>
      </Panel>
    )
  }

  if (type === 'ip') {
    const pluginUrl = WordPress.getInstance().getPluginUrl()
    return (
      <Panel className="p-4">
        <div className="flex items-start gap-4">
          <div className="h-16 w-16 rounded-full bg-neutral-100 flex items-center justify-center shrink-0">
            {visitorInfo.country_code ? (
              <img
                src={`${pluginUrl}public/images/flags/${visitorInfo.country_code}.svg`}
                alt={visitorInfo.country_name || ''}
                className="w-8 h-8 object-contain"
              />
            ) : (
              <Globe className="h-8 w-8 text-neutral-400" />
            )}
          </div>
          <div className="flex-1 min-w-0">
            <div className="flex items-center gap-2 mb-1">
              <h2 className="text-lg font-semibold text-neutral-800 font-mono">{visitorInfo.ip_address}</h2>
              <ReturningBadge totalSessions={visitorInfo.total_sessions} />
            </div>
            <div className="flex flex-wrap items-center gap-x-4 gap-y-1">
              <LocationInfo countryCode={visitorInfo.country_code} countryName={visitorInfo.country_name} regionName={visitorInfo.region_name} cityName={visitorInfo.city_name} />
            </div>
            <div className="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2">
              <DeviceInfo browser={visitorInfo.browser_name} browserVersion={visitorInfo.browser_version} os={visitorInfo.os_name} deviceType={visitorInfo.device_type_name} />
            </div>
          </div>
        </div>
      </Panel>
    )
  }

  // Hash type
  const shortHash = (visitorInfo.visitor_hash || '').replace(/^#hash#/i, '').substring(0, 8) || '------'
  return (
    <Panel className="p-4">
      <div className="flex items-start gap-4">
        <div className="h-16 w-16 rounded-full bg-neutral-100 flex items-center justify-center shrink-0">
          <Hash className="h-8 w-8 text-neutral-400" />
        </div>
        <div className="flex-1 min-w-0">
          <div className="flex items-center gap-2 mb-1">
            <h2 className="text-lg font-semibold text-neutral-800">
              <span className="text-neutral-400">#</span>
              <span className="font-mono">{shortHash}</span>
            </h2>
            <ReturningBadge totalSessions={visitorInfo.total_sessions} />
          </div>
          <div className="text-xs text-neutral-500 mb-2">{__('Anonymous Visitor', 'wp-statistics')}</div>
          <div className="flex flex-wrap items-center gap-x-4 gap-y-1">
            <LocationInfo countryCode={visitorInfo.country_code} countryName={visitorInfo.country_name} />
            <DeviceInfo browser={visitorInfo.browser_name} browserVersion={visitorInfo.browser_version} os={visitorInfo.os_name} deviceType={visitorInfo.device_type_name} />
          </div>
        </div>
      </div>
    </Panel>
  )
}
