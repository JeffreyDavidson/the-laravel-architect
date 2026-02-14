<?php

namespace App\Services;

use App\Models\Post;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Geometry\Factories\LineFactory;
use Intervention\Image\Geometry\Factories\RectangleFactory;
use Intervention\Image\Geometry\Factories\CircleFactory;
use Intervention\Image\Typography\FontFactory;

class OgImageGenerator
{
    protected ImageManager $manager;
    protected string $fontBold;
    protected string $fontRegular;
    protected string $fontSemiBold;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
        $this->fontBold = resource_path('fonts/Inter-Bold.ttf');
        $this->fontRegular = resource_path('fonts/Inter-Regular.ttf');
        $this->fontSemiBold = resource_path('fonts/Inter-SemiBold.ttf');
    }

    public function generate(Post $post): string
    {
        $width = 1200;
        $height = 630;

        $image = $this->manager->create($width, $height)->fill('0D1117');

        // Geometric pattern overlay - subtle grid
        for ($x = 0; $x < $width; $x += 60) {
            $image->drawLine(function (LineFactory $line) use ($x, $height) {
                $line->from($x, 0);
                $line->to($x, $height);
                $line->color('141922');
                $line->width(1);
            });
        }
        for ($y = 0; $y < $height; $y += 60) {
            $image->drawLine(function (LineFactory $line) use ($y, $width) {
                $line->from(0, $y);
                $line->to($width, $y);
                $line->color('141922');
                $line->width(1);
            });
        }

        // Decorative circles
        $image->drawCircle(900, 120, function (CircleFactory $circle) {
            $circle->radius(80);
            $circle->border('1a2332', 2);
        });
        $image->drawCircle(1050, 400, function (CircleFactory $circle) {
            $circle->radius(120);
            $circle->border('1a2332', 2);
        });

        // Brand accent line at top
        $image->drawRectangle(0, 0, function (RectangleFactory $rect) use ($width) {
            $rect->size($width, 4);
            $rect->background('4A7FBF');
        });

        // Category label
        $categoryName = $post->category?->name ?? 'Blog';
        $image->text(strtoupper($categoryName), 80, 180, function (FontFactory $font) {
            $font->filename($this->fontSemiBold);
            $font->size(18);
            $font->color('4A7FBF');
            $font->lineHeight(1.6);
        });

        // Post title - word wrap
        $title = $post->title;
        $lines = $this->wordWrap($title, 28);
        $yOffset = 230;
        foreach ($lines as $line) {
            $image->text($line, 80, $yOffset, function (FontFactory $font) {
                $font->filename($this->fontBold);
                $font->size(48);
                $font->color('ffffff');
                $font->lineHeight(1.4);
            });
            $yOffset += 65;
        }

        // Branding at bottom
        $image->text('The Laravel Architect', 80, 560, function (FontFactory $font) {
            $font->filename($this->fontRegular);
            $font->size(20);
            $font->color('6b7280');
        });

        // Code bracket motif
        $image->text('{ }', 1050, 560, function (FontFactory $font) {
            $font->filename($this->fontBold);
            $font->size(32);
            $font->color('1a2332');
        });

        return $image->toPng()->toString();
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

        return array_slice($lines, 0, 4); // Max 4 lines
    }
}
