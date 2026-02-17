<div>
    <div class="rounded-xl border border-gray-950/5 dark:border-white/10 bg-white dark:bg-gray-900 p-5">
        <h3 class="text-sm font-semibold text-gray-950 dark:text-white mb-4 flex items-center gap-2">
            <span class="w-1 h-4 rounded-full bg-[#4A7FBF]"></span>
            Quick Actions
        </h3>
        <div class="grid grid-cols-2 gap-2">
            <a href="{{ route('filament.admin.resources.posts.create') }}"
               class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors border border-transparent hover:border-gray-200 dark:hover:border-white/10">
                <span class="text-lg">✍️</span>
                <span>New Post</span>
            </a>
            <a href="{{ route('filament.admin.resources.projects.create') }}"
               class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors border border-transparent hover:border-gray-200 dark:hover:border-white/10">
                <span class="text-lg">🚀</span>
                <span>New Project</span>
            </a>
            <a href="{{ route('filament.admin.resources.podcasts.create') }}"
               class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors border border-transparent hover:border-gray-200 dark:hover:border-white/10">
                <span class="text-lg">🎙️</span>
                <span>New Podcast</span>
            </a>
            <a href="/" target="_blank"
               class="flex items-center gap-2 rounded-lg px-3 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors border border-transparent hover:border-gray-200 dark:hover:border-white/10">
                <span class="text-lg">🌐</span>
                <span>View Site</span>
            </a>
        </div>
    </div>
</div>
