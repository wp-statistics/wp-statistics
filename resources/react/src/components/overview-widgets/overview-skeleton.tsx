import { BarListSkeleton, ChartSkeleton, MetricsSkeleton, PanelSkeleton } from '@/components/ui/skeletons'

const COL_SPAN: Record<number, string> = {
  4: 'col-span-12 lg:col-span-4',
  6: 'col-span-12 lg:col-span-6',
  8: 'col-span-12 lg:col-span-8',
  12: 'col-span-12',
}

export function OverviewSkeleton({ config }: { config: { metrics: unknown[]; widgets: PhpOverviewWidget[] } }) {
  const metricsCount = config.metrics.length

  return (
    <div className="grid gap-3 grid-cols-12">
      {config.widgets.map((w) => {
        if (w.type === 'metrics') {
          return (
            <div key={w.id} className="col-span-12">
              <PanelSkeleton showTitle={false}>
                <MetricsSkeleton count={metricsCount} columns={metricsCount} />
              </PanelSkeleton>
            </div>
          )
        }
        if (w.type === 'chart') {
          return (
            <div key={w.id} className={COL_SPAN[w.defaultSize] || 'col-span-12'}>
              <PanelSkeleton titleWidth="w-32">
                <ChartSkeleton height={256} showTitle={false} />
              </PanelSkeleton>
            </div>
          )
        }
        if (w.type === 'map') {
          return (
            <div key={w.id} className={COL_SPAN[w.defaultSize] || 'col-span-12'}>
              <PanelSkeleton titleWidth="w-40">
                <ChartSkeleton height={256} showTitle={false} />
              </PanelSkeleton>
            </div>
          )
        }
        if (w.type === 'bar-list') {
          return (
            <div key={w.id} className={COL_SPAN[w.defaultSize] || 'col-span-12 lg:col-span-6'}>
              <PanelSkeleton>
                <BarListSkeleton items={5} showIcon={!!w.iconType} />
              </PanelSkeleton>
            </div>
          )
        }
        if (w.type === 'tabbed-bar-list') {
          return (
            <div key={w.id} className={COL_SPAN[w.defaultSize] || 'col-span-12'}>
              <PanelSkeleton>
                <BarListSkeleton items={5} />
              </PanelSkeleton>
            </div>
          )
        }
        if (w.type === 'traffic-summary') {
          return (
            <div key={w.id} className={COL_SPAN[w.defaultSize] || 'col-span-12 lg:col-span-4'}>
              <PanelSkeleton titleWidth="w-32">
                <MetricsSkeleton count={5} columns={3} />
              </PanelSkeleton>
            </div>
          )
        }
        if (w.type === 'registered') {
          return (
            <div key={w.id} className={COL_SPAN[w.defaultSize] || 'col-span-12'}>
              <PanelSkeleton>
                <BarListSkeleton items={5} />
              </PanelSkeleton>
            </div>
          )
        }
        return null
      })}
    </div>
  )
}
