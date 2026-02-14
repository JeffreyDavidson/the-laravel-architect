<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            [
                'title' => 'Ringside',
                'slug' => 'ringside',
                'description' => 'A wrestling promotion management platform for booking events, managing rosters, tracking championships, and organizing storylines.',
                'content' => "<h2>What It Does</h2><p>Ringside is a full-featured management platform built for wrestling promotions. It handles everything from roster management and contract tracking to event booking, championship lineage, and storyline planning — replacing the spreadsheets and disjointed tools most promotions rely on.</p><h2>Technical Details</h2><ul><li>Built with Laravel and a custom admin panel (ThemeForest template integration)</li><li>Action-based architecture for clean, testable business logic</li><li>Complex Eloquent relationships for championship histories, faction memberships, and event cards</li><li>Full test coverage with Pest</li><li>Event-driven system where match results cascade through rankings and storylines</li></ul><h2>The Challenge</h2><p>Wrestling promotions have surprisingly intricate domain logic — title vacancies, multi-person matches, faction dynamics, contract windows, and suspension tracking. Modeling all of that cleanly without the codebase becoming a mess was the real architecture challenge. This project is where I sharpened my skills with action classes and domain-driven design patterns.</p>",
                'url' => 'https://theringside.app',
                'github_url' => 'https://github.com/JeffreyDavidson/Ringside',
                'tech_stack' => ['Laravel', 'PHP', 'Pest', 'Tailwind CSS', 'MySQL'],
                'is_featured' => true,
                'sort_order' => 1,
                'status' => 'published',
                'tags' => ['Laravel', 'PHP', 'Architecture'],
            ],
            [
                'title' => 'The Laravel Architect',
                'slug' => 'the-laravel-architect',
                'description' => 'My personal website and content platform — blog, podcasts, projects, and a hire-me page — built from scratch with Laravel and Filament.',
                'content' => "<h2>Building My Own Platform</h2><p>Instead of using WordPress or a static site generator, I built my personal site from scratch with Laravel. It's a content hub for everything I create — blog posts, podcast episodes, project showcases, and a contact form for freelance inquiries.</p><h2>Technical Highlights</h2><ul><li>Laravel 12 with Filament 5 admin panel for content management</li><li>Multi-podcast support with episode management and show notes</li><li>Markdown blog with Prism.js syntax highlighting</li><li>Newsletter subscriber system</li><li>Contact form powered by Resend</li><li>Modern dark theme with micro-interactions and CSS animations</li></ul><h2>Design Philosophy</h2><p>I wanted something that felt like a developer's site — dark, clean, and fast. The homepage features a VS Code-style code editor mockup that doubles as an introduction. No bloat, no page builders, just Blade templates and Tailwind.</p>",
                'url' => 'https://thelaravelarchitect.com',
                'github_url' => 'https://github.com/JeffreyDavidson/personal-website',
                'tech_stack' => ['Laravel', 'Filament', 'Tailwind CSS', 'Blade', 'SQLite', 'Resend'],
                'is_featured' => true,
                'sort_order' => 2,
                'status' => 'published',
                'tags' => ['Laravel', 'PHP'],
            ],
            [
                'title' => 'Campus Sync',
                'slug' => 'campus-sync',
                'description' => 'An admissions and grading application for educational institutions. Student enrollment, course management, and academic record tracking.',
                'content' => "<h2>The Project</h2><p>Campus Sync is a Laravel application designed to streamline the admissions process and academic grading for educational institutions. It brings student enrollment, course management, grading, and transcript generation into a single unified platform.</p><h2>Key Features</h2><ul><li>Admissions pipeline with application tracking and status management</li><li>Course and section management with instructor assignment</li><li>Grade entry and GPA calculation</li><li>Student academic record and transcript generation</li><li>Role-based access for administrators, faculty, and students</li></ul><h2>Status</h2><p>Currently in active development. Building out the core domain models and admissions workflow first, with grading and reporting to follow.</p>",
                'url' => null,
                'github_url' => 'https://github.com/JeffreyDavidson/campus-sync',
                'tech_stack' => ['Laravel', 'PHP', 'Tailwind CSS'],
                'is_featured' => false,
                'sort_order' => 3,
                'status' => 'published',
                'tags' => ['Laravel', 'PHP'],
            ],
            [
                'title' => 'Dotfiles',
                'slug' => 'dotfiles',
                'description' => 'My macOS development environment configuration. Shell setup, Git config, Homebrew packages, and editor preferences — version controlled and portable.',
                'content' => "<h2>Why Dotfiles?</h2><p>Every developer should version control their environment. These are the configuration files I use across my machines — shell aliases, Git settings, Homebrew package lists, and editor config. One clone and a bootstrap script gets me from a fresh Mac to a fully configured development environment.</p><h2>What's Included</h2><ul><li>Zsh configuration with custom aliases and functions</li><li>Git config with sensible defaults and aliases</li><li>Homebrew Brewfile for automated package installation</li><li>macOS system preferences scripted for consistency</li><li>Editor configuration and extensions list</li></ul><h2>Philosophy</h2><p>Keep it simple, keep it documented, keep it in Git. If my laptop dies tomorrow, I want to be productive on a new machine within an hour.</p>",
                'url' => null,
                'github_url' => 'https://github.com/JeffreyDavidson/dotfiles',
                'tech_stack' => ['Shell', 'Homebrew', 'Zsh', 'Git'],
                'is_featured' => false,
                'sort_order' => 4,
                'status' => 'published',
                'tags' => [],
            ],
        ];

        foreach ($projects as $data) {
            $tags = $data['tags'] ?? [];
            unset($data['tags']);

            $project = Project::create($data);

            if (!empty($tags)) {
                $tagIds = Tag::whereIn('name', $tags)->pluck('id');
                $project->tags()->attach($tagIds);
            }
        }
    }
}
