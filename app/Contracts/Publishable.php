<?php

namespace App\Contracts;

interface Publishable
{
    public function isPublished(): bool;
}
