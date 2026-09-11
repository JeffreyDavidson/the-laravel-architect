<?php

namespace App\Presenters;

use App\Models\Post;

final readonly class PostPresenter
{
    public function __construct(private Post $post) {}

    public static function from(Post $post): self
    {
        return new self($post);
    }

    public function readingTime(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags($this->post->content)) / 250));
    }
}
