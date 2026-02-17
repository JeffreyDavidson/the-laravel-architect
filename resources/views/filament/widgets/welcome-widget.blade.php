<div>
    <div class="relative overflow-hidden rounded-xl border border-[#1e2a3a] bg-[#161b22] p-6">
        {{-- Gradient accent top --}}
        <div class="absolute top-0 left-0 right-0 h-[2px]" style="background: linear-gradient(90deg, #4A7FBF, #c74b7a);"></div>

        {{-- Subtle glow orbs --}}
        <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full opacity-[0.06]" style="background: radial-gradient(circle, #4A7FBF, transparent);"></div>
        <div class="absolute -bottom-16 -left-16 w-32 h-32 rounded-full opacity-[0.04]" style="background: radial-gradient(circle, #c74b7a, transparent);"></div>

        <div class="relative">
            {{-- Header --}}
            <div class="flex items-start justify-between mb-6">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-mono text-[#4A7FBF]/60">$ php artisan dashboard</span>
                        <span class="inline-block w-1.5 h-4 bg-[#4A7FBF] animate-pulse rounded-sm"></span>
                    </div>
                    <h2 class="text-xl font-bold text-white">Welcome back, Jeffrey</h2>
                    <p class="text-sm text-gray-400 mt-1">Here's what's happening with The Laravel Architect.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Live
                    </span>
                </div>
            </div>

            {{-- Stats grid --}}
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <a href="{{ route('filament.admin.resources.posts.index') }}" class="group rounded-lg border border-[#1e2a3a] bg-[#0d1117] p-4 transition-all hover:border-[#4A7FBF]/30 hover:bg-[#4A7FBF]/5">
                    <div class="text-2xl font-bold text-white group-hover:text-[#4A7FBF] transition-colors">{{ $posts }}</div>
                    <div class="text-xs text-gray-500 mt-1">Blog Posts</div>
                </a>
                <a href="{{ route('filament.admin.resources.projects.index') }}" class="group rounded-lg border border-[#1e2a3a] bg-[#0d1117] p-4 transition-all hover:border-[#4A7FBF]/30 hover:bg-[#4A7FBF]/5">
                    <div class="text-2xl font-bold text-white group-hover:text-[#4A7FBF] transition-colors">{{ $projects }}</div>
                    <div class="text-xs text-gray-500 mt-1">Projects</div>
                </a>
                <a href="{{ route('filament.admin.resources.subscribers.index') }}" class="group rounded-lg border border-[#1e2a3a] bg-[#0d1117] p-4 transition-all hover:border-[#4A7FBF]/30 hover:bg-[#4A7FBF]/5">
                    <div class="text-2xl font-bold text-white group-hover:text-[#4A7FBF] transition-colors">{{ $subscribers }}</div>
                    <div class="text-xs text-gray-500 mt-1">Subscribers</div>
                </a>
                <a href="{{ route('filament.admin.resources.videos.index') }}" class="group rounded-lg border border-[#1e2a3a] bg-[#0d1117] p-4 transition-all hover:border-[#4A7FBF]/30 hover:bg-[#4A7FBF]/5">
                    <div class="text-2xl font-bold text-white group-hover:text-[#4A7FBF] transition-colors">{{ $videos }}</div>
                    <div class="text-xs text-gray-500 mt-1">Videos</div>
                </a>
                @if($pendingTestimonials > 0)
                <a href="{{ route('filament.admin.resources.testimonials.index') }}" class="group rounded-lg border border-amber-500/20 bg-amber-500/5 p-4 transition-all hover:border-amber-500/40 hover:bg-amber-500/10">
                    <div class="text-2xl font-bold text-amber-400">{{ $pendingTestimonials }}</div>
                    <div class="text-xs text-amber-500/70 mt-1">Pending Reviews</div>
                </a>
                @else
                <div class="rounded-lg border border-[#1e2a3a] bg-[#0d1117] p-4">
                    <div class="text-2xl font-bold text-white">✓</div>
                    <div class="text-xs text-gray-500 mt-1">All Reviewed</div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
