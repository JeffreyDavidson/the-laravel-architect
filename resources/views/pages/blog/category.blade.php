<x-layouts.site :page-meta="$pageMeta">
    <x-blog.taxonomy-archive
        eyebrow="Filed under"
        :title="$category->name"
        :description="$category->description"
        :posts="$posts"
        empty-message="No posts in this category yet."
        :show-category="false"
    />
</x-layouts.site>
