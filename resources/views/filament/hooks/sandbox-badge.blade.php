@if(auth()->check() && auth()->user()?->company?->is_sandbox)
    <div class="px-3 py-1 bg-amber-500 text-white text-xs font-bold rounded-full animate-pulse">
        SANDBOX
    </div>
@endif
