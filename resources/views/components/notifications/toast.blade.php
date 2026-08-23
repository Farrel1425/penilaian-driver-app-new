@php
    $notifications = collect([
        ['type' => 'success', 'message' => session('status')],
        ['type' => 'error', 'message' => session('error')],
        ['type' => 'warning', 'message' => session('warning')],
        ['type' => 'info', 'message' => session('info')],
    ])->filter(fn (array $notification) => filled($notification['message']))->values();

    if ($errors->any()) {
        $notifications->push(['type' => 'error', 'message' => $errors->first()]);
    }
@endphp

@if ($notifications->isNotEmpty())
    <section class="app-toast-stack" aria-live="polite" aria-atomic="true" data-toast-stack>
        @foreach ($notifications as $notification)
            <div class="app-toast app-toast-{{ $notification['type'] }}" role="status" data-toast>
                <span class="app-toast-icon" aria-hidden="true">
                    @if ($notification['type'] === 'success')
                        <x-lucide-circle-check />
                    @elseif ($notification['type'] === 'error')
                        <x-lucide-circle-x />
                    @elseif ($notification['type'] === 'warning')
                        <x-lucide-triangle-alert />
                    @else
                        <x-lucide-info />
                    @endif
                </span>
                <p>{{ $notification['message'] }}</p>
                <button class="app-toast-close" type="button" data-toast-dismiss aria-label="Tutup notifikasi">
                    <x-lucide-x aria-hidden="true" />
                </button>
            </div>
        @endforeach
    </section>
@endif
