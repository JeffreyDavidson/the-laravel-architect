<?php

namespace App\Services;

use App\Models\Post;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Geometry\Factories\LineFactory;
use Intervention\Image\Typography\FontFactory;

class FeaturedImageGenerator
{
    private ImageManager $manager;
    private int $width = 1200;
    private int $height = 630;

    // Category color schemes: [primary, secondary, accent]
    private array $categoryColors = [
        'personal' => ['#3d6a9e', '#2d5078', '#5a86b5'],
        'career' => ['#8b5a6b', '#6b4555', '#a07080'],
        'laravel' => ['#8b3a3a', '#6b2d2d', '#a85454'],
        'testing' => ['#3a6b4a', '#2d5538', '#5a8b6a'],
        'architecture' => ['#6b5a8b', '#553d6b', '#8070a0'],
        'default' => ['#3d6a9e', '#2d5078', '#5a86b5'],
    ];

    // Code snippets per category - these get rendered as syntax-highlighted text
    private array $codeSnippets = [
        'personal' => [
            ['$architect = new Developer();', 'keyword'],
            ['$architect->name = \'Jeffrey\';', 'string'],
            ['$architect->location = \'Florida\';', 'string'],
            ['', ''],
            ['class Life extends Journey {', 'keyword'],
            ['    protected $coffee = true;', 'variable'],
            ['    protected $passion = \'code\';', 'string'],
            ['}', 'keyword'],
            ['', ''],
            ['Route::get(\'/story\', fn() =>', 'method'],
            ['    view(\'chapters.next\'));', 'string'],
            ['', ''],
            ['$this->buildInPublic();', 'method'],
            ['$this->shareTheJourney();', 'method'],
        ],
        'career' => [
            ['function careerAdvice(): Collection', 'keyword'],
            ['{', 'bracket'],
            ['    return collect([', 'method'],
            ['        \'Ship early, iterate often\',', 'string'],
            ['        \'Soft skills > algorithms\',', 'string'],
            ['        \'Read the source code\',', 'string'],
            ['        \'Teach to learn deeper\',', 'string'],
            ['    ]);', 'bracket'],
            ['}', 'bracket'],
            ['', ''],
            ['expect($developer)->toGrow();', 'method'],
            ['expect($career)->toBeRewarding();', 'method'],
            ['', ''],
            ['// 15 years and counting...', 'comment'],
        ],
        'laravel' => [
            ['Route::middleware(\'architect\')', 'method'],
            ['    ->group(function () {', 'keyword'],
            ['        Route::resource(', 'method'],
            ['            \'projects\',', 'string'],
            ['            ProjectController::class', 'class'],
            ['        );', 'bracket'],
            ['    });', 'bracket'],
            ['', ''],
            ['$app = Application::configure()', 'method'],
            ['    ->withRouting(web: true)', 'method'],
            ['    ->withMiddleware()', 'method'],
            ['    ->create();', 'method'],
            ['', ''],
            ['// Elegant. Always.', 'comment'],
        ],
        'testing' => [
            ['test(\'it builds quality\', function () {', 'method'],
            ['    $project = Architect::build(', 'method'],
            ['        request: $validated,', 'variable'],
            ['    );', 'bracket'],
            ['', ''],
            ['    expect($project)', 'method'],
            ['        ->toBeClean()', 'method'],
            ['        ->toBeScalable()', 'method'],
            ['        ->toBeWellTested();', 'method'],
            ['});', 'bracket'],
            ['', ''],
            ['// ✓ All tests passed', 'comment'],
            ['// 4 tests, 12 assertions', 'comment'],
        ],
        'architecture' => [
            ['namespace App\\Actions;', 'keyword'],
            ['', ''],
            ['class CreateSubscription', 'class'],
            ['{', 'bracket'],
            ['    public function handle(', 'keyword'],
            ['        User $user,', 'variable'],
            ['        Plan $plan,', 'variable'],
            ['    ): Subscription {', 'class'],
            ['        return $user', 'variable'],
            ['            ->subscriptions()', 'method'],
            ['            ->create([...]);', 'method'],
            ['    }', 'bracket'],
            ['}', 'bracket'],
        ],
    ];

    // Syntax colors matching the hero code editor
    private array $syntaxColors = [
        'keyword' => '#ff7b72',
        'string' => '#a5d6ff',
        'class' => '#79c0ff',
        'method' => '#d2a8ff',
        'comment' => '#484f58',
        'variable' => '#ffa657',
        'bracket' => '#8b949e',
    ];

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    public function generate(Post $post): string
    {
        $categorySlug = $post->category?->slug ?? 'default';
        $colors = $this->categoryColors[$categorySlug] ?? $this->categoryColors['default'];
        $snippets = $this->codeSnippets[$categorySlug] ?? $this->codeSnippets['laravel'];
        $categoryName = $post->category?->name ?? 'Blog';

        $image = $this->manager->create($this->width, $this->height);
        $image = $image->fill('#0D1117');

        // Draw gradient orbs
        $this->drawGradientOrbs($image, $colors);

        // Draw dot grid pattern
        $this->drawDotGrid($image);

        // Draw decorative accent lines
        $this->drawAccentLines($image, $colors);

        // Draw code snippets
        $this->drawCodeSnippets($image, $snippets);

        // Draw category badge (small, bottom-left)
        $this->drawCategoryBadge($image, $categoryName, $colors[0]);

        // Draw brand mark (bottom-right)
        $this->drawBrandMark($image, $colors[0]);

        // Save
        $path = 'featured-images/' . $post->slug . '.png';
        $storagePath = storage_path('app/public/' . $path);

        if (!is_dir(dirname($storagePath))) {
            mkdir(dirname($storagePath), 0755, true);
        }

        $image->toPng()->save($storagePath);

        return $path;
    }

    private function drawGradientOrbs($image, array $colors): void
    {
        // Large orb top-right
        for ($r = 300; $r > 0; $r -= 3) {
            $image->drawCircle(1000, 100, function ($circle) use ($r, $colors) {
                $circle->radius($r);
                $circle->background($colors[0] . '02');
            });
        }

        // Smaller orb bottom-left
        for ($r = 200; $r > 0; $r -= 3) {
            $image->drawCircle(200, 530, function ($circle) use ($r, $colors) {
                $circle->radius($r);
                $circle->background($colors[1] . '02');
            });
        }
    }

    private function drawDotGrid($image): void
    {
        $spacing = 32;
        for ($x = 0; $x < $this->width; $x += $spacing) {
            for ($y = 0; $y < $this->height; $y += $spacing) {
                $image->drawCircle($x, $y, function ($circle) {
                    $circle->radius(1);
                    $circle->background('#ffffff05');
                });
            }
        }
    }

    private function drawAccentLines($image, array $colors): void
    {
        // Top accent line (partial)
        $image->drawLine(function (LineFactory $line) use ($colors) {
            $line->from(0, 2);
            $line->to((int)($this->width * 0.3), 2);
            $line->color($colors[0]);
            $line->width(4);
        });

        // Right side vertical accent
        $image->drawLine(function (LineFactory $line) use ($colors) {
            $line->from($this->width - 2, 0);
            $line->to($this->width - 2, (int)($this->height * 0.25));
            $line->color($colors[2] . '80');
            $line->width(2);
        });
    }

    private function drawCodeSnippets($image, array $snippets): void
    {
        $monoFont = $this->getMonoFont();
        $startX = 60;
        $startY = 50;
        $lineHeight = 38;
        $lineNumberWidth = 40;

        foreach ($snippets as $i => $snippet) {
            [$text, $type] = $snippet;
            $y = $startY + ($i * $lineHeight);

            // Don't draw past the bottom
            if ($y > $this->height - 80) break;

            // Line number
            $lineNum = str_pad((string)($i + 1), 2, ' ', STR_PAD_LEFT);
            $image->text($lineNum, $startX, $y, function (FontFactory $font) use ($monoFont) {
                $font->filename($monoFont);
                $font->size(15);
                $font->color('#484f5840');
            });

            // Code text
            if ($text && $type) {
                $color = $this->syntaxColors[$type] ?? '#c9d1d9';
                // Reduce opacity for a subtle, background feel
                $image->text($text, $startX + $lineNumberWidth, $y, function (FontFactory $font) use ($monoFont, $color) {
                    $font->filename($monoFont);
                    $font->size(16);
                    $font->color($color . '90');
                });
            }
        }

        // Draw a second column of code (offset, more faded) for visual density
        $startX2 = 620;
        $startY2 = 120;
        $reversedSnippets = array_reverse($snippets);

        foreach ($reversedSnippets as $i => $snippet) {
            [$text, $type] = $snippet;
            $y = $startY2 + ($i * $lineHeight);

            if ($y > $this->height - 60) break;
            if (!$text || !$type) continue;

            $color = $this->syntaxColors[$type] ?? '#c9d1d9';
            $image->text($text, $startX2, $y, function (FontFactory $font) use ($monoFont, $color) {
                $font->filename($monoFont);
                $font->size(14);
                $font->color($color . '40');
            });
        }
    }

    private function drawCategoryBadge($image, string $categoryName, string $color): void
    {
        $monoFont = $this->getMonoFont();

        $image->text(strtoupper($categoryName), 60, $this->height - 40, function (FontFactory $font) use ($monoFont, $color) {
            $font->filename($monoFont);
            $font->size(11);
            $font->color($color . 'a0');
        });
    }

    private function drawBrandMark($image, string $color): void
    {
        $monoFont = $this->getMonoFont();

        $image->text('thelaravelarchitect.com', $this->width - 230, $this->height - 40, function (FontFactory $font) use ($monoFont) {
            $font->filename($monoFont);
            $font->size(11);
            $font->color('#484f5880');
        });
    }

    private function getMonoFont(): string
    {
        $fonts = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSansMono.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationMono-Regular.ttf',
        ];

        foreach ($fonts as $font) {
            if (file_exists($font)) {
                return $font;
            }
        }

        return public_path('fonts/empera/Empera-Regular.ttf');
    }
}
