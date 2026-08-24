@extends('layouts.app')

@section('title', 'Contact')

@section('content')
    {{-- Hero --}}
    <x-hero-section>
        <div class="grid gap-6 md:grid-cols-[8rem_1fr] md:gap-10">
            <p class="font-mono text-xs uppercase tracking-[0.18em] text-brand-600">Contact / 06</p>
            <div>
                <h1 class="mb-4 text-4xl font-bold tracking-tight text-gray-900 dark:text-white md:text-6xl">Let’s talk about the work.</h1>
                <p class="text-lg leading-relaxed text-gray-600 dark:text-gray-400 md:text-xl">Have a project in mind? Need help modernizing a legacy codebase? Or just want to talk shop about Laravel? I'd love to hear from you.</p>
                <p class="mt-5 font-mono text-xs uppercase tracking-wide text-gray-500">Available for select projects</p>
            </div>
        </div>
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

                    @php($firstErrorField = $errors->keys()[0] ?? null)

                    @if($errors->any())
                        <div role="alert" class="mb-6 rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-700 dark:text-red-400">
                            <p class="font-semibold">Please review the highlighted fields.</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5">
                                @foreach($errors->getMessages() as $field => $messages)
                                    @foreach($messages as $error)
                                        <li><a href="#{{ $field }}" class="underline underline-offset-2">{{ $error }}</a></li>
                                    @endforeach
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
                                <x-form.input id="name" name="name" required autocomplete="name" placeholder="Your name" :autofocus="$firstErrorField === 'name'" />
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                                <x-form.input id="email" name="email" type="email" required autocomplete="email" placeholder="you@example.com" :autofocus="$firstErrorField === 'email'" />
                            </div>
                        </div>

                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">What can I help with?</label>
                            <x-form.select id="type" name="type" :autofocus="$firstErrorField === 'type'">
                                <option value="freelance" @selected(old('type', 'freelance') === 'freelance')>Freelance Project</option>
                                <option value="consulting" @selected(old('type') === 'consulting')>Consulting / Code Review</option>
                                <option value="modernization" @selected(old('type') === 'modernization')>Legacy Modernization</option>
                                <option value="collaboration" @selected(old('type') === 'collaboration')>Collaboration</option>
                                <option value="other" @selected(old('type') === 'other')>Just Saying Hi</option>
                            </x-form.select>
                        </div>

                        <div>
                            <label for="budget" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Budget Range <span class="text-gray-600">(optional)</span></label>
                            <x-form.select id="budget" name="budget">
                                <option value="" @selected(old('budget') === null || old('budget') === '')>Prefer not to say</option>
                                <option value="small" @selected(old('budget') === 'small')>Under $5,000</option>
                                <option value="medium" @selected(old('budget') === 'medium')>$5,000 to $15,000</option>
                                <option value="large" @selected(old('budget') === 'large')>$15,000 to $50,000</option>
                                <option value="enterprise" @selected(old('budget') === 'enterprise')>$50,000+</option>
                            </x-form.select>
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Message</label>
                            <x-form.textarea id="message" name="message" rows="6" required placeholder="Tell me about your project, timeline, and any specific requirements..." :autofocus="$firstErrorField === 'message'" />
                        </div>

                        @if(config('services.turnstile.site_key'))
                            @once
                                <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
                            @endonce

                            <div>
                                <div
                                    class="cf-turnstile"
                                    data-sitekey="{{ config('services.turnstile.site_key') }}"
                                    data-action="{{ config('services.turnstile.contact_action') }}"
                                    data-size="flexible"
                                ></div>
                                @error('cf-turnstile-response')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400" role="alert">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <x-button type="submit" class="px-8 py-3.5">
                            Send Message
                            <x-svg-icon name="arrow-right" class="w-4 h-4" />
                        </x-button>
                        <p class="text-xs leading-relaxed text-gray-600 dark:text-gray-400">
                            Your details are used to reply to this inquiry. Read the <a href="{{ route('privacy') }}" class="underline decoration-gray-300 underline-offset-2 transition-colors hover:text-brand-600 dark:decoration-gray-700 dark:hover:text-brand-300">privacy notice</a> for more information.
                        </p>
                    </form>
                </div>

                {{-- Sidebar --}}
                <div class="lg:w-80 flex-shrink-0 space-y-6">
                    {{-- What to Expect --}}
                    <section class="border-t border-gray-200 pt-6 dark:border-[#1e2a3a]">
                        <h3 class="font-bold mb-4 text-gray-900 dark:text-white flex items-center gap-2">
                            <x-svg-icon name="info" class="h-4 w-4 text-brand-600" />
                            What to Expect
                        </h3>
                        <ul role="list" class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                            <li class="flex items-start gap-2">
                                <x-svg-icon name="check" class="mt-0.5 h-4 w-4 flex-shrink-0 text-brand-600" />
                                I'll respond within 24 to 48 hours
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
                    </section>

                    {{-- Services --}}
                    <section class="border-t border-gray-200 pt-6 dark:border-[#1e2a3a]">
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
                                <p class="text-xs text-gray-500 mt-0.5">CodeIgniter, CakePHP, and Yii to Laravel</p>
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
                    </section>

                    {{-- Connect --}}
                    <section class="border-t border-gray-200 pt-6 dark:border-[#1e2a3a]">
                        <h3 class="font-bold mb-4 text-gray-900 dark:text-white flex items-center gap-2">
                            <x-svg-icon name="chat" class="h-4 w-4 text-brand-600" />
                            Other Ways to Connect
                        </h3>
                        <x-social-links variant="list" />
                    </section>
                </div>
            </div>
    </x-page-section>
@endsection
