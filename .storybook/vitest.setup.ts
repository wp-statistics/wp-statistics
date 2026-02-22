import { beforeAll } from 'vitest'
import { setProjectAnnotations } from '@storybook/react'
import * as projectAnnotations from './preview'

// This is an important step to apply the right configuration when testing your stories.
// More info at: https://storybook.js.org/docs/api/portable-stories/portable-stories-vitest#setprojectannotations
const project = setProjectAnnotations([projectAnnotations])

beforeAll(project.beforeAll)

// Note: A11y testing is automatically handled by @storybook/addon-a11y when configured in preview.ts
// The addon-vitest integration respects the a11y parameters set in preview.ts
// Pattern-based rule overrides are configured at the story/component level via story parameters
