@php
    $user = auth()->user();
    $unread = $user ? $user->unreadNotifications()->count() : 0;
    $recentes = $user ? $user->notifications()->latest()->take(6)->get() : collect();
@endphp

<flux:dropdown position="bottom" align="end">
    <flux:button variant="ghost" class="relative !h-10" icon="bell" aria-label="Notifications">
        @if ($unread > 0)
            <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-600 px-1 text-[10px] font-semibold text-white">
                {{ $unread > 9 ? '9+' : $unread }}
            </span>
        @endif
    </flux:button>

    <flux:menu class="w-80">
        <div class="flex items-center justify-between px-3 py-2">
            <flux:heading size="sm">Notifications</flux:heading>
            @if ($unread > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-xs font-medium text-emerald-700 hover:underline dark:text-emerald-400">
                        Tout marquer comme lu
                    </button>
                </form>
            @endif
        </div>

        <flux:menu.separator />

        @forelse ($recentes as $notification)
            <a href="{{ route('notifications.read', $notification->id) }}"
               class="block px-3 py-2.5 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800 {{ is_null($notification->read_at) ? 'bg-emerald-50/60 dark:bg-emerald-950/30' : '' }}">
                <p class="text-zinc-800 dark:text-zinc-100">{{ $notification->data['message'] ?? 'Notification' }}</p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ $notification->created_at->diffForHumans() }}</p>
            </a>
        @empty
            <p class="px-3 py-6 text-center text-sm text-zinc-500 dark:text-zinc-400">Aucune notification.</p>
        @endforelse

        <flux:menu.separator />

        <flux:menu.item :href="route('notifications.index')" icon="inbox" wire:navigate>
            Voir toutes les notifications
        </flux:menu.item>
    </flux:menu>
</flux:dropdown>
