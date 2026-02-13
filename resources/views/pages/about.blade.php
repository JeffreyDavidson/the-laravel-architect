@extends('layouts.app')

@section('title', 'About')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            {{-- Brand character --}}
            <div>
                <div class="aspect-square rounded-2xl overflow-hidden border border-brand-800/50 shadow-xl shadow-brand-600/10">
                    <img src="/images/logo-alternate.jpg" alt="The Laravel Architect" class="w-full h-full object-cover">
                </div>
                <p class="text-center text-xs text-gray-500 mt-3 italic">The Laravel Architect</p>
            </div>

            {{-- Bio --}}
            <div class="md:col-span-2">
                <h1 class="text-3xl font-bold mb-6">About Me</h1>
                <div class="prose prose-lg dark:prose-invert max-w-none">
                    <p>
                        Hey! I'm Jeffrey Davidson — a web developer based in Florida with a passion for 
                        building clean, maintainable applications with Laravel.
                    </p>
                    <p>
                        I've been in the web development world for years, working primarily with PHP and the 
                        Laravel ecosystem. I believe in writing code that's not just functional, but elegant — 
                        code that your future self (and your teammates) will thank you for.
                    </p>
                    <p>
                        When I'm not coding, you'll find me creating content on 
                        <a href="#">The Laravel Architect</a> YouTube channel, 
                        spending time with my family, or exploring Florida's theme parks.
                    </p>

                    <h2>What I Work With</h2>
                    <div class="not-prose grid grid-cols-2 gap-3 my-6">
                        @foreach(['Laravel', 'PHP', 'Filament', 'Livewire', 'Tailwind CSS', 'Alpine.js', 'MySQL', 'Redis', 'Git', 'Docker', 'Laravel Forge', 'Vue.js'] as $skill)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="w-2 h-2 bg-brand-500 rounded-full"></span>
                            {{ $skill }}
                        </div>
                        @endforeach
                    </div>

                    <h2>Let's Connect</h2>
                    <p>
                        I'm always up for interesting conversations about code, architecture, and web development. 
                        Feel free to <a href="{{ route('contact') }}">reach out</a> or find me on social media.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
