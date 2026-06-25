<x-layouts::app title="Notifications">
    <div class="flex h-full w-full flex-1 flex-col gap-5 rounded-xl">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Notifications</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">Vos alertes et rappels — par e-mail et dans l'application.</p>
            </div>
            @if (auth()->user()->unreadNotifications()->count() > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit"
                        class="rounded-lg bg-emerald-700 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-800">
                        Tout marquer comme lu
                    </button>
                </form>
            @endif
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-950/40">
                <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ session('success') }}</p>
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($notifications as $notification)
                    <li class="flex items-start gap-3 px-5 py-4 {{ is_null($notification->read_at) ? 'bg-emerald-50/50 dark:bg-emerald-950/20' : '' }}">
                        <div class="mt-1.5">
                            @if (is_null($notification->read_at))
                                <span class="block h-2.5 w-2.5 rounded-full bg-emerald-600" title="Non lue"></span>
                            @else
                                <span class="block h-2.5 w-2.5 rounded-full bg-slate-300 dark:bg-slate-600" title="Lue"></span>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('notifications.read', $notification->id) }}"
                               class="block text-sm text-slate-800 hover:underline dark:text-slate-100">
                                {{ $notification->data['message'] ?? 'Notification' }}
                            </a>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                {{ $notification->created_at->translatedFormat('d/m/Y à H:i') }} — {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-400 hover:text-rose-600" title="Supprimer" aria-label="Supprimer">
                                <flux:icon name="trash" class="size-4" />
                            </button>
                        </form>
                    </li>
                @empty
                    <li class="px-5 py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                        Vous n'avez aucune notification.
                    </li>
                @endforelse
            </ul>
        </div>

        <div class="mt-2">{{ $notifications->links() }}</div>
    </div>
</x-layouts::app>
