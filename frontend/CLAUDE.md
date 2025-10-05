<!-- vibe-rules Integration -->

<@tanstack/react-router_api>
Always Apply: false - This rule should only be applied when relevant files are open
Always apply this rule in these files: src/**/\*.ts, src/**/\*.tsx

# ActiveLinkOptions type

The `ActiveLinkOptions` type extends the [`LinkOptions`](../LinkOptionsType.md) type and contains additional options that can be used to describe how a link should be styled when it is active.

```tsx
type ActiveLinkOptions = LinkOptions & {
  activeProps?: React.AnchorHTMLAttributes<HTMLAnchorElement> | (() => React.AnchorHTMLAttributes<HTMLAnchorElement>)
  inactiveProps?: React.AnchorHTMLAttributes<HTMLAnchorElement> | (() => React.AnchorHTMLAttributes<HTMLAnchorElement>)
}
```

## ActiveLinkOptions properties

The `ActiveLinkOptions` object accepts/contains the following properties:

### `activeProps`

- `React.AnchorHTMLAttributes<HTMLAnchorElement>`
- Optional
- The props that will be applied to the anchor element when the link is active

### `inactiveProps`

- Type: `React.AnchorHTMLAttributes<HTMLAnchorElement>`
- Optional
- The props that will be applied to the anchor element when the link is inactive

# AsyncRouteComponent type

The `AsyncRouteComponent` type is used to describe a code-split route component that can be preloaded using a `component.preload()` method.

```tsx
type AsyncRouteComponent<TProps> = SyncRouteComponent<TProps> & {
  preload?: () => Promise<void>
}
```

# FileRoute class

> [!CAUTION]
> This class has been deprecated and will be removed in the next major version of TanStack Router.
> Please use the [`createFileRoute`](../createFileRouteFunction.md) function instead.

The `FileRoute` class is a factory that can be used to create a file-based route instance. This route instance can then be used to automatically generate a route tree with the `tsr generate` and `tsr watch` commands.

## `FileRoute` constructor

The `FileRoute` constructor accepts a single argument: the `path` of the file that the route will be generated for.

### Constructor options

- Type: `string` literal
- Required, but **automatically inserted and updated by the `tsr generate` and `tsr watch` commands**.
- The full path of the file that the route will be generated from.

### Constructor returns

- An instance of the `FileRoute` class that can be used to create a route.

## `FileRoute` methods

The `FileRoute` class implements the following method(s):

### `.createRoute` method

The `createRoute` method is a method that can be used to configure the file route instance. It accepts a single argument: the `options` that will be used to configure the file route instance.

#### .createRoute options

- Type: `Omit<RouteOptions, 'getParentRoute' | 'path' | 'id'>`
- [`RouteOptions`](../RouteOptionsType.md)
- Optional
- The same options that are available to the `Route` class, but with the `getParentRoute`, `path`, and `id` options omitted since they are unnecessary for file-based routing.

#### .createRoute returns

A [`Route`](../RouteType.md) instance that can be used to configure the route to be inserted into the route-tree.

> ⚠️ Note: For `tsr generate` and `tsr watch` to work properly, the file route instance must be exported from the file using the `Route` identifier.

### Examples

```tsx
import { FileRoute } from '@tanstack/react-router'

export const Route = new FileRoute('/').createRoute({
  loader: () => {
    return 'Hello World'
  },
  component: IndexComponent,
})

function IndexComponent() {
  const data = Route.useLoaderData()
  return <div>{data}</div>
}
```

# LinkOptions type

The `LinkOptions` type extends the [`NavigateOptions`](../NavigateOptionsType.md) type and contains additional options that can be used by TanStack Router when handling actual anchor element attributes.

```tsx
type LinkOptions = NavigateOptions & {
  target?: HTMLAnchorElement['target']
  activeOptions?: ActiveOptions
  preload?: false | 'intent'
  preloadDelay?: number
  disabled?: boolean
}
```

## LinkOptions properties

The `LinkOptions` object accepts/contains the following properties:

### `target`

- Type: `HTMLAnchorElement['target']`
- Optional
- The standard anchor tag target attribute

### `activeOptions`

- Type: `ActiveOptions`
- Optional
- The options that will be used to determine if the link is active

### `preload`

- Type: `false | 'intent' | 'viewport' | 'render'`
- Optional
- If set, the link's preloading strategy will be set to this value.
- See the [Preloading guide](../../../guide/preloading.md) for more information.

### `preloadDelay`

- Type: `number`
- Optional
- Delay intent preloading by this many milliseconds. If the intent exits before this delay, the preload will be cancelled.

### `disabled`

- Type: `boolean`
- Optional
- If true, will render the link without the href attribute

# LinkProps type

The `LinkProps` type extends the [`ActiveLinkOptions`](../ActiveLinkOptionsType.md) and `React.AnchorHTMLAttributes<HTMLAnchorElement>` types and contains additional props specific to the `Link` component.

```tsx
type LinkProps = ActiveLinkOptions &
  Omit<React.AnchorHTMLAttributes<HTMLAnchorElement>, 'children'> & {
    children?: React.ReactNode | ((state: { isActive: boolean }) => React.ReactNode)
  }
```

## LinkProps properties

- All of the props from [`ActiveLinkOptions`](../ActiveLinkOptionsType.md)
- All of the props from `React.AnchorHTMLAttributes<HTMLAnchorElement>`

#### `children`

- Type: `React.ReactNode | ((state: { isActive: boolean }) => React.ReactNode)`
- Optional
- The children that will be rendered inside of the anchor element. If a function is provided, it will be called with an object that contains the `isActive` boolean value that can be used to determine if the link is active.

# MatchRouteOptions type

The `MatchRouteOptions` type is used to describe the options that can be used when matching a route.

```tsx
interface MatchRouteOptions {
  pending?: boolean
  caseSensitive?: boolean
  includeSearch?: boolean
  fuzzy?: boolean
}
```

## MatchRouteOptions properties

The `MatchRouteOptions` type has the following properties:

### `pending` property

- Type: `boolean`
- Optional
- If `true`, will match against pending location instead of the current location

### `caseSensitive` property

- Type: `boolean`
- Optional
- If `true`, will match against the current location with case sensitivity

### `includeSearch` property

- Type: `boolean`
- Optional
- If `true`, will match against the current location's search params using a deep inclusive check. e.g. `{ a: 1 }` will match for a current location of `{ a: 1, b: 2 }`

### `fuzzy` property

- Type: `boolean`
- Optional
- If `true`, will match against the current location using a fuzzy match. e.g. `/posts` will match for a current location of `/posts/123`

# NavigateOptions type

The `NavigateOptions` type is used to describe the options that can be used when describing a navigation action in TanStack Router.

```tsx
type NavigateOptions = ToOptions & {
  replace?: boolean
  resetScroll?: boolean
  hashScrollIntoView?: boolean | ScrollIntoViewOptions
  viewTransition?: boolean | ViewTransitionOptions
  ignoreBlocker?: boolean
  reloadDocument?: boolean
  href?: string
}
```

## NavigateOptions properties

The `NavigateOptions` object accepts the following properties:

### `replace`

- Type: `boolean`
- Optional
- Defaults to `false`.
- If `true`, the location will be committed to the browser history using `history.replace` instead of `history.push`.

### `resetScroll`

- Type: `boolean`
- Optional
- Defaults to `true` so that the scroll position will be reset to 0,0 after the location is committed to the browser history.
- If `false`, the scroll position will not be reset to 0,0 after the location is committed to history.

### `hashScrollIntoView`

- Type: `boolean | ScrollIntoViewOptions`
- Optional
- Defaults to `true` so the element with an id matching the hash will be scrolled into view after the location is committed to history.
- If `false`, the element with an id matching the hash will not be scrolled into view after the location is committed to history.
- If an object is provided, it will be passed to the `scrollIntoView` method as options.
- See [MDN](https://developer.mozilla.org/en-US/docs/Web/API/Element/scrollIntoView) for more information on `ScrollIntoViewOptions`.

### `viewTransition`

- Type: `boolean | ViewTransitionOptions`
- Optional
- Defaults to `false`.
- If `true`, navigation will be called using `document.startViewTransition()`.
- If [`ViewTransitionOptions`](../ViewTransitionOptionsType.md), route navigations will be called using `document.startViewTransition({update, types})` where `types` will be the strings array passed with `ViewTransitionOptions["types"]`. If the browser does not support viewTransition types, the navigation will fall back to normal `document.startTransition()`, same as if `true` was passed.
- If the browser does not support this api, this option will be ignored.
- See [MDN](https://developer.mozilla.org/en-US/docs/Web/API/Document/startViewTransition) for more information on how this function works.
- See [Google](https://developer.chrome.com/docs/web-platform/view-transitions/same-document#view-transition-types) for more information on viewTransition types

### `ignoreBlocker`

- Type: `boolean`
- Optional
- Defaults to `false`.
- If `true`, navigation will ignore any blockers that might prevent it.

### `reloadDocument`

- Type: `boolean`
- Optional
- Defaults to `false`.
- If `true`, navigation to a route inside of router will trigger a full page load instead of the traditional SPA navigation.

### `href`

- Type: `string`
- Optional
- This can be used instead of `to` to navigate to a fully built href, e.g. pointing to an external target.

- [`ToOptions`](../ToOptionsType.md)

# NotFoundError

The `NotFoundError` type is used to represent a not-found error in TanStack Router.

```tsx
export type NotFoundError = {
  global?: boolean
  data?: any
  throw?: boolean
  routeId?: string
  headers?: HeadersInit
}
```

## NotFoundError properties

The `NotFoundError` object accepts/contains the following properties:

### `global` property (⚠️ deprecated, use `routeId: rootRouteId` instead)

- Type: `boolean`
- Optional - `default: false`
- If true, the not-found error will be handled by the `notFoundComponent` of the root route instead of bubbling up from the route that threw it. This has the same behavior as importing the root route and calling `RootRoute.notFound()`.

### `data` property

- Type: `any`
- Optional
- Custom data that is passed into to `notFoundComponent` when the not-found error is handled

### `throw` property

- Type: `boolean`
- Optional - `default: false`
- If provided, will throw the not-found object instead of returning it. This can be useful in places where `throwing` in a function might cause it to have a return type of `never`. In that case, you can use `notFound({ throw: true })` to throw the not-found object instead of returning it.

### `route` property

- Type: `string`
- Optional
- The ID of the route that will attempt to handle the not-found error. If the route does not have a `notFoundComponent`, the error will bubble up to the parent route (and be handled by the root route if necessary). By default, TanStack Router will attempt to handle the not-found error with the route that threw it.

### `headers` property

- Type: `HeadersInit`
- Optional
- HTTP headers to be included when the not-found error is handled on the server side.

# NotFoundRoute class

> [!CAUTION]
> This class has been deprecated and will be removed in the next major version of TanStack Router.
> Please use the `notFoundComponent` route option that is present during route configuration.
> See the [Not Found Errors guide](../../../guide/not-found-errors.md) for more information.

The `NotFoundRoute` class extends the `Route` class and can be used to create a not found route instance. A not found route instance can be passed to the `routerOptions.notFoundRoute` option to configure a default not-found/404 route for every branch of the route tree.

## Constructor options

The `NotFoundRoute` constructor accepts an object as its only argument.

- Type:

```tsx
Omit<RouteOptions, 'path' | 'id' | 'getParentRoute' | 'caseSensitive' | 'parseParams' | 'stringifyParams'>
```

- [RouteOptions](../RouteOptionsType.md)
- Required
- The options that will be used to configure the not found route instance.

## Examples

```tsx
import { NotFoundRoute, createRouter } from '@tanstack/react-router'
import { Route as rootRoute } from './routes/__root'
import { routeTree } from './routeTree.gen'

const notFoundRoute = new NotFoundRoute({
  getParentRoute: () => rootRoute,
  component: () => <div>Not found!!!</div>,
})

const router = createRouter({
  routeTree,
  notFoundRoute,
})

// ... other code
```

# ParsedHistoryState type

The `ParsedHistoryState` type represents a parsed state object. Additionally to `HistoryState`, it contains the index and the unique key of the route.

```tsx
export type ParsedHistoryState = HistoryState & {
  key?: string // TODO: Remove in v2 - use __TSR_key instead
  __TSR_key?: string
  __TSR_index: number
}
```

# ParsedLocation type

The `ParsedLocation` type represents a parsed location in TanStack Router. It contains a lot of useful information about the current location, including the pathname, search params, hash, location state, and route masking information.

```tsx
interface ParsedLocation {
  href: string
  pathname: string
  search: TFullSearchSchema
  searchStr: string
  state: ParsedHistoryState
  hash: string
  maskedLocation?: ParsedLocation
  unmaskOnReload?: boolean
}
```

# Redirect type

The `Redirect` type is used to represent a redirect action in TanStack Router.

```tsx
export type Redirect = {
  statusCode?: number
  throw?: any
  headers?: HeadersInit
} & NavigateOptions
```

- [`NavigateOptions`](../NavigateOptionsType.md)

## Redirect properties

The `Redirect` object accepts/contains the following properties:

### `statusCode` property

- Type: `number`
- Optional
- The HTTP status code to use when redirecting

### `throw` property

- Type: `any`
- Optional
- If provided, will throw the redirect object instead of returning it. This can be useful in places where `throwing` in a function might cause it to have a return type of `never`. In that case, you can use `redirect({ throw: true })` to throw the redirect object instead of returning it.

### `headers` property

- Type: `HeadersInit`
- Optional
- The HTTP headers to use when redirecting.

### Navigation Properties

Since `Redirect` extends `NavigateOptions`, it also supports navigation properties:

- **`to`**: Use for internal application routes (e.g., `/dashboard`, `../profile`)
- **`href`**: Use for external URLs (e.g., `https://example.com`, `https://authprovider.com`)

> **Important**: For external URLs, always use the `href` property instead of `to`. The `to` property is designed for internal navigation within your application.

# Register type

This type is used to register a route tree with a router instance. Doing so unlocks the full type safety of TanStack Router, including top-level exports from the `@tanstack/react-router` package.

```tsx
export type Register = {
  // router: [Your router type here]
}
```

To register a route tree with a router instance, use declaration merging to add the type of your router instance to the Register interface under the `router` property:

## Examples

```tsx
const router = createRouter({
  // ...
})

declare module '@tanstack/react-router' {
  interface Register {
    router: typeof router
  }
}
```

# RootRoute class

> [!CAUTION]
> This class has been deprecated and will be removed in the next major version of TanStack Router.
> Please use the [`createRootRoute`](../createRootRouteFunction.md) function instead.

The `RootRoute` class extends the `Route` class and can be used to create a root route instance. A root route instance can then be used to create a route tree.

## `RootRoute` constructor

The `RootRoute` constructor accepts an object as its only argument.

### Constructor options

The options that will be used to configure the root route instance.

- Type:

```tsx
Omit<RouteOptions, 'path' | 'id' | 'getParentRoute' | 'caseSensitive' | 'parseParams' | 'stringifyParams'>
```

- [`RouteOptions`](../RouteOptionsType.md)
- Optional

## Constructor returns

A new [`Route`](../RouteType.md) instance.

## Examples

```tsx
import { RootRoute, createRouter, Outlet } from '@tanstack/react-router'

const rootRoute = new RootRoute({
  component: () => <Outlet />,
  // ... root route options
})

const routeTree = rootRoute.addChildren([
  // ... other routes
])

const router = createRouter({
  routeTree,
})
```

# RouteApi class

> [!CAUTION]
> This class has been deprecated and will be removed in the next major version of TanStack Router.
> Please use the [`getRouteApi`](../getRouteApiFunction.md) function instead.

The `RouteApi` class provides type-safe version of common hooks like `useParams`, `useSearch`, `useRouteContext`, `useNavigate`, `useLoaderData`, and `useLoaderDeps` that are pre-bound to a specific route ID and corresponding registered route types.

## Constructor options

The `RouteApi` constructor accepts a single argument: the `options` that will be used to configure the `RouteApi` instance.

### `opts.routeId` option

- Type: `string`
- Required
- The route ID to which the `RouteApi` instance will be bound

## Constructor returns

- An instance of the [`RouteApi`](../RouteApiType.md) that is pre-bound to the route ID that it was called with.

## Examples

```tsx
import { RouteApi } from '@tanstack/react-router'

const routeApi = new RouteApi({ id: '/posts' })

export function PostsPage() {
  const posts = routeApi.useLoaderData()
  // ...
}
```

# RouteApi Type

The `RouteApi` describes an instance that provides type-safe versions of common hooks like `useParams`, `useSearch`, `useRouteContext`, `useNavigate`, `useLoaderData`, and `useLoaderDeps` that are pre-bound to a specific route ID and corresponding registered route types.

## `RouteApi` properties and methods

The `RouteApi` has the following properties and methods:

### `useMatch` method

```tsx
  useMatch<TSelected = TAllContext>(opts?: {
    select?: (match: TAllContext) => TSelected
  }): TSelected
```

- A type-safe version of the [`useMatch`](../useMatchHook.md) hook that is pre-bound to the route ID that the `RouteApi` instance was created with.
- Options
  - `opts.select`
    - Optional
    - `(match: RouteMatch) => TSelected`
    - If supplied, this function will be called with the route match and the return value will be returned from `useMatch`. This value will also be used to determine if the hook should re-render its parent component using shallow equality checks.
  - `opts.structuralSharing`
    - Optional
    - `boolean`
    - Configures whether structural sharing is enabled for the value returned by `select`.
    - See the [Render Optimizations guide](../../../guide/render-optimizations.md) for more information.
- Returns
  - If a `select` function is provided, the return value of the `select` function.
  - If no `select` function is provided, the `RouteMatch` object or a loosened version of the `RouteMatch` object if `opts.strict` is `false`.

### `useRouteContext` method

```tsx
  useRouteContext<TSelected = TAllContext>(opts?: {
    select?: (search: TAllContext) => TSelected
  }): TSelected
```

- A type-safe version of the [`useRouteContext`](../useRouteContextHook.md) hook that is pre-bound to the route ID that the `RouteApi` instance was created with.
- Options
  - `opts.select`
    - Optional
    - `(match: RouteContext) => TSelected`
    - If supplied, this function will be called with the route match and the return value will be returned from `useRouteContext`. This value will also be used to determine if the hook should re-render its parent component using shallow equality checks.
- Returns
  - If a `select` function is provided, the return value of the `select` function.
  - If no `select` function is provided, the `RouteContext` object or a loosened version of the `RouteContext` object if `opts.strict` is `false`.

### `useSearch` method

```tsx
  useSearch<TSelected = TFullSearchSchema>(opts?: {
    select?: (search: TFullSearchSchema) => TSelected
  }): TSelected
```

- A type-safe version of the [`useSearch`](../useSearchHook.md) hook that is pre-bound to the route ID that the `RouteApi` instance was created with.
- Options
  - `opts.select`
    - Optional
    - `(match: TFullSearchSchema) => TSelected`
    - If supplied, this function will be called with the route match and the return value will be returned from `useSearch`. This value will also be used to determine if the hook should re-render its parent component using shallow equality checks.
  - `opts.structuralSharing`
    - Optional
    - `boolean`
    - Configures whether structural sharing is enabled for the value returned by `select`.
    - See the [Render Optimizations guide](../../../guide/render-optimizations.md) for more information.
- Returns
  - If a `select` function is provided, the return value of the `select` function.
  - If no `select` function is provided, the `TFullSearchSchema` object or a loosened version of the `TFullSearchSchema` object if `opts.strict` is `false`.

### `useParams` method

```tsx
  useParams<TSelected = TAllParams>(opts?: {
    select?: (params: TAllParams) => TSelected
  }): TSelected
```

- A type-safe version of the [`useParams`](../useParamsHook.md) hook that is pre-bound to the route ID that the `RouteApi` instance was created with.
- Options
  - `opts.select`
    - Optional
    - `(match: TAllParams) => TSelected`
    - If supplied, this function will be called with the route match and the return value will be returned from `useParams`. This value will also be used to determine if the hook should re-render its parent component using shallow equality checks.
  - `opts.structuralSharing`
    - Optional
    - `boolean`
    - Configures whether structural sharing is enabled for the value returned by `select`.
    - See the [Render Optimizations guide](../../../guide/render-optimizations.md) for more information.
- Returns
  - If a `select` function is provided, the return value of the `select` function.
  - If no `select` function is provided, the `TAllParams` object or a loosened version of the `TAllParams` object if `opts.strict` is `false`.

### `useLoaderData` method

```tsx
  useLoaderData<TSelected = TLoaderData>(opts?: {
    select?: (search: TLoaderData) => TSelected
  }): TSelected
```

- A type-safe version of the [`useLoaderData`](../useLoaderDataHook.md) hook that is pre-bound to the route ID that the `RouteApi` instance was created with.
- Options
  - `opts.select`
    - Optional
    - `(match: TLoaderData) => TSelected`
    - If supplied, this function will be called with the route match and the return value will be returned from `useLoaderData`. This value will also be used to determine if the hook should re-render its parent component using shallow equality checks.
  - `opts.structuralSharing`
    - Optional
    - `boolean`
    - Configures whether structural sharing is enabled for the value returned by `select`.
    - See the [Render Optimizations guide](../../../guide/render-optimizations.md) for more information.
- Returns
  - If a `select` function is provided, the return value of the `select` function.
  - If no `select` function is provided, the `TLoaderData` object or a loosened version of the `TLoaderData` object if `opts.strict` is `false`.

### `useLoaderDeps` method

```tsx
  useLoaderDeps<TSelected = TLoaderDeps>(opts?: {
    select?: (search: TLoaderDeps) => TSelected
  }): TSelected
```

- A type-safe version of the [`useLoaderDeps`](../useLoaderDepsHook.md) hook that is pre-bound to the route ID that the `RouteApi` instance was created with.
- Options
  - `opts.select`
    - Optional
    - `(match: TLoaderDeps) => TSelected`
    - If supplied, this function will be called with the route match and the return value will be returned from `useLoaderDeps`.
  - `opts.structuralSharing`
    - Optional
    - `boolean`
    - Configures whether structural sharing is enabled for the value returned by `select`.
    - See the [Render Optimizations guide](../../../guide/render-optimizations.md) for more information.
- Returns
  - If a `select` function is provided, the return value of the `select` function.
  - If no `select` function is provided, the `TLoaderDeps` object.

### `useNavigate` method

```tsx
  useNavigate(): // navigate function
```

- A type-safe version of [`useNavigate`](../useNavigateHook.md) that is pre-bound to the route ID that the `RouteApi` instance was created with.

# Route class

> [!CAUTION]
> This class has been deprecated and will be removed in the next major version of TanStack Router.
> Please use the [`createRoute`](../createRouteFunction.md) function instead.

The `Route` class implements the `RouteApi` class and can be used to create route instances. A route instance can then be used to create a route tree.

## `Route` constructor

The `Route` constructor accepts an object as its only argument.

### Constructor options

- Type: [`RouteOptions`](../RouteOptionsType.md)
- Required
- The options that will be used to configure the route instance

### Constructor returns

A new [`Route`](../RouteType.md) instance.

## Examples

```tsx
import { Route } from '@tanstack/react-router'
import { rootRoute } from './__root'

const indexRoute = new Route({
  getParentRoute: () => rootRoute,
  path: '/',
  loader: () => {
    return 'Hello World'
  },
  component: IndexComponent,
})

function IndexComponent() {
  const data = indexRoute.useLoaderData()
  return <div>{data}</div>
}
```

# RouteMask type

The `RouteMask` type extends the [`ToOptions`](../ToOptionsType.md) type and has other the necessary properties to create a route mask.

## RouteMask properties

The `RouteMask` type accepts an object with the following properties:

### `...ToOptions`

- Type: [`ToOptions`](../ToOptionsType.md)
- Required
- The options that will be used to configure the route mask

### `options.routeTree`

- Type: `TRouteTree`
- Required
- The route tree that this route mask will support

### `options.unmaskOnReload`

- Type: `boolean`
- Optional
- If `true`, the route mask will be removed when the page is reloaded

# RouteMatch type

The `RouteMatch` type represents a route match in TanStack Router.

```tsx
interface RouteMatch {
  id: string
  routeId: string
  pathname: string
  params: Route['allParams']
  status: 'pending' | 'success' | 'error' | 'redirected' | 'notFound'
  isFetching: false | 'beforeLoad' | 'loader'
  showPending: boolean
  error: unknown
  paramsError: unknown
  searchError: unknown
  updatedAt: number
  loaderData?: Route['loaderData']
  context: Route['allContext']
  search: Route['fullSearchSchema']
  fetchedAt: number
  abortController: AbortController
  cause: 'enter' | 'stay'
  ssr?: boolean | 'data-only'
}
```

# RouteOptions type

The `RouteOptions` type is used to describe the options that can be used when creating a route.

## RouteOptions properties

The `RouteOptions` type accepts an object with the following properties:

### `getParentRoute` method

- Type: `() => TParentRoute`
- Required
- A function that returns the parent route of the route being created. This is required to provide full type safety to child route configurations and to ensure that the route tree is built correctly.

### `path` property

- Type: `string`
- Required, unless an `id` is provided to configure the route as a pathless layout route
- The path segment that will be used to match the route.

### `id` property

- Type: `string`
- Optional, but required if a `path` is not provided
- The unique identifier for the route if it is to be configured as a pathless layout route. If provided, the route will not match against the location pathname and its routes will be flattened into its parent route for matching.

### `component` property

- Type: `RouteComponent` or `LazyRouteComponent`
- Optional - Defaults to `<Outlet />`
- The content to be rendered when the route is matched.

### `errorComponent` property

- Type: `RouteComponent` or `LazyRouteComponent`
- Optional - Defaults to `routerOptions.defaultErrorComponent`
- The content to be rendered when the route encounters an error.

### `pendingComponent` property

- Type: `RouteComponent` or `LazyRouteComponent`
- Optional - Defaults to `routerOptions.defaultPendingComponent`
- The content to be rendered if and when the route is pending and has reached its pendingMs threshold.

### `notFoundComponent` property

- Type: `NotFoundRouteComponent` or `LazyRouteComponent`
- Optional - Defaults to `routerOptions.defaultNotFoundComponent`
- The content to be rendered when the route is not found.

### `validateSearch` method

- Type: `(rawSearchParams: unknown) => TSearchSchema`
- Optional
- A function that will be called when this route is matched and passed the raw search params from the current location and return valid parsed search params. If this function throws, the route will be put into an error state and the error will be thrown during render. If this function does not throw, its return value will be used as the route's search params and the return type will be inferred into the rest of the router.
- Optionally, the parameter type can be tagged with the `SearchSchemaInput` type like this: `(searchParams: TSearchSchemaInput & SearchSchemaInput) => TSearchSchema`. If this tag is present, `TSearchSchemaInput` will be used to type the `search` property of `<Link />` and `navigate()` **instead of** `TSearchSchema`. The difference between `TSearchSchemaInput` and `TSearchSchema` can be useful, for example, to express optional search parameters.

### `search.middlewares` property

- Type: `(({search: TSearchSchema, next: (newSearch: TSearchSchema) => TSearchSchema}) => TSearchSchema)[]`
- Optional
- Search middlewares are functions that transform the search parameters when generating new links for a route or its descendants.
- A search middleware is passed in the current search (if it is the first middleware to run) or is invoked by the previous middleware calling `next`.

### `parseParams` method (⚠️ deprecated, use `params.parse` instead)

- Type: `(rawParams: Record<string, string>) => TParams`
- Optional
- A function that will be called when this route is matched and passed the raw params from the current location and return valid parsed params. If this function throws, the route will be put into an error state and the error will be thrown during render. If this function does not throw, its return value will be used as the route's params and the return type will be inferred into the rest of the router.

### `stringifyParams` method (⚠️ deprecated, use `params.stringify` instead)

- Type: `(params: TParams) => Record<string, string>`
- Required if `parseParams` is provided
- A function that will be called when this route's parsed params are being used to build a location. This function should return a valid object of `Record<string, string>` mapping.

### `params.parse` method

- Type: `(rawParams: Record<string, string>) => TParams`
- Optional
- A function that will be called when this route is matched and passed the raw params from the current location and return valid parsed params. If this function throws, the route will be put into an error state and the error will be thrown during render. If this function does not throw, its return value will be used as the route's params and the return type will be inferred into the rest of the router.

### `params.stringify` method

- Type: `(params: TParams) => Record<string, string>`
- A function that will be called when this route's parsed params are being used to build a location. This function should return a valid object of `Record<string, string>` mapping.

### `beforeLoad` method

- Type:

```tsx
type beforeLoad = (
  opts: RouteMatch & {
    search: TFullSearchSchema
    abortController: AbortController
    preload: boolean
    params: TAllParams
    context: TParentContext
    location: ParsedLocation
    navigate: NavigateFn<AnyRoute> // @deprecated
    buildLocation: BuildLocationFn<AnyRoute>
    cause: 'enter' | 'stay'
  }
) => Promise<TRouteContext> | TRouteContext | void
```

- Optional
- [`ParsedLocation`](../ParsedLocationType.md)
- This async function is called before a route is loaded. If an error is thrown here, the route's loader will not be called and the route will not render. If thrown during a navigation, the navigation will be canceled and the error will be passed to the `onError` function. If thrown during a preload event, the error will be logged to the console and the preload will fail.
- If this function returns a promise, the route will be put into a pending state and cause rendering to suspend until the promise resolves. If this route's pendingMs threshold is reached, the `pendingComponent` will be shown until it resolves. If the promise rejects, the route will be put into an error state and the error will be thrown during render.
- If this function returns a `TRouteContext` object, that object will be merged into the route's context and be made available in the `loader` and other related route components/methods.
- It's common to use this function to check if a user is authenticated and redirect them to a login page if they are not. To do this, you can either return or throw a `redirect` object from this function.

> 🚧 `opts.navigate` has been deprecated and will be removed in the next major release. Use `throw redirect({ to: '/somewhere' })` instead. Read more about the `redirect` function [here](../redirectFunction.md).

### `loader` method

- Type:

```tsx
type loader = (
  opts: RouteMatch & {
    abortController: AbortController
    cause: 'preload' | 'enter' | 'stay'
    context: TAllContext
    deps: TLoaderDeps
    location: ParsedLocation
    params: TAllParams
    preload: boolean
    parentMatchPromise: Promise<MakeRouteMatchFromRoute<TParentRoute>>
    navigate: NavigateFn<AnyRoute> // @deprecated
    route: AnyRoute
  }
) => Promise<TLoaderData> | TLoaderData | void
```

- Optional
- [`ParsedLocation`](../ParsedLocationType.md)
- This async function is called when a route is matched and passed the route's match object. If an error is thrown here, the route will be put into an error state and the error will be thrown during render. If thrown during a navigation, the navigation will be canceled and the error will be passed to the `onError` function. If thrown during a preload event, the error will be logged to the console and the preload will fail.
- If this function returns a promise, the route will be put into a pending state and cause rendering to suspend until the promise resolves. If this route's pendingMs threshold is reached, the `pendingComponent` will be shown until it resolves. If the promise rejects, the route will be put into an error state and the error will be thrown during render.
- If this function returns a `TLoaderData` object, that object will be stored on the route match until the route match is no longer active. It can be accessed using the `useLoaderData` hook in any component that is a child of the route match before another `<Outlet />` is rendered.
- Deps must be returned by your `loaderDeps` function in order to appear.

> 🚧 `opts.navigate` has been deprecated and will be removed in the next major release. Use `throw redirect({ to: '/somewhere' })` instead. Read more about the `redirect` function [here](../redirectFunction.md).

### `loaderDeps` method

- Type:

```tsx
type loaderDeps = (opts: { search: TFullSearchSchema }) => Record<string, any>
```

- Optional
- A function that will be called before this route is matched to provide additional unique identification to the route match and serve as a dependency tracker for when the match should be reloaded. It should return any serializable value that can uniquely identify the route match from navigation to navigation.
- By default, path params are already used to uniquely identify a route match, so it's unnecessary to return these here.
- If your route match relies on search params for unique identification, it's required that you return them here so they can be made available in the `loader`'s `deps` argument.

### `staleTime` property

- Type: `number`
- Optional
- Defaults to `routerOptions.defaultStaleTime`, which defaults to `0`
- The amount of time in milliseconds that a route match's loader data will be considered fresh. If a route match is matched again within this time frame, its loader data will not be reloaded.

### `preloadStaleTime` property

- Type: `number`
- Optional
- Defaults to `routerOptions.defaultPreloadStaleTime`, which defaults to `30_000` ms (30 seconds)
- The amount of time in milliseconds that a route match's loader data will be considered fresh when preloading. If a route match is preloaded again within this time frame, its loader data will not be reloaded. If a route match is loaded (for navigation) within this time frame, the normal `staleTime` is used instead.

### `gcTime` property

- Type: `number`
- Optional
- Defaults to `routerOptions.defaultGcTime`, which defaults to 30 minutes.
- The amount of time in milliseconds that a route match's loader data will be kept in memory after a preload or it is no longer in use.

### `shouldReload` property

- Type: `boolean | ((args: LoaderArgs) => boolean)`
- Optional
- If `false` or returns `false`, the route match's loader data will not be reloaded on subsequent matches.
- If `true` or returns `true`, the route match's loader data will be reloaded on subsequent matches.
- If `undefined` or returns `undefined`, the route match's loader data will adhere to the default stale-while-revalidate behavior.

### `caseSensitive` property

- Type: `boolean`
- Optional
- If `true`, this route will be matched as case-sensitive.

### `wrapInSuspense` property

- Type: `boolean`
- Optional
- If `true`, this route will be forcefully wrapped in a suspense boundary, regardless if a reason is found to do so from inspecting its provided components.

### `pendingMs` property

- Type: `number`
- Optional
- Defaults to `routerOptions.defaultPendingMs`, which defaults to `1000`
- The threshold in milliseconds that a route must be pending before its `pendingComponent` is shown.

### `pendingMinMs` property

- Type: `number`
- Optional
- Defaults to `routerOptions.defaultPendingMinMs` which defaults to `500`
- The minimum amount of time in milliseconds that the pending component will be shown for if it is shown. This is useful to prevent the pending component from flashing on the screen for a split second.

### `preloadMaxAge` property

- Type: `number`
- Optional
- Defaults to `30_000` ms (30 seconds)
- The maximum amount of time in milliseconds that a route's preloaded route data will be cached for. If a route is not matched within this time frame, its loader data will be discarded.

### `preSearchFilters` property (⚠️ deprecated, use `search.middlewares` instead)

- Type: `((search: TFullSearchSchema) => TFullSearchSchema)[]`
- Optional
- An array of functions that will be called when generating any new links to this route or its grandchildren.
- Each function will be called with the current search params and should return a new search params object that will be used to generate the link.
- It has a `pre` prefix because it is called before the user-provided function that is passed to `navigate`/`Link` etc has a chance to modify the search params.

### `postSearchFilters` property (⚠️ deprecated, use `search.middlewares` instead)

- Type: `((search: TFullSearchSchema) => TFullSearchSchema)[]`
- Optional
- An array of functions that will be called when generating any new links to this route or its grandchildren.
- Each function will be called with the current search params and should return a new search params object that will be used to generate the link.
- It has a `post` prefix because it is called after the user-provided function that is passed to `navigate`/`Link` etc has modified the search params.

### `onError` property

- Type: `(error: any) => void`
- Optional
- A function that will be called when an error is thrown during a navigation or preload event.
- If this function throws a [`redirect`](../redirectFunction.md), then the router will process and apply the redirect immediately.

### `onEnter` property

- Type: `(match: RouteMatch) => void`
- Optional
- A function that will be called when a route is matched and loaded after not being matched in the previous location.

### `onStay` property

- Type: `(match: RouteMatch) => void`
- Optional
- A function that will be called when a route is matched and loaded after being matched in the previous location.

### `onLeave` property

- Type: `(match: RouteMatch) => void`
- Optional
- A function that will be called when a route is no longer matched after being matched in the previous location.

### `onCatch` property

- Type: `(error: Error, errorInfo: ErrorInfo) => void`
- Optional - Defaults to `routerOptions.defaultOnCatch`
- A function that will be called when errors are caught when the route encounters an error.

### `remountDeps` method

- Type:

```tsx
type remountDeps = (opts: RemountDepsOptions) => any

interface RemountDepsOptions<in out TRouteId, in out TFullSearchSchema, in out TAllParams, in out TLoaderDeps> {
  routeId: TRouteId
  search: TFullSearchSchema
  params: TAllParams
  loaderDeps: TLoaderDeps
}
```

- Optional
- A function that will be called to determine whether a route component shall be remounted after navigation. If this function returns a different value than previously, it will remount.
- The return value needs to be JSON serializable.
- By default, a route component will not be remounted if it stays active after a navigation.

Example:
If you want to configure to remount a route component upon `params` change, use:

```tsx
remountDeps: ({ params }) => params
```

### `headers` method

- Type:

```tsx
type headers = (opts: {
  matches: Array<RouteMatch>
  match: RouteMatch
  params: TAllParams
  loaderData?: TLoaderData
}) => Promise<Record<string, string>> | Record<string, string>
```

- Optional
- Allows you to specify custom HTTP headers to be sent when this route is rendered during SSR. The function receives the current match context and should return a plain object of header name/value pairs.

### `head` method

- Type:

```tsx
type head = (ctx: { matches: Array<RouteMatch>; match: RouteMatch; params: TAllParams; loaderData?: TLoaderData }) =>
  | Promise<{
      links?: RouteMatch['links']
      scripts?: RouteMatch['headScripts']
      meta?: RouteMatch['meta']
      styles?: RouteMatch['styles']
    }>
  | {
      links?: RouteMatch['links']
      scripts?: RouteMatch['headScripts']
      meta?: RouteMatch['meta']
      styles?: RouteMatch['styles']
    }
```

- Optional
- Returns additional elements to inject into the document `<head>` for this route. Use it to add route-level SEO metadata, preload links, inline styles, or custom scripts.

### `scripts` method

- Type:

```tsx
type scripts = (ctx: {
  matches: Array<RouteMatch>
  match: RouteMatch
  params: TAllParams
  loaderData?: TLoaderData
}) => Promise<RouteMatch['scripts']> | RouteMatch['scripts']
```

- Optional
- A shorthand helper to return only `<script>` elements. Equivalent to returning the `scripts` field from the `head` method.

### `codeSplitGroupings` property

- Type: `Array<Array<'loader' | 'component' | 'pendingComponent' | 'notFoundComponent' | 'errorComponent'>>`
- Optional
- Fine-grained control over how the router groups lazy-loaded pieces of a route into chunks. Each inner array represents a group of assets that will be placed into the same bundle during code-splitting.

# Route type

The `Route` type is used to describe a route instance.

## `Route` properties and methods

An instance of the `Route` has the following properties and methods:

### `.addChildren` method

- Type: `(children: Route[]) => this`
- Adds child routes to the route instance and returns the route instance (but with updated types to reflect the new children).

### `.update` method

- Type: `(options: Partial<UpdatableRouteOptions>) => this`
- Updates the route instance with new options and returns the route instance (but with updated types to reflect the new options).
- In some circumstances, it can be useful to update a route instance's options after it has been created to avoid circular type references.
- ...`RouteApi` methods

### `.lazy` method

- Type: `(lazyImporter: () => Promise<Partial<UpdatableRouteOptions>>) => this`
- Updates the route instance with a new lazy importer which will be resolved lazily when loading the route. This can be useful for code splitting.

### ...`RouteApi` methods

- All of the methods from [`RouteApi`](../RouteApiType.md) are available.

# Router Class

> [!CAUTION]
> This class has been deprecated and will be removed in the next major version of TanStack Router.
> Please use the [`createRouter`](../createRouterFunction.md) function instead.

The `Router` class is used to instantiate a new router instance.

## `Router` constructor

The `Router` constructor accepts a single argument: the `options` that will be used to configure the router instance.

### Constructor options

- Type: [`RouterOptions`](../RouterOptionsType.md)
- Required
- The options that will be used to configure the router instance.

### Constructor returns

- An instance of the [`Router`](../RouterType.md).

## Examples

```tsx
import { Router, RouterProvider } from '@tanstack/react-router'
import { routeTree } from './routeTree.gen'

const router = new Router({
  routeTree,
  defaultPreload: 'intent',
})

export default function App() {
  return <RouterProvider router={router} />
}
```

# RouterEvents type

The `RouterEvents` type contains all of the events that the router can emit. Each top-level key of this type, represents the name of an event that the router can emit. The values of the keys are the event payloads.

```tsx
type RouterEvents = {
  onBeforeNavigate: {
    type: 'onBeforeNavigate'
    fromLocation?: ParsedLocation
    toLocation: ParsedLocation
    pathChanged: boolean
    hrefChanged: boolean
  }
  onBeforeLoad: {
    type: 'onBeforeLoad'
    fromLocation?: ParsedLocation
    toLocation: ParsedLocation
    pathChanged: boolean
    hrefChanged: boolean
  }
  onLoad: {
    type: 'onLoad'
    fromLocation?: ParsedLocation
    toLocation: ParsedLocation
    pathChanged: boolean
    hrefChanged: boolean
  }
  onResolved: {
    type: 'onResolved'
    fromLocation?: ParsedLocation
    toLocation: ParsedLocation
    pathChanged: boolean
    hrefChanged: boolean
  }
  onBeforeRouteMount: {
    type: 'onBeforeRouteMount'
    fromLocation?: ParsedLocation
    toLocation: ParsedLocation
    pathChanged: boolean
    hrefChanged: boolean
  }
  onInjectedHtml: {
    type: 'onInjectedHtml'
    promise: Promise<string>
  }
  onRendered: {
    type: 'onRendered'
    fromLocation?: ParsedLocation
    toLocation: ParsedLocation
  }
}
```

## RouterEvents properties

Once an event is emitted, the following properties will be present on the event payload.

### `type` property

- Type: `onBeforeNavigate | onBeforeLoad | onLoad | onBeforeRouteMount | onResolved`
- The type of the event
- This is useful for discriminating between events in a listener function.

### `fromLocation` property

- Type: [`ParsedLocation`](../ParsedLocationType.md)
- The location that the router is transitioning from.

### `toLocation` property

- Type: [`ParsedLocation`](../ParsedLocationType.md)
- The location that the router is transitioning to.

### `pathChanged` property

- Type: `boolean`
- `true` if the path has changed between the `fromLocation` and `toLocation`.

### `hrefChanged` property

- Type: `boolean`
- `true` if the href has changed between the `fromLocation` and `toLocation`.

## Example

```tsx
import { createRouter } from '@tanstack/react-router'
import { routeTree } from './routeTree.gen'

const router = createRouter({ routeTree })

const unsub = router.subscribe('onResolved', (evt) => {
  // ...
})
```

# RouterOptions

The `RouterOptions` type contains all of the options that can be used to configure a router instance.

## RouterOptions properties

The `RouterOptions` type accepts an object with the following properties and methods:

### `routeTree` property

- Type: `AnyRoute`
- Required
- The route tree that will be used to configure the router instance.

### `history` property

- Type: `RouterHistory`
- Optional
- The history object that will be used to manage the browser history. If not provided, a new `createBrowserHistory` instance will be created and used.

### `stringifySearch` method

- Type: `(search: Record<string, any>) => string`
- Optional
- A function that will be used to stringify search params when generating links.
- Defaults to `defaultStringifySearch`.

### `parseSearch` method

- Type: `(search: string) => Record<string, any>`
- Optional
- A function that will be used to parse search params when parsing the current location.
- Defaults to `defaultParseSearch`.

### `search.strict` property

- Type: `boolean`
- Optional
- Defaults to `false`
- Configures how unknown search params (= not returned by any `validateSearch`) are treated.
- If `false`, unknown search params will be kept.
- If `true`, unknown search params will be removed.

### `defaultPreload` property

- Type: `undefined | false | 'intent' | 'viewport' | 'render'`
- Optional
- Defaults to `false`
- If `false`, routes will not be preloaded by default in any way.
- If `'intent'`, routes will be preloaded by default when the user hovers over a link or a `touchstart` event is detected on a `<Link>`.
- If `'viewport'`, routes will be preloaded by default when they are within the viewport of the browser.
- If `'render'`, routes will be preloaded by default as soon as they are rendered in the DOM.

### `defaultPreloadDelay` property

- Type: `number`
- Optional
- Defaults to `50`
- The delay in milliseconds that a route must be hovered over or touched before it is preloaded.

### `defaultComponent` property

- Type: `RouteComponent`
- Optional
- Defaults to `Outlet`
- The default `component` a route should use if no component is provided.

### `defaultErrorComponent` property

- Type: `RouteComponent`
- Optional
- Defaults to `ErrorComponent`
- The default `errorComponent` a route should use if no error component is provided.

### `defaultNotFoundComponent` property

- Type: `NotFoundRouteComponent`
- Optional
- Defaults to `NotFound`
- The default `notFoundComponent` a route should use if no notFound component is provided.

### `defaultPendingComponent` property

- Type: `RouteComponent`
- Optional
- The default `pendingComponent` a route should use if no pending component is provided.

### `defaultPendingMs` property

- Type: `number`
- Optional
- Defaults to `1000`
- The default `pendingMs` a route should use if no pendingMs is provided.

### `defaultPendingMinMs` property

- Type: `number`
- Optional
- Defaults to `500`
- The default `pendingMinMs` a route should use if no pendingMinMs is provided.

### `defaultStaleTime` property

- Type: `number`
- Optional
- Defaults to `0`
- The default `staleTime` a route should use if no staleTime is provided.

### `defaultPreloadStaleTime` property

- Type: `number`
- Optional
- Defaults to `30_000` ms (30 seconds)
- The default `preloadStaleTime` a route should use if no preloadStaleTime is provided.

### `defaultPreloadGcTime` property

- Type: `number`
- Optional
- Defaults to `routerOptions.defaultGcTime`, which defaults to 30 minutes.
- The default `preloadGcTime` a route should use if no preloadGcTime is provided.

### `defaultGcTime` property

- Type: `number`
- Optional
- Defaults to 30 minutes.
- The default `gcTime` a route should use if no gcTime is provided.

### `defaultOnCatch` property

- Type: `(error: Error, errorInfo: ErrorInfo) => void`
- Optional
- The default `onCatch` handler for errors caught by the Router ErrorBoundary

### `disableGlobalCatchBoundary` property

- Type: `boolean`
- Optional
- Defaults to `false`
- When `true`, disables the global catch boundary that normally wraps all route matches. This allows unhandled errors to bubble up to top-level error handlers in the browser.
- Useful for testing tools, error reporting services, and debugging scenarios.

### `defaultViewTransition` property

- Type: `boolean | ViewTransitionOptions`
- Optional
- If `true`, route navigations will be called using `document.startViewTransition()`.
- If [`ViewTransitionOptions`](../ViewTransitionOptionsType.md), route navigations will be called using `document.startViewTransition({update, types})`
  where `types` will be the strings array passed with `ViewTransitionOptions["types"]`. If the browser does not support viewTransition types,
  the navigation will fall back to normal `document.startTransition()`, same as if `true` was passed.
- If the browser does not support this api, this option will be ignored.
- See [MDN](https://developer.mozilla.org/en-US/docs/Web/API/Document/startViewTransition) for more information on how this function works.
- See [Google](https://developer.chrome.com/docs/web-platform/view-transitions/same-document#view-transition-types) for more information on viewTransition types

### `defaultHashScrollIntoView` property

- Type: `boolean | ScrollIntoViewOptions`
- Optional
- Defaults to `true` so the element with an id matching the hash will be scrolled into view after the location is committed to history.
- If `false`, the element with an id matching the hash will not be scrolled into view after the location is committed to history.
- If an object is provided, it will be passed to the `scrollIntoView` method as options.
- See [MDN](https://developer.mozilla.org/en-US/docs/Web/API/Element/scrollIntoView) for more information on `ScrollIntoViewOptions`.

### `caseSensitive` property

- Type: `boolean`
- Optional
- Defaults to `false`
- If `true`, all routes will be matched as case-sensitive.

### `basepath` property

- Type: `string`
- Optional
- Defaults to `/`
- The basepath for the entire router. This is useful for mounting a router instance at a subpath.

### `context` property

- Type: `any`
- Optional or required if the root route was created with [`createRootRouteWithContext()`](../createRootRouteWithContextFunction.md).
- The root context that will be provided to all routes in the route tree. This can be used to provide a context to all routes in the tree without having to provide it to each route individually.

### `dehydrate` method

- Type: `() => TDehydrated`
- Optional
- A function that will be called when the router is dehydrated. The return value of this function will be serialized and stored in the router's dehydrated state.

### `hydrate` method

- Type: `(dehydrated: TDehydrated) => void`
- Optional
- A function that will be called when the router is hydrated. The return value of this function will be serialized and stored in the router's dehydrated state.

### `routeMasks` property

- Type: `RouteMask[]`
- Optional
- An array of route masks that will be used to mask routes in the route tree. Route masking is when you display a route at a different path than the one it is configured to match, like a modal popup that when shared will unmask to the modal's content instead of the modal's context.

### `unmaskOnReload` property

- Type: `boolean`
- Optional
- Defaults to `false`
- If `true`, route masks will, by default, be removed when the page is reloaded. This can be overridden on a per-mask basis by setting the `unmaskOnReload` option on the mask, or on a per-navigation basis by setting the `unmaskOnReload` option in the `Navigate` options.

### `Wrap` property

- Type: `React.Component`
- Optional
- A component that will be used to wrap the entire router. This is useful for providing a context to the entire router. Only non-DOM-rendering components like providers should be used, anything else will cause a hydration error.

**Example**

```tsx
import { createRouter } from '@tanstack/react-router'

const router = createRouter({
  // ...
  Wrap: ({ children }) => {
    return <MyContext.Provider value={myContext}>{children}</MyContext>
  },
})
```

### `InnerWrap` property

- Type: `React.Component`
- Optional
- A component that will be used to wrap the inner contents of the router. This is useful for providing a context to the inner contents of the router where you also need access to the router context and hooks. Only non-DOM-rendering components like providers should be used, anything else will cause a hydration error.

**Example**

```tsx
import { createRouter } from '@tanstack/react-router'

const router = createRouter({
  // ...
  InnerWrap: ({ children }) => {
    const routerState = useRouterState()

    return (
      <MyContext.Provider value={myContext}>
        {children}
      </MyContext>
    )
  },
})
```

### `notFoundMode` property

- Type: `'root' | 'fuzzy'`
- Optional
- Defaults to `'fuzzy'`
- This property controls how TanStack Router will handle scenarios where it cannot find a route to match the current location. See the [Not Found Errors guide](../../../guide/not-found-errors.md) for more information.

### `notFoundRoute` property

- **Deprecated**
- Type: `NotFoundRoute`
- Optional
- A route that will be used as the default not found route for every branch of the route tree. This can be overridden on a per-branch basis by providing a not found route to the `NotFoundRoute` option on the root route of the branch.

### `trailingSlash` property

- Type: `'always' | 'never' | 'preserve'`
- Optional
- Defaults to `never`
- Configures how trailing slashes are treated. `'always'` will add a trailing slash if not present, `'never'` will remove the trailing slash if present and `'preserve'` will not modify the trailing slash.

### `pathParamsAllowedCharacters` property

- Type: `Array<';' | ':' | '@' | '&' | '=' | '+' | '$' | ','>`
- Optional
- Configures which URI characters are allowed in path params that would ordinarily be escaped by encodeURIComponent.

### `defaultStructuralSharing` property

- Type: `boolean`
- Optional
- Defaults to `false`
- Configures whether structural sharing is enabled by default for fine-grained selectors.
- See the [Render Optimizations guide](../../../guide/render-optimizations.md) for more information.

### `defaultRemountDeps` property

- Type:

```tsx
type defaultRemountDeps = (opts: RemountDepsOptions) => any

interface RemountDepsOptions<in out TRouteId, in out TFullSearchSchema, in out TAllParams, in out TLoaderDeps> {
  routeId: TRouteId
  search: TFullSearchSchema
  params: TAllParams
  loaderDeps: TLoaderDeps
}
```

- Optional
- A default function that will be called to determine whether a route component shall be remounted after navigation. If this function returns a different value than previously, it will remount.
- The return value needs to be JSON serializable.
- By default, a route component will not be remounted if it stays active after a navigation

Example:  
If you want to configure to remount all route components upon `params` change, use:

```tsx
remountDeps: ({ params }) => params
```

# RouterState type

The `RouterState` type represents shape of the internal state of the router. The Router's internal state is useful, if you need to access certain internals of the router, such as any pending matches, is the router in its loading state, etc.

```tsx
type RouterState = {
  status: 'pending' | 'idle'
  isLoading: boolean
  isTransitioning: boolean
  matches: Array<RouteMatch>
  pendingMatches: Array<RouteMatch>
  location: ParsedLocation
  resolvedLocation: ParsedLocation
}
```

## RouterState properties

The `RouterState` type contains all of the properties that are available on the router state.

### `status` property

- Type: `'pending' | 'idle'`
- The current status of the router. If the router is pending, it means that it is currently loading a route or the router is still transitioning to the new route.

### `isLoading` property

- Type: `boolean`
- `true` if the router is currently loading a route or waiting for a route to finish loading.

### `isTransitioning` property

- Type: `boolean`
- `true` if the router is currently transitioning to a new route.

### `matches` property

- Type: [`Array<RouteMatch>`](../RouteMatchType.md)
- An array of all of the route matches that have been resolved and are currently active.

### `pendingMatches` property

- Type: [`Array<RouteMatch>`](../RouteMatchType.md)
- An array of all of the route matches that are currently pending.

### `location` property

- Type: [`ParsedLocation`](../ParsedLocationType.md)
- The latest location that the router has parsed from the browser history. This location may not be resolved and loaded yet.

### `resolvedLocation` property

- Type: [`ParsedLocation`](../ParsedLocationType.md)
- The location that the router has resolved and loaded.

# Router type

The `Router` type is used to describe a router instance.

## `Router` properties and methods

An instance of the `Router` has the following properties and methods:

### `.update` method

- Type: `(newOptions: RouterOptions) => void`
- Updates the router instance with new options.

### `state` property

- Type: [`RouterState`](../RouterStateType.md)
- The current state of the router.

> ⚠️⚠️⚠️ **`router.state` is always up to date, but NOT REACTIVE. If you use `router.state` in a component, the component will not re-render when the router state changes. To get a reactive version of the router state, use the [`useRouterState`](../useRouterStateHook.md) hook.**

### `.subscribe` method

- Type: `(eventType: TType, fn: ListenerFn<RouterEvents[TType]>) => (event: RouterEvent) => void`
- Subscribes to a [`RouterEvent`](../RouterEventsType.md).
- Returns a function that can be used to unsubscribe from the event.
- The callback provided to the returned function will be called with the event that was emitted.

### `.matchRoutes` method

- Type: `(pathname: string, locationSearch?: Record<string, any>, opts?: { throwOnError?: boolean; }) => RouteMatch[]`
- Matches a pathname and search params against the router's route tree and returns an array of route matches.
- If `opts.throwOnError` is `true`, any errors that occur during the matching process will be thrown (in addition to being returned in the route match's `error` property).

### `.cancelMatch` method

- Type: `(matchId: string) => void`
- Cancels a route match that is currently pending by calling `match.abortController.abort()`.

### `.cancelMatches` method

- Type: `() => void`
- Cancels all route matches that are currently pending by calling `match.abortController.abort()` on each one.

### `.buildLocation` method

Builds a new parsed location object that can be used later to navigate to a new location.

- Type: `(opts: BuildNextOptions) => ParsedLocation`
- Properties
  - `from`
    - Type: `string`
    - Optional
    - The path to navigate from. If not provided, the current path will be used.
  - `to`
    - Type: `string | number | null`
    - Optional
    - The path to navigate to. If `null`, the current path will be used.
  - `params`
    - Type: `true | Updater<unknown>`
    - Optional
    - If `true`, the current params will be used. If a function is provided, it will be called with the current params and the return value will be used.
  - `search`
    - Type: `true | Updater<unknown>`
    - Optional
    - If `true`, the current search params will be used. If a function is provided, it will be called with the current search params and the return value will be used.
  - `hash`
    - Type: `true | Updater<string>`
    - Optional
    - If `true`, the current hash will be used. If a function is provided, it will be called with the current hash and the return value will be used.
  - `state`
    - Type: `true | NonNullableUpdater<ParsedHistoryState, HistoryState>`
    - Optional
    - If `true`, the current state will be used. If a function is provided, it will be called with the current state and the return value will be used.
  - `mask`
    - Type: `object`
    - Optional
    - Contains all of the same BuildNextOptions, with the addition of `unmaskOnReload`.
    - `unmaskOnReload`
      - Type: `boolean`
      - Optional
      - If `true`, the route mask will be removed when the page is reloaded. This can be overridden on a per-navigation basis by setting the `unmaskOnReload` option in the `Navigate` options.

### `.commitLocation` method

Commits a new location object to the browser history.

- Type
  ```tsx
  type commitLocation = (
    location: ParsedLocation & {
      replace?: boolean
      resetScroll?: boolean
      hashScrollIntoView?: boolean | ScrollIntoViewOptions
      ignoreBlocker?: boolean
    }
  ) => Promise<void>
  ```
- Properties
  - `location`
    - Type: [`ParsedLocation`](../ParsedLocationType.md)
    - Required
    - The location to commit to the browser history.
  - `replace`
    - Type: `boolean`
    - Optional
    - Defaults to `false`.
    - If `true`, the location will be committed to the browser history using `history.replace` instead of `history.push`.
  - `resetScroll`
    - Type: `boolean`
    - Optional
    - Defaults to `true` so that the scroll position will be reset to 0,0 after the location is committed to the browser history.
    - If `false`, the scroll position will not be reset to 0,0 after the location is committed to history.
  - `hashScrollIntoView`
    - Type: `boolean | ScrollIntoViewOptions`
    - Optional
    - Defaults to `true` so the element with an id matching the hash will be scrolled into view after the location is committed to history.
    - If `false`, the element with an id matching the hash will not be scrolled into view after the location is committed to history.
    - If an object is provided, it will be passed to the `scrollIntoView` method as options.
    - See [MDN](https://developer.mozilla.org/en-US/docs/Web/API/Element/scrollIntoView) for more information on `ScrollIntoViewOptions`.
  - `ignoreBlocker`
    - Type: `boolean`
    - Optional
    - Defaults to `false`.
    - If `true`, navigation will ignore any blockers that might prevent it.

### `.navigate` method

Navigates to a new location.

- Type
  ```tsx
  type navigate = (options: NavigateOptions) => Promise<void>
  ```

### `.invalidate` method

Invalidates route matches by forcing their `beforeLoad` and `load` functions to be called again.

- Type: `(opts?: {filter?: (d: MakeRouteMatchUnion<TRouter>) => boolean, sync?: boolean, forcePending?: boolean }) => Promise<void>`
- This is useful any time your loader data might be out of date or stale. For example, if you have a route that displays a list of posts, and you have a loader function that fetches the list of posts from an API, you might want to invalidate the route matches for that route any time a new post is created so that the list of posts is always up-to-date.
- if `filter` is not supplied, all matches will be invalidated
- if `filter` is supplied, only matches for which `filter` returns `true` will be invalidated.
- if `sync` is true, the promise returned by this function will only resolve once all loaders have finished.
- if `forcePending` is true, the invalidated matches will be put into `'pending'` state regardless whether they are in `'error'` state or not.
- You might also want to invalidate the Router if you imperatively `reset` the router's `CatchBoundary` to trigger loaders again.

### `.clearCache` method

Remove cached route matches.

- Type: `(opts?: {filter?: (d: MakeRouteMatchUnion<TRouter>) => boolean}) => void`
- if `filter` is not supplied, all cached matches will be removed
- if `filter` is supplied, only matches for which `filter` returns `true` will be removed.

### `.load` method

Loads all of the currently matched route matches and resolves when they are all loaded and ready to be rendered.

> ⚠️⚠️⚠️ **`router.load()` respects `route.staleTime` and will not forcefully reload a route match if it is still fresh. If you need to forcefully reload a route match, use `router.invalidate()` instead.**

- Type: `(opts?: {sync?: boolean}) => Promise<void>`
- if `sync` is true, the promise returned by this function will only resolve once all loaders have finished.
- The most common use case for this method is to call it when doing SSR to ensure that all of the critical data for the current route is loaded before attempting to stream or render the application to the client.

### `.preloadRoute` method

Preloads all of the matches that match the provided `NavigateOptions`.

> ⚠️⚠️⚠️ **Preloaded route matches are not stored long-term in the router state. They are only stored until the next attempted navigation action.**

- Type: `(opts?: NavigateOptions) => Promise<RouteMatch[]>`
- Properties
  - `opts`
    - Type: `NavigateOptions`
    - Optional, defaults to the current location.
    - The options that will be used to determine which route matches to preload.
- Returns
  - A promise that resolves with an array of all of the route matches that were preloaded.

### `.loadRouteChunk` method

Loads the JS chunk of the route.

- Type: `(route: AnyRoute) => Promise<void>`

### `.matchRoute` method

Matches a pathname and search params against the router's route tree and returns a route match's params or false if no match was found.

- Type: `(dest: ToOptions, matchOpts?: MatchRouteOptions) => RouteMatch['params'] | false`
- Properties
  - `dest`
    - Type: `ToOptions`
    - Required
    - The destination to match against.
  - `matchOpts`
    - Type: `MatchRouteOptions`
    - Optional
    - Options that will be used to match the destination.
- Returns
  - A route match's params if a match was found.
  - `false` if no match was found.

### `.dehydrate` method

Dehydrates the router's critical state into a serializable object that can be sent to the client in an initial request.

- Type: `() => DehydratedRouter`
- Returns
  - A serializable object that contains the router's critical state.

### `.hydrate` method

Hydrates the router's critical state from a serializable object that was sent from the server in an initial request.

- Type: `(dehydrated: DehydratedRouter) => void`
- Properties
  - `dehydrated`
    - Type: `DehydratedRouter`
    - Required
    - The dehydrated router state that was sent from the server.

# ToMaskOptions type

The `ToMaskOptions` type extends the [`ToOptions`](../ToOptionsType.md) type and describes additional options available when using route masks.

```tsx
type ToMaskOptions = ToOptions & {
  unmaskOnReload?: boolean
}
```

- [`ToOptions`](../ToOptionsType.md)

# ToOptions type

The `ToOptions` type contains several properties that can be used to describe a router destination.

```tsx
type ToOptions = {
  from?: ValidRoutePath | string
  to?: ValidRoutePath | string
  hash?: true | string | ((prev?: string) => string)
  state?: true | HistoryState | ((prev: HistoryState) => HistoryState)
} & SearchParamOptions &
  PathParamOptions

type SearchParamOptions = {
  search?: true | TToSearch | ((prev: TFromSearch) => TToSearch)
}

type PathParamOptions = {
  path?: true | Record<string, TPathParam> | ((prev: TFromParams) => TToParams)
}
```

# UseMatchRouteOptions type

The `UseMatchRouteOptions` type extends the [`ToOptions`](../ToOptionsType.md) type and describes additional options available when using the [`useMatchRoute`](../useMatchRouteHook.md) hook.

```tsx
export type UseMatchRouteOptions = ToOptions & MatchRouteOptions
```

- [`ToOptions`](../ToOptionsType.md)
- [`MatchRouteOptions`](../MatchRouteOptionsType.md)

# ViewTransitionOptions type

The `ViewTransitionOptions` type is used to define a
[viewTransition type](https://developer.chrome.com/docs/web-platform/view-transitions/same-document#view-transition-types).

```tsx
interface ViewTransitionOptions {
  types: Array<string>
}
```

## ViewTransitionOptions properties

The `ViewTransitionOptions` type accepts an object with a single property:

### `types` property

- Type: `Array<string>`
- Required
- The types array that will be passed to the `document.startViewTransition({update, types}) call`;

# Await component

The `Await` component is a component that suspends until the provided promise is resolved or rejected.
This is only necessary for React 18.
If you are using React 19, you can use the `use()` hook instead.

## Await props

The `Await` component accepts the following props:

### `props.promise` prop

- Type: `Promise<T>`
- Required
- The promise to await.

### `props.children` prop

- Type: `(result: T) => React.ReactNode`
- Required
- A function that will be called with the resolved value of the promise.

## Await returns

- Throws an error if the promise is rejected.
- Suspends (throws a promise) if the promise is pending.
- Returns the resolved value of a deferred promise if the promise is resolved using `props.children` as the render function.

## Examples

```tsx
import { Await } from '@tanstack/react-router'

function Component() {
  const { deferredPromise } = route.useLoaderData()

  return <Await promise={deferredPromise}>{(data) => <div>{JSON.stringify(data)}</div>}</Await>
}
```

# CatchBoundary component

The `CatchBoundary` component is a component that catches errors thrown by its children, renders an error component and optionally calls the `onCatch` callback. It also accepts a `getResetKey` function that can be used to declaratively reset the component's state when the key changes.

## CatchBoundary props

The `CatchBoundary` component accepts the following props:

### `props.getResetKey` prop

- Type: `() => string`
- Required
- A function that returns a string that will be used to reset the component's state when the key changes.

### `props.children` prop

- Type: `React.ReactNode`
- Required
- The component's children to render when there is no error

### `props.errorComponent` prop

- Type: `React.ReactNode`
- Optional - [`default: ErrorComponent`](../errorComponentComponent.md)
- The component to render when there is an error.

### `props.onCatch` prop

- Type: `(error: any) => void`
- Optional
- A callback that will be called with the error that was thrown by the component's children.

## CatchBoundary returns

- Returns the component's children if there is no error.
- Returns the `errorComponent` if there is an error.

## Examples

```tsx
import { CatchBoundary } from '@tanstack/react-router'

function Component() {
  return (
    <CatchBoundary getResetKey={() => 'reset'} onCatch={(error) => console.error(error)}>
      <div>My Component</div>
    </CatchBoundary>
  )
}
```

# CatchNotFound Component

The `CatchNotFound` component is a component that catches not-found errors thrown by its children, renders a fallback component and optionally calls the `onCatch` callback. It resets when the pathname changes.

## CatchNotFound props

The `CatchNotFound` component accepts the following props:

### `props.children` prop

- Type: `React.ReactNode`
- Required
- The component's children to render when there is no error

### `props.fallback` prop

- Type: `(error: NotFoundError) => React.ReactElement`
- Optional
- The component to render when there is an error

### `props.onCatch` prop

- Type: `(error: any) => void`
- Optional
- A callback that will be called with the error that was thrown by the component's children

## CatchNotFound returns

- Returns the component's children if there is no error.
- Returns the `fallback` if there is an error.

## Examples

```tsx
import { CatchNotFound } from '@tanstack/react-router'

function Component() {
  return (
    <CatchNotFound fallback={(error) => <p>Not found error! {JSON.stringify(error)}</p>}>
      <ComponentThatMightThrowANotFoundError />
    </CatchNotFound>
  )
}
```

# ClientOnly Component

The `ClientOnly` component is used to render a components only in the client, without breaking the server-side rendering due to hydration errors. It accepts a `fallback` prop that will be rendered if the JS is not yet loaded in the client.

## Props

The `ClientOnly` component accepts the following props:

### `props.fallback` prop

The fallback component to render if the JS is not yet loaded in the client.

### `props.children` prop

The component to render if the JS is loaded in the client.

## Returns

- Returns the component's children if the JS is loaded in the client.
- Returns the `fallback` component if the JS is not yet loaded in the client.

## Examples

```tsx
// src/routes/dashboard.tsx
import { ClientOnly, createFileRoute } from '@tanstack/react-router'
import { Charts, FallbackCharts } from './charts-that-break-server-side-rendering'

export const Route = createFileRoute('/dashboard')({
  component: Dashboard,
  // ... other route options
})

function Dashboard() {
  return (
    <div>
      <p>Dashboard</p>
      <ClientOnly fallback={<FallbackCharts />}>
        <Charts />
      </ClientOnly>
    </div>
  )
}
```

# createFileRoute function

The `createFileRoute` function is a factory that can be used to create a file-based route instance. This route instance can then be used to automatically generate a route tree with the `tsr generate` and `tsr watch` commands.

## createFileRoute options

The `createFileRoute` function accepts a single argument of type `string` that represents the `path` of the file that the route will be generated from.

### `path` option

- Type: `string` literal
- Required, but **automatically inserted and updated by the `tsr generate` and `tsr watch` commands**
- The full path of the file that the route will be generated from

## createFileRoute returns

A new function that accepts a single argument of type [`RouteOptions`](../RouteOptionsType.md) that will be used to configure the file [`Route`](../RouteType.md) instance.

> ⚠️ Note: For `tsr generate` and `tsr watch` to work properly, the file route instance must be exported from the file using the `Route` identifier.

## Examples

```tsx
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/')({
  loader: () => {
    return 'Hello World'
  },
  component: IndexComponent,
})

function IndexComponent() {
  const data = Route.useLoaderData()
  return <div>{data}</div>
}
```

# createLazyFileRoute function

The `createLazyFileRoute` function is used for creating a partial file-based route route instance that is lazily loaded when matched. This route instance can only be used to configure the [non-critical properties](../../../guide/code-splitting.md#how-does-tanstack-router-split-code) of the route, such as `component`, `pendingComponent`, `errorComponent`, and the `notFoundComponent`.

## createLazyFileRoute options

The `createLazyFileRoute` function accepts a single argument of type `string` that represents the `path` of the file that the route will be generated from.

### `path`

- Type: `string`
- Required, but **automatically inserted and updated by the `tsr generate` and `tsr watch` commands**
- The full path of the file that the route will be generated from.

### createLazyFileRoute returns

A new function that accepts a single argument of partial of the type [`RouteOptions`](../RouteOptionsType.md) that will be used to configure the file [`Route`](../RouteType.md) instance.

- Type:

```tsx
Pick<RouteOptions, 'component' | 'pendingComponent' | 'errorComponent' | 'notFoundComponent'>
```

- [`RouteOptions`](../RouteOptionsType.md)

> ⚠️ Note: For `tsr generate` and `tsr watch` to work properly, the file route instance must be exported from the file using the `Route` identifier.

### Examples

```tsx
import { createLazyFileRoute } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/')({
  component: IndexComponent,
})

function IndexComponent() {
  const data = Route.useLoaderData()
  return <div>{data}</div>
}
```

# createLazyRoute function

The `createLazyRoute` function is used for creating a partial code-based route route instance that is lazily loaded when matched. This route instance can only be used to configure the [non-critical properties](../../../guide/code-splitting.md#how-does-tanstack-router-split-code) of the route, such as `component`, `pendingComponent`, `errorComponent`, and the `notFoundComponent`.

## createLazyRoute options

The `createLazyRoute` function accepts a single argument of type `string` that represents the `id` of the route.

### `id`

- Type: `string`
- Required
- The route id of the route.

### createLazyRoute returns

A new function that accepts a single argument of partial of the type [`RouteOptions`](../RouteOptionsType.md) that will be used to configure the file [`Route`](../RouteType.md) instance.

- Type:

```tsx
Pick<RouteOptions, 'component' | 'pendingComponent' | 'errorComponent' | 'notFoundComponent'>
```

- [`RouteOptions`](../RouteOptionsType.md)

> ⚠️ Note: This route instance must be manually lazily loaded against its critical route instance using the `lazy` method returned by the `createRoute` function.

### Examples

```tsx
// src/route-pages/index.tsx
import { createLazyRoute } from '@tanstack/react-router'

export const Route = createLazyRoute('/')({
  component: IndexComponent,
})

function IndexComponent() {
  const data = Route.useLoaderData()
  return <div>{data}</div>
}

// src/routeTree.tsx
import { createRootRouteWithContext, createRoute, Outlet } from '@tanstack/react-router'

interface MyRouterContext {
  foo: string
}

const rootRoute = createRootRouteWithContext<MyRouterContext>()({
  component: () => <Outlet />,
})

const indexRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: '/',
}).lazy(() => import('./route-pages/index').then((d) => d.Route))

export const routeTree = rootRoute.addChildren([indexRoute])
```

# createRootRoute function

The `createRootRoute` function returns a new root route instance. A root route instance can then be used to create a route-tree.

## createRootRoute options

The options that will be used to configure the root route instance.

- Type:

```tsx
Omit<RouteOptions, 'path' | 'id' | 'getParentRoute' | 'caseSensitive' | 'parseParams' | 'stringifyParams'>
```

- [`RouteOptions`](../RouteOptionsType.md)
- Optional

## createRootRoute returns

A new [`Route`](../RouteType.md) instance.

## Examples

```tsx
import { createRootRoute, createRouter, Outlet } from '@tanstack/react-router'

const rootRoute = createRootRoute({
  component: () => <Outlet />,
  // ... root route options
})

const routeTree = rootRoute.addChildren([
  // ... other routes
])

const router = createRouter({
  routeTree,
})
```

# createRootRouteWithContext function

The `createRootRouteWithContext` function is a helper function that can be used to create a root route instance that requires a context type to be fulfilled when the router is created.

## createRootRouteWithContext generics

The `createRootRouteWithContext` function accepts a single generic argument:

### `TRouterContext` generic

- Type: `TRouterContext`
- Optional, **but recommended**.
- The context type that will be required to be fulfilled when the router is created

## createRootRouteWithContext returns

- A factory function that can be used to create a new [`createRootRoute`](../createRootRouteFunction.md) instance.
- It accepts a single argument, the same as the [`createRootRoute`](../createRootRouteFunction.md) function.

## Examples

```tsx
import { createRootRouteWithContext, createRouter } from '@tanstack/react-router'
import { QueryClient } from '@tanstack/react-query'

interface MyRouterContext {
  queryClient: QueryClient
}

const rootRoute = createRootRouteWithContext<MyRouterContext>()({
  component: () => <Outlet />,
  // ... root route options
})

const routeTree = rootRoute.addChildren([
  // ... other routes
])

const queryClient = new QueryClient()

const router = createRouter({
  routeTree,
  context: {
    queryClient,
  },
})
```

# createRoute function

The `createRoute` function implements returns a [`Route`](../RouteType.md) instance. A route instance can then be passed to a root route's children to create a route tree, which is then passed to the router.

## createRoute options

- Type: [`RouteOptions`](../RouteOptionsType.md)
- Required
- The options that will be used to configure the route instance

## createRoute returns

A new [`Route`](../RouteType.md) instance.

## Examples

```tsx
import { createRoute } from '@tanstack/react-router'
import { rootRoute } from './__root'

const Route = createRoute({
  getParentRoute: () => rootRoute,
  path: '/',
  loader: () => {
    return 'Hello World'
  },
  component: IndexComponent,
})

function IndexComponent() {
  const data = Route.useLoaderData()
  return <div>{data}</div>
}
```

# createRouteMask function

The `createRouteMask` function is a helper function that can be used to create a route mask configuration that can be passed to the `RouterOptions.routeMasks` option.

## createRouteMask options

- Type: [`RouteMask`](../RouteMaskType.md)
- Required
- The options that will be used to configure the route mask

## createRouteMask returns

- A object with the type signature of [`RouteMask`](../RouteMaskType.md) that can be passed to the `RouterOptions.routeMasks` option.

## Examples

```tsx
import { createRouteMask, createRouter } from '@tanstack/react-router'

const photoModalToPhotoMask = createRouteMask({
  routeTree,
  from: '/photos/$photoId/modal',
  to: '/photos/$photoId',
  params: true,
})

// Set up a Router instance
const router = createRouter({
  routeTree,
  routeMasks: [photoModalToPhotoMask],
})
```

# createRouter function

The `createRouter` function accepts a [`RouterOptions`](../RouterOptionsType.md) object and creates a new [`Router`](../RouterClass.md) instance.

## createRouter options

- Type: [`RouterOptions`](../RouterOptionsType.md)
- Required
- The options that will be used to configure the router instance.

## createRouter returns

- An instance of the [`Router`](../RouterType.md).

## Examples

```tsx
import { createRouter, RouterProvider } from '@tanstack/react-router'
import { routeTree } from './routeTree.gen'

const router = createRouter({
  routeTree,
  defaultPreload: 'intent',
})

export default function App() {
  return <RouterProvider router={router} />
}
```

# DefaultGlobalNotFound component

The `DefaultGlobalNotFound` component is a component that renders "Not Found" on the root route when there is no other route that matches and a `notFoundComponent` is not provided.

## DefaultGlobalNotFound returns

```tsx
<p>Not Found</p>
```

# defer function

> [!CAUTION]
> You don't need to call `defer` manually anymore, Promises are handled automatically now.

The `defer` function wraps a promise with a deferred state object that can be used to inspect the promise's state. This deferred promise can then be passed to the [`useAwaited`](../useAwaitedHook.md) hook or the [`<Await>`](../awaitComponent.md) component for suspending until the promise is resolved or rejected.

The `defer` function accepts a single argument, the `promise` to wrap with a deferred state object.

## defer options

- Type: `Promise<T>`
- Required
- The promise to wrap with a deferred state object.

## defer returns

- A promise that can be passed to the [`useAwaited`](../useAwaitedHook.md) hook or the [`<Await>`](../awaitComponent.md) component.

## Examples

```tsx
import { defer } from '@tanstack/react-router'

const route = createRoute({
  loader: () => {
    const deferredPromise = defer(fetch('/api/data'))
    return { deferredPromise }
  },
  component: MyComponent,
})

function MyComponent() {
  const { deferredPromise } = Route.useLoaderData()

  const data = useAwaited({ promise: deferredPromise })

  // or

  return <Await promise={deferredPromise}>{(data) => <div>{JSON.stringify(data)}</div>}</Await>
}
```

# ErrorComponent component

The `ErrorComponent` component is a component that renders an error message and optionally the error's message.

## ErrorComponent props

The `ErrorComponent` component accepts the following props:

### `props.error` prop

- Type: `TError` (defaults to `Error`)
- The error that was thrown by the component's children

### `props.info` prop

- Type: `{ componentStack: string }`
- Optional
- Additional information about where the error was thrown, such as the React component stack trace.

### `props.reset` prop

- Type: `() => void`
- A function to programmatically reset the error state

## ErrorComponent returns

- Returns a formatted error message with the error's message if it exists.
- The error message can be toggled by clicking the "Show Error" button.
- By default, the error message will be shown in development.

# getRouteApi function

The `getRouteApi` function provides type-safe version of common hooks like `useParams`, `useSearch`, `useRouteContext`, `useNavigate`, `useLoaderData`, and `useLoaderDeps` that are pre-bound to a specific route ID and corresponding registered route types.

## getRouteApi options

The `getRouteApi` function accepts a single argument, a `routeId` string literal.

### `routeId` option

- Type: `string`
- Required
- The route ID to which the [`RouteApi`](../RouteApiClass.md) instance will be bound

## getRouteApi returns

- An instance of the [`RouteApi`](../RouteApiType.md) that is pre-bound to the route ID that the `getRouteApi` function was called with.

## Examples

```tsx
import { getRouteApi } from '@tanstack/react-router'

const routeApi = getRouteApi('/posts')

export function PostsPage() {
  const posts = routeApi.useLoaderData()
  // ...
}
```

# HistoryState interface

The `HistoryState` interface is an interface exported by the `history` package that describes the shape of the state object that can be used in conjunction with the `history` package and the `window.location` API.

You can extend this interface to add additional properties to the state object across your application.

```tsx
// src/main.tsx
declare module '@tanstack/react-router' {
  // ...

  interface HistoryState {
    additionalRequiredProperty: number
    additionalProperty?: string
  }
}
```

# isNotFound function

The `isNotFound` function can be used to determine if an object is a [`NotFoundError`](../NotFoundErrorType.md) object.

## isNotFound options

The `isNotFound` function accepts a single argument, an `input`.

### `input` option

- Type: `unknown`
- Required
- An object to check if it is a [`NotFoundError`](../NotFoundErrorType.md).

## isNotFound returns

- Type: `boolean`
- `true` if the object is a [`NotFoundError`](../NotFoundErrorType.md).
- `false` if the object is not a [`NotFoundError`](../NotFoundErrorType.md).

## Examples

```tsx
import { isNotFound } from '@tanstack/react-router'

function somewhere(obj: unknown) {
  if (isNotFound(obj)) {
    // ...
  }
}
```

# isRedirect function

The `isRedirect` function can be used to determine if an object is a redirect object.

## isRedirect options

The `isRedirect` function accepts a single argument, an `input`.

#### `input`

- Type: `unknown`
- Required
- An object to check if it is a redirect object

## isRedirect returns

- Type: `boolean`
- `true` if the object is a redirect object
- `false` if the object is not a redirect object

## Examples

```tsx
import { isRedirect } from '@tanstack/react-router'

function somewhere(obj: unknown) {
  if (isRedirect(obj)) {
    // ...
  }
}
```

# lazyRouteComponent function

> [!IMPORTANT]
> If you are using file-based routing, it's recommended to use the `createLazyFileRoute` function instead.

The `lazyRouteComponent` function can be used to create a one-off code-split route component that can be preloaded using a `component.preload()` method.

## lazyRouteComponent options

The `lazyRouteComponent` function accepts two arguments:

### `importer` option

- Type: `() => Promise<T>`
- Required
- A function that returns a promise that resolves to an object that contains the component to be loaded.

### `exportName` option

- Type: `string`
- Optional
- The name of the component to be loaded from the imported object. Defaults to `'default'`.

## lazyRouteComponent returns

- A `React.lazy` component that can be preloaded using a `component.preload()` method.

## Examples

```tsx
import { lazyRouteComponent } from '@tanstack/react-router'

const route = createRoute({
  path: '/posts/$postId',
  component: lazyRouteComponent(() => import('./Post')), // default export
})

// or

const route = createRoute({
  path: '/posts/$postId',
  component: lazyRouteComponent(
    () => import('./Post'),
    'PostByIdPageComponent' // named export
  ),
})
```

# Link component

The `Link` component is a component that can be used to create a link that can be used to navigate to a new location. This includes changes to the pathname, search params, hash, and location state.

## Link props

The `Link` component accepts the following props:

### `...props`

- Type: `LinkProps & React.RefAttributes<HTMLAnchorElement>`
- [`LinkProps`](../LinkPropsType.md)

## Link returns

An anchor element that can be used to navigate to a new location.

## Examples

```tsx
import { Link } from '@tanstack/react-router'

function Component() {
  return (
    <Link to="/somewhere/$somewhereId" params={{ somewhereId: 'baz' }} search={(prev) => ({ ...prev, foo: 'bar' })}>
      Click me
    </Link>
  )
}
```

# Link options

`linkOptions` is a function which type checks an object literal with the intention of being used for `Link`, `navigate` or `redirect`

## linkOptions props

The `linkOptions` accepts the following option:

### `...props`

- Type: `LinkProps & React.RefAttributes<HTMLAnchorElement>`
- [`LinkProps`](../LinkPropsType.md)

## `linkOptions` returns

An object literal with the exact type inferred from the input

## Examples

```tsx
const userLinkOptions = linkOptions({
  to: '/dashboard/users/user',
  search: {
    usersView: {
      sortBy: 'email',
      filterBy: 'filter',
    },
    userId: 0,
  },
})

function DashboardComponent() {
  return <Link {...userLinkOptions} />
}
```

# MatchRoute component

A component version of the `useMatchRoute` hook. It accepts the same options as the `useMatchRoute` with additional props to aid in conditional rendering.

## MatchRoute props

The `MatchRoute` component accepts the same options as the `useMatchRoute` hook with additional props to aid in conditional rendering.

### `...props` prop

- Type: [`UseMatchRouteOptions`](../UseMatchRouteOptionsType.md)

### `children` prop

- Optional
- `React.ReactNode`
  - The component that will be rendered if the route is matched.
- `((params: TParams | false) => React.ReactNode)`
  - A function that will be called with the matched route's params or `false` if no route was matched. This can be useful for components that need to always render, but render different props based on a route match or not.

## MatchRoute returns

Either the `children` prop or the return value of the `children` function.

## Examples

```tsx
import { MatchRoute } from '@tanstack/react-router'

function Component() {
  return (
    <div>
      <MatchRoute to="/posts/$postId" params={{ postId: '123' }} pending>
        {(match) => <Spinner show={!!match} wait="delay-50" />}
      </MatchRoute>
    </div>
  )
}
```

# Navigate component

The `Navigate` component is a component that can be used to navigate to a new location when rendered. This includes changes to the pathname, search params, hash, and location state. The underlying navigation will happen inside of a `useEffect` hook when successfully rendered.

## Navigate props

The `Navigate` component accepts the following props:

### `...options`

- Type: [`NavigateOptions`](../NavigateOptionsType.md)

## Navigate returns

- `null`

# notFound function

The `notFound` function returns a new `NotFoundError` object that can be either returned or thrown from places like a Route's `beforeLoad` or `loader` callbacks to trigger the `notFoundComponent`.

## notFound options

The `notFound` function accepts a single optional argument, the `options` to create the not-found error object.

- Type: [`Partial<NotFoundError>`](../NotFoundErrorType.md)
- Optional

## notFound returns

- If the `throw` property is `true` in the `options` object, the `NotFoundError` object will be thrown from within the function call.
- If the `throw` property is `false | undefined` in the `options` object, the `NotFoundError` object will be returned.

## Examples

```tsx
import { notFound, createFileRoute, rootRouteId } from '@tanstack/react-router'

const Route = new createFileRoute('/posts/$postId')({
  // throwing a not-found object
  loader: ({ context: { post } }) => {
    if (!post) {
      throw notFound()
    }
  },
  // or if you want to show a not-found on the whole page
  loader: ({ context: { team } }) => {
    if (!team) {
      throw notFound({ routeId: rootRouteId })
    }
  },
  // ... other route options
})
```

# Outlet component

The `Outlet` component is a component that can be used to render the next child route of a parent route.

## Outlet props

The `Outlet` component does not accept any props.

## Outlet returns

- If matched, the child route match's `component`/`errorComponent`/`pendingComponent`/`notFoundComponent`.
- If not matched, `null`.

# redirect function

The `redirect` function returns a new `Redirect` object that can be either returned or thrown from places like a Route's `beforeLoad` or `loader` callbacks to trigger _redirect_ to a new location.

## redirect options

The `redirect` function accepts a single argument, the `options` to determine the redirect behavior.

- Type: [`Redirect`](../RedirectType.md)
- Required

## redirect returns

- If the `throw` property is `true` in the `options` object, the `Redirect` object will be thrown from within the function call.
- If the `throw` property is `false | undefined` in the `options` object, the `Redirect` object will be returned.

## Examples

```tsx
import { redirect } from '@tanstack/react-router'

const route = createRoute({
  // throwing an internal redirect object using 'to' property
  loader: () => {
    if (!user) {
      throw redirect({
        to: '/login',
      })
    }
  },
  // throwing an external redirect object using 'href' property
  loader: () => {
    if (needsExternalAuth) {
      throw redirect({
        href: 'https://authprovider.com/login',
      })
    }
  },
  // or forcing `redirect` to throw itself
  loader: () => {
    if (!user) {
      redirect({
        to: '/login',
        throw: true,
      })
    }
  },
  // ... other route options
})
```

# Search middleware to retain search params

`retainSearchParams` is a search middleware that allows to keep search params.

## retainSearchParams props

The `retainSearchParams` either accepts `true` or a list of keys of those search params that shall be retained.
If `true` is passed in, all search params will be retained.

## Examples

```tsx
import { z } from 'zod'
import { createRootRoute, retainSearchParams } from '@tanstack/react-router'
import { zodValidator } from '@tanstack/zod-adapter'

const searchSchema = z.object({
  rootValue: z.string().optional(),
})

export const Route = createRootRoute({
  validateSearch: zodValidator(searchSchema),
  search: {
    middlewares: [retainSearchParams(['rootValue'])],
  },
})
```

```tsx
import { z } from 'zod'
import { createFileRoute, retainSearchParams } from '@tanstack/react-router'
import { zodValidator } from '@tanstack/zod-adapter'

const searchSchema = z.object({
  one: z.string().optional(),
  two: z.string().optional(),
})

export const Route = createFileRoute('/')({
  validateSearch: zodValidator(searchSchema),
  search: {
    middlewares: [retainSearchParams(true)],
  },
})
```

# rootRouteWithContext function

> [!CAUTION]
> This function is deprecated and will be removed in the next major version of TanStack Router.
> Please use the [`createRootRouteWithContext`](../createRootRouteWithContextFunction.md) function instead.

The `rootRouteWithContext` function is a helper function that can be used to create a root route instance that requires a context type to be fulfilled when the router is created.

## rootRouteWithContext generics

The `rootRouteWithContext` function accepts a single generic argument:

### `TRouterContext` generic

- Type: `TRouterContext`
- Optional, **but recommended**.
- The context type that will be required to be fulfilled when the router is created

## rootRouteWithContext returns

- A factory function that can be used to create a new [`createRootRoute`](../createRootRouteFunction.md) instance.
- It accepts a single argument, the same as the [`createRootRoute`](../createRootRouteFunction.md) function.

## Examples

```tsx
import { rootRouteWithContext, createRouter } from '@tanstack/react-router'
import { QueryClient } from '@tanstack/react-query'

interface MyRouterContext {
  queryClient: QueryClient
}

const rootRoute = rootRouteWithContext<MyRouterContext>()({
  component: () => <Outlet />,
  // ... root route options
})

const routeTree = rootRoute.addChildren([
  // ... other routes
])

const queryClient = new QueryClient()

const router = createRouter({
  routeTree,
  context: {
    queryClient,
  },
})
```

# Search middleware to strip search params

`stripSearchParams` is a search middleware that allows to remove search params.

## stripSearchParams props

`stripSearchParams` accepts one of the following inputs:

- `true`: if the search schema has no required params, `true` can be used to strip all search params
- a list of keys of those search params that shall be removed; only keys of optional search params are allowed.
- an object that conforms to the partial input search schema. The search params are compared against the values of this object; if the value is deeply equal, it will be removed. This is especially useful to strip out default search params.

## Examples

```tsx
import { z } from 'zod'
import { createFileRoute, stripSearchParams } from '@tanstack/react-router'
import { zodValidator } from '@tanstack/zod-adapter'

const defaultValues = {
  one: 'abc',
  two: 'xyz',
}

const searchSchema = z.object({
  one: z.string().default(defaultValues.one),
  two: z.string().default(defaultValues.two),
})

export const Route = createFileRoute('/')({
  validateSearch: zodValidator(searchSchema),
  search: {
    // strip default values
    middlewares: [stripSearchParams(defaultValues)],
  },
})
```

```tsx
import { z } from 'zod'
import { createRootRoute, stripSearchParams } from '@tanstack/react-router'
import { zodValidator } from '@tanstack/zod-adapter'

const searchSchema = z.object({
  hello: z.string().default('world'),
  requiredParam: z.string(),
})

export const Route = createRootRoute({
  validateSearch: zodValidator(searchSchema),
  search: {
    // always remove `hello`
    middlewares: [stripSearchParams(['hello'])],
  },
})
```

```tsx
import { z } from 'zod'
import { createFileRoute, stripSearchParams } from '@tanstack/react-router'
import { zodValidator } from '@tanstack/zod-adapter'

const searchSchema = z.object({
  one: z.string().default('abc'),
  two: z.string().default('xyz'),
})

export const Route = createFileRoute('/')({
  validateSearch: zodValidator(searchSchema),
  search: {
    // remove all search params
    middlewares: [stripSearchParams(true)],
  },
})
```

# useAwaited hook

The `useAwaited` method is a hook that suspends until the provided promise is resolved or rejected.

## useAwaited options

The `useAwaited` hook accepts a single argument, an `options` object.

### `options.promise` option

- Type: `Promise<T>`
- Required
- The deferred promise to await.

## useAwaited returns

- Throws an error if the promise is rejected.
- Suspends (throws a promise) if the promise is pending.
- Returns the resolved value of a deferred promise if the promise is resolved.

## Examples

```tsx
import { useAwaited } from '@tanstack/react-router'

function Component() {
  const { deferredPromise } = route.useLoaderData()

  const data = useAwaited({ promise: myDeferredPromise })
  // ...
}
```

# useBlocker hook

The `useBlocker` method is a hook that [blocks navigation](../../../guide/navigation-blocking.md) when a condition is met.

> ⚠️ The following new `useBlocker` API is currently _experimental_.

## useBlocker options

The `useBlocker` hook accepts a single _required_ argument, an option object:

### `options.shouldBlockFn` option

- Required
- Type: `ShouldBlockFn`
- This function should return a `boolean` or a `Promise<boolean>` that tells the blocker if it should block the current navigation
- The function has the argument of type `ShouldBlockFnArgs` passed to it, which tells you information about the current and next route and the action performed
- Think of this function as telling the router if it should block the navigation, so returning `true` mean that it should block the navigation and `false` meaning that it should be allowed

```ts
interface ShouldBlockFnLocation<...> {
  routeId: TRouteId
  fullPath: TFullPath
  pathname: string
  params: TAllParams
  search: TFullSearchSchema
}

type ShouldBlockFnArgs = {
  current: ShouldBlockFnLocation
  next: ShouldBlockFnLocation
  action: HistoryAction
}
```

### `options.disabled` option

- Optional - defaults to `false`
- Type: `boolean`
- Specifies if the blocker should be entirely disabled or not

### `options.enableBeforeUnload` option

- Optional - defaults to `true`
- Type: `boolean | (() => boolean)`
- Tell the blocker to sometimes or always block the browser `beforeUnload` event or not

### `options.withResolver` option

- Optional - defaults to `false`
- Type: `boolean`
- Specify if the resolver returned by the hook should be used or whether your `shouldBlockFn` function itself resolves the blocking

### `options.blockerFn` option (⚠️ deprecated)

- Optional
- Type: `BlockerFn`
- The function that returns a `boolean` or `Promise<boolean>` indicating whether to allow navigation.

### `options.condition` option (⚠️ deprecated)

- Optional - defaults to `true`
- Type: `boolean`
- A navigation attempt is blocked when this condition is `true`.

## useBlocker returns

An object with the controls to allow manual blocking and unblocking of navigation.

- `status` - A string literal that can be either `'blocked'` or `'idle'`
- `next` - When status is `blocked`, a type narrrowable object that contains information about the next location
- `current` - When status is `blocked`, a type narrrowable object that contains information about the current location
- `action` - When status is `blocked`, a `HistoryAction` string that shows the action that triggered the navigation
- `proceed` - When status is `blocked`, a function that allows navigation to continue
- `reset` - When status is `blocked`, a function that cancels navigation (`status` will be reset to `'idle'`)

or

`void` when `withResolver` is `false`

## Examples

Two common use cases for the `useBlocker` hook are:

### Basic usage

```tsx
import { useBlocker } from '@tanstack/react-router'

function MyComponent() {
  const [formIsDirty, setFormIsDirty] = useState(false)

  useBlocker({
    shouldBlockFn: () => formIsDirty,
  })

  // ...
}
```

### Custom UI

```tsx
import { useBlocker } from '@tanstack/react-router'

function MyComponent() {
  const [formIsDirty, setFormIsDirty] = useState(false)

  const { proceed, reset, status, next } = useBlocker({
    shouldBlockFn: () => formIsDirty,
    withResolver: true,
  })

  // ...

  return (
    <>
      {/* ... */}
      {status === 'blocked' && (
        <div>
          <p>You are navigating to {next.pathname}</p>
          <p>Are you sure you want to leave?</p>
          <button onClick={proceed}>Yes</button>
          <button onClick={reset}>No</button>
        </div>
      )}
    </>
}
```

### Conditional blocking

```tsx
import { useBlocker } from '@tanstack/react-router'

function MyComponent() {
  const { proceed, reset, status } = useBlocker({
    shouldBlockFn: ({ next }) => {
      return !next.pathname.includes('step/')
    },
    withResolver: true,
  })

  // ...

  return (
    <>
      {/* ... */}
      {status === 'blocked' && (
        <div>
          <p>Are you sure you want to leave?</p>
          <button onClick={proceed}>Yes</button>
          <button onClick={reset}>No</button>
        </div>
      )}
    </>
  )
}
```

### Without resolver

```tsx
import { useBlocker } from '@tanstack/react-router'

function MyComponent() {
  const [formIsDirty, setFormIsDirty] = useState(false)

  useBlocker({
    shouldBlockFn: ({ next }) => {
      if (next.pathname.includes('step/')) {
        return false
      }

      const shouldLeave = confirm('Are you sure you want to leave?')
      return !shouldLeave
    },
  })

  // ...
}
```

### Type narrowing

```tsx
import { useBlocker } from '@tanstack/react-router'

function MyComponent() {
  const [formIsDirty, setFormIsDirty] = useState(false)

  // block going from editor-1 to /foo/123?hello=world
  const { proceed, reset, status } = useBlocker({
    shouldBlockFn: ({ current, next }) => {
      if (
        current.routeId === '/editor-1' &&
        next.fullPath === '/foo/$id' &&
        next.params.id === '123' &&
        next.search.hello === 'world'
      ) {
        return true
      }
      return false
    },
    enableBeforeUnload: false,
    withResolver: true,
  })

  // ...
}
```

# useCanGoBack hook

The `useCanGoBack` hook returns a boolean representing if the router history can safely go back without exiting the application.

> ⚠️ The following new `useCanGoBack` API is currently _experimental_.

## useCanGoBack returns

- If the router history is not at index `0`, `true`.
- If the router history is at index `0`, `false`.

## Limitations

The router history index is reset after a navigation with [`reloadDocument`](../NavigateOptionsType.md#reloaddocument) set as `true`. This causes the router history to consider the new location as the initial one and will cause `useCanGoBack` to return `false`.

## Examples

### Showing a back button

```tsx
import { useRouter, useCanGoBack } from '@tanstack/react-router'

function Component() {
  const router = useRouter()
  const canGoBack = useCanGoBack()

  return (
    <div>
      {canGoBack ? <button onClick={() => router.history.back()}>Go back</button> : null}

      {/* ... */}
    </div>
  )
}
```

# useChildMatches hook

The `useChildMatches` hook returns all of the child [`RouteMatch`](../RouteMatchType.md) objects from the closest match down to the leaf-most match. **It does not include the current match, which can be obtained using the `useMatch` hook.**

> [!IMPORTANT]
> If the router has pending matches and they are showing their pending component fallbacks, `router.state.pendingMatches` will used instead of `router.state.matches`.

## useChildMatches options

The `useChildMatches` hook accepts a single _optional_ argument, an `options` object.

### `opts.select` option

- Optional
- `(matches: RouteMatch[]) => TSelected`
- If supplied, this function will be called with the route matches and the return value will be returned from `useChildMatches`. This value will also be used to determine if the hook should re-render its parent component using shallow equality checks.

### `opts.structuralSharing` option

- Type: `boolean`
- Optional
- Configures whether structural sharing is enabled for the value returned by `select`.
- See the [Render Optimizations guide](../../../guide/render-optimizations.md) for more information.

## useChildMatches returns

- If a `select` function is provided, the return value of the `select` function.
- If no `select` function is provided, an array of [`RouteMatch`](../RouteMatchType.md) objects.

## Examples

```tsx
import { useChildMatches } from '@tanstack/react-router'

function Component() {
  const childMatches = useChildMatches()
  // ...
}
```

# useLinkProps hook

The `useLinkProps` hook that takes an object as its argument and returns a `React.AnchorHTMLAttributes<HTMLAnchorElement>` props object. These props can then be safely applied to an anchor element to create a link that can be used to navigate to the new location. This includes changes to the pathname, search params, hash, and location state.

## useLinkProps options

```tsx
type UseLinkPropsOptions = ActiveLinkOptions & React.AnchorHTMLAttributes<HTMLAnchorElement>
```

- [`ActiveLinkOptions`](../ActiveLinkOptionsType.md)
- The `useLinkProps` options are used to build a [`LinkProps`](../LinkPropsType.md) object.
- It also extends the `React.AnchorHTMLAttributes<HTMLAnchorElement>` type, so that any additional props that are passed to the `useLinkProps` hook will be merged with the [`LinkProps`](../LinkPropsType.md) object.

## useLinkProps returns

- A `React.AnchorHTMLAttributes<HTMLAnchorElement>` object that can be applied to an anchor element to create a link that can be used to navigate to the new location

# useLoaderData hook

The `useLoaderData` hook returns the loader data from the closest [`RouteMatch`](../RouteMatchType.md) in the component tree.

## useLoaderData options

The `useLoaderData` hook accepts an `options` object.

### `opts.from` option

- Type: `string`
- The route id of the closest parent match
- Optional, but recommended for full type safety.
- If `opts.strict` is `true`, TypeScript will warn for this option if it is not provided.
- If `opts.strict` is `false`, TypeScript will provided loosened types for the returned loader data.

### `opts.strict` option

- Type: `boolean`
- Optional - `default: true`
- If `false`, the `opts.from` option will be ignored and types will be loosened to to reflect the shared types of all possible loader data.

### `opts.select` option

- Optional
- `(loaderData: TLoaderData) => TSelected`
- If supplied, this function will be called with the loader data and the return value will be returned from `useLoaderData`. This value will also be used to determine if the hook should re-render its parent component using shallow equality checks.

### `opts.structuralSharing` option

- Type: `boolean`
- Optional
- Configures whether structural sharing is enabled for the value returned by `select`.
- See the [Render Optimizations guide](../../../guide/render-optimizations.md) for more information.

## useLoaderData returns

- If a `select` function is provided, the return value of the `select` function.
- If no `select` function is provided, the loader data or a loosened version of the loader data if `opts.strict` is `false`.

## Examples

```tsx
import { useLoaderData } from '@tanstack/react-router'

function Component() {
  const loaderData = useLoaderData({ from: '/posts/$postId' })
  //     ^? { postId: string, body: string, ... }
  // ...
}
```

# useLoaderDeps hook

The `useLoaderDeps` hook is a hook that returns an object with the dependencies that are used to trigger the `loader` for a given route.

## useLoaderDepsHook options

The `useLoaderDepsHook` hook accepts an `options` object.

### `opts.from` option

- Type: `string`
- Required
- The RouteID or path to get the loader dependencies from.

### `opts.select` option

- Type: `(deps: TLoaderDeps) => TSelected`
- Optional
- If supplied, this function will be called with the loader dependencies object and the return value will be returned from `useLoaderDeps`.

### `opts.structuralSharing` option

- Type: `boolean`
- Optional
- Configures whether structural sharing is enabled for the value returned by `select`.
- See the [Render Optimizations guide](../../../guide/render-optimizations.md) for more information.

## useLoaderDeps returns

- An object of the loader dependencies or `TSelected` if a `select` function is provided.

## Examples

```tsx
import { useLoaderDeps } from '@tanstack/react-router'

const routeApi = getRouteApi('/posts/$postId')

function Component() {
  const deps = useLoaderDeps({ from: '/posts/$postId' })

  // OR

  const routeDeps = routeApi.useLoaderDeps()

  // OR

  const postId = useLoaderDeps({
    from: '/posts',
    select: (deps) => deps.view,
  })

  // ...
}
```

# useLocation hook

The `useLocation` method is a hook that returns the current [`location`](../ParsedLocationType.md) object. This hook is useful for when you want to perform some side effect whenever the current location changes.

## useLocation options

The `useLocation` hook accepts an optional `options` object.

### `opts.select` option

- Type: `(state: ParsedLocationType) => TSelected`
- Optional
- If supplied, this function will be called with the [`location`](../ParsedLocationType.md) object and the return value will be returned from `useLocation`.

## useLocation returns

- The current [`location`](../ParsedLocationType.md) object or `TSelected` if a `select` function is provided.

## Examples

```tsx
import { useLocation } from '@tanstack/react-router'

function Component() {
  const location = useLocation()
  //    ^ ParsedLocation

  // OR

  const pathname = useLocation({
    select: (location) => location.pathname,
  })
  //    ^ string

  // ...
}
```

# useMatch hook

The `useMatch` hook returns a [`RouteMatch`](../RouteMatchType.md) in the component tree. The raw route match contains all of the information about a route match in the router and also powers many other hooks under the hood like `useParams`, `useLoaderData`, `useRouteContext`, and `useSearch`.

## useMatch options

The `useMatch` hook accepts a single argument, an `options` object.

### `opts.from` option

- Type: `string`
- The route id of a match
- Optional, but recommended for full type safety.
- If `opts.strict` is `true`, `from` is required and TypeScript will warn for this option if it is not provided.
- If `opts.strict` is `false`, `from` must not be set and TypeScript will provided loosened types for the returned [`RouteMatch`](../RouteMatchType.md).

### `opts.strict` option

- Type: `boolean`
- Optional
- `default: true`
- If `false`, the `opts.from` must not be set and types will be loosened to `Partial<RouteMatch>` to reflect the shared types of all matches.

### `opts.select` option

- Optional
- `(match: RouteMatch) => TSelected`
- If supplied, this function will be called with the route match and the return value will be returned from `useMatch`. This value will also be used to determine if the hook should re-render its parent component using shallow equality checks.

### `opts.structuralSharing` option

- Type: `boolean`
- Optional
- Configures whether structural sharing is enabled for the value returned by `select`.
- See the [Render Optimizations guide](../../../guide/render-optimizations.md) for more information.

### `opts.shouldThrow` option

- Type: `boolean`
- Optional
- `default: true`
- If `false`,`useMatch` will not throw an invariant exception in case a match was not found in the currently rendered matches; in this case, it will return `undefined`.

## useMatch returns

- If a `select` function is provided, the return value of the `select` function.
- If no `select` function is provided, the [`RouteMatch`](../RouteMatchType.md) object or a loosened version of the `RouteMatch` object if `opts.strict` is `false`.

## Examples

### Accessing a route match

```tsx
import { useMatch } from '@tanstack/react-router'

function Component() {
  const match = useMatch({ from: '/posts/$postId' })
  //     ^? strict match for RouteMatch
  // ...
}
```

### Accessing the root route's match

```tsx
import {
  useMatch,
  rootRouteId, // <<<< use this token!
} from '@tanstack/react-router'

function Component() {
  const match = useMatch({ from: rootRouteId })
  //     ^? strict match for RouteMatch
  // ...
}
```

### Checking if a specific route is currently rendered

```tsx
import { useMatch } from '@tanstack/react-router'

function Component() {
  const match = useMatch({ from: '/posts', shouldThrow: false })
  //     ^? RouteMatch | undefined
  if (match !== undefined) {
    // ...
  }
}
```

# useMatchRoute hook

The `useMatchRoute` hook is a hook that returns a `matchRoute` function that can be used to match a route against either the current or pending location.

## useMatchRoute returns

- A `matchRoute` function that can be used to match a route against either the current or pending location.

## matchRoute function

The `matchRoute` function is a function that can be used to match a route against either the current or pending location.

### matchRoute function options

The `matchRoute` function accepts a single argument, an `options` object.

- Type: [`UseMatchRouteOptions`](../UseMatchRouteOptionsType.md)

### matchRoute function returns

- The matched route's params or `false` if no route was matched

## Examples

```tsx
import { useMatchRoute } from '@tanstack/react-router'

// Current location: /posts/123
function Component() {
  const matchRoute = useMatchRoute()
  const params = matchRoute({ to: '/posts/$postId' })
  //    ^ { postId: '123' }
}

// Current location: /posts/123
function Component() {
  const matchRoute = useMatchRoute()
  const params = matchRoute({ to: '/posts' })
  //    ^ false
}

// Current location: /posts/123
function Component() {
  const matchRoute = useMatchRoute()
  const params = matchRoute({ to: '/posts', fuzzy: true })
  //    ^ {}
}

// Current location: /posts
// Pending location: /posts/123
function Component() {
  const matchRoute = useMatchRoute()
  const params = matchRoute({ to: '/posts/$postId', pending: true })
  //    ^ { postId: '123' }
}

// Current location: /posts/123/foo/456
function Component() {
  const matchRoute = useMatchRoute()
  const params = matchRoute({ to: '/posts/$postId/foo/$fooId' })
  //    ^ { postId: '123', fooId: '456' }
}

// Current location: /posts/123/foo/456
function Component() {
  const matchRoute = useMatchRoute()
  const params = matchRoute({
    to: '/posts/$postId/foo/$fooId',
    params: { postId: '123' },
  })
  //    ^ { postId: '123', fooId: '456' }
}

// Current location: /posts/123/foo/456
function Component() {
  const matchRoute = useMatchRoute()
  const params = matchRoute({
    to: '/posts/$postId/foo/$fooId',
    params: { postId: '789' },
  })
  //    ^ false
}

// Current location: /posts/123/foo/456
function Component() {
  const matchRoute = useMatchRoute()
  const params = matchRoute({
    to: '/posts/$postId/foo/$fooId',
    params: { fooId: '456' },
  })
  //    ^ { postId: '123', fooId: '456' }
}

// Current location: /posts/123/foo/456
function Component() {
  const matchRoute = useMatchRoute()
  const params = matchRoute({
    to: '/posts/$postId/foo/$fooId',
    params: { postId: '123', fooId: '456' },
  })
  //    ^ { postId: '123', fooId: '456' }
}

// Current location: /posts/123/foo/456
function Component() {
  const matchRoute = useMatchRoute()
  const params = matchRoute({
    to: '/posts/$postId/foo/$fooId',
    params: { postId: '789', fooId: '456' },
  })
  //    ^ false
}
```

# useMatches hook

The `useMatches` hook returns all of the [`RouteMatch`](../RouteMatchType.md) objects from the router **regardless of its callers position in the React component tree**.

> [!TIP]
> If you only want the parent or child matches, then you can use the [`useParentMatches`](../useParentMatchesHook.md) or the [`useChildMatches`](../useChildMatchesHook.md) based on the selection you need.

## useMatches options

The `useMatches` hook accepts a single _optional_ argument, an `options` object.

### `opts.select` option

- Optional
- `(matches: RouteMatch[]) => TSelected`
- If supplied, this function will be called with the route matches and the return value will be returned from `useMatches`. This value will also be used to determine if the hook should re-render its parent component using shallow equality checks.

### `opts.structuralSharing` option

- Type: `boolean`
- Optional
- Configures whether structural sharing is enabled for the value returned by `select`.
- See the [Render Optimizations guide](../../../guide/render-optimizations.md) for more information.

## useMatches returns

- If a `select` function is provided, the return value of the `select` function.
- If no `select` function is provided, an array of [`RouteMatch`](../RouteMatchType.md) objects.

## Examples

```tsx
import { useMatches } from '@tanstack/react-router'

function Component() {
  const matches = useMatches()
  //     ^? [RouteMatch, RouteMatch, ...]
  // ...
}
```

# useNavigate hook

The `useNavigate` hook is a hook that returns a `navigate` function that can be used to navigate to a new location. This includes changes to the pathname, search params, hash, and location state.

## useNavigate options

The `useNavigate` hook accepts a single argument, an `options` object.

### `opts.from` option

- Type: `string`
- Optional
- Description: The location to navigate from. This is useful when you want to navigate to a new location from a specific location, rather than the current location.

## useNavigate returns

- A `navigate` function that can be used to navigate to a new location.

## navigate function

The `navigate` function is a function that can be used to navigate to a new location.

### navigate function options

The `navigate` function accepts a single argument, an `options` object.

- Type: [`NavigateOptions`](../NavigateOptionsType.md)

### navigate function returns

- A `Promise` that resolves when the navigation is complete

## Examples

```tsx
import { useNavigate } from '@tanstack/react-router'

function PostsPage() {
  const navigate = useNavigate({ from: '/posts' })
  const handleClick = () => navigate({ search: { page: 2 } })
  // ...
}

function Component() {
  const navigate = useNavigate()
  return (
    <div>
      <button
        onClick={() =>
          navigate({
            to: '/posts',
          })
        }
      >
        Posts
      </button>
      <button
        onClick={() =>
          navigate({
            to: '/posts',
            search: { page: 2 },
          })
        }
      >
        Posts (Page 2)
      </button>
      <button
        onClick={() =>
          navigate({
            to: '/posts',
            hash: 'my-hash',
          })
        }
      >
        Posts (Hash)
      </button>
      <button
        onClick={() =>
          navigate({
            to: '/posts',
            state: { from: 'home' },
          })
        }
      >
        Posts (State)
      </button>
    </div>
  )
}
```

# useParams hook

The `useParams` method returns all of the path parameters that were parsed for the closest match and all of its parent matches.

## useParams options

The `useParams` hook accepts an optional `options` object.

### `opts.strict` option

- Type: `boolean`
- Optional - `default: true`
- If `false`, the `opts.from` option will be ignored and types will be loosened to `Partial<AllParams>` to reflect the shared types of all params.

### `opts.shouldThrow` option

- Type: `boolean`
- Optional
- `default: true`
- If `false`,`useParams` will not throw an invariant exception in case a match was not found in the currently rendered matches; in this case, it will return `undefined`.

### `opts.select` option

- Optional
- `(params: AllParams) => TSelected`
- If supplied, this function will be called with the params object and the return value will be returned from `useParams`. This value will also be used to determine if the hook should re-render its parent component using shallow equality checks.

### `opts.structuralSharing` option

- Type: `boolean`
- Optional
- Configures whether structural sharing is enabled for the value returned by `select`.
- See the [Render Optimizations guide](../../../guide/render-optimizations.md) for more information.

## useParams returns

- An object of of the match's and parent match path params or `TSelected` if a `select` function is provided.

## Examples

```tsx
import { useParams } from '@tanstack/react-router'

const routeApi = getRouteApi('/posts/$postId')

function Component() {
  const params = useParams({ from: '/posts/$postId' })

  // OR

  const routeParams = routeApi.useParams()

  // OR

  const postId = useParams({
    from: '/posts/$postId',
    select: (params) => params.postId,
  })

  // OR

  const looseParams = useParams({ strict: false })

  // ...
}
```

# useParentMatches hook

The `useParentMatches` hook returns all of the parent [`RouteMatch`](../RouteMatchType.md) objects from the root down to the immediate parent of the current match in context. **It does not include the current match, which can be obtained using the `useMatch` hook.**

> [!IMPORTANT]
> If the router has pending matches and they are showing their pending component fallbacks, `router.state.pendingMatches` will used instead of `router.state.matches`.

## useParentMatches options

The `useParentMatches` hook accepts an optional `options` object.

### `opts.select` option

- Optional
- `(matches: RouteMatch[]) => TSelected`
- If supplied, this function will be called with the route matches and the return value will be returned from `useParentMatches`. This value will also be used to determine if the hook should re-render its parent component using shallow equality checks.

### `opts.structuralSharing` option

- Type: `boolean`
- Optional
- Configures whether structural sharing is enabled for the value returned by `select`.
- See the [Render Optimizations guide](../../../guide/render-optimizations.md) for more information.

## useParentMatches returns

- If a `select` function is provided, the return value of the `select` function.
- If no `select` function is provided, an array of [`RouteMatch`](../RouteMatchType.md) objects.

## Examples

```tsx
import { useParentMatches } from '@tanstack/react-router'

function Component() {
  const parentMatches = useParentMatches()
  //    ^ [RouteMatch, RouteMatch, ...]
}
```

# useRouteContext hook

The `useRouteContext` method is a hook that returns the current context for the current route. This hook is useful for accessing the current route context in a component.

## useRouteContext options

The `useRouteContext` hook accepts an `options` object.

### `opts.from` option

- Type: `string`
- Required
- The RouteID to match the route context from.

### `opts.select` option

- Type: `(context: RouteContext) => TSelected`
- Optional
- If supplied, this function will be called with the route context object and the return value will be returned from `useRouteContext`.

## useRouteContext returns

- The current context for the current route or `TSelected` if a `select` function is provided.

## Examples

```tsx
import { useRouteContext } from '@tanstack/react-router'

function Component() {
  const context = useRouteContext({ from: '/posts/$postId' })
  //    ^ RouteContext

  // OR

  const selected = useRouteContext({
    from: '/posts/$postId',
    select: (context) => context.postId,
  })
  //    ^ string

  // ...
}
```

# useRouter hook

The `useRouter` method is a hook that returns the current instance of [`Router`](../RouterType.md) from context. This hook is useful for accessing the router instance in a component.

## useRouter returns

- The current [`Router`](../RouterType.md) instance.

> ⚠️⚠️⚠️ **`router.state` is always up to date, but NOT REACTIVE. If you use `router.state` in a component, the component will not re-render when the router state changes. To get a reactive version of the router state, use the [`useRouterState`](../useRouterStateHook.md) hook.**

## Examples

```tsx
import { useRouter } from '@tanstack/react-router'

function Component() {
  const router = useRouter()
  //    ^ Router

  // ...
}
```

# useRouterState hook

The `useRouterState` method is a hook that returns the current internal state of the router. This hook is useful for accessing the current state of the router in a component.

> [!TIP]
> If you want to access the current location or the current matches, you should try out the [`useLocation`](../useLocationHook.md) and [`useMatches`](../useMatchesHook.md) hooks first. These hooks are designed to be more ergonomic and easier to use than accessing the router state directly.

## useRouterState options

The `useRouterState` hook accepts an optional `options` object.

### `opts.select` option

- Type: `(state: RouterState) => TSelected`
- Optional
- If supplied, this function will be called with the [`RouterState`](../RouterStateType.md) object and the return value will be returned from `useRouterState`.

### `opts.structuralSharing` option

- Type: `boolean`
- Optional
- Configures whether structural sharing is enabled for the value returned by `select`.
- See the [Render Optimizations guide](../../../guide/render-optimizations.md) for more information.

## useRouterState returns

- The current [`RouterState`](../RouterStateType.md) object or `TSelected` if a `select` function is provided.

## Examples

```tsx
import { useRouterState } from '@tanstack/react-router'

function Component() {
  const state = useRouterState()
  //    ^ RouterState

  // OR

  const selected = useRouterState({
    select: (state) => state.location,
  })
  //    ^ ParsedLocation

  // ...
}
```

# useSearch hook

The `useSearch` method is a hook that returns the current search query parameters as an object for the current location. This hook is useful for accessing the current search string and query parameters in a component.

## useSearch options

The `useSearch` hook accepts an `options` object.

### `opts.from` option

- Type: `string`
- Required
- The RouteID to match the search query parameters from.

### `opts.shouldThrow` option

- Type: `boolean`
- Optional
- `default: true`
- If `false`,`useSearch` will not throw an invariant exception in case a match was not found in the currently rendered matches; in this case, it will return `undefined`.

### `opts.select` option

- Type: `(search: SelectedSearchSchema) => TSelected`
- Optional
- If supplied, this function will be called with the search object and the return value will be returned from `useSearch`.

### `opts.structuralSharing` option

- Type: `boolean`
- Optional
- Configures whether structural sharing is enabled for the value returned by `select`.
- See the [Render Optimizations guide](../../../guide/render-optimizations.md) for more information.

### `opts.strict` option

- Type: `boolean`
- Optional - `default: true`
- If `false`, the `opts.from` option will be ignored and types will be loosened to `Partial<FullSearchSchema>` to reflect the shared types of all search query parameters.

## useSearch returns

- If `opts.from` is provided, an object of the search query parameters for the current location or `TSelected` if a `select` function is provided.
- If `opts.strict` is `false`, an object of the search query parameters for the current location or `TSelected` if a `select` function is provided.

## Examples

```tsx
import { useSearch } from '@tanstack/react-router'

function Component() {
  const search = useSearch({ from: '/posts/$postId' })
  //    ^ FullSearchSchema

  // OR

  const selected = useSearch({
    from: '/posts/$postId',
    select: (search) => search.postView,
  })
  //    ^ string

  // OR

  const looseSearch = useSearch({ strict: false })
  //    ^ Partial<FullSearchSchema>

  // ...
}
```

</@tanstack/react-router_api>

<@tanstack/react-router_guide>
Always Apply: false - This rule should only be applied when relevant files are open
Always apply this rule in these files: src/**/\*.ts, src/**/\*.tsx

# Authenticated Routes

Authentication is an extremely common requirement for web applications. In this guide, we'll walk through how to use TanStack Router to build protected routes, and how to redirect users to login if they try to access them.

## The `route.beforeLoad` Option

The `route.beforeLoad` option allows you to specify a function that will be called before a route is loaded. It receives all of the same arguments that the `route.loader` function does. This is a great place to check if a user is authenticated, and redirect them to a login page if they are not.

The `beforeLoad` function runs in relative order to these other route loading functions:

- Route Matching (Top-Down)
  - `route.params.parse`
  - `route.validateSearch`
- Route Loading (including Preloading)
  - **`route.beforeLoad`**
  - `route.onError`
- Route Loading (Parallel)
  - `route.component.preload?`
  - `route.load`

**It's important to know that the `beforeLoad` function for a route is called _before any of its child routes' `beforeLoad` functions_.** It is essentially a middleware function for the route and all of its children.

**If you throw an error in `beforeLoad`, none of its children will attempt to load**.

## Redirecting

While not required, some authentication flows require redirecting to a login page. To do this, you can **throw a `redirect()`** from `beforeLoad`:

```tsx
// src/routes/_authenticated.tsx
export const Route = createFileRoute('/_authenticated')({
  beforeLoad: async ({ location }) => {
    if (!isAuthenticated()) {
      throw redirect({
        to: '/login',
        search: {
          // Use the current location to power a redirect after login
          // (Do not use `router.state.resolvedLocation` as it can
          // potentially lag behind the actual current location)
          redirect: location.href,
        },
      })
    }
  },
})
```

> [!TIP]
> The `redirect()` function takes all of the same options as the `navigate` function, so you can pass options like `replace: true` if you want to replace the current history entry instead of adding a new one.

Once you have authenticated a user, it's also common practice to redirect them back to the page they were trying to access. To do this, you can utilize the `redirect` search param that we added in our original redirect. Since we'll be replacing the entire URL with what it was, `router.history.push` is better suited for this than `router.navigate`:

```tsx
router.history.push(search.redirect)
```

## Non-Redirected Authentication

Some applications choose to not redirect users to a login page, and instead keep the user on the same page and show a login form that either replaces the main content or hides it via a modal. This is also possible with TanStack Router by simply short circuiting rendering the `<Outlet />` that would normally render the child routes:

```tsx
// src/routes/_authenticated.tsx
export const Route = createFileRoute('/_authenticated')({
  component: () => {
    if (!isAuthenticated()) {
      return <Login />
    }

    return <Outlet />
  },
})
```

This keeps the user on the same page, but still allows you to render a login form. Once the user is authenticated, you can simply render the `<Outlet />` and the child routes will be rendered.

## Authentication using React context/hooks

If your authentication flow relies on interactions with React context and/or hooks, you'll need to pass down your authentication state to TanStack Router using `router.context` option.

> [!IMPORTANT]
> React hooks are not meant to be consumed outside of React components. If you need to use a hook outside of a React component, you need to extract the returned state from the hook in a component that wraps your `<RouterProvider />` and then pass the returned value down to TanStack Router.

We'll cover the `router.context` options in-detail in the [Router Context](../router-context.md) section.

Here's an example that uses React context and hooks for protecting authenticated routes in TanStack Router. See the entire working setup in the [Authenticated Routes example](https://github.com/TanStack/router/tree/main/examples/react/authenticated-routes).

- `src/routes/__root.tsx`

```tsx
import { createRootRouteWithContext } from '@tanstack/react-router'

interface MyRouterContext {
  // The ReturnType of your useAuth hook or the value of your AuthContext
  auth: AuthState
}

export const Route = createRootRouteWithContext<MyRouterContext>()({
  component: () => <Outlet />,
})
```

- `src/router.tsx`

```tsx
import { createRouter } from '@tanstack/react-router'

import { routeTree } from './routeTree.gen'

export const router = createRouter({
  routeTree,
  context: {
    // auth will initially be undefined
    // We'll be passing down the auth state from within a React component
    auth: undefined!,
  },
})
```

- `src/App.tsx`

```tsx
import { RouterProvider } from '@tanstack/react-router'

import { AuthProvider, useAuth } from './auth'

import { router } from './router'

function InnerApp() {
  const auth = useAuth()
  return <RouterProvider router={router} context={{ auth }} />
}

function App() {
  return (
    <AuthProvider>
      <InnerApp />
    </AuthProvider>
  )
}
```

Then in the authenticated route, you can check the auth state using the `beforeLoad` function, and **throw a `redirect()`** to your **Login route** if the user is not signed-in.

- `src/routes/dashboard.route.tsx`

```tsx
import { createFileRoute, redirect } from '@tanstack/react-router'

export const Route = createFileRoute('/dashboard')({
  beforeLoad: ({ context, location }) => {
    if (!context.auth.isAuthenticated) {
      throw redirect({
        to: '/login',
        search: {
          redirect: location.href,
        },
      })
    }
  },
})
```

You can _optionally_, also use the [Non-Redirected Authentication](#non-redirected-authentication) approach to show a login form instead of calling a **redirect**.

This approach can also be used in conjunction with Pathless or Layout Route to protect all routes under their parent route.

## Related How-To Guides

For detailed, step-by-step implementation guides, see:

- [How to Set Up Basic Authentication](../../how-to/setup-authentication.md) - Complete setup with React Context and protected routes
- [How to Integrate Authentication Providers](../../how-to/setup-auth-providers.md) - Use Auth0, Clerk, or Supabase
- [How to Set Up Role-Based Access Control](../../how-to/setup-rbac.md) - Implement permissions and role-based routing

## Examples

Working authentication examples are available in the repository:

- [Basic Authentication Example](https://github.com/TanStack/router/tree/main/examples/react/authenticated-routes) - Simple authentication with context
- [Firebase Authentication](https://github.com/TanStack/router/tree/main/examples/react/authenticated-routes-firebase) - Firebase Auth integration
- [TanStack Start Auth Examples](https://github.com/TanStack/router/tree/main/examples/react) - Various auth implementations with TanStack Start

# Automatic Code Splitting

The automatic code splitting feature in TanStack Router allows you to optimize your application's bundle size by lazily loading route components and their associated data. This is particularly useful for large applications where you want to minimize the initial load time by only loading the necessary code for the current route.

To turn this feature on, simply set the `autoCodeSplitting` option to `true` in your bundler plugin configuration. This enables the router to automatically handle code splitting for your routes without requiring any additional setup.

```ts
// vite.config.ts
import { defineConfig } from 'vite'
import { tanstackRouter } from '@tanstack/router-plugin/vite'

export default defineConfig({
  plugins: [
    tanstackRouter({
      autoCodeSplitting: true, // Enable automatic code splitting
    }),
  ],
})
```

But that's just the beginning! TanStack Router's automatic code splitting is not only easy to enable, but it also provides powerful customization options to tailor how your routes are split into chunks. This allows you to optimize your application's performance based on your specific needs and usage patterns.

## How does it work?

TanStack Router's automatic code splitting works by transforming your route files both during 'development' and at 'build' time. It rewrites the route definitions to use lazy-loading wrappers for components and loaders, which allows the bundler to group these properties into separate chunks.

> [!TIP]
> A **chunk** is a file that contains a portion of your application's code, which can be loaded on demand. This helps reduce the initial load time of your application by only loading the code that is needed for the current route.

So when your application loads, it doesn't include all the code for every route. Instead, it only includes the code for the routes that are initially needed. As users navigate through your application, additional chunks are loaded on demand.

This happens seamlessly, without requiring you to manually split your code or manage lazy loading. The TanStack Router bundler plugin takes care of everything, ensuring that your routes are optimized for performance right out of the box.

### The transformation process

When you enable automatic code splitting, the bundler plugin does this by using static code analysis look at your the code in your route files to transform them into optimized outputs.

This transformation process produces two key outputs when each of your route files are processed:

1. **Reference File**: The bundler plugin takes your original route file (e.g., `posts.route.tsx`) and modifies the values for properties like `component` or `pendingComponent` to use special lazy-loading wrappers that'll fetch the actual code later. These wrappers point to a "virtual" file that the bundler will resolve later on.
2. **Virtual File**: When the bundler sees a request for one of these virtual files (e.g., `posts.route.tsx?tsr-split=component`), it intercepts it to generate a new, minimal on-the-fly file that _only_ contains the code for the requested properties (e.g., just the `PostsComponent`).

This process ensures that your original code remains clean and readable, while the actual bundled output is optimized for initial bundle size.

### What gets code split?

The decision of what to split into separate chunks is crucial for optimizing your application's performance. TanStack Router uses a concept called "**Split Groupings**" to determine how different parts of your route should be bundled together.

Split groupings are arrays of properties that tell TanStack Router how to bundle different parts of your route together. Each grouping is an list of property names that you want to bundle together into a single lazy-loaded chunk.

The available properties to split are:

- `component`
- `errorComponent`
- `pendingComponent`
- `notFoundComponent`
- `loader`

By default, TanStack Router uses the following split groupings:

```sh
[
  ['component'],
  ['errorComponent'],
  ['notFoundComponent']
]
```

This means that it creates three separate lazy-loaded chunks for each route. Resulting in:

- One for the main component
- One for the error component
- And one for the not-found component.

### Rules of Splitting

For automatic code splitting to work, there are some rules in-place to make sure that this process can reliably and predictably happen.

#### Do not export route properties

Route properties like `component`, `loader`, etc., should not be exported from the route file. Exporting these properties results in them being bundled into the main application bundle, which means that they will not be code-split.

```tsx
import { createRoute } from '@tanstack/react-router'

export const Route = createRoute('/posts')({
  // ...
  notFoundComponent: PostsNotFoundComponent,
})

// ❌ Do NOT do this!
// Exporting the notFoundComponent will prevent it from being code-split
// and will be included in the main bundle.
export function PostsNotFoundComponent() {
  // ❌
  // ...
}

function PostsNotFoundComponent() {
  // ✅
  // ...
}
```

**That's it!** There are no other restrictions. You can use any other JavaScript or TypeScript features in your route files as you normally would. If you run into any issues, please [open an issue](https://github.com/tanstack/router/issues) on GitHub.

## Granular control

For most applications, the default behavior of using `autoCodeSplitting: true` is sufficient. However, TanStack Router provides several options to customize how your routes are split into chunks, allowing you to optimize for specific use cases or performance needs.

### Global code splitting behavior (`defaultBehavior`)

You can change how TanStack Router splits your routes by changing the `defaultBehavior` option in your bundler plugin configuration. This allows you to define how different properties of your routes should be bundled together.

For example, to bundle all UI-related components into a single chunk, you could configure it like this:

```ts
// vite.config.ts
import { defineConfig } from 'vite'
import { tanstackRouter } from '@tanstack/router-plugin/vite'

export default defineConfig({
  plugins: [
    tanstackRouter({
      autoCodeSplitting: true,
      codeSplittingOptions: {
        defaultBehavior: [
          ['component', 'pendingComponent', 'errorComponent', 'notFoundComponent'], // Bundle all UI components together
        ],
      },
    }),
  ],
})
```

### Advanced programmatic control (`splitBehavior`)

For complex rulesets, you can use the `splitBehavior` function in your vite config to programmatically define how routes should be split into chunks based on their `routeId`. This function allows you to implement custom logic for grouping properties together, giving you fine-grained control over the code splitting behavior.

```ts
// vite.config.ts
import { defineConfig } from 'vite'
import { tanstackRouter } from '@tanstack/router-plugin/vite'

export default defineConfig({
  plugins: [
    tanstackRouter({
      autoCodeSplitting: true,
      codeSplittingOptions: {
        splitBehavior: ({ routeId }) => {
          // For all routes under /posts, bundle the loader and component together
          if (routeId.startsWith('/posts')) {
            return [['loader', 'component']]
          }
          // All other routes will use the `defaultBehavior`
        },
      },
    }),
  ],
})
```

### Per-route overrides (`codeSplitGroupings`)

For ultimate control, you can override the global configuration directly inside a route file by adding a `codeSplitGroupings` property. This is useful for routes that have unique optimization needs.

```tsx
// src/routes/posts.route.tsx
import { createFileRoute } from '@tanstack/react-router'
import { loadPostsData } from './-heavy-posts-utils'

export const Route = createFileRoute('/posts')({
  // For this specific route, bundle the loader and component together.
  codeSplitGroupings: [['loader', 'component']],
  loader: () => loadPostsData(),
  component: PostsComponent,
})

function PostsComponent() {
  // ...
}
```

This will create a single chunk that includes both the `loader` and the `component` for this specific route, overriding both the default behavior and any programmatic split behavior defined in your bundler config.

### Configuration order matters

This guide has so far describe three different ways to configure how TanStack Router splits your routes into chunks.

To make sure that the different configurations do not conflict with each other, TanStack Router uses the following order of precedence:

1. **Per-route overrides**: The `codeSplitGroupings` property inside a route file takes the highest precedence. This allows you to define specific split groupings for individual routes.
2. **Programmatic split behavior**: The `splitBehavior` function in your bundler config allows you to define custom logic for how routes should be split based on their `routeId`.
3. **Default behavior**: The `defaultBehavior` option in your bundler config serves as the fallback for any routes that do not have specific overrides or custom logic defined. This is the base configuration that applies to all routes unless overridden.

### Splitting the Data Loader

The `loader` function is responsible for fetching data needed by the route. By default, it is bundled with into your "reference file" and loaded in the initial bundle. However, you can also split the `loader` into its own chunk if you want to optimize further.

> [!CAUTION]
> Moving the `loader` into its own chunk is a **performance trade-off**. It introduces an additional trip to the server before the data can be fetched, which can lead to slower initial page loads. This is because the `loader` **must** be fetched and executed before the route can render its component.
> Therefore, we recommend keeping the `loader` in the initial bundle unless you have a specific reason to split it.

```ts
// vite.config.ts
import { defineConfig } from 'vite'
import { tanstackRouter } from '@tanstack/router-plugin/vite'

export default defineConfig({
  plugins: [
    tanstackRouter({
      autoCodeSplitting: true,
      codeSplittingOptions: {
        defaultBehavior: [
          ['loader'], // The loader will be in its own chunk
          ['component'],
          // ... other component groupings
        ],
      },
    }),
  ],
})
```

We highly discourage splitting the `loader` unless you have a specific use case that requires it. In most cases, not splitting off the `loader` and keep it in the main bundle is the best choice for performance.

# Code Splitting

Code splitting and lazy loading is a powerful technique for improving the bundle size and load performance of an application.

- Reduces the amount of code that needs to be loaded on initial page load
- Code is loaded on-demand when it is needed
- Results in more chunks that are smaller in size that can be cached more easily by the browser.

## How does TanStack Router split code?

TanStack Router separates code into two categories:

- **Critical Route Configuration** - The code that is required to render the current route and kick off the data loading process as early as possible.
  - Path Parsing/Serialization
  - Search Param Validation
  - Loaders, Before Load
  - Route Context
  - Static Data
  - Links
  - Scripts
  - Styles
  - All other route configuration not listed below

- **Non-Critical/Lazy Route Configuration** - The code that is not required to match the route, and can be loaded on-demand.
  - Route Component
  - Error Component
  - Pending Component
  - Not-found Component

> 🧠 **Why is the loader not split?**
>
> - The loader is already an asynchronous boundary, so you pay double to both get the chunk _and_ wait for the loader to execute.
> - Categorically, it is less likely to contribute to a large bundle size than a component.
> - The loader is one of the most important preloadable assets for a route, especially if you're using a default preload intent, like hovering over a link, so it's important for the loader to be available without any additional async overhead.
>
>   Knowing the disadvantages of splitting the loader, if you still want to go ahead with it, head over to the [Data Loader Splitting](#data-loader-splitting) section.

## Encapsulating a route's files into a directory

Since TanStack Router's file-based routing system is designed to support both flat and nested file structures, it's possible to encapsulate a route's files into a single directory without any additional configuration.

To encapsulate a route's files into a directory, move the route file itself into a `.route` file within a directory with the same name as the route file.

For example, if you have a route file named `posts.tsx`, you would create a new directory named `posts` and move the `posts.tsx` file into that directory, renaming it to `route.tsx`.

**Before**

- `posts.tsx`

**After**

- `posts`
  - `route.tsx`

## Approaches to code splitting

TanStack Router supports multiple approaches to code splitting. If you are using code-based routing, skip to the [Code-Based Splitting](#code-based-splitting) section.

When you are using file-based routing, you can use the following approaches to code splitting:

- [Using automatic code-splitting ✨](#using-automatic-code-splitting)
- [Using the `.lazy.tsx` suffix](#using-the-lazytsx-suffix)
- [Using Virtual Routes](#using-virtual-routes)

## Using automatic code-splitting✨

This is the easiest and most powerful way to code split your route files.

When using the `autoCodeSplitting` feature, TanStack Router will automatically code split your route files based on the non-critical route configuration mentioned above.

> [!IMPORTANT]
> The automatic code-splitting feature is **ONLY** available when you are using file-based routing with one of our [supported bundlers](../../routing/file-based-routing.md#getting-started-with-file-based-routing).
> This will **NOT** work if you are **only** using the CLI (`@tanstack/router-cli`).

To enable automatic code-splitting, you just need to add the following to the configuration of your TanStack Router Bundler Plugin:

```ts
// vite.config.ts
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import { tanstackRouter } from '@tanstack/router-plugin/vite'

export default defineConfig({
  plugins: [
    tanstackRouter({
      // ...
      autoCodeSplitting: true,
    }),
    react(), // Make sure to add this plugin after the TanStack Router Bundler plugin
  ],
})
```

That's it! TanStack Router will automatically code-split all your route files by their critical and non-critical route configurations.

If you want more control over the code-splitting process, head over to the [Automatic Code Splitting](../automatic-code-splitting.md) guide to learn more about the options available.

## Using the `.lazy.tsx` suffix

If you are not able to use the automatic code-splitting feature, you can still code-split your route files using the `.lazy.tsx` suffix. It is **as easy as moving your code into a separate file with a `.lazy.tsx` suffix** and using the `createLazyFileRoute` function instead of `createFileRoute`.

> [!IMPORTANT]
> The `__root.tsx` route file, using either `createRootRoute` or `createRootRouteWithContext`, does not support code splitting, since it's always rendered regardless of the current route.

These are the only options that `createLazyFileRoute` support:

| Export Name         | Description                                                           |
| ------------------- | --------------------------------------------------------------------- |
| `component`         | The component to render for the route.                                |
| `errorComponent`    | The component to render when an error occurs while loading the route. |
| `pendingComponent`  | The component to render while the route is loading.                   |
| `notFoundComponent` | The component to render if a not-found error gets thrown.             |

### Example code splitting with `.lazy.tsx`

When you are using `.lazy.tsx` you can split your route into two files to enable code splitting:

**Before (Single File)**

```tsx
// src/routes/posts.tsx
import { createFileRoute } from '@tanstack/react-router'
import { fetchPosts } from './api'

export const Route = createFileRoute('/posts')({
  loader: fetchPosts,
  component: Posts,
})

function Posts() {
  // ...
}
```

**After (Split into two files)**

This file would contain the critical route configuration:

```tsx
// src/routes/posts.tsx

import { createFileRoute } from '@tanstack/react-router'
import { fetchPosts } from './api'

export const Route = createFileRoute('/posts')({
  loader: fetchPosts,
})
```

With the non-critical route configuration going into the file with the `.lazy.tsx` suffix:

```tsx
// src/routes/posts.lazy.tsx
import { createLazyFileRoute } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/posts')({
  component: Posts,
})

function Posts() {
  // ...
}
```

## Using Virtual Routes

You might run into a situation where you end up splitting out everything from a route file, leaving it empty! In this case, simply **delete the route file entirely**! A virtual route will automatically be generated for you to serve as an anchor for your code split files. This virtual route will live directly in the generated route tree file.

**Before (Virtual Routes)**

```tsx
// src/routes/posts.tsx
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/posts')({
  // Hello?
})
```

```tsx
// src/routes/posts.lazy.tsx
import { createLazyFileRoute } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/posts')({
  component: Posts,
})

function Posts() {
  // ...
}
```

**After (Virtual Routes)**

```tsx
// src/routes/posts.lazy.tsx
import { createLazyFileRoute } from '@tanstack/react-router'

export const Route = createLazyFileRoute('/posts')({
  component: Posts,
})

function Posts() {
  // ...
}
```

Tada! 🎉

## Code-Based Splitting

If you are using code-based routing, you can still code-split your routes using the `Route.lazy()` method and the `createLazyRoute` function. You'll need to split your route configuration into two parts:

Create a lazy route using the `createLazyRoute` function.

```tsx
// src/posts.lazy.tsx
export const Route = createLazyRoute('/posts')({
  component: MyComponent,
})

function MyComponent() {
  return <div>My Component</div>
}
```

Then, call the `.lazy` method on the route definition in your `app.tsx` to import the lazy/code-split route with the non-critical route configuration.

```tsx
// src/app.tsx
const postsRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: '/posts',
}).lazy(() => import('./posts.lazy').then((d) => d.Route))
```

## Data Loader Splitting

**Be warned!!!** Splitting a route loader is a dangerous game.

It can be a powerful tool to reduce bundle size, but it comes with a cost as mentioned in the [How does TanStack Router split code?](#how-does-tanstack-router-split-code) section.

You can code split your data loading logic using the Route's `loader` option. While this process makes it difficult to maintain type-safety with the parameters passed to your loader, you can always use the generic `LoaderContext` type to get you most of the way there:

```tsx
import { lazyFn } from '@tanstack/react-router'

const route = createRoute({
  path: '/my-route',
  component: MyComponent,
  loader: lazyFn(() => import('./loader'), 'loader'),
})

// In another file...a
export const loader = async (context: LoaderContext) => {
  /// ...
}
```

If you are using file-based routing, you'll only be able to split your `loader` if you are using [Automatic Code Splitting](#using-automatic-code-splitting) with customized bundling options.

## Manually accessing Route APIs in other files with the `getRouteApi` helper

As you might have guessed, placing your component code in a separate file than your route can make it difficult to consume the route itself. To help with this, TanStack Router exports a handy `getRouteApi` function that you can use to access a route's type-safe APIs in a file without importing the route itself.

- `my-route.tsx`

```tsx
import { createRoute } from '@tanstack/react-router'
import { MyComponent } from './MyComponent'

const route = createRoute({
  path: '/my-route',
  loader: () => ({
    foo: 'bar',
  }),
  component: MyComponent,
})
```

- `MyComponent.tsx`

```tsx
import { getRouteApi } from '@tanstack/react-router'

const route = getRouteApi('/my-route')

export function MyComponent() {
  const loaderData = route.useLoaderData()
  //    ^? { foo: string }

  return <div>...</div>
}
```

The `getRouteApi` function is useful for accessing other type-safe APIs:

- `useLoaderData`
- `useLoaderDeps`
- `useMatch`
- `useParams`
- `useRouteContext`
- `useSearch`

# Creating a Router

## The `Router` Class

When you're ready to start using your router, you'll need to create a new `Router` instance. The router instance is the core brains of TanStack Router and is responsible for managing the route tree, matching routes, and coordinating navigations and route transitions. It also serves as a place to configure router-wide settings.

```tsx
import { createRouter } from '@tanstack/react-router'

const router = createRouter({
  // ...
})
```

## Route Tree

You'll probably notice quickly that the `Router` constructor requires a `routeTree` option. This is the route tree that the router will use to match routes and render components.

Whether you used [file-based routing](../../routing/file-based-routing.md) or [code-based routing](../../routing/code-based-routing.md), you'll need to pass your route tree to the `createRouter` function:

### Filesystem Route Tree

If you used our recommended file-based routing, then it's likely your generated route tree file was created at the default `src/routeTree.gen.ts` location. If you used a custom location, then you'll need to import your route tree from that location.

```tsx
import { routeTree } from './routeTree.gen'
```

### Code-Based Route Tree

If you used code-based routing, then you likely created your route tree manually using the root route's `addChildren` method:

```tsx
const routeTree = rootRoute.addChildren([
  // ...
])
```

## Router Type Safety

> [!IMPORTANT]
> DO NOT SKIP THIS SECTION! ⚠️

TanStack Router provides amazing support for TypeScript, even for things you wouldn't expect like bare imports straight from the library! To make this possible, you must register your router's types using TypeScripts' [Declaration Merging](https://www.typescriptlang.org/docs/handbook/declaration-merging.html) feature. This is done by extending the `Register` interface on `@tanstack/react-router` with a `router` property that has the type of your `router` instance:

```tsx
declare module '@tanstack/react-router' {
  interface Register {
    // This infers the type of our router and registers it across your entire project
    router: typeof router
  }
}
```

With your router registered, you'll now get type-safety across your entire project for anything related to routing.

## 404 Not Found Route

As promised in earlier guides, we'll now cover the `notFoundRoute` option. This option is used to configure a route that will render when no other suitable match is found. This is useful for rendering a 404 page or redirecting to a default route.

If you are using either file-based or code-based routing, then you'll need to add a `notFoundComponent` key to `createRootRoute`:

```tsx
export const Route = createRootRoute({
  component: () => (
    // ...
  ),
  notFoundComponent: () => <div>404 Not Found</div>,
});
```

## Other Options

There are many other options that can be passed to the `Router` constructor. You can find a full list of them in the [API Reference](../../api/router/RouterOptionsType.md).

# Custom Link

While repeating yourself can be acceptable in many situations, you might find that you do it too often. At times, you may want to create cross-cutting components with additional behavior or styles. You might also consider using third-party libraries in combination with TanStack Router's type safety.

## `createLink` for cross-cutting concerns

`createLink` creates a custom `Link` component with the same type parameters as `Link`. This means you can create your own component which provides the same type safety and typescript performance as `Link`.

### Basic example

If you want to create a basic custom link component, you can do so with the following:

[//]: # 'BasicExampleImplementation'

```tsx
import * as React from 'react'
import { createLink, LinkComponent } from '@tanstack/react-router'

interface BasicLinkProps extends React.AnchorHTMLAttributes<HTMLAnchorElement> {
  // Add any additional props you want to pass to the anchor element
}

const BasicLinkComponent = React.forwardRef<HTMLAnchorElement, BasicLinkProps>((props, ref) => {
  return <a ref={ref} {...props} className={'block px-3 py-2 text-blue-700'} />
})

const CreatedLinkComponent = createLink(BasicLinkComponent)

export const CustomLink: LinkComponent<typeof BasicLinkComponent> = (props) => {
  return <CreatedLinkComponent preload={'intent'} {...props} />
}
```

[//]: # 'BasicExampleImplementation'

You can then use your newly created `Link` component as any other `Link`

```tsx
<CustomLink to={'/dashboard/invoices/$invoiceId'} params={{ invoiceId: 0 }} />
```

[//]: # 'ExamplesUsingThirdPartyLibs'

## `createLink` with third party libraries

Here are some examples of how you can use `createLink` with third-party libraries.

### React Aria Components example

React Aria Components v1.11.0 and later works with TanStack Router's `preload (intent)` prop. Use `createLink` to wrap each React Aria component that you use as a link.

```tsx
import { createLink } from '@tanstack/react-router'
import { Link as RACLink, MenuItem } from 'react-aria-components'

export const Link = createLink(RACLink)
export const MenuItemLink = createLink(MenuItem)
```

To use React Aria's render props, including the `className`, `style`, and `children` functions, create a wrapper component and pass that to `createLink`.

```tsx
import { createLink } from '@tanstack/react-router'
import { Link as RACLink, type LinkProps } from 'react-aria-components'

interface MyLinkProps extends LinkProps {
  // your props
}

function MyLink(props: MyLinkProps) {
  return (
    <RACLink
      {...props}
      style={({ isHovered }) => ({
        color: isHovered ? 'red' : 'blue',
      })}
    />
  )
}

export const Link = createLink(MyLink)
```

### Chakra UI example

```tsx
import * as React from 'react'
import { createLink, LinkComponent } from '@tanstack/react-router'
import { Link } from '@chakra-ui/react'

interface ChakraLinkProps extends Omit<React.ComponentPropsWithoutRef<typeof Link>, 'href'> {
  // Add any additional props you want to pass to the link
}

const ChakraLinkComponent = React.forwardRef<HTMLAnchorElement, ChakraLinkProps>((props, ref) => {
  return <Link ref={ref} {...props} />
})

const CreatedLinkComponent = createLink(ChakraLinkComponent)

export const CustomLink: LinkComponent<typeof ChakraLinkComponent> = (props) => {
  return (
    <CreatedLinkComponent
      textDecoration={'underline'}
      _hover={{ textDecoration: 'none' }}
      _focus={{ textDecoration: 'none' }}
      preload={'intent'}
      {...props}
    />
  )
}
```

### MUI example

There is an [example](https://github.com/TanStack/router/tree/main/examples/react/start-material-ui) available which uses these patterns.

#### `Link`

If the MUI `Link` should simply behave like the router `Link`, it can be just wrapped with `createLink`:

```tsx
import { createLink } from '@tanstack/react-router'
import { Link } from '@mui/material'

export const CustomLink = createLink(Link)
```

If the `Link` should be customized this approach can be used:

```tsx
import React from 'react'
import { createLink } from '@tanstack/react-router'
import { Link } from '@mui/material'
import type { LinkProps } from '@mui/material'
import type { LinkComponent } from '@tanstack/react-router'

interface MUILinkProps extends LinkProps {
  // Add any additional props you want to pass to the Link
}

const MUILinkComponent = React.forwardRef<HTMLAnchorElement, MUILinkProps>((props, ref) => (
  <Link ref={ref} {...props} />
))

const CreatedLinkComponent = createLink(MUILinkComponent)

export const CustomLink: LinkComponent<typeof MUILinkComponent> = (props) => {
  return <CreatedLinkComponent preload={'intent'} {...props} />
}

// Can also be styled
```

#### `Button`

If a `Button` should be used as a router `Link`, the `component` should be set as `a`:

```tsx
import React from 'react'
import { createLink } from '@tanstack/react-router'
import { Button } from '@mui/material'
import type { ButtonProps } from '@mui/material'
import type { LinkComponent } from '@tanstack/react-router'

interface MUIButtonLinkProps extends ButtonProps<'a'> {
  // Add any additional props you want to pass to the Button
}

const MUIButtonLinkComponent = React.forwardRef<HTMLAnchorElement, MUIButtonLinkProps>((props, ref) => (
  <Button ref={ref} component="a" {...props} />
))

const CreatedButtonLinkComponent = createLink(MUIButtonLinkComponent)

export const CustomButtonLink: LinkComponent<typeof MUIButtonLinkComponent> = (props) => {
  return <CreatedButtonLinkComponent preload={'intent'} {...props} />
}
```

#### Usage with `styled`

Any of these MUI approaches can then be used with `styled`:

```tsx
import { css, styled } from '@mui/material'
import { CustomLink } from './CustomLink'

const StyledCustomLink = styled(CustomLink)(
  ({ theme }) => css`
    color: ${theme.palette.common.white};
  `
)
```

### Mantine example

```tsx
import * as React from 'react'
import { createLink, LinkComponent } from '@tanstack/react-router'
import { Anchor, AnchorProps } from '@mantine/core'

interface MantineAnchorProps extends Omit<AnchorProps, 'href'> {
  // Add any additional props you want to pass to the anchor
}

const MantineLinkComponent = React.forwardRef<HTMLAnchorElement, MantineAnchorProps>((props, ref) => {
  return <Anchor ref={ref} {...props} />
})

const CreatedLinkComponent = createLink(MantineLinkComponent)

export const CustomLink: LinkComponent<typeof MantineLinkComponent> = (props) => {
  return <CreatedLinkComponent preload="intent" {...props} />
}
```

[//]: # 'ExamplesUsingThirdPartyLibs'

# Custom Search Param Serialization

By default, TanStack Router parses and serializes your URL Search Params automatically using `JSON.stringify` and `JSON.parse`. This process involves escaping and unescaping the search string, which is a common practice for URL search params, in addition to the serialization and deserialization of the search object.

For instance, using the default configuration, if you have the following search object:

```tsx
const search = {
  page: 1,
  sort: 'asc',
  filters: { author: 'tanner', min_words: 800 },
}
```

It would be serialized and escaped into the following search string:

```txt
?page=1&sort=asc&filters=%7B%22author%22%3A%22tanner%22%2C%22min_words%22%3A800%7D
```

We can implement the default behavior with the following code:

```tsx
import { createRouter, parseSearchWith, stringifySearchWith } from '@tanstack/react-router'

const router = createRouter({
  // ...
  parseSearch: parseSearchWith(JSON.parse),
  stringifySearch: stringifySearchWith(JSON.stringify),
})
```

However, this default behavior may not be suitable for all use cases. For example, you may want to use a different serialization format, such as base64 encoding, or you may want to use a purpose-built serialization/deserialization library, like [query-string](https://github.com/sindresorhus/query-string), [JSURL2](https://github.com/wmertens/jsurl2), or [Zipson](https://jgranstrom.github.io/zipson/).

This can be achieved by providing your own serialization and deserialization functions to the `parseSearch` and `stringifySearch` options in the [`Router`](../../api/router/RouterOptionsType.md#stringifysearch-method) configuration. When doing this, you can utilize TanStack Router's built-in helper functions, `parseSearchWith` and `stringifySearchWith`, to simplify the process.

> [!TIP]
> An important aspect of serialization and deserialization, is that you are able to get the same object back after deserialization. This is important because if the serialization and deserialization process is not done correctly, you may lose some information. For example, if you are using a library that does not support nested objects, you may lose the nested object when deserializing the search string.

![Diagram showing idempotent nature of URL search param serialization and deserialization](https://raw.githubusercontent.com/TanStack/router/main/docs/router/assets/search-serialization-deserialization-idempotency.jpg)

Here are some examples of how you can customize the search param serialization in TanStack Router:

## Using Base64

It's common to base64 encode your search params to achieve maximum compatibility across browsers and URL unfurlers, etc. This can be done with the following code:

```tsx
import { Router, parseSearchWith, stringifySearchWith } from '@tanstack/react-router'

const router = createRouter({
  parseSearch: parseSearchWith((value) => JSON.parse(decodeFromBinary(value))),
  stringifySearch: stringifySearchWith((value) => encodeToBinary(JSON.stringify(value))),
})

function decodeFromBinary(str: string): string {
  return decodeURIComponent(
    Array.prototype.map
      .call(atob(str), function (c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)
      })
      .join('')
  )
}

function encodeToBinary(str: string): string {
  return btoa(
    encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function (match, p1) {
      return String.fromCharCode(parseInt(p1, 16))
    })
  )
}
```

> [⚠️ Why does this snippet not use atob/btoa?](#safe-binary-encodingdecoding)

So, if we were to turn the previous object into a search string using this configuration, it would look like this:

```txt
?page=1&sort=asc&filters=eyJhdXRob3IiOiJ0YW5uZXIiLCJtaW5fd29yZHMiOjgwMH0%3D
```

> [!WARNING]
> If you are serializing user input into Base64, you run the risk of causing a collision with the URL deserialization. This can lead to unexpected behavior, such as the URL not being parsed correctly or being interpreted as a different value. To avoid this, you should encode the search params using a safe binary encoding/decoding method (see below).

## Using the query-string library

The [query-string](https://github.com/sindresorhus/query-string) library is a popular for being able to reliably parse and stringify query strings. You can use it to customize the serialization format of your search params. This can be done with the following code:

```tsx
import { createRouter } from '@tanstack/react-router'
import qs from 'query-string'

const router = createRouter({
  // ...
  stringifySearch: stringifySearchWith((value) =>
    qs.stringify(value, {
      // ...options
    })
  ),
  parseSearch: parseSearchWith((value) =>
    qs.parse(value, {
      // ...options
    })
  ),
})
```

So, if we were to turn the previous object into a search string using this configuration, it would look like this:

```txt
?page=1&sort=asc&filters=author%3Dtanner%26min_words%3D800
```

## Using the JSURL2 library

[JSURL2](https://github.com/wmertens/jsurl2) is a non-standard library that can compress URLs while still maintaining readability. This can be done with the following code:

```tsx
import { Router, parseSearchWith, stringifySearchWith } from '@tanstack/react-router'
import { parse, stringify } from 'jsurl2'

const router = createRouter({
  // ...
  parseSearch: parseSearchWith(parse),
  stringifySearch: stringifySearchWith(stringify),
})
```

So, if we were to turn the previous object into a search string using this configuration, it would look like this:

```txt
?page=1&sort=asc&filters=(author~tanner~min*_words~800)~
```

## Using the Zipson library

[Zipson](https://jgranstrom.github.io/zipson/) is a very user-friendly and performant JSON compression library (both in runtime performance and the resulting compression performance). To compress your search params with it (which requires escaping/unescaping and base64 encoding/decoding them as well), you can use the following code:

```tsx
import { Router, parseSearchWith, stringifySearchWith } from '@tanstack/react-router'
import { stringify, parse } from 'zipson'

const router = createRouter({
  parseSearch: parseSearchWith((value) => parse(decodeFromBinary(value))),
  stringifySearch: stringifySearchWith((value) => encodeToBinary(stringify(value))),
})

function decodeFromBinary(str: string): string {
  return decodeURIComponent(
    Array.prototype.map
      .call(atob(str), function (c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)
      })
      .join('')
  )
}

function encodeToBinary(str: string): string {
  return btoa(
    encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function (match, p1) {
      return String.fromCharCode(parseInt(p1, 16))
    })
  )
}
```

> [⚠️ Why does this snippet not use atob/btoa?](#safe-binary-encodingdecoding)

So, if we were to turn the previous object into a search string using this configuration, it would look like this:

```txt
?page=1&sort=asc&filters=JTdCJUMyJUE4YXV0aG9yJUMyJUE4JUMyJUE4dGFubmVyJUMyJUE4JUMyJUE4bWluX3dvcmRzJUMyJUE4JUMyJUEyQ3UlN0Q%3D
```

<hr>

### Safe Binary Encoding/Decoding

In the browser, the `atob` and `btoa` functions are not guaranteed to work properly with non-UTF8 characters. We recommend using these encoding/decoding utilities instead:

To encode from a string to a binary string:

```typescript
export function encodeToBinary(str: string): string {
  return btoa(
    encodeURIComponent(str).replace(/%([0-9A-F]{2})/g, function (match, p1) {
      return String.fromCharCode(parseInt(p1, 16))
    })
  )
}
```

To decode from a binary string to a string:

```typescript
export function decodeFromBinary(str: string): string {
  return decodeURIComponent(
    Array.prototype.map
      .call(atob(str), function (c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)
      })
      .join('')
  )
}
```

# Data Loading

Data loading is a common concern for web applications and is related to routing. When loading a page for your app, it's ideal if all of the page's async requirements are fetched and fulfilled as early as possible, in parallel. The router is the best place to coordinate these async dependencies as it's usually the only place in your app that knows where users are headed before content is rendered.

You may be familiar with `getServerSideProps` from Next.js or `loader`s from Remix/React-Router. TanStack Router has similar functionality to preload/load assets on a per-route basis in parallel allowing it to render as quickly as possible as it fetches via suspense.

Beyond these normal expectations of a router, TanStack Router goes above and beyond and provides **built-in SWR Caching**, a long-term in-memory caching layer for route loaders. This means that you can use TanStack Router to both preload data for your routes so they load instantaneously or temporarily cache route data for previously visited routes to use again later.

## The route loading lifecycle

Every time a URL/history update is detected, the router executes the following sequence:

- Route Matching (Top-Down)
  - `route.params.parse`
  - `route.validateSearch`
- Route Pre-Loading (Serial)
  - `route.beforeLoad`
  - `route.onError`
    - `route.errorComponent` / `parentRoute.errorComponent` / `router.defaultErrorComponent`
- Route Loading (Parallel)
  - `route.component.preload?`
  - `route.loader`
    - `route.pendingComponent` (Optional)
    - `route.component`
  - `route.onError`
    - `route.errorComponent` / `parentRoute.errorComponent` / `router.defaultErrorComponent`

## To Router Cache or not to Router Cache?

There is a high possibility that TanStack's router cache will be a good fit for most smaller to medium size applications, but it's important to understand the tradeoffs of using it vs a more robust caching solution like TanStack Query:

TanStack Router Cache Pros:

- Built-in, easy to use, no extra dependencies
- Handles deduping, preloading, loading, stale-while-revalidate, background refetching on a per-route basis
- Coarse invalidation (invalidate all routes and cache at once)
- Automatic garbage collection
- Works great for apps that share little data between routes
- "Just works" for SSR

TanStack Router Cache Cons:

- No persistence adapters/model
- No shared caching/deduping between routes
- No built-in mutation APIs (a basic `useMutation` hook is provided in many examples that may be sufficient for many use cases)
- No built-in cache-level optimistic update APIs (you can still use ephemeral state from something like a `useMutation` hook to achieve this at the component level)

> [!TIP]
> If you know right away that you'd like to or need to use something more robust like TanStack Query, skip to the [External Data Loading](../external-data-loading.md) guide.

## Using the Router Cache

The router cache is built-in and is as easy as returning data from any route's `loader` function. Let's learn how!

## Route `loader`s

Route `loader` functions are called when a route match is loaded. They are called with a single parameter which is an object containing many helpful properties. We'll go over those in a bit, but first, let's look at an example of a route `loader` function:

```tsx
// routes/posts.tsx
export const Route = createFileRoute('/posts')({
  loader: () => fetchPosts(),
})
```

## `loader` Parameters

The `loader` function receives a single object with the following properties:

- `abortController` - The route's abortController. Its signal is cancelled when the route is unloaded or when the Route is no longer relevant and the current invocation of the `loader` function becomes outdated.
- `cause` - The cause of the current route match. Can be either one of the following:
  - `enter` - When the route is matched and loaded after not being matched in the previous location.
  - `preload` - When the route is being preloaded.
  - `stay` - When the route is matched and loaded after being matched in the previous location.
- `context` - The route's context object, which is a merged union of:
  - Parent route context
  - This route's context as provided by the `beforeLoad` option
- `deps` - The object value returned from the `Route.loaderDeps` function. If `Route.loaderDeps` is not defined, an empty object will be provided instead.
- `location` - The current location
- `params` - The route's path params
- `parentMatchPromise` - `Promise<RouteMatch>` (`undefined` for the root route)
- `preload` - Boolean which is `true` when the route is being preloaded instead of loaded
- `route` - The route itself

Using these parameters, we can do a lot of cool things, but first, let's take a look at how we can control it and when the `loader` function is called.

## Consuming data from `loader`s

To consume data from a `loader`, use the `useLoaderData` hook defined on your Route object.

```tsx
const posts = Route.useLoaderData()
```

If you don't have ready access to your route object (i.e. you're deep in the component tree for the current route), you can use `getRouteApi` to access the same hook (as well as the other hooks on the Route object). This should be preferred over importing the Route object, which is likely to create circular dependencies.

```tsx
import { getRouteApi } from '@tanstack/react-router'

// in your component

const routeApi = getRouteApi('/posts')
const data = routeApi.useLoaderData()
```

## Dependency-based Stale-While-Revalidate Caching

TanStack Router provides a built-in Stale-While-Revalidate caching layer for route loaders that is keyed on the dependencies of a route:

- The route's fully parsed pathname
  - e.g. `/posts/1` vs `/posts/2`
- Any additional dependencies provided by the `loaderDeps` option
  - e.g. `loaderDeps: ({ search: { pageIndex, pageSize } }) => ({ pageIndex, pageSize })`

Using these dependencies as keys, TanStack Router will cache the data returned from a route's `loader` function and use it to fulfill subsequent requests for the same route match. This means that if a route's data is already in the cache, it will be returned immediately, then **potentially** be refetched in the background depending on the "freshness" of the data.

### Key options

To control router dependencies and "freshness", TanStack Router provides a plethora of options to control the keying and caching behavior of your route loaders. Let's take a look at them in the order that you are most likely to use them:

- `routeOptions.loaderDeps`
  - A function that supplies you the search params for a router and returns an object of dependencies for use in your `loader` function. When these deps changed from navigation to navigation, it will cause the route to reload regardless of `staleTime`s. The deps are compared using a deep equality check.
- `routeOptions.staleTime`
- `routerOptions.defaultStaleTime`
  - The number of milliseconds that a route's data should be considered fresh when attempting to load.
- `routeOptions.preloadStaleTime`
- `routerOptions.defaultPreloadStaleTime`
  - The number of milliseconds that a route's data should be considered fresh attempting to preload.
- `routeOptions.gcTime`
- `routerOptions.defaultGcTime`
  - The number of milliseconds that a route's data should be kept in the cache before being garbage collected.
- `routeOptions.shouldReload`
  - A function that receives the same `beforeLoad` and `loaderContext` parameters and returns a boolean indicating if the route should reload. This offers one more level of control over when a route should reload beyond `staleTime` and `loaderDeps` and can be used to implement patterns similar to Remix's `shouldLoad` option.

### ⚠️ Some Important Defaults

- By default, the `staleTime` is set to `0`, meaning that the route's data will always be considered stale and will always be reloaded in the background when the route is rematched.
- By default, a previously preloaded route is considered fresh for **30 seconds**. This means if a route is preloaded, then preloaded again within 30 seconds, the second preload will be ignored. This prevents unnecessary preloads from happening too frequently. **When a route is loaded normally, the standard `staleTime` is used.**
- By default, the `gcTime` is set to **30 minutes**, meaning that any route data that has not been accessed in 30 minutes will be garbage collected and removed from the cache.
- `router.invalidate()` will force all active routes to reload their loaders immediately and mark every cached route's data as stale.

### Using `loaderDeps` to access search params

Imagine a `/posts` route supports some pagination via search params `offset` and `limit`. For the cache to uniquely store this data, we need to access these search params via the `loaderDeps` function. By explicitly identifying them, each route match for `/posts` with different `offset` and `limit` won't get mixed up!

Once we have these deps in place, the route will always reload when the deps change.

```tsx
// /routes/posts.tsx
export const Route = createFileRoute('/posts')({
  loaderDeps: ({ search: { offset, limit } }) => ({ offset, limit }),
  loader: ({ deps: { offset, limit } }) =>
    fetchPosts({
      offset,
      limit,
    }),
})
```

### Using `staleTime` to control how long data is considered fresh

By default, `staleTime` for navigations is set to `0`ms (and 30 seconds for preloads) which means that the route's data will always be considered stale and will always be reloaded in the background when the route is matched and navigated to.

**This is a good default for most use cases, but you may find that some route data is more static or potentially expensive to load.** In these cases, you can use the `staleTime` option to control how long the route's data is considered fresh for navigations. Let's take a look at an example:

```tsx
// /routes/posts.tsx
export const Route = createFileRoute('/posts')({
  loader: () => fetchPosts(),
  // Consider the route's data fresh for 10 seconds
  staleTime: 10_000,
})
```

By passing `10_000` to the `staleTime` option, we are telling the router to consider the route's data fresh for 10 seconds. This means that if the user navigates to `/posts` from `/about` within 10 seconds of the last loader result, the route's data will not be reloaded. If the user then navigates to `/posts` from `/about` after 10 seconds, the route's data will be reloaded **in the background**.

## Turning off stale-while-revalidate caching

To disable stale-while-revalidate caching for a route, set the `staleTime` option to `Infinity`:

```tsx
// /routes/posts.tsx
export const Route = createFileRoute('/posts')({
  loader: () => fetchPosts(),
  staleTime: Infinity,
})
```

You can even turn this off for all routes by setting the `defaultStaleTime` option on the router:

```tsx
const router = createRouter({
  routeTree,
  defaultStaleTime: Infinity,
})
```

## Using `shouldReload` and `gcTime` to opt-out of caching

Similar to Remix's default functionality, you may want to configure a route to only load on entry or when critical loader deps change. You can do this by using the `gcTime` option combined with the `shouldReload` option, which accepts either a `boolean` or a function that receives the same `beforeLoad` and `loaderContext` parameters and returns a boolean indicating if the route should reload.

```tsx
// /routes/posts.tsx
export const Route = createFileRoute('/posts')({
  loaderDeps: ({ search: { offset, limit } }) => ({ offset, limit }),
  loader: ({ deps }) => fetchPosts(deps),
  // Do not cache this route's data after it's unloaded
  gcTime: 0,
  // Only reload the route when the user navigates to it or when deps change
  shouldReload: false,
})
```

### Opting out of caching while still preloading

Even though you may opt-out of short-term caching for your route data, you can still get the benefits of preloading! With the above configuration, preloading will still "just work" with the default `preloadGcTime`. This means that if a route is preloaded, then navigated to, the route's data will be considered fresh and will not be reloaded.

To opt out of preloading, don't turn it on via the `routerOptions.defaultPreload` or `routeOptions.preload` options.

## Passing all loader events to an external cache

We break down this use case in the [External Data Loading](../external-data-loading.md) page, but if you'd like to use an external cache like TanStack Query, you can do so by passing all loader events to your external cache. As long as you are using the defaults, the only change you'll need to make is to set the `defaultPreloadStaleTime` option on the router to `0`:

```tsx
const router = createRouter({
  routeTree,
  defaultPreloadStaleTime: 0,
})
```

This will ensure that every preload, load, and reload event will trigger your `loader` functions, which can then be handled and deduped by your external cache.

## Using Router Context

The `context` argument passed to the `loader` function is an object containing a merged union of:

- Parent route context
- This route's context as provided by the `beforeLoad` option

Starting at the very top of the router, you can pass an initial context to the router via the `context` option. This context will be available to all routes in the router and get copied and extended by each route as they are matched. This happens by passing a context to a route via the `beforeLoad` option. This context will be available to all the route's child routes. The resulting context will be available to the route's `loader` function.

In this example, we'll create a function in our route context to fetch posts, then use it in our `loader` function.

> 🧠 Context is a powerful tool for dependency injection. You can use it to inject services, hooks, and other objects into your router and routes. You can also additively pass data down the route tree at every route using a route's `beforeLoad` option.

- `/utils/fetchPosts.tsx`

```tsx
export const fetchPosts = async () => {
  const res = await fetch(`/api/posts?page=${pageIndex}`)
  if (!res.ok) throw new Error('Failed to fetch posts')
  return res.json()
}
```

- `/routes/__root.tsx`

```tsx
import { createRootRouteWithContext } from '@tanstack/react-router'

// Create a root route using the createRootRouteWithContext<{...}>() function and pass it whatever types you would like to be available in your router context.
export const Route = createRootRouteWithContext<{
  fetchPosts: typeof fetchPosts
}>()() // NOTE: the double call is on purpose, since createRootRouteWithContext is a factory ;)
```

- `/routes/posts.tsx`

```tsx
import { createFileRoute } from '@tanstack/react-router'

// Notice how our postsRoute references context to get our fetchPosts function
// This can be a powerful tool for dependency injection across your router
// and routes.
export const Route = createFileRoute('/posts')({
  loader: ({ context: { fetchPosts } }) => fetchPosts(),
})
```

- `/router.tsx`

```tsx
import { routeTree } from './routeTree.gen'

// Use your routerContext to create a new router
// This will require that you fullfil the type requirements of the routerContext
const router = createRouter({
  routeTree,
  context: {
    // Supply the fetchPosts function to the router context
    fetchPosts,
  },
})
```

## Using Path Params

To use path params in your `loader` function, access them via the `params` property on the function's parameters. Here's an example:

```tsx
// routes/posts.$postId.tsx
export const Route = createFileRoute('/posts/$postId')({
  loader: ({ params: { postId } }) => fetchPostById(postId),
})
```

## Using Route Context

Passing down global context to your router is great, but what if you want to provide context that is specific to a route? This is where the `beforeLoad` option comes in. The `beforeLoad` option is a function that runs right before attempting to load a route and receives the same parameters as `loader`. Beyond its ability to redirect potential matches, block loader requests, etc, it can also return an object that will be merged into the route's context. Let's take a look at an example where we inject some data into our route context via the `beforeLoad` option:

```tsx
// /routes/posts.tsx
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/posts')({
  // Pass the fetchPosts function to the route context
  beforeLoad: () => ({
    fetchPosts: () => console.info('foo'),
  }),
  loader: ({ context: { fetchPosts } }) => {
    console.info(fetchPosts()) // 'foo'

    // ...
  },
})
```

## Using Search Params in Loaders

> ❓ But wait Tanner... where the heck are my search params?!

You might be here wondering why `search` isn't directly available in the `loader` function's parameters. We've purposefully designed it this way to help you succeed. Let's take a look at why:

- Search Parameters being used in a loader function are a very good indicator that those search params should also be used to uniquely identify the data being loaded. For example, you may have a route that uses a search param like `pageIndex` that uniquely identifies the data held inside of the route match. Or, imagine a `/users/user` route that uses the search param `userId` to identify a specific user in your application, you might model your url like this: `/users/user?userId=123`. This means that your `user` route would need some extra help to identify a specific user.
- Directly accessing search params in a loader function can lead to bugs in caching and preloading where the data being loaded is not unique to the current URL pathname and search params. For example, you might ask your `/posts` route to preload page 2's results, but without the distinction of pages in your route configuration, you will end up fetching, storing and displaying page 2's data on your `/posts` or `?page=1` screen instead of it preloading in the background!
- Placing a threshold between search parameters and the loader function allows the router to understand your dependencies and reactivity.

```tsx
// /routes/users.user.tsx
export const Route = createFileRoute('/users/user')({
  validateSearch: (search) =>
    search as {
      userId: string
    },
  loaderDeps: ({ search: { userId } }) => ({
    userId,
  }),
  loader: async ({ deps: { userId } }) => getUser(userId),
})
```

### Accessing Search Params via `routeOptions.loaderDeps`

```tsx
// /routes/posts.tsx
export const Route = createFileRoute('/posts')({
  // Use zod to validate and parse the search params
  validateSearch: z.object({
    offset: z.number().int().nonnegative().catch(0),
  }),
  // Pass the offset to your loader deps via the loaderDeps function
  loaderDeps: ({ search: { offset } }) => ({ offset }),
  // Use the offset from context in the loader function
  loader: async ({ deps: { offset } }) =>
    fetchPosts({
      offset,
    }),
})
```

## Using the Abort Signal

The `abortController` property of the `loader` function is an [AbortController](https://developer.mozilla.org/en-US/docs/Web/API/AbortController). Its signal is cancelled when the route is unloaded or when the `loader` call becomes outdated. This is useful for cancelling network requests when the route is unloaded or when the route's params change. Here is an example using it with a fetch call:

```tsx
// routes/posts.tsx
export const Route = createFileRoute('/posts')({
  loader: ({ abortController }) =>
    fetchPosts({
      // Pass this to an underlying fetch call or anything that supports signals
      signal: abortController.signal,
    }),
})
```

## Using the `preload` flag

The `preload` property of the `loader` function is a boolean which is `true` when the route is being preloaded instead of loaded. Some data loading libraries may handle preloading differently than a standard fetch, so you may want to pass `preload` to your data loading library, or use it to execute the appropriate data loading logic:

```tsx
// routes/posts.tsx
export const Route = createFileRoute('/posts')({
  loader: async ({ preload }) =>
    fetchPosts({
      maxAge: preload ? 10_000 : 0, // Preloads should hang around a bit longer
    }),
})
```

## Handling Slow Loaders

Ideally most route loaders can resolve their data within a short moment, removing the need to render a placeholder spinner and simply rely on suspense to render the next route when it's completely ready. When critical data that is required to render a route's component is slow though, you have 2 options:

- Split up your fast and slow data into separate promises and `defer` the slow data until after the fast data is loaded (see the [Deferred Data Loading](../deferred-data-loading.md) guide).
- Show a pending component after an optimistic suspense threshold until all of the data is ready (See below).

## Showing a pending component

**By default, TanStack Router will show a pending component for loaders that take longer than 1 second to resolve.** This is an optimistic threshold that can be configured via:

- `routeOptions.pendingMs` or
- `routerOptions.defaultPendingMs`

When the pending time threshold is exceeded, the router will render the `pendingComponent` option of the route, if configured.

## Avoiding Pending Component Flash

If you're using a pending component, the last thing you want is for your pending time threshold to be met, then have your data resolve immediately after, resulting in a jarring flash of your pending component. To avoid this, **TanStack Router by default will show your pending component for at least 500ms**. This is an optimistic threshold that can be configured via:

- `routeOptions.pendingMinMs` or
- `routerOptions.defaultPendingMinMs`

## Handling Errors

TanStack Router provides a few ways to handle errors that occur during the route loading lifecycle. Let's take a look at them.

### Handling Errors with `routeOptions.onError`

The `routeOptions.onError` option is a function that is called when an error occurs during the route loading.

```tsx
// routes/posts.tsx
export const Route = createFileRoute('/posts')({
  loader: () => fetchPosts(),
  onError: ({ error }) => {
    // Log the error
    console.error(error)
  },
})
```

### Handling Errors with `routeOptions.onCatch`

The `routeOptions.onCatch` option is a function that is called whenever an error was caught by the router's CatchBoundary.

```tsx
// routes/posts.tsx
export const Route = createFileRoute('/posts')({
  onCatch: ({ error, errorInfo }) => {
    // Log the error
    console.error(error)
  },
})
```

### Handling Errors with `routeOptions.errorComponent`

The `routeOptions.errorComponent` option is a component that is rendered when an error occurs during the route loading or rendering lifecycle. It is rendered with the following props:

- `error` - The error that occurred
- `reset` - A function to reset the internal `CatchBoundary`

```tsx
// routes/posts.tsx
export const Route = createFileRoute('/posts')({
  loader: () => fetchPosts(),
  errorComponent: ({ error }) => {
    // Render an error message
    return <div>{error.message}</div>
  },
})
```

The `reset` function can be used to allow the user to retry rendering the error boundaries normal children:

```tsx
// routes/posts.tsx
export const Route = createFileRoute('/posts')({
  loader: () => fetchPosts(),
  errorComponent: ({ error, reset }) => {
    return (
      <div>
        {error.message}
        <button
          onClick={() => {
            // Reset the router error boundary
            reset()
          }}
        >
          retry
        </button>
      </div>
    )
  },
})
```

If the error was the result of a route load, you should instead call `router.invalidate()`, which will coordinate both a router reload and an error boundary reset:

```tsx
// routes/posts.tsx
export const Route = createFileRoute('/posts')({
  loader: () => fetchPosts(),
  errorComponent: ({ error, reset }) => {
    const router = useRouter()

    return (
      <div>
        {error.message}
        <button
          onClick={() => {
            // Invalidate the route to reload the loader, which will also reset the error boundary
            router.invalidate()
          }}
        >
          retry
        </button>
      </div>
    )
  },
})
```

### Using the default `ErrorComponent`

TanStack Router provides a default `ErrorComponent` that is rendered when an error occurs during the route loading or rendering lifecycle. If you choose to override your routes' error components, it's still wise to always fall back to rendering any uncaught errors with the default `ErrorComponent`:

```tsx
// routes/posts.tsx
import { createFileRoute, ErrorComponent } from '@tanstack/react-router'

export const Route = createFileRoute('/posts')({
  loader: () => fetchPosts(),
  errorComponent: ({ error }) => {
    if (error instanceof MyCustomError) {
      // Render a custom error message
      return <div>{error.message}</div>
    }

    // Fallback to the default ErrorComponent
    return <ErrorComponent error={error} />
  },
})
```

# Data Mutations

Since TanStack router does not store or cache data, it's role in data mutation is slim to none outside of reacting to potential URL side-effects from external mutation events. That said, we've compiled a list of mutation-related features you might find useful and libraries that implement them.

Look for and use mutation utilities that support:

- Handling and caching submission state
- Providing both local and global optimistic UI support
- Built-in hooks to wire up invalidation (or automatically support it)
- Handling multiple in-flight mutations at once
- Organizing mutation state as a globally accessible resource
- Submission state history and garbage collection

Some suggested libraries:

- [TanStack Query](https://tanstack.com/query/latest/docs/react/guides/mutations)
- [SWR](https://swr.vercel.app/)
- [RTK Query](https://redux-toolkit.js.org/rtk-query/overview)
- [urql](https://formidable.com/open-source/urql/)
- [Relay](https://relay.dev/)
- [Apollo](https://www.apollographql.com/docs/react/)

Or, even...

- [Zustand](https://zustand-demo.pmnd.rs/)
- [Jotai](https://jotai.org/)
- [Recoil](https://recoiljs.org/)
- [Redux](https://redux.js.org/)

Similar to data fetching, mutation state isn't a one-size-fits-all solution, so you'll need to pick a solution that fits your needs and your team's needs. We recommend trying out a few different solutions and seeing what works best for you.

> ⚠️ Still here? Submission state is an interesting topic when it comes to persistence. Do you keep every mutation around forever? How do you know when to get rid of it? What if the user navigates away from the screen and then back? Let's dig in!

## Invalidating TanStack Router after a mutation

TanStack Router comes with short-term caching built-in. So even though we're not storing any data after a route match is unmounted, there is a high probability that if any mutations are made related to the data stored in the Router, the current route matches' data could become stale.

When mutations related to loader data are made, we can use `router.invalidate` to force the router to reload all of the current route matches:

```tsx
const router = useRouter()

const addTodo = async (todo: Todo) => {
  try {
    await api.addTodo()
    router.invalidate()
  } catch {
    //
  }
}
```

Invalidating all of the current route matches happens in the background, so existing data will continue to be served until the new data is ready, just as if you were navigating to a new route.

If you want to await the invalidation until all loaders have finished, pass `{sync: true}` into `router.invalidate`:

```tsx
const router = useRouter()

const addTodo = async (todo: Todo) => {
  try {
    await api.addTodo()
    await router.invalidate({ sync: true })
  } catch {
    //
  }
}
```

## Long-term mutation State

Regardless of the mutation library used, mutations often create state related to their submission. While most mutations are set-and-forget, some mutation states are more long-lived, either to support optimistic UI or to provide feedback to the user about the status of their submissions. Most state managers will correctly keep this submission state around and expose it to make it possible to show UI elements like loading spinners, success messages, error messages, etc.

Let's consider the following interactions:

- User navigates to the `/posts/123/edit` screen to edit a post
- User edits the `123` post and upon success, sees a success message below the editor that the post was updated
- User navigates to the `/posts` screen
- User navigates back to the `/posts/123/edit` screen again

Without notifying your mutation management library about the route change, it's possible that your submission state could still be around and your user would still see the **"Post updated successfully"** message when they return to the previous screen. This is not ideal. Obviously, our intent wasn't to keep this mutation state around forever, right?!

## Using mutation keys

Hopefully and hypothetically, the easiest way is for your mutation library to support a keying mechanism that will allow your mutations's state to be reset when the key changes:

```tsx
const routeApi = getRouteApi('/room/$roomId/chat')

function ChatRoom() {
  const { roomId } = routeApi.useParams()

  const sendMessageMutation = useCoolMutation({
    fn: sendMessage,
    // Clear the mutation state when the roomId changes
    // including any submission state
    key: ['sendMessage', roomId],
  })

  // Fire off a bunch of messages
  const test = () => {
    sendMessageMutation.mutate({ roomId, message: 'Hello!' })
    sendMessageMutation.mutate({ roomId, message: 'How are you?' })
    sendMessageMutation.mutate({ roomId, message: 'Goodbye!' })
  }

  return (
    <>
      {sendMessageMutation.submissions.map((submission) => {
        return (
          <div>
            <div>{submission.status}</div>
            <div>{submission.message}</div>
          </div>
        )
      })}
    </>
  )
}
```

## Using the `router.subscribe` method

For libraries that don't have a keying mechanism, we'll likely need to manually reset the mutation state when the user navigates away from the screen. To solve this, we can use TanStack Router's `invalidate` and `subscribe` method to clear mutation states when the user is no longer in need of them.

The `router.subscribe` method is a function that subscribes a callback to various router events. The event in particular that we'll use here is the `onResolved` event. It's important to understand that this event is fired when the location path is _changed (not just reloaded) and has finally resolved_.

This is a great place to reset your old mutation states. Here's an example:

```tsx
const router = createRouter()
const coolMutationCache = createCoolMutationCache()

const unsubscribeFn = router.subscribe('onResolved', () => {
  // Reset mutation states when the route changes
  coolMutationCache.clear()
})
```

# Deferred Data Loading

TanStack Router is designed to run loaders in parallel and wait for all of them to resolve before rendering the next route. This is great most of the time, but occasionally, you may want to show the user something sooner while the rest of the data loads in the background.

Deferred data loading is a pattern that allows the router to render the next location's critical data/markup while slower, non-critical route data is resolved in the background. This process works on both the client and server (via streaming) and is a great way to improve the perceived performance of your application.

If you are using a library like [TanStack Query](https://tanstack.com/query/latest) or any other data fetching library, then deferred data loading works a bit differently. Skip ahead to the [Deferred Data Loading with External Libraries](#deferred-data-loading-with-external-libraries) section for more information.

## Deferred Data Loading with `Await`

To defer slow or non-critical data, return an **unawaited/unresolved** promise anywhere in your loader response:

```tsx
// src/routes/posts.$postId.tsx
import { createFileRoute, defer } from '@tanstack/react-router'

export const Route = createFileRoute('/posts/$postId')({
  loader: async () => {
    // Fetch some slower data, but do not await it
    const slowDataPromise = fetchSlowData()

    // Fetch and await some data that resolves quickly
    const fastData = await fetchFastData()

    return {
      fastData,
      deferredSlowData: slowDataPromise,
    }
  },
})
```

As soon as any awaited promises are resolved, the next route will begin rendering while the deferred promises continue to resolve.

In the component, deferred promises can be resolved and utilized using the `Await` component:

```tsx
// src/routes/posts.$postId.tsx
import { createFileRoute, Await } from '@tanstack/react-router'

export const Route = createFileRoute('/posts/$postId')({
  // ...
  component: PostIdComponent,
})

function PostIdComponent() {
  const { deferredSlowData, fastData } = Route.useLoaderData()

  // do something with fastData

  return (
    <Await promise={deferredSlowData} fallback={<div>Loading...</div>}>
      {(data) => {
        return <div>{data}</div>
      }}
    </Await>
  )
}
```

> [!TIP]
> If your component is code-split, you can use the [getRouteApi function](../code-splitting.md#manually-accessing-route-apis-in-other-files-with-the-getrouteapi-helper) to avoid having to import the `Route` configuration to get access to the typed `useLoaderData()` hook.

The `Await` component resolves the promise by triggering the nearest suspense boundary until it is resolved, after which it renders the component's `children` as a function with the resolved data.

If the promise is rejected, the `Await` component will throw the serialized error, which can be caught by the nearest error boundary.

[//]: # 'DeferredWithAwaitFinalTip'

> [!TIP]
> In React 19, you can use the `use()` hook instead of `Await`

[//]: # 'DeferredWithAwaitFinalTip'

## Deferred Data Loading with External libraries

When your strategy for fetching information for the route relies on [External Data Loading](../external-data-loading.md) with an external library like [TanStack Query](https://tanstack.com/query), deferred data loading works a bit differently, as the library handles the data fetching and caching for you outside of TanStack Router.

So, instead of using `defer` and `Await`, you'll instead want to use the Route's `loader` to kick off the data fetching and then use the library's hooks to access the data in your components.

```tsx
// src/routes/posts.$postId.tsx
import { createFileRoute } from '@tanstack/react-router'
import { slowDataOptions, fastDataOptions } from '~/api/query-options'

export const Route = createFileRoute('/posts/$postId')({
  loader: async ({ context: { queryClient } }) => {
    // Kick off the fetching of some slower data, but do not await it
    queryClient.prefetchQuery(slowDataOptions())

    // Fetch and await some data that resolves quickly
    await queryClient.ensureQueryData(fastDataOptions())
  },
})
```

Then in your component, you can use the library's hooks to access the data:

```tsx
// src/routes/posts.$postId.tsx
import { createFileRoute } from '@tanstack/react-router'
import { useSuspenseQuery } from '@tanstack/react-query'
import { slowDataOptions, fastDataOptions } from '~/api/query-options'

export const Route = createFileRoute('/posts/$postId')({
  // ...
  component: PostIdComponent,
})

function PostIdComponent() {
  const fastData = useSuspenseQuery(fastDataOptions())

  // do something with fastData

  return (
    <Suspense fallback={<div>Loading...</div>}>
      <SlowDataComponent />
    </Suspense>
  )
}

function SlowDataComponent() {
  const data = useSuspenseQuery(slowDataOptions())

  return <div>{data}</div>
}
```

## Caching and Invalidation

Streamed promises follow the same lifecycle as the loader data they are associated with. They can even be preloaded!

[//]: # 'SSRContent'

## SSR & Streaming Deferred Data

**Streaming requires a server that supports it and for TanStack Router to be configured to use it properly.**

Please read the entire [Streaming SSR Guide](../ssr.md#streaming-ssr) for step by step instructions on how to set up your server for streaming.

## SSR Streaming Lifecycle

The following is a high-level overview of how deferred data streaming works with TanStack Router:

- Server
  - Promises are marked and tracked as they are returned from route loaders
  - All loaders resolve and any deferred promises are serialized and embedded into the html
  - The route begins to render
  - Deferred promises rendered with the `<Await>` component trigger suspense boundaries, allowing the server to stream html up to that point
- Client
  - The client receives the initial html from the server
  - `<Await>` components suspend with placeholder promises while they wait for their data to resolve on the server
- Server
  - As deferred promises resolve, their results (or errors) are serialized and streamed to the client via an inline script tag
  - The resolved `<Await>` components and their suspense boundaries are resolved and their resulting HTML is streamed to the client along with their dehydrated data
- Client
  - The suspended placeholder promises within `<Await>` are resolved with the streamed data/error responses and either render the result or throw the error to the nearest error boundary

[//]: # 'SSRContent'

# Document Head Management

Document head management is the process of managing the head, title, meta, link, and script tags of a document and TanStack Router provides a robust way to manage the document head for full-stack applications that use Start and for single-page applications that use `@tanstack/react-router`. It provides:

- Automatic deduping of `title` and `meta` tags
- Automatic loading/unloading of tags based on route visibility
- A composable way to merge `title` and `meta` tags from nested routes

For full-stack applications that use Start, and even for single-page applications that use `@tanstack/react-router`, managing the document head is a crucial part of any application for the following reasons:

- SEO
- Social media sharing
- Analytics
- CSS and JS loading/unloading

To manage the document head, it's required that you render both the `<HeadContent />` and `<Scripts />` components and use the `routeOptions.head` property to manage the head of a route, which returns an object with `title`, `meta`, `links`, `styles`, and `scripts` properties.

## Managing the Document Head

```tsx
export const Route = createRootRoute({
  head: () => ({
    meta: [
      {
        name: 'description',
        content: 'My App is a web application',
      },
      {
        title: 'My App',
      },
    ],
    links: [
      {
        rel: 'icon',
        href: '/favicon.ico',
      },
    ],
    styles: [
      {
        media: 'all and (max-width: 500px)',
        children: `p {
                  color: blue;
                  background-color: yellow;
                }`,
      },
    ],
    scripts: [
      {
        src: 'https://www.google-analytics.com/analytics.js',
      },
    ],
  }),
})
```

### Deduping

Out of the box, TanStack Router will dedupe `title` and `meta` tags, preferring the **last** occurrence of each tag found in nested routes.

- `title` tags defined in nested routes will override a `title` tag defined in a parent route (but you can compose them together, which is covered in a future section of this guide)
- `meta` tags with the same `name` or `property` will be overridden by the last occurrence of that tag found in nested routes

### `<HeadContent />`

The `<HeadContent />` component is **required** to render the head, title, meta, link, and head-related script tags of a document.

It should be **rendered either in the `<head>` tag of your root layout or as high up in the component tree as possible** if your application doesn't or can't manage the `<head>` tag.

### Start/Full-Stack Applications

```tsx
import { HeadContent } from '@tanstack/react-router'

export const Route = createRootRoute({
  component: () => (
    <html>
      <head>
        <HeadContent />
      </head>
      <body>
        <Outlet />
      </body>
    </html>
  ),
})
```

### Single-Page Applications

First, remove the `<title>` tag from the the index.html if you have set any.

```tsx
import { HeadContent } from '@tanstack/react-router'

const rootRoute = createRootRoute({
  component: () => (
    <>
      <HeadContent />
      <Outlet />
    </>
  ),
})
```

## Managing Body Scripts

In addition to scripts that can be rendered in the `<head>` tag, you can also render scripts in the `<body>` tag using the `routeOptions.scripts` property. This is useful for loading scripts (even inline scripts) that require the DOM to be loaded, but before the main entry point of your application (which includes hydration if you're using Start or a full-stack implementation of TanStack Router).

To do this, you must:

- Use the `scripts` property of the `routeOptions` object
- [Render the `<Scripts />` component](#scripts)

```tsx
export const Route = createRootRoute({
  scripts: () => [
    {
      children: 'console.log("Hello, world!")',
    },
  ],
})
```

### `<Scripts />`

The `<Scripts />` component is **required** to render the body scripts of a document. It should be rendered either in the `<body>` tag of your root layout or as high up in the component tree as possible if your application doesn't or can't manage the `<body>` tag.

### Example

```tsx
import { createFileRoute, Scripts } from '@tanstack/react-router'
export const Router = createFileRoute('/')({
  component: () => (
    <html>
      <head />
      <body>
        <Outlet />
        <Scripts />
      </body>
    </html>
  ),
})
```

```tsx
import { Scripts, createRootRoute } from '@tanstack/react-router'

export const Route = createRootRoute({
  component: () => (
    <>
      <Outlet />
      <Scripts />
    </>
  ),
})
```

# External Data Loading

> [!IMPORTANT]
> This guide is geared towards external state management libraries and their integration with TanStack Router for data fetching, ssr, hydration/dehydration and streaming. If you haven't read the standard [Data Loading](../data-loading.md) guide, please do so first.

## To **Store** or to **Coordinate**?

While Router is very capable of storing and managing most data needs out of the box, sometimes you just might want something more robust!

Router is designed to be a perfect **coordinator** for external data fetching and caching libraries. This means that you can use any data fetching/caching library you want, and the router will coordinate the loading of your data in a way that aligns with your users' navigation and expectations of freshness.

## What data fetching libraries are supported?

Any data fetching library that supports asynchronous promises can be used with TanStack Router. This includes:

- [TanStack Query](https://tanstack.com/query/latest/docs/react/overview)
- [SWR](https://swr.vercel.app/)
- [RTK Query](https://redux-toolkit.js.org/rtk-query/overview)
- [urql](https://formidable.com/open-source/urql/)
- [Relay](https://relay.dev/)
- [Apollo](https://www.apollographql.com/docs/react/)

Or, even...

- [Zustand](https://zustand-demo.pmnd.rs/)
- [Jotai](https://jotai.org/)
- [Recoil](https://recoiljs.org/)
- [Redux](https://redux.js.org/)

Literally any library that **can return a promise and read/write data** can be integrated.

## Using Loaders to ensure data is loaded

The easiest way to integrate external caching/data library into Router is to use `route.loader`s to ensure that the data required inside of a route has been loaded and is ready to be displayed.

> ⚠️ BUT WHY? It's very important to preload your critical render data in the loader for a few reasons:
>
> - No "flash of loading" states
> - No waterfall data fetching, caused by component based fetching
> - Better for SEO. If your data is available at render time, it will be indexed by search engines.

Here is a naive illustration (don't do this) of using a Route's `loader` option to seed the cache for some data:

```tsx
// src/routes/posts.tsx
let postsCache = []

export const Route = createFileRoute('/posts')({
  loader: async () => {
    postsCache = await fetchPosts()
  },
  component: () => {
    return (
      <div>
        {postsCache.map((post) => (
          <Post key={post.id} post={post} />
        ))}
      </div>
    )
  },
})
```

This example is **obviously flawed**, but illustrates the point that you can use a route's `loader` option to seed your cache with data. Let's take a look at a more realistic example using TanStack Query.

- Replace `fetchPosts` with your preferred data fetching library's prefetching API
- Replace `postsCache` with your preferred data fetching library's read-or-fetch API or hook

## A more realistic example using TanStack Query

Let's take a look at a more realistic example using TanStack Query.

```tsx
// src/routes/posts.tsx
const postsQueryOptions = queryOptions({
  queryKey: ['posts'],
  queryFn: () => fetchPosts(),
})

export const Route = createFileRoute('/posts')({
  // Use the `loader` option to ensure that the data is loaded
  loader: () => queryClient.ensureQueryData(postsQueryOptions),
  component: () => {
    // Read the data from the cache and subscribe to updates
    const {
      data: { posts },
    } = useSuspenseQuery(postsQueryOptions)

    return (
      <div>
        {posts.map((post) => (
          <Post key={post.id} post={post} />
        ))}
      </div>
    )
  },
})
```

### Error handling with TanStack Query

When an error occurs while using `suspense` with `TanStack Query`, you need to let queries know that you want to try again when re-rendering. This can be done by using the `reset` function provided by the `useQueryErrorResetBoundary` hook. You can invoke this function in an effect as soon as the error component mounts. This will make sure that the query is reset and will try to fetch data again when the route component is rendered again. This will also cover cases where users navigate away from the route instead of clicking the `retry` button.

```tsx
export const Route = createFileRoute('/')({
  loader: () => queryClient.ensureQueryData(postsQueryOptions),
  errorComponent: ({ error, reset }) => {
    const router = useRouter()
    const queryErrorResetBoundary = useQueryErrorResetBoundary()

    useEffect(() => {
      // Reset the query error boundary
      queryErrorResetBoundary.reset()
    }, [queryErrorResetBoundary])

    return (
      <div>
        {error.message}
        <button
          onClick={() => {
            // Invalidate the route to reload the loader, and reset any router error boundaries
            router.invalidate()
          }}
        >
          retry
        </button>
      </div>
    )
  },
})
```

## SSR Dehydration/Hydration

Tools that are able can integrate with TanStack Router's convenient Dehydration/Hydration APIs to shuttle dehydrated data between the server and client and rehydrate it where needed. Let's go over how to do this with both 3rd party critical data and 3rd party deferred data.

## Critical Dehydration/Hydration

**For critical data needed for the first render/paint**, TanStack Router supports **`dehydrate` and `hydrate`** options when configuring the `Router`. These callbacks are functions that are automatically called on the server and client when the router dehydrates and hydrates normally and allow you to augment the dehydrated data with your own data.

The `dehydrate` function can return any serializable JSON data which will get merged and injected into the dehydrated payload that is sent to the client.

For example, let's dehydrate and hydrate a TanStack Query `QueryClient` so that our data we fetched on the server will be available for hydration on the client.

```tsx
// src/router.tsx

export function createRouter() {
  // Make sure you create your loader client or similar data
  // stores inside of your `createRouter` function. This ensures
  // that your data stores are unique to each request and
  // always present on both server and client.
  const queryClient = new QueryClient()

  return createRouter({
    routeTree,
    // Optionally provide your loaderClient to the router context for
    // convenience (you can provide anything you want to the router
    // context!)
    context: {
      queryClient,
    },
    // On the server, dehydrate the loader client so the router
    // can serialize it and send it to the client for us
    dehydrate: () => {
      return {
        queryClientState: dehydrate(queryClient),
      }
    },
    // On the client, hydrate the loader client with the data
    // we dehydrated on the server
    hydrate: (dehydrated) => {
      hydrate(queryClient, dehydrated.queryClientState)
    },
    // Optionally, we can use `Wrap` to wrap our router in the loader client provider
    Wrap: ({ children }) => {
      return <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>
    },
  })
}
```

# History Types

While it's not required to know the `@tanstack/history` API itself to use TanStack Router, it's a good idea to understand how it works. Under the hood, TanStack Router requires and uses a `history` abstraction to manage the routing history.

If you don't create a history instance, a browser-oriented instance of this API is created for you when the router is initialized. If you need a special history API type, You can use the `@tanstack/history` package to create your own:

- `createBrowserHistory`: The default history type.
- `createHashHistory`: A history type that uses a hash to track history.
- `createMemoryHistory`: A history type that keeps the history in memory.

Once you have a history instance, you can pass it to the `Router` constructor:

```ts
import { createMemoryHistory, createRouter } from '@tanstack/react-router'

const memoryHistory = createMemoryHistory({
  initialEntries: ['/'], // Pass your initial url
})

const router = createRouter({ routeTree, history: memoryHistory })
```

## Browser Routing

The `createBrowserHistory` is the default history type. It uses the browser's history API to manage the browser history.

## Hash Routing

Hash routing can be helpful if your server doesn't support rewrites to index.html for HTTP requests (among other environments that don't have a server).

```ts
import { createHashHistory, createRouter } from '@tanstack/react-router'

const hashHistory = createHashHistory()

const router = createRouter({ routeTree, history: hashHistory })
```

## Memory Routing

Memory routing is useful in environments that are not a browser or when you do not want components to interact with the URL.

```ts
import { createMemoryHistory, createRouter } from '@tanstack/react-router'

const memoryHistory = createMemoryHistory({
  initialEntries: ['/'], // Pass your initial url
})

const router = createRouter({ routeTree, history: memoryHistory })
```

Refer to the [SSR Guide](../ssr.md#server-history) for usage on the server for server-side rendering.

# Link Options

You may want to reuse options that are intended to be passed to `Link`, `redirect` or `navigate`. In which case you may decide an object literal is a good way to represent options passed to `Link`.

```tsx
const dashboardLinkOptions = {
  to: '/dashboard',
  search: { search: '' },
}

function DashboardComponent() {
  return <Link {...dashboardLinkOptions} />
}
```

There are a few problems here. `dashboardLinkOptions.to` is inferred as `string` which by default will resolve to every route when passed to `Link`, `navigate` or `redirect` (this particular issue could be fixed by `as const`). The other issue here is we do not know `dashboardLinkOptions` even passes the type checker until it is spread into `Link`. We could very easily create incorrect navigation options and only when the options are spread into `Link` do we know there is a type error.

### Using `linkOptions` function to create re-usable options

`linkOptions` is a function which type checks an object literal and returns the inferred input as is. This provides type safety on options exactly like `Link` before it is used allowing for easier maintenance and re-usability. Our above example using `linkOptions` looks like this:

```tsx
const dashboardLinkOptions = linkOptions({
  to: '/dashboard',
  search: { search: '' },
})

function DashboardComponent() {
  return <Link {...dashboardLinkOptions} />
}
```

This allows eager type checking of `dashboardLinkOptions` which can then be re-used anywhere

```tsx
const dashboardLinkOptions = linkOptions({
  to: '/dashboard',
  search: { search: '' },
})

export const Route = createFileRoute('/dashboard')({
  component: DashboardComponent,
  validateSearch: (input) => ({ search: input.search }),
  beforeLoad: () => {
    // can used in redirect
    throw redirect(dashboardLinkOptions)
  },
})

function DashboardComponent() {
  const navigate = useNavigate()

  return (
    <div>
      {/** can be used in navigate */}
      <button onClick={() => navigate(dashboardLinkOptions)} />

      {/** can be used in Link */}
      <Link {...dashboardLinkOptions} />
    </div>
  )
}
```

### An array of `linkOptions`

When creating navigation you might loop over an array to construct a navigation bar. In which case `linkOptions` can be used to type check an array of object literals which are intended for `Link` props

```tsx
const options = linkOptions([
  {
    to: '/dashboard',
    label: 'Summary',
    activeOptions: { exact: true },
  },
  {
    to: '/dashboard/invoices',
    label: 'Invoices',
  },
  {
    to: '/dashboard/users',
    label: 'Users',
  },
])

function DashboardComponent() {
  return (
    <>
      <div className="flex items-center border-b">
        <h2 className="text-xl p-2">Dashboard</h2>
      </div>

      <div className="flex flex-wrap divide-x">
        {options.map((option) => {
          return (
            <Link {...option} key={option.to} activeProps={{ className: `font-bold` }} className="p-2">
              {option.label}
            </Link>
          )
        })}
      </div>
      <hr />

      <Outlet />
    </>
  )
}
```

The input of `linkOptions` is inferred and returned, as shown with the use of `label` as this does not exist on `Link` props

# Navigation Blocking

Navigation blocking is a way to prevent navigation from happening. This is typical if a user attempts to navigate while they:

- Have unsaved changes
- Are in the middle of a form
- Are in the middle of a payment

In these situations, a prompt or custom UI should be shown to the user to confirm they want to navigate away.

- If the user confirms, navigation will continue as normal
- If the user cancels, all pending navigations will be blocked

## How does navigation blocking work?

Navigation blocking adds one or more layers of "blockers" to the entire underlying history API. If any blockers are present, navigation will be paused via one of the following ways:

- Custom UI
  - If the navigation is triggered by something we control at the router level, we can allow you to perform any task or show any UI you'd like to the user to confirm the action. Each blocker's `blocker` function will be asynchronously and sequentially executed. If any blocker function resolves or returns `true`, the navigation will be allowed and all other blockers will continue to do the same until all blockers have been allowed to proceed. If any single blocker resolves or returns `false`, the navigation will be canceled and the rest of the `blocker` functions will be ignored.
- The `onbeforeunload` event
  - For page events that we cannot control directly, we rely on the browser's `onbeforeunload` event. If the user attempts to close the tab or window, refresh, or "unload" the page assets in any way, the browser's generic "Are you sure you want to leave?" dialog will be shown. If the user confirms, all blockers will be bypassed and the page will unload. If the user cancels, the unload will be cancelled, and the page will remain as is.

## How do I use navigation blocking?

There are 2 ways to use navigation blocking:

- Hook/logical-based blocking
- Component-based blocking

## Hook/logical-based blocking

Let's imagine we want to prevent navigation if a form is dirty. We can do this by using the `useBlocker` hook:

[//]: # 'HookBasedBlockingExample'

```tsx
import { useBlocker } from '@tanstack/react-router'

function MyComponent() {
  const [formIsDirty, setFormIsDirty] = useState(false)

  useBlocker({
    shouldBlockFn: () => {
      if (!formIsDirty) return false

      const shouldLeave = confirm('Are you sure you want to leave?')
      return !shouldLeave
    },
  })

  // ...
}
```

[//]: # 'HookBasedBlockingExample'

`shouldBlockFn` gives you type safe access to the `current` and `next` location:

```tsx
import { useBlocker } from '@tanstack/react-router'

function MyComponent() {
  // always block going from /foo to /bar/123?hello=world
  const { proceed, reset, status } = useBlocker({
    shouldBlockFn: ({ current, next }) => {
      return (
        current.routeId === '/foo' &&
        next.fullPath === '/bar/$id' &&
        next.params.id === 123 &&
        next.search.hello === 'world'
      )
    },
    withResolver: true,
  })

  // ...
}
```

Note that even if `shouldBlockFn` returns `false`, the browser's `beforeunload` event may still be triggered on page reloads or tab closing. To gain control over this, you can use the `enableBeforeUnload` option to conditionally register the `beforeunload` handler:

[//]: # 'HookBasedBlockingExample'

```tsx
import { useBlocker } from '@tanstack/react-router'

function MyComponent() {
  const [formIsDirty, setFormIsDirty] = useState(false)

  useBlocker({
    {/* ... */}
    enableBeforeUnload: formIsDirty, // or () => formIsDirty
  })

  // ...
}
```

You can find more information about the `useBlocker` hook in the [API reference](../../api/router/useBlockerHook.md).

## Component-based blocking

In addition to logical/hook based blocking, you can use the `Block` component to achieve similar results:

[//]: # 'ComponentBasedBlockingExample'

```tsx
import { Block } from '@tanstack/react-router'

function MyComponent() {
  const [formIsDirty, setFormIsDirty] = useState(false)

  return (
    <Block
      shouldBlockFn={() => {
        if (!formIsDirty) return false

        const shouldLeave = confirm('Are you sure you want to leave?')
        return !shouldLeave
      }}
      enableBeforeUnload={formIsDirty}
    />
  )

  // OR

  return (
    <Block shouldBlockFn={() => formIsDirty} enableBeforeUnload={formIsDirty} withResolver>
      {({ status, proceed, reset }) => <>{/* ... */}</>}
    </Block>
  )
}
```

[//]: # 'ComponentBasedBlockingExample'

## How can I show a custom UI?

In most cases, using `window.confirm` in the `shouldBlockFn` function with `withResolver: false` in the hook is enough since it will clearly show the user that the navigation is being blocked and resolve the blocking based on their response.

However, in some situations, you might want to show a custom UI that is intentionally less disruptive and more integrated with your app's design.

**Note:** The return value of `shouldBlockFn` does not resolve the blocking if `withResolver` is `true`.

### Hook/logical-based custom UI with resolver

[//]: # 'HookBasedCustomUIBlockingWithResolverExample'

```tsx
import { useBlocker } from '@tanstack/react-router'

function MyComponent() {
  const [formIsDirty, setFormIsDirty] = useState(false)

  const { proceed, reset, status } = useBlocker({
    shouldBlockFn: () => formIsDirty,
    withResolver: true,
  })

  // ...

  return (
    <>
      {/* ... */}
      {status === 'blocked' && (
        <div>
          <p>Are you sure you want to leave?</p>
          <button onClick={proceed}>Yes</button>
          <button onClick={reset}>No</button>
        </div>
      )}
    </>
}
```

[//]: # 'HookBasedCustomUIBlockingWithResolverExample'

### Hook/logical-based custom UI without resolver

[//]: # 'HookBasedCustomUIBlockingWithoutResolverExample'

```tsx
import { useBlocker } from '@tanstack/react-router'

function MyComponent() {
  const [formIsDirty, setFormIsDirty] = useState(false)

  useBlocker({
    shouldBlockFn: () => {
      if (!formIsDirty) {
        return false
      }

      const shouldBlock = new Promise<boolean>((resolve) => {
        // Using a modal manager of your choice
        modals.open({
          title: 'Are you sure you want to leave?',
          children: (
            <SaveBlocker
              confirm={() => {
                modals.closeAll()
                resolve(false)
              }}
              reject={() => {
                modals.closeAll()
                resolve(true)
              }}
            />
          ),
          onClose: () => resolve(true),
        })
      })
      return shouldBlock
    },
  })

  // ...
}
```

[//]: # 'HookBasedCustomUIBlockingWithoutResolverExample'

### Component-based custom UI

Similarly to the hook, the `Block` component returns the same state and functions as render props:

[//]: # 'ComponentBasedCustomUIBlockingExample'

```tsx
import { Block } from '@tanstack/react-router'

function MyComponent() {
  const [formIsDirty, setFormIsDirty] = useState(false)

  return (
    <Block shouldBlockFn={() => formIsDirty} withResolver>
      {({ status, proceed, reset }) => (
        <>
          {/* ... */}
          {status === 'blocked' && (
            <div>
              <p>Are you sure you want to leave?</p>
              <button onClick={proceed}>Yes</button>
              <button onClick={reset}>No</button>
            </div>
          )}
        </>
      )}
    </Block>
  )
}
```

[//]: # 'ComponentBasedCustomUIBlockingExample'

# Navigation

## Everything is Relative

Believe it or not, every navigation within an app is **relative**, even if you aren't using explicit relative path syntax (`../../somewhere`). Any time a link is clicked or an imperative navigation call is made, you will always have an **origin** path and a **destination** path which means you are navigating **from** one route **to** another route.

TanStack Router keeps this constant concept of relative navigation in mind for every navigation, so you'll constantly see two properties in the API:

- `from` - The origin route path
- `to` - The destination route path

> ⚠️ If a `from` route path isn't provided the router will assume you are navigating from the root `/` route and only auto-complete absolute paths. After all, you need to know where you are from in order to know where you're going 😉.

## Shared Navigation API

Every navigation and route matching API in TanStack Router uses the same core interface with minor differences depending on the API. This means that you can learn navigation and route matching once and use the same syntax and concepts across the library.

### `ToOptions` Interface

This is the core `ToOptions` interface that is used in every navigation and route matching API:

```ts
type ToOptions<
  TRouteTree extends AnyRoute = AnyRoute,
  TFrom extends RoutePaths<TRouteTree> | string = string,
  TTo extends string = '',
> = {
  // `from` is an optional route ID or path. If it is not supplied, only absolute paths will be auto-completed and type-safe. It's common to supply the route.fullPath of the origin route you are rendering from for convenience. If you don't know the origin route, leave this empty and work with absolute paths or unsafe relative paths.
  from?: string
  // `to` can be an absolute route path or a relative path from the `from` option to a valid route path. ⚠️ Do not interpolate path params, hash or search params into the `to` options. Use the `params`, `search`, and `hash` options instead.
  to: string
  // `params` is either an object of path params to interpolate into the `to` option or a function that supplies the previous params and allows you to return new ones. This is the only way to interpolate dynamic parameters into the final URL. Depending on the `from` and `to` route, you may need to supply none, some or all of the path params. TypeScript will notify you of the required params if there are any.
  params: Record<string, unknown> | ((prevParams: Record<string, unknown>) => Record<string, unknown>)
  // `search` is either an object of query params or a function that supplies the previous search and allows you to return new ones. Depending on the `from` and `to` route, you may need to supply none, some or all of the query params. TypeScript will notify you of the required search params if there are any.
  search: Record<string, unknown> | ((prevSearch: Record<string, unknown>) => Record<string, unknown>)
  // `hash` is either a string or a function that supplies the previous hash and allows you to return a new one.
  hash?: string | ((prevHash: string) => string)
  // `state` is either an object of state or a function that supplies the previous state and allows you to return a new one. State is stored in the history API and can be useful for passing data between routes that you do not want to permanently store in URL search params.
  state?: Record<string, any> | ((prevState: Record<string, unknown>) => Record<string, unknown>)
}
```

> 🧠 Every route object has a `to` property, which can be used as the `to` for any navigation or route matching API. Where possible, this will allow you to avoid plain strings and use type-safe route references instead:

```tsx
import { Route as aboutRoute } from './routes/about.tsx'

function Comp() {
  return <Link to={aboutRoute.to}>About</Link>
}
```

### `NavigateOptions` Interface

This is the core `NavigateOptions` interface that extends `ToOptions`. Any API that is actually performing a navigation will use this interface:

```ts
export type NavigateOptions<
  TRouteTree extends AnyRoute = AnyRoute,
  TFrom extends RoutePaths<TRouteTree> | string = string,
  TTo extends string = '',
> = ToOptions<TRouteTree, TFrom, TTo> & {
  // `replace` is a boolean that determines whether the navigation should replace the current history entry or push a new one.
  replace?: boolean
  // `resetScroll` is a boolean that determines whether scroll position will be reset to 0,0 after the location is committed to browser history.
  resetScroll?: boolean
  // `hashScrollIntoView` is a boolean or object that determines whether an id matching the hash will be scrolled into view after the location is committed to history.
  hashScrollIntoView?: boolean | ScrollIntoViewOptions
  // `viewTransition` is either a boolean or function that determines if and how the browser will call document.startViewTransition() when navigating.
  viewTransition?: boolean | ViewTransitionOptions
  // `ignoreBlocker` is a boolean that determines if navigation should ignore any blockers that might prevent it.
  ignoreBlocker?: boolean
  // `reloadDocument` is a boolean that determines if navigation to a route inside of router will trigger a full page load instead of the traditional SPA navigation.
  reloadDocument?: boolean
  // `href` is a string that can be used in place of `to` to navigate to a full built href, e.g. pointing to an external target.
  href?: string
}
```

### `LinkOptions` Interface

Anywhere an actual `<a>` tag the `LinkOptions` interface which extends `NavigateOptions` will be available:

```tsx
export type LinkOptions<
  TRouteTree extends AnyRoute = AnyRoute,
  TFrom extends RoutePaths<TRouteTree> | string = string,
  TTo extends string = '',
> = NavigateOptions<TRouteTree, TFrom, TTo> & {
  // The standard anchor tag target attribute
  target?: HTMLAnchorElement['target']
  // Defaults to `{ exact: false, includeHash: false }`
  activeOptions?: {
    exact?: boolean
    includeHash?: boolean
    includeSearch?: boolean
    explicitUndefined?: boolean
  }
  // If set, will preload the linked route on hover and cache it for this many milliseconds in hopes that the user will eventually navigate there.
  preload?: false | 'intent'
  // Delay intent preloading by this many milliseconds. If the intent exits before this delay, the preload will be cancelled.
  preloadDelay?: number
  // If true, will render the link without the href attribute
  disabled?: boolean
}
```

## Navigation API

With relative navigation and all of the interfaces in mind now, let's talk about the different flavors of navigation API at your disposal:

- The `<Link>` component
  - Generates an actual `<a>` tag with a valid `href` which can be click or even cmd/ctrl + clicked to open in a new tab
- The `useNavigate()` hook
  - When possible, `Link` component should be used for navigation, but sometimes you need to navigate imperatively as a result of a side-effect. `useNavigate` returns a function that can be called to perform an immediate client-side navigation.
- The `<Navigate>` component
  - Renders nothing and performs an immediate client-side navigation.
- The `Router.navigate()` method
  - This is the most powerful navigation API in TanStack Router. Similar to `useNavigate`, it imperatively navigates, but is available everywhere you have access to your router.

⚠️ None of these APIs are a replacement for server-side redirects. If you need to redirect a user immediately from one route to another before mounting your application, use a server-side redirect instead of a client-side navigation.

## `<Link>` Component

The `Link` component is the most common way to navigate within an app. It renders an actual `<a>` tag with a valid `href` attribute which can be clicked or even cmd/ctrl + clicked to open in a new tab. It also supports any normal `<a>` attributes including `target` to open links in new windows, etc.

In addition to the [`LinkOptions`](#linkoptions-interface) interface, the `Link` component also supports the following props:

```tsx
export type LinkProps<
  TFrom extends RoutePaths<RegisteredRouter['routeTree']> | string = string,
  TTo extends string = '',
> = LinkOptions<RegisteredRouter['routeTree'], TFrom, TTo> & {
  // A function that returns additional props for the `active` state of this link. These props override other props passed to the link (`style`'s are merged, `className`'s are concatenated)
  activeProps?: FrameworkHTMLAnchorTagAttributes | (() => FrameworkHTMLAnchorAttributes)
  // A function that returns additional props for the `inactive` state of this link. These props override other props passed to the link (`style`'s are merged, `className`'s are concatenated)
  inactiveProps?: FrameworkHTMLAnchorAttributes | (() => FrameworkHTMLAnchorAttributes)
}
```

### Absolute Links

Let's make a simple static link!

```tsx
import { Link } from '@tanstack/react-router'

const link = <Link to="/about">About</Link>
```

### Dynamic Links

Dynamic links are links that have dynamic segments in them. For example, a link to a blog post might look like this:

```tsx
const link = (
  <Link
    to="/blog/post/$postId"
    params={{
      postId: 'my-first-blog-post',
    }}
  >
    Blog Post
  </Link>
)
```

Keep in mind that normally dynamic segment params are `string` values, but they can also be any other type that you parse them to in your route options. Either way, the type will be checked at compile time to ensure that you are passing the correct type.

### Relative Links

By default, all links are absolute unless a `from` route path is provided. This means that the above link will always navigate to the `/about` route regardless of what route you are currently on.

Relative links can be combined with a `from` route path. If a from route path isn't provided, relative paths default to the current active location.

```tsx
const postIdRoute = createRoute({
  path: '/blog/post/$postId',
})

const link = (
  <Link from={postIdRoute.fullPath} to="../categories">
    Categories
  </Link>
)
```

As seen above, it's common to provide the `route.fullPath` as the `from` route path. This is because the `route.fullPath` is a reference that will update if you refactor your application. However, sometimes it's not possible to import the route directly, in which case it's fine to provide the route path directly as a string. It will still get type-checked as per usual!

### Special relative paths: `"."` and `".."`

Quite often you might want to reload the current location or another `from` path, for example, to rerun the loaders on the current and/or parent routes, or maybe navigate back to a parent route. This can be achieved by specifying a `to` route path of `"."` which will reload the current location or provided `from` path.

Another common need is to navigate one route back relative to the current location or another path. By specifying a `to` route path of `".."` navigation will be resolved to the first parent route preceding the current location.

```tsx
export const Route = createFileRoute('/posts/$postId')({
  component: PostComponent,
})

function PostComponent() {
  return (
    <div>
      <Link to=".">Reload the current route of /posts/$postId</Link>
      <Link to="..">Navigate back to /posts</Link>
      // the below are all equivalent
      <Link to="/posts">Navigate back to /posts</Link>
      <Link from="/posts" to=".">
        Navigate back to /posts
      </Link>
      // the below are all equivalent
      <Link to="/">Navigate to root</Link>
      <Link from="/posts" to="..">
        Navigate to root
      </Link>
    </div>
  )
}
```

### Search Param Links

Search params are a great way to provide additional context to a route. For example, you might want to provide a search query to a search page:

```tsx
const link = (
  <Link
    to="/search"
    search={{
      query: 'tanstack',
    }}
  >
    Search
  </Link>
)
```

It's also common to want to update a single search param without supplying any other information about the existing route. For example, you might want to update the page number of a search result:

```tsx
const link = (
  <Link
    to="."
    search={(prev) => ({
      ...prev,
      page: prev.page + 1,
    })}
  >
    Next Page
  </Link>
)
```

### Search Param Type Safety

Search params are a highly dynamic state management mechanism, so it's important to ensure that you are passing the correct types to your search params. We'll see in a later section in detail how to validate and ensure search params typesafety, among other great features!

### Hash Links

Hash links are a great way to link to a specific section of a page. For example, you might want to link to a specific section of a blog post:

```tsx
const link = (
  <Link
    to="/blog/post/$postId"
    params={{
      postId: 'my-first-blog-post',
    }}
    hash="section-1"
  >
    Section 1
  </Link>
)
```

### Navigating with Optional Parameters

Optional path parameters provide flexible navigation patterns where you can include or omit parameters as needed. Optional parameters use the `{-$paramName}` syntax and offer fine-grained control over URL structure.

#### Parameter Inheritance vs Removal

When navigating with optional parameters, you have two main strategies:

**Inheriting Current Parameters**
Use `params: {}` to inherit all current route parameters:

```tsx
// Inherits current route parameters
<Link to="/posts/{-$category}" params={{}}>
  All Posts
</Link>
```

**Removing Parameters**  
Set parameters to `undefined` to explicitly remove them:

```tsx
// Removes the category parameter
<Link to="/posts/{-$category}" params={{ category: undefined }}>
  All Posts
</Link>
```

#### Basic Optional Parameter Navigation

```tsx
// Navigate with optional parameter
<Link
  to="/posts/{-$category}"
  params={{ category: 'tech' }}
>
  Tech Posts
</Link>

// Navigate without optional parameter
<Link
  to="/posts/{-$category}"
  params={{ category: undefined }}
>
  All Posts
</Link>

// Navigate using parameter inheritance
<Link
  to="/posts/{-$category}"
  params={{}}
>
  Current Category
</Link>
```

#### Function-Style Parameter Updates

Function-style parameter updates are particularly useful with optional parameters:

```tsx
// Remove a parameter using function syntax
<Link
  to="/posts/{-$category}"
  params={(prev) => ({ ...prev, category: undefined })}
>
  Clear Category
</Link>

// Update a parameter while keeping others
<Link
  to="/articles/{-$category}/{-$slug}"
  params={(prev) => ({ ...prev, category: 'news' })}
>
  News Articles
</Link>

// Conditionally set parameters
<Link
  to="/posts/{-$category}"
  params={(prev) => ({
    ...prev,
    category: someCondition ? 'tech' : undefined
  })}
>
  Conditional Category
</Link>
```

#### Multiple Optional Parameters

When working with multiple optional parameters, you can mix and match which ones to include:

```tsx
// Navigate with some optional parameters
<Link
  to="/posts/{-$category}/{-$slug}"
  params={{ category: 'tech', slug: undefined }}
>
  Tech Posts
</Link>

// Remove all optional parameters
<Link
  to="/posts/{-$category}/{-$slug}"
  params={{ category: undefined, slug: undefined }}
>
  All Posts
</Link>

// Set multiple parameters
<Link
  to="/posts/{-$category}/{-$slug}"
  params={{ category: 'tech', slug: 'react-tips' }}
>
  Specific Post
</Link>
```

#### Mixed Required and Optional Parameters

Optional parameters work seamlessly with required parameters:

```tsx
// Required 'id', optional 'tab'
<Link
  to="/users/$id/{-$tab}"
  params={{ id: '123', tab: 'settings' }}
>
  User Settings
</Link>

// Remove optional parameter while keeping required
<Link
  to="/users/$id/{-$tab}"
  params={{ id: '123', tab: undefined }}
>
  User Profile
</Link>

// Use function style with mixed parameters
<Link
  to="/users/$id/{-$tab}"
  params={(prev) => ({ ...prev, tab: 'notifications' })}
>
  User Notifications
</Link>
```

#### Advanced Optional Parameter Patterns

**Prefix and Suffix Parameters**
Optional parameters with prefix/suffix work with navigation:

```tsx
// Navigate to file with optional name
<Link
  to="/files/prefix{-$name}.txt"
  params={{ name: 'document' }}
>
  Document File
</Link>

// Navigate to file without optional name
<Link
  to="/files/prefix{-$name}.txt"
  params={{ name: undefined }}
>
  Default File
</Link>
```

**All Optional Parameters**
Routes where all parameters are optional:

```tsx
// Navigate to specific date
<Link
  to="/{-$year}/{-$month}/{-$day}"
  params={{ year: '2023', month: '12', day: '25' }}
>
  Christmas 2023
</Link>

// Navigate to partial date
<Link
  to="/{-$year}/{-$month}/{-$day}"
  params={{ year: '2023', month: '12', day: undefined }}
>
  December 2023
</Link>

// Navigate to root with all parameters removed
<Link
  to="/{-$year}/{-$month}/{-$day}"
  params={{ year: undefined, month: undefined, day: undefined }}
>
  Home
</Link>
```

#### Navigation with Search Params and Optional Parameters

Optional parameters work great in combination with search params:

```tsx
// Combine optional path params with search params
<Link
  to="/posts/{-$category}"
  params={{ category: 'tech' }}
  search={{ page: 1, sort: 'newest' }}
>
  Tech Posts - Page 1
</Link>

// Remove path param but keep search params
<Link
  to="/posts/{-$category}"
  params={{ category: undefined }}
  search={(prev) => prev}
>
  All Posts - Same Filters
</Link>
```

#### Imperative Navigation with Optional Parameters

All the same patterns work with imperative navigation:

```tsx
function Component() {
  const navigate = useNavigate()

  const clearFilters = () => {
    navigate({
      to: '/posts/{-$category}/{-$tag}',
      params: { category: undefined, tag: undefined },
    })
  }

  const setCategory = (category: string) => {
    navigate({
      to: '/posts/{-$category}/{-$tag}',
      params: (prev) => ({ ...prev, category }),
    })
  }

  const applyFilters = (category?: string, tag?: string) => {
    navigate({
      to: '/posts/{-$category}/{-$tag}',
      params: { category, tag },
    })
  }
}
```

### Active & Inactive Props

The `Link` component supports two additional props: `activeProps` and `inactiveProps`. These props are functions that return additional props for the `active` and `inactive` states of the link. All props other than styles and classes passed here will override the original props passed to `Link`. Any styles or classes passed are merged together.

Here's an example:

```tsx
const link = (
  <Link
    to="/blog/post/$postId"
    params={{
      postId: 'my-first-blog-post',
    }}
    activeProps={{
      style: {
        fontWeight: 'bold',
      },
    }}
  >
    Section 1
  </Link>
)
```

### The `data-status` attribute

In addition to the `activeProps` and `inactiveProps` props, the `Link` component also adds a `data-status` attribute to the rendered element when it is in an active state. This attribute will be `active` or `undefined` depending on the current state of the link. This can come in handy if you prefer to use data-attributes to style your links instead of props.

### Active Options

The `Link` component comes with an `activeOptions` property that offers a few options of determining if a link is active or not. The following interface describes those options:

```tsx
export interface ActiveOptions {
  // If true, the link will be active if the current route matches the `to` route path exactly (no children routes)
  // Defaults to `false`
  exact?: boolean
  // If true, the link will only be active if the current URL hash matches the `hash` prop
  // Defaults to `false`
  includeHash?: boolean // Defaults to false
  // If true, the link will only be active if the current URL search params inclusively match the `search` prop
  // Defaults to `true`
  includeSearch?: boolean
  // This modifies the `includeSearch` behavior.
  // If true,  properties in `search` that are explicitly `undefined` must NOT be present in the current URL search params for the link to be active.
  // defaults to `false`
  explicitUndefined?: boolean
}
```

By default, it will check if the resulting **pathname** is a prefix of the current route. If any search params are provided, it will check that they _inclusively_ match those in the current location. Hashes are not checked by default.

For example, if you are on the `/blog/post/my-first-blog-post` route, the following links will be active:

```tsx
const link1 = (
  <Link to="/blog/post/$postId" params={{ postId: 'my-first-blog-post' }}>
    Blog Post
  </Link>
)
const link2 = <Link to="/blog/post">Blog Post</Link>
const link3 = <Link to="/blog">Blog Post</Link>
```

However, the following links will not be active:

```tsx
const link4 = (
  <Link to="/blog/post/$postId" params={{ postId: 'my-second-blog-post' }}>
    Blog Post
  </Link>
)
```

It's common for some links to only be active if they are an exact match. A good example of this would be a link to the home page. In scenarios like these, you can pass the `exact: true` option:

```tsx
const link = (
  <Link to="/" activeOptions={{ exact: true }}>
    Home
  </Link>
)
```

This will ensure that the link is not active when you are a child route.

A few more options to be aware of:

- If you want to include the hash in your matching, you can pass the `includeHash: true` option
- If you do **not** want to include the search params in your matching, you can pass the `includeSearch: false` option

### Passing `isActive` to children

The `Link` component accepts a function for its children, allowing you to propagate its `isActive` property to children. For example, you could style a child component based on whether the parent link is active:

```tsx
const link = (
  <Link to="/blog/post">
    {({ isActive }) => {
      return (
        <>
          <span>My Blog Post</span>
          <icon className={isActive ? 'active' : 'inactive'} />
        </>
      )
    }}
  </Link>
)
```

### Link Preloading

The `Link` component supports automatically preloading routes on intent (hovering or touchstart for now). This can be configured as a default in the router options (which we'll talk more about soon) or by passing a `preload='intent'` prop to the `Link` component. Here's an example:

```tsx
const link = (
  <Link to="/blog/post/$postId" preload="intent">
    Blog Post
  </Link>
)
```

With preloading enabled and relatively quick asynchronous route dependencies (if any), this simple trick can increase the perceived performance of your application with very little effort.

What's even better is that by using a cache-first library like `@tanstack/query`, preloaded routes will stick around and be ready for a stale-while-revalidate experience if the user decides to navigate to the route later on.

### Link Preloading Delay

Along with preloading is a configurable delay which determines how long a user must hover over a link to trigger the intent-based preloading. The default delay is 50 milliseconds, but you can change this by passing a `preloadDelay` prop to the `Link` component with the number of milliseconds you'd like to wait:

```tsx
const link = (
  <Link to="/blog/post/$postId" preload="intent" preloadDelay={100}>
    Blog Post
  </Link>
)
```

## `useNavigate`

> ⚠️ Because of the `Link` component's built-in affordances around `href`, cmd/ctrl + click-ability, and active/inactive capabilities, it's recommended to use the `Link` component instead of `useNavigate` for anything the user can interact with (e.g. links, buttons). However, there are some cases where `useNavigate` is necessary to handle side-effect navigations (e.g. a successful async action that results in a navigation).

The `useNavigate` hook returns a `navigate` function that can be called to imperatively navigate. It's a great way to navigate to a route from a side-effect (e.g. a successful async action). Here's an example:

```tsx
function Component() {
  const navigate = useNavigate({ from: '/posts/$postId' })

  const handleSubmit = async (e: FrameworkFormEvent) => {
    e.preventDefault()

    const response = await fetch('/posts', {
      method: 'POST',
      body: JSON.stringify({ title: 'My First Post' }),
    })

    const { id: postId } = await response.json()

    if (response.ok) {
      navigate({ to: '/posts/$postId', params: { postId } })
    }
  }
}
```

> 🧠 As shown above, you can pass the `from` option to specify the route to navigate from in the hook call. While this is also possible to pass in the resulting `navigate` function each time you call it, it's recommended to pass it here to reduce on potential error and also not type as much!

### `navigate` Options

The `navigate` function returned by `useNavigate` accepts the [`NavigateOptions` interface](#navigateoptions-interface)

## `Navigate` Component

Occasionally, you may find yourself needing to navigate immediately when a component mounts. Your first instinct might be to reach for `useNavigate` and an immediate side-effect (e.g. useEffect), but this is unnecessary. Instead, you can render the `Navigate` component to achieve the same result:

```tsx
function Component() {
  return <Navigate to="/posts/$postId" params={{ postId: 'my-first-post' }} />
}
```

Think of the `Navigate` component as a way to navigate to a route immediately when a component mounts. It's a great way to handle client-only redirects. It is _definitely not_ a substitute for handling server-aware redirects responsibly on the server.

## `router.navigate`

The `router.navigate` method is the same as the `navigate` function returned by `useNavigate` and accepts the same [`NavigateOptions` interface](#navigateoptions-interface). Unlike the `useNavigate` hook, it is available anywhere your `router` instance is available and is thus a great way to navigate imperatively from anywhere in your application, including outside of your framework.

## `useMatchRoute` and `<MatchRoute>`

The `useMatchRoute` hook and `<MatchRoute>` component are the same thing, but the hook is a bit more flexible. They both accept the standard navigation `ToOptions` interface either as options or props and return `true/false` if that route is currently matched. It also has a handy `pending` option that will return `true` if the route is currently pending (e.g. a route is currently transitioning to that route). This can be extremely useful for showing optimistic UI around where a user is navigating:

```tsx
function Component() {
  return (
    <div>
      <Link to="/users">
        Users
        <MatchRoute to="/users" pending>
          <Spinner />
        </MatchRoute>
      </Link>
    </div>
  )
}
```

The component version `<MatchRoute>` can also be used with a function as children to render something when the route is matched:

```tsx
function Component() {
  return (
    <div>
      <Link to="/users">
        Users
        <MatchRoute to="/users" pending>
          {(match) => {
            return <Spinner show={match} />
          }}
        </MatchRoute>
      </Link>
    </div>
  )
}
```

The hook version `useMatchRoute` returns a function that can be called programmatically to check if a route is matched:

```tsx
function Component() {
  const matchRoute = useMatchRoute()

  useEffect(() => {
    if (matchRoute({ to: '/users', pending: true })) {
      console.info('The /users route is matched and pending')
    }
  })

  return (
    <div>
      <Link to="/users">Users</Link>
    </div>
  )
}
```

---

Phew! That's a lot of navigating! That said, hopefully you're feeling pretty good about getting around your application now. Let's move on!

# Not Found Errors

> ⚠️ This page covers the newer `notFound` function and `notFoundComponent` API for handling not found errors. The `NotFoundRoute` route is deprecated and will be removed in a future release. See [Migrating from `NotFoundRoute`](#migrating-from-notfoundroute) for more information.

## Overview

There are 2 uses for not-found errors in TanStack Router:

- **Non-matching route paths**: When a path does not match any known route matching pattern **OR** when it partially matches a route, but with extra path segments
  - The **router** will automatically throw a not-found error when a path does not match any known route matching pattern
  - If the router's `notFoundMode` is set to `fuzzy`, the nearest parent route with a `notFoundComponent` will handle the error. If the router's `notFoundMode` is set to `root`, the root route will handle the error.
  - Examples:
    - Attempting to access `/users` when there is no `/users` route
    - Attempting to access `/posts/1/edit` when the route tree only handles `/posts/$postId`
- **Missing resources**: When a resource cannot be found, such as a post with a given ID or any asynchronous data that is not available or does not exist
  - **You, the developer** must throw a not-found error when a resource cannot be found. This can be done in the `beforeLoad` or `loader` functions using the `notFound` utility.
  - Will be handled by the nearest parent route with a `notFoundComponent` (when `notFound` is called within `loader`) or the root route.
  - Examples:
    - Attempting to access `/posts/1` when the post with ID 1 does not exist
    - Attempting to access `/docs/path/to/document` when the document does not exist

Under the hood, both of these cases are implemented using the same `notFound` function and `notFoundComponent` API.

## The `notFoundMode` option

When TanStack Router encounters a **pathname** that doesn't match any known route pattern **OR** partially matches a route pattern but with extra trailing pathname segments, it will automatically throw a not-found error.

Depending on the `notFoundMode` option, the router will handle these automatic errors differently::

- ["fuzzy" mode](#notfoundmode-fuzzy) (default): The router will intelligently find the closest matching suitable route and display the `notFoundComponent`.
- ["root" mode](#notfoundmode-root): All not-found errors will be handled by the root route's `notFoundComponent`, regardless of the nearest matching route.

### `notFoundMode: 'fuzzy'`

By default, the router's `notFoundMode` is set to `fuzzy`, which indicates that if a pathname doesn't match any known route, the router will attempt to use the closest matching route with children/(an outlet) and a configured not found component.

> **❓ Why is this the default?** Fuzzy matching to preserve as much parent layout as possible for the user gives them more context to navigate to a useful location based on where they thought they would arrive.

The nearest suitable route is found using the following criteria:

- The route must have children and therefore an `Outlet` to render the `notFoundComponent`
- The route must have a `notFoundComponent` configured or the router must have a `defaultNotFoundComponent` configured

For example, consider the following route tree:

- `__root__` (has a `notFoundComponent` configured)
  - `posts` (has a `notFoundComponent` configured)
    - `$postId` (has a `notFoundComponent` configured)

If provided the path of `/posts/1/edit`, the following component structure will be rendered:

- `<Root>`
  - `<Posts>`
    - `<Posts.notFoundComponent>`

The `notFoundComponent` of the `posts` route will be rendered because it is the **nearest suitable parent route with children (and therefore an outlet) and a `notFoundComponent` configured**.

### `notFoundMode: 'root'`

When `notFoundMode` is set to `root`, all not-found errors will be handled by the root route's `notFoundComponent` instead of bubbling up from the nearest fuzzy-matched route.

For example, consider the following route tree:

- `__root__` (has a `notFoundComponent` configured)
  - `posts` (has a `notFoundComponent` configured)
    - `$postId` (has a `notFoundComponent` configured)

If provided the path of `/posts/1/edit`, the following component structure will be rendered:

- `<Root>`
  - `<Root.notFoundComponent>`

The `notFoundComponent` of the `__root__` route will be rendered because the `notFoundMode` is set to `root`.

## Configuring a route's `notFoundComponent`

To handle both types of not-found errors, you can attach a `notFoundComponent` to a route. This component will be rendered when a not-found error is thrown.

For example, configuring a `notFoundComponent` for a `/settings` route to handle non-existing settings pages:

```tsx
export const Route = createFileRoute('/settings')({
  component: () => {
    return (
      <div>
        <p>Settings page</p>
        <Outlet />
      </div>
    )
  },
  notFoundComponent: () => {
    return <p>This setting page doesn't exist!</p>
  },
})
```

Or configuring a `notFoundComponent` for a `/posts/$postId` route to handle posts that don't exist:

```tsx
export const Route = createFileRoute('/posts/$postId')({
  loader: async ({ params: { postId } }) => {
    const post = await getPost(postId)
    if (!post) throw notFound()
    return { post }
  },
  component: ({ post }) => {
    return (
      <div>
        <h1>{post.title}</h1>
        <p>{post.body}</p>
      </div>
    )
  },
  notFoundComponent: () => {
    return <p>Post not found!</p>
  },
})
```

## Default Router-Wide Not Found Handling

You may want to provide a default not-found component for every route in your app with child routes.

> Why only routes with children? **Leaf-node routes (routes without children) will never render an `Outlet` and therefore are not able to handle not-found errors.**

To do this, pass a `defaultNotFoundComponent` to the `createRouter` function:

```tsx
const router = createRouter({
  defaultNotFoundComponent: () => {
    return (
      <div>
        <p>Not found!</p>
        <Link to="/">Go home</Link>
      </div>
    )
  },
})
```

## Throwing your own `notFound` errors

You can manually throw not-found errors in loader methods and components using the `notFound` function. This is useful when you need to signal that a resource cannot be found.

The `notFound` function works in a similar fashion to the `redirect` function. To cause a not-found error, you can **throw a `notFound()`**.

```tsx
export const Route = createFileRoute('/posts/$postId')({
  loader: async ({ params: { postId } }) => {
    // Returns `null` if the post doesn't exist
    const post = await getPost(postId)
    if (!post) {
      throw notFound()
      // Alternatively, you can make the notFound function throw:
      // notFound({ throw: true })
    }
    // Post is guaranteed to be defined here because we threw an error
    return { post }
  },
})
```

The not-found error above will be handled by the same route or nearest parent route that has either a `notFoundComponent` route option or the `defaultNotFoundComponent` router option configured.

If neither the route nor any suitable parent route is found to handle the error, the root route will handle it using TanStack Router's **extremely basic (and purposefully undesirable)** default not-found component that simply renders `<div>Not Found</div>`. It's highly recommended to either attach at least one `notFoundComponent` to the root route or configure a router-wide `defaultNotFoundComponent` to handle not-found errors.

## Specifying Which Routes Handle Not Found Errors

Sometimes you may want to trigger a not-found on a specific parent route and bypass the normal not-found component propagation. To do this, pass in a route id to the `route` option in the `notFound` function.

```tsx
// _pathlessLayout.tsx
export const Route = createFileRoute('/_pathlessLayout')({
  // This will render
  notFoundComponent: () => {
    return <p>Not found (in _pathlessLayout)</p>
  },
  component: () => {
    return (
      <div>
        <p>This is a pathless layout route!</p>
        <Outlet />
      </div>
    )
  },
})

// _pathlessLayout/route-a.tsx
export const Route = createFileRoute('/_pathless/route-a')({
  loader: async () => {
    // This will make LayoutRoute handle the not-found error
    throw notFound({ routeId: '/_pathlessLayout' })
    //                      ^^^^^^^^^ This will autocomplete from the registered router
  },
  // This WILL NOT render
  notFoundComponent: () => {
    return <p>Not found (in _pathlessLayout/route-a)</p>
  },
})
```

### Manually targeting the root route

You can also target the root route by passing the exported `rootRouteId` variable to the `notFound` function's `route` property:

```tsx
import { rootRouteId } from '@tanstack/react-router'

export const Route = createFileRoute('/posts/$postId')({
  loader: async ({ params: { postId } }) => {
    const post = await getPost(postId)
    if (!post) throw notFound({ routeId: rootRouteId })
    return { post }
  },
})
```

### Throwing Not Found Errors in Components

You can also throw not-found errors in components. However, **it is recommended to throw not-found errors in loader methods instead of components in order to correctly type loader data and prevent flickering.**

TanStack Router exposes a `CatchNotFound` component similar to `CatchBoundary` that can be used to catch not-found errors in components and display UI accordingly.

### Data Loading Inside `notFoundComponent`

`notFoundComponent` is a special case when it comes to data loading. **`SomeRoute.useLoaderData` may not be defined depending on which route you are trying to access and where the not-found error gets thrown**. However, `Route.useParams`, `Route.useSearch`, `Route.useRouteContext`, etc. will return a defined value.

**If you need to pass incomplete loader data to `notFoundComponent`,** pass the data via the `data` option in the `notFound` function and validate it in `notFoundComponent`.

```tsx
export const Route = createFileRoute('/posts/$postId')({
  loader: async ({ params: { postId } }) => {
    const post = await getPost(postId)
    if (!post)
      throw notFound({
        // Forward some data to the notFoundComponent
        // data: someIncompleteLoaderData
      })
    return { post }
  },
  // `data: unknown` is passed to the component via the `data` option when calling `notFound`
  notFoundComponent: ({ data }) => {
    // ❌ useLoaderData is not valid here: const { post } = Route.useLoaderData()

    // ✅:
    const { postId } = Route.useParams()
    const search = Route.useSearch()
    const context = Route.useRouteContext()

    return <p>Post with id {postId} not found!</p>
  },
})
```

## Usage With SSR

See [SSR guide](../ssr.md) for more information.

## Migrating from `NotFoundRoute`

The `NotFoundRoute` API is deprecated in favor of `notFoundComponent`. The `NotFoundRoute` API will be removed in a future release.

**The `notFound` function and `notFoundComponent` will not work when using `NotFoundRoute`.**

The main differences are:

- `NotFoundRoute` is a route that requires an `<Outlet>` on its parent route to render. `notFoundComponent` is a component that can be attached to any route.
- When using `NotFoundRoute`, you can't use layouts. `notFoundComponent` can be used with layouts.
- When using `notFoundComponent`, path matching is strict. This means that if you have a route at `/post/$postId`, a not-found error will be thrown if you try to access `/post/1/2/3`. With `NotFoundRoute`, `/post/1/2/3` would match the `NotFoundRoute` and only render it if there is an `<Outlet>`.

To migrate from `NotFoundRoute` to `notFoundComponent`, you'll just need to make a few changes:

```tsx
// router.tsx
import { createRouter } from '@tanstack/react-router'
import { routeTree } from './routeTree.gen.'
- import { notFoundRoute } from './notFoundRoute'  // [!code --]

export const router = createRouter({
  routeTree,
- notFoundRoute // [!code --]
})

// routes/__root.tsx
import { createRootRoute } from '@tanstack/react-router'

export const Route = createRootRoute({
  // ...
+ notFoundComponent: () => {  // [!code ++]
+   return <p>Not found!</p>  // [!code ++]
+ } // [!code ++]
})
```

Important changes:

- A `notFoundComponent` is added to the root route for global not-found handling.
  - You can also add a `notFoundComponent` to any other route in your route tree to handle not-found errors for that specific route.
- The `notFoundComponent` does not support rendering an `<Outlet>`.

# Outlets

Nested routing means that routes can be nested within other routes, including the way they render. So how do we tell our routes where to render this nested content?

## The `Outlet` Component

The `Outlet` component is used to render the next potentially matching child route. `<Outlet />` doesn't take any props and can be rendered anywhere within a route's component tree. If there is no matching child route, `<Outlet />` will render `null`.

> [!TIP]
> If a route's `component` is left undefined, it will render an `<Outlet />` automatically.

A great example is configuring the root route of your application. Let's give our root route a component that renders a title, then an `<Outlet />` for our top-level routes to render.

```tsx
import { createRootRoute, Outlet } from '@tanstack/react-router'

export const Route = createRootRoute({
  component: RootComponent,
})

function RootComponent() {
  return (
    <div>
      <h1>My App</h1>
      <Outlet /> {/* This is where child routes will render */}
    </div>
  )
}
```

# Parallel Routes

We haven't covered this yet. Stay tuned!

# Path Params

Path params are used to match a single segment (the text until the next `/`) and provide its value back to you as a **named** variable. They are defined by using the `$` character prefix in the path, followed by the key variable to assign it to. The following are valid path param paths:

- `$postId`
- `$name`
- `$teamId`
- `about/$name`
- `team/$teamId`
- `blog/$postId`

Because path param routes only match to the next `/`, child routes can be created to continue expressing hierarchy:

Let's create a post route file that uses a path param to match the post ID:

- `posts.$postId.tsx`

```tsx
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/posts/$postId')({
  loader: async ({ params }) => {
    return fetchPost(params.postId)
  },
})
```

## Path Params can be used by child routes

Once a path param has been parsed, it is available to all child routes. This means that if we define a child route to our `postRoute`, we can use the `postId` variable from the URL in the child route's path!

## Path Params in Loaders

Path params are passed to the loader as a `params` object. The keys of this object are the names of the path params, and the values are the values that were parsed out of the actual URL path. For example, if we were to visit the `/blog/123` URL, the `params` object would be `{ postId: '123' }`:

```tsx
export const Route = createFileRoute('/posts/$postId')({
  loader: async ({ params }) => {
    return fetchPost(params.postId)
  },
})
```

The `params` object is also passed to the `beforeLoad` option:

```tsx
export const Route = createFileRoute('/posts/$postId')({
  beforeLoad: async ({ params }) => {
    // do something with params.postId
  },
})
```

## Path Params in Components

If we add a component to our `postRoute`, we can access the `postId` variable from the URL by using the route's `useParams` hook:

```tsx
export const Route = createFileRoute('/posts/$postId')({
  component: PostComponent,
})

function PostComponent() {
  const { postId } = Route.useParams()
  return <div>Post {postId}</div>
}
```

> 🧠 Quick tip: If your component is code-split, you can use the [getRouteApi function](../code-splitting.md#manually-accessing-route-apis-in-other-files-with-the-getrouteapi-helper) to avoid having to import the `Route` configuration to get access to the typed `useParams()` hook.

## Path Params outside of Routes

You can also use the globally exported `useParams` hook to access any parsed path params from any component in your app. You'll need to pass the `strict: false` option to `useParams`, denoting that you want to access the params from an ambiguous location:

```tsx
function PostComponent() {
  const { postId } = useParams({ strict: false })
  return <div>Post {postId}</div>
}
```

## Navigating with Path Params

When navigating to a route with path params, TypeScript will require you to pass the params either as an object or as a function that returns an object of params.

Let's see what an object style looks like:

```tsx
function Component() {
  return (
    <Link to="/blog/$postId" params={{ postId: '123' }}>
      Post 123
    </Link>
  )
}
```

And here's what a function style looks like:

```tsx
function Component() {
  return (
    <Link to="/blog/$postId" params={(prev) => ({ ...prev, postId: '123' })}>
      Post 123
    </Link>
  )
}
```

Notice that the function style is useful when you need to persist params that are already in the URL for other routes. This is because the function style will receive the current params as an argument, allowing you to modify them as needed and return the final params object.

## Prefixes and Suffixes for Path Params

You can also use **prefixes** and **suffixes** with path params to create more complex routing patterns. This allows you to match specific URL structures while still capturing the dynamic segments.

When using either prefixes or suffixes, you can define them by wrapping the path param in curly braces `{}` and placing the prefix or suffix before or after the variable name.

### Defining Prefixes

Prefixes are defined by placing the prefix text outside the curly braces before the variable name. For example, if you want to match a URL that starts with `post-` followed by a post ID, you can define it like this:

```tsx
// src/routes/posts/post-{$postId}.tsx
export const Route = createFileRoute('/posts/post-{$postId}')({
  component: PostComponent,
})

function PostComponent() {
  const { postId } = Route.useParams()
  // postId will be the value after 'post-'
  return <div>Post ID: {postId}</div>
}
```

You can even combines prefixes with wildcard routes to create more complex patterns:

```tsx
// src/routes/on-disk/storage-{$}
export const Route = createFileRoute('/on-disk/storage-{$postId}/$')({
  component: StorageComponent,
})

function StorageComponent() {
  const { _splat } = Route.useParams()
  // _splat, will be value after 'storage-'
  // i.e. my-drive/documents/foo.txt
  return <div>Storage Location: /{_splat}</div>
}
```

### Defining Suffixes

Suffixes are defined by placing the suffix text outside the curly braces after the variable name. For example, if you want to match a URL a filename that ends with `txt`, you can define it like this:

```tsx
// src/routes/files/{$fileName}txt
export const Route = createFileRoute('/files/{$fileName}.txt')({
  component: FileComponent,
})

function FileComponent() {
  const { fileName } = Route.useParams()
  // fileName will be the value before 'txt'
  return <div>File Name: {fileName}</div>
}
```

You can also combine suffixes with wildcards for more complex routing patterns:

```tsx
// src/routes/files/{$}[.]txt
export const Route = createFileRoute('/files/{$fileName}[.]txt')({
  component: FileComponent,
})

function FileComponent() {
  const { _splat } = Route.useParams()
  // _splat will be the value before '.txt'
  return <div>File Splat: {_splat}</div>
}
```

### Combining Prefixes and Suffixes

You can combine both prefixes and suffixes to create very specific routing patterns. For example, if you want to match a URL that starts with `user-` and ends with `.json`, you can define it like this:

```tsx
// src/routes/users/user-{$userId}person
export const Route = createFileRoute('/users/user-{$userId}person')({
  component: UserComponent,
})

function UserComponent() {
  const { userId } = Route.useParams()
  // userId will be the value between 'user-' and 'person'
  return <div>User ID: {userId}</div>
}
```

Similar to the previous examples, you can also use wildcards with prefixes and suffixes. Go wild!

## Optional Path Parameters

Optional path parameters allow you to define route segments that may or may not be present in the URL. They use the `{-$paramName}` syntax and provide flexible routing patterns where certain parameters are optional.

### Defining Optional Parameters

Optional path parameters are defined using curly braces with a dash prefix: `{-$paramName}`

```tsx
// Single optional parameter
// src/routes/posts/{-$category}.tsx
export const Route = createFileRoute('/posts/{-$category}')({
  component: PostsComponent,
})

// Multiple optional parameters
// src/routes/posts/{-$category}/{-$slug}.tsx
export const Route = createFileRoute('/posts/{-$category}/{-$slug}')({
  component: PostComponent,
})

// Mixed required and optional parameters
// src/routes/users/$id/{-$tab}.tsx
export const Route = createFileRoute('/users/$id/{-$tab}')({
  component: UserComponent,
})
```

### How Optional Parameters Work

Optional parameters create flexible URL patterns:

- `/posts/{-$category}` matches both `/posts` and `/posts/tech`
- `/posts/{-$category}/{-$slug}` matches `/posts`, `/posts/tech`, and `/posts/tech/hello-world`
- `/users/$id/{-$tab}` matches `/users/123` and `/users/123/settings`

When an optional parameter is not present in the URL, its value will be `undefined` in your route handlers and components.

### Accessing Optional Parameters

Optional parameters work exactly like regular parameters in your components, but their values may be `undefined`:

```tsx
function PostsComponent() {
  const { category } = Route.useParams()

  return <div>{category ? `Posts in ${category}` : 'All Posts'}</div>
}
```

### Optional Parameters in Loaders

Optional parameters are available in loaders and may be `undefined`:

```tsx
export const Route = createFileRoute('/posts/{-$category}')({
  loader: async ({ params }) => {
    // params.category might be undefined
    return fetchPosts({ category: params.category })
  },
})
```

### Optional Parameters in beforeLoad

Optional parameters work in `beforeLoad` handlers as well:

```tsx
export const Route = createFileRoute('/posts/{-$category}')({
  beforeLoad: async ({ params }) => {
    if (params.category) {
      // Validate category exists
      await validateCategory(params.category)
    }
  },
})
```

### Advanced Optional Parameter Patterns

#### With Prefix and Suffix

Optional parameters support prefix and suffix patterns:

```tsx
// File route: /files/prefix{-$name}.txt
// Matches: /files/prefix.txt and /files/prefixdocument.txt
export const Route = createFileRoute('/files/prefix{-$name}.txt')({
  component: FileComponent,
})

function FileComponent() {
  const { name } = Route.useParams()
  return <div>File: {name || 'default'}</div>
}
```

#### All Optional Parameters

You can create routes where all parameters are optional:

```tsx
// Route: /{-$year}/{-$month}/{-$day}
// Matches: /, /2023, /2023/12, /2023/12/25
export const Route = createFileRoute('/{-$year}/{-$month}/{-$day}')({
  component: DateComponent,
})

function DateComponent() {
  const { year, month, day } = Route.useParams()

  if (!year) return <div>Select a year</div>
  if (!month) return <div>Year: {year}</div>
  if (!day)
    return (
      <div>
        Month: {year}/{month}
      </div>
    )

  return (
    <div>
      Date: {year}/{month}/{day}
    </div>
  )
}
```

#### Optional Parameters with Wildcards

Optional parameters can be combined with wildcards for complex routing patterns:

```tsx
// Route: /docs/{-$version}/$
// Matches: /docs/extra/path, /docs/v2/extra/path
export const Route = createFileRoute('/docs/{-$version}/$')({
  component: DocsComponent,
})

function DocsComponent() {
  const { version } = Route.useParams()
  const { _splat } = Route.useParams()

  return (
    <div>
      Version: {version || 'latest'}
      Path: {_splat}
    </div>
  )
}
```

### Navigating with Optional Parameters

When navigating to routes with optional parameters, you have fine-grained control over which parameters to include:

```tsx
function Navigation() {
  return (
    <div>
      {/* Navigate with optional parameter */}
      <Link to="/posts/{-$category}" params={{ category: 'tech' }}>
        Tech Posts
      </Link>

      {/* Navigate without optional parameter */}
      <Link to="/posts/{-$category}" params={{ category: undefined }}>
        All Posts
      </Link>

      {/* Navigate with multiple optional parameters */}
      <Link to="/posts/{-$category}/{-$slug}" params={{ category: 'tech', slug: 'react-tips' }}>
        Specific Post
      </Link>
    </div>
  )
}
```

### Type Safety with Optional Parameters

TypeScript provides full type safety for optional parameters:

```tsx
function PostsComponent() {
  // TypeScript knows category might be undefined
  const { category } = Route.useParams() // category: string | undefined

  // Safe navigation
  const categoryUpper = category?.toUpperCase()

  return <div>{categoryUpper || 'All Categories'}</div>
}

// Navigation is type-safe and flexible
<Link
  to="/posts/{-$category}"
  params={{ category: 'tech' }} // ✅ Valid - string
>
  Tech Posts
</Link>

<Link
  to="/posts/{-$category}"
  params={{ category: 123 }} // ✅ Valid - number (auto-stringified)
>
  Category 123
</Link>
```

## Internationalization (i18n) with Optional Path Parameters

Optional path parameters are excellent for implementing internationalization (i18n) routing patterns. You can use prefix patterns to handle multiple languages while maintaining clean, SEO-friendly URLs.

### Prefix-based i18n

Use optional language prefixes to support URLs like `/en/about`, `/fr/about`, or just `/about` (default language):

```tsx
// Route: /{-$locale}/about
export const Route = createFileRoute('/{-$locale}/about')({
  component: AboutComponent,
})

function AboutComponent() {
  const { locale } = Route.useParams()
  const currentLocale = locale || 'en' // Default to English

  const content = {
    en: { title: 'About Us', description: 'Learn more about our company.' },
    fr: {
      title: 'À Propos',
      description: 'En savoir plus sur notre entreprise.',
    },
    es: {
      title: 'Acerca de',
      description: 'Conoce más sobre nuestra empresa.',
    },
  }

  return (
    <div>
      <h1>{content[currentLocale]?.title}</h1>
      <p>{content[currentLocale]?.description}</p>
    </div>
  )
}
```

This pattern matches:

- `/about` (default locale)
- `/en/about` (explicit English)
- `/fr/about` (French)
- `/es/about` (Spanish)

### Complex i18n Patterns

Combine optional parameters for more sophisticated i18n routing:

```tsx
// Route: /{-$locale}/blog/{-$category}/$slug
export const Route = createFileRoute('/{-$locale}/blog/{-$category}/$slug')({
  beforeLoad: async ({ params }) => {
    const locale = params.locale || 'en'
    const category = params.category

    // Validate locale and category
    const validLocales = ['en', 'fr', 'es', 'de']
    if (locale && !validLocales.includes(locale)) {
      throw new Error('Invalid locale')
    }

    return { locale, category }
  },
  loader: async ({ params, context }) => {
    const { locale } = context
    const { slug, category } = params

    return fetchBlogPost({ slug, category, locale })
  },
  component: BlogPostComponent,
})

function BlogPostComponent() {
  const { locale, category, slug } = Route.useParams()
  const data = Route.useLoaderData()

  return (
    <article>
      <h1>{data.title}</h1>
      <p>
        Category: {category || 'All'} | Language: {locale || 'en'}
      </p>
      <div>{data.content}</div>
    </article>
  )
}
```

This supports URLs like:

- `/blog/tech/my-post` (default locale, tech category)
- `/fr/blog/my-post` (French, no category)
- `/en/blog/tech/my-post` (explicit English, tech category)
- `/es/blog/tecnologia/mi-post` (Spanish, Spanish category)

### Language Navigation

Create language switchers using optional i18n parameters with function-style params:

```tsx
function LanguageSwitcher() {
  const currentParams = useParams({ strict: false })

  const languages = [
    { code: 'en', name: 'English' },
    { code: 'fr', name: 'Français' },
    { code: 'es', name: 'Español' },
  ]

  return (
    <div className="language-switcher">
      {languages.map(({ code, name }) => (
        <Link
          key={code}
          to="/{-$locale}/blog/{-$category}/$slug"
          params={(prev) => ({
            ...prev,
            locale: code === 'en' ? undefined : code, // Remove 'en' for clean URLs
          })}
          className={currentParams.locale === code ? 'active' : ''}
        >
          {name}
        </Link>
      ))}
    </div>
  )
}
```

You can also create more sophisticated language switching logic:

```tsx
function AdvancedLanguageSwitcher() {
  const currentParams = useParams({ strict: false })

  const handleLanguageChange = (newLocale: string) => {
    return (prev: any) => {
      // Preserve all existing params but update locale
      const updatedParams = { ...prev }

      if (newLocale === 'en') {
        // Remove locale for clean English URLs
        delete updatedParams.locale
      } else {
        updatedParams.locale = newLocale
      }

      return updatedParams
    }
  }

  return (
    <div className="language-switcher">
      <Link to="/{-$locale}/blog/{-$category}/$slug" params={handleLanguageChange('fr')}>
        Français
      </Link>

      <Link to="/{-$locale}/blog/{-$category}/$slug" params={handleLanguageChange('es')}>
        Español
      </Link>

      <Link to="/{-$locale}/blog/{-$category}/$slug" params={handleLanguageChange('en')}>
        English
      </Link>
    </div>
  )
}
```

### Advanced i18n with Optional Parameters

Organize i18n routes using optional parameters for flexible locale handling:

```tsx
// Route structure:
// routes/
//   {-$locale}/
//     index.tsx        // /, /en, /fr
//     about.tsx        // /about, /en/about, /fr/about
//     blog/
//       index.tsx      // /blog, /en/blog, /fr/blog
//       $slug.tsx      // /blog/post, /en/blog/post, /fr/blog/post

// routes/{-$locale}/index.tsx
export const Route = createFileRoute('/{-$locale}/')({
  component: HomeComponent,
})

function HomeComponent() {
  const { locale } = Route.useParams()
  const isRTL = ['ar', 'he', 'fa'].includes(locale || '')

  return (
    <div dir={isRTL ? 'rtl' : 'ltr'}>
      <h1>Welcome ({locale || 'en'})</h1>
      {/* Localized content */}
    </div>
  )
}

// routes/{-$locale}/about.tsx
export const Route = createFileRoute('/{-$locale}/about')({
  component: AboutComponent,
})
```

### SEO and Canonical URLs

Handle SEO for i18n routes properly:

```tsx
export const Route = createFileRoute('/{-$locale}/products/$id')({
  component: ProductComponent,
  head: ({ params, loaderData }) => {
    const locale = params.locale || 'en'
    const product = loaderData

    return {
      title: product.title[locale] || product.title.en,
      meta: [
        {
          name: 'description',
          content: product.description[locale] || product.description.en,
        },
        {
          property: 'og:locale',
          content: locale,
        },
      ],
      links: [
        // Canonical URL (always use default locale format)
        {
          rel: 'canonical',
          href: `https://example.com/products/${params.id}`,
        },
        // Alternate language versions
        {
          rel: 'alternate',
          hreflang: 'en',
          href: `https://example.com/products/${params.id}`,
        },
        {
          rel: 'alternate',
          hreflang: 'fr',
          href: `https://example.com/fr/products/${params.id}`,
        },
        {
          rel: 'alternate',
          hreflang: 'es',
          href: `https://example.com/es/products/${params.id}`,
        },
      ],
    }
  },
})
```

### Type Safety for i18n

Ensure type safety for your i18n implementations:

```tsx
// Define supported locales
type Locale = 'en' | 'fr' | 'es' | 'de'

// Type-safe locale validation
function validateLocale(locale: string | undefined): locale is Locale {
  return ['en', 'fr', 'es', 'de'].includes(locale as Locale)
}

export const Route = createFileRoute('/{-$locale}/shop/{-$category}')({
  beforeLoad: async ({ params }) => {
    const { locale } = params

    // Type-safe locale validation
    if (locale && !validateLocale(locale)) {
      throw redirect({
        to: '/shop/{-$category}',
        params: { category: params.category },
      })
    }

    return {
      locale: (locale as Locale) || 'en',
      isDefaultLocale: !locale || locale === 'en',
    }
  },
  component: ShopComponent,
})

function ShopComponent() {
  const { locale, category } = Route.useParams()
  const { isDefaultLocale } = Route.useRouteContext()

  // TypeScript knows locale is Locale | undefined
  // and we have validated it in beforeLoad

  return (
    <div>
      <h1>Shop {category ? `- ${category}` : ''}</h1>
      <p>Language: {locale || 'en'}</p>
      {!isDefaultLocale && (
        <Link to="/shop/{-$category}" params={{ category }}>
          View in English
        </Link>
      )}
    </div>
  )
}
```

Optional path parameters provide a powerful and flexible foundation for implementing internationalization in your TanStack Router applications. Whether you prefer prefix-based or combined approaches, you can create clean, SEO-friendly URLs while maintaining excellent developer experience and type safety.

## Allowed Characters

By default, path params are escaped with `encodeURIComponent`. If you want to allow other valid URI characters (e.g. `@` or `+`), you can specify that in your [RouterOptions](../../api/router/RouterOptionsType.md#pathparamsallowedcharacters-property).

Example usage:

```tsx
const router = createRouter({
  // ...
  pathParamsAllowedCharacters: ['@'],
})
```

The following is the list of accepted allowed characters:

- `;`
- `:`
- `@`
- `&`
- `=`
- `+`
- `$`
- `,`

# Preloading

Preloading in TanStack Router is a way to load a route before the user actually navigates to it. This is useful for routes that are likely to be visited by the user next. For example, if you have a list of posts and the user is likely to click on one of them, you can preload the post route so that it's ready to go when the user clicks on it.

## Supported Preloading Strategies

- Intent
  - Preloading by **"intent"** works by using hover and touch start events on `<Link>` components to preload the dependencies for the destination route.
  - This strategy is useful for preloading routes that the user is likely to visit next.
- Viewport Visibility
  - Preloading by **"viewport**" works by using the Intersection Observer API to preload the dependencies for the destination route when the `<Link>` component is in the viewport.
  - This strategy is useful for preloading routes that are below the fold or off-screen.
- Render
  - Preloading by **"render"** works by preloading the dependencies for the destination route as soon as the `<Link>` component is rendered in the DOM.
  - This strategy is useful for preloading routes that are always needed.

## How long does preloaded data stay in memory?

Preloaded route matches are temporarily cached in memory with a few important caveats:

- **Unused preloaded data is removed after 30 seconds by default.** This can be configured by setting the `defaultPreloadMaxAge` option on your router.
- **Obviously, when a route is loaded, its preloaded version is promoted to the router's normal pending matches state.**

If you need more control over preloading, caching and/or garbage collection of preloaded data, you should use an external caching library like [TanStack Query](https://tanstack.com/query).

The simplest way to preload routes for your application is to set the `defaultPreload` option to `intent` for your entire router:

```tsx
import { createRouter } from '@tanstack/react-router'

const router = createRouter({
  // ...
  defaultPreload: 'intent',
})
```

This will turn on `intent` preloading by default for all `<Link>` components in your application. You can also set the `preload` prop on individual `<Link>` components to override the default behavior.

## Preload Delay

By default, preloading will start after **50ms** of the user hovering or touching a `<Link>` component. You can change this delay by setting the `defaultPreloadDelay` option on your router:

```tsx
import { createRouter } from '@tanstack/react-router'

const router = createRouter({
  // ...
  defaultPreloadDelay: 100,
})
```

You can also set the `preloadDelay` prop on individual `<Link>` components to override the default behavior on a per-link basis.

## Built-in Preloading & `preloadStaleTime`

If you're using the built-in loaders, you can control how long preloaded data is considered fresh until another preload is triggered by setting either `routerOptions.defaultPreloadStaleTime` or `routeOptions.preloadStaleTime` to a number of milliseconds. **By default, preloaded data is considered fresh for 30 seconds.**.

To change this, you can set the `defaultPreloadStaleTime` option on your router:

```tsx
import { createRouter } from '@tanstack/react-router'

const router = createRouter({
  // ...
  defaultPreloadStaleTime: 10_000,
})
```

Or, you can use the `routeOptions.preloadStaleTime` option on individual routes:

```tsx
// src/routes/posts.$postId.tsx
export const Route = createFileRoute('/posts/$postId')({
  loader: async ({ params }) => fetchPost(params.postId),
  // Preload the route again if the preload cache is older than 10 seconds
  preloadStaleTime: 10_000,
})
```

## Preloading with External Libraries

When integrating external caching libraries like React Query, which have their own mechanisms for determining stale data, you may want to override the default preloading and stale-while-revalidate logic of TanStack Router. These libraries often use options like staleTime to control the freshness of data.

To customize the preloading behavior in TanStack Router and fully leverage your external library's caching strategy, you can bypass the built-in caching by setting routerOptions.defaultPreloadStaleTime or routeOptions.preloadStaleTime to 0. This ensures that all preloads are marked as stale internally, and loaders are always invoked, allowing your external library, such as React Query, to manage data loading and caching.

For example:

```tsx
import { createRouter } from '@tanstack/react-router'

const router = createRouter({
  // ...
  defaultPreloadStaleTime: 0,
})
```

This would then allow you, for instance, to use an option like React Query's `staleTime` to control the freshness of your preloads.

## Preloading Manually

If you need to manually preload a route, you can use the router's `preloadRoute` method. It accepts a standard TanStack `NavigateOptions` object and returns a promise that resolves when the route is preloaded.

```tsx
function Component() {
  const router = useRouter()

  useEffect(() => {
    async function preload() {
      try {
        const matches = await router.preloadRoute({
          to: postRoute,
          params: { id: 1 },
        })
      } catch (err) {
        // Failed to preload route
      }
    }

    preload()
  }, [router])

  return <div />
}
```

If you need to preload only the JS chunk of a route, you can use the router's `loadRouteChunk` method. It accepts a route object and returns a promise that resolves when the route chunk is loaded.

```tsx
function Component() {
  const router = useRouter()

  useEffect(() => {
    async function preloadRouteChunks() {
      try {
        const postsRoute = router.routesByPath['/posts']
        await Promise.all([
          router.loadRouteChunk(router.routesByPath['/']),
          router.loadRouteChunk(postsRoute),
          router.loadRouteChunk(postsRoute.parentRoute),
        ])
      } catch (err) {
        // Failed to preload route chunk
      }
    }

    preloadRouteChunks()
  }, [router])

  return <div />
}
```

# Render Optimizations

TanStack Router includes several optimizations to ensure your components only re-render when necessary. These optimizations include:

## structural sharing

TanStack Router uses a technique called "structural sharing" to preserve as many references as possible between re-renders, which is particularly useful for state stored in the URL, such as search parameters.

For example, consider a `details` route with two search parameters, `foo` and `bar`, accessed like this:

```tsx
const search = Route.useSearch()
```

When only `bar` is changed by navigating from `/details?foo=f1&bar=b1` to `/details?foo=f1&bar=b2`, `search.foo` will be referentially stable and only `search.bar` will be replaced.

## fine-grained selectors

You can access and subscribe to the router state using various hooks like `useRouterState`, `useSearch`, and others. If you only want a specific component to re-render when a particular subset of the router state such as a subset of the search parameters changes, you can use partial subscriptions with the `select` property.

```tsx
// component won't re-render when `bar` changes
const foo = Route.useSearch({ select: ({ foo }) => foo })
```

### structural sharing with fine-grained selectors

The `select` function can perform various calculations on the router state, allowing you to return different types of values, such as objects. For example:

```tsx
const result = Route.useSearch({
  select: (search) => {
    return {
      foo: search.foo,
      hello: `hello ${search.foo}`,
    }
  },
})
```

Although this works, it will cause your component to re-render each time, since `select` is now returning a new object each time it’s called.

You can avoid this re-rendering issue by using "structural sharing" as described above. By default, structural sharing is turned off to maintain backward compatibility, but this may change in v2.

To enable structural sharing for fine grained selectors, you have two options:

#### Enable it by default in the router options:

```tsx
const router = createRouter({
  routeTree,
  defaultStructuralSharing: true,
})
```

#### Enable it per hook usage as shown here:

```tsx
const result = Route.useSearch({
  select: (search) => {
    return {
      foo: search.foo,
      hello: `hello ${search.foo}`,
    }
  },
  structuralSharing: true,
})
```

> [!IMPORTANT]
> Structural sharing only works with JSON-compatible data. This means you cannot use `select` to return items like class instances if structural sharing is enabled.

In line with TanStack Router's type-safe design, TypeScript will raise an error if you attempt the following:

```tsx
const result = Route.useSearch({
  select: (search) => {
    return {
      date: new Date(),
    }
  },
  structuralSharing: true,
})
```

If structural sharing is enabled by default in the router options, you can prevent this error by setting `structuralSharing: false`.

# Route Masking

Route masking is a way to mask the actual URL of a route that gets persisted to the browser's history and URL bar. This is useful for scenarios where you want to show a different URL than the one that is actually being navigated to and then falling back to the displayed URL when it is shared and (optionally) when the page is reloaded. Here's a few examples:

- Navigating to a modal route like `/photo/5/modal`, but masking the actual URL as `/photos/5`
- Navigating to a modal route like `/post/5/comments`, but masking the actual URL as `/posts/5`
- Navigating to a route with the search param `?showLogin=true`, but masking the URL to _not_ contain the search param
- Navigating to a route with the search param `?modal=settings`, but masking the URL as `/settings'

Each of these scenarios can be achieved with route masking and even extended to support more advanced patterns like [parallel routes](../parallel-routes.md).

## How does route masking work?

> [!IMPORTANT]
> You **do not** need to understand how route masking works in order to use it. This section is for those who are curious about how it works under the hood. Skip to [How do I use route masking?](#how-do-i-use-route-masking) to learn how to use it!.

Route masking utilizes the `location.state` API to store the desired runtime location inside of the location that will get written to the URL. It stores this runtime location under the `__tempLocation` state property:

```tsx
const location = {
  pathname: '/photos/5',
  search: '',
  hash: '',
  state: {
    key: 'wesdfs',
    __tempKey: 'sadfasd',
    __tempLocation: {
      pathname: '/photo/5/modal',
      search: '',
      hash: '',
      state: {},
    },
  },
}
```

When the router parses a location from history with the `location.state.__tempLocation` property, it will use that location instead of the one that was parsed from the URL. This allows you to navigate to a route like `/photos/5` and have the router actually navigate to `/photo/5/modal` instead. When this happens, the history location is saved back into the `location.maskedLocation` property, just in case we need to know what the **actual URL** is. One example of where this is used is in the Devtools where we detect if a route is masked and show the actual URL instead of the masked one!

Remember, you don't need to worry about any of this. It's all handled for you automatically under the hood!

## How do I use route masking?

Route masking is a simple API that can be used in 2 ways:

- Imperatively via the `mask` option available on the `<Link>` and `navigate()` APIs
- Declaratively via the Router's `routeMasks` option

When using either route masking APIs, the `mask` option accepts the same navigation object that the `<Link>` and `navigate()` APIs accept. This means you can use the same `to`, `replace`, `state`, and `search` options that you're already familiar with. The only difference is that the `mask` option will be used to mask the URL of the route being navigated to.

> 🧠 The mask option is also **type-safe**! This means that if you're using TypeScript, you'll get type errors if you try to pass an invalid navigation object to the `mask` option. Booyah!

### Imperative route masking

The `<Link>` and `navigate()` APIs both accept a `mask` option that can be used to mask the URL of the route being navigated to. Here's an example of using it with the `<Link>` component:

```tsx
<Link
  to="/photos/$photoId/modal"
  params={{ photoId: 5 }}
  mask={{
    to: '/photos/$photoId',
    params: {
      photoId: 5,
    },
  }}
>
  Open Photo
</Link>
```

And here's an example of using it with the `navigate()` API:

```tsx
const navigate = useNavigate()

function onOpenPhoto() {
  navigate({
    to: '/photos/$photoId/modal',
    params: { photoId: 5 },
    mask: {
      to: '/photos/$photoId',
      params: {
        photoId: 5,
      },
    },
  })
}
```

### Declarative route masking

In addition to the imperative API, you can also use the Router's `routeMasks` option to declaratively mask routes. Instead of needing to pass the `mask` option to every `<Link>` or `navigate()` call, you can instead create a route mask on the Router to mask routes that match a certain pattern. Here's an example of the same route mask from above, but using the `routeMasks` option instead:

// Use the following for the example below

```tsx
import { createRouteMask } from '@tanstack/react-router'

const photoModalToPhotoMask = createRouteMask({
  routeTree,
  from: '/photos/$photoId/modal',
  to: '/photos/$photoId',
  params: (prev) => ({
    photoId: prev.photoId,
  }),
})

const router = createRouter({
  routeTree,
  routeMasks: [photoModalToPhotoMask],
})
```

When creating a route mask, you'll need to pass 1 argument with at least:

- `routeTree` - The route tree that the route mask will be applied to
- `from` - The route ID that the route mask will be applied to
- `...navigateOptions` - The standard `to`, `search`, `params`, `replace`, etc options that the `<Link>` and `navigate()` APIs accept

> 🧠 The `createRouteMask` option is also **type-safe**! This means that if you're using TypeScript, you'll get type errors if you try to pass an invalid route mask to the `routeMasks` option.

## Unmasking when sharing the URL

URLs are automatically unmasked when they are shared since as soon as a URL is detached from your browsers local history stack, the URL masking data is no longer available. Essentially, as soon as you copy and paste a URL out of your history, its masking data is lost... after all, that's the point of masking a URL!

## Local Unmasking Defaults

**By default, URLs are not unmasked when the page is reloaded locally**. Masking data is stored in the `location.state` property of the history location, so as long as the history location is still in memory in your history stack, the masking data will be available and the URL will continue to be masked.

## Unmasking on page reload

**As stated above, URLs are not unmasked when the page is reloaded by default**.

If you want to unmask a URL locally when the page is reloaded, you have 3 options, each overriding the previous one in priority if passed:

- Set the Router's default `unmaskOnReload` option to `true`
- Return the `unmaskOnReload: true` option from the masking function when creating a route mask with `createRouteMask()`
- Pass the `unmaskOnReload: true` option to the `<Link`> component or `navigate()` API

# Router Context

TanStack Router's router context is a very powerful tool that can be used for dependency injection among many other things. Aptly named, the router context is passed through the router and down through each matching route. At each route in the hierarchy, the context can be modified or added to. Here's a few ways you might use the router context practically:

- Dependency Injection
  - You can supply dependencies (e.g. a loader function, a data fetching client, a mutation service) which the route and all child routes can access and use without importing or creating directly.
- Breadcrumbs
  - While the main context object for each route is merged as it descends, each route's unique context is also stored making it possible to attach breadcrumbs or methods to each route's context.
- Dynamic meta tag management
  - You can attach meta tags to each route's context and then use a meta tag manager to dynamically update the meta tags on the page as the user navigates the site.

These are just suggested uses of the router context. You can use it for whatever you want!

## Typed Router Context

Like everything else, the root router context is strictly typed. This type can be augmented via any route's `beforeLoad` option as it is merged down the route match tree. To constrain the type of the root router context, you must use the `createRootRouteWithContext<YourContextTypeHere>()(routeOptions)` function to create a new router context instead of the `createRootRoute()` function to create your root route. Here's an example:

```tsx
import { createRootRouteWithContext, createRouter } from '@tanstack/react-router'

interface MyRouterContext {
  user: User
}

// Use the routerContext to create your root route
const rootRoute = createRootRouteWithContext<MyRouterContext>()({
  component: App,
})

const routeTree = rootRoute.addChildren([
  // ...
])

// Use the routerContext to create your router
const router = createRouter({
  routeTree,
})
```

## Passing the initial Router Context

The router context is passed to the router at instantiation time. You can pass the initial router context to the router via the `context` option:

> [!TIP]
> If your context has any required properties, you will see a TypeScript error if you don't pass them in the initial router context. If all of your context properties are optional, you will not see a TypeScript error and passing the context will be optional. If you don't pass a router context, it defaults to `{}`.

```tsx
import { createRouter } from '@tanstack/react-router'

// Use the routerContext you created to create your router
const router = createRouter({
  routeTree,
  context: {
    user: {
      id: '123',
      name: 'John Doe',
    },
  },
})
```

### Invalidating the Router Context

If you need to invalidate the context state you are passing into the router, you can call the `invalidate` method to tell the router to recompute the context. This is useful when you need to update the context state and have the router recompute the context for all routes.

```tsx
function useAuth() {
  const router = useRouter()
  const [user, setUser] = useState<User | null>(null)

  useEffect(() => {
    const unsubscribe = auth.onAuthStateChanged((user) => {
      setUser(user)
      router.invalidate()
    })

    return unsubscribe
  }, [])

  return user
}
```

## Using the Router Context

Once you have defined the router context type, you can use it in your route definitions:

```tsx
// src/routes/todos.tsx
export const Route = createFileRoute('/todos')({
  component: Todos,
  loader: ({ context }) => fetchTodosByUserId(context.user.id),
})
```

You can even inject data fetching and mutation implementations themselves! In fact, this is highly recommended 😜

Let's try this with a simple function to fetch some todos:

```tsx
const fetchTodosByUserId = async ({ userId }) => {
  const response = await fetch(`/api/todos?userId=${userId}`)
  const data = await response.json()
  return data
}

const router = createRouter({
  routeTree: rootRoute,
  context: {
    userId: '123',
    fetchTodosByUserId,
  },
})
```

Then, in your route:

```tsx
// src/routes/todos.tsx
export const Route = createFileRoute('/todos')({
  component: Todos,
  loader: ({ context }) => context.fetchTodosByUserId(context.userId),
})
```

### How about an external data fetching library?

```tsx
import { createRootRouteWithContext, createRouter } from '@tanstack/react-router'

interface MyRouterContext {
  queryClient: QueryClient
}

const rootRoute = createRootRouteWithContext<MyRouterContext>()({
  component: App,
})

const queryClient = new QueryClient()

const router = createRouter({
  routeTree: rootRoute,
  context: {
    queryClient,
  },
})
```

Then, in your route:

```tsx
// src/routes/todos.tsx
export const Route = createFileRoute('/todos')({
  component: Todos,
  loader: async ({ context }) => {
    await context.queryClient.ensureQueryData({
      queryKey: ['todos', { userId: user.id }],
      queryFn: fetchTodos,
    })
  },
})
```

## How about using React Context/Hooks?

When trying to use React Context or Hooks in your route's `beforeLoad` or `loader` functions, it's important to remember React's [Rules of Hooks](https://react.dev/reference/rules/rules-of-hooks). You can't use hooks in a non-React function, so you can't use hooks in your `beforeLoad` or `loader` functions.

So, how do we use React Context or Hooks in our route's `beforeLoad` or `loader` functions? We can use the router context to pass down the React Context or Hooks to our route's `beforeLoad` or `loader` functions.

Let's look at the setup for an example, where we pass down a `useNetworkStrength` hook to our route's `loader` function:

- `src/routes/__root.tsx`

```tsx
// First, make sure the context for the root route is typed
import { createRootRouteWithContext } from '@tanstack/react-router'
import { useNetworkStrength } from '@/hooks/useNetworkStrength'

interface MyRouterContext {
  networkStrength: ReturnType<typeof useNetworkStrength>
}

export const Route = createRootRouteWithContext<MyRouterContext>()({
  component: App,
})
```

In this example, we'd instantiate the hook before rendering the router using the `<RouterProvider />`. This way, the hook would be called in React-land, therefore adhering to the Rules of Hooks.

- `src/router.tsx`

```tsx
import { createRouter } from '@tanstack/react-router'

import { routeTree } from './routeTree.gen'

export const router = createRouter({
  routeTree,
  context: {
    networkStrength: undefined!, // We'll set this in React-land
  },
})
```

- `src/main.tsx`

```tsx
import { RouterProvider } from '@tanstack/react-router'
import { router } from './router'

import { useNetworkStrength } from '@/hooks/useNetworkStrength'

function App() {
  const networkStrength = useNetworkStrength()
  // Inject the returned value from the hook into the router context
  return <RouterProvider router={router} context={{ networkStrength }} />
}

// ...
```

So, now in our route's `loader` function, we can access the `networkStrength` hook from the router context:

- `src/routes/posts.tsx`

```tsx
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/posts')({
  component: Posts,
  loader: ({ context }) => {
    if (context.networkStrength === 'STRONG') {
      // Do something
    }
  },
})
```

## Modifying the Router Context

The router context is passed down the route tree and is merged at each route. This means that you can modify the context at each route and the modifications will be available to all child routes. Here's an example:

- `src/routes/__root.tsx`

```tsx
import { createRootRouteWithContext } from '@tanstack/react-router'

interface MyRouterContext {
  foo: boolean
}

export const Route = createRootRouteWithContext<MyRouterContext>()({
  component: App,
})
```

- `src/router.tsx`

```tsx
import { createRouter } from '@tanstack/react-router'

import { routeTree } from './routeTree.gen'

const router = createRouter({
  routeTree,
  context: {
    foo: true,
  },
})
```

- `src/routes/todos.tsx`

```tsx
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/todos')({
  component: Todos,
  beforeLoad: () => {
    return {
      bar: true,
    }
  },
  loader: ({ context }) => {
    context.foo // true
    context.bar // true
  },
})
```

## Processing Accumulated Route Context

Context, especially the isolated route `context` objects, make it trivial to accumulate and process the route context objects for all matched routes. Here's an example where we use all of the matched route contexts to generate a breadcrumb trail:

```tsx
// src/routes/__root.tsx
export const Route = createRootRoute({
  component: () => {
    const matches = useRouterState({ select: (s) => s.matches })

    const breadcrumbs = matches
      .filter((match) => match.context.getTitle)
      .map(({ pathname, context }) => {
        return {
          title: context.getTitle(),
          path: pathname,
        }
      })

    // ...
  },
})
```

Using that same route context, we could also generate a title tag for our page's `<head>`:

```tsx
// src/routes/__root.tsx
export const Route = createRootRoute({
  component: () => {
    const matches = useRouterState({ select: (s) => s.matches })

    const matchWithTitle = [...matches].reverse().find((d) => d.context.getTitle)

    const title = matchWithTitle?.context.getTitle() || 'My App'

    return (
      <html>
        <head>
          <title>{title}</title>
        </head>
        <body>{/* ... */}</body>
      </html>
    )
  },
})
```

# Scroll Restoration

## Hash/Top-of-Page Scrolling

Out of the box, TanStack Router supports both **hash scrolling** and **top-of-page scrolling** without any additional configuration.

## Scroll-to-top & Nested Scrollable Areas

By default, scroll-to-top mimics the behavior of the browser, which means only the `window` itself is scrolled to the top after successful navigation. For many apps however, it's common for the main scrollable area to be a nested div or similar because of advanced layouts. If you would like TanStack Router to also scroll these main scrollable areas for you, you can add selectors to target them using the `routerOptions.scrollToTopSelectors`:

```tsx
const router = createRouter({
  scrollToTopSelectors: ['#main-scrollable-area'],
})
```

For complex selectors that cannot be simply resolved using `document.querySelector(selector)`, you can pass functions that return HTML elements to `routerOptions.scrollToTopSelectors`:

```tsx
const selector = () => document.querySelector('#shadowRootParent')?.shadowRoot?.querySelector('#main-scrollable-area')

const router = createRouter({
  scrollToTopSelectors: [selector],
})
```

These selectors are handled **in addition to `window`** which cannot be disabled currently.

## Scroll Restoration

Scroll restoration is the process of restoring the scroll position of a page when the user navigates back to it. This is normally a built-in feature for standard HTML based websites, but can be difficult to replicate for SPA applications because:

- SPAs typically use the `history.pushState` API for navigation, so the browser doesn't know to restore the scroll position natively
- SPAs sometimes render content asynchronously, so the browser doesn't know the height of the page until after it's rendered
- SPAs can sometimes use nested scrollable containers to force specific layouts and features.

Not only that, but it's very common for applications to have multiple scrollable areas within an app, not just the body. For example, a chat application might have a scrollable sidebar and a scrollable chat area. In this case, you would want to restore the scroll position of both areas independently.

To alleviate this problem, TanStack Router provides a scroll restoration component and hook that handle the process of monitoring, caching and restoring scroll positions for you.

It does this by:

- Monitoring the DOM for scroll events
- Registering scrollable areas with the scroll restoration cache
- Listening to the proper router events to know when to cache and restore scroll positions
- Storing scroll positions for each scrollable area in the cache (including `window` and `body`)
- Restoring scroll positions after successful navigations before DOM paint

That may sound like a lot, but for you, it's as simple as this:

```tsx
import { createRouter } from '@tanstack/react-router'

const router = createRouter({
  scrollRestoration: true,
})
```

> [!NOTE]
> The `<ScrollRestoration />` component still works, but has been deprecated.

## Custom Cache Keys

Falling in behind Remix's own Scroll Restoration APIs, you can also customize the key used to cache scroll positions for a given scrollable area using the `getKey` option. This could be used, for example, to force the same scroll position to be used regardless of the users browser history.

The `getKey` option receives the relevant `Location` state from TanStack Router and expects you to return a string to uniquely identify the scrollable measurements for that state.

The default `getKey` is `(location) => location.state.__TSR_key!`, where `__TSR_key` is the unique key generated for each entry in the history.

> Older versions, prior to `v1.121.34`, used `state.key` as the default key, but this has been deprecated in favor of `state.__TSR_key`. For now, `location.state.key` will still be available for compatibility, but it will be removed in the next major version.

## Examples

You could sync scrolling to the pathname:

```tsx
import { createRouter } from '@tanstack/react-router'

const router = createRouter({
  getScrollRestorationKey: (location) => location.pathname,
})
```

You can conditionally sync only some paths, then use the key for the rest:

```tsx
import { createRouter } from '@tanstack/react-router'

const router = createRouter({
  getScrollRestorationKey: (location) => {
    const paths = ['/', '/chat']
    return paths.includes(location.pathname) ? location.pathname : location.state.__TSR_key!
  },
})
```

## Preventing Scroll Restoration

Sometimes you may want to prevent scroll restoration from happening. To do this you can utilize the `resetScroll` option available on the following APIs:

- `<Link resetScroll={false}>`
- `navigate({ resetScroll: false })`
- `redirect({ resetScroll: false })`

When `resetScroll` is set to `false`, the scroll position for the next navigation will not be restored (if navigating to an existing history event in the stack) or reset to the top (if it's a new history event in the stack).

## Manual Scroll Restoration

Most of the time, you won't need to do anything special to get scroll restoration to work. However, there are some cases where you may need to manually control scroll restoration. The most common example is **virtualized lists**.

To manually control scroll restoration for virtualized lists within the whole browser window:

[//]: # 'VirtualizedWindowScrollRestorationExample'

```tsx
function Component() {
  const scrollEntry = useElementScrollRestoration({
    getElement: () => window,
  })

  // Let's use TanStack Virtual to virtualize some content!
  const virtualizer = useWindowVirtualizer({
    count: 10000,
    estimateSize: () => 100,
    // We pass the scrollY from the scroll restoration entry to the virtualizer
    // as the initial offset
    initialOffset: scrollEntry?.scrollY,
  })

  return (
    <div>
      {virtualizer.getVirtualItems().map(item => (
        ...
      ))}
    </div>
  )
}
```

[//]: # 'VirtualizedWindowScrollRestorationExample'

To manually control scroll restoration for a specific element, you can use the `useElementScrollRestoration` hook and the `data-scroll-restoration-id` DOM attribute:

[//]: # 'ManualRestorationExample'

```tsx
function Component() {
  // We need a unique ID for manual scroll restoration on a specific element
  // It should be as unique as possible for this element across your app
  const scrollRestorationId = 'myVirtualizedContent'

  // We use that ID to get the scroll entry for this element
  const scrollEntry = useElementScrollRestoration({
    id: scrollRestorationId,
  })

  // Let's use TanStack Virtual to virtualize some content!
  const virtualizerParentRef = React.useRef<HTMLDivElement>(null)
  const virtualizer = useVirtualizer({
    count: 10000,
    getScrollElement: () => virtualizerParentRef.current,
    estimateSize: () => 100,
    // We pass the scrollY from the scroll restoration entry to the virtualizer
    // as the initial offset
    initialOffset: scrollEntry?.scrollY,
  })

  return (
    <div
      ref={virtualizerParentRef}
      // We pass the scroll restoration ID to the element
      // as a custom attribute that will get picked up by the
      // scroll restoration watcher
      data-scroll-restoration-id={scrollRestorationId}
      className="flex-1 border rounded-lg overflow-auto relative"
    >
      ...
    </div>
  )
}
```

[//]: # 'ManualRestorationExample'

## Scroll Behavior

To control the scroll behavior when navigating between pages, you can use the `scrollRestorationBehavior` option. This allows you to make the transition between pages instant instead of a smooth scroll. The global configuration of scroll restoration behavior has the same options as those supported by the browser, which are `smooth`, `instant`, and `auto` (see [MDN](https://developer.mozilla.org/en-US/docs/Web/API/Element/scrollIntoView#behavior) for more information).

```tsx
import { createRouter } from '@tanstack/react-router'

const router = createRouter({
  scrollRestorationBehavior: 'instant',
})
```

# Search Params

Similar to how TanStack Query made handling server-state in your React and Solid applications a breeze, TanStack Router aims to unlock the power of URL search params in your applications.

> 🧠 If you are on a really old browser, like IE11, you may need to use a polyfill for `URLSearchParams`.

## Why not just use `URLSearchParams`?

We get it, you've been hearing a lot of "use the platform" lately and for the most part, we agree. However, we also believe it's important to recognize where the platform falls short for more advanced use-cases and we believe `URLSearchParams` is one of these circumstances.

Traditional Search Param APIs usually assume a few things:

- Search params are always strings
- They are _mostly_ flat
- Serializing and deserializing using `URLSearchParams` is good enough (Spoiler alert: it's not.)
- Search params modifications are tightly coupled with the URL's pathname and must be updated together, even if the pathname is not changing.

Reality is very different from these assumptions though.

- Search params represent application state, so inevitably, we will expect them to have the same DX associated with other state managers. This means having the capability of distinguishing between primitive value types and efficiently storing and manipulating complex data structures like nested arrays and objects.
- There are many ways to serialize and deserialize state with different tradeoffs. You should be able to choose the best one for your application or at the very least get a better default than `URLSearchParams`.
- Immutability & Structural Sharing. Every time you stringify and parse url search params, referential integrity and object identity is lost because each new parse creates a brand new data structure with a unique memory reference. If not properly managed over its lifetime, this constant serialization and parsing can result in unexpected and undesirable performance issues, especially in frameworks like React that choose to track reactivity via immutability or in Solid that normally relies on reconciliation to detect changes from deserialized data sources.
- Search params, while an important part of the URL, do frequently change independently of the URL's pathname. For example, a user may want to change the page number of a paginated list without touching the URL's pathname.

## Search Params, the "OG" State Manager

You've probably seen search params like `?page=3` or `?filter-name=tanner` in the URL. There is no question that this is truly **a form of global state** living inside of the URL. It's valuable to store specific pieces of state in the URL because:

- Users should be able to:
  - Cmd/Ctrl + Click to open a link in a new tab and reliably see the state they expected
  - Bookmark and share links from your application with others with assurances that they will see exactly the state as when the link was copied.
  - Refresh your app or navigate back and forth between pages without losing their state
- Developers should be able to easily:
  - Add, remove or modify state in the URL with the same great DX as other state managers
  - Easily validate search params coming from the URL in a format and type that is safe for their application to consume
  - Read and write to search params without having to worry about the underlying serialization format

## JSON-first Search Params

To achieve the above, the first step built in to TanStack Router is a powerful search param parser that automatically converts the search string of your URL to structured JSON. This means that you can store any JSON-serializable data structure in your search params and it will be parsed and serialized as JSON. This is a huge improvement over `URLSearchParams` which has limited support for array-like structures and nested data.

For example, navigating to the following route:

```tsx
const link = (
  <Link
    to="/shop"
    search={{
      pageIndex: 3,
      includeCategories: ['electronics', 'gifts'],
      sortBy: 'price',
      desc: true,
    }}
  />
)
```

Will result in the following URL:

```
/shop?pageIndex=3&includeCategories=%5B%22electronics%22%2C%22gifts%22%5D&sortBy=price&desc=true
```

When this URL is parsed, the search params will be accurately converted back to the following JSON:

```json
{
  "pageIndex": 3,
  "includeCategories": ["electronics", "gifts"],
  "sortBy": "price",
  "desc": true
}
```

If you noticed, there are a few things going on here:

- The first level of the search params is flat and string based, just like `URLSearchParams`.
- First level values that are not strings are accurately preserved as actual numbers and booleans.
- Nested data structures are automatically converted to URL-safe JSON strings

> 🧠 It's common for other tools to assume that search params are always flat and string-based which is why we've chosen to keep things URLSearchParam compliant at the first level. This ultimately means that even though TanStack Router is managing your nested search params as JSON, other tools will still be able to write to the URL and read first-level params normally.

## Validating and Typing Search Params

Despite TanStack Router being able to parse search params into reliable JSON, they ultimately still came from **a user-facing raw-text input**. Similar to other serialization boundaries, this means that before you consume search params, they should be validated into a format that your application can trust and rely on.

### Enter Validation + TypeScript!

TanStack Router provides convenient APIs for validating and typing search params. This all starts with the `Route`'s `validateSearch` option:

```tsx
// /routes/shop.products.tsx

type ProductSearchSortOptions = 'newest' | 'oldest' | 'price'

type ProductSearch = {
  page: number
  filter: string
  sort: ProductSearchSortOptions
}

export const Route = createFileRoute('/shop/products')({
  validateSearch: (search: Record<string, unknown>): ProductSearch => {
    // validate and parse the search params into a typed state
    return {
      page: Number(search?.page ?? 1),
      filter: (search.filter as string) || '',
      sort: (search.sort as ProductSearchSortOptions) || 'newest',
    }
  },
})
```

In the above example, we're validating the search params of the `Route` and returning a typed `ProductSearch` object. This typed object is then made available to this route's other options **and any child routes, too!**

### Validating Search Params

The `validateSearch` option is a function that is provided the JSON parsed (but non-validated) search params as a `Record<string, unknown>` and returns a typed object of your choice. It's usually best to provide sensible fallbacks for malformed or unexpected search params so your users' experience stays non-interrupted.

Here's an example:

```tsx
// /routes/shop.products.tsx

type ProductSearchSortOptions = 'newest' | 'oldest' | 'price'

type ProductSearch = {
  page: number
  filter: string
  sort: ProductSearchSortOptions
}

export const Route = createFileRoute('/shop/products')({
  validateSearch: (search: Record<string, unknown>): ProductSearch => {
    // validate and parse the search params into a typed state
    return {
      page: Number(search?.page ?? 1),
      filter: (search.filter as string) || '',
      sort: (search.sort as ProductSearchSortOptions) || 'newest',
    }
  },
})
```

Here's an example using the [Zod](https://zod.dev/) library (but feel free to use any validation library you want) to both validate and type the search params in a single step:

```tsx
// /routes/shop.products.tsx

import { z } from 'zod'

const productSearchSchema = z.object({
  page: z.number().catch(1),
  filter: z.string().catch(''),
  sort: z.enum(['newest', 'oldest', 'price']).catch('newest'),
})

type ProductSearch = z.infer<typeof productSearchSchema>

export const Route = createFileRoute('/shop/products')({
  validateSearch: (search) => productSearchSchema.parse(search),
})
```

Because `validateSearch` also accepts an object with the `parse` property, this can be shortened to:

```tsx
validateSearch: productSearchSchema
```

In the above example, we used Zod's `.catch()` modifier instead of `.default()` to avoid showing an error to the user because we firmly believe that if a search parameter is malformed, you probably don't want to halt the user's experience through the app to show a big fat error message. That said, there may be times that you **do want to show an error message**. In that case, you can use `.default()` instead of `.catch()`.

The underlying mechanics why this works relies on the `validateSearch` function throwing an error. If an error is thrown, the route's `onError` option will be triggered (and `error.routerCode` will be set to `VALIDATE_SEARCH` and the `errorComponent` will be rendered instead of the route's `component` where you can handle the search param error however you'd like.

#### Adapters

When using a library like [Zod](https://zod.dev/) to validate search params you might want to `transform` search params before committing the search params to the URL. A common `zod` `transform` is `default` for example.

```tsx
import { createFileRoute } from '@tanstack/react-router'
import { z } from 'zod'

const productSearchSchema = z.object({
  page: z.number().default(1),
  filter: z.string().default(''),
  sort: z.enum(['newest', 'oldest', 'price']).default('newest'),
})

export const Route = createFileRoute('/shop/products/')({
  validateSearch: productSearchSchema,
})
```

It might be surprising that when you try to navigate to this route, `search` is required. The following `Link` will type error as `search` is missing.

```tsx
<Link to="/shop/products" />
```

For validation libraries we recommend using adapters which infer the correct `input` and `output` types.

### Zod

An adapter is provided for [Zod](https://zod.dev/) which will pipe through the correct `input` type and `output` type

```tsx
import { createFileRoute } from '@tanstack/react-router'
import { zodValidator } from '@tanstack/zod-adapter'
import { z } from 'zod'

const productSearchSchema = z.object({
  page: z.number().default(1),
  filter: z.string().default(''),
  sort: z.enum(['newest', 'oldest', 'price']).default('newest'),
})

export const Route = createFileRoute('/shop/products/')({
  validateSearch: zodValidator(productSearchSchema),
})
```

The important part here is the following use of `Link` no longer requires `search` params

```tsx
<Link to="/shop/products" />
```

However the use of `catch` here overrides the types and makes `page`, `filter` and `sort` `unknown` causing type loss. We have handled this case by providing a `fallback` generic function which retains the types but provides a `fallback` value when validation fails

```tsx
import { createFileRoute } from '@tanstack/react-router'
import { fallback, zodValidator } from '@tanstack/zod-adapter'
import { z } from 'zod'

const productSearchSchema = z.object({
  page: fallback(z.number(), 1).default(1),
  filter: fallback(z.string(), '').default(''),
  sort: fallback(z.enum(['newest', 'oldest', 'price']), 'newest').default('newest'),
})

export const Route = createFileRoute('/shop/products/')({
  validateSearch: zodValidator(productSearchSchema),
})
```

Therefore when navigating to this route, `search` is optional and retains the correct types.

While not recommended, it is also possible to configure `input` and `output` type in case the `output` type is more accurate than the `input` type

```tsx
const productSearchSchema = z.object({
  page: fallback(z.number(), 1).default(1),
  filter: fallback(z.string(), '').default(''),
  sort: fallback(z.enum(['newest', 'oldest', 'price']), 'newest').default('newest'),
})

export const Route = createFileRoute('/shop/products/')({
  validateSearch: zodValidator({
    schema: productSearchSchema,
    input: 'output',
    output: 'input',
  }),
})
```

This provides flexibility in which type you want to infer for navigation and which types you want to infer for reading search params.

### Valibot

> [!WARNING]
> Router expects the valibot 1.0 package to be installed.

When using [Valibot](https://valibot.dev/) an adapter is not needed to ensure the correct `input` and `output` types are used for navigation and reading search params. This is because `valibot` implements [Standard Schema](https://github.com/standard-schema/standard-schema)

```tsx
import { createFileRoute } from '@tanstack/react-router'
import * as v from 'valibot'

const productSearchSchema = v.object({
  page: v.optional(v.fallback(v.number(), 1), 1),
  filter: v.optional(v.fallback(v.string(), ''), ''),
  sort: v.optional(v.fallback(v.picklist(['newest', 'oldest', 'price']), 'newest'), 'newest'),
})

export const Route = createFileRoute('/shop/products/')({
  validateSearch: productSearchSchema,
})
```

### Arktype

> [!WARNING]
> Router expects the arktype 2.0-rc package to be installed.

When using [ArkType](https://arktype.io/) an adapter is not needed to ensure the correct `input` and `output` types are used for navigation and reading search params. This is because [ArkType](https://arktype.io/) implements [Standard Schema](https://github.com/standard-schema/standard-schema)

```tsx
import { createFileRoute } from '@tanstack/react-router'
import { type } from 'arktype'

const productSearchSchema = type({
  page: 'number = 1',
  filter: 'string = ""',
  sort: '"newest" | "oldest" | "price" = "newest"',
})

export const Route = createFileRoute('/shop/products/')({
  validateSearch: productSearchSchema,
})
```

### Effect/Schema

When using [Effect/Schema](https://effect.website/docs/schema/introduction/) an adapter is not needed to ensure the correct `input` and `output` types are used for navigation and reading search params. This is because [Effect/Schema](https://effect.website/docs/schema/standard-schema/) implements [Standard Schema](https://github.com/standard-schema/standard-schema)

```tsx
import { createFileRoute } from '@tanstack/react-router'
import { Schema as S } from 'effect'

const productSearchSchema = S.standardSchemaV1(
  S.Struct({
    page: S.NumberFromString.pipe(
      S.optional,
      S.withDefaults({
        constructor: () => 1,
        decoding: () => 1,
      })
    ),
    filter: S.String.pipe(
      S.optional,
      S.withDefaults({
        constructor: () => '',
        decoding: () => '',
      })
    ),
    sort: S.Literal('newest', 'oldest', 'price').pipe(
      S.optional,
      S.withDefaults({
        constructor: () => 'newest' as const,
        decoding: () => 'newest' as const,
      })
    ),
  })
)

export const Route = createFileRoute('/shop/products/')({
  validateSearch: productSearchSchema,
})
```

## Reading Search Params

Once your search params have been validated and typed, you're finally ready to start reading and writing to them. There are a few ways to do this in TanStack Router, so let's check them out.

### Using Search Params in Loaders

Please read the [Search Params in Loaders](../data-loading.md#using-loaderdeps-to-access-search-params) section for more information about how to read search params in loaders with the `loaderDeps` option.

### Search Params are inherited from Parent Routes

The search parameters and types of parents are merged as you go down the route tree, so child routes also have access to their parent's search params:

- `shop.products.tsx`

```tsx
const productSearchSchema = z.object({
  page: z.number().catch(1),
  filter: z.string().catch(''),
  sort: z.enum(['newest', 'oldest', 'price']).catch('newest'),
})

type ProductSearch = z.infer<typeof productSearchSchema>

export const Route = createFileRoute('/shop/products')({
  validateSearch: productSearchSchema,
})
```

- `shop.products.$productId.tsx`

```tsx
export const Route = createFileRoute('/shop/products/$productId')({
  beforeLoad: ({ search }) => {
    search
    // ^? ProductSearch ✅
  },
})
```

### Search Params in Components

You can access your route's validated search params in your route's `component` via the `useSearch` hook.

```tsx
// /routes/shop.products.tsx

export const Route = createFileRoute('/shop/products')({
  validateSearch: productSearchSchema,
})

const ProductList = () => {
  const { page, filter, sort } = Route.useSearch()

  return <div>...</div>
}
```

> [!TIP]
> If your component is code-split, you can use the [getRouteApi function](../code-splitting.md#manually-accessing-route-apis-in-other-files-with-the-getrouteapi-helper) to avoid having to import the `Route` configuration to get access to the typed `useSearch()` hook.

### Search Params outside of Route Components

You can access your route's validated search params anywhere in your app using the `useSearch` hook. By passing the `from` id/path of your origin route, you'll get even better type safety:

```tsx
// /routes/shop.products.tsx
export const Route = createFileRoute('/shop/products')({
  validateSearch: productSearchSchema,
  // ...
})

// Somewhere else...

// /components/product-list-sidebar.tsx
const routeApi = getRouteApi('/shop/products')

const ProductList = () => {
  const routeSearch = routeApi.useSearch()

  // OR

  const { page, filter, sort } = useSearch({
    from: Route.fullPath,
  })

  return <div>...</div>
}
```

Or, you can loosen up the type-safety and get an optional `search` object by passing `strict: false`:

```tsx
function ProductList() {
  const search = useSearch({
    strict: false,
  })
  // {
  //   page: number | undefined
  //   filter: string | undefined
  //   sort: 'newest' | 'oldest' | 'price' | undefined
  // }

  return <div>...</div>
}
```

## Writing Search Params

Now that you've learned how to read your route's search params, you'll be happy to know that you've already seen the primary APIs to modify and update them. Let's remind ourselves a bit

### `<Link search />`

The best way to update search params is to use the `search` prop on the `<Link />` component.

If the search for the current page shall be updated and the `from` prop is specified, the `to` prop can be omitted.  
Here's an example:

```tsx
// /routes/shop.products.tsx
export const Route = createFileRoute('/shop/products')({
  validateSearch: productSearchSchema,
})

const ProductList = () => {
  return (
    <div>
      <Link from={Route.fullPath} search={(prev) => ({ page: prev.page + 1 })}>
        Next Page
      </Link>
    </div>
  )
}
```

If you want to update the search params in a generic component that is rendered on multiple routes, specifying `from` can be challenging.

In this scenario you can set `to="."` which will give you access to loosely typed search params.  
Here is an example that illustrates this:

```tsx
// `page` is a search param that is defined in the __root route and hence available on all routes.
const PageSelector = () => {
  return (
    <div>
      <Link to="." search={(prev) => ({ ...prev, page: prev.page + 1 })}>
        Next Page
      </Link>
    </div>
  )
}
```

If the generic component is only rendered in a specific subtree of the route tree, you can specify that subtree using `from`. Here you can omit `to='.'` if you want.

```tsx
// `page` is a search param that is defined in the /posts route and hence available on all of its child routes.
const PageSelector = () => {
  return (
    <div>
      <Link
        from="/posts"
        to="."
        search={(prev) => ({ ...prev, page: prev.page + 1 })}
      >
        Next Page
      </Link>
    </div>
  )
```

### `useNavigate(), navigate({ search })`

The `navigate` function also accepts a `search` option that works the same way as the `search` prop on `<Link />`:

```tsx
// /routes/shop.products.tsx
export const Route = createFileRoute('/shop/products/$productId')({
  validateSearch: productSearchSchema,
})

const ProductList = () => {
  const navigate = useNavigate({ from: Route.fullPath })

  return (
    <div>
      <button
        onClick={() => {
          navigate({
            search: (prev) => ({ page: prev.page + 1 }),
          })
        }}
      >
        Next Page
      </button>
    </div>
  )
}
```

### `router.navigate({ search })`

The `router.navigate` function works exactly the same way as the `useNavigate`/`navigate` hook/function above.

### `<Navigate search />`

The `<Navigate search />` component works exactly the same way as the `useNavigate`/`navigate` hook/function above, but accepts its options as props instead of a function argument.

## Transforming search with search middlewares

When link hrefs are built, by default the only thing that matters for the query string part is the `search` property of a `<Link>`.

TanStack Router provides a way to manipulate search params before the href is generated via **search middlewares**.
Search middlewares are functions that transform the search parameters when generating new links for a route or its descendants.
They are also executed upon navigation after search validation to allow manipulation of the query string.

The following example shows how to make sure that for **every** link that is being built, the `rootValue` search param is added _if_ it is part of the current search params. If a link specifies `rootValue` inside `search`, then that value is used for building the link.

```tsx
import { z } from 'zod'
import { createFileRoute } from '@tanstack/react-router'
import { zodValidator } from '@tanstack/zod-adapter'

const searchSchema = z.object({
  rootValue: z.string().optional(),
})

export const Route = createRootRoute({
  validateSearch: zodValidator(searchSchema),
  search: {
    middlewares: [
      ({ search, next }) => {
        const result = next(search)
        return {
          rootValue: search.rootValue,
          ...result,
        }
      },
    ],
  },
})
```

Since this specific use case is quite common, TanStack Router provides a generic implementation to retain search params via `retainSearchParams`:

```tsx
import { z } from 'zod'
import { createFileRoute, retainSearchParams } from '@tanstack/react-router'
import { zodValidator } from '@tanstack/zod-adapter'

const searchSchema = z.object({
  rootValue: z.string().optional(),
})

export const Route = createRootRoute({
  validateSearch: zodValidator(searchSchema),
  search: {
    middlewares: [retainSearchParams(['rootValue'])],
  },
})
```

Another common use case is to strip out search params from links if their default value is set. TanStack Router provides a generic implementation for this use case via `stripSearchParams`:

```tsx
import { z } from 'zod'
import { createFileRoute, stripSearchParams } from '@tanstack/react-router'
import { zodValidator } from '@tanstack/zod-adapter'

const defaultValues = {
  one: 'abc',
  two: 'xyz',
}

const searchSchema = z.object({
  one: z.string().default(defaultValues.one),
  two: z.string().default(defaultValues.two),
})

export const Route = createFileRoute('/hello')({
  validateSearch: zodValidator(searchSchema),
  search: {
    // strip default values
    middlewares: [stripSearchParams(defaultValues)],
  },
})
```

Multiple middlewares can be chained. The following example shows how to combine both `retainSearchParams` and `stripSearchParams`.

```tsx
import { Link, createFileRoute, retainSearchParams, stripSearchParams } from '@tanstack/react-router'
import { z } from 'zod'
import { zodValidator } from '@tanstack/zod-adapter'

const defaultValues = ['foo', 'bar']

export const Route = createFileRoute('/search')({
  validateSearch: zodValidator(
    z.object({
      retainMe: z.string().optional(),
      arrayWithDefaults: z.string().array().default(defaultValues),
      required: z.string(),
    })
  ),
  search: {
    middlewares: [retainSearchParams(['retainMe']), stripSearchParams({ arrayWithDefaults: defaultValues })],
  },
})
```

# SSR

> [!WARNING]
> While every effort has been made to separate these APIs from changes to Tanstack Start, there are underlying shared implementations internally. Therefore these can be subject to change and should be regarded as experimental until Start reaches stable status.

Server Side Rendering (SSR) is the process of rendering a component on the server and sending the HTML markup to the client. The client then hydrates the markup into a fully interactive component.

There are usually two different flavors of SSR to be considered:

- Non-streaming SSR
  - The entire page is rendered on the server and sent to the client in one single HTML request, including the serialized data the application needs to hydrate on the client.
- Streaming SSR
  - The critical first paint of the page is rendered on the server and sent to the client in one single HTML request, including the serialized data the application needs to hydrate on the client
  - The rest of the page is then streamed to the client as it is rendered on the server.

This guide will explain how to implement both flavors of SSR with TanStack Router!

## Non-Streaming SSR

Non-Streaming server-side rendering is the classic process of rendering the markup for your entire application page on the server and sending the completed HTML markup (and data) to the client. The client then hydrates the markup into a fully interactive application again.

To implement non-streaming SSR with TanStack Router, you will need the following utilities:

- `RouterClient` from `@tanstack/react-router`
  - e.g. `<RouterClient router={router} />`
  - Rendering this component in your client entry will render your application and also automatically implement the `Wrap` component option on `Router`
- And, either:
  - `defaultRenderHandler` from `@tanstack/react-router`
    - This will render your application in your server entry and also automatically handle application-level hydration/dehydration and also automatically implement the RouterServer component.
      or:
  - `renderRouterToString` from `@tanstack/react-router`
    - This differs from defaultRenderHandler in that it allows you to manually specify the `Wrap` component option on `Router` together with any other providers you may need to wrap it with.
  - `RouterServer` from `@tanstack/react-router`
    - This implements the `Wrap` component option on `Router`

### Automatic Server History

On the client, Router defaults to using an instance of `createBrowserHistory`, which is the preferred type of history to use on the client. On the server, however, you will want to use an instance of `createMemoryHistory` instead. This is because `createBrowserHistory` uses the `window` object, which does not exist on the server. This is handled automatically for you in the RouterServer component.

### Automatic Loader Dehydration/Hydration

Resolved loader data fetched by routes is automatically dehydrated and rehydrated by TanStack Router so long as you complete the standard SSR steps outlined in this guide.

⚠️ If you are using deferred data streaming, you will also need to ensure that you have implemented the [SSR Streaming & Stream Transform](#streaming-ssr) pattern near the end of this guide.

For more information on how to utilize data loading, see the [Data Loading](../data-loading.md) guide.

### Router Creation

Since your router will exist both on the server and the client, it's important that you create your router in a way that is consistent between both of these environments. The easiest way to do this is to expose a `createRouter` function in a shared file that can be imported and called by both your server and client entry files.

```tsx
// src/router.tsx
import { createRouter as createTanstackRouter } from '@tanstack/react-router'
import { routeTree } from './routeTree.gen'

export function createRouter() {
  return createTanstackRouter({ routeTree })
}

declare module '@tanstack/react-router' {
  interface Register {
    router: ReturnType<typeof createRouter>
  }
}
```

### Rendering the Application on the Server

Now that you have a router instance that has loaded all of the critical data for the current URL, you can render your application on the server:

using `defaultRenderToString`

```tsx
// src/entry-server.tsx
import { createRequestHandler, defaultRenderToString } from '@tanstack/react-router/ssr/server'
import { createRouter } from './router'

export async function render({ request }: { request: Request }) {
  const handler = createRequestHandler({ request, createRouter })

  return await handler(defaultRenderToString)
}
```

using `renderRouterToString`

```tsx
// src/entry-server.tsx
import { createRequestHandler, renderRouterToString, RouterServer } from '@tanstack/react-router/ssr/server'
import { createRouter } from './router'

export function render({ request }: { request: Request }) {
  const handler = createRequestHandler({ request, createRouter })

  return handler(({ request, responseHeaders, router }) =>
    renderRouterToString({
      request,
      responseHeaders,
      router,
      children: <RouterServer router={router} />,
    })
  )
}
```

NOTE: The createRequestHandler method requires a web api standard Request object, while the handler method will return a web api standard Response promise.

Should you be using a server framework like Express that uses its own Request and Response objects you would need to convert from the one to the other. Please have a look at the examples for how such an implementation might look like.

## Rendering the Application on the Client

On the client, things are much simpler.

- Create your router instance
- Render your application using the `<RouterClient />` component

[//]: # 'ClientEntryFileExample'

```tsx
// src/entry-client.tsx
import { hydrateRoot } from 'react-dom/client'
import { RouterClient } from '@tanstack/react-router/ssr/client'
import { createRouter } from './router'

const router = createRouter()

hydrateRoot(document, <RouterClient router={router} />)
```

[//]: # 'ClientEntryFileExample'

With this setup, your application will be rendered on the server and then hydrated on the client!

## Streaming SSR

Streaming SSR is the most modern flavor of SSR and is the process of continuously and incrementally sending HTML markup to the client as it is rendered on the server. This is slightly different from traditional SSR in concept because beyond being able to dehydrate and rehydrate a critical first paint, markup and data with less priority or slower response times can be streamed to the client after the initial render, but in the same request.

This pattern can be useful for pages that have slow or high-latency data fetching requirements. For example, if you have a page that needs to fetch data from a third-party API, you can stream the critical initial markup and data to the client and then stream the less-critical third-party data to the client as it is resolved.

> [!NOTE]
> This streaming pattern is all automatic as long as you are using either `defaultStreamHandler` or `renderRouterToStream`.

using `defaultStreamHandler`

```tsx
// src/entry-server.tsx
import { createRequestHandler, defaultStreamHandler } from '@tanstack/react-router/ssr/server'
import { createRouter } from './router'

export async function render({ request }: { request: Request }) {
  const handler = createRequestHandler({ request, createRouter })

  return await handler(defaultStreamHandler)
}
```

using `renderRouterToStream`

```tsx
// src/entry-server.tsx
import { createRequestHandler, renderRouterToStream, RouterServer } from '@tanstack/react-router/ssr/server'
import { createRouter } from './router'

export function render({ request }: { request: Request }) {
  const handler = createRequestHandler({ request, createRouter })

  return handler(({ request, responseHeaders, router }) =>
    renderRouterToStream({
      request,
      responseHeaders,
      router,
      children: <RouterServer router={router} />,
    })
  )
}
```

## Streaming Dehydration/Hydration

Streaming dehydration/hydration is an advanced pattern that goes beyond markup and allows you to dehydrate and stream any supporting data from the server to the client and rehydrate it on arrival. This is useful for applications that may need to further use/manage the underlying data that was used to render the initial markup on the server.

## Data Serialization

When using SSR, data passed between the server and the client must be serialized before it is sent across network-boundaries. TanStack Router handles this serialization using a very lightweight serializer that supports common data types beyond JSON.stringify/JSON.parse.

Out of the box, the following types are supported:

- `undefined`
- `Date`
- `Error`
- `FormData`

If you feel that there are other types that should be supported by default, please open an issue on the TanStack Router repository.

If you are using more complex data types like `Map`, `Set`, `BigInt`, etc, you may need to use a custom serializer to ensure that your type-definitions are accurate and your data is correctly serialized and deserialized. We are currently working on both a more robust serializer and a way to customize the serializer for your application. Open an issue if you are interested in helping out!

# Static Route Data

When creating routes, you can optionally specify a `staticData` property in the route's options. This object can literally contain anything you want as long as it's synchronously available when you create your route.

In addition to being able to access this data from the route itself, you can also access it from any match under the `match.staticData` property.

## Example

- `posts.tsx`

```tsx
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/posts')({
  staticData: {
    customData: 'Hello!',
  },
})
```

You can then access this data anywhere you have access to your routes, including matches that can be mapped back to their routes.

- `__root.tsx`

```tsx
import { createRootRoute } from '@tanstack/react-router'

export const Route = createRootRoute({
  component: () => {
    const matches = useMatches()

    return (
      <div>
        {matches.map((match) => {
          return <div key={match.id}>{match.staticData.customData}</div>
        })}
      </div>
    )
  },
})
```

## Enforcing Static Data

If you want to enforce that a route has static data, you can use declaration merging to add a type to the route's static option:

```tsx
declare module '@tanstack/react-router' {
  interface StaticDataRouteOption {
    customData: string
  }
}
```

Now, if you try to create a route without the `customData` property, you'll get a type error:

```tsx
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/posts')({
  staticData: {
    // Property 'customData' is missing in type '{ customData: number; }' but required in type 'StaticDataRouteOption'.ts(2741)
  },
})
```

## Optional Static Data

If you want to make static data optional, simply add a `?` to the property:

```tsx
declare module '@tanstack/react-router' {
  interface StaticDataRouteOption {
    customData?: string
  }
}
```

As long as there are any required properties on the `StaticDataRouteOption`, you'll be required to pass in an object.

# Type Safety

TanStack Router is built to be as type-safe as possible within the limits of the TypeScript compiler and runtime. This means that it's not only written in TypeScript, but that it also **fully infers the types it's provided and tenaciously pipes them through the entire routing experience**.

Ultimately, this means that you write **less types as a developer** and have **more confidence in your code** as it evolves.

## Route Definitions

### File-based Routing

Routes are hierarchical, and so are their definitions. If you're using file-based routing, much of the type-safety is already taken care of for you.

### Code-based Routing

If you're using the `Route` class directly, you'll need to be aware of how to ensure your routes are typed properly using the `Route`'s `getParentRoute` option. This is because child routes need to be aware of **all** of their parent routes types. Without this, those precious search params you parsed out of your _layout_ and _pathless layout_ routes, 3 levels up, would be lost to the JS void.

So, don't forget to pass the parent route to your child routes!

```tsx
const parentRoute = createRoute({
  getParentRoute: () => parentRoute,
})
```

## Exported Hooks, Components, and Utilities

For the types of your router to work with top-level exports like `Link`, `useNavigate`, `useParams`, etc. they must permeate the type-script module boundary and be registered right into the library. To do this, we use declaration merging on the exported `Register` interface.

```ts
const router = createRouter({
  // ...
})

declare module '@tanstack/react-router' {
  interface Register {
    router: typeof router
  }
}
```

By registering your router with the module, you can now use the exported hooks, components, and utilities with your router's exact types.

## Fixing the Component Context Problem

Component context is a wonderful tool in React and other frameworks for providing dependencies to components. However, if that context is changing types as it moves throughout your component hierarchy, it becomes impossible for TypeScript to know how to infer those changes. To get around this, context-based hooks and components require that you give them a hint on how and where they are being used.

```tsx
export const Route = createFileRoute('/posts')({
  component: PostsComponent,
})

function PostsComponent() {
  // Each route has type-safe versions of most of the built-in hooks from TanStack Router
  const params = Route.useParams()
  const search = Route.useSearch()

  // Some hooks require context from the *entire* router, not just the current route. To achieve type-safety here,
  // we must pass the `from` param to tell the hook our relative position in the route hierarchy.
  const navigate = useNavigate({ from: Route.fullPath })
  // ... etc
}
```

Every hook and component that requires a context hint will have a `from` param where you can pass the ID or path of the route you are rendering within.

> 🧠 Quick tip: If your component is code-split, you can use the [getRouteApi function](../code-splitting.md#manually-accessing-route-apis-in-other-files-with-the-getrouteapi-helper) to avoid having to pass in the `Route.fullPath` to get access to the typed `useParams()` and `useSearch()` hooks.

### What if I don't know the route? What if it's a shared component?

The `from` property is optional, which means if you don't pass it, you'll get the router's best guess on what types will be available. Usually, that means you'll get a union of all of the types of all of the routes in the router.

### What if I pass the wrong `from` path?

It's technically possible to pass a `from` that satisfies TypeScript, but may not match the actual route you are rendering within at runtime. In this case, each hook and component that supports `from` will detect if your expectations don't match the actual route you are rendering within, and will throw a runtime error.

### What if I don't know the route, or it's a shared component, and I can't pass `from`?

If you are rendering a component that is shared across multiple routes, or you are rendering a component that is not within a route, you can pass `strict: false` instead of a `from` option. This will not only silence the runtime error, but will also give you relaxed, but accurate types for the potential hook you are calling. A good example of this is calling `useSearch` from a shared component:

```tsx
function MyComponent() {
  const search = useSearch({ strict: false })
}
```

In this case, the `search` variable will be typed as a union of all possible search params from all routes in the router.

## Router Context

Router context is so extremely useful as it's the ultimate hierarchical dependency injection. You can supply context to the router and to each and every route it renders. As you build up this context, TanStack Router will merge it down with the hierarchy of routes, so that each route has access to the context of all of its parents.

The `createRootRouteWithContext` factory creates a new router with the instantiated type, which then creates a requirement for you to fulfill the same type contract to your router, and will also ensure that your context is properly typed throughout the entire route tree.

```tsx
const rootRoute = createRootRouteWithContext<{ whateverYouWant: true }>()({
  component: App,
})

const routeTree = rootRoute.addChildren([
  // ... all child routes will have access to `whateverYouWant` in their context
])

const router = createRouter({
  routeTree,
  context: {
    // This will be required to be passed now
    whateverYouWant: true,
  },
})
```

## Performance Recommendations

As your application scales, TypeScript check times will naturally increase. There are a few things to keep in mind when your application scales to keep your TS check times down.

### Only infer types you need

A great pattern with client side data caches (TanStack Query, etc.) is to prefetch data. For example with TanStack Query you might have a route which calls `queryClient.ensureQueryData` in a `loader`.

```tsx
export const Route = createFileRoute('/posts/$postId/deep')({
  loader: ({ context: { queryClient }, params: { postId } }) => queryClient.ensureQueryData(postQueryOptions(postId)),
  component: PostDeepComponent,
})

function PostDeepComponent() {
  const params = Route.useParams()
  const data = useSuspenseQuery(postQueryOptions(params.postId))

  return <></>
}
```

This may look fine and for small route trees and you may not notice any TS performance issues. However in this case TS has to infer the loader's return type, despite it never being used in your route. If the loader data is a complex type with many routes that prefetch in this manner, it can slow down editor performance. In this case, the change is quite simple and let typescript infer Promise<void>.

```tsx
export const Route = createFileRoute('/posts/$postId/deep')({
  loader: async ({ context: { queryClient }, params: { postId } }) => {
    await queryClient.ensureQueryData(postQueryOptions(postId))
  },
  component: PostDeepComponent,
})

function PostDeepComponent() {
  const params = Route.useParams()
  const data = useSuspenseQuery(postQueryOptions(params.postId))

  return <></>
}
```

This way the loader data is never inferred and it moves the inference out of the route tree to the first time you use `useSuspenseQuery`.

### Narrow to relevant routes as much as you possibly can

Consider the following usage of `Link`

```tsx
<Link to=".." search={{ page: 0 }} />
<Link to="." search={{ page: 0 }} />
```

**These examples are bad for TS performance**. That's because `search` resolves to a union of all `search` params for all routes and TS has to check whatever you pass to the `search` prop against this potentially big union. As your application grows, this check time will increase linearly to number of routes and search params. We have done our best to optimize for this case (TypeScript will typically do this work once and cache it) but the initial check against this large union is expensive. This also applies to `params` and other API's such as `useSearch`, `useParams`, `useNavigate` etc.

Instead you should try to narrow to relevant routes with `from` or `to`.

```tsx
<Link from={Route.fullPath} to=".." search={{page: 0}} />
<Link from="/posts" to=".." search={{page: 0}} />
```

Remember you can always pass a union to `to` or `from` to narrow the routes you're interested in.

```tsx
const from: '/posts/$postId/deep' | '/posts/' = '/posts/'
<Link from={from} to='..' />
```

You can also pass branches to `from` to only resolve `search` or `params` to be from any descendants of that branch:

```tsx
const from = '/posts'
<Link from={from} to='..' />
```

`/posts` could be a branch with many descendants which share the same `search` or `params`

### Consider using the object syntax of `addChildren`

It's typical of routes to have `params` `search`, `loaders` or `context` that can even reference external dependencies which are also heavy on TS inference. For such applications, using objects for creating the route tree can be more performant than tuples.

`createChildren` also can accept an object. For large route trees with complex routes and external libraries, objects can be much faster for TS to type check as opposed to large tuples. The performance gains depend on your project, what external dependencies you have and how the types for those libraries are written

```tsx
const routeTree = rootRoute.addChildren({
  postsRoute: postsRoute.addChildren({ postRoute, postsIndexRoute }),
  indexRoute,
})
```

Note this syntax is more verbose but has better TS performance. With file based routing, the route tree is generated for you so a verbose route tree is not a concern

### Avoid internal types without narrowing

It's common you might want to re-use types exposed. For example you might be tempted to use `LinkProps` like so

```tsx
const props: LinkProps = {
  to: '/posts/',
}

return (
  <Link {...props}>
)
```

**This is VERY bad for TS Performance**. The problem here is `LinkProps` has no type arguments and is therefore an extremely large type. It includes `search` which is a union of all `search` params, it contains `params` which is a union of all `params`. When merging this object with `Link` it will do a structural comparison of this huge type.

Instead you can use `as const satisfies` to infer a precise type and not `LinkProps` directly to avoid the huge check

```tsx
const props = {
  to: '/posts/',
} as const satisfies LinkProps

return (
  <Link {...props}>
)
```

As `props` is not of type `LinkProps` and therefore this check is cheaper because the type is much more precise. You can also improve type checking further by narrowing `LinkProps`

```tsx
const props = {
  to: '/posts/',
} as const satisfies LinkProps<RegisteredRouter, string '/posts/'>

return (
  <Link {...props}>
)
```

This is even faster as we're checking against the narrowed `LinkProps` type.

You can also use this to narrow the type of `LinkProps` to a specific type to be used as a prop or parameter to a function

```tsx
export const myLinkProps = [
  {
    to: '/posts',
  },
  {
    to: '/posts/$postId',
    params: { postId: 'postId' },
  },
] as const satisfies ReadonlyArray<LinkProps>

export type MyLinkProps = (typeof myLinkProps)[number]

const MyComponent = (props: { linkProps: MyLinkProps }) => {
  return <Link {...props.linkProps} />
}
```

This is faster than using `LinkProps` directly in a component because `MyLinkProps` is a much more precise type

Another solution is not to use `LinkProps` and to provide inversion of control to render a `Link` component narrowed to a specific route. Render props are a good method of inverting control to the user of a component

```tsx
export interface MyComponentProps {
  readonly renderLink: () => React.ReactNode
}

const MyComponent = (props: MyComponentProps) => {
  return <div>{props.renderLink()}</div>
}

const Page = () => {
  return <MyComponent renderLink={() => <Link to="/absolute" />} />
}
```

This particular example is very fast as we've inverted control of where we're navigating to the user of the component. The `Link` is narrowed to the exact route
we want to navigate to

# Type Utilities

Most types exposed by TanStack Router are internal, subject to breaking changes and not always easy to use. That is why TanStack Router has a subset of exposed types focused on ease of use with the intension to be used externally. These types provide the same type safe experience from TanStack Router's runtime concepts on the type level, with flexibility of where to provide type checking

## Type checking Link options with `ValidateLinkOptions`

`ValidateLinkOptions` type checks object literal types to ensure they conform to `Link` options at inference sites. For example, you may have a generic `HeadingLink` component which accepts a `title` prop along with `linkOptions`, the idea being this component can be re-used for any navigation.

```tsx
export interface HeaderLinkProps<TRouter extends RegisteredRouter = RegisteredRouter, TOptions = unknown> {
  title: string
  linkOptions: ValidateLinkOptions<TRouter, TOptions>
}

export function HeadingLink<TRouter extends RegisteredRouter, TOptions>(
  props: HeaderLinkProps<TRouter, TOptions>
): React.ReactNode
export function HeadingLink(props: HeaderLinkProps): React.ReactNode {
  return (
    <>
      <h1>{props.title}</h1>
      <Link {...props.linkOptions} />
    </>
  )
}
```

A more permissive overload of `HeadingLink` is used to avoid type assertions you would otherwise have to do with the generic signature. Using a looser signature without type parameters is an easy way to avoid type assertions in the implementation of `HeadingLink`

All type parameters for utilities are optional but for the best TypeScript performance `TRouter` should always be specified for the public facing signature. And `TOptions` should always be used at inference sites like `HeadingLink` to infer the `linkOptions` to correctly narrow `params` and `search`

The result of this is that `linkOptions` in the following is completely type-safe

```tsx
<HeadingLink title="Posts" linkOptions={{ to: '/posts' }} />
<HeadingLink title="Post" linkOptions={{ to: '/posts/$postId', params: {postId: 'postId'} }} />
```

## Type checking an array of Link options with `ValidateLinkOptionsArray`

All navigation type utilities have an array variant. `ValidateLinkOptionsArray` enables type checking of an array of `Link` options. For example, you might have a generic `Menu` component where each item is a `Link`.

```tsx
export interface MenuProps<
  TRouter extends RegisteredRouter = RegisteredRouter,
  TItems extends ReadonlyArray<unknown> = ReadonlyArray<unknown>,
> {
  items: ValidateLinkOptionsArray<TRouter, TItems>
}

export function Menu<TRouter extends RegisteredRouter = RegisteredRouter, TItems extends ReadonlyArray<unknown>>(
  props: MenuProps<TRouter, TItems>
): React.ReactNode
export function Menu(props: MenuProps): React.ReactNode {
  return (
    <ul>
      {props.items.map((item) => (
        <li>
          <Link {...item} />
        </li>
      ))}
    </ul>
  )
}
```

This of course allows the following `items` prop to be completely type-safe

```tsx
<Menu items={[{ to: '/posts' }, { to: '/posts/$postId', params: { postId: 'postId' } }]} />
```

It is also possible to fix `from` for each `Link` options in the array. This would allow all `Menu` items to navigate relative to `from`. Additional type checking of `from` can be provided by the `ValidateFromPath` utility

```tsx
export interface MenuProps<
  TRouter extends RegisteredRouter = RegisteredRouter,
  TItems extends ReadonlyArray<unknown> = ReadonlyArray<unknown>,
  TFrom extends string = string,
> {
  from: ValidateFromPath<TRouter, TFrom>
  items: ValidateLinkOptionsArray<TRouter, TItems, TFrom>
}

export function Menu<
  TRouter extends RegisteredRouter = RegisteredRouter,
  TItems extends ReadonlyArray<unknown>,
  TFrom extends string = string,
>(props: MenuProps<TRouter, TItems, TFrom>): React.ReactNode
export function Menu(props: MenuProps): React.ReactNode {
  return (
    <ul>
      {props.items.map((item) => (
        <li>
          <Link {...item} from={props.from} />
        </li>
      ))}
    </ul>
  )
}
```

`ValidateLinkOptionsArray` allows you to fix `from` by providing an extra type parameter. The result is a type safe array of `Link` options providing navigation relative to `from`

```tsx
<Menu from="/posts" items={[{ to: '.' }, { to: './$postId', params: { postId: 'postId' } }]} />
```

## Type checking redirect options with `ValidateRedirectOptions`

`ValidateRedirectOptions` type checks object literal types to ensure they conform to redirect options at inference sites. For example, you may need a generic `fetchOrRedirect` function which accepts a `url` along with `redirectOptions`, the idea being this function will redirect when the `fetch` fails.

```tsx
export async function fetchOrRedirect<TRouter extends RegisteredRouter = RegisteredRouter, TOptions>(
  url: string,
  redirectOptions: ValidateRedirectOptions<TRouter, TOptions>
): Promise<unknown>
export async function fetchOrRedirect(url: string, redirectOptions: ValidateRedirectOptions): Promise<unknown> {
  const response = await fetch(url)

  if (!response.ok && response.status === 401) {
    throw redirect(redirectOptions)
  }

  return await response.json()
}
```

The result is that `redirectOptions` passed to `fetchOrRedirect` is completely type-safe

```tsx
fetchOrRedirect('http://example.com/', { to: '/login' })
```

## Type checking navigate options with `ValidateNavigateOptions`

`ValidateNavigateOptions` type checks object literal types to ensure they conform to navigate options at inference sites. For example, you may want to write a custom hook to enable/disable navigation.

[//]: # 'TypeCheckingNavigateOptionsWithValidateNavigateOptionsImpl'

```tsx
export interface UseConditionalNavigateResult {
  enable: () => void
  disable: () => void
  navigate: () => void
}

export function useConditionalNavigate<TRouter extends RegisteredRouter = RegisteredRouter, TOptions>(
  navigateOptions: ValidateNavigateOptions<TRouter, TOptions>
): UseConditionalNavigateResult
export function useConditionalNavigate(navigateOptions: ValidateNavigateOptions): UseConditionalNavigateResult {
  const [enabled, setEnabled] = useState(false)
  const navigate = useNavigate()
  return {
    enable: () => setEnabled(true),
    disable: () => setEnabled(false),
    navigate: () => {
      if (enabled) {
        navigate(navigateOptions)
      }
    },
  }
}
```

[//]: # 'TypeCheckingNavigateOptionsWithValidateNavigateOptionsImpl'

The result of this is that `navigateOptions` passed to `useConditionalNavigate` is completely type-safe and we can enable/disable navigation based on react state

```tsx
const { enable, disable, navigate } = useConditionalNavigate({
  to: '/posts/$postId',
  params: { postId: 'postId' },
})
```

</@tanstack/react-router_guide>

<@tanstack/react-router_routing>
Always Apply: false - This rule should only be applied when relevant files are open
Always apply this rule in these files: src/**/\*.ts, src/**/\*.tsx

# Code-Based Routing

> [!TIP]
> Code-based routing is not recommended for most applications. It is recommended to use [File-Based Routing](../file-based-routing.md) instead.

## ⚠️ Before You Start

- If you're using [File-Based Routing](../file-based-routing.md), **skip this guide**.
- If you still insist on using code-based routing, you must read the [Routing Concepts](../routing-concepts.md) guide first, as it also covers core concepts of the router.

## Route Trees

Code-based routing is no different from file-based routing in that it uses the same route tree concept to organize, match and compose matching routes into a component tree. The only difference is that instead of using the filesystem to organize your routes, you use code.

Let's consider the same route tree from the [Route Trees & Nesting](../route-trees.md#route-trees) guide, and convert it to code-based routing:

Here is the file-based version:

```
routes/
├── __root.tsx
├── index.tsx
├── about.tsx
├── posts/
│   ├── index.tsx
│   ├── $postId.tsx
├── posts.$postId.edit.tsx
├── settings/
│   ├── profile.tsx
│   ├── notifications.tsx
├── _pathlessLayout.tsx
├── _pathlessLayout/
│   ├── route-a.tsx
├── ├── route-b.tsx
├── files/
│   ├── $.tsx
```

And here is a summarized code-based version:

```tsx
import { createRootRoute, createRoute } from '@tanstack/react-router'

const rootRoute = createRootRoute()

const indexRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: '/',
})

const aboutRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: 'about',
})

const postsRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: 'posts',
})

const postsIndexRoute = createRoute({
  getParentRoute: () => postsRoute,
  path: '/',
})

const postRoute = createRoute({
  getParentRoute: () => postsRoute,
  path: '$postId',
})

const postEditorRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: 'posts/$postId/edit',
})

const settingsRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: 'settings',
})

const profileRoute = createRoute({
  getParentRoute: () => settingsRoute,
  path: 'profile',
})

const notificationsRoute = createRoute({
  getParentRoute: () => settingsRoute,
  path: 'notifications',
})

const pathlessLayoutRoute = createRoute({
  getParentRoute: () => rootRoute,
  id: 'pathlessLayout',
})

const pathlessLayoutARoute = createRoute({
  getParentRoute: () => pathlessLayoutRoute,
  path: 'route-a',
})

const pathlessLayoutBRoute = createRoute({
  getParentRoute: () => pathlessLayoutRoute,
  path: 'route-b',
})

const filesRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: 'files/$',
})
```

## Anatomy of a Route

All other routes other than the root route are configured using the `createRoute` function:

```tsx
const route = createRoute({
  getParentRoute: () => rootRoute,
  path: '/posts',
  component: PostsComponent,
})
```

The `getParentRoute` option is a function that returns the parent route of the route you're creating.

**❓❓❓ "Wait, you're making me pass the parent route for every route I make?"**

Absolutely! The reason for passing the parent route has **everything to do with the magical type safety** of TanStack Router. Without the parent route, TypeScript would have no idea what types to supply your route with!

> [!IMPORTANT]
> For every route that's **NOT** the **Root Route** or a **Pathless Layout Route**, a `path` option is required. This is the path that will be matched against the URL pathname to determine if the route is a match.

When configuring route `path` option on a route, it ignores leading and trailing slashes (this does not include "index" route paths `/`). You can include them if you want, but they will be normalized internally by TanStack Router. Here is a table of valid paths and what they will be normalized to:

| Path     | Normalized Path |
| -------- | --------------- |
| `/`      | `/`             |
| `/about` | `about`         |
| `about/` | `about`         |
| `about`  | `about`         |
| `$`      | `$`             |
| `/$`     | `$`             |
| `/$/`    | `$`             |

## Manually building the route tree

When building a route tree in code, it's not enough to define the parent route of each route. You must also construct the final route tree by adding each route to its parent route's `children` array. This is because the route tree is not built automatically for you like it is in file-based routing.

```tsx
/* prettier-ignore */
const routeTree = rootRoute.addChildren([
  indexRoute,
  aboutRoute,
  postsRoute.addChildren([
    postsIndexRoute,
    postRoute,
  ]),
  postEditorRoute,
  settingsRoute.addChildren([
    profileRoute,
    notificationsRoute,
  ]),
  pathlessLayoutRoute.addChildren([
    pathlessLayoutARoute,
    pathlessLayoutBRoute,
  ]),
  filesRoute.addChildren([
    fileRoute,
  ]),
])
/* prettier-ignore-end */
```

But before you can go ahead and build the route tree, you need to understand how the Routing Concepts for Code-Based Routing work.

## Routing Concepts for Code-Based Routing

Believe it or not, file-based routing is really a superset of code-based routing and uses the filesystem and a bit of code-generation abstraction on top of it to generate this structure you see above automatically.

We're going to assume you've read the [Routing Concepts](../routing-concepts.md) guide and are familiar with each of these main concepts:

- The Root Route
- Basic Routes
- Index Routes
- Dynamic Route Segments
- Splat / Catch-All Routes
- Layout Routes
- Pathless Routes
- Non-Nested Routes

Now, let's take a look at how to create each of these route types in code.

## The Root Route

Creating a root route in code-based routing is thankfully the same as doing so in file-based routing. Call the `createRootRoute()` function.

Unlike file-based routing however, you do not need to export the root route if you don't want to. It's certainly not recommended to build an entire route tree and application in a single file (although you can and we do this in the examples to demonstrate routing concepts in brevity).

```tsx
// Standard root route
import { createRootRoute } from '@tanstack/react-router'

const rootRoute = createRootRoute()

// Root route with Context
import { createRootRouteWithContext } from '@tanstack/react-router'
import type { QueryClient } from '@tanstack/react-query'

export interface MyRouterContext {
  queryClient: QueryClient
}
const rootRoute = createRootRouteWithContext<MyRouterContext>()
```

To learn more about Context in TanStack Router, see the [Router Context](../../guide/router-context.md) guide.

## Basic Routes

To create a basic route, simply provide a normal `path` string to the `createRoute` function:

```tsx
const aboutRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: 'about',
})
```

See, it's that simple! The `aboutRoute` will match the URL `/about`.

## Index Routes

Unlike file-based routing, which uses the `index` filename to denote an index route, code-based routing uses a single slash `/` to denote an index route. For example, the `posts.index.tsx` file from our example route tree above would be represented in code-based routing like this:

```tsx
const postsRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: 'posts',
})

const postsIndexRoute = createRoute({
  getParentRoute: () => postsRoute,
  // Notice the single slash `/` here
  path: '/',
})
```

So, the `postsIndexRoute` will match the URL `/posts/` (or `/posts`).

## Dynamic Route Segments

Dynamic route segments work exactly the same in code-based routing as they do in file-based routing. Simply prefix a segment of the path with a `$` and it will be captured into the `params` object of the route's `loader` or `component`:

```tsx
const postIdRoute = createRoute({
  getParentRoute: () => postsRoute,
  path: '$postId',
  // In a loader
  loader: ({ params }) => fetchPost(params.postId),
  // Or in a component
  component: PostComponent,
})

function PostComponent() {
  const { postId } = postIdRoute.useParams()
  return <div>Post ID: {postId}</div>
}
```

> [!TIP]
> If your component is code-split, you can use the [getRouteApi function](../../guide/code-splitting.md#manually-accessing-route-apis-in-other-files-with-the-getrouteapi-helper) to avoid having to import the `postIdRoute` configuration to get access to the typed `useParams()` hook.

## Splat / Catch-All Routes

As expected, splat/catch-all routes also work the same in code-based routing as they do in file-based routing. Simply prefix a segment of the path with a `$` and it will be captured into the `params` object under the `_splat` key:

```tsx
const filesRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: 'files',
})

const fileRoute = createRoute({
  getParentRoute: () => filesRoute,
  path: '$',
})
```

For the URL `/documents/hello-world`, the `params` object will look like this:

```js
{
  '_splat': 'documents/hello-world'
}
```

## Layout Routes

Layout routes are routes that wrap their children in a layout component. In code-based routing, you can create a layout route by simply nesting a route under another route:

```tsx
const postsRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: 'posts',
  component: PostsLayoutComponent, // The layout component
})

function PostsLayoutComponent() {
  return (
    <div>
      <h1>Posts</h1>
      <Outlet />
    </div>
  )
}

const postsIndexRoute = createRoute({
  getParentRoute: () => postsRoute,
  path: '/',
})

const postsCreateRoute = createRoute({
  getParentRoute: () => postsRoute,
  path: 'create',
})

const routeTree = rootRoute.addChildren([
  // The postsRoute is the layout route
  // Its children will be nested under the PostsLayoutComponent
  postsRoute.addChildren([postsIndexRoute, postsCreateRoute]),
])
```

Now, both the `postsIndexRoute` and `postsCreateRoute` will render their contents inside of the `PostsLayoutComponent`:

```tsx
// URL: /posts
<PostsLayoutComponent>
  <PostsIndexComponent />
</PostsLayoutComponent>

// URL: /posts/create
<PostsLayoutComponent>
  <PostsCreateComponent />
</PostsLayoutComponent>
```

## Pathless Layout Routes

In file-based routing a pathless layout route is prefixed with a `_`, but in code-based routing, this is simply a route with an `id` instead of a `path` option. This is because code-based routing does not use the filesystem to organize routes, so there is no need to prefix a route with a `_` to denote that it has no path.

```tsx
const pathlessLayoutRoute = createRoute({
  getParentRoute: () => rootRoute,
  id: 'pathlessLayout',
  component: PathlessLayoutComponent,
})

function PathlessLayoutComponent() {
  return (
    <div>
      <h1>Pathless Layout</h1>
      <Outlet />
    </div>
  )
}

const pathlessLayoutARoute = createRoute({
  getParentRoute: () => pathlessLayoutRoute,
  path: 'route-a',
})

const pathlessLayoutBRoute = createRoute({
  getParentRoute: () => pathlessLayoutRoute,
  path: 'route-b',
})

const routeTree = rootRoute.addChildren([
  // The pathless layout route has no path, only an id
  // So its children will be nested under the pathless layout route
  pathlessLayoutRoute.addChildren([pathlessLayoutARoute, pathlessLayoutBRoute]),
])
```

Now both `/route-a` and `/route-b` will render their contents inside of the `PathlessLayoutComponent`:

```tsx
// URL: /route-a
<PathlessLayoutComponent>
  <RouteAComponent />
</PathlessLayoutComponent>

// URL: /route-b
<PathlessLayoutComponent>
  <RouteBComponent />
</PathlessLayoutComponent>
```

## Non-Nested Routes

Building non-nested routes in code-based routing does not require using a trailing `_` in the path, but does require you to build your route and route tree with the right paths and nesting. Let's consider the route tree where we want the post editor to **not** be nested under the posts route:

- `/posts_/$postId/edit`
- `/posts`
  - `$postId`

To do this we need to build a separate route for the post editor and include the entire path in the `path` option from the root of where we want the route to be nested (in this case, the root):

```tsx
// The posts editor route is nested under the root route
const postEditorRoute = createRoute({
  getParentRoute: () => rootRoute,
  // The path includes the entire path we need to match
  path: 'posts/$postId/edit',
})

const postsRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: 'posts',
})

const postRoute = createRoute({
  getParentRoute: () => postsRoute,
  path: '$postId',
})

const routeTree = rootRoute.addChildren([
  // The post editor route is nested under the root route
  postEditorRoute,
  postsRoute.addChildren([postRoute]),
])
```

# File-Based Routing

Most of the TanStack Router documentation is written for file-based routing and is intended to help you understand in more detail how to configure file-based routing and the technical details behind how it works. While file-based routing is the preferred and recommended way to configure TanStack Router, you can also use [code-based routing](../code-based-routing.md) if you prefer.

## What is File-Based Routing?

File-based routing is a way to configure your routes using the filesystem. Instead of defining your route structure via code, you can define your routes using a series of files and directories that represent the route hierarchy of your application. This brings a number of benefits:

- **Simplicity**: File-based routing is visually intuitive and easy to understand for both new and experienced developers.
- **Organization**: Routes are organized in a way that mirrors the URL structure of your application.
- **Scalability**: As your application grows, file-based routing makes it easy to add new routes and maintain existing ones.
- **Code-Splitting**: File-based routing allows TanStack Router to automatically code-split your routes for better performance.
- **Type-Safety**: File-based routing raises the ceiling on type-safety by generating managing type linkages for your routes, which can otherwise be a tedious process via code-based routing.
- **Consistency**: File-based routing enforces a consistent structure for your routes, making it easier to maintain and update your application and move from one project to another.

## `/`s or `.`s?

While directories have long been used to represent route hierarchy, file-based routing introduces an additional concept of using the `.` character in the file-name to denote a route nesting. This allows you to avoid creating directories for few deeply nested routes and continue to use directories for wider route hierarchies. Let's take a look at some examples!

## Directory Routes

Directories can be used to denote route hierarchy, which can be useful for organizing multiple routes into logical groups and also cutting down on the filename length for large groups of deeply nested routes.

See the example below:

| Filename                | Route Path                | Component Output                  |
| ----------------------- | ------------------------- | --------------------------------- |
| ʦ `__root.tsx`          |                           | `<Root>`                          |
| ʦ `index.tsx`           | `/` (exact)               | `<Root><RootIndex>`               |
| ʦ `about.tsx`           | `/about`                  | `<Root><About>`                   |
| ʦ `posts.tsx`           | `/posts`                  | `<Root><Posts>`                   |
| 📂 `posts`              |                           |                                   |
| ┄ ʦ `index.tsx`         | `/posts` (exact)          | `<Root><Posts><PostsIndex>`       |
| ┄ ʦ `$postId.tsx`       | `/posts/$postId`          | `<Root><Posts><Post>`             |
| 📂 `posts_`             |                           |                                   |
| ┄ 📂 `$postId`          |                           |                                   |
| ┄ ┄ ʦ `edit.tsx`        | `/posts/$postId/edit`     | `<Root><EditPost>`                |
| ʦ `settings.tsx`        | `/settings`               | `<Root><Settings>`                |
| 📂 `settings`           |                           | `<Root><Settings>`                |
| ┄ ʦ `profile.tsx`       | `/settings/profile`       | `<Root><Settings><Profile>`       |
| ┄ ʦ `notifications.tsx` | `/settings/notifications` | `<Root><Settings><Notifications>` |
| ʦ `_pathlessLayout.tsx` |                           | `<Root><PathlessLayout>`          |
| 📂 `_pathlessLayout`    |                           |                                   |
| ┄ ʦ `route-a.tsx`       | `/route-a`                | `<Root><PathlessLayout><RouteA>`  |
| ┄ ʦ `route-b.tsx`       | `/route-b`                | `<Root><PathlessLayout><RouteB>`  |
| 📂 `files`              |                           |                                   |
| ┄ ʦ `$.tsx`             | `/files/$`                | `<Root><Files>`                   |
| 📂 `account`            |                           |                                   |
| ┄ ʦ `route.tsx`         | `/account`                | `<Root><Account>`                 |
| ┄ ʦ `overview.tsx`      | `/account/overview`       | `<Root><Account><Overview>`       |

## Flat Routes

Flat routing gives you the ability to use `.`s to denote route nesting levels.

This can be useful when you have a large number of uniquely deeply nested routes and want to avoid creating directories for each one:

See the example below:

| Filename                        | Route Path                | Component Output                  |
| ------------------------------- | ------------------------- | --------------------------------- |
| ʦ `__root.tsx`                  |                           | `<Root>`                          |
| ʦ `index.tsx`                   | `/` (exact)               | `<Root><RootIndex>`               |
| ʦ `about.tsx`                   | `/about`                  | `<Root><About>`                   |
| ʦ `posts.tsx`                   | `/posts`                  | `<Root><Posts>`                   |
| ʦ `posts.index.tsx`             | `/posts` (exact)          | `<Root><Posts><PostsIndex>`       |
| ʦ `posts.$postId.tsx`           | `/posts/$postId`          | `<Root><Posts><Post>`             |
| ʦ `posts_.$postId.edit.tsx`     | `/posts/$postId/edit`     | `<Root><EditPost>`                |
| ʦ `settings.tsx`                | `/settings`               | `<Root><Settings>`                |
| ʦ `settings.profile.tsx`        | `/settings/profile`       | `<Root><Settings><Profile>`       |
| ʦ `settings.notifications.tsx`  | `/settings/notifications` | `<Root><Settings><Notifications>` |
| ʦ `_pathlessLayout.tsx`         |                           | `<Root><PathlessLayout>`          |
| ʦ `_pathlessLayout.route-a.tsx` | `/route-a`                | `<Root><PathlessLayout><RouteA>`  |
| ʦ `_pathlessLayout.route-b.tsx` | `/route-b`                | `<Root><PathlessLayout><RouteB>`  |
| ʦ `files.$.tsx`                 | `/files/$`                | `<Root><Files>`                   |
| ʦ `account.tsx`                 | `/account`                | `<Root><Account>`                 |
| ʦ `account.overview.tsx`        | `/account/overview`       | `<Root><Account><Overview>`       |

## Mixed Flat and Directory Routes

It's extremely likely that a 100% directory or flat route structure won't be the best fit for your project, which is why TanStack Router allows you to mix both flat and directory routes together to create a route tree that uses the best of both worlds where it makes sense:

See the example below:

| Filename                       | Route Path                | Component Output                  |
| ------------------------------ | ------------------------- | --------------------------------- |
| ʦ `__root.tsx`                 |                           | `<Root>`                          |
| ʦ `index.tsx`                  | `/` (exact)               | `<Root><RootIndex>`               |
| ʦ `about.tsx`                  | `/about`                  | `<Root><About>`                   |
| ʦ `posts.tsx`                  | `/posts`                  | `<Root><Posts>`                   |
| 📂 `posts`                     |                           |                                   |
| ┄ ʦ `index.tsx`                | `/posts` (exact)          | `<Root><Posts><PostsIndex>`       |
| ┄ ʦ `$postId.tsx`              | `/posts/$postId`          | `<Root><Posts><Post>`             |
| ┄ ʦ `$postId.edit.tsx`         | `/posts/$postId/edit`     | `<Root><Posts><Post><EditPost>`   |
| ʦ `settings.tsx`               | `/settings`               | `<Root><Settings>`                |
| ʦ `settings.profile.tsx`       | `/settings/profile`       | `<Root><Settings><Profile>`       |
| ʦ `settings.notifications.tsx` | `/settings/notifications` | `<Root><Settings><Notifications>` |
| ʦ `account.tsx`                | `/account`                | `<Root><Account>`                 |
| ʦ `account.overview.tsx`       | `/account/overview`       | `<Root><Account><Overview>`       |

Both flat and directory routes can be mixed together to create a route tree that uses the best of both worlds where it makes sense.

> [!TIP]
> If you find that the default file-based routing structure doesn't fit your needs, you can always use [Virtual File Routes](../virtual-file-routes.md) to control the source of your routes whilst still getting the awesome performance benefits of file-based routing.

## Getting started with File-Based Routing

To get started with file-based routing, you'll need to configure your project's bundler to use the TanStack Router Plugin or the TanStack Router CLI.

To enable file-based routing, you'll need to be using React with a supported bundler. See if your bundler is listed in the configuration guides below.

[//]: # 'SupportedBundlersList'

- [Installation with Vite](../installation-with-vite.md)
- [Installation with Rspack/Rsbuild](../installation-with-rspack.md)
- [Installation with Webpack](../installation-with-webpack.md)
- [Installation with Esbuild](../installation-with-esbuild.md)

[//]: # 'SupportedBundlersList'

When using TanStack Router's file-based routing through one of the supported bundlers, our plugin will **automatically generate your route configuration through your bundler's dev and build processes**. It is the easiest way to use TanStack Router's route generation features.

If your bundler is not yet supported, you can reach out to us on Discord or GitHub to let us know. Till then, fear not! You can still use the [`@tanstack/router-cli`](../installation-with-router-cli.md) package to generate your route tree file.

# File Naming Conventions

File-based routing requires that you follow a few simple file naming conventions to ensure that your routes are generated correctly. The concepts these conventions enable are covered in detail in the [Route Trees & Nesting](../route-trees.md) guide.

| Feature                            | Description                                                                                                                                                                                                                                                                                                                                                                        |
| ---------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`__root.tsx`**                   | The root route file must be named `__root.tsx` and must be placed in the root of the configured `routesDirectory`.                                                                                                                                                                                                                                                                 |
| **`.` Separator**                  | Routes can use the `.` character to denote a nested route. For example, `blog.post` will be generated as a child of `blog`.                                                                                                                                                                                                                                                        |
| **`$` Token**                      | Route segments with the `$` token are parameterized and will extract the value from the URL pathname as a route `param`.                                                                                                                                                                                                                                                           |
| **`_` Prefix**                     | Route segments with the `_` prefix are considered to be pathless layout routes and will not be used when matching its child routes against the URL pathname.                                                                                                                                                                                                                       |
| **`_` Suffix**                     | Route segments with the `_` suffix exclude the route from being nested under any parent routes.                                                                                                                                                                                                                                                                                    |
| **`-` Prefix**                     | Files and folders with the `-` prefix are excluded from the route tree. They will not be added to the `routeTree.gen.ts` file and can be used to colocate logic in route folders.                                                                                                                                                                                                  |
| **`(folder)` folder name pattern** | A folder that matches this pattern is treated as a **route group**, preventing the folder from being included in the route's URL path.                                                                                                                                                                                                                                             |
| **`[x]` Escaping**                 | Square brackets escape special characters in filenames that would otherwise have routing meaning. For example, `script[.]js.tsx` becomes `/script.js` and `api[.]v1.tsx` becomes `/api.v1`.                                                                                                                                                                                        |
| **`index` Token**                  | Route segments ending with the `index` token (before any file extensions) will match the parent route when the URL pathname matches the parent route exactly. This can be configured via the `indexToken` configuration option, see [options](../../../../api/file-based-routing.md#indextoken).                                                                                   |
| **`.route.tsx` File Type**         | When using directories to organise routes, the `route` suffix can be used to create a route file at the directory's path. For example, `blog.post.route.tsx` or `blog/post/route.tsx` can be used as the route file for the `/blog/post` route. This can be configured via the `routeToken` configuration option, see [options](../../../../api/file-based-routing.md#routetoken). |

> **💡 Remember:** The file-naming conventions for your project could be affected by what [options](../../../../api/file-based-routing.md) are configured.

## Dynamic Path Params

Dynamic path params can be used in both flat and directory routes to create routes that can match a dynamic segment of the URL path. Dynamic path params are denoted by the `$` character in the filename:

| Filename              | Route Path       | Component Output      |
| --------------------- | ---------------- | --------------------- |
| ...                   | ...              | ...                   |
| ʦ `posts.$postId.tsx` | `/posts/$postId` | `<Root><Posts><Post>` |

We'll learn more about dynamic path params in the [Path Params](../../guide/path-params.md) guide.

## Pathless Routes

Pathless routes wrap child routes with either logic or a component without requiring a URL path. Non-path routes are denoted by the `_` character in the filename:

| Filename       | Route Path | Component Output |
| -------------- | ---------- | ---------------- |
| ʦ `_app.tsx`   |            |                  |
| ʦ `_app.a.tsx` | /a         | `<Root><App><A>` |
| ʦ `_app.b.tsx` | /b         | `<Root><App><B>` |

To learn more about pathless routes, see the [Routing Concepts - Pathless Routes](../routing-concepts.md#pathless-layout-routes) guide.

# Installation with Esbuild

[//]: # 'BundlerConfiguration'

To use file-based routing with **Esbuild**, you'll need to install the `@tanstack/router-plugin` package.

```sh
npm install -D @tanstack/router-plugin
```

Once installed, you'll need to add the plugin to your configuration.

```tsx
// esbuild.config.js
import { tanstackRouter } from '@tanstack/router-plugin/esbuild'

export default {
  // ...
  plugins: [
    tanstackRouter({
      target: 'react',
      autoCodeSplitting: true,
    }),
  ],
}
```

Or, you can clone our [Quickstart Esbuild example](https://github.com/TanStack/router/tree/main/examples/react/quickstart-esbuild-file-based) and get started.

Now that you've added the plugin to your Esbuild configuration, you're all set to start using file-based routing with TanStack Router.

[//]: # 'BundlerConfiguration'

## Ignoring the generated route tree file

If your project is configured to use a linter and/or formatter, you may want to ignore the generated route tree file. This file is managed by TanStack Router and therefore shouldn't be changed by your linter or formatter.

Here are some resources to help you ignore the generated route tree file:

- Prettier - [https://prettier.io/docs/en/ignore.html#ignoring-files-prettierignore](https://prettier.io/docs/en/ignore.html#ignoring-files-prettierignore)
- ESLint - [https://eslint.org/docs/latest/use/configure/ignore#ignoring-files](https://eslint.org/docs/latest/use/configure/ignore#ignoring-files)
- Biome - [https://biomejs.dev/reference/configuration/#filesignore](https://biomejs.dev/reference/configuration/#filesignore)

> [!WARNING]
> If you are using VSCode, you may experience the route tree file unexpectedly open (with errors) after renaming a route.

You can prevent that from the VSCode settings by marking the file as readonly. Our recommendation is to also exclude it from search results and file watcher with the following settings:

```json
{
  "files.readonlyInclude": {
    "**/routeTree.gen.ts": true
  },
  "files.watcherExclude": {
    "**/routeTree.gen.ts": true
  },
  "search.exclude": {
    "**/routeTree.gen.ts": true
  }
}
```

You can use those settings either at a user level or only for a single workspace by creating the file `.vscode/settings.json` at the root of your project.

## Configuration

When using the TanStack Router Plugin with Esbuild for File-based routing, it comes with some sane defaults that should work for most projects:

```json
{
  "routesDirectory": "./src/routes",
  "generatedRouteTree": "./src/routeTree.gen.ts",
  "routeFileIgnorePrefix": "-",
  "quoteStyle": "single"
}
```

If these defaults work for your project, you don't need to configure anything at all! However, if you need to customize the configuration, you can do so by editing the configuration object passed into the `tanstackRouter` function.

You can find all the available configuration options in the [File-based Routing API Reference](../../../../api/file-based-routing.md).

# Installation with Router CLI

> [!WARNING]
> You should only use the TanStack Router CLI if you are not using a supported bundler. The CLI only supports the generation of the route tree file and does not provide any other features.

To use file-based routing with the TanStack Router CLI, you'll need to install the `@tanstack/router-cli` package.

```sh
npm install -D @tanstack/router-cli
```

Once installed, you'll need to amend your scripts in your `package.json` for the CLI to `watch` and `generate` files.

```json
{
  "scripts": {
    "generate-routes": "tsr generate",
    "watch-routes": "tsr watch",
    "build": "npm run generate-routes && ...",
    "dev": "npm run watch-routes && ..."
  }
}
```

[//]: # 'AfterScripts'
[//]: # 'AfterScripts'

You shouldn't forget to _ignore_ the generated route tree file. Head over to the [Ignoring the generated route tree file](#ignoring-the-generated-route-tree-file) section to learn more.

With the CLI installed, the following commands are made available via the `tsr` command

## Using the `generate` command

Generates the routes for a project based on the provided configuration.

```sh
tsr generate
```

## Using the `watch` command

Continuously watches the specified directories and regenerates routes as needed.

**Usage:**

```sh
tsr watch
```

With file-based routing enabled, whenever you start your application in development mode, TanStack Router will watch your configured `routesDirectory` and generate your route tree whenever a file is added, removed, or changed.

## Ignoring the generated route tree file

If your project is configured to use a linter and/or formatter, you may want to ignore the generated route tree file. This file is managed by TanStack Router and therefore shouldn't be changed by your linter or formatter.

Here are some resources to help you ignore the generated route tree file:

- Prettier - [https://prettier.io/docs/en/ignore.html#ignoring-files-prettierignore](https://prettier.io/docs/en/ignore.html#ignoring-files-prettierignore)
- ESLint - [https://eslint.org/docs/latest/use/configure/ignore#ignoring-files](https://eslint.org/docs/latest/use/configure/ignore#ignoring-files)
- Biome - [https://biomejs.dev/reference/configuration/#filesignore](https://biomejs.dev/reference/configuration/#filesignore)

> [!WARNING]
> If you are using VSCode, you may experience the route tree file unexpectedly open (with errors) after renaming a route.

You can prevent that from the VSCode settings by marking the file as readonly. Our recommendation is to also exclude it from search results and file watcher with the following settings:

```json
{
  "files.readonlyInclude": {
    "**/routeTree.gen.ts": true
  },
  "files.watcherExclude": {
    "**/routeTree.gen.ts": true
  },
  "search.exclude": {
    "**/routeTree.gen.ts": true
  }
}
```

You can use those settings either at a user level or only for a single workspace by creating the file `.vscode/settings.json` at the root of your project.

## Configuration

When using the TanStack Router CLI for File-based routing, it comes with some sane defaults that should work for most projects:

```json
{
  "routesDirectory": "./src/routes",
  "generatedRouteTree": "./src/routeTree.gen.ts",
  "routeFileIgnorePrefix": "-",
  "quoteStyle": "single"
}
```

If these defaults work for your project, you don't need to configure anything at all! However, if you need to customize the configuration, you can do so by creating a `tsr.config.json` file in the root of your project directory.

[//]: # 'TargetConfiguration'
[//]: # 'TargetConfiguration'

You can find all the available configuration options in the [File-based Routing API Reference](../../../../api/file-based-routing.md).

# Installation with Rspack

[//]: # 'BundlerConfiguration'

To use file-based routing with **Rspack** or **Rsbuild**, you'll need to install the `@tanstack/router-plugin` package.

```sh
npm install -D @tanstack/router-plugin
```

Once installed, you'll need to add the plugin to your configuration.

```tsx
// rsbuild.config.ts
import { defineConfig } from '@rsbuild/core'
import { pluginReact } from '@rsbuild/plugin-react'
import { tanstackRouter } from '@tanstack/router-plugin/rspack'

export default defineConfig({
  plugins: [pluginReact()],
  tools: {
    rspack: {
      plugins: [
        tanstackRouter({
          target: 'react',
          autoCodeSplitting: true,
        }),
      ],
    },
  },
})
```

Or, you can clone our [Quickstart Rspack/Rsbuild example](https://github.com/TanStack/router/tree/main/examples/react/quickstart-rspack-file-based) and get started.

Now that you've added the plugin to your Rspack/Rsbuild configuration, you're all set to start using file-based routing with TanStack Router.

[//]: # 'BundlerConfiguration'

## Ignoring the generated route tree file

If your project is configured to use a linter and/or formatter, you may want to ignore the generated route tree file. This file is managed by TanStack Router and therefore shouldn't be changed by your linter or formatter.

Here are some resources to help you ignore the generated route tree file:

- Prettier - [https://prettier.io/docs/en/ignore.html#ignoring-files-prettierignore](https://prettier.io/docs/en/ignore.html#ignoring-files-prettierignore)
- ESLint - [https://eslint.org/docs/latest/use/configure/ignore#ignoring-files](https://eslint.org/docs/latest/use/configure/ignore#ignoring-files)
- Biome - [https://biomejs.dev/reference/configuration/#filesignore](https://biomejs.dev/reference/configuration/#filesignore)

> [!WARNING]
> If you are using VSCode, you may experience the route tree file unexpectedly open (with errors) after renaming a route.

You can prevent that from the VSCode settings by marking the file as readonly. Our recommendation is to also exclude it from search results and file watcher with the following settings:

```json
{
  "files.readonlyInclude": {
    "**/routeTree.gen.ts": true
  },
  "files.watcherExclude": {
    "**/routeTree.gen.ts": true
  },
  "search.exclude": {
    "**/routeTree.gen.ts": true
  }
}
```

You can use those settings either at a user level or only for a single workspace by creating the file `.vscode/settings.json` at the root of your project.

## Configuration

When using the TanStack Router Plugin with Rspack (or Rsbuild) for File-based routing, it comes with some sane defaults that should work for most projects:

```json
{
  "routesDirectory": "./src/routes",
  "generatedRouteTree": "./src/routeTree.gen.ts",
  "routeFileIgnorePrefix": "-",
  "quoteStyle": "single"
}
```

If these defaults work for your project, you don't need to configure anything at all! However, if you need to customize the configuration, you can do so by editing the configuration object passed into the `tanstackRouter` function.

You can find all the available configuration options in the [File-based Routing API Reference](../../../../api/file-based-routing.md).

# Installation with Vite

[//]: # 'BundlerConfiguration'

To use file-based routing with **Vite**, you'll need to install the `@tanstack/router-plugin` package.

```sh
npm install -D @tanstack/router-plugin
```

Once installed, you'll need to add the plugin to your Vite configuration.

```ts
// vite.config.ts
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import { tanstackRouter } from '@tanstack/router-plugin/vite'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    // Please make sure that '@tanstack/router-plugin' is passed before '@vitejs/plugin-react'
    tanstackRouter({
      target: 'react',
      autoCodeSplitting: true,
    }),
    react(),
    // ...
  ],
})
```

Or, you can clone our [Quickstart Vite example](https://github.com/TanStack/router/tree/main/examples/react/quickstart-file-based) and get started.

> [!WARNING]
> If you are using the older `@tanstack/router-vite-plugin` package, you can still continue to use it, as it will be aliased to the `@tanstack/router-plugin/vite` package. However, we would recommend using the `@tanstack/router-plugin` package directly.

Now that you've added the plugin to your Vite configuration, you're all set to start using file-based routing with TanStack Router.

[//]: # 'BundlerConfiguration'

## Ignoring the generated route tree file

If your project is configured to use a linter and/or formatter, you may want to ignore the generated route tree file. This file is managed by TanStack Router and therefore shouldn't be changed by your linter or formatter.

Here are some resources to help you ignore the generated route tree file:

- Prettier - [https://prettier.io/docs/en/ignore.html#ignoring-files-prettierignore](https://prettier.io/docs/en/ignore.html#ignoring-files-prettierignore)
- ESLint - [https://eslint.org/docs/latest/use/configure/ignore#ignoring-files](https://eslint.org/docs/latest/use/configure/ignore#ignoring-files)
- Biome - [https://biomejs.dev/reference/configuration/#filesignore](https://biomejs.dev/reference/configuration/#filesignore)

> [!WARNING]
> If you are using VSCode, you may experience the route tree file unexpectedly open (with errors) after renaming a route.

You can prevent that from the VSCode settings by marking the file as readonly. Our recommendation is to also exclude it from search results and file watcher with the following settings:

```json
{
  "files.readonlyInclude": {
    "**/routeTree.gen.ts": true
  },
  "files.watcherExclude": {
    "**/routeTree.gen.ts": true
  },
  "search.exclude": {
    "**/routeTree.gen.ts": true
  }
}
```

You can use those settings either at a user level or only for a single workspace by creating the file `.vscode/settings.json` at the root of your project.

## Configuration

When using the TanStack Router Plugin with Vite for File-based routing, it comes with some sane defaults that should work for most projects:

```json
{
  "routesDirectory": "./src/routes",
  "generatedRouteTree": "./src/routeTree.gen.ts",
  "routeFileIgnorePrefix": "-",
  "quoteStyle": "single"
}
```

If these defaults work for your project, you don't need to configure anything at all! However, if you need to customize the configuration, you can do so by editing the configuration object passed into the `tanstackRouter` function.

You can find all the available configuration options in the [File-based Routing API Reference](../../../../api/file-based-routing.md).

# Installation with Webpack

[//]: # 'BundlerConfiguration'

To use file-based routing with **Webpack**, you'll need to install the `@tanstack/router-plugin` package.

```sh
npm install -D @tanstack/router-plugin
```

Once installed, you'll need to add the plugin to your configuration.

```tsx
// webpack.config.ts
import { tanstackRouter } from '@tanstack/router-plugin/webpack'

export default {
  plugins: [
    tanstackRouter({
      target: 'react',
      autoCodeSplitting: true,
    }),
  ],
}
```

Or, you can clone our [Quickstart Webpack example](https://github.com/TanStack/router/tree/main/examples/react/quickstart-webpack-file-based) and get started.

Now that you've added the plugin to your Webpack configuration, you're all set to start using file-based routing with TanStack Router.

[//]: # 'BundlerConfiguration'

## Ignoring the generated route tree file

If your project is configured to use a linter and/or formatter, you may want to ignore the generated route tree file. This file is managed by TanStack Router and therefore shouldn't be changed by your linter or formatter.

Here are some resources to help you ignore the generated route tree file:

- Prettier - [https://prettier.io/docs/en/ignore.html#ignoring-files-prettierignore](https://prettier.io/docs/en/ignore.html#ignoring-files-prettierignore)
- ESLint - [https://eslint.org/docs/latest/use/configure/ignore#ignoring-files](https://eslint.org/docs/latest/use/configure/ignore#ignoring-files)
- Biome - [https://biomejs.dev/reference/configuration/#filesignore](https://biomejs.dev/reference/configuration/#filesignore)

> [!WARNING]
> If you are using VSCode, you may experience the route tree file unexpectedly open (with errors) after renaming a route.

You can prevent that from the VSCode settings by marking the file as readonly. Our recommendation is to also exclude it from search results and file watcher with the following settings:

```json
{
  "files.readonlyInclude": {
    "**/routeTree.gen.ts": true
  },
  "files.watcherExclude": {
    "**/routeTree.gen.ts": true
  },
  "search.exclude": {
    "**/routeTree.gen.ts": true
  }
}
```

You can use those settings either at a user level or only for a single workspace by creating the file `.vscode/settings.json` at the root of your project.

## Configuration

When using the TanStack Router Plugin with Webpack for File-based routing, it comes with some sane defaults that should work for most projects:

```json
{
  "routesDirectory": "./src/routes",
  "generatedRouteTree": "./src/routeTree.gen.ts",
  "routeFileIgnorePrefix": "-",
  "quoteStyle": "single"
}
```

If these defaults work for your project, you don't need to configure anything at all! However, if you need to customize the configuration, you can do so by editing the configuration object passed into the `tanstackRouter` function.

You can find all the available configuration options in the [File-based Routing API Reference](../../../../api/file-based-routing.md).

# Route Matching

Route matching follows a consistent and predictable pattern. This guide will explain how route trees are matched.

When TanStack Router processes your route tree, all of your routes are automatically sorted to match the most specific routes first. This means that regardless of the order your route tree is defined, routes will always be sorted in this order:

- Index Route
- Static Routes (most specific to least specific)
- Dynamic Routes (longest to shortest)
- Splat/Wildcard Routes

Consider the following pseudo route tree:

```
Root
  - blog
    - $postId
    - /
    - new
  - /
  - *
  - about
  - about/us
```

After sorting, this route tree will become:

```
Root
  - /
  - about/us
  - about
  - blog
    - /
    - new
    - $postId
  - *
```

This final order represents the order in which routes will be matched based on specificity.

Using that route tree, let's follow the matching process for a few different URLs:

- `/blog`
  ```
  Root
    ❌ /
    ❌ about/us
    ❌ about
    ⏩ blog
      ✅ /
      - new
      - $postId
    - *
  ```
- `/blog/my-post`
  ```
  Root
    ❌ /
    ❌ about/us
    ❌ about
    ⏩ blog
      ❌ /
      ❌ new
      ✅ $postId
    - *
  ```
- `/`
  ```
  Root
    ✅ /
    - about/us
    - about
    - blog
      - /
      - new
      - $postId
    - *
  ```
- `/not-a-route`
  ```
  Root
    ❌ /
    ❌ about/us
    ❌ about
    ❌ blog
      - /
      - new
      - $postId
    ✅ *
  ```

# Route Trees

TanStack Router uses a nested route tree to match up the URL with the correct component tree to render.

To build a route tree, TanStack Router supports:

- [File-Based Routing](../file-based-routing.md)
- [Code-Based Routing](../code-based-routing.md)

Both methods support the exact same core features and functionality, but **file-based routing requires less code for the same or better results**. For this reason, **file-based routing is the preferred and recommended way** to configure TanStack Router. Most of the documentation is written from the perspective of file-based routing.

## Route Trees

Nested routing is a powerful concept that allows you to use a URL to render a nested component tree. For example, given the URL of `/blog/posts/123`, you could create a route hierarchy that looks like this:

```tsx
├── blog
│   ├── posts
│   │   ├── $postId
```

And render a component tree that looks like this:

```tsx
<Blog>
  <Posts>
    <Post postId="123" />
  </Posts>
</Blog>
```

Let's take that concept and expand it out to a larger site structure, but with file-names now:

```
/routes
├── __root.tsx
├── index.tsx
├── about.tsx
├── posts/
│   ├── index.tsx
│   ├── $postId.tsx
├── posts.$postId.edit.tsx
├── settings/
│   ├── profile.tsx
│   ├── notifications.tsx
├── _pathlessLayout/
│   ├── route-a.tsx
├── ├── route-b.tsx
├── files/
│   ├── $.tsx
```

The above is a valid route tree configuration that can be used with TanStack Router! There's a lot of power and convention to unpack with file-based routing, so let's break it down a bit.

## Route Tree Configuration

Route trees can be configured using a few different ways:

- [Flat Routes](../file-based-routing.md#flat-routes)
- [Directories](../file-based-routing.md#directory-routes)
- [Mixed Flat Routes and Directories](../file-based-routing.md#mixed-flat-and-directory-routes)
- [Virtual File Routes](../virtual-file-routes.md)
- [Code-Based Routes](../code-based-routing.md)

Please be sure to check out the full documentation links above for each type of route tree, or just proceed to the next section to get started with file-based routing.

# Routing Concepts

TanStack Router supports a number of powerful routing concepts that allow you to build complex and dynamic routing systems with ease.

Each of these concepts is useful and powerful, and we'll dive into each of them in the following sections.

## Anatomy of a Route

All other routes, other than the [Root Route](#the-root-route), are configured using the `createFileRoute` function, which provides type safety when using file-based routing:

```tsx
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/')({
  component: PostsComponent,
})
```

The `createFileRoute` function takes a single argument, the file-route's path as a string.

**❓❓❓ "Wait, you're making me pass the path of the route file to `createFileRoute`?"**

Yes! But don't worry, this path is **automatically written and managed by the router for you via the TanStack Router Bundler Plugin or Router CLI.** So, as you create new routes, move routes around or rename routes, the path will be updated for you automatically.

The reason for this pathname has everything to do with the magical type safety of TanStack Router. Without this pathname, TypeScript would have no idea what file we're in! (We wish TypeScript had a built-in for this, but they don't yet 🤷‍♂️)

## The Root Route

The root route is the top-most route in the entire tree and encapsulates all other routes as children.

- It has no path
- It is **always** matched
- Its `component` is **always** rendered

Even though it doesn't have a path, the root route has access to all of the same functionality as other routes including:

- components
- loaders
- search param validation
- etc.

To create a root route, call the `createRootRoute()` function and export it as the `Route` variable in your route file:

```tsx
// Standard root route
import { createRootRoute } from '@tanstack/react-router'

export const Route = createRootRoute()

// Root route with Context
import { createRootRouteWithContext } from '@tanstack/react-router'
import type { QueryClient } from '@tanstack/react-query'

export interface MyRouterContext {
  queryClient: QueryClient
}
export const Route = createRootRouteWithContext<MyRouterContext>()
```

To learn more about Context in TanStack Router, see the [Router Context](../../guide/router-context.md) guide.

## Basic Routes

Basic routes match a specific path, for example `/about`, `/settings`, `/settings/notifications` are all basic routes, as they match the path exactly.

Let's take a look at an `/about` route:

```tsx
// about.tsx
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/about')({
  component: AboutComponent,
})

function AboutComponent() {
  return <div>About</div>
}
```

Basic routes are simple and straightforward. They match the path exactly and render the provided component.

## Index Routes

Index routes specifically target their parent route when it is **matched exactly and no child route is matched**.

Let's take a look at an index route for a `/posts` URL:

```tsx
// posts.index.tsx
import { createFileRoute } from '@tanstack/react-router'

// Note the trailing slash, which is used to target index routes
export const Route = createFileRoute('/posts/')({
  component: PostsIndexComponent,
})

function PostsIndexComponent() {
  return <div>Please select a post!</div>
}
```

This route will be matched when the URL is `/posts` exactly.

## Dynamic Route Segments

Route path segments that start with a `$` followed by a label are dynamic and capture that section of the URL into the `params` object for use in your application. For example, a pathname of `/posts/123` would match the `/posts/$postId` route, and the `params` object would be `{ postId: '123' }`.

These params are then usable in your route's configuration and components! Let's look at a `posts.$postId.tsx` route:

```tsx
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/posts/$postId')({
  // In a loader
  loader: ({ params }) => fetchPost(params.postId),
  // Or in a component
  component: PostComponent,
})

function PostComponent() {
  // In a component!
  const { postId } = Route.useParams()
  return <div>Post ID: {postId}</div>
}
```

> 🧠 Dynamic segments work at **each** segment of the path. For example, you could have a route with the path of `/posts/$postId/$revisionId` and each `$` segment would be captured into the `params` object.

## Splat / Catch-All Routes

A route with a path of only `$` is called a "splat" route because it _always_ captures _any_ remaining section of the URL pathname from the `$` to the end. The captured pathname is then available in the `params` object under the special `_splat` property.

For example, a route targeting the `files/$` path is a splat route. If the URL pathname is `/files/documents/hello-world`, the `params` object would contain `documents/hello-world` under the special `_splat` property:

```js
{
  '_splat': 'documents/hello-world'
}
```

> ⚠️ In v1 of the router, splat routes are also denoted with a `*` instead of a `_splat` key for backwards compatibility. This will be removed in v2.

> 🧠 Why use `$`? Thanks to tools like Remix, we know that despite `*`s being the most common character to represent a wildcard, they do not play nice with filenames or CLI tools, so just like them, we decided to use `$` instead.

## Optional Path Parameters

Optional path parameters allow you to define route segments that may or may not be present in the URL. They use the `{-$paramName}` syntax and provide flexible routing patterns where certain parameters are optional.

```tsx
// posts.{-$category}.tsx - Optional category parameter
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/posts/{-$category}')({
  component: PostsComponent,
})

function PostsComponent() {
  const { category } = Route.useParams()

  return <div>{category ? `Posts in ${category}` : 'All Posts'}</div>
}
```

This route will match both `/posts` (category is `undefined`) and `/posts/tech` (category is `"tech"`).

You can also define multiple optional parameters in a single route:

```tsx
// posts.{-$category}.{-$slug}.tsx
export const Route = createFileRoute('/posts/{-$category}/{-$slug}')({
  component: PostsComponent,
})
```

This route matches `/posts`, `/posts/tech`, and `/posts/tech/hello-world`.

> 🧠 Routes with optional parameters are ranked lower in priority than exact matches, ensuring that more specific routes like `/posts/featured` are matched before `/posts/{-$category}`.

## Layout Routes

Layout routes are used to wrap child routes with additional components and logic. They are useful for:

- Wrapping child routes with a layout component
- Enforcing a `loader` requirement before displaying any child routes
- Validating and providing search params to child routes
- Providing fallbacks for error components or pending elements to child routes
- Providing shared context to all child routes
- And more!

Let's take a look at an example layout route called `app.tsx`:

```
routes/
├── app.tsx
├── app.dashboard.tsx
├── app.settings.tsx
```

In the tree above, `app.tsx` is a layout route that wraps two child routes, `app.dashboard.tsx` and `app.settings.tsx`.

This tree structure is used to wrap the child routes with a layout component:

```tsx
import { Outlet, createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/app')({
  component: AppLayoutComponent,
})

function AppLayoutComponent() {
  return (
    <div>
      <h1>App Layout</h1>
      <Outlet />
    </div>
  )
}
```

The following table shows which component(s) will be rendered based on the URL:

| URL Path         | Component                |
| ---------------- | ------------------------ |
| `/app`           | `<AppLayout>`            |
| `/app/dashboard` | `<AppLayout><Dashboard>` |
| `/app/settings`  | `<AppLayout><Settings>`  |

Since TanStack Router supports mixed flat and directory routes, you can also express your application's routing using layout routes within directories:

```
routes/
├── app/
│   ├── route.tsx
│   ├── dashboard.tsx
│   ├── settings.tsx
```

In this nested tree, the `app/route.tsx` file is a configuration for the layout route that wraps two child routes, `app/dashboard.tsx` and `app/settings.tsx`.

Layout Routes also let you enforce component and loader logic for Dynamic Route Segments:

```
routes/
├── app/users/
│   ├── $userId/
|   |   ├── route.tsx
|   |   ├── index.tsx
|   |   ├── edit.tsx
```

## Pathless Layout Routes

Like [Layout Routes](#layout-routes), Pathless Layout Routes are used to wrap child routes with additional components and logic. However, pathless layout routes do not require a matching `path` in the URL and are used to wrap child routes with additional components and logic without requiring a matching `path` in the URL.

Pathless Layout Routes are prefixed with an underscore (`_`) to denote that they are "pathless".

> 🧠 The part of the path after the `_` prefix is used as the route's ID and is required because every route must be uniquely identifiable, especially when using TypeScript so as to avoid type errors and accomplish autocomplete effectively.

Let's take a look at an example route called `_pathlessLayout.tsx`:

```

routes/
├── _pathlessLayout.tsx
├── _pathlessLayout.a.tsx
├── _pathlessLayout.b.tsx

```

In the tree above, `_pathlessLayout.tsx` is a pathless layout route that wraps two child routes, `_pathlessLayout.a.tsx` and `_pathlessLayout.b.tsx`.

The `_pathlessLayout.tsx` route is used to wrap the child routes with a Pathless layout component:

```tsx
import { Outlet, createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/_pathlessLayout')({
  component: PathlessLayoutComponent,
})

function PathlessLayoutComponent() {
  return (
    <div>
      <h1>Pathless layout</h1>
      <Outlet />
    </div>
  )
}
```

The following table shows which component will be rendered based on the URL:

| URL Path | Component             |
| -------- | --------------------- |
| `/`      | `<Index>`             |
| `/a`     | `<PathlessLayout><A>` |
| `/b`     | `<PathlessLayout><B>` |

Since TanStack Router supports mixed flat and directory routes, you can also express your application's routing using pathless layout routes within directories:

```
routes/
├── _pathlessLayout/
│   ├── route.tsx
│   ├── a.tsx
│   ├── b.tsx
```

However, unlike Layout Routes, since Pathless Layout Routes do match based on URL path segments, this means that these routes do not support [Dynamic Route Segments](#dynamic-route-segments) as part of their path and therefore cannot be matched in the URL.

This means that you cannot do this:

```
routes/
├── _$postId/ ❌
│   ├── ...
```

Rather, you'd have to do this:

```
routes/
├── $postId/
├── _postPathlessLayout/ ✅
│   ├── ...
```

## Non-Nested Routes

Non-nested routes can be created by suffixing a parent file route segment with a `_` and are used to **un-nest** a route from its parents and render its own component tree.

Consider the following flat route tree:

```
routes/
├── posts.tsx
├── posts.$postId.tsx
├── posts_.$postId.edit.tsx
```

The following table shows which component will be rendered based on the URL:

| URL Path          | Component                    |
| ----------------- | ---------------------------- |
| `/posts`          | `<Posts>`                    |
| `/posts/123`      | `<Posts><Post postId="123">` |
| `/posts/123/edit` | `<PostEditor postId="123">`  |

- The `posts.$postId.tsx` route is nested as normal under the `posts.tsx` route and will render `<Posts><Post>`.
- The `posts_.$postId.edit.tsx` route **does not share** the same `posts` prefix as the other routes and therefore will be treated as if it is a top-level route and will render `<PostEditor>`.

## Excluding Files and Folders from Routes

Files and folders can be excluded from route generation with a `-` prefix attached to the file name. This gives you the ability to colocate logic in the route directories.

Consider the following route tree:

```
routes/
├── posts.tsx
├── -posts-table.tsx // 👈🏼 ignored
├── -components/ // 👈🏼 ignored
│   ├── header.tsx // 👈🏼 ignored
│   ├── footer.tsx // 👈🏼 ignored
│   ├── ...
```

We can import from the excluded files into our posts route

```tsx
import { createFileRoute } from '@tanstack/react-router'
import { PostsTable } from './-posts-table'
import { PostsHeader } from './-components/header'
import { PostsFooter } from './-components/footer'

export const Route = createFileRoute('/posts')({
  loader: () => fetchPosts(),
  component: PostComponent,
})

function PostComponent() {
  const posts = Route.useLoaderData()

  return (
    <div>
      <PostsHeader />
      <PostsTable posts={posts} />
      <PostsFooter />
    </div>
  )
}
```

The excluded files will not be added to `routeTree.gen.ts`.

## Pathless Route Group Directories

Pathless route group directories use `()` as a way to group routes files together regardless of their path. They are purely organizational and do not affect the route tree or component tree in any way.

```
routes/
├── index.tsx
├── (app)/
│   ├── dashboard.tsx
│   ├── settings.tsx
│   ├── users.tsx
├── (auth)/
│   ├── login.tsx
│   ├── register.tsx
```

In the example above, the `app` and `auth` directories are purely organizational and do not affect the route tree or component tree in any way. They are used to group related routes together for easier navigation and organization.

The following table shows which component will be rendered based on the URL:

| URL Path     | Component     |
| ------------ | ------------- |
| `/`          | `<Index>`     |
| `/dashboard` | `<Dashboard>` |
| `/settings`  | `<Settings>`  |
| `/users`     | `<Users>`     |
| `/login`     | `<Login>`     |
| `/register`  | `<Register>`  |

As you can see, the `app` and `auth` directories are purely organizational and do not affect the route tree or component tree in any way.

# Virtual File Routes

> We'd like to thank the Remix team for [pioneering the concept of virtual file routes](https://www.youtube.com/watch?v=fjTX8hQTlEc&t=730s). We've taken inspiration from their work and adapted it to work with TanStack Router's existing file-based route-tree generation.

Virtual file routes are a powerful concept that allows you to build a route tree programmatically using code that references real files in your project. This can be useful if:

- You have an existing route organization that you want to keep.
- You want to customize the location of your route files.
- You want to completely override TanStack Router's file-based route generation and build your own convention.

Here's a quick example of using virtual file routes to map a route tree to a set of real files in your project:

```tsx
// routes.ts
import { rootRoute, route, index, layout, physical } from '@tanstack/virtual-file-routes'

export const routes = rootRoute('root.tsx', [
  index('index.tsx'),
  layout('pathlessLayout.tsx', [
    route('/dashboard', 'app/dashboard.tsx', [
      index('app/dashboard-index.tsx'),
      route('/invoices', 'app/dashboard-invoices.tsx', [
        index('app/invoices-index.tsx'),
        route('$id', 'app/invoice-detail.tsx'),
      ]),
    ]),
    physical('/posts', 'posts'),
  ]),
])
```

## Configuration

Virtual file routes can be configured either via:

- The `TanStackRouter` plugin for Vite/Rspack/Webpack
- The `tsr.config.json` file for the TanStack Router CLI

## Configuration via the TanStackRouter Plugin

If you're using the `TanStackRouter` plugin for Vite/Rspack/Webpack, you can configure virtual file routes by passing the path of your routes file to the `virtualRoutesConfig` option when setting up the plugin:

```tsx
// vite.config.ts
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import { tanstackRouter } from '@tanstack/router-plugin/vite'

export default defineConfig({
  plugins: [
    tanstackRouter({
      target: 'react',
      virtualRouteConfig: './routes.ts',
    }),
    react(),
  ],
})
```

Or, you choose to define the virtual routes directly in the configuration:

```tsx
// vite.config.ts
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import { tanstackRouter } from '@tanstack/router-plugin/vite'
import { rootRoute } from '@tanstack/virtual-file-routes'

const routes = rootRoute('root.tsx', [
  // ... the rest of your virtual route tree
])

export default defineConfig({
  plugins: [tanstackRouter({ virtualRouteConfig: routes }), react()],
})
```

## Creating Virtual File Routes

To create virtual file routes, you'll need to import the `@tanstack/virtual-file-routes` package. This package provides a set of functions that allow you to create virtual routes that reference real files in your project. A few utility functions are exported from the package:

- `rootRoute` - Creates a virtual root route.
- `route` - Creates a virtual route.
- `index` - Creates a virtual index route.
- `layout` - Creates a virtual pathless layout route.
- `physical` - Creates a physical virtual route (more on this later).

## Virtual Root Route

The `rootRoute` function is used to create a virtual root route. It takes a file name and an array of children routes. Here's an example of a virtual root route:

```tsx
// routes.ts
import { rootRoute } from '@tanstack/virtual-file-routes'

export const routes = rootRoute('root.tsx', [
  // ... children routes
])
```

## Virtual Route

The `route` function is used to create a virtual route. It takes a path, a file name, and an array of children routes. Here's an example of a virtual route:

```tsx
// routes.ts
import { route } from '@tanstack/virtual-file-routes'

export const routes = rootRoute('root.tsx', [
  route('/about', 'about.tsx', [
    // ... children routes
  ]),
])
```

You can also define a virtual route without a file name. This allows to set a common path prefix for its children:

```tsx
// routes.ts
import { route } from '@tanstack/virtual-file-routes'

export const routes = rootRoute('root.tsx', [
  route('/hello', [
    route('/world', 'world.tsx'), // full path will be "/hello/world"
    route('/universe', 'universe.tsx'), // full path will be "/hello/universe"
  ]),
])
```

## Virtual Index Route

The `index` function is used to create a virtual index route. It takes a file name. Here's an example of a virtual index route:

```tsx
import { index } from '@tanstack/virtual-file-routes'

const routes = rootRoute('root.tsx', [index('index.tsx')])
```

## Virtual Pathless Route

The `layout` function is used to create a virtual pathless route. It takes a file name, an array of children routes, and an optional pathless ID. Here's an example of a virtual pathless route:

```tsx
// routes.ts
import { layout } from '@tanstack/virtual-file-routes'

export const routes = rootRoute('root.tsx', [
  layout('pathlessLayout.tsx', [
    // ... children routes
  ]),
])
```

You can also specify a pathless ID to give the route a unique identifier that is different from the filename:

```tsx
// routes.ts
import { layout } from '@tanstack/virtual-file-routes'

export const routes = rootRoute('root.tsx', [
  layout('my-pathless-layout-id', 'pathlessLayout.tsx', [
    // ... children routes
  ]),
])
```

## Physical Virtual Routes

Physical virtual routes are a way to "mount" a directory of good ol' TanStack Router File Based routing convention under a specific URL path. This can be useful if you are using virtual routes to customize a small portion of your route tree high up in the hierarchy, but want to use the standard file-based routing convention for sub-routes and directories.

Consider the following file structure:

```
/routes
├── root.tsx
├── index.tsx
├── pathlessLayout.tsx
├── app
│   ├── dashboard.tsx
│   ├── dashboard-index.tsx
│   ├── dashboard-invoices.tsx
│   ├── invoices-index.tsx
│   ├── invoice-detail.tsx
└── posts
    ├── index.tsx
    ├── $postId.tsx
    ├── $postId.edit.tsx
    ├── comments/
    │   ├── index.tsx
    │   ├── $commentId.tsx
    └── likes/
        ├── index.tsx
        ├── $likeId.tsx
```

Let's use virtual routes to customize our route tree for everything but `posts`, then use physical virtual routes to mount the `posts` directory under the `/posts` path:

```tsx
// routes.ts
export const routes = rootRoute('root.tsx', [
  // Set up your virtual routes as normal
  index('index.tsx'),
  layout('pathlessLayout.tsx', [
    route('/dashboard', 'app/dashboard.tsx', [
      index('app/dashboard-index.tsx'),
      route('/invoices', 'app/dashboard-invoices.tsx', [
        index('app/invoices-index.tsx'),
        route('$id', 'app/invoice-detail.tsx'),
      ]),
    ]),
    // Mount the `posts` directory under the `/posts` path
    physical('/posts', 'posts'),
  ]),
])
```

## Virtual Routes inside of TanStack Router File Based routing

The previous section showed you how you can use TanStack Router's File Based routing convention inside of a virtual route configuration.
However, the opposite is possible as well.  
You can configure the main part of your app's route tree using TanStack Router's File Based routing convention and opt into virtual route configuration for specific subtrees.

Consider the following file structure:

```
/routes
├── __root.tsx
├── foo
│   ├── bar
│   │   ├── __virtual.ts
│   │   ├── details.tsx
│   │   ├── home.tsx
│   │   └── route.ts
│   └── bar.tsx
└── index.tsx
```

Let's look at the `bar` directory which contains a special file named `__virtual.ts`. This file instructs the generator to switch over to virtual file route configuration for this directory (and its child directories).

`__virtual.ts` configures the virtual routes for that particular subtree of the route tree. It uses the same API as explained above, with the only difference being that no `rootRoute` is defined for that subtree:

```tsx
// routes/foo/bar/__virtual.ts
import { defineVirtualSubtreeConfig, index, route } from '@tanstack/virtual-file-routes'

export default defineVirtualSubtreeConfig([index('home.tsx'), route('$id', 'details.tsx')])
```

The helper function `defineVirtualSubtreeConfig` is closely modeled after vite's `defineConfig` and allows you to define a subtree configuration via a default export. The default export can either be

- a subtree config object
- a function returning a subtree config object
- an async function returning a subtree config object

## Inception

You can mix and match TanStack Router's File Based routing convention and virtual route configuration however you like.  
Let's go deeper!  
Check out the following example that starts off using File Based routing convention, switches over to virtual route configuration for `/posts`, switches back to File Based routing convention for `/posts/lets-go` only to switch over to virtual route configuration again for `/posts/lets-go/deeper`.

```
├── __root.tsx
├── index.tsx
├── posts
│   ├── __virtual.ts
│   ├── details.tsx
│   ├── home.tsx
│   └── lets-go
│       ├── deeper
│       │   ├── __virtual.ts
│       │   └── home.tsx
│       └── index.tsx
└── posts.tsx
```

## Configuration via the TanStack Router CLI

If you're using the TanStack Router CLI, you can configure virtual file routes by defining the path to your routes file in the `tsr.config.json` file:

```json
// tsr.config.json
{
  "virtualRouteConfig": "./routes.ts"
}
```

Or you can define the virtual routes directly in the configuration, while much less common allows you to configure them via the TanStack Router CLI by adding a `virtualRouteConfig` object to your `tsr.config.json` file and defining your virtual routes and passing the resulting JSON that is generated by calling the actual `rootRoute`/`route`/`index`/etc functions from the `@tanstack/virtual-file-routes` package:

```json
// tsr.config.json
{
  "virtualRouteConfig": {
    "type": "root",
    "file": "root.tsx",
    "children": [
      {
        "type": "index",
        "file": "home.tsx"
      },
      {
        "type": "route",
        "file": "posts/posts.tsx",
        "path": "/posts",
        "children": [
          {
            "type": "index",
            "file": "posts/posts-home.tsx"
          },
          {
            "type": "route",
            "file": "posts/posts-detail.tsx",
            "path": "$postId"
          }
        ]
      },
      {
        "type": "layout",
        "id": "first",
        "file": "layout/first-pathless-layout.tsx",
        "children": [
          {
            "type": "layout",
            "id": "second",
            "file": "layout/second-pathless-layout.tsx",
            "children": [
              {
                "type": "route",
                "file": "a.tsx",
                "path": "/route-a"
              },
              {
                "type": "route",
                "file": "b.tsx",
                "path": "/route-b"
              }
            ]
          }
        ]
      }
    ]
  }
}
```

</@tanstack/react-router_routing>

<@tanstack/react-router_setup-and-architecture>
Always Apply: false - This rule should only be applied when relevant files are open
Always apply this rule in these files: package.json, vite.config.ts, tsconfig.json, src/**/\*.ts, src/**/\*.tsx

# Overview

**TanStack Router is a router for building React and Solid applications**. Some of its features include:

- 100% inferred TypeScript support
- Typesafe navigation
- Nested Routing and layout routes (with pathless layouts)
- Built-in Route Loaders w/ SWR Caching
- Designed for client-side data caches (TanStack Query, SWR, etc.)
- Automatic route prefetching
- Asynchronous route elements and error boundaries
- File-based Route Generation
- Typesafe JSON-first Search Params state management APIs
- Path and Search Parameter Schema Validation
- Search Param Navigation APIs
- Custom Search Param parser/serializer support
- Search param middleware
- Route matching/loading middleware

To get started quickly, head to the next page. For a more lengthy explanation, buckle up while I bring you up to speed!

## "A Fork in the Route"

Using a router to build applications is widely regarded as a must-have and is usually one of the first choices you’ll make in your tech stack.

[//]: # 'WhyChooseTanStackRouter'

**So, why should you choose TanStack Router over another router?**

To answer this question, we need to look at the other options in the space. There are many if you look hard enough, but in my experience, only a couple are worth exploring seriously:

- **Next.js** - Widely regarded as the de facto framework for starting a new React project, it’s laser focused on performance, workflow, and bleeding edge technology. Its APIs and abstractions are powerful, but can sometimes come across as non-standard. Its extremely fast growth and adoption in the industry has resulted in a featured packed experience, but at the expense of feeling overwhelming and sometimes bloated.
- **Remix / React Router** - A full-stack framework based on the historically successful React Router offers a similarly powerful developer and user experience, with APIs and vision based firmly on web standards like Request/Response and a focus on running anywhere JS can run. Many of its APIs and abstractions are wonderfully designed and were inspiration for more than a few TanStack Router APIs. That said, its rigid design, bolted-on type safety and sometimes strict over-adherence to platform APIs can leave some developers wanting more.

Both of these frameworks (and their routers) are great, and I can personally attest that both are very good solutions for building React applications. My experience has also taught me that these solutions could also be much better, especially around the actual routing APIs that are available to developers to make their apps faster, easier, and more enjoyable to work with.

It's probably no surprise at this point that picking a router is so important that it is often tied 1-to-1 with your choice of framework, since most frameworks rely on a specific router.

[//]: # 'WhyChooseTanStackRouter'

**Does this mean that TanStack Router is a framework?**

TanStack Router itself is not a "framework" in the traditional sense, since it doesn't address a few other common full-stack concerns. However TanStack Router has been designed to be upgradable to a full-stack framework when used in conjunction with other tools that address bundling, deployments, and server-side-specific functionality. This is why we are currently developing [TanStack Start](https://tanstack.com/start), a full-stack framework that is built on top of TanStack Router and Vite.

For a deeper dive on the history of TanStack Router, feel free to read [TanStack Router's History](../decisions-on-dx.md#tanstack-routers-origin-story).

## Why TanStack Router?

TanStack Router delivers on the same fundamental expectations as other routers that you’ve come to expect:

- Nested routes, layout routes, grouped routes
- File-based Routing
- Parallel data loading
- Prefetching
- URL Path Params
- Error Boundaries and Handling
- SSR
- Route Masking

And it also delivers some new features that raise the bar:

- 100% inferred TypeScript support
- Typesafe navigation
- Built-in SWR Caching for loaders
- Designed for client-side data caches (TanStack Query, SWR, etc.)
- Typesafe JSON-first Search Params state management APIs
- Path and Search Parameter Schema Validation
- Search Parameter Navigation APIs
- Custom Search Param parser/serializer support
- Search param middleware
- Inherited Route Context
- Mixed file-based and code-based routing

Let’s dive into some of the more important ones in more detail!

## 100% Inferred TypeScript Support

Everything these days is written “in Typescript” or at the very least offers type definitions that are veneered over runtime functionality, but too few packages in the ecosystem actually design their APIs with TypeScript in mind. So while I’m pleased that your router is auto-completing your option fields and catching a few property/method typos here and there, there is much more to be had.

- TanStack Router is fully aware of all of your routes and their configuration at any given point in your code. This includes the path, path params, search params, context, and any other configuration you’ve provided. Ultimately this means that you can navigate to any route in your app with 100% type safety and confidence that your link or navigate call will succeed.
- TanStack Router provides lossless type-inference. It uses countless generic type parameters to enforce and propagate any type information you give it throughout the rest of its API and ultimately your app. No other router offers this level of type safety and developer confidence.

What does all of that mean for you?

- Faster feature development with auto-completion and type hints
- Safer and faster refactors
- Confidence that your code will work as expected

## 1st Class Search Parameters

Search parameters are often an afterthought, treated like a black box of strings (or string) that you can parse and update, but not much else. Existing solutions are **not** type-safe either, adding to the caution that is required to deal with them. Even the most "modern" frameworks and routers leave it up to you to figure out how to manage this state. Sometimes they'll parse the search string into an object for you, or sometimes you're left to do it yourself with `URLSearchParams`.

Let's step back and remember that **search params are the most powerful state manager in your entire application.** They are global, serializable, bookmarkable, and shareable making them the perfect place to store any kind of state that needs to survive a page refresh or a social share.

To live up to that responsibility, search parameters are a first-class citizen in TanStack Router. While still based on standard URLSearchParams, TanStack Router uses a powerful parser/serializer to manage deeper and more complex data structures in your search params, all while keeping them type-safe and easy to work with.

**It's like having `useState` right in the URL!**

Search parameters are:

- Automatically parsed and serialized as JSON
- Validated and typed
- Inherited from parent routes
- Accessible in loaders, components, and hooks
- Easily modified with the useSearch hook, Link, navigate, and router.navigate APIs
- Customizable with a custom search filters and middleware
- Subscribed via fine-grained search param selectors for efficient re-renders

Once you start using TanStack Router's search parameters, you'll wonder how you ever lived without them.

## Built-In Caching and Friendly Data Loading

Data loading is a critical part of any application and while most existing routers offer some form of critical data loading APIs, they often fall short when it comes to caching and data lifecycle management. Existing solutions suffer from a few common problems:

- No caching at all. Data is always fresh, but your users are left waiting for frequently accessed data to load over and over again.
- Overly-aggressive caching. Data is cached for too long, leading to stale data and a poor user experience.
- Blunt invalidation strategies and APIs. Data may be invalidated too often, leading to unnecessary network requests and wasted resources, or you may not have any fine-grained control over when data is invalidated at all.

TanStack Router solves these problems with a two-prong approach to caching and data loading:

### Built-in Cache

TanStack Router provides a light-weight built-in caching layer that works seamlessly with the Router. This caching layer is loosely based on TanStack Query, but with fewer features and a much smaller API surface area. Like TanStack Query, sane but powerful defaults guarantee that your data is cached for reuse, invalidated when necessary, and garbage collected when not in use. It also provides a simple API for invalidating the cache manually when needed.

### Flexible & Powerful Data Lifecycle APIs

TanStack Router is designed with a flexible and powerful data loading API that more easily integrates with existing data fetching libraries like TanStack Query, SWR, Apollo, Relay, or even your own custom data fetching solution. Configurable APIs like `context`, `beforeLoad`, `loaderDeps` and `loader` work in unison to make it easy to define declarative data dependencies, prefetch data, and manage the lifecycle of an external data source with ease.

## Inherited Route Context

TanStack Router's router and route context is a powerful feature that allows you to define context that is specific to a route which is then inherited by all child routes. Even the router and root routes themselves can provide context. Context can be built up both synchronously and asynchronously, and can be used to share data, configuration, or even functions between routes and route configurations. This is especially useful for scenarios like:

- Authentication and Authorization
- Hybrid SSR/CSR data fetching and preloading
- Theming
- Singletons and global utilities
- Curried or partial application across preloading, loading, and rendering stages

Also, what would route context be if it weren't type-safe? TanStack Router's route context is fully type-safe and inferred at zero cost to you.

## File-based and/or Code-Based Routing

TanStack Router supports both file-based and code-based routing at the same time. This flexibility allows you to choose the approach that best fits your project's needs.

TanStack Router's file-based routing approach is uniquely user-facing. Route configuration is generated for you either by the Vite plugin or TanStack Router CLI, leaving the usage of said generated code up to you! This means that you're always in total control of your routes and router, even if you use file-based routing.

## Acknowledgements

TanStack Router builds on concepts and patterns popularized by many other OSS projects, including:

- [TRPC](https://trpc.io/)
- [Remix](https://remix.run)
- [Chicane](https://swan-io.github.io/chicane/)
- [Next.js](https://nextjs.org)

We acknowledge the investment, risk and research that went into their development, but are excited to push the bar they have set even higher.

## Let's go!

Enough overview, there's so much more to do with TanStack Router. Hit that next button and let's get started!

# Quick Start

If you're feeling impatient and prefer to skip all of our wonderful documentation, here is the bare minimum to get going with TanStack Router using both file-based route generation and code-based route configuration:

## Using File-Based Route Generation

File based route generation (through Vite, and other supported bundlers) is the recommended way to use TanStack Router as it provides the best experience, performance, and ergonomics for the least amount of effort.

### Scaffolding Your First TanStack Router Project

```sh
npx create-tsrouter-app@latest my-app --template file-router
```

See [create-tsrouter-app](https://github.com/TanStack/create-tsrouter-app/tree/main/cli/create-tsrouter-app) for more options.

### Manual Setup

Alternatively, you can manually setup the project using the following steps:

#### Install TanStack Router, Vite Plugin, and the Router Devtools

```sh
npm install @tanstack/react-router @tanstack/react-router-devtools
npm install -D @tanstack/router-plugin
# or
pnpm add @tanstack/react-router @tanstack/react-router-devtools
pnpm add -D @tanstack/router-plugin
# or
yarn add @tanstack/react-router @tanstack/react-router-devtools
yarn add -D @tanstack/router-plugin
# or
bun add @tanstack/react-router @tanstack/react-router-devtools
bun add -D @tanstack/router-plugin
# or
deno add npm:@tanstack/react-router npm:@tanstack/router-plugin npm:@tanstack/react-router-devtools
```

#### Configure the Vite Plugin

```tsx
// vite.config.ts
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import { tanstackRouter } from '@tanstack/router-plugin/vite'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    // Please make sure that '@tanstack/router-plugin' is passed before '@vitejs/plugin-react'
    tanstackRouter({
      target: 'react',
      autoCodeSplitting: true,
    }),
    react(),
    // ...,
  ],
})
```

> [!TIP]
> If you are not using Vite, or any of the supported bundlers, you can check out the [TanStack Router CLI](../routing/installation-with-router-cli.md) guide for more info.

Create the following files:

- `src/routes/__root.tsx` (with two '`_`' characters)
- `src/routes/index.tsx`
- `src/routes/about.tsx`
- `src/main.tsx`

#### `src/routes/__root.tsx`

```tsx
import { createRootRoute, Link, Outlet } from '@tanstack/react-router'
import { TanStackRouterDevtools } from '@tanstack/react-router-devtools'

const RootLayout = () => (
  <>
    <div className="p-2 flex gap-2">
      <Link to="/" className="[&.active]:font-bold">
        Home
      </Link>{' '}
      <Link to="/about" className="[&.active]:font-bold">
        About
      </Link>
    </div>
    <hr />
    <Outlet />
    <TanStackRouterDevtools />
  </>
)

export const Route = createRootRoute({ component: RootLayout })
```

#### `src/routes/index.tsx`

```tsx
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/')({
  component: Index,
})

function Index() {
  return (
    <div className="p-2">
      <h3>Welcome Home!</h3>
    </div>
  )
}
```

#### `src/routes/about.tsx`

```tsx
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/about')({
  component: About,
})

function About() {
  return <div className="p-2">Hello from About!</div>
}
```

#### `src/main.tsx`

Regardless of whether you are using the `@tanstack/router-plugin` package and running the `npm run dev`/`npm run build` scripts, or manually running the `tsr watch`/`tsr generate` commands from your package scripts, the route tree file will be generated at `src/routeTree.gen.ts`.

Import the generated route tree and create a new router instance:

```tsx
import { StrictMode } from 'react'
import ReactDOM from 'react-dom/client'
import { RouterProvider, createRouter } from '@tanstack/react-router'

// Import the generated route tree
import { routeTree } from './routeTree.gen'

// Create a new router instance
const router = createRouter({ routeTree })

// Register the router instance for type safety
declare module '@tanstack/react-router' {
  interface Register {
    router: typeof router
  }
}

// Render the app
const rootElement = document.getElementById('root')!
if (!rootElement.innerHTML) {
  const root = ReactDOM.createRoot(rootElement)
  root.render(
    <StrictMode>
      <RouterProvider router={router} />
    </StrictMode>
  )
}
```

If you are working with this pattern you should change the `id` of the root `<div>` on your `index.html` file to `<div id='root'></div>`

## Using Code-Based Route Configuration

> [!IMPORTANT]
> The following example shows how to configure routes using code, and for simplicity's sake is in a single file for this demo. While code-based generation allows you to declare many routes and even the router instance in a single file, we recommend splitting your routes into separate files for better organization and performance as your application grows.

```tsx
import { StrictMode } from 'react'
import ReactDOM from 'react-dom/client'
import { Outlet, RouterProvider, Link, createRouter, createRoute, createRootRoute } from '@tanstack/react-router'
import { TanStackRouterDevtools } from '@tanstack/react-router-devtools'

const rootRoute = createRootRoute({
  component: () => (
    <>
      <div className="p-2 flex gap-2">
        <Link to="/" className="[&.active]:font-bold">
          Home
        </Link>{' '}
        <Link to="/about" className="[&.active]:font-bold">
          About
        </Link>
      </div>
      <hr />
      <Outlet />
      <TanStackRouterDevtools />
    </>
  ),
})

const indexRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: '/',
  component: function Index() {
    return (
      <div className="p-2">
        <h3>Welcome Home!</h3>
      </div>
    )
  },
})

const aboutRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: '/about',
  component: function About() {
    return <div className="p-2">Hello from About!</div>
  },
})

const routeTree = rootRoute.addChildren([indexRoute, aboutRoute])

const router = createRouter({ routeTree })

declare module '@tanstack/react-router' {
  interface Register {
    router: typeof router
  }
}

const rootElement = document.getElementById('app')!
if (!rootElement.innerHTML) {
  const root = ReactDOM.createRoot(rootElement)
  root.render(
    <StrictMode>
      <RouterProvider router={router} />
    </StrictMode>
  )
}
```

If you glossed over these examples or didn't understand something, we don't blame you, because there's so much more to learn to really take advantage of TanStack Router! Let's move on.

# Devtools

> Link, take this sword... I mean Devtools!... to help you on your way!

Wave your hands in the air and shout hooray because TanStack Router comes with dedicated devtools! 🥳

When you begin your TanStack Router journey, you'll want these devtools by your side. They help visualize all of the inner workings of TanStack Router and will likely save you hours of debugging if you find yourself in a pinch!

## Installation

The devtools are a separate package that you need to install:

```sh
npm install @tanstack/react-router-devtools
```

or

```sh
pnpm add @tanstack/react-router-devtools
```

or

```sh
yarn add @tanstack/react-router-devtools
```

or

```sh
bun add @tanstack/react-router-devtools
```

## Import the Devtools

```js
import { TanStackRouterDevtools } from '@tanstack/react-router-devtools'
```

## Using Devtools in production

The Devtools, if imported as `TanStackRouterDevtools` will not be shown in production. If you want to have devtools in an environment with `process.env.NODE_ENV === 'production'`, use instead `TanStackRouterDevtoolsInProd`, which has all the same options:

```tsx
import { TanStackRouterDevtoolsInProd } from '@tanstack/react-router-devtools'
```

## Using inside of the `RouterProvider`

The easiest way for the devtools to work is to render them inside of your root route (or any other route). This will automatically connect the devtools to the router instance.

```tsx
const rootRoute = createRootRoute({
  component: () => (
    <>
      <Outlet />
      <TanStackRouterDevtools />
    </>
  ),
})

const routeTree = rootRoute.addChildren([
  // ... other routes
])

const router = createRouter({
  routeTree,
})

function App() {
  return <RouterProvider router={router} />
}
```

## Manually passing the Router Instance

If rendering the devtools inside of the `RouterProvider` isn't your cup of tea, a `router` prop for the devtools accepts the same `router` instance you pass to the `Router` component. This makes it possible to place the devtools anywhere on the page, not just inside the provider:

```tsx
function App() {
  return (
    <>
      <RouterProvider router={router} />
      <TanStackRouterDevtools router={router} />
    </>
  )
}
```

## Floating Mode

Floating Mode will mount the devtools as a fixed, floating element in your app and provide a toggle in the corner of the screen to show and hide the devtools. This toggle state will be stored and remembered in localStorage across reloads.

Place the following code as high in your React app as you can. The closer it is to the root of the page, the better it will work!

```js
import { TanStackRouterDevtools } from '@tanstack/react-router-devtools'

function App() {
  return (
    <>
      <Router />
      <TanStackRouterDevtools initialIsOpen={false} />
    </>
  )
}
```

### Devtools Options

- `router: Router`
  - The router instance to connect to.
- `initialIsOpen: Boolean`
  - Set this `true` if you want the devtools to default to being open.
- `panelProps: PropsObject`
  - Use this to add props to the panel. For example, you can add `className`, `style` (merge and override default style), etc.
- `closeButtonProps: PropsObject`
  - Use this to add props to the close button. For example, you can add `className`, `style` (merge and override default style), `onClick` (extend default handler), etc.
- `toggleButtonProps: PropsObject`
  - Use this to add props to the toggle button. For example, you can add `className`, `style` (merge and override default style), `onClick` (extend default handler), etc.
- `position?: "top-left" | "top-right" | "bottom-left" | "bottom-right"`
  - Defaults to `bottom-left`.
  - The position of the TanStack Router logo to open and close the devtools panel.
- `shadowDOMTarget?: ShadowRoot`
  - Specifies a Shadow DOM target for the devtools.
  - By default, devtool styles are applied to the `<head>` tag of the main document (light DOM). When a `shadowDOMTarget` is provided, styles will be applied within this Shadow DOM instead.
- `containerElement?: string | any`
  - Use this to render the devtools inside a different type of container element for ally purposes.
  - Any string which corresponds to a valid intrinsic JSX element is allowed.
  - Defaults to 'footer'.

## Fixed Mode

To control the position of the devtools, import the `TanStackRouterDevtoolsPanel`:

```js
import { TanStackRouterDevtoolsPanel } from '@tanstack/react-router-devtools'
```

It can then be attached to provided shadow DOM target:

```js
<TanStackRouterDevtoolsPanel shadowDOMTarget={shadowContainer} router={router} />
```

Click [here](https://tanstack.com/router/latest/docs/framework/react/examples/basic-devtools-panel) to see a live example of this in StackBlitz.

## Embedded Mode

Embedded Mode will embed the devtools as a regular component in your application. You can style it however you'd like after that!

```js
import { TanStackRouterDevtoolsPanel } from '@tanstack/react-router-devtools'

function App() {
  return (
    <>
      <Router router={router} />
      <TanStackRouterDevtoolsPanel router={router} style={styles} className={className} />
    </>
  )
}
```

### DevtoolsPanel Options

- `router: Router`
  - The router instance to connect to.
- `style: StyleObject`
  - The standard React style object used to style a component with inline styles.
- `className: string`
  - The standard React className property used to style a component with classes.
- `isOpen?: boolean`
  - A boolean variable indicating whether the panel is open or closed.
- `setIsOpen?: (isOpen: boolean) => void`
  - A function that toggles the open and close state of the panel.
- `handleDragStart?: (e: any) => void`
  - Handles the opening and closing the devtools panel.
- `shadowDOMTarget?: ShadowRoot`
  - Specifies a Shadow DOM target for the devtools.
  - By default, devtool styles are applied to the `<head>` tag of the main document (light DOM). When a `shadowDOMTarget` is provided, styles will be applied within this Shadow DOM instead.

# Migration from React Router Checklist

**_If your UI is blank, open the console, and you will probably have some errors that read something along the lines of `cannot use 'useNavigate' outside of context` . This means there are React Router api’s that are still imported and referenced that you need to find and remove. The easiest way to make sure you find all React Router imports is to uninstall `react-router-dom` and then you should get typescript errors in your files. Then you will know what to change to a `@tanstack/react-router` import._**

Here is the [example repo](https://github.com/Benanna2019/SickFitsForEveryone/tree/migrate-to-tanstack/router/React-Router)

- [ ] Install Router - `npm i @tanstack/react-router` (see [detailed installation guide](../how-to/install.md))
- [ ] **Optional:** Uninstall React Router to get TypeScript errors on imports.
  - At this point I don’t know if you can do a gradual migration, but it seems likely you could have multiple router providers, not desirable.
  - The api’s between React Router and TanStack Router are very similar and could most likely be handled in a sprint cycle or two if that is your companies way of doing things.
- [ ] Create Routes for each existing React Router route we have
- [ ] Create root route
- [ ] Create router instance
- [ ] Add global module in main.tsx
- [ ] Remove any React Router (`createBrowserRouter` or `BrowserRouter`), `Routes`, and `Route` Components from main.tsx
- [ ] **Optional:** Refactor `render` function for custom setup/providers - The repo referenced above has an example - This was necessary in the case of Supertokens. Supertoken has a specific setup with React Router and a different setup with all other React implementations
- [ ] Set RouterProvider and pass it the router as the prop
- [ ] Replace all instances of React Router `Link` component with `@tanstack/react-router` `Link` component
  - [ ] Add `to` prop with literal path
  - [ ] Add `params` prop, where necessary with params like so `params={{ orderId: order.id }}`
- [ ] Replace all instances of React Router `useNavigate` hook with `@tanstack/react-router` `useNavigate` hook
  - [ ] Set `to` property and `params` property where needed
- [ ] Replace any React Router `Outlet`'s with the `@tanstack/react-router` equivalent
- [ ] If you are using `useSearchParams` hook from React Router, move the search params default value to the validateSearch property on a Route definition.
  - [ ] Instead of using the `useSearchParams` hook, use `@tanstack/react-router` `Link`'s search property to update the search params state
  - [ ] To read search params you can do something like the following
    - `const { page } = useSearch({ from: productPage.fullPath })`
- [ ] If using React Router’s `useParams` hook, update the import to be from `@tanstack/react-router` and set the `from` property to the literal path name where you want to read the params object from
  - So say we have a route with the path name `orders/$orderid`.
  - In the `useParams` hook we would set up our hook like so: `const params = useParams({ from: "/orders/$orderId" })`
  - Then wherever we wanted to access the order id we would get it off of the params object `params.orderId`

# Migration from React Location

Before you begin your journey in migrating from React Location, it's important that you have a good understanding of the [Routing Concepts](../routing/routing-concepts.md) and [Design Decisions](../decisions-on-dx.md) used by TanStack Router.

## Differences between React Location and TanStack Router

React Location and TanStack Router share much of same design decisions concepts, but there are some key differences that you should be aware of.

- React Location uses _generics_ to infer types for routes, while TanStack Router uses _module declaration merging_ to infer types.
- Route configuration in React Location is done using a single array of route definitions, while in TanStack Router, route configuration is done using a tree of route definitions starting with the [root route](../routing/routing-concepts.md#the-root-route).
- [File-based routing](../routing/file-based-routing.md) is the recommended way to define routes in TanStack Router, while React Location only allows you to define routes in a single file using a code-based approach.
  - TanStack Router does support a [code-based approach](../routing/code-based-routing.md) to defining routes, but it is not recommended for most use cases. You can read more about why, over here: [why is file-based routing the preferred way to define routes?](../decisions-on-dx.md#3-why-is-file-based-routing-the-preferred-way-to-define-routes)

## Migration guide

In this guide we'll go over the process of migrating the [React Location Basic example](https://github.com/TanStack/router/tree/react-location/examples/basic) over to TanStack Router using file-based routing, with the end goal of having the same functionality as the original example (styling and other non-routing related code will be omitted).

> [!TIP]
> To use a code-based approach for defining your routes, you can read the [code-based Routing](../routing/code-based-routing.md) guide.

### Step 1: Swap over to TanStack Router's dependencies

First, we need to install the dependencies for TanStack Router. For detailed installation instructions, see our [How to Install TanStack Router](../how-to/install.md) guide.

```sh
npm install @tanstack/react-router @tanstack/router-devtools
```

And remove the React Location dependencies.

```sh
npm uninstall @tanstack/react-location @tanstack/react-location-devtools
```

### Step 2: Use the file-based routing watcher

If your project uses Vite (or one of the supported bundlers), you can use the TanStack Router plugin to watch for changes in your routes files and automatically update the routes configuration.

Installation of the Vite plugin:

```sh
npm install -D @tanstack/router-plugin
```

And add it to your `vite.config.js`:

```js
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import { tanstackRouter } from '@tanstack/router-plugin/vite'

export default defineConfig({
  // ...
  plugins: [tanstackRouter(), react()],
})
```

However, if your application does not use Vite, you use one of our other [supported bundlers](../routing/file-based-routing.md#getting-started-with-file-based-routing), or you can use the `@tanstack/router-cli` package to watch for changes in your routes files and automatically update the routes configuration.

### Step 3: Add the file-based configuration file to your project

Create a `tsr.config.json` file in the root of your project with the following content:

```json
{
  "routesDirectory": "./src/routes",
  "generatedRouteTree": "./src/routeTree.gen.ts"
}
```

You can find the full list of options for the `tsr.config.json` file [here](../../../api/file-based-routing.md).

### Step 4: Create the routes directory

Create a `routes` directory in the `src` directory of your project.

```sh
mkdir src/routes
```

### Step 5: Create the root route file

```tsx
// src/routes/__root.tsx
import { createRootRoute, Outlet, Link } from '@tanstack/react-router'
import { TanStackRouterDevtools } from '@tanstack/router-devtools'

export const Route = createRootRoute({
  component: () => {
    return (
      <>
        <div>
          <Link to="/" activeOptions={{ exact: true }}>
            Home
          </Link>
          <Link to="/posts">Posts</Link>
        </div>
        <hr />
        <Outlet />
        <TanStackRouterDevtools />
      </>
    )
  },
})
```

### Step 6: Create the index route file

```tsx
// src/routes/index.tsx
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/')({
  component: Index,
})
```

> You will need to move any related components and logic needed for the index route from the `src/index.tsx` file to the `src/routes/index.tsx` file.

### Step 7: Create the posts route file

```tsx
// src/routes/posts.tsx
import { createFileRoute, Link, Outlet } from '@tanstack/react-router'

export const Route = createFileRoute('/posts')({
  component: Posts,
  loader: async () => {
    const posts = await fetchPosts()
    return {
      posts,
    }
  },
})

function Posts() {
  const { posts } = Route.useLoaderData()
  return (
    <div>
      <nav>
        {posts.map((post) => (
          <Link key={post.id} to={`/posts/$postId`} params={{ postId: post.id }}>
            {post.title}
          </Link>
        ))}
      </nav>
      <Outlet />
    </div>
  )
}
```

> You will need to move any related components and logic needed for the posts route from the `src/index.tsx` file to the `src/routes/posts.tsx` file.

### Step 8: Create the posts index route file

```tsx
// src/routes/posts.index.tsx
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/posts/')({
  component: PostsIndex,
})
```

> You will need to move any related components and logic needed for the posts index route from the `src/index.tsx` file to the `src/routes/posts.index.tsx` file.

### Step 9: Create the posts id route file

```tsx
// src/routes/posts.$postId.tsx
import { createFileRoute } from '@tanstack/react-router'

export const Route = createFileRoute('/posts/$postId')({
  component: PostsId,
  loader: async ({ params: { postId } }) => {
    const post = await fetchPost(postId)
    return {
      post,
    }
  },
})

function PostsId() {
  const { post } = Route.useLoaderData()
  // ...
}
```

> You will need to move any related components and logic needed for the posts id route from the `src/index.tsx` file to the `src/routes/posts.$postId.tsx` file.

### Step 10: Generate the route tree

If you are using one of the supported bundlers, the route tree will be generated automatically when you run the dev script.

If you are not using one of the supported bundlers, you can generate the route tree by running the following command:

```sh
npx tsr generate
```

### Step 11: Update the main entry file to render the Router

Once you've generated the route-tree, you can then update the `src/index.tsx` file to create the router instance and render it.

```tsx
// src/index.tsx
import React from 'react'
import ReactDOM from 'react-dom'
import { createRouter, RouterProvider } from '@tanstack/react-router'

// Import the generated route tree
import { routeTree } from './routeTree.gen'

// Create a new router instance
const router = createRouter({ routeTree })

// Register the router instance for type safety
declare module '@tanstack/react-router' {
  interface Register {
    router: typeof router
  }
}

const domElementId = 'root' // Assuming you have a root element with the id 'root'

// Render the app
const rootElement = document.getElementById(domElementId)
if (!rootElement) {
  throw new Error(`Element with id ${domElementId} not found`)
}

ReactDOM.createRoot(rootElement).render(
  <React.StrictMode>
    <RouterProvider router={router} />
  </React.StrictMode>
)
```

### Finished!

You should now have successfully migrated your application from React Location to TanStack Router using file-based routing.

React Location also has a few more features that you might be using in your application. Here are some guides to help you migrate those features:

- [Search params](../guide/search-params.md)
- [Data loading](../guide/data-loading.md)
- [History types](../guide/history-types.md)
- [Wildcard / Splat / Catch-all routes](../routing/routing-concepts.md#splat--catch-all-routes)
- [Authenticated routes](../guide/authenticated-routes.md)

TanStack Router also has a few more features that you might want to explore:

- [Router Context](../guide/router-context.md)
- [Preloading](../guide/preloading.md)
- [Pathless Layout Routes](../routing/routing-concepts.md#pathless-layout-routes)
- [Route masking](../guide/route-masking.md)
- [SSR](../guide/ssr.md)
- ... and more!

If you are facing any issues or have any questions, feel free to ask for help in the TanStack Discord.

# Frequently Asked Questions

Welcome to the TanStack Router FAQ! Here you'll find answers to common questions about the TanStack Router. If you have a question that isn't answered here, please feel free to ask in the [TanStack Discord](https://tlinz.com/discord).

## Should I commit my `routeTree.gen.ts` file into git?

Yes! Although the route tree file (i.e., `routeTree.gen.ts`) is generated by TanStack Router, it is essentially part of your application’s runtime, not a build artifact. The route tree file is a critical part of your application’s source code, and it is used by TanStack Router to build your application’s routes at runtime.

You should commit this file into git so that other developers can use it to build your application.

## Can I conditionally render the Root Route component?

No, the root route is always rendered as it is the entry point of your application.

If you need to conditionally render a route's component, this usually means that the page content needs to be different based on some condition (e.g. user authentication). For this use case, you should use a [Layout Route](../routing/routing-concepts.md#layout-routes) or a [Pathless Layout Route](../routing/routing-concepts.md#pathless-layout-routes) to conditionally render the content.

You can restrict access to these routes using a conditional check in the `beforeLoad` function of the route.

<details>
<summary>What does this look like?</summary>

```tsx
// src/routes/_pathless-layout.tsx
import { createFileRoute, Outlet } from '@tanstack/react-router'
import { isAuthenticated } from '../utils/auth'

export const Route = createFileRoute('/_pathless-layout', {
  beforeLoad: async () => {
    // Check if the user is authenticated
    const authed = await isAuthenticated()
    if (!authed) {
      // Redirect the user to the login page
      return '/login'
    }
  },
  component: PathlessLayoutRouteComponent,
  // ...
})

function PathlessLayoutRouteComponent() {
  return (
    <div>
      <h1>You are authed</h1>
      <Outlet />
    </div>
  )
}
```

</details>

</@tanstack/react-router_setup-and-architecture>

<!-- /vibe-rules Integration -->
