@extends('layouts.app')

@section('title', 'Contact')

@section('content')
    {{-- Hero --}}
    <x-hero-section>
        <x-terminal-prompt command="contact:new" />
        <h1 class="mb-4 text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white md:text-5xl">Let's Build Something <span class="text-brand-600">Together</span></h1>
        <p class="text-gray-600 dark:text-gray-400 text-lg md:text-xl leading-relaxed">Have a project in mind? Need help modernizing a legacy codebase? Or just want to talk shop about Laravel? I'd love to hear from you.</p>
        <p class="text-green-600 dark:text-green-400 text-sm mt-4 flex items-center gap-2">
            <span class="relative flex h-2 w-2">
                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
            </span>
            Available for Projects
        </p>
    </x-hero-section>

    {{-- Content --}}
    <x-page-section>
            <div class="flex flex-col lg:flex-row gap-16">

                {{-- Form --}}
                <div class="flex-1">
                    <x-section-heading icon="mail" class="mb-8">Send a Message</x-section-heading>

                    @if(session('success'))
                    <div class="mb-6 p-4 rounded-xl border border-green-500/30 bg-green-500/10 text-green-400 text-sm">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="mb-6 p-4 rounded-xl border border-red-500/30 bg-red-500/10 text-red-400 text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('contact.submit') }}" method="POST" class="space-y-6">
                        @csrf
                        {{-- Honeypot: hidden from humans, bots fill it --}}
                        <div style="position:absolute;left:-9999px;top:-9999px;" aria-hidden="true">
                            <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name</label>
                                <x-form.input id="name" name="name" required placeholder="Your name" />
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                                <x-form.input id="email" name="email" type="email" required placeholder="you@example.com" />
                            </div>
                        </div>

                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">What can I help with?</label>
                            <x-form.select id="type" name="type">
                                <option value="freelance">Freelance Project</option>
                                <option value="consulting">Consulting / Code Review</option>
                                <option value="modernization">Legacy Modernization</option>
                                <option value="collaboration">Collaboration</option>
                                <option value="other">Just Saying Hi</option>
                            </x-form.select>
                        </div>

                        <div>
                            <label for="budget" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Budget Range <span class="text-gray-600">(optional)</span></label>
                            <x-form.select id="budget" name="budget">
                                <option value="">Prefer not to say</option>
                                <option value="small">Under $5,000</option>
                                <option value="medium">$5,000 – $15,000</option>
                                <option value="large">$15,000 – $50,000</option>
                                <option value="enterprise">$50,000+</option>
                            </x-form.select>
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Message</label>
                            <x-form.textarea id="message" name="message" rows="6" required placeholder="Tell me about your project, timeline, and any specific requirements..." />
                        </div>

                        <x-button type="submit" class="px-8 py-3.5 shadow-[0_0_30px_rgba(74,127,191,0.3)]">
                            Send Message
                            <x-svg-icon name="arrow-right" class="w-4 h-4" />
                        </x-button>
                    </form>
                </div>

                {{-- Sidebar --}}
                <div class="lg:w-80 flex-shrink-0 space-y-6">
                    {{-- What to Expect --}}
                    <x-card class="p-6">
                        <h3 class="font-bold mb-4 text-gray-900 dark:text-white flex items-center gap-2">
                            <x-svg-icon name="info" class="h-4 w-4 text-brand-600" />
                            What to Expect
                        </h3>
                        <ul role="list" class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                            <li class="flex items-start gap-2">
                                <x-svg-icon name="check" class="mt-0.5 h-4 w-4 flex-shrink-0 text-brand-600" />
                                I'll respond within 24–48 hours
                            </li>
                            <li class="flex items-start gap-2">
                                <x-svg-icon name="check" class="mt-0.5 h-4 w-4 flex-shrink-0 text-brand-600" />
                                Free initial consultation call
                            </li>
                            <li class="flex items-start gap-2">
                                <x-svg-icon name="check" class="mt-0.5 h-4 w-4 flex-shrink-0 text-brand-600" />
                                Detailed project scope & estimate
                            </li>
                            <li class="flex items-start gap-2">
                                <x-svg-icon name="check" class="mt-0.5 h-4 w-4 flex-shrink-0 text-brand-600" />
                                No obligation, no pressure
                            </li>
                        </ul>
                    </x-card>

                    {{-- Services --}}
                    <x-card class="p-6">
                        <h3 class="font-bold mb-4 text-gray-900 dark:text-white flex items-center gap-2">
                            <x-svg-icon name="settings" class="h-4 w-4 text-brand-600" />
                            Services
                        </h3>
                        <div class="space-y-3">
                            <x-public.muted-card>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Custom Laravel Development</p>
                                <p class="text-xs text-gray-500 mt-0.5">Full-stack applications built right</p>
                            </x-public.muted-card>
                            <x-public.muted-card>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Legacy Modernization</p>
                                <p class="text-xs text-gray-500 mt-0.5">CodeIgniter, CakePHP, Yii → Laravel</p>
                            </x-public.muted-card>
                            <x-public.muted-card>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Code Review & Consulting</p>
                                <p class="text-xs text-gray-500 mt-0.5">Architecture guidance & best practices</p>
                            </x-public.muted-card>
                            <x-public.muted-card>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Testing Strategy</p>
                                <p class="text-xs text-gray-500 mt-0.5">Feature, Integration & Unit test suites</p>
                            </x-public.muted-card>
                        </div>
                    </x-card>

                    {{-- Connect --}}
                    <x-card class="p-6">
                        <h3 class="font-bold mb-4 text-gray-900 dark:text-white flex items-center gap-2">
                            <x-svg-icon name="chat" class="h-4 w-4 text-brand-600" />
                            Other Ways to Connect
                        </h3>
                        <x-social-links variant="list" />
                    </x-card>
                </div>
            </div>
    </x-page-section>
@endsection
