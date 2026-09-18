<?php

namespace App\Models\Concerns;

trait DeletesOwnedContent
{
    public function delete(): ?bool
    {
        return $this->getConnection()->transaction(function (): ?bool {
            $deleted = parent::delete();

            if ($deleted === true) {
                $this->seo()->delete();
            }

            return $deleted;
        });
    }
}
