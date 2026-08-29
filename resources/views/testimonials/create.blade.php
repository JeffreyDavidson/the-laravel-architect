@extends('layouts.app')

@section('title', 'Share Your Experience')

@section('content')
    <x-hero-section>
        <x-terminal-prompt command="testimonial:new" />
        <h1 class="mb-4 text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white md:text-5xl">Share your <span class="text-brand-600">experience</span></h1>
        <p class="max-w-2xl text-lg leading-relaxed text-gray-600 dark:text-gray-400 md:text-xl">Worked with me on a Laravel project, modernization effort, or technical review? Your perspective helps future collaborators know what to expect.</p>
    </x-hero-section>

    <x-page-section>
        <div class="mx-auto max-w-2xl">
            @if(session('testimonial_success'))
                <div class="mb-6 rounded-xl border border-green-500/30 bg-green-500/10 p-4 text-sm text-green-700 dark:text-green-400" role="status" aria-live="polite">
                    {{ session('testimonial_success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-700 dark:text-red-400" role="alert" aria-live="assertive">
                    <p class="font-semibold">Please review the highlighted fields.</p>
                </div>
            @endif

            <x-card class="p-6 sm:p-8">
                <form action="{{ route('testimonials.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="absolute -left-[9999px] -top-[9999px]" aria-hidden="true">
                        <label for="testimonial-website">Website</label>
                        <input id="testimonial-website" type="text" name="website" tabindex="-1" autocomplete="off" value="">
                    </div>

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="testimonial-name" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                            <x-form.input id="testimonial-name" name="name" required maxlength="100" value="{{ old('name') }}" autocomplete="name" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" />
                        </div>
                        <div>
                            <label for="testimonial-role" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Role <span class="text-gray-500">(optional)</span></label>
                            <x-form.input id="testimonial-role" name="role" maxlength="100" value="{{ old('role') }}" placeholder="CTO, developer, founder…" />
                        </div>
                    </div>

                    <div>
                        <label for="testimonial-company" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Company <span class="text-gray-500">(optional)</span></label>
                        <x-form.input id="testimonial-company" name="company" maxlength="100" value="{{ old('company') }}" autocomplete="organization" />
                    </div>

                    <div>
                        <label for="testimonial-body" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Testimonial</label>
                        <x-form.textarea id="testimonial-body" name="body" rows="6" required maxlength="1000" aria-describedby="testimonial-body-help" aria-invalid="{{ $errors->has('body') ? 'true' : 'false' }}">{{ old('body') }}</x-form.textarea>
                        <p id="testimonial-body-help" class="mt-2 text-sm text-gray-500">Your testimonial will be reviewed before it appears publicly.</p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <x-button type="submit" class="justify-center px-7 py-3">Submit testimonial</x-button>
                        <p class="text-xs leading-relaxed text-gray-500 sm:max-w-xs sm:text-right">
                            Approved submissions may be displayed publicly. <a href="{{ route('privacy') }}" class="underline decoration-gray-300 underline-offset-2 transition-colors hover:text-brand-600 dark:decoration-gray-700 dark:hover:text-brand-300">Privacy details</a>
                        </p>
                    </div>
                </form>
            </x-card>
        </div>
    </x-page-section>
@endsection
