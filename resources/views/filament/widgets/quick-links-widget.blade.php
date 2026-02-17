<x-filament-widgets::widget>
    <div class="rounded-xl border border-gray-950/5 dark:border-white/10 bg-white dark:bg-gray-900 p-5">
        <h3 class="text-sm font-semibold text-gray-950 dark:text-white mb-3 flex items-center gap-2">
            <span class="w-1 h-4 rounded-full bg-[#4A7FBF]"></span>
            Quick Actions
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-2">
            <a href="{{ route('filament.admin.resources.posts.create') }}"
               class="group flex items-center gap-3 rounded-lg border border-gray-950/5 dark:border-white/5 px-4 py-3 text-sm transition-all hover:border-[#4A7FBF]/30 hover:bg-[#4A7FBF]/5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#4A7FBF]/10 text-[#4A7FBF] group-hover:bg-[#4A7FBF]/20 transition-colors flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </span>
                <div class="min-w-0 text-left">
                    <span class="text-gray-700 dark:text-gray-300 font-medium block leading-tight">Write a Post</span>
                    <span class="text-xs text-gray-400 dark:text-gray-500 leading-tight">New blog content</span>
                </div>
            </a>
            <a href="{{ route('filament.admin.resources.projects.create') }}"
               class="group flex items-center gap-3 rounded-lg border border-gray-950/5 dark:border-white/5 px-4 py-3 text-sm transition-all hover:border-[#c74b7a]/30 hover:bg-[#c74b7a]/5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#c74b7a]/10 text-[#c74b7a] group-hover:bg-[#c74b7a]/20 transition-colors flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                </span>
                <div class="min-w-0 text-left">
                    <span class="text-gray-700 dark:text-gray-300 font-medium block leading-tight">Add Project</span>
                    <span class="text-xs text-gray-400 dark:text-gray-500 leading-tight">Showcase work</span>
                </div>
            </a>
            <a href="{{ route('filament.admin.resources.podcasts.create') }}"
               class="group flex items-center gap-3 rounded-lg border border-gray-950/5 dark:border-white/5 px-4 py-3 text-sm transition-all hover:border-emerald-500/30 hover:bg-emerald-500/5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-500 group-hover:bg-emerald-500/20 transition-colors flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path></svg>
                </span>
                <div class="min-w-0 text-left">
                    <span class="text-gray-700 dark:text-gray-300 font-medium block leading-tight">New Podcast</span>
                    <span class="text-xs text-gray-400 dark:text-gray-500 leading-tight">Start a show</span>
                </div>
            </a>
            <a href="/" target="_blank"
               class="group flex items-center gap-3 rounded-lg border border-gray-950/5 dark:border-white/5 px-4 py-3 text-sm transition-all hover:border-amber-500/30 hover:bg-amber-500/5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-500/10 text-amber-500 group-hover:bg-amber-500/20 transition-colors flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </span>
                <div class="min-w-0 text-left">
                    <span class="text-gray-700 dark:text-gray-300 font-medium block leading-tight">View Site</span>
                    <span class="text-xs text-gray-400 dark:text-gray-500 leading-tight">thelaravelarchitect.com</span>
                </div>
            </a>
        </div>
    </div>
</x-filament-widgets::widget>
