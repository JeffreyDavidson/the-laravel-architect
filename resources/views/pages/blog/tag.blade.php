<x-layouts.site :seo-source="$seoSource ?? null" :structured-data="$structuredData ?? []">
    <x-blog.taxonomy-archive
        eyebrow="Tagged note"
        :title="$tag->name"
        :posts="$posts"
        empty-message="No posts with this tag yet."
    />
</x-layouts.site>
