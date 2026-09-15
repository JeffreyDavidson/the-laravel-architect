<?php

namespace App\Support\Content;

use App\Models\Project;

final class ProjectReadiness
{
    public function __construct(private readonly Project $project) {}

    /**
     * @return array<string, array{label: string, complete: bool}>
     */
    public function checks(): array
    {
        return [
            'description' => [
                'label' => 'Description',
                'complete' => filled($this->project->description),
            ],
            'case_study' => [
                'label' => 'Case study',
                'complete' => filled($this->project->content),
            ],
            'featured_image' => [
                'label' => 'Featured image',
                'complete' => filled($this->project->featured_image_path),
            ],
            'project_link' => [
                'label' => 'Project link',
                'complete' => filled($this->project->url) || filled($this->project->github_url),
            ],
            'tech_stack' => [
                'label' => 'Tech stack',
                'complete' => $this->hasTechStack(),
            ],
            'tags' => [
                'label' => 'Tags',
                'complete' => $this->hasTags(),
            ],
        ];
    }

    public function isReady(): bool
    {
        foreach ($this->checks() as $check) {
            if (! $check['complete']) {
                return false;
            }
        }

        return true;
    }

    public function label(): string
    {
        return $this->isReady() ? 'Ready' : 'Needs attention';
    }

    public function progress(): string
    {
        $checks = $this->checks();
        $complete = count(array_filter($checks, fn (array $check): bool => $check['complete']));

        return $complete.'/'.count($checks).' complete';
    }

    public function missingSummary(): string
    {
        $missing = array_map(
            fn (array $check): string => $check['label'],
            array_filter($this->checks(), fn (array $check): bool => ! $check['complete']),
        );

        return $missing === [] ? 'All public details are complete.' : 'Missing: '.implode(', ', $missing);
    }

    private function hasTechStack(): bool
    {
        $techStack = $this->project->tech_stack;

        return is_array($techStack) && count(array_filter($techStack, fn (mixed $item): bool => is_string($item) && filled($item))) > 0;
    }

    private function hasTags(): bool
    {
        if ($this->project->relationLoaded('tags')) {
            return $this->project->tags->isNotEmpty();
        }

        if (array_key_exists('tags_count', $this->project->getAttributes())) {
            $tagsCount = $this->project->getAttribute('tags_count');

            return is_numeric($tagsCount) && (int) $tagsCount > 0;
        }

        return $this->project->tags()->exists();
    }
}
