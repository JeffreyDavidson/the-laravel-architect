<x-layouts.site :page-meta="$pageMeta">
    <x-blog.taxonomy-archive
        eyebrow="Tagged note"
        :title="$tag->name"
        :posts="$posts"
        empty-message="No posts with this tag yet."
    />
</x-layouts.site>
