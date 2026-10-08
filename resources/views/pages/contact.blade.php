<x-layouts.site :page-meta="$pageMeta">
    {{-- Hero --}}
    <x-hero-section>
        <div class="max-w-3xl">
            <div>
                <h1 class="mb-4 text-4xl font-bold tracking-tight text-gray-900 md:text-6xl dark:text-white">
                    Let’s talk about the work.
                </h1>
                <p class="text-lg leading-relaxed text-gray-600 md:text-xl dark:text-gray-400">
                    Have a project in mind? Need help modernizing a legacy codebase? Or just want to talk shop about
                    Laravel? I'd love to hear from you.
                </p>
                <p class="text-brand-700 dark:text-brand-300 mt-5 text-sm font-medium">
                    Custom Laravel applications · Modernization · Code review
                </p>
            </div>
        </div>
    </x-hero-section>

    {{-- Content --}}
    <x-page-section>
        <div class="flex flex-col gap-16 lg:flex-row">
            {{-- Form --}}
            <div class="flex-1">
                <x-section-heading icon="mail" class="mb-8">Send a Message</x-section-heading>
                <p class="mb-6 max-w-xl text-base leading-7 text-gray-600 dark:text-gray-400">
                    A few sentences are enough to start. Share what you’re building, what’s getting in the way, and any
                    timeline you have in mind. I’ll reply within 24 to 48 hours.
                </p>

                @session('success')
                    <x-alert class="mb-6 rounded-xl p-4" aria-live="polite">{{ $value }}</x-alert>
                @endsession

                @php($firstErrorField = $errors->keys()[0] ?? null)

                @if ($errors->any())
                    <x-alert type="error" class="mb-6 rounded-xl p-4" aria-live="assertive">
                        <p class="font-semibold">Please review the highlighted fields.</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->getMessages() as $field => $messages)
                                @foreach ($messages as $error)
                                    <li>
                                        <a href="#{{ $field }}" class="underline underline-offset-2">{{ $error }}</a>
                                    </li>
                                @endforeach
                            @endforeach
                        </ul>
                    </x-alert>
                @endif

                <form
                    action="{{ route('contact.store') }}"
                    method="POST"
                    class="space-y-6"
                    data-contact-form
                    x-data="turnstileWidget"
                    x-on:focusin="load"
                    x-on:pointerdown="load"
                >
                    @csrf
                    @if ($selectedProject)
                        <div class="border-brand-500/30 bg-brand-500/10 text-brand-800 dark:text-brand-200 rounded-xl border p-4 text-sm">
                            <p class="font-semibold">Project inquiry</p>
                            <p class="mt-1">
                                I’ll include <span class="font-medium">{{ $selectedProject->title }}</span> with your
                                message.
                            </p>
                        </div>
                        <input type="hidden" name="project" value="{{ $selectedProject->slug }}" />
                    @endif
                    <x-form.honeypot />
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <x-form.label for="name">Name</x-form.label>
                            <x-form.input
                                id="name"
                                name="name"
                                required
                                autocomplete="name"
                                placeholder="Your name"
                                :autofocus="$firstErrorField === 'name'"
                            />
                        </div>
                        <div>
                            <x-form.label for="email">Email</x-form.label>
                            <x-form.input
                                id="email"
                                name="email"
                                type="email"
                                required
                                autocomplete="email"
                                placeholder="you@example.com"
                                :autofocus="$firstErrorField === 'email'"
                            />
                        </div>
                    </div>

                    <div>
                        <x-form.label for="type">What can I help with?</x-form.label>
                        <x-form.select
                            id="type"
                            name="type"
                            :autofocus="$firstErrorField === 'type'"
                            :options="$contactTypeOptions"
                            :value="$defaultContactType"
                        />
                    </div>

                    <div>
                        <x-form.label for="budget">
                            Budget Range <span class="text-gray-600">(optional)</span>
                        </x-form.label>
                        <x-form.select
                            id="budget"
                            name="budget"
                            :options="$contactBudgetOptions"
                            placeholder="Prefer not to say"
                        />
                    </div>

                    <div>
                        <x-form.label for="message">Message</x-form.label>
                        <x-form.textarea
                            id="message"
                            name="message"
                            rows="6"
                            required
                            placeholder="Tell me about your project, timeline, and any specific requirements…"
                            :autofocus="$firstErrorField === 'message'"
                        />
                    </div>

                    @if (config('services.turnstile.site_key'))
                        <div>
                            <div
                                data-turnstile-widget
                                x-ref="widget"
                                data-sitekey="{{ config('services.turnstile.site_key') }}"
                                data-action="{{ config('services.turnstile.contact_action') }}"
                            ></div>
                            <noscript>
                                <p class="text-sm text-red-600 dark:text-red-400" role="alert" aria-live="assertive">
                                    JavaScript is required to complete the verification.
                                </p>
                            </noscript>
                            @error('cf-turnstile-response')
                                <p
                                    class="mt-2 text-sm text-red-600 dark:text-red-400"
                                    role="alert"
                                    aria-live="assertive"
                                >
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    @endif

                    <x-button type="submit" class="px-8 py-3.5">
                        Send Message
                        <x-svg-icon name="arrow-right" class="h-4 w-4" />
                    </x-button>
                    <p class="text-xs leading-relaxed text-gray-600 dark:text-gray-400">
                        Your details are used to reply to this inquiry. Read the
                        <a
                            href="{{ route('privacy') }}"
                            class="hover:text-brand-action dark:hover:text-brand-300 underline decoration-gray-300 underline-offset-2 transition-colors dark:decoration-gray-700"
                        >privacy notice</a>
                        for more information.
                    </p>
                </form>
            </div>

            {{-- Sidebar --}}
            <div class="flex-shrink-0 space-y-6 lg:w-80">
                {{-- What to Expect --}}
                <section class="dark:border-surface-border border-t border-gray-200 pt-6">
                    <h3 class="mb-4 flex items-center gap-2 font-bold text-gray-900 dark:text-white">
                        <x-svg-icon name="info" class="text-brand-600 h-4 w-4" />
                        What to Expect
                    </h3>
                    <ul role="list" class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                        <li class="flex items-start gap-2">
                            <x-svg-icon name="check" class="text-brand-600 mt-0.5 h-4 w-4 flex-shrink-0" />
                            I'll respond within 24 to 48 hours
                        </li>
                        <li class="flex items-start gap-2">
                            <x-svg-icon name="check" class="text-brand-600 mt-0.5 h-4 w-4 flex-shrink-0" />
                            Free initial consultation call
                        </li>
                        <li class="flex items-start gap-2">
                            <x-svg-icon name="check" class="text-brand-600 mt-0.5 h-4 w-4 flex-shrink-0" />
                            Detailed project scope & estimate
                        </li>
                        <li class="flex items-start gap-2">
                            <x-svg-icon name="check" class="text-brand-600 mt-0.5 h-4 w-4 flex-shrink-0" />
                            No obligation, no pressure
                        </li>
                    </ul>
                </section>

                {{-- Services --}}
                <section class="dark:border-surface-border border-t border-gray-200 pt-6">
                    <h3 class="mb-4 flex items-center gap-2 font-bold text-gray-900 dark:text-white">
                        <x-svg-icon name="settings" class="text-brand-600 h-4 w-4" />
                        Services
                    </h3>
                    <div class="space-y-3">
                        <x-public.muted-card>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                Custom Laravel Development
                            </p>
                            <p class="mt-0.5 text-xs text-gray-500">Full-stack applications built right</p>
                        </x-public.muted-card>
                        <x-public.muted-card>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Legacy Modernization</p>
                            <p class="mt-0.5 text-xs text-gray-500">CodeIgniter, CakePHP, and Yii to Laravel</p>
                        </x-public.muted-card>
                        <x-public.muted-card>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Code Review & Consulting</p>
                            <p class="mt-0.5 text-xs text-gray-500">Architecture guidance & best practices</p>
                        </x-public.muted-card>
                        <x-public.muted-card>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Testing Strategy</p>
                            <p class="mt-0.5 text-xs text-gray-500">Feature, Integration & Unit test suites</p>
                        </x-public.muted-card>
                    </div>
                </section>

                {{-- Connect --}}
                <section class="dark:border-surface-border border-t border-gray-200 pt-6">
                    <h3 class="mb-4 flex items-center gap-2 font-bold text-gray-900 dark:text-white">
                        <x-svg-icon name="chat" class="text-brand-600 h-4 w-4" />
                        Other Ways to Connect
                    </h3>
                    <x-social-links variant="list" />
                </section>
            </div>
        </div>
    </x-page-section>
</x-layouts.site>
