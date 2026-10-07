<?php

use App\Models\Post;
use Illuminate\Support\Facades\Date;

function blogCardPost(): Post
{
    $post = new Post([
        'title' => 'Designing Clear Boundaries',
        'slug' => 'designing-clear-boundaries',
        'excerpt' => 'Why boundaries keep Laravel applications easy to change.',
        'content' => 'Body copy.',
        'published_at' => Date::parse('2026-06-14 12:00:00'),
    ]);
    $post->setRelation('category', null);
    $post->setRelation('tags', collect());

    return $post;
}

it('renders the editorial layout for the editorial flag and the editorial variant alike', function () {
    $post = blogCardPost();

    $flagged = (string) $this->blade('<x-blog-card :post="$post" editorial />', ['post' => $post]);
    $variant = (string) $this->blade('<x-blog-card :post="$post" variant="editorial" />', ['post' => $post]);

    expect($variant)->toBe($flagged)
        ->toContain('Read article');
});

it('renders the related variant as a single linked card with the reading time but no date', function () {
    $post = blogCardPost();

    $html = (string) $this->blade('<x-blog-card :post="$post" variant="related" />', ['post' => $post]);

    expect($html)->toContain('<h3')
        ->toContain('min read')
        ->not->toContain('<time')
        ->and(substr_count($html, 'href="'.route('blog.show', $post).'"'))
        ->toBe(1);
});
