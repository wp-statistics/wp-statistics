import { useSuspenseQuery } from '@tanstack/react-query'
import { createLazyFileRoute } from '@tanstack/react-router'

import { getVisitorCountQueryOptions } from '@/services/get-visitor-count'
import { Card, CardHeader, CardTitle } from '@/components/ui/card'
import { HorizontalBarList } from '@/components/custom/horizontal-bar-list'

export const Route = createLazyFileRoute('/visitor-insights')({
  component: RouteComponent,
})

function RouteComponent() {
  const {
    data: { data: result },
  } = useSuspenseQuery(getVisitorCountQueryOptions())
  console.log(result)

  const topCountriesData = {
    title: 'Top Countries',
    items: [
      {
        icon: '🇫🇷',
        label: 'France',
        value: '17K',
        percentage: '8.3',
        isNegative: false,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '17,000 visitors from France',
      },
      {
        icon: '🇬🇧',
        label: 'United Kingdom',
        value: '7K',
        percentage: '45',
        isNegative: false,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '7,000 visitors from United Kingdom',
      },
      {
        icon: '🇳🇱',
        label: 'Netherlands',
        value: '5K',
        percentage: '34',
        isNegative: false,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '5,000 visitors from Netherlands',
      },
      {
        icon: '🇩🇪',
        label: 'Germany',
        value: '2K',
        percentage: '20',
        isNegative: false,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '2,000 visitors from Germany',
      },
      {
        icon: '🇬🇪',
        label: 'Georgia',
        value: '1K',
        percentage: '15',
        isNegative: true,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '1,000 visitors from Georgia',
      },
    ],
    link: {
      title: 'View Countries',
      action: () => console.log('View all countries'),
    },
  }

  const deviceTypeData = {
    title: 'Device Type',
    items: [
      {
        icon: '�',
        label: 'PC',
        value: '17K',
        percentage: '8.3',
        isNegative: false,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '17,000 visitors from PC',
      },
      {
        icon: '📱',
        label: 'Tablet',
        value: '7K',
        percentage: '45',
        isNegative: false,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '7,000 visitors from Tablet',
      },
      {
        icon: '�',
        label: 'Mobile',
        value: '5K',
        percentage: '34',
        isNegative: false,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '5,000 visitors from Mobile',
      },
      {
        icon: '🔧',
        label: 'Other',
        value: '1K',
        percentage: '15',
        isNegative: true,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '1,000 visitors from Other devices',
      },
    ],
    link: {
      title: 'View Device Types',
      action: () => console.log('View all device types'),
    },
  }

  const operatingSystemsData = {
    title: 'Operating Systems',
    items: [
      {
        icon: '🍎',
        label: 'Mac',
        value: '5K',
        percentage: '34',
        isNegative: false,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '5,000 visitors from Mac',
      },
      {
        icon: '🪟',
        label: 'Windows',
        value: '5K',
        percentage: '34',
        isNegative: false,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '5,000 visitors from Windows',
      },
      {
        icon: '🤖',
        label: 'Android',
        value: '5K',
        percentage: '34',
        isNegative: false,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '5,000 visitors from Android',
      },
      {
        icon: '🐧',
        label: 'GNU/Linux',
        value: '5K',
        percentage: '34',
        isNegative: false,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '5,000 visitors from GNU/Linux',
      },
      {
        icon: '🔧',
        label: 'Other',
        value: '1K',
        percentage: '15',
        isNegative: true,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '1,000 visitors from Other OS',
      },
    ],
    link: {
      title: 'View Operating Systems',
      action: () => console.log('View all operating systems'),
    },
  }

  const topEntryPagesData = {
    title: 'Top Entry Pages',
    items: [
      {
        icon: '🇫🇷',
        label: 'France',
        value: '17K',
        percentage: '8.3',
        isNegative: false,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '17,000 visitors from France',
      },
      {
        icon: '🇬🇧',
        label: 'United Kingdom',
        value: '7K',
        percentage: '45',
        isNegative: false,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '7,000 visitors from United Kingdom',
      },
      {
        icon: '🇳🇱',
        label: 'Netherlands',
        value: '5K',
        percentage: '34',
        isNegative: false,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '5,000 visitors from Netherlands',
      },
      {
        icon: '🇩🇪',
        label: 'Germany',
        value: '2K',
        percentage: '20',
        isNegative: false,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '2,000 visitors from Germany',
      },
      {
        icon: '🇬🇪',
        label: 'Georgia',
        value: '1K',
        percentage: '15',
        isNegative: true,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '1,000 visitors from Georgia',
      },
    ],
    link: {
      title: 'View Entry Pages',
      action: () => console.log('View all entry pages'),
    },
  }

  const topReferrersData = {
    title: 'Top Referrers',
    items: [
      {
        icon: '🇫🇷',
        label: 'France',
        value: '17K',
        percentage: '8.3',
        isNegative: false,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '17,000 visitors from France',
      },
      {
        icon: '🇬🇧',
        label: 'United Kingdom',
        value: '7K',
        percentage: '45',
        isNegative: false,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '7,000 visitors from United Kingdom',
      },
      {
        icon: '🇳🇱',
        label: 'Netherlands',
        value: '5K',
        percentage: '34',
        isNegative: false,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '5,000 visitors from Netherlands',
      },
      {
        icon: '🇩🇪',
        label: 'Germany',
        value: '2K',
        percentage: '20',
        isNegative: false,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '2,000 visitors from Germany',
      },
      {
        icon: '🇬🇪',
        label: 'Georgia',
        value: '1K',
        percentage: '15',
        isNegative: true,
        tooltipTitle: 'November 2025',
        tooltipSubtitle: '1,000 visitors from Georgia',
      },
    ],
    link: {
      title: 'View Referees',
      action: () => console.log('View all referrers'),
    },
  }

  return (
    <div className="p-2 grid gap-6">
      <h1 className="text-2xl font-medium text-neutral-700">Visitor insights</h1>
      <div className="grid gap-3 grid-cols-12">
        <div className="col-span-12">Statistics Section</div>

        <Card className="col-span-12">
          <CardHeader>
            <CardTitle>Traffic Trends</CardTitle>
          </CardHeader>
        </Card>

        <div className="col-span-6">
          <HorizontalBarList
            title={topEntryPagesData.title}
            items={topEntryPagesData.items}
            link={topEntryPagesData.link}
          />
        </div>

        <div className="col-span-6">
          <HorizontalBarList
            title={topReferrersData.title}
            items={topReferrersData.items}
            link={topReferrersData.link}
          />
        </div>

        <div className="col-span-4">
          <HorizontalBarList
            title={topCountriesData.title}
            items={topCountriesData.items}
            link={topCountriesData.link}
          />
        </div>

        <div className="col-span-4">
          <HorizontalBarList title={deviceTypeData.title} items={deviceTypeData.items} link={deviceTypeData.link} />
        </div>

        <div className="col-span-4">
          <HorizontalBarList
            title={operatingSystemsData.title}
            items={operatingSystemsData.items}
            link={operatingSystemsData.link}
          />
        </div>

        <Card className="col-span-12">
          <CardHeader>
            <CardTitle>Top Visitors</CardTitle>
          </CardHeader>
        </Card>

        <Card className="col-span-6">
          <CardHeader>
            <CardTitle>Traffic by Hour</CardTitle>
          </CardHeader>
        </Card>

        <Card className="col-span-6">
          <CardHeader>
            <CardTitle>Global Visitor Distribution</CardTitle>
          </CardHeader>
        </Card>
      </div>
    </div>
  )
}
