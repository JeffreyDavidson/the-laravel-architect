@extends('layouts.app')

@section('title', '404 | Page Not Found')

@section('content')
    <div class="flex min-h-[80vh] items-center bg-white dark:bg-[#0b1016]">
        <div class="mx-auto grid w-full max-w-7xl gap-8 px-4 py-20 sm:px-6 md:grid-cols-[10rem_minmax(0,1fr)] md:gap-14 lg:px-8">
            <p class="text-brand-600 font-mono text-sm tracking-[0.18em] uppercase">Error / 404</p>
            <div>
                <h1 class="max-w-3xl text-5xl font-bold tracking-tight text-gray-900 md:text-7xl dark:text-white">
                    This route ends here.
                </h1>
                <p class="mt-6 max-w-xl text-lg leading-relaxed text-gray-600 dark:text-gray-400">
                    The page may have moved, or the address may be incomplete. Choose a known path below.
                </p>

                <div class="mt-10 flex flex-wrap gap-4">
                    <a
                        href="{{ route('home') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-[#4A7FBF] px-6 py-3 font-semibold text-white transition-colors hover:bg-[#5A8FD0]"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                        Go Home
                    </a>
                    <a
                        href="{{ route('blog.index') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-6 py-3 font-semibold text-gray-700 transition-colors hover:border-gray-400 dark:border-[#1e2a3a] dark:text-gray-300 dark:hover:border-gray-600"
                    >
                        Read the Blog
                    </a>
                    <a
                        href="{{ route('contact') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-200 px-6 py-3 font-semibold text-gray-700 transition-colors hover:border-gray-400 dark:border-[#1e2a3a] dark:text-gray-300 dark:hover:border-gray-600"
                    >
                        Contact Me
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
