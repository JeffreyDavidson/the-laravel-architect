<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Enums\ContactBudget;
use App\Enums\ContactType;
use App\Models\Project;
use App\Queries\PublishedProjectQuery;
use JeffreyDavidson\CreatorKit\Contracts\PageViewModel;
use JeffreyDavidson\CreatorKit\Data\PageMeta;
use RalphJSmit\Laravel\SEO\Support\SEOData;

final readonly class ContactViewModel implements PageViewModel
{
    public function __construct(
        private PublishedProjectQuery $publishedProjectQuery,
        private SiteStructuredData $site,
    ) {}

    /**
     * @return array{
     *     pageMeta: PageMeta,
     *     contactTypeOptions: array<string, string>,
     *     contactBudgetOptions: array<string, string>,
     *     defaultContactType: string,
     *     selectedProject: Project|null,
     * }
     */
    public function data(string $projectSlug = ''): array
    {
        $url = route('contact.create');

        return [
            'contactTypeOptions' => collect(ContactType::cases())
                ->mapWithKeys(fn (ContactType $type): array => [$type->value => $type->getLabel()])
                ->all(),
            'contactBudgetOptions' => collect(ContactBudget::cases())
                ->mapWithKeys(fn (ContactBudget $budget): array => [$budget->value => $budget->getLabel()])
                ->all(),
            'defaultContactType' => ContactType::Freelance->value,
            'selectedProject' => $this->publishedProjectQuery->findBySlug($projectSlug),
            'pageMeta' => new PageMeta(
                seo: new SEOData(
                    title: 'Contact',
                    description: 'Get in touch with Jeffrey Davidson for freelance Laravel development, consulting, legacy modernization, or just to say hello.',
                ),
                structuredData: [
                    $this->site->page('ContactPage', 'Contact', $url),
                    $this->site->breadcrumbs([['name' => 'Contact', 'url' => $url]]),
                ],
            ),
        ];
    }
}
