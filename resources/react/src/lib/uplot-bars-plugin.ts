import uPlot from 'uplot'

const _barsPathBuilder: uPlot.Series.PathBuilder = uPlot.paths.bars!({
  radius: 0.15,
  size: [0.6, 40],
})

/**
 * Returns a uPlot paths function for bar series with rounded top corners.
 */
export function barsPath(): uPlot.Series.PathBuilder {
  return _barsPathBuilder
}
