@props(['project'])

<a href="{{ route('projects.show', $project) }}" class="related-card group block rounded-2xl border border-gray-200 bg-white p-6 dark:border-brand-700 dark:bg-brand-950/50">
    <div class="mb-3 flex items-start justify-between">
        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl bg-brand-600/10">
            <x-svg-icon name="folder" class="h-4 w-4 text-brand-600" />
        </div>
        @if($project->is_featured)
            <span class="text-[10px] font-bold text-brand-600">Featured</span>
        @endif
    </div>
    <h3 class="mb-2 font-bold transition-colors group-hover:text-brand-600">{{ $project->title }}</h3>
    <p class="line-clamp-2 text-sm leading-relaxed text-gray-500">{{ $project->description }}</p>
</a>
