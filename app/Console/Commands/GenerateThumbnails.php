<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Geometry\Factories\CircleFactory;
use Intervention\Image\Geometry\Factories\LineFactory;
use Intervention\Image\Geometry\Factories\RectangleFactory;
use Intervention\Image\ImageManager;
use Intervention\Image\Typography\FontFactory;

#[Signature('thumbnails:generate {--output-dir= : Directory where generated thumbnails will be written}')]
#[Description('Generate YouTube thumbnail placeholders')]
class GenerateThumbnails extends Command
{
    private int $width = 1280;

    private int $height = 720;

    public function handle(): int
    {
        $manager = new ImageManager(new Driver);

        $thumbnails = [
            [
                'filename' => 'yt-thumb-testing.png',
                'title' => "Testing Like\nYou Mean It",
                'subtitle' => '3 Suites, Zero Excuses',
                'colors' => ['#22c55e', '#166534'],
                'emoji' => '🧪',
            ],
            [
                'filename' => 'yt-thumb-saas.png',
                'title' => "Build a SaaS\nfrom Scratch",
                'subtitle' => 'Laravel & Filament',
                'colors' => ['#4A7FBF', '#1e3a5f'],
                'emoji' => '🏗️',
            ],
            [
                'filename' => 'yt-thumb-codeigniter.png',
                'title' => "Why I Left\nCodeIgniter",
                'subtitle' => 'And Never Looked Back',
                'colors' => ['#dc2626', '#7f1d1d'],
                'emoji' => '🔥',
            ],
        ];

        $outputDirectory = $this->option('output-dir');
        $outputDirectory = is_string($outputDirectory) && $outputDirectory !== ''
            ? $outputDirectory
            : public_path('images');
        $outputDirectory = Str::finish($outputDirectory, DIRECTORY_SEPARATOR);

        File::ensureDirectoryExists($outputDirectory);

        foreach ($thumbnails as $thumb) {
            $image = $manager->create($this->width, $this->height)->fill('#0D1117');

            // Background gradient orbs
            for ($r = 350; $r > 0; $r -= 3) {
                $image->drawCircle(1050, 200, function (CircleFactory $circle) use ($r, $thumb) {
                    $circle->radius($r);
                    $circle->background($thumb['colors'][0].'03');
                });
            }
            for ($r = 250; $r > 0; $r -= 3) {
                $image->drawCircle(200, 550, function (CircleFactory $circle) use ($r, $thumb) {
                    $circle->radius($r);
                    $circle->background($thumb['colors'][1].'03');
                });
            }

            // Dot grid
            for ($x = 0; $x < $this->width; $x += 30) {
                for ($y = 0; $y < $this->height; $y += 30) {
                    $image->drawCircle($x, $y, function (CircleFactory $circle) {
                        $circle->radius(1);
                        $circle->background('#ffffff05');
                    });
                }
            }

            // Top accent line
            $image->drawLine(function (LineFactory $line) use ($thumb) {
                $line->from(0, 3);
                $line->to(400, 3);
                $line->color($thumb['colors'][0]);
                $line->width(6);
            });

            // Bottom accent line
            $image->drawLine(function (LineFactory $line) use ($thumb) {
                $line->from(880, $this->height - 3);
                $line->to($this->width, $this->height - 3);
                $line->color($thumb['colors'][0].'80');
                $line->width(4);
            });

            // Right accent
            $image->drawLine(function (LineFactory $line) use ($thumb) {
                $line->from($this->width - 3, 0);
                $line->to($this->width - 3, 200);
                $line->color($thumb['colors'][0].'60');
                $line->width(3);
            });

            $boldFont = $this->getBoldFont();
            $monoFont = $this->getMonoFont();

            // Title text
            $lines = explode("\n", $thumb['title']);
            $y = 220;
            foreach ($lines as $line) {
                $image->text($line, 80, $y, function (FontFactory $font) use ($boldFont) {
                    $font->filename($boldFont);
                    $font->size(72);
                    $font->color('#ffffff');
                });
                $y += 90;
            }

            // Subtitle
            $image->text($thumb['subtitle'], 80, $y + 20, function (FontFactory $font) use ($monoFont, $thumb) {
                $font->filename($monoFont);
                $font->size(28);
                $font->color($thumb['colors'][0]);
            });

            // Channel name
            $image->text('THE LARAVEL ARCHITECT', 80, $this->height - 50, function (FontFactory $font) use ($monoFont) {
                $font->filename($monoFont);
                $font->size(14);
                $font->color('#484f58');
            });

            // Decorative shapes
            $image->drawRectangle(900, 150, function (RectangleFactory $rect) use ($thumb) {
                $rect->size(120, 120);
                $rect->border($thumb['colors'][0].'15', 1);
            });
            $image->drawCircle(1050, 400, function (CircleFactory $circle) use ($thumb) {
                $circle->radius(40);
                $circle->border($thumb['colors'][0].'10', 1);
            });
            $image->drawRectangle(950, 480, function (RectangleFactory $rect) use ($thumb) {
                $rect->size(80, 80);
                $rect->border($thumb['colors'][0].'12', 1);
            });

            $path = $outputDirectory.$thumb['filename'];
            $image->toPng()->save($path);
            $this->info("Generated: {$thumb['filename']}");
        }

        $this->info('All thumbnails generated!');

        return self::SUCCESS;
    }

    private function getBoldFont(): string
    {
        $fonts = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
            resource_path('fonts/empera/Empera-Regular.ttf'),
            public_path('fonts/empera/Empera-Regular.ttf'),
        ];
        foreach ($fonts as $font) {
            if (file_exists($font)) {
                return $font;
            }
        }

        return resource_path('fonts/empera/Empera-Regular.ttf');
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
