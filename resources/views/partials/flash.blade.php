@php
    $flashes = collect([
        ['type' => 'success', 'msg' => session('success')],
        ['type' => 'error', 'msg' => session('error')],
    ])->filter(fn ($f) => filled($f['msg']));
@endphp

@if ($flashes->isNotEmpty())
    <div class="fixed top-4 right-4 z-[70] space-y-2 w-[calc(100%-2rem)] max-w-sm">
        @foreach ($flashes as $f)
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition.opacity.duration.300ms
                 class="anim-slide-in flex items-start gap-3 rounded-2xl px-4 py-3 shadow-xl text-white {{ $f['type'] === 'success' ? 'bg-emerald-600' : 'bg-rose-600' }}">
                <span class="text-lg leading-none mt-0.5">{{ $f['type'] === 'success' ? '✅' : '⚠️' }}</span>
                <p class="flex-1 text-sm font-medium">{{ $f['msg'] }}</p>
                <button type="button" @click="show = false" class="text-white/80 hover:text-white text-xl leading-none" aria-label="বন্ধ করুন">&times;</button>
            </div>
        @endforeach
    </div>
@endif
