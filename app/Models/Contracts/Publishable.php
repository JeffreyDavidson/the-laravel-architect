<?php

namespace App\Models\Contracts;

interface Publishable
{
    public function isPublished(): bool;
}
