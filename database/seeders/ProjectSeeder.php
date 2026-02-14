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
                'title' => 'RingGeneral',
                'slug' => 'ringgeneral',
                'description' => 'A full-featured wrestling promotion management platform. Manage rosters, events, championships, and storylines with a clean admin interface.',
                'content' => "<h2>The Problem</h2><p>Managing a wrestling promotion involves tracking dozens of wrestlers, multiple championships, complex event cards, and ongoing storylines. Most promotions use spreadsheets or outdated software that wasn't built for the job.</p><h2>The Solution</h2><p>RingGeneral is a purpose-built Laravel application that handles everything a wrestling promotion needs — roster management with contract tracking, championship lineage, event booking with match card builders, and a storyline timeline that keeps everything connected.</p><h2>Technical Highlights</h2><ul><li>Built with Laravel 12 and Filament 5 for the admin panel</li><li>Action-based architecture with full test coverage</li><li>Complex Eloquent relationships for championship histories and faction memberships</li><li>Event-driven system for match results affecting rankings and storylines</li><li>RESTful API for potential mobile app integration</li></ul><h2>What I Learned</h2><p>This project pushed my understanding of complex domain modeling. Wrestling promotions have surprisingly intricate business rules — title vacancies, faction betrayals, multi-person matches — and encoding all of that cleanly in Laravel was a genuine architecture challenge.</p>",
                'url' => null,
                'github_url' => 'https://github.com/JeffreyDavidson/ringgeneral',
                'tech_stack' => ['Laravel', 'Filament', 'Livewire', 'Tailwind CSS', 'SQLite', 'Pest'],
                'is_featured' => true,
                'sort_order' => 1,
                'status' => 'published',
                'tags' => ['Laravel', 'PHP', 'Architecture'],
            ],
            [
                'title' => 'The Laravel Architect',
                'slug' => 'the-laravel-architect',
                'description' => 'My personal website and content platform. Blog, podcasts, projects, and a hire-me page — all built with Laravel and Filament.',
                'content' => "<h2>Building My Own Platform</h2><p>Instead of using WordPress or a static site generator, I built my personal site from scratch with Laravel. It's a content hub for everything I create — blog posts, podcast episodes, project showcases, and a contact form for freelance inquiries.</p><h2>Technical Highlights</h2><ul><li>Laravel 12 with Filament 5 admin panel for content management</li><li>Multi-podcast support with episode management and show notes</li><li>Markdown blog with Prism.js syntax highlighting</li><li>Dynamic OG image generation via Intervention Image</li><li>Programmatic featured images with category-themed gradients</li><li>Modern dark theme with micro-interactions and CSS animations</li></ul><h2>Design Philosophy</h2><p>I wanted something that felt like a developer's site — dark, clean, and fast. The homepage features a VS Code-style code editor mockup that doubles as an introduction. No bloat, no page builders, just Blade templates and Tailwind.</p>",
                'url' => 'https://jeffreydavidson.me',
                'github_url' => 'https://github.com/JeffreyDavidson/personal-website',
                'tech_stack' => ['Laravel', 'Filament', 'Tailwind CSS', 'Blade', 'SQLite', 'Intervention Image'],
                'is_featured' => true,
                'sort_order' => 2,
                'status' => 'published',
                'tags' => ['Laravel', 'PHP'],
            ],
            [
                'title' => 'Stafford County Schools Portal',
                'slug' => 'stafford-county-schools-portal',
                'description' => 'A legacy CakePHP school district portal migrated to Laravel. Student records, teacher dashboards, parent communication, and grade management.',
                'content' => "<h2>The Migration</h2><p>Stafford County's school district was running on a CakePHP 2.x application that hadn't been meaningfully updated in years. The codebase was brittle, untested, and increasingly difficult to maintain. They needed a modern rewrite without losing any functionality.</p><h2>Approach</h2><p>I took a strangler fig approach — building the new Laravel application alongside the old one, migrating feature by feature. This let the district keep operating without downtime while I systematically replaced every piece of the legacy system.</p><h2>Technical Highlights</h2><ul><li>Migrated from CakePHP 2.x to Laravel 10</li><li>Role-based access control for administrators, teachers, parents, and students</li><li>Real-time grade notifications via Laravel Echo and Pusher</li><li>PDF report card generation with DomPDF</li><li>Comprehensive test suite written during migration</li><li>MySQL with complex reporting queries optimized for 15,000+ student records</li></ul><h2>Outcome</h2><p>Page load times dropped by 60%. Teacher satisfaction with the portal went up significantly. And the new codebase has full test coverage, making future changes safe and predictable.</p>",
                'url' => null,
                'github_url' => null,
                'tech_stack' => ['Laravel', 'MySQL', 'Pusher', 'DomPDF', 'Tailwind CSS', 'Alpine.js'],
                'is_featured' => true,
                'sort_order' => 3,
                'status' => 'published',
                'tags' => ['Laravel', 'PHP', 'Architecture'],
            ],
            [
                'title' => 'Laravel Action Generator',
                'slug' => 'laravel-action-generator',
                'description' => 'An open-source Artisan command that scaffolds action classes with tests. Because every project deserves clean, single-responsibility actions.',
                'content' => "<h2>Why Actions?</h2><p>I use action classes in every Laravel project. They keep controllers thin, make business logic reusable, and are dead simple to test. But scaffolding them by hand gets tedious — creating the class, the test, wiring up the namespace.</p><h2>The Package</h2><p>This package adds a single Artisan command: <code>php artisan make:action</code>. It generates a clean action class with an <code>execute</code> method and an accompanying Pest test file. Supports customizable stubs and namespace configuration.</p><h2>Technical Highlights</h2><ul><li>Published on Packagist with full Composer support</li><li>Customizable stubs for action and test templates</li><li>Supports nested namespaces (e.g., <code>make:action Users/CreateUser</code>)</li><li>Config file for default paths and test framework preference</li><li>100% test coverage with Pest</li></ul>",
                'url' => null,
                'github_url' => 'https://github.com/JeffreyDavidson/laravel-action-generator',
                'tech_stack' => ['Laravel', 'PHP', 'Pest', 'Composer'],
                'is_featured' => false,
                'sort_order' => 4,
                'status' => 'published',
                'tags' => ['Laravel', 'PHP', 'Testing'],
            ],
            [
                'title' => 'Flavor Fleet',
                'slug' => 'flavor-fleet',
                'description' => 'A restaurant ordering and delivery management SaaS. Multi-tenant architecture with real-time order tracking and driver dispatch.',
                'content' => "<h2>The Concept</h2><p>Flavor Fleet was a SaaS experiment — a white-label ordering platform that restaurants could sign up for and have their own branded ordering experience without building anything custom. Think DoorDash meets Shopify for local restaurants.</p><h2>Architecture</h2><p>The multi-tenant setup was the most interesting challenge. Each restaurant gets its own subdomain with customizable branding, but shares the underlying infrastructure. Orders flow through a real-time pipeline from customer to kitchen to driver.</p><h2>Technical Highlights</h2><ul><li>Multi-tenant architecture using Stancl/Tenancy</li><li>Real-time order tracking with Laravel WebSockets</li><li>Stripe Connect for split payments between platform and restaurants</li><li>Driver dispatch algorithm based on proximity and availability</li><li>Queue-driven order processing with retry logic</li><li>Admin dashboard for platform-wide analytics</li></ul><h2>Status</h2><p>This was a learning project that taught me a ton about multi-tenancy, payment splitting, and real-time systems. It's not currently active, but the codebase is some of my best architectural work.</p>",
                'url' => null,
                'github_url' => 'https://github.com/JeffreyDavidson/flavor-fleet',
                'tech_stack' => ['Laravel', 'Livewire', 'WebSockets', 'Stripe', 'Redis', 'MySQL', 'Tailwind CSS'],
                'is_featured' => false,
                'sort_order' => 5,
                'status' => 'published',
                'tags' => ['Laravel', 'PHP', 'Architecture'],
            ],
            [
                'title' => 'Church Connect',
                'slug' => 'church-connect',
                'description' => 'A church management system for small to mid-size congregations. Member directory, event calendar, volunteer scheduling, and donation tracking.',
                'content' => "<h2>Built for My Church</h2><p>Our ELCA Lutheran congregation was using a patchwork of spreadsheets, SignUpGenius, and a clunky legacy system for member management. I volunteered to build something better.</p><h2>Features</h2><p>Church Connect handles the core needs of a small church: a searchable member directory with family grouping, an event calendar with RSVP tracking, volunteer scheduling for services and events, and integration with Stripe for online donations.</p><h2>Technical Highlights</h2><ul><li>Laravel with Filament admin for church staff</li><li>Family/household grouping with relationship tracking</li><li>Recurring event support with exception handling</li><li>Stripe integration for one-time and recurring donations</li><li>Email notifications for upcoming volunteer assignments</li><li>Privacy-conscious design — members control their own visibility</li></ul><h2>Impact</h2><p>The church office went from spending hours on administrative tasks to having everything in one place. Volunteer no-shows dropped significantly once people started getting automated reminders.</p>",
                'url' => null,
                'github_url' => null,
                'tech_stack' => ['Laravel', 'Filament', 'Stripe', 'MySQL', 'Tailwind CSS', 'Alpine.js'],
                'is_featured' => false,
                'sort_order' => 6,
                'status' => 'published',
                'tags' => ['Laravel', 'PHP'],
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
