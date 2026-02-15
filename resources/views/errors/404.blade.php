@extends('layouts.app')

@section('title', '404 — Page Not Found')

@section('content')
<div class="relative min-h-[80vh] flex items-center justify-center overflow-hidden">
    {{-- Ambient glow --}}
    <div class="hidden dark:block absolute top-1/3 left-1/4 w-[500px] h-[500px] rounded-full opacity-[0.06] blur-[120px]" style="background: radial-gradient(circle, #4A7FBF, transparent 70%);"></div>
    <div class="hidden dark:block absolute bottom-1/4 right-1/4 w-[400px] h-[400px] rounded-full opacity-[0.05] blur-[100px]" style="background: radial-gradient(circle, #9D5175, transparent 70%);"></div>

    <div class="relative text-center px-4">
        {{-- Big 404 --}}
        <style>
            .four-oh-four { background: linear-gradient(180deg, #c5cdd8 0%, #e2e8f0 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
            .dark .four-oh-four { background: linear-gradient(180deg, #2a3a4f 0%, #1a2535 100%); -webkit-background-clip: text; background-clip: text; }
        </style>
        <div class="four-oh-four font-mono text-[10rem] md:text-[14rem] font-extrabold leading-none select-none mb-6">
            404
        </div>

        {{-- Terminal style message --}}
        <div class="inline-block bg-white dark:bg-[#0D1117] border border-gray-200 dark:border-[#1e2a3a] rounded-xl px-6 py-4 mb-10 relative">
            <div class="flex items-center gap-1.5 mb-3">
                <div class="w-2.5 h-2.5 rounded-full bg-red-500/80"></div>
                <div class="w-2.5 h-2.5 rounded-full bg-yellow-500/80"></div>
                <div class="w-2.5 h-2.5 rounded-full bg-green-500/80"></div>
            </div>
            <div class="font-mono text-sm text-left">
                <p class="text-gray-500">$ php artisan route:find <span class="text-gray-400">{{ request()->path() }}</span></p>
                <p class="text-red-400 mt-1">Error: Route not found.</p>
                <p class="text-gray-600 mt-1">$ <span class="animate-pulse text-[#4A7FBF]">▊</span></p>
            </div>
        </div>

        <h1 class="text-3xl md:text-4xl font-extrabold mb-4 text-gray-900 dark:text-white">This page doesn't exist.</h1>
        <p class="text-gray-600 dark:text-gray-400 text-lg mb-10 max-w-md mx-auto">Looks like you hit a dead route. Let's get you back on track.</p>

        <div class="flex flex-wrap gap-4 justify-center">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-[#4A7FBF] hover:bg-[#5A8FD0] text-white font-semibold rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Go Home
            </a>
            <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-2 px-6 py-3 border border-gray-200 dark:border-[#1e2a3a] hover:border-gray-400 dark:hover:border-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-lg transition-colors">
                Read the Blog
            </a>
            <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 px-6 py-3 border border-gray-200 dark:border-[#1e2a3a] hover:border-gray-400 dark:hover:border-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-lg transition-colors">
                Contact Me
            </a>
        </div>
    </div>
</div>
@endsection
