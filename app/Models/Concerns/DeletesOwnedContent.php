<?php

namespace App\Models\Concerns;

/**
 * Removes the SEO row owned by soft-deletable content once it is force deleted.
 */
trait DeletesOwnedContent
{
    public function delete(): ?bool
    {
        return $this->getConnection()
            ->transaction(function (): ?bool {
                $deleted = parent::delete();

                if ($deleted === true && $this->isForceDeleting()) {
                    $this->seo()
                        ->delete();
                }

                return $deleted;
            });
    }
}
