<div>
    <div class="relative overflow-hidden rounded-xl border border-gray-950/5 dark:border-white/10 bg-white dark:bg-gray-900 p-6">
        {{-- Gradient accent top --}}
        <div class="absolute top-0 left-0 right-0 h-[2px]" style="background: linear-gradient(90deg, #4A7FBF, #c74b7a);"></div>

        <div class="relative">
            {{-- Top row: greeting + live badge --}}
            <div class="flex items-center justify-between mb-5">
                <div>
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="text-xs font-mono text-gray-400 dark:text-gray-500">$ php artisan dashboard</span>
                        <span class="inline-block w-1.5 h-3.5 bg-[#4A7FBF] animate-pulse rounded-sm"></span>
                    </div>
                    <h2 class="text-lg font-bold text-gray-950 dark:text-white">Welcome back, Jeffrey</h2>
                </div>
                <a href="/" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 transition-colors">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Site Live
                </a>
            </div>

            {{-- Stats row --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <a href="{{ route('filament.admin.resources.posts.index') }}" class="group relative overflow-hidden rounded-lg border border-gray-950/5 dark:border-white/10 bg-gray-50 dark:bg-white/5 p-4 transition-all hover:border-[#4A7FBF]/30 hover:shadow-sm">
                    <div class="absolute top-0 left-0 w-full h-0.5 bg-[#4A7FBF] scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-gray-950 dark:text-white">{{ $posts }}</span>
                        <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wide">Posts</span>
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        @php $published = \App\Models\Post::where('status', 'published')->count(); @endphp
                        {{ $published }} published
                    </div>
                </a>
                <a href="{{ route('filament.admin.resources.projects.index') }}" class="group relative overflow-hidden rounded-lg border border-gray-950/5 dark:border-white/10 bg-gray-50 dark:bg-white/5 p-4 transition-all hover:border-[#c74b7a]/30 hover:shadow-sm">
                    <div class="absolute top-0 left-0 w-full h-0.5 bg-[#c74b7a] scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-gray-950 dark:text-white">{{ $projects }}</span>
                        <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wide">Projects</span>
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        @php $featured = \App\Models\Project::where('is_featured', true)->count(); @endphp
                        {{ $featured }} featured
                    </div>
                </a>
                <a href="{{ route('filament.admin.resources.subscribers.index') }}" class="group relative overflow-hidden rounded-lg border border-gray-950/5 dark:border-white/10 bg-gray-50 dark:bg-white/5 p-4 transition-all hover:border-emerald-500/30 hover:shadow-sm">
                    <div class="absolute top-0 left-0 w-full h-0.5 bg-emerald-500 scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-gray-950 dark:text-white">{{ $subscribers }}</span>
                        <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wide">Subscribers</span>
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Newsletter</div>
                </a>
                <a href="{{ route('filament.admin.resources.videos.index') }}" class="group relative overflow-hidden rounded-lg border border-gray-950/5 dark:border-white/10 bg-gray-50 dark:bg-white/5 p-4 transition-all hover:border-amber-500/30 hover:shadow-sm">
                    <div class="absolute top-0 left-0 w-full h-0.5 bg-amber-500 scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-gray-950 dark:text-white">{{ $videos }}</span>
                        <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wide">Videos</span>
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">YouTube</div>
                </a>
            </div>

            {{-- Alert bar for pending items --}}
            @if($pendingTestimonials > 0)
            <a href="{{ route('filament.admin.resources.testimonials.index') }}" class="mt-3 flex items-center gap-2 rounded-lg border border-amber-500/20 bg-amber-500/5 px-4 py-2.5 text-sm hover:bg-amber-500/10 transition-colors">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-500/20 text-xs font-bold text-amber-500">{{ $pendingTestimonials }}</span>
                <span class="text-amber-600 dark:text-amber-400 font-medium">{{ $pendingTestimonials === 1 ? 'testimonial' : 'testimonials' }} pending review</span>
                <span class="ml-auto text-amber-500/50">→</span>
            </a>
            @endif
        </div>
    </div>
</div>
