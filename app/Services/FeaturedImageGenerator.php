<?php

namespace App\Services;

use App\Models\Post;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Geometry\Factories\LineFactory;
use Intervention\Image\Geometry\Factories\RectangleFactory;
use Intervention\Image\Geometry\Factories\CircleFactory;
use Intervention\Image\Typography\FontFactory;

class FeaturedImageGenerator
{
    protected ImageManager $manager;
    protected string $fontBold;
    protected string $fontRegular;
    protected string $fontSemiBold;

    protected array $categoryGradients = [
        'laravel' => ['accent' => 'E74C3C', 'secondary' => '1a3a6b'],
        'personal' => ['accent' => '8B5CF6', 'secondary' => '2d1b69'],
        'career' => ['accent' => '14B8A6', 'secondary' => '134e4a'],
    ];

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
        $this->fontBold = resource_path('fonts/Inter-Bold.ttf');
        $this->fontRegular = resource_path('fonts/Inter-Regular.ttf');
        $this->fontSemiBold = resource_path('fonts/Inter-SemiBold.ttf');
    }

    public function generate(Post $post): \Intervention\Image\Interfaces\ImageInterface
    {
        $width = 1200;
        $height = 675;

        $categorySlug = $post->category?->slug ?? 'personal';
        $colors = $this->categoryGradients[$categorySlug] ?? $this->categoryGradients['personal'];

        $image = $this->manager->create($width, $height)->fill('0D1117');

        // Simulated gradient by drawing horizontal bands
        $this->drawGradientBands($image, $width, $height, $colors['secondary']);

        // Grid pattern
        for ($x = 0; $x < $width; $x += 40) {
            $image->drawLine(function (LineFactory $line) use ($x, $height) {
                $line->from($x, 0);
                $line->to($x, $height);
                $line->color('1a2332');
                $line->width(1);
            });
        }
        for ($y = 0; $y < $height; $y += 40) {
            $image->drawLine(function (LineFactory $line) use ($y, $width) {
                $line->from(0, $y);
                $line->to($width, $y);
                $line->color('1a2332');
                $line->width(1);
            });
        }

        // Abstract geometric shapes
        $seed = crc32($post->slug);
        mt_srand($seed);

        // Circles
        for ($i = 0; $i < 5; $i++) {
            $cx = mt_rand(600, 1150);
            $cy = mt_rand(50, 625);
            $r = mt_rand(30, 100);
            $image->drawCircle($cx, $cy, function (CircleFactory $circle) use ($r, $colors) {
                $circle->radius($r);
                $circle->border($colors['secondary'], 2);
            });
        }

        // Rectangles
        for ($i = 0; $i < 3; $i++) {
            $rx = mt_rand(700, 1100);
            $ry = mt_rand(100, 500);
            $image->drawRectangle($rx, $ry, function (RectangleFactory $rect) use ($colors) {
                $rect->size(mt_rand(40, 120), mt_rand(40, 120));
                $rect->border($colors['secondary'], 2);
            });
        }

        // Brand accent bar at top
        $image->drawRectangle(0, 0, function (RectangleFactory $rect) use ($width, $colors) {
            $rect->size($width, 4);
            $rect->background($colors['accent']);
        });

        // Code bracket motif - large faded angle brackets
        $image->text('<', 950, 200, function (FontFactory $font) {
            $font->filename($this->fontBold);
            $font->size(180);
            $font->color('1a2332');
        });
        $image->text('/>', 1020, 350, function (FontFactory $font) {
            $font->filename($this->fontBold);
            $font->size(140);
            $font->color('1a2332');
        });

        // Category name at top
        $categoryName = $post->category?->name ?? 'Blog';
        $image->text(strtoupper($categoryName), 80, 120, function (FontFactory $font) use ($colors) {
            $font->filename($this->fontSemiBold);
            $font->size(16);
            $font->color($colors['accent']);
            $font->lineHeight(1.6);
        });

        // Post title
        $lines = $this->wordWrap($post->title, 26);
        $yOffset = 180;
        foreach ($lines as $line) {
            $image->text($line, 80, $yOffset, function (FontFactory $font) {
                $font->filename($this->fontBold);
                $font->size(52);
                $font->color('ffffff');
                $font->lineHeight(1.4);
            });
            $yOffset += 70;
        }

        // Branding at bottom
        $image->text('The Laravel Architect', 80, 620, function (FontFactory $font) {
            $font->filename($this->fontRegular);
            $font->size(18);
            $font->color('6b7280');
        });

        // Small accent line before branding
        $image->drawRectangle(80, 580, function (RectangleFactory $rect) use ($colors) {
            $rect->size(60, 3);
            $rect->background($colors['accent']);
        });

        return $image;
    }

    protected function drawGradientBands($image, int $width, int $height, string $secondaryHex): void
    {
        // Parse secondary color
        $r2 = hexdec(substr($secondaryHex, 0, 2));
        $g2 = hexdec(substr($secondaryHex, 2, 2));
        $b2 = hexdec(substr($secondaryHex, 4, 2));

        // Base: 0D1117
        $r1 = 13; $g1 = 17; $b1 = 23;

        $bands = 20;
        $bandHeight = (int)ceil($height / $bands);

        for ($i = 0; $i < $bands; $i++) {
            $ratio = $i / $bands;
            $r = (int)($r1 + ($r2 - $r1) * $ratio * 0.5);
            $g = (int)($g1 + ($g2 - $g1) * $ratio * 0.5);
            $b = (int)($b1 + ($b2 - $b1) * $ratio * 0.5);
            $color = sprintf('%02x%02x%02x', $r, $g, $b);

            $image->drawRectangle(0, $i * $bandHeight, function (RectangleFactory $rect) use ($width, $bandHeight, $color) {
                $rect->size($width, $bandHeight);
                $rect->background($color);
            });
        }
    }

    protected function wordWrap(string $text, int $maxChars): array
    {
        $words = explode(' ', $text);
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            if (strlen($current . ' ' . $word) > $maxChars && $current !== '') {
                $lines[] = trim($current);
                $current = $word;
            } else {
                $current .= ($current ? ' ' : '') . $word;
            }
        }
        if ($current) {
            $lines[] = trim($current);
        }

        return array_slice($lines, 0, 4);
    }
}
