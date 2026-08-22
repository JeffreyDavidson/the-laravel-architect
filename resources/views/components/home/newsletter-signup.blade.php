<div {{ $attributes->merge(['class' => 'newsletter-card relative overflow-hidden rounded-2xl border border-brand-200 bg-brand-50 p-6 shadow-sm dark:border-brand-800/50 dark:bg-brand-900/50 dark:shadow-none sm:p-10']) }}>
    <div class="pointer-events-none absolute -right-16 -top-16 h-40 w-40 rounded-full bg-brand-400/20 blur-3xl dark:bg-brand-300/10" aria-hidden="true"></div>
    <svg class="mx-auto mb-4 h-10 w-10 text-brand-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
    </svg>
    <h3 class="mb-3 text-2xl font-semibold text-gray-900 dark:text-white">Get Laravel tips in your inbox</h3>
    <p class="mb-6 text-gray-600 dark:text-gray-400">
        A weekly-ish newsletter with practical tips, tutorials, and thoughts on building better Laravel apps. No spam, unsubscribe anytime.
    </p>
    @if(session('newsletter_success'))
    <div class="mx-auto mb-4 max-w-md rounded-lg border border-green-500/30 bg-green-500/10 p-3 text-sm text-green-400">
        {{ session('newsletter_success') }}
    </div>
    @endif
    @error('email')
    <div class="mx-auto mb-4 max-w-md rounded-lg border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-400">
        {{ $message }}
    </div>
    @enderror
    <form action="{{ route('newsletter.subscribe') }}" method="POST" class="mx-auto flex max-w-md flex-col gap-3 sm:flex-row">
        @csrf
        <div class="absolute -left-[9999px] -top-[9999px]" aria-hidden="true">
            <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
        </div>
        <input type="email" name="email" placeholder="you@example.com" required
            class="newsletter-input min-w-0 flex-1 rounded-lg border border-brand-200 bg-white px-4 py-3 text-base text-gray-900 shadow-sm transition-all placeholder:text-gray-400 dark:border-brand-700/50 dark:bg-brand-800 dark:text-white dark:shadow-none dark:placeholder:text-gray-500 sm:text-sm">
        <button type="submit" class="glow-btn rounded-lg bg-brand-600 px-6 py-3 text-base font-semibold text-white transition-all hover:bg-brand-500 sm:text-sm">
            Subscribe
        </button>
    </form>
    <p class="mx-auto mt-3 max-w-md text-xs text-gray-600 dark:text-gray-400">
        Confirmation is required. See the <a href="{{ route('privacy') }}" class="underline decoration-gray-300 underline-offset-2 transition-colors hover:text-brand-600 dark:decoration-gray-700 dark:hover:text-brand-300">privacy notice</a> for how your email is handled.
    </p>
</div>
