import { Logo } from './ui/logo'

export function Header() {
  return (
    <header className="bg-header p-4 flex gap-2">
      <div className="flex gap-1 items-center text-white font-medium italic text-xl">
        <Logo />
      </div>
    </header>
  )
}
