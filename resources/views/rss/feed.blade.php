{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>The Laravel Architect</title>
        <link>{{ url('/') }}</link>
        <description>Deep dives into Laravel, PHP, architecture patterns, and the craft of building modern web applications.</description>
        <language>en-us</language>
        <lastBuildDate>{{ $posts->first()?->published_at?->toRssString() ?? now()->toRssString() }}</lastBuildDate>
        <atom:link href="{{ url('/rss') }}" rel="self" type="application/rss+xml" />
        @foreach($posts as $post)
        <item>
            <title>{{ htmlspecialchars($post->title, ENT_XML1) }}</title>
            <link>{{ route('blog.show', $post) }}</link>
            <guid isPermaLink="true">{{ route('blog.show', $post) }}</guid>
            <description>{{ htmlspecialchars($post->excerpt ?? '', ENT_XML1) }}</description>
            <pubDate>{{ $post->published_at->toRssString() }}</pubDate>
            @if($post->category)
            <category>{{ htmlspecialchars($post->category->name, ENT_XML1) }}</category>
            @endif
        </item>
        @endforeach
    </channel>
</rss>
