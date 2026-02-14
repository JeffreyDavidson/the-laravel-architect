<?php

namespace Database\Seeders;

use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Database\Seeder;

class EpisodeSeeder extends Seeder
{
    public function run(): void
    {
        $coffee = Podcast::where('slug', 'coffee-with-the-laravel-architect')->first();
        $cloudy = Podcast::where('slug', 'embracing-cloudy-days')->first();

        if ($coffee) {
            $this->seedCoffeeEpisodes($coffee);
        }

        if ($cloudy) {
            $this->seedCloudyEpisodes($cloudy);
        }
    }

    private function seedCoffeeEpisodes(Podcast $podcast): void
    {
        $episodes = [
            [
                'title' => 'Why Laravel? A Love Letter to the Framework',
                'episode_number' => 1,
                'description' => "In the debut episode, Jeffrey talks about his journey from vanilla PHP to Laravel, why he's stuck with it for over a decade, and what makes it different from every other framework he's tried.",
                'duration_minutes' => 38,
                'published_at' => '2026-01-06 09:00:00',
            ],
            [
                'title' => 'The Architecture Nobody Talks About',
                'episode_number' => 2,
                'description' => "Everyone talks about clean architecture in the abstract. Jeffrey breaks down what it actually looks like in a real Laravel project — actions, DTOs, form requests, and where most developers overcomplicate things.",
                'duration_minutes' => 45,
                'published_at' => '2026-01-13 09:00:00',
            ],
            [
                'title' => 'Testing Is Not Optional',
                'episode_number' => 3,
                'description' => "Jeffrey's three-suite testing philosophy: Feature, Integration, and Unit. What goes where, why Unit means zero dependencies, and how Adam Wathan's TDD course changed everything.",
                'duration_minutes' => 42,
                'published_at' => '2026-01-20 09:00:00',
            ],
            [
                'title' => 'Legacy Code Isn\'t a Dirty Word',
                'episode_number' => 4,
                'description' => "After migrating codebases from CodeIgniter, Yii2, CakePHP, and ExpressionEngine into Laravel, Jeffrey shares the patterns that make legacy rewrites survivable — and even enjoyable.",
                'duration_minutes' => 51,
                'published_at' => '2026-01-27 09:00:00',
            ],
            [
                'title' => 'Filament Changed How I Build Admin Panels',
                'episode_number' => 5,
                'description' => "A deep dive into Filament — why Jeffrey chose it over Nova, how it handles forms and tables, and the moment he realized he'd never hand-roll another admin panel again.",
                'duration_minutes' => 36,
                'published_at' => '2026-02-03 09:00:00',
            ],
            [
                'title' => 'Eloquent: The Good, The Bad, and The N+1',
                'episode_number' => 6,
                'description' => "Eloquent is beautiful until it isn't. Jeffrey talks about the patterns that keep Eloquent clean, the traps that kill performance, and when it's time to reach for the query builder instead.",
                'duration_minutes' => 40,
                'published_at' => '2026-02-10 09:00:00',
            ],
            [
                'title' => 'Deploying Laravel Without Losing Sleep',
                'episode_number' => 7,
                'description' => "From Forge to Envoyer to GitHub Actions — Jeffrey walks through his deployment setup, zero-downtime strategies, and the checklist he runs before every production push.",
                'duration_minutes' => 34,
                'published_at' => '2026-02-17 09:00:00',
            ],
            [
                'title' => 'The Self-Taught Developer\'s Advantage',
                'episode_number' => 8,
                'description' => "Jeffrey started self-taught, went to Full Sail, and came out the other side. He talks about what formal education gave him, what it didn't, and why self-taught developers have an edge they don't realize.",
                'duration_minutes' => 44,
                'published_at' => '2026-02-24 09:00:00',
            ],
            [
                'title' => 'APIs That Don\'t Make People Cry',
                'episode_number' => 9,
                'description' => "Designing APIs that other developers actually want to use. Resource classes, consistent error handling, versioning strategies, and why your API is a product whether you think of it that way or not.",
                'duration_minutes' => 39,
                'published_at' => '2026-03-03 09:00:00',
            ],
            [
                'title' => 'Building in Public: Why I Started This Podcast',
                'episode_number' => 10,
                'description' => "Jeffrey gets meta and talks about why he's building a content platform, the fear of putting yourself out there, and what he hopes this podcast becomes over time.",
                'duration_minutes' => 32,
                'published_at' => '2026-03-10 09:00:00',
            ],
        ];

        foreach ($episodes as $episode) {
            Episode::create(array_merge($episode, [
                'podcast_id' => $podcast->id,
                'season_number' => 1,
                'status' => 'published',
                'show_notes' => null,
            ]));
        }
    }

    private function seedCloudyEpisodes(Podcast $podcast): void
    {
        $episodes = [
            [
                'title' => 'Welcome to the Clouds',
                'episode_number' => 1,
                'description' => "The one where Jeffrey explains why he's starting a mental health podcast, what he hopes it becomes, and why pretending everything is fine helps exactly nobody.",
                'duration_minutes' => 28,
                'published_at' => '2026-01-08 09:00:00',
            ],
            [
                'title' => 'The Weight of Being "Fine"',
                'episode_number' => 2,
                'description' => "We all say we're fine when we're not. Jeffrey talks about the cost of that lie — at work, at home, and in your own head. And what happens when you finally stop saying it.",
                'duration_minutes' => 35,
                'published_at' => '2026-01-15 09:00:00',
            ],
            [
                'title' => 'Parenting When the Playbook Doesn\'t Exist',
                'episode_number' => 3,
                'description' => "Raising a nonverbal autistic daughter means there's no manual. Jeffrey talks about the beautiful chaos of parenting Viola, the grief nobody warns you about, and the joy that blindsides you when you least expect it.",
                'duration_minutes' => 42,
                'published_at' => '2026-01-22 09:00:00',
            ],
            [
                'title' => 'Burnout Is Not a Badge of Honor',
                'episode_number' => 4,
                'description' => "The tech industry glorifies hustle culture. Jeffrey pushes back — talking about the burnout he's experienced, the warning signs he missed, and why rest isn't the opposite of productivity.",
                'duration_minutes' => 37,
                'published_at' => '2026-01-29 09:00:00',
            ],
            [
                'title' => 'Marriage Under Pressure',
                'episode_number' => 5,
                'description' => "Being a spouse when life is heavy. Jeffrey talks honestly about what it's like to maintain a marriage when you're both exhausted, both stressed, and both trying to hold it together for your kid.",
                'duration_minutes' => 40,
                'published_at' => '2026-02-05 09:00:00',
            ],
            [
                'title' => 'The Loneliness of Remote Work',
                'episode_number' => 6,
                'description' => "Working from home sounds like a dream until the walls start closing in. Jeffrey explores the isolation that comes with remote work and the small things that keep him connected.",
                'duration_minutes' => 33,
                'published_at' => '2026-02-12 09:00:00',
            ],
            [
                'title' => 'Anxiety and the Keyboard',
                'episode_number' => 7,
                'description' => "That feeling before you push to production. The dread before a code review. Jeffrey talks about anxiety as a developer — where it shows up, how it disguises itself, and what helps.",
                'duration_minutes' => 36,
                'published_at' => '2026-02-19 09:00:00',
            ],
            [
                'title' => 'Finding God in the Mess',
                'episode_number' => 8,
                'description' => "Jeffrey's faith journey as a Lutheran and how it intersects with the hard stuff — doubt, unanswered prayers, and finding meaning when life doesn't make sense.",
                'duration_minutes' => 44,
                'published_at' => '2026-02-26 09:00:00',
            ],
            [
                'title' => 'The Things We Don\'t Say Out Loud',
                'episode_number' => 9,
                'description' => "There are thoughts every parent of a special needs child has that they never say out loud. Jeffrey says some of them. Raw, honest, and necessary.",
                'duration_minutes' => 38,
                'published_at' => '2026-03-05 09:00:00',
            ],
            [
                'title' => 'Small Wins and Why They Matter',
                'episode_number' => 10,
                'description' => "When the big picture feels overwhelming, small wins keep you going. Jeffrey talks about celebrating the tiny victories — in code, in parenting, and in getting through the day.",
                'duration_minutes' => 31,
                'published_at' => '2026-03-12 09:00:00',
            ],
        ];

        foreach ($episodes as $episode) {
            Episode::create(array_merge($episode, [
                'podcast_id' => $podcast->id,
                'season_number' => 1,
                'status' => 'published',
                'show_notes' => null,
            ]));
        }
    }
}
