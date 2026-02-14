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
        'personal' => ['#4A7FBF', '#2d5a8e', '#6fa3d6'],
        'career' => ['#E47A9D', '#9D5175', '#f4a5bd'],
        'laravel' => ['#FF2D20', '#cc2419', '#ff6b61'],
        'testing' => ['#22c55e', '#16a34a', '#4ade80'],
        'architecture' => ['#a855f7', '#7c3aed', '#c084fc'],
        'default' => ['#4A7FBF', '#2d5a8e', '#6fa3d6'],
    ];

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    public function generate(Post $post): string
    {
        $categorySlug = $post->category?->slug ?? 'default';
        $colors = $this->categoryColors[$categorySlug] ?? $this->categoryColors['default'];
        $categoryName = $post->category?->name ?? 'Blog';

        $image = $this->manager->create($this->width, $this->height);

        // Dark background
        $image = $image->fill('#0D1117');

        // Draw gradient-like background with rectangles
        $this->drawGradientBackground($image, $colors);

        // Draw dot grid pattern
        $this->drawDotGrid($image);

        // Draw decorative lines
        $this->drawDecorativeLines($image, $colors);

        // Draw category badge
        $this->drawCategoryBadge($image, $categoryName, $colors[0]);

        // Draw post title
        $this->drawTitle($image, $post->title);

        // Draw author line
        $this->drawAuthorLine($image);

        // Draw brand mark
        $this->drawBrandMark($image, $colors[0]);

        // Save to storage
        $path = 'featured-images/' . $post->slug . '.png';
        $storagePath = storage_path('app/public/' . $path);

        // Ensure directory exists
        if (!is_dir(dirname($storagePath))) {
            mkdir(dirname($storagePath), 0755, true);
        }

        $image->toPng()->save($storagePath);

        return $path;
    }

    private function drawGradientBackground($image, array $colors): void
    {
        // Large accent orb top-right
        for ($r = 300; $r > 0; $r -= 2) {
            $opacity = max(0, min(1, ($r / 300) * 0.12));
            $hex = $colors[0];
            $alpha = (int)($opacity * 127);
            // Use circles for gradient orb effect
            $image->drawCircle(1050, 80, function ($circle) use ($r, $hex, $alpha) {
                $circle->radius($r);
                $circle->background($hex . sprintf('%02x', min(255, max(0, (int)(255 * 0.03)))));
            });
        }

        // Smaller accent orb bottom-left
        for ($r = 200; $r > 0; $r -= 3) {
            $image->drawCircle(150, 550, function ($circle) use ($r, $colors) {
                $circle->radius($r);
                $circle->background($colors[1] . sprintf('%02x', min(255, max(0, (int)(255 * 0.02)))));
            });
        }
    }

    private function drawDotGrid($image): void
    {
        $spacing = 30;
        for ($x = 0; $x < $this->width; $x += $spacing) {
            for ($y = 0; $y < $this->height; $y += $spacing) {
                $image->drawCircle($x, $y, function ($circle) {
                    $circle->radius(1);
                    $circle->background('rgba(255, 255, 255, 0.03)');
                });
            }
        }
    }

    private function drawDecorativeLines($image, array $colors): void
    {
        // Top accent line
        $image->drawLine(function (LineFactory $line) use ($colors) {
            $line->from(0, 3);
            $line->to((int)($this->width * 0.4), 3);
            $line->color($colors[0]);
            $line->width(6);
        });

        // Right side vertical accent
        $image->drawLine(function (LineFactory $line) use ($colors) {
            $line->from($this->width - 3, 0);
            $line->to($this->width - 3, (int)($this->height * 0.3));
            $line->color($colors[2]);
            $line->width(3);
        });

        // Bottom subtle line
        $image->drawLine(function (LineFactory $line) use ($colors) {
            $line->from((int)($this->width * 0.6), $this->height - 3);
            $line->to($this->width, $this->height - 3);
            $line->color($colors[0] . '40');
            $line->width(2);
        });
    }

    private function drawCategoryBadge($image, string $categoryName, string $color): void
    {
        $badgeY = 180;
        $badgeX = 80;

        // Badge background
        $image->drawRectangle($badgeX - 2, $badgeY - 2, function ($rect) use ($color) {
            $rect->size(strlen($color) > 7 ? 140 : 120, 32);
            $rect->background($color . '30');
            $rect->border($color . '60', 1);
        });

        // Badge text
        $fontPath = $this->getMonoFont();
        $image->text(strtoupper($categoryName), $badgeX + 12, $badgeY + 20, function (FontFactory $font) use ($fontPath, $color) {
            $font->filename($fontPath);
            $font->size(13);
            $font->color($color);
        });
    }

    private function drawTitle($image, string $title): void
    {
        $fontPath = $this->getBoldFont();
        $maxWidth = $this->width - 160; // 80px padding each side
        $x = 80;
        $y = 260;
        $lineHeight = 58;

        // Word-wrap the title
        $words = explode(' ', $title);
        $lines = [];
        $currentLine = '';

        foreach ($words as $word) {
            $testLine = $currentLine ? $currentLine . ' ' . $word : $word;
            // Rough character width estimate for ~44px font
            $testWidth = strlen($testLine) * 26;
            if ($testWidth > $maxWidth && $currentLine) {
                $lines[] = $currentLine;
                $currentLine = $word;
            } else {
                $currentLine = $testLine;
            }
        }
        if ($currentLine) {
            $lines[] = $currentLine;
        }

        // Limit to 3 lines
        if (count($lines) > 3) {
            $lines = array_slice($lines, 0, 3);
            $lines[2] = rtrim($lines[2]) . '...';
        }

        foreach ($lines as $i => $line) {
            $image->text($line, $x, $y + ($i * $lineHeight), function (FontFactory $font) use ($fontPath) {
                $font->filename($fontPath);
                $font->size(44);
                $font->color('#ffffff');
            });
        }
    }

    private function drawAuthorLine($image): void
    {
        $fontPath = $this->getMonoFont();
        $y = 530;

        $image->text('Jeffrey Davidson', 80, $y, function (FontFactory $font) use ($fontPath) {
            $font->filename($fontPath);
            $font->size(14);
            $font->color('#8b949e');
        });

        $image->text('thelaravelarchitect.com', 80, $y + 24, function (FontFactory $font) use ($fontPath) {
            $font->filename($fontPath);
            $font->size(12);
            $font->color('#484f58');
        });
    }

    private function drawBrandMark($image, string $color): void
    {
        // Small colored square in bottom-right as brand mark
        $image->drawRectangle($this->width - 120, $this->height - 60, function ($rect) use ($color) {
            $rect->size(40, 40);
            $rect->background($color . '40');
            $rect->border($color, 2);
        });

        // "TLA" text inside
        $fontPath = $this->getMonoFont();
        $image->text('TLA', $this->width - 112, $this->height - 32, function (FontFactory $font) use ($fontPath, $color) {
            $font->filename($fontPath);
            $font->size(14);
            $font->color($color);
        });
    }

    private function getBoldFont(): string
    {
        // Try system fonts that support bold
        $fonts = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
            '/usr/share/fonts/truetype/ubuntu/Ubuntu-Bold.ttf',
            public_path('fonts/empera/Empera-Regular.ttf'),
        ];

        foreach ($fonts as $font) {
            if (file_exists($font)) {
                return $font;
            }
        }

        return public_path('fonts/empera/Empera-Regular.ttf');
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

        return $this->getBoldFont();
    }
}
