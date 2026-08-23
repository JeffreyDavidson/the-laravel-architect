@props(['project'])

<a href="{{ route('projects.show', $project) }}" class="related-card group flex h-full flex-col border-b border-gray-200 py-6 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-400 dark:border-[#1e2a3a]">
    <div class="mb-3 flex items-start justify-between">
        <span class="font-mono text-xs uppercase tracking-[0.16em] text-gray-500">Build note</span>
        @if($project->is_featured)
            <span class="text-xs font-bold uppercase tracking-wider text-brand-600">Featured</span>
        @endif
    </div>
    <h3 class="mb-2 font-bold transition-colors group-hover:text-brand-600">{{ $project->title }}</h3>
    <p class="mb-5 text-sm leading-relaxed text-gray-500">{{ $project->description }}</p>
    <span class="mt-auto inline-flex items-center gap-2 text-sm font-semibold text-brand-600">
        Read build notes
        <x-svg-icon name="arrow-long-right" class="h-4 w-4 transition-transform group-hover:translate-x-1" />
    </span>
</a>
