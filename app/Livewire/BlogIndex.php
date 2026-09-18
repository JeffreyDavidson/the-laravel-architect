<?php

namespace App\Livewire;

use App\Queries\BlogIndexQuery;
use App\Support\Seo\StructuredDataBuilder;
use App\ViewModels\BlogIndexViewModel;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class BlogIndex extends Component
{
    use WithPagination;

    /** @var array<string, mixed>|null */
    private ?array $initialData = null;

    #[Url(as: 'q', history: true, except: '')]
    public string $search = '';

    #[Url(as: 'category', history: true, except: null)]
    public ?string $categorySlug = null;

    /** @param array<string, mixed>|null $initialData */
    public function mount(?string $search = null, ?string $categorySlug = null, ?array $initialData = null): void
    {
        $this->initialData = $initialData;
        if ($search !== null) {
            $this->search = $search;
        }

        if ($categorySlug !== null) {
            $this->categorySlug = $categorySlug;
        }

        $this->normalizeFilters();
    }

    public function updatedSearch(): void
    {
        $this->search = $this->normalizedSearch();
        $this->resetPage();
    }

    public function applySearch(): void
    {
        $this->updatedSearch();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function selectCategory(?string $categorySlug = null): void
    {
        $this->categorySlug = $categorySlug !== null && $categorySlug !== ''
            ? $categorySlug
            : null;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->categorySlug = null;
        $this->resetPage();
    }

    public function render(BlogIndexQuery $blogIndexQuery, BlogIndexViewModel $blogIndexViewModel, StructuredDataBuilder $structuredDataBuilder): View
    {
        if ($this->initialData !== null) {
            return view('livewire.blog-index', $this->initialData);
        }

        $data = $blogIndexViewModel->data(
            $blogIndexQuery->results($this->search, $this->categorySlug),
            $this->search,
            $this->categorySlug,
        );

        $this->dispatch(
            'blog-metadata-updated',
            title: ($data['seoSource']->title ?? '').config()->string('seo.title.suffix'),
            description: $data['seoSource']->description,
            canonicalUrl: $data['seoSource']->canonical_url,
            robots: $data['seoSource']->robots,
            structuredData: $structuredDataBuilder->build($data, 'blog.index'),
        );

        return view('livewire.blog-index', $data);
    }

    private function normalizeFilters(): void
    {
        $this->search = $this->normalizedSearch();
        $this->categorySlug = filled($this->categorySlug) ? $this->categorySlug : null;
    }

    private function normalizedSearch(): string
    {
        return str($this->search)->trim()->limit(120, '')->toString();
    }
}
