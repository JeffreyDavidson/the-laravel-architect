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
                'show_notes' => '### Topics Covered

- The early days of PHP 4 and spaghetti code
- How Jeffrey discovered Laravel through a Laracasts video in 2014
- Why developer experience matters more than raw performance benchmarks
- The Laravel ecosystem: Forge, Vapor, Nova, Horizon, and beyond
- Comparing Laravel to Symfony, CodeIgniter, and CakePHP

### Resources Mentioned

- [Laravel Official Site](https://laravel.com)
- [Laracasts](https://laracasts.com) — Jeffrey\'s go-to learning resource
- [Laravel News](https://laravel-news.com)

### Timestamps

- 00:00 — Introduction and why this podcast exists
- 05:30 — The pre-Laravel dark ages
- 14:15 — First impressions of Laravel\'s Eloquent ORM
- 22:40 — The ecosystem that keeps you in
- 31:00 — Advice for developers choosing a framework today',
                'tags' => ['laravel', 'php', 'career'],
            ],
            [
                'title' => 'The Architecture Nobody Talks About',
                'slug' => 'the-architecture-nobody-talks-about',
                'episode_number' => 2,
                'description' => 'Everyone talks about clean architecture in the abstract. Jeffrey breaks down what it actually looks like in a real Laravel project — actions, DTOs, form requests, and where most developers overcomplicate things.',
                'duration_minutes' => 45,
                'published_at' => '2026-01-13 09:00:00',
                'show_notes' => '### Topics Covered

- Why "clean architecture" is often cargo-culted in Laravel apps
- Actions vs. service classes — when each makes sense
- Data Transfer Objects (DTOs) and when they\'re overkill
- Form Requests as your first line of validation defense
- The danger of premature abstraction

### Resources Mentioned

- [Spatie Laravel Data](https://spatie.be/docs/laravel-data) — DTOs done right
- [Brent Roose\'s Blog](https://stitcher.io) — Excellent posts on PHP architecture
- [Laravel Actions Pattern](https://laravel.com/docs/actions)

### Timestamps

- 00:00 — What does "architecture" even mean?
- 08:20 — The Action pattern in practice
- 18:45 — DTOs: love them or leave them
- 29:10 — Form Requests as architectural boundaries
- 38:30 — Keeping it simple: Jeffrey\'s golden rule',
                'tags' => ['laravel', 'architecture', 'php'],
            ],
            [
                'title' => 'Testing Is Not Optional',
                'slug' => 'testing-is-not-optional',
                'episode_number' => 3,
                'description' => "Jeffrey's three-suite testing philosophy: Feature, Integration, and Unit. What goes where, why Unit means zero dependencies, and how Adam Wathan's TDD course changed everything.",
                'duration_minutes' => 42,
                'published_at' => '2026-01-20 09:00:00',
                'show_notes' => '### Topics Covered

- The three-suite testing philosophy: Feature, Integration, Unit
- Why most "unit tests" are actually integration tests
- Test-Driven Development in practice — not just theory
- Pest PHP vs. PHPUnit: Jeffrey\'s take
- What to test and what to skip

### Resources Mentioned

- [Pest PHP](https://pestphp.com) — Elegant testing framework
- [Test-Driven Laravel](https://course.testdrivenlaravel.com) by Adam Wathan
- [Laravel Testing Docs](https://laravel.com/docs/testing)

### Timestamps

- 00:00 — Why Jeffrey almost skipped testing entirely
- 07:15 — Defining the three test suites
- 16:30 — Writing your first Feature test
- 25:00 — The TDD mindset shift
- 35:45 — Pest PHP: worth the switch?',
                'tags' => ['testing', 'laravel', 'php'],
            ],
            [
                'title' => 'Legacy Code Isn\'t a Dirty Word',
                'slug' => 'legacy-code-isnt-a-dirty-word',
                'episode_number' => 4,
                'description' => 'After migrating codebases from CodeIgniter, Yii2, CakePHP, and ExpressionEngine into Laravel, Jeffrey shares the patterns that make legacy rewrites survivable — and even enjoyable.',
                'duration_minutes' => 51,
                'published_at' => '2026-01-27 09:00:00',
                'show_notes' => '### Topics Covered

- The strangler fig pattern for gradual migrations
- Migrating from CodeIgniter to Laravel: a war story
- Database-first vs. code-first migration strategies
- When to rewrite and when to wrap
- Keeping the business running during a migration

### Resources Mentioned

- [Strangler Fig Pattern](https://martinfowler.com/bliki/StranglerFigApplication.html) — Martin Fowler
- [Working Effectively with Legacy Code](https://www.goodreads.com/book/show/44919.Working_Effectively_with_Legacy_Code) by Michael Feathers

### Timestamps

- 00:00 — Why legacy code gets a bad reputation
- 10:20 — The strangler fig approach
- 22:00 — CodeIgniter to Laravel: the full story
- 35:15 — Database migration strategies
- 44:30 — Lessons learned across four major migrations',
                'tags' => ['laravel', 'architecture', 'php'],
            ],
            [
                'title' => 'Filament Changed How I Build Admin Panels',
                'slug' => 'filament-changed-how-i-build-admin-panels',
                'episode_number' => 5,
                'description' => "A deep dive into Filament — why Jeffrey chose it over Nova, how it handles forms and tables, and the moment he realized he'd never hand-roll another admin panel again.",
                'duration_minutes' => 36,
                'published_at' => '2026-02-03 09:00:00',
                'show_notes' => '### Topics Covered

- Filament vs. Nova vs. hand-rolled admin panels
- Building forms with Filament\'s declarative API
- Table filters, bulk actions, and custom columns
- Extending Filament with custom pages and widgets
- Performance considerations for large datasets

### Resources Mentioned

- [Filament PHP](https://filamentphp.com)
- [Filament Plugin Ecosystem](https://filamentphp.com/plugins)
- [Laravel Nova](https://nova.laravel.com) — for comparison

### Timestamps

- 00:00 — The admin panel problem
- 06:45 — First encounter with Filament
- 14:20 — Building a resource from scratch
- 23:00 — Custom widgets and dashboard pages
- 30:15 — Why Jeffrey chose Filament over Nova',
                'tags' => ['laravel', 'php', 'architecture'],
            ],
            [
                'title' => 'Eloquent: The Good, The Bad, and The N+1',
                'slug' => 'eloquent-the-good-the-bad-and-the-n1',
                'episode_number' => 6,
                'description' => "Eloquent is beautiful until it isn't. Jeffrey talks about the patterns that keep Eloquent clean, the traps that kill performance, and when it's time to reach for the query builder instead.",
                'duration_minutes' => 40,
                'published_at' => '2026-02-10 09:00:00',
                'show_notes' => '### Topics Covered

- Eager loading and the N+1 problem explained
- Query scopes for reusable, readable queries
- When to use the query builder instead of Eloquent
- Model events and observers: helpful or hidden danger?
- Eloquent performance profiling with Laravel Debugbar

### Resources Mentioned

- [Eloquent Relationships Docs](https://laravel.com/docs/eloquent-relationships)
- [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar)
- [Laravel Query Detector](https://beyondco.de/docs/laravel-query-detector) — catch N+1 automatically

### Timestamps

- 00:00 — Why Jeffrey loves and fears Eloquent
- 08:30 — The N+1 trap with real examples
- 17:45 — Query scopes in practice
- 26:00 — When the query builder wins
- 34:20 — Profiling and debugging Eloquent queries',
                'tags' => ['eloquent', 'laravel', 'php'],
            ],
            [
                'title' => 'Deploying Laravel Without Losing Sleep',
                'slug' => 'deploying-laravel-without-losing-sleep',
                'episode_number' => 7,
                'description' => 'From Forge to Envoyer to GitHub Actions — Jeffrey walks through his deployment setup, zero-downtime strategies, and the checklist he runs before every production push.',
                'duration_minutes' => 34,
                'published_at' => '2026-02-17 09:00:00',
                'show_notes' => '### Topics Covered

- Laravel Forge for server provisioning and management
- Zero-downtime deployments with Envoyer
- GitHub Actions CI/CD pipeline walkthrough
- The pre-deployment checklist Jeffrey runs every time
- Rollback strategies when things go sideways

### Resources Mentioned

- [Laravel Forge](https://forge.laravel.com)
- [Envoyer](https://envoyer.io) — zero-downtime deployments
- [GitHub Actions Documentation](https://docs.github.com/en/actions)

### Timestamps

- 00:00 — The anxiety of deploying to production
- 06:00 — Forge setup walkthrough
- 14:30 — Zero-downtime with Envoyer
- 22:15 — GitHub Actions CI pipeline
- 28:45 — The deployment checklist',
                'tags' => ['deployment', 'laravel', 'php'],
            ],
            [
                'title' => 'The Self-Taught Developer\'s Advantage',
                'slug' => 'the-self-taught-developers-advantage',
                'episode_number' => 8,
                'description' => "Jeffrey started self-taught, went to Full Sail, and came out the other side. He talks about what formal education gave him, what it didn't, and why self-taught developers have an edge they don't realize.",
                'duration_minutes' => 44,
                'published_at' => '2026-02-24 09:00:00',
                'show_notes' => '### Topics Covered

- The self-taught path: from curiosity to career
- What Full Sail University taught (and didn\'t teach)
- Why self-taught developers are naturally better problem-solvers
- Building a portfolio that speaks louder than a degree
- The imposter syndrome trap and how to escape it

### Resources Mentioned

- [Laracasts](https://laracasts.com) — where Jeffrey learned most of what he knows
- [freeCodeCamp](https://www.freecodecamp.org)
- [The Odin Project](https://www.theodinproject.com)

### Timestamps

- 00:00 — Jeffrey\'s learning journey timeline
- 09:30 — The Full Sail experience
- 19:00 — Self-taught advantages in the job market
- 28:45 — Building a portfolio that gets interviews
- 37:20 — Dealing with imposter syndrome',
                'tags' => ['career', 'php', 'laravel'],
            ],
            [
                'title' => 'APIs That Don\'t Make People Cry',
                'slug' => 'apis-that-dont-make-people-cry',
                'episode_number' => 9,
                'description' => 'Designing APIs that other developers actually want to use. Resource classes, consistent error handling, versioning strategies, and why your API is a product whether you think of it that way or not.',
                'duration_minutes' => 39,
                'published_at' => '2026-03-03 09:00:00',
                'show_notes' => '### Topics Covered

- API Resource classes for consistent JSON responses
- Error handling that actually helps consumers debug
- Versioning strategies: URI vs. header-based
- Authentication with Sanctum vs. Passport
- Documentation with Scribe and OpenAPI specs

### Resources Mentioned

- [API Resources Docs](https://laravel.com/docs/eloquent-resources)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [Scribe](https://scribe.knuckles.wtf) — API documentation generator

### Timestamps

- 00:00 — What makes a great API?
- 07:00 — Resource classes deep dive
- 16:30 — Error handling patterns
- 25:15 — Versioning your API
- 33:00 — Auto-generating documentation',
                'tags' => ['laravel', 'architecture', 'php'],
            ],
            [
                'title' => 'Building in Public: Why I Started This Podcast',
                'slug' => 'building-in-public-why-i-started-this-podcast',
                'episode_number' => 10,
                'description' => "Jeffrey gets meta and talks about why he's building a content platform, the fear of putting yourself out there, and what he hopes this podcast becomes over time.",
                'duration_minutes' => 32,
                'published_at' => '2026-03-10 09:00:00',
                'show_notes' => '### Topics Covered

- The decision to build in public and share the journey
- Overcoming the fear of judgment and criticism
- Building a content platform with Laravel (meta!)
- The tech stack behind The Laravel Architect website
- Goals for the podcast and community

### Resources Mentioned

- [The Laravel Architect](https://thelaravelarchitect.com) — this very site
- [@thelaravelarch on X](https://twitter.com/thelaravelarch)
- [Building in Public](https://buildingpublic.com) — community and resources

### Timestamps

- 00:00 — Why go public with the process?
- 06:30 — The fear of being seen
- 13:45 — Tech stack breakdown: Laravel, Filament, Tailwind
- 21:00 — Content strategy and consistency
- 27:30 — Where this is all headed',
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
                'show_notes' => '### Topics Covered

- Why Jeffrey decided to start a mental health podcast
- The stigma around men talking about their feelings
- What "embracing cloudy days" actually means
- Setting expectations: this isn\'t therapy, it\'s conversation

### Resources Mentioned

- [NAMI](https://www.nami.org) — National Alliance on Mental Illness
- [BetterHelp](https://www.betterhelp.com) — Online therapy platform
- [988 Suicide & Crisis Lifeline](https://988lifeline.org)

### Timestamps

- 00:00 — Why this podcast needs to exist
- 06:30 — The mask we all wear
- 14:00 — What you can expect from this show
- 21:45 — An invitation to join the conversation',
                'tags' => ['mental-health', 'resilience', 'career'],
            ],
            [
                'title' => 'The Weight of Being "Fine"',
                'slug' => 'the-weight-of-being-fine',
                'episode_number' => 2,
                'description' => "We all say we're fine when we're not. Jeffrey talks about the cost of that lie — at work, at home, and in your own head. And what happens when you finally stop saying it.",
                'duration_minutes' => 35,
                'published_at' => '2026-01-15 09:00:00',
                'show_notes' => '### Topics Covered

- The automatic "I\'m fine" response and its hidden cost
- How suppressing emotions affects your work and relationships
- The moment Jeffrey stopped pretending
- Practical steps toward honest communication

### Resources Mentioned

- [It\'s OK That You\'re Not OK](https://www.goodreads.com/book/show/34890015-it-s-ok-that-you-re-not-ok) by Megan Devine
- [Brené Brown\'s Books](https://brenebrown.com/books-audio/)

### Timestamps

- 00:00 — The most common lie we tell
- 08:15 — What "fine" is really costing you
- 17:30 — Jeffrey\'s breaking point
- 26:00 — Learning to say "I\'m not okay"',
                'tags' => ['mental-health', 'anxiety', 'relationships'],
            ],
            [
                'title' => 'Parenting When the Playbook Doesn\'t Exist',
                'slug' => 'parenting-when-the-playbook-doesnt-exist',
                'episode_number' => 3,
                'description' => "Raising a nonverbal autistic daughter means there's no manual. Jeffrey talks about the beautiful chaos of parenting Viola, the grief nobody warns you about, and the joy that blindsides you when you least expect it.",
                'duration_minutes' => 42,
                'published_at' => '2026-01-22 09:00:00',
                'show_notes' => '### Topics Covered

- The day Jeffrey and his wife got the diagnosis
- Grieving the life you imagined vs. embracing the one you have
- Nonverbal communication: learning Viola\'s language
- The therapy marathon: ABA, speech, occupational
- Moments of unexpected, overwhelming joy

### Resources Mentioned

- [Autism Speaks](https://www.autismspeaks.org)
- [Uniquely Human](https://www.goodreads.com/book/show/35068761-uniquely-human) by Barry Prizant
- [Child Mind Institute](https://childmind.org)

### Timestamps

- 00:00 — Meet Viola
- 09:00 — The diagnosis day
- 18:30 — The grief no one talks about
- 27:15 — Learning to communicate without words
- 35:00 — The joy that catches you off guard',
                'tags' => ['parenting', 'mental-health', 'resilience'],
            ],
            [
                'title' => 'Burnout Is Not a Badge of Honor',
                'slug' => 'burnout-is-not-a-badge-of-honor',
                'episode_number' => 4,
                'description' => "The tech industry glorifies hustle culture. Jeffrey pushes back — talking about the burnout he's experienced, the warning signs he missed, and why rest isn't the opposite of productivity.",
                'duration_minutes' => 37,
                'published_at' => '2026-01-29 09:00:00',
                'show_notes' => '### Topics Covered

- The hustle culture trap in the tech industry
- Warning signs of burnout that Jeffrey missed
- Physical symptoms: the body keeps the score
- Rest as a productivity strategy, not its enemy
- Setting boundaries with work (especially remote work)

### Resources Mentioned

- [Burnout](https://www.goodreads.com/book/show/36141137-burnout) by Emily and Amelia Nagoski
- [The Body Keeps the Score](https://www.goodreads.com/book/show/375802.The_Body_Keeps_the_Score) by Bessel van der Kolk

### Timestamps

- 00:00 — The glorification of hustle
- 07:45 — Jeffrey\'s burnout story
- 16:00 — Warning signs you\'re ignoring
- 24:30 — Rest is not laziness
- 31:00 — Practical boundary-setting strategies',
                'tags' => ['burnout', 'mental-health', 'remote-work'],
            ],
            [
                'title' => 'Marriage Under Pressure',
                'slug' => 'marriage-under-pressure',
                'episode_number' => 5,
                'description' => "Being a spouse when life is heavy. Jeffrey talks honestly about what it's like to maintain a marriage when you're both exhausted, both stressed, and both trying to hold it together for your kid.",
                'duration_minutes' => 40,
                'published_at' => '2026-02-05 09:00:00',
                'show_notes' => '### Topics Covered

- Marriage under the weight of special needs parenting
- The resentment cycle and how to break it
- Finding time for each other when there\'s none left
- Communicating needs without keeping score
- Why asking for help isn\'t weakness

### Resources Mentioned

- [The Gottman Institute](https://www.gottman.com)
- [The Seven Principles for Making Marriage Work](https://www.goodreads.com/book/show/849.The_Seven_Principles_for_Making_Marriage_Work) by John Gottman

### Timestamps

- 00:00 — The pressure cooker of life
- 08:00 — The resentment trap
- 17:30 — Micro-dates and stolen moments
- 27:00 — Communication when you\'re both depleted
- 34:15 — Asking for help together',
                'tags' => ['relationships', 'mental-health', 'parenting'],
            ],
            [
                'title' => 'The Loneliness of Remote Work',
                'slug' => 'the-loneliness-of-remote-work',
                'episode_number' => 6,
                'description' => 'Working from home sounds like a dream until the walls start closing in. Jeffrey explores the isolation that comes with remote work and the small things that keep him connected.',
                'duration_minutes' => 33,
                'published_at' => '2026-02-12 09:00:00',
                'show_notes' => '### Topics Covered

- The remote work paradox: freedom and isolation
- How loneliness sneaks up on you over months
- The difference between being alone and being lonely
- Strategies Jeffrey uses to stay connected
- Building community in a distributed world

### Resources Mentioned

- [Together](https://www.goodreads.com/book/show/48930191-together) by Vivek Murthy
- [Laravel Discord](https://discord.gg/laravel) — community that keeps Jeffrey sane

### Timestamps

- 00:00 — The dream of working from home
- 06:30 — When the walls close in
- 14:00 — Alone vs. lonely
- 21:15 — Jeffrey\'s connection strategies
- 27:30 — Building your own community',
                'tags' => ['remote-work', 'mental-health', 'anxiety'],
            ],
            [
                'title' => 'Anxiety and the Keyboard',
                'slug' => 'anxiety-and-the-keyboard',
                'episode_number' => 7,
                'description' => 'That feeling before you push to production. The dread before a code review. Jeffrey talks about anxiety as a developer — where it shows up, how it disguises itself, and what helps.',
                'duration_minutes' => 36,
                'published_at' => '2026-02-19 09:00:00',
                'show_notes' => '### Topics Covered

- Anxiety triggers specific to software development
- The deploy dread and code review fear
- Imposter syndrome as anxiety in disguise
- Physical anxiety symptoms at the desk
- Coping strategies that actually work for developers

### Resources Mentioned

- [Feeling Good](https://www.goodreads.com/book/show/52036.Feeling_Good) by David Burns — CBT classic
- [Headspace](https://headspace.com) — meditation app Jeffrey uses
- [Anxiety Canada](https://www.anxietycanada.com) — free resources and tools

### Timestamps

- 00:00 — The deploy button and dread
- 07:00 — Code review anxiety
- 15:30 — Imposter syndrome as anxiety
- 23:00 — Physical symptoms at the desk
- 30:00 — What actually helps',
                'tags' => ['anxiety', 'mental-health', 'career'],
            ],
            [
                'title' => 'Finding God in the Mess',
                'slug' => 'finding-god-in-the-mess',
                'episode_number' => 8,
                'description' => "Jeffrey's faith journey as a Lutheran and how it intersects with the hard stuff — doubt, unanswered prayers, and finding meaning when life doesn't make sense.",
                'duration_minutes' => 44,
                'published_at' => '2026-02-26 09:00:00',
                'show_notes' => '### Topics Covered

- Growing up Lutheran and what faith means to Jeffrey today
- Wrestling with doubt — and why that\'s okay
- Unanswered prayers and the silence of God
- Finding meaning in suffering (without clichés)
- Faith as a messy, honest relationship

### Resources Mentioned

- [A Grief Observed](https://www.goodreads.com/book/show/25814.A_Grief_Observed) by C.S. Lewis
- [When Bad Things Happen to Good People](https://www.goodreads.com/book/show/15818.When_Bad_Things_Happen_to_Good_People) by Harold Kushner

### Timestamps

- 00:00 — Faith and the tech world
- 09:30 — Jeffrey\'s faith journey
- 19:00 — Doubt as part of the process
- 28:15 — When prayers go unanswered
- 37:00 — Finding meaning without easy answers',
                'tags' => ['faith', 'mental-health', 'resilience'],
            ],
            [
                'title' => 'The Things We Don\'t Say Out Loud',
                'slug' => 'the-things-we-dont-say-out-loud',
                'episode_number' => 9,
                'description' => 'There are thoughts every parent of a special needs child has that they never say out loud. Jeffrey says some of them. Raw, honest, and necessary.',
                'duration_minutes' => 38,
                'published_at' => '2026-03-05 09:00:00',
                'show_notes' => '### Topics Covered

- The unspoken thoughts of special needs parents
- Guilt about having those thoughts in the first place
- The loneliness of a journey others can\'t understand
- Why vulnerability is the antidote to shame
- Permission to feel everything you feel

### Resources Mentioned

- [Daring Greatly](https://www.goodreads.com/book/show/13588356-daring-greatly) by Brené Brown
- [Child Mind Institute: Special Needs Parenting](https://childmind.org/article/special-needs-parenting/)

### Timestamps

- 00:00 — The thoughts no one admits to
- 08:00 — Guilt and shame spiral
- 17:30 — You\'re not alone in this
- 26:00 — Vulnerability as medicine
- 33:00 — Giving yourself permission',
                'tags' => ['parenting', 'mental-health', 'anxiety'],
            ],
            [
                'title' => 'Small Wins and Why They Matter',
                'slug' => 'small-wins-and-why-they-matter',
                'episode_number' => 10,
                'description' => 'When the big picture feels overwhelming, small wins keep you going. Jeffrey talks about celebrating the tiny victories — in code, in parenting, and in getting through the day.',
                'duration_minutes' => 31,
                'published_at' => '2026-03-12 09:00:00',
                'show_notes' => '### Topics Covered

- Why big goals paralyze and small wins energize
- Celebrating progress in code: shipped features, fixed bugs, clean tests
- Parenting wins: a new word, a good day, a moment of connection
- The gratitude practice Jeffrey swears by
- Building momentum one small win at a time

### Resources Mentioned

- [Atomic Habits](https://www.goodreads.com/book/show/40121378-atomic-habits) by James Clear
- [The Happiness Advantage](https://www.goodreads.com/book/show/34507927-how-to-be-happy) by Shawn Achor

### Timestamps

- 00:00 — When everything feels too big
- 06:00 — Small wins in code
- 13:30 — Small wins in parenting
- 20:00 — The gratitude practice
- 26:15 — Building momentum for tomorrow',
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
