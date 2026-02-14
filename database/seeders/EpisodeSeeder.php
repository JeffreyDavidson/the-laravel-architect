<?php

namespace Database\Seeders;

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class EpisodeSeeder extends Seeder
{
    public function run(): void
    {
        $coffee = Podcast::where('slug', 'coffee-with-the-laravel-architect')->first();
        $cloudy = Podcast::where('slug', 'embracing-cloudy-days')->first();

        // Create tags
        $tags = collect([
            ['name' => 'Laravel', 'slug' => 'laravel'],
            ['name' => 'PHP', 'slug' => 'php'],
            ['name' => 'Testing', 'slug' => 'testing'],
            ['name' => 'Architecture', 'slug' => 'architecture'],
            ['name' => 'Eloquent', 'slug' => 'eloquent'],
            ['name' => 'Deployment', 'slug' => 'deployment'],
            ['name' => 'Career', 'slug' => 'career'],
            ['name' => 'Mental Health', 'slug' => 'mental-health'],
            ['name' => 'Parenting', 'slug' => 'parenting'],
            ['name' => 'Burnout', 'slug' => 'burnout'],
            ['name' => 'Faith', 'slug' => 'faith'],
            ['name' => 'Relationships', 'slug' => 'relationships'],
            ['name' => 'Remote Work', 'slug' => 'remote-work'],
            ['name' => 'Anxiety', 'slug' => 'anxiety'],
            ['name' => 'Resilience', 'slug' => 'resilience'],
        ])->mapWithKeys(function ($t) {
            $tag = Tag::firstOrCreate(['slug' => $t['slug']], $t);
            return [$t['slug'] => $tag];
        });

        if ($coffee) {
            $this->seedCoffeeEpisodes($coffee, $tags);
        }

        if ($cloudy) {
            $this->seedCloudyEpisodes($cloudy, $tags);
        }
    }

    private function seedCoffeeEpisodes(Podcast $podcast, $tags): void
    {
        $episodes = [
            [
                'title' => 'Why Laravel? A Love Letter to the Framework',
                'slug' => 'why-laravel-a-love-letter-to-the-framework',
                'episode_number' => 1,
                'description' => "In the debut episode, Jeffrey talks about his journey from vanilla PHP to Laravel, why he's stuck with it for over a decade, and what makes it different from every other framework he's tried.",
                'duration_minutes' => 38,
                'published_at' => '2026-01-06 09:00:00',
                'show_notes' => '<h3>Topics Covered</h3>
<ul>
<li>The early days of PHP 4 and spaghetti code</li>
<li>How Jeffrey discovered Laravel through a Laracasts video in 2014</li>
<li>Why developer experience matters more than raw performance benchmarks</li>
<li>The Laravel ecosystem: Forge, Vapor, Nova, Horizon, and beyond</li>
<li>Comparing Laravel to Symfony, CodeIgniter, and CakePHP</li>
</ul>

<h3>Resources Mentioned</h3>
<ul>
<li><a href="https://laravel.com">Laravel Official Site</a></li>
<li><a href="https://laracasts.com">Laracasts</a> — Jeffrey\'s go-to learning resource</li>
<li><a href="https://laravel-news.com">Laravel News</a></li>
</ul>

<h3>Timestamps</h3>
<ul>
<li>00:00 — Introduction and why this podcast exists</li>
<li>05:30 — The pre-Laravel dark ages</li>
<li>14:15 — First impressions of Laravel\'s Eloquent ORM</li>
<li>22:40 — The ecosystem that keeps you in</li>
<li>31:00 — Advice for developers choosing a framework today</li>
</ul>',
                'tags' => ['laravel', 'php', 'career'],
            ],
            [
                'title' => 'The Architecture Nobody Talks About',
                'slug' => 'the-architecture-nobody-talks-about',
                'episode_number' => 2,
                'description' => "Everyone talks about clean architecture in the abstract. Jeffrey breaks down what it actually looks like in a real Laravel project — actions, DTOs, form requests, and where most developers overcomplicate things.",
                'duration_minutes' => 45,
                'published_at' => '2026-01-13 09:00:00',
                'show_notes' => '<h3>Topics Covered</h3>
<ul>
<li>Why "clean architecture" is often cargo-culted in Laravel apps</li>
<li>Actions vs. service classes — when each makes sense</li>
<li>Data Transfer Objects (DTOs) and when they\'re overkill</li>
<li>Form Requests as your first line of validation defense</li>
<li>The danger of premature abstraction</li>
</ul>

<h3>Resources Mentioned</h3>
<ul>
<li><a href="https://spatie.be/docs/laravel-data">Spatie Laravel Data</a> — DTOs done right</li>
<li><a href="https://stitcher.io">Brent Roose\'s Blog</a> — Excellent posts on PHP architecture</li>
<li><a href="https://laravel.com/docs/actions">Laravel Actions Pattern</a></li>
</ul>

<h3>Timestamps</h3>
<ul>
<li>00:00 — What does "architecture" even mean?</li>
<li>08:20 — The Action pattern in practice</li>
<li>18:45 — DTOs: love them or leave them</li>
<li>29:10 — Form Requests as architectural boundaries</li>
<li>38:30 — Keeping it simple: Jeffrey\'s golden rule</li>
</ul>',
                'tags' => ['laravel', 'architecture', 'php'],
            ],
            [
                'title' => 'Testing Is Not Optional',
                'slug' => 'testing-is-not-optional',
                'episode_number' => 3,
                'description' => "Jeffrey's three-suite testing philosophy: Feature, Integration, and Unit. What goes where, why Unit means zero dependencies, and how Adam Wathan's TDD course changed everything.",
                'duration_minutes' => 42,
                'published_at' => '2026-01-20 09:00:00',
                'show_notes' => '<h3>Topics Covered</h3>
<ul>
<li>The three-suite testing philosophy: Feature, Integration, Unit</li>
<li>Why most "unit tests" are actually integration tests</li>
<li>Test-Driven Development in practice — not just theory</li>
<li>Pest PHP vs. PHPUnit: Jeffrey\'s take</li>
<li>What to test and what to skip</li>
</ul>

<h3>Resources Mentioned</h3>
<ul>
<li><a href="https://pestphp.com">Pest PHP</a> — Elegant testing framework</li>
<li><a href="https://course.testdrivenlaravel.com">Test-Driven Laravel</a> by Adam Wathan</li>
<li><a href="https://laravel.com/docs/testing">Laravel Testing Docs</a></li>
</ul>

<h3>Timestamps</h3>
<ul>
<li>00:00 — Why Jeffrey almost skipped testing entirely</li>
<li>07:15 — Defining the three test suites</li>
<li>16:30 — Writing your first Feature test</li>
<li>25:00 — The TDD mindset shift</li>
<li>35:45 — Pest PHP: worth the switch?</li>
</ul>',
                'tags' => ['testing', 'laravel', 'php'],
            ],
            [
                'title' => 'Legacy Code Isn\'t a Dirty Word',
                'slug' => 'legacy-code-isnt-a-dirty-word',
                'episode_number' => 4,
                'description' => "After migrating codebases from CodeIgniter, Yii2, CakePHP, and ExpressionEngine into Laravel, Jeffrey shares the patterns that make legacy rewrites survivable — and even enjoyable.",
                'duration_minutes' => 51,
                'published_at' => '2026-01-27 09:00:00',
                'show_notes' => '<h3>Topics Covered</h3>
<ul>
<li>The strangler fig pattern for gradual migrations</li>
<li>Migrating from CodeIgniter to Laravel: a war story</li>
<li>Database-first vs. code-first migration strategies</li>
<li>When to rewrite and when to wrap</li>
<li>Keeping the business running during a migration</li>
</ul>

<h3>Resources Mentioned</h3>
<ul>
<li><a href="https://martinfowler.com/bliki/StranglerFigApplication.html">Strangler Fig Pattern</a> — Martin Fowler</li>
<li><a href="https://www.goodreads.com/book/show/44919.Working_Effectively_with_Legacy_Code">Working Effectively with Legacy Code</a> by Michael Feathers</li>
</ul>

<h3>Timestamps</h3>
<ul>
<li>00:00 — Why legacy code gets a bad reputation</li>
<li>10:20 — The strangler fig approach</li>
<li>22:00 — CodeIgniter to Laravel: the full story</li>
<li>35:15 — Database migration strategies</li>
<li>44:30 — Lessons learned across four major migrations</li>
</ul>',
                'tags' => ['laravel', 'architecture', 'php'],
            ],
            [
                'title' => 'Filament Changed How I Build Admin Panels',
                'slug' => 'filament-changed-how-i-build-admin-panels',
                'episode_number' => 5,
                'description' => "A deep dive into Filament — why Jeffrey chose it over Nova, how it handles forms and tables, and the moment he realized he'd never hand-roll another admin panel again.",
                'duration_minutes' => 36,
                'published_at' => '2026-02-03 09:00:00',
                'show_notes' => '<h3>Topics Covered</h3>
<ul>
<li>Filament vs. Nova vs. hand-rolled admin panels</li>
<li>Building forms with Filament\'s declarative API</li>
<li>Table filters, bulk actions, and custom columns</li>
<li>Extending Filament with custom pages and widgets</li>
<li>Performance considerations for large datasets</li>
</ul>

<h3>Resources Mentioned</h3>
<ul>
<li><a href="https://filamentphp.com">Filament PHP</a></li>
<li><a href="https://filamentphp.com/plugins">Filament Plugin Ecosystem</a></li>
<li><a href="https://nova.laravel.com">Laravel Nova</a> — for comparison</li>
</ul>

<h3>Timestamps</h3>
<ul>
<li>00:00 — The admin panel problem</li>
<li>06:45 — First encounter with Filament</li>
<li>14:20 — Building a resource from scratch</li>
<li>23:00 — Custom widgets and dashboard pages</li>
<li>30:15 — Why Jeffrey chose Filament over Nova</li>
</ul>',
                'tags' => ['laravel', 'php', 'architecture'],
            ],
            [
                'title' => 'Eloquent: The Good, The Bad, and The N+1',
                'slug' => 'eloquent-the-good-the-bad-and-the-n1',
                'episode_number' => 6,
                'description' => "Eloquent is beautiful until it isn't. Jeffrey talks about the patterns that keep Eloquent clean, the traps that kill performance, and when it's time to reach for the query builder instead.",
                'duration_minutes' => 40,
                'published_at' => '2026-02-10 09:00:00',
                'show_notes' => '<h3>Topics Covered</h3>
<ul>
<li>Eager loading and the N+1 problem explained</li>
<li>Query scopes for reusable, readable queries</li>
<li>When to use the query builder instead of Eloquent</li>
<li>Model events and observers: helpful or hidden danger?</li>
<li>Eloquent performance profiling with Laravel Debugbar</li>
</ul>

<h3>Resources Mentioned</h3>
<ul>
<li><a href="https://laravel.com/docs/eloquent-relationships">Eloquent Relationships Docs</a></li>
<li><a href="https://github.com/barryvdh/laravel-debugbar">Laravel Debugbar</a></li>
<li><a href="https://beyondco.de/docs/laravel-query-detector">Laravel Query Detector</a> — catch N+1 automatically</li>
</ul>

<h3>Timestamps</h3>
<ul>
<li>00:00 — Why Jeffrey loves and fears Eloquent</li>
<li>08:30 — The N+1 trap with real examples</li>
<li>17:45 — Query scopes in practice</li>
<li>26:00 — When the query builder wins</li>
<li>34:20 — Profiling and debugging Eloquent queries</li>
</ul>',
                'tags' => ['eloquent', 'laravel', 'php'],
            ],
            [
                'title' => 'Deploying Laravel Without Losing Sleep',
                'slug' => 'deploying-laravel-without-losing-sleep',
                'episode_number' => 7,
                'description' => "From Forge to Envoyer to GitHub Actions — Jeffrey walks through his deployment setup, zero-downtime strategies, and the checklist he runs before every production push.",
                'duration_minutes' => 34,
                'published_at' => '2026-02-17 09:00:00',
                'show_notes' => '<h3>Topics Covered</h3>
<ul>
<li>Laravel Forge for server provisioning and management</li>
<li>Zero-downtime deployments with Envoyer</li>
<li>GitHub Actions CI/CD pipeline walkthrough</li>
<li>The pre-deployment checklist Jeffrey runs every time</li>
<li>Rollback strategies when things go sideways</li>
</ul>

<h3>Resources Mentioned</h3>
<ul>
<li><a href="https://forge.laravel.com">Laravel Forge</a></li>
<li><a href="https://envoyer.io">Envoyer</a> — zero-downtime deployments</li>
<li><a href="https://docs.github.com/en/actions">GitHub Actions Documentation</a></li>
</ul>

<h3>Timestamps</h3>
<ul>
<li>00:00 — The anxiety of deploying to production</li>
<li>06:00 — Forge setup walkthrough</li>
<li>14:30 — Zero-downtime with Envoyer</li>
<li>22:15 — GitHub Actions CI pipeline</li>
<li>28:45 — The deployment checklist</li>
</ul>',
                'tags' => ['deployment', 'laravel', 'php'],
            ],
            [
                'title' => 'The Self-Taught Developer\'s Advantage',
                'slug' => 'the-self-taught-developers-advantage',
                'episode_number' => 8,
                'description' => "Jeffrey started self-taught, went to Full Sail, and came out the other side. He talks about what formal education gave him, what it didn't, and why self-taught developers have an edge they don't realize.",
                'duration_minutes' => 44,
                'published_at' => '2026-02-24 09:00:00',
                'show_notes' => '<h3>Topics Covered</h3>
<ul>
<li>The self-taught path: from curiosity to career</li>
<li>What Full Sail University taught (and didn\'t teach)</li>
<li>Why self-taught developers are naturally better problem-solvers</li>
<li>Building a portfolio that speaks louder than a degree</li>
<li>The imposter syndrome trap and how to escape it</li>
</ul>

<h3>Resources Mentioned</h3>
<ul>
<li><a href="https://laracasts.com">Laracasts</a> — where Jeffrey learned most of what he knows</li>
<li><a href="https://www.freecodecamp.org">freeCodeCamp</a></li>
<li><a href="https://www.theodinproject.com">The Odin Project</a></li>
</ul>

<h3>Timestamps</h3>
<ul>
<li>00:00 — Jeffrey\'s learning journey timeline</li>
<li>09:30 — The Full Sail experience</li>
<li>19:00 — Self-taught advantages in the job market</li>
<li>28:45 — Building a portfolio that gets interviews</li>
<li>37:20 — Dealing with imposter syndrome</li>
</ul>',
                'tags' => ['career', 'php', 'laravel'],
            ],
            [
                'title' => 'APIs That Don\'t Make People Cry',
                'slug' => 'apis-that-dont-make-people-cry',
                'episode_number' => 9,
                'description' => "Designing APIs that other developers actually want to use. Resource classes, consistent error handling, versioning strategies, and why your API is a product whether you think of it that way or not.",
                'duration_minutes' => 39,
                'published_at' => '2026-03-03 09:00:00',
                'show_notes' => '<h3>Topics Covered</h3>
<ul>
<li>API Resource classes for consistent JSON responses</li>
<li>Error handling that actually helps consumers debug</li>
<li>Versioning strategies: URI vs. header-based</li>
<li>Authentication with Sanctum vs. Passport</li>
<li>Documentation with Scribe and OpenAPI specs</li>
</ul>

<h3>Resources Mentioned</h3>
<ul>
<li><a href="https://laravel.com/docs/eloquent-resources">API Resources Docs</a></li>
<li><a href="https://laravel.com/docs/sanctum">Laravel Sanctum</a></li>
<li><a href="https://scribe.knuckles.wtf">Scribe</a> — API documentation generator</li>
</ul>

<h3>Timestamps</h3>
<ul>
<li>00:00 — What makes a great API?</li>
<li>07:00 — Resource classes deep dive</li>
<li>16:30 — Error handling patterns</li>
<li>25:15 — Versioning your API</li>
<li>33:00 — Auto-generating documentation</li>
</ul>',
                'tags' => ['laravel', 'architecture', 'php'],
            ],
            [
                'title' => 'Building in Public: Why I Started This Podcast',
                'slug' => 'building-in-public-why-i-started-this-podcast',
                'episode_number' => 10,
                'description' => "Jeffrey gets meta and talks about why he's building a content platform, the fear of putting yourself out there, and what he hopes this podcast becomes over time.",
                'duration_minutes' => 32,
                'published_at' => '2026-03-10 09:00:00',
                'show_notes' => '<h3>Topics Covered</h3>
<ul>
<li>The decision to build in public and share the journey</li>
<li>Overcoming the fear of judgment and criticism</li>
<li>Building a content platform with Laravel (meta!)</li>
<li>The tech stack behind The Laravel Architect website</li>
<li>Goals for the podcast and community</li>
</ul>

<h3>Resources Mentioned</h3>
<ul>
<li><a href="https://thelaravelarchitect.com">The Laravel Architect</a> — this very site</li>
<li><a href="https://twitter.com/thelaravelarch">@thelaravelarch on X</a></li>
<li><a href="https://buildingpublic.com">Building in Public</a> — community and resources</li>
</ul>

<h3>Timestamps</h3>
<ul>
<li>00:00 — Why go public with the process?</li>
<li>06:30 — The fear of being seen</li>
<li>13:45 — Tech stack breakdown: Laravel, Filament, Tailwind</li>
<li>21:00 — Content strategy and consistency</li>
<li>27:30 — Where this is all headed</li>
</ul>',
                'tags' => ['career', 'laravel', 'architecture'],
            ],
        ];

        foreach ($episodes as $episodeData) {
            $tagSlugs = $episodeData['tags'] ?? [];
            unset($episodeData['tags']);

            $episode = Episode::create(array_merge($episodeData, [
                'podcast_id' => $podcast->id,
                'season_number' => 1,
                'status' => 'published',
            ]));

            $tagIds = $tags->only($tagSlugs)->pluck('id')->toArray();
            $episode->tags()->attach($tagIds);
        }
    }

    private function seedCloudyEpisodes(Podcast $podcast, $tags): void
    {
        $episodes = [
            [
                'title' => 'Welcome to the Clouds',
                'slug' => 'welcome-to-the-clouds',
                'episode_number' => 1,
                'description' => "The one where Jeffrey explains why he's starting a mental health podcast, what he hopes it becomes, and why pretending everything is fine helps exactly nobody.",
                'duration_minutes' => 28,
                'published_at' => '2026-01-08 09:00:00',
                'show_notes' => '<h3>Topics Covered</h3>
<ul>
<li>Why Jeffrey decided to start a mental health podcast</li>
<li>The stigma around men talking about their feelings</li>
<li>What "embracing cloudy days" actually means</li>
<li>Setting expectations: this isn\'t therapy, it\'s conversation</li>
</ul>

<h3>Resources Mentioned</h3>
<ul>
<li><a href="https://www.nami.org">NAMI</a> — National Alliance on Mental Illness</li>
<li><a href="https://www.betterhelp.com">BetterHelp</a> — Online therapy platform</li>
<li><a href="https://988lifeline.org">988 Suicide & Crisis Lifeline</a></li>
</ul>

<h3>Timestamps</h3>
<ul>
<li>00:00 — Why this podcast needs to exist</li>
<li>06:30 — The mask we all wear</li>
<li>14:00 — What you can expect from this show</li>
<li>21:45 — An invitation to join the conversation</li>
</ul>',
                'tags' => ['mental-health', 'resilience', 'career'],
            ],
            [
                'title' => 'The Weight of Being "Fine"',
                'slug' => 'the-weight-of-being-fine',
                'episode_number' => 2,
                'description' => "We all say we're fine when we're not. Jeffrey talks about the cost of that lie — at work, at home, and in your own head. And what happens when you finally stop saying it.",
                'duration_minutes' => 35,
                'published_at' => '2026-01-15 09:00:00',
                'show_notes' => '<h3>Topics Covered</h3>
<ul>
<li>The automatic "I\'m fine" response and its hidden cost</li>
<li>How suppressing emotions affects your work and relationships</li>
<li>The moment Jeffrey stopped pretending</li>
<li>Practical steps toward honest communication</li>
</ul>

<h3>Resources Mentioned</h3>
<ul>
<li><a href="https://www.goodreads.com/book/show/34890015-it-s-ok-that-you-re-not-ok">It\'s OK That You\'re Not OK</a> by Megan Devine</li>
<li><a href="https://brenebrown.com/books-audio/">Brené Brown\'s Books</a></li>
</ul>

<h3>Timestamps</h3>
<ul>
<li>00:00 — The most common lie we tell</li>
<li>08:15 — What "fine" is really costing you</li>
<li>17:30 — Jeffrey\'s breaking point</li>
<li>26:00 — Learning to say "I\'m not okay"</li>
</ul>',
                'tags' => ['mental-health', 'anxiety', 'relationships'],
            ],
            [
                'title' => 'Parenting When the Playbook Doesn\'t Exist',
                'slug' => 'parenting-when-the-playbook-doesnt-exist',
                'episode_number' => 3,
                'description' => "Raising a nonverbal autistic daughter means there's no manual. Jeffrey talks about the beautiful chaos of parenting Viola, the grief nobody warns you about, and the joy that blindsides you when you least expect it.",
                'duration_minutes' => 42,
                'published_at' => '2026-01-22 09:00:00',
                'show_notes' => '<h3>Topics Covered</h3>
<ul>
<li>The day Jeffrey and his wife got the diagnosis</li>
<li>Grieving the life you imagined vs. embracing the one you have</li>
<li>Nonverbal communication: learning Viola\'s language</li>
<li>The therapy marathon: ABA, speech, occupational</li>
<li>Moments of unexpected, overwhelming joy</li>
</ul>

<h3>Resources Mentioned</h3>
<ul>
<li><a href="https://www.autismspeaks.org">Autism Speaks</a></li>
<li><a href="https://www.goodreads.com/book/show/35068761-uniquely-human">Uniquely Human</a> by Barry Prizant</li>
<li><a href="https://childmind.org">Child Mind Institute</a></li>
</ul>

<h3>Timestamps</h3>
<ul>
<li>00:00 — Meet Viola</li>
<li>09:00 — The diagnosis day</li>
<li>18:30 — The grief no one talks about</li>
<li>27:15 — Learning to communicate without words</li>
<li>35:00 — The joy that catches you off guard</li>
</ul>',
                'tags' => ['parenting', 'mental-health', 'resilience'],
            ],
            [
                'title' => 'Burnout Is Not a Badge of Honor',
                'slug' => 'burnout-is-not-a-badge-of-honor',
                'episode_number' => 4,
                'description' => "The tech industry glorifies hustle culture. Jeffrey pushes back — talking about the burnout he's experienced, the warning signs he missed, and why rest isn't the opposite of productivity.",
                'duration_minutes' => 37,
                'published_at' => '2026-01-29 09:00:00',
                'show_notes' => '<h3>Topics Covered</h3>
<ul>
<li>The hustle culture trap in the tech industry</li>
<li>Warning signs of burnout that Jeffrey missed</li>
<li>Physical symptoms: the body keeps the score</li>
<li>Rest as a productivity strategy, not its enemy</li>
<li>Setting boundaries with work (especially remote work)</li>
</ul>

<h3>Resources Mentioned</h3>
<ul>
<li><a href="https://www.goodreads.com/book/show/36141137-burnout">Burnout</a> by Emily and Amelia Nagoski</li>
<li><a href="https://www.goodreads.com/book/show/375802.The_Body_Keeps_the_Score">The Body Keeps the Score</a> by Bessel van der Kolk</li>
</ul>

<h3>Timestamps</h3>
<ul>
<li>00:00 — The glorification of hustle</li>
<li>07:45 — Jeffrey\'s burnout story</li>
<li>16:00 — Warning signs you\'re ignoring</li>
<li>24:30 — Rest is not laziness</li>
<li>31:00 — Practical boundary-setting strategies</li>
</ul>',
                'tags' => ['burnout', 'mental-health', 'remote-work'],
            ],
            [
                'title' => 'Marriage Under Pressure',
                'slug' => 'marriage-under-pressure',
                'episode_number' => 5,
                'description' => "Being a spouse when life is heavy. Jeffrey talks honestly about what it's like to maintain a marriage when you're both exhausted, both stressed, and both trying to hold it together for your kid.",
                'duration_minutes' => 40,
                'published_at' => '2026-02-05 09:00:00',
                'show_notes' => '<h3>Topics Covered</h3>
<ul>
<li>Marriage under the weight of special needs parenting</li>
<li>The resentment cycle and how to break it</li>
<li>Finding time for each other when there\'s none left</li>
<li>Communicating needs without keeping score</li>
<li>Why asking for help isn\'t weakness</li>
</ul>

<h3>Resources Mentioned</h3>
<ul>
<li><a href="https://www.gottman.com">The Gottman Institute</a></li>
<li><a href="https://www.goodreads.com/book/show/849.The_Seven_Principles_for_Making_Marriage_Work">The Seven Principles for Making Marriage Work</a> by John Gottman</li>
</ul>

<h3>Timestamps</h3>
<ul>
<li>00:00 — The pressure cooker of life</li>
<li>08:00 — The resentment trap</li>
<li>17:30 — Micro-dates and stolen moments</li>
<li>27:00 — Communication when you\'re both depleted</li>
<li>34:15 — Asking for help together</li>
</ul>',
                'tags' => ['relationships', 'mental-health', 'parenting'],
            ],
            [
                'title' => 'The Loneliness of Remote Work',
                'slug' => 'the-loneliness-of-remote-work',
                'episode_number' => 6,
                'description' => "Working from home sounds like a dream until the walls start closing in. Jeffrey explores the isolation that comes with remote work and the small things that keep him connected.",
                'duration_minutes' => 33,
                'published_at' => '2026-02-12 09:00:00',
                'show_notes' => '<h3>Topics Covered</h3>
<ul>
<li>The remote work paradox: freedom and isolation</li>
<li>How loneliness sneaks up on you over months</li>
<li>The difference between being alone and being lonely</li>
<li>Strategies Jeffrey uses to stay connected</li>
<li>Building community in a distributed world</li>
</ul>

<h3>Resources Mentioned</h3>
<ul>
<li><a href="https://www.goodreads.com/book/show/48930191-together">Together</a> by Vivek Murthy</li>
<li><a href="https://discord.gg/laravel">Laravel Discord</a> — community that keeps Jeffrey sane</li>
</ul>

<h3>Timestamps</h3>
<ul>
<li>00:00 — The dream of working from home</li>
<li>06:30 — When the walls close in</li>
<li>14:00 — Alone vs. lonely</li>
<li>21:15 — Jeffrey\'s connection strategies</li>
<li>27:30 — Building your own community</li>
</ul>',
                'tags' => ['remote-work', 'mental-health', 'anxiety'],
            ],
            [
                'title' => 'Anxiety and the Keyboard',
                'slug' => 'anxiety-and-the-keyboard',
                'episode_number' => 7,
                'description' => "That feeling before you push to production. The dread before a code review. Jeffrey talks about anxiety as a developer — where it shows up, how it disguises itself, and what helps.",
                'duration_minutes' => 36,
                'published_at' => '2026-02-19 09:00:00',
                'show_notes' => '<h3>Topics Covered</h3>
<ul>
<li>Anxiety triggers specific to software development</li>
<li>The deploy dread and code review fear</li>
<li>Imposter syndrome as anxiety in disguise</li>
<li>Physical anxiety symptoms at the desk</li>
<li>Coping strategies that actually work for developers</li>
</ul>

<h3>Resources Mentioned</h3>
<ul>
<li><a href="https://www.goodreads.com/book/show/52036.Feeling_Good">Feeling Good</a> by David Burns — CBT classic</li>
<li><a href="https://headspace.com">Headspace</a> — meditation app Jeffrey uses</li>
<li><a href="https://www.anxietycanada.com">Anxiety Canada</a> — free resources and tools</li>
</ul>

<h3>Timestamps</h3>
<ul>
<li>00:00 — The deploy button and dread</li>
<li>07:00 — Code review anxiety</li>
<li>15:30 — Imposter syndrome as anxiety</li>
<li>23:00 — Physical symptoms at the desk</li>
<li>30:00 — What actually helps</li>
</ul>',
                'tags' => ['anxiety', 'mental-health', 'career'],
            ],
            [
                'title' => 'Finding God in the Mess',
                'slug' => 'finding-god-in-the-mess',
                'episode_number' => 8,
                'description' => "Jeffrey's faith journey as a Lutheran and how it intersects with the hard stuff — doubt, unanswered prayers, and finding meaning when life doesn't make sense.",
                'duration_minutes' => 44,
                'published_at' => '2026-02-26 09:00:00',
                'show_notes' => '<h3>Topics Covered</h3>
<ul>
<li>Growing up Lutheran and what faith means to Jeffrey today</li>
<li>Wrestling with doubt — and why that\'s okay</li>
<li>Unanswered prayers and the silence of God</li>
<li>Finding meaning in suffering (without clichés)</li>
<li>Faith as a messy, honest relationship</li>
</ul>

<h3>Resources Mentioned</h3>
<ul>
<li><a href="https://www.goodreads.com/book/show/25814.A_Grief_Observed">A Grief Observed</a> by C.S. Lewis</li>
<li><a href="https://www.goodreads.com/book/show/15818.When_Bad_Things_Happen_to_Good_People">When Bad Things Happen to Good People</a> by Harold Kushner</li>
</ul>

<h3>Timestamps</h3>
<ul>
<li>00:00 — Faith and the tech world</li>
<li>09:30 — Jeffrey\'s faith journey</li>
<li>19:00 — Doubt as part of the process</li>
<li>28:15 — When prayers go unanswered</li>
<li>37:00 — Finding meaning without easy answers</li>
</ul>',
                'tags' => ['faith', 'mental-health', 'resilience'],
            ],
            [
                'title' => 'The Things We Don\'t Say Out Loud',
                'slug' => 'the-things-we-dont-say-out-loud',
                'episode_number' => 9,
                'description' => "There are thoughts every parent of a special needs child has that they never say out loud. Jeffrey says some of them. Raw, honest, and necessary.",
                'duration_minutes' => 38,
                'published_at' => '2026-03-05 09:00:00',
                'show_notes' => '<h3>Topics Covered</h3>
<ul>
<li>The unspoken thoughts of special needs parents</li>
<li>Guilt about having those thoughts in the first place</li>
<li>The loneliness of a journey others can\'t understand</li>
<li>Why vulnerability is the antidote to shame</li>
<li>Permission to feel everything you feel</li>
</ul>

<h3>Resources Mentioned</h3>
<ul>
<li><a href="https://www.goodreads.com/book/show/13588356-daring-greatly">Daring Greatly</a> by Brené Brown</li>
<li><a href="https://childmind.org/article/special-needs-parenting/">Child Mind Institute: Special Needs Parenting</a></li>
</ul>

<h3>Timestamps</h3>
<ul>
<li>00:00 — The thoughts no one admits to</li>
<li>08:00 — Guilt and shame spiral</li>
<li>17:30 — You\'re not alone in this</li>
<li>26:00 — Vulnerability as medicine</li>
<li>33:00 — Giving yourself permission</li>
</ul>',
                'tags' => ['parenting', 'mental-health', 'anxiety'],
            ],
            [
                'title' => 'Small Wins and Why They Matter',
                'slug' => 'small-wins-and-why-they-matter',
                'episode_number' => 10,
                'description' => "When the big picture feels overwhelming, small wins keep you going. Jeffrey talks about celebrating the tiny victories — in code, in parenting, and in getting through the day.",
                'duration_minutes' => 31,
                'published_at' => '2026-03-12 09:00:00',
                'show_notes' => '<h3>Topics Covered</h3>
<ul>
<li>Why big goals paralyze and small wins energize</li>
<li>Celebrating progress in code: shipped features, fixed bugs, clean tests</li>
<li>Parenting wins: a new word, a good day, a moment of connection</li>
<li>The gratitude practice Jeffrey swears by</li>
<li>Building momentum one small win at a time</li>
</ul>

<h3>Resources Mentioned</h3>
<ul>
<li><a href="https://www.goodreads.com/book/show/40121378-atomic-habits">Atomic Habits</a> by James Clear</li>
<li><a href="https://www.goodreads.com/book/show/34507927-how-to-be-happy">The Happiness Advantage</a> by Shawn Achor</li>
</ul>

<h3>Timestamps</h3>
<ul>
<li>00:00 — When everything feels too big</li>
<li>06:00 — Small wins in code</li>
<li>13:30 — Small wins in parenting</li>
<li>20:00 — The gratitude practice</li>
<li>26:15 — Building momentum for tomorrow</li>
</ul>',
                'tags' => ['resilience', 'mental-health', 'parenting'],
            ],
        ];

        foreach ($episodes as $episodeData) {
            $tagSlugs = $episodeData['tags'] ?? [];
            unset($episodeData['tags']);

            $episode = Episode::create(array_merge($episodeData, [
                'podcast_id' => $podcast->id,
                'season_number' => 1,
                'status' => 'published',
            ]));

            $tagIds = $tags->only($tagSlugs)->pluck('id')->toArray();
            $episode->tags()->attach($tagIds);
        }
    }
}
