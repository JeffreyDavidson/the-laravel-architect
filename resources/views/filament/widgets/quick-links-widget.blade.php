<div>
    <div class="rounded-xl border border-gray-950/5 dark:border-white/10 bg-white dark:bg-gray-900 p-5 h-full">
        <h3 class="text-sm font-semibold text-gray-950 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-1 h-4 rounded-full bg-[#4A7FBF]"></span>
            Quick Actions
        </h3>
        <div class="space-y-1.5">
            <a href="{{ route('filament.admin.resources.posts.create') }}"
               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-all hover:bg-gray-50 dark:hover:bg-white/5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#4A7FBF]/10 text-[#4A7FBF] group-hover:bg-[#4A7FBF]/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </span>
                <div>
                    <span class="text-gray-700 dark:text-gray-300 font-medium">Write a Post</span>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Create new blog content</p>
                </div>
                <span class="ml-auto text-gray-300 dark:text-gray-600 group-hover:text-[#4A7FBF] transition-colors">→</span>
            </a>
            <a href="{{ route('filament.admin.resources.projects.create') }}"
               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-all hover:bg-gray-50 dark:hover:bg-white/5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#c74b7a]/10 text-[#c74b7a] group-hover:bg-[#c74b7a]/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                </span>
                <div>
                    <span class="text-gray-700 dark:text-gray-300 font-medium">Add Project</span>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Showcase your work</p>
                </div>
                <span class="ml-auto text-gray-300 dark:text-gray-600 group-hover:text-[#c74b7a] transition-colors">→</span>
            </a>
            <a href="{{ route('filament.admin.resources.podcasts.create') }}"
               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-all hover:bg-gray-50 dark:hover:bg-white/5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-500 group-hover:bg-emerald-500/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path></svg>
                </span>
                <div>
                    <span class="text-gray-700 dark:text-gray-300 font-medium">New Podcast</span>
                    <p class="text-xs text-gray-400 dark:text-gray-500">Start a new show</p>
                </div>
                <span class="ml-auto text-gray-300 dark:text-gray-600 group-hover:text-emerald-500 transition-colors">→</span>
            </a>
            <div class="border-t border-gray-950/5 dark:border-white/5 my-2"></div>
            <a href="/" target="_blank"
               class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm transition-all hover:bg-gray-50 dark:hover:bg-white/5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-gray-400 group-hover:bg-gray-200 dark:group-hover:bg-white/15 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </span>
                <div>
                    <span class="text-gray-700 dark:text-gray-300 font-medium">View Live Site</span>
                    <p class="text-xs text-gray-400 dark:text-gray-500">thelaravelarchitect.com</p>
                </div>
                <span class="ml-auto text-gray-300 dark:text-gray-600 group-hover:text-gray-500 transition-colors">↗</span>
            </a>
        </div>
    </div>
</div>
