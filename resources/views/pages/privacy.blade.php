@extends('layouts.app')

@section('title', 'Privacy')

@section('content')
    <x-hero-section>
        <x-terminal-prompt command="privacy:read" />
        <h1 class="mb-4 text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white md:text-5xl">A clear view of your <span class="text-brand-600">privacy</span></h1>
        <p class="max-w-3xl text-lg leading-relaxed text-gray-600 dark:text-gray-400 md:text-xl">This notice explains what The Laravel Architect collects, why it is needed, and the choices available to you.</p>
        <p class="mt-4 text-sm text-gray-500 dark:text-gray-500">Last updated August 21, 2026</p>
    </x-hero-section>

    <x-page-section>
        <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_18rem] lg:gap-16">
            <article class="space-y-10 text-gray-600 dark:text-gray-400">
                <section aria-labelledby="privacy-overview">
                    <h2 id="privacy-overview" class="mb-4 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">The short version</h2>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <x-public.muted-card class="p-5">
                            <p class="mb-1 font-semibold text-gray-900 dark:text-white">No data sales</p>
                            <p class="text-sm leading-relaxed">Personal information is not sold or rented.</p>
                        </x-public.muted-card>
                        <x-public.muted-card class="p-5">
                            <p class="mb-1 font-semibold text-gray-900 dark:text-white">Purpose limited</p>
                            <p class="text-sm leading-relaxed">Submitted details are used to provide the feature you requested.</p>
                        </x-public.muted-card>
                        <x-public.muted-card class="p-5">
                            <p class="mb-1 font-semibold text-gray-900 dark:text-white">You have choices</p>
                            <p class="text-sm leading-relaxed">You can unsubscribe or request access, correction, or deletion.</p>
                        </x-public.muted-card>
                    </div>
                </section>

                <section aria-labelledby="privacy-information">
                    <h2 id="privacy-information" class="mb-4 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Information you choose to provide</h2>
                    <div class="space-y-5">
                        <div>
                            <h3 class="mb-1 font-semibold text-gray-900 dark:text-white">Contact inquiries</h3>
                            <p class="leading-relaxed">The contact form asks for your name, email address, inquiry type, an optional budget range, and your message. The application sends those details by email to Jeffrey and sends a confirmation copy to you. It does not save contact messages in the application database, though messages may remain in the email system while the inquiry is handled and for reasonable business records.</p>
                        </div>
                        <div>
                            <h3 class="mb-1 font-semibold text-gray-900 dark:text-white">Newsletter subscriptions</h3>
                            <p class="leading-relaxed">The newsletter stores your email address and subscription, confirmation, and unsubscribe timestamps. A temporary, hashed verification token supports the confirmation process. Subscription requires email confirmation, and every subscriber can unsubscribe using the link provided in newsletter messages.</p>
                        </div>
                        <div>
                            <h3 class="mb-1 font-semibold text-gray-900 dark:text-white">Testimonials</h3>
                            <p class="leading-relaxed">A testimonial submission includes your name, testimonial text, and any role or company you choose to provide. Submissions are stored for review and are not displayed until approved. Because approved testimonials may appear publicly, do not submit information you do not want published.</p>
                        </div>
                    </div>
                </section>

                <section aria-labelledby="privacy-automatic">
                    <h2 id="privacy-automatic" class="mb-4 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Site operation and analytics</h2>
                    <div class="space-y-4 leading-relaxed">
                        <p>Laravel uses essential session and security cookies to protect forms, preserve validation messages, and support authenticated administration. The server may process standard request information such as IP address, browser details, requested URL, and timestamps for security, rate limiting, and operational logs.</p>
                        <p>When configured, the site loads Fathom Analytics to understand aggregate site traffic. Podcast pages may include a YouTube player; loading or using that player can send information to YouTube under its own privacy practices.</p>
                    </div>
                </section>

                <section aria-labelledby="privacy-use">
                    <h2 id="privacy-use" class="mb-4 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">How information is used and shared</h2>
                    <p class="mb-4 leading-relaxed">Information is used to answer inquiries, deliver and administer newsletter subscriptions, review and publish approved testimonials, secure the application, prevent abuse, and understand site performance.</p>
                    <p class="leading-relaxed">Information is shared only with service providers needed to operate the site—such as hosting, email delivery, analytics, and embedded media—or when disclosure is required to comply with law or protect the site and its users. Those providers process information under their own terms and privacy notices.</p>
                </section>

                <section aria-labelledby="privacy-retention">
                    <h2 id="privacy-retention" class="mb-4 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Retention and your choices</h2>
                    <p class="mb-4 leading-relaxed">Information is kept only as long as reasonably needed for the purpose described above, site security, or legitimate recordkeeping. Newsletter records and testimonials remain until they are no longer needed or a deletion request is completed. Operational logs and email records follow the retention settings of the services that store them.</p>
                    <p class="leading-relaxed">You may ask to access, correct, or delete personal information associated with you. Newsletter subscribers can also use the unsubscribe link in any newsletter. Requests can be made through the <a href="{{ route('contact') }}" class="font-medium text-brand-600 underline decoration-brand-300 underline-offset-4 transition-colors hover:text-brand-500 dark:text-brand-300 dark:decoration-brand-700">contact form</a>.</p>
                </section>

                <section aria-labelledby="privacy-updates">
                    <h2 id="privacy-updates" class="mb-4 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Updates to this notice</h2>
                    <p class="leading-relaxed">This notice may change as the site and its services evolve. Material updates will be reflected on this page with a revised date.</p>
                </section>
            </article>

            <aside class="lg:sticky lg:top-24 lg:self-start" aria-label="Privacy contact">
                <x-card class="p-6">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-brand-600 dark:text-brand-300">Questions or requests</p>
                    <h2 class="mb-3 text-xl font-bold text-gray-900 dark:text-white">Talk directly to Jeffrey</h2>
                    <p class="mb-5 text-sm leading-relaxed text-gray-600 dark:text-gray-400">Use the contact form for a privacy question or a request about information associated with you.</p>
                    <x-button :href="route('contact')" class="w-full justify-center">Contact me</x-button>
                </x-card>
            </aside>
        </div>
    </x-page-section>
@endsection
