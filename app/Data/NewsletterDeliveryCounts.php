<?php

declare(strict_types=1);

namespace App\Data;

/**
 * How many deliveries a sent newsletter issue has, and how many of them the mailer has sent.
 */
final readonly class NewsletterDeliveryCounts
{
    public function __construct(
        public int $total,
        public int $delivered,
    ) {}
}
