<section
    data-home-services
    aria-labelledby="services-heading"
    class="dark:border-brand-800/50 border-t border-gray-200 bg-white py-14 sm:py-20 dark:bg-transparent"
>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-10 flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <h2
                id="services-heading"
                class="text-3xl font-semibold tracking-tight text-gray-900 sm:text-4xl dark:text-white"
            >
                Where I can help
            </h2>
            <a
                href="{{ route('services') }}"
                class="text-brand-700 decoration-brand-300 hover:text-brand-900 focus-visible:outline-brand-500 dark:text-brand-300 w-fit rounded py-2 text-sm font-semibold underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-4 dark:hover:text-white"
            >Explore services</a>
        </div>

        <dl class="grid gap-8 md:grid-cols-3 md:gap-10">
            <div>
                <dt>
                    <x-heroicon-o-wrench-screwdriver
                        class="stroke-brand-600 dark:stroke-brand-300 mb-5 size-8"
                        aria-hidden="true"
                    />
                    <span class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">Improve an existing codebase</span>
                </dt>
                <dd class="mt-3 max-w-sm text-base leading-7 text-gray-600 dark:text-gray-400">
                    Get a clear code review, untangle problem areas and strengthen tests so your team can move forward
                    with confidence.
                </dd>
            </div>
            <div>
                <dt>
                    <x-heroicon-o-command-line
                        class="stroke-brand-600 dark:stroke-brand-300 mb-5 size-8"
                        aria-hidden="true"
                    />
                    <span class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">Build your application</span>
                </dt>
                <dd class="mt-3 max-w-sm text-base leading-7 text-gray-600 dark:text-gray-400">
                    Bring experienced Laravel development to your team, from shaping the next feature to taking it
                    through to production.
                </dd>
            </div>
            <div>
                <dt>
                    <x-heroicon-o-shield-check
                        class="stroke-brand-600 dark:stroke-brand-300 mb-5 size-8"
                        aria-hidden="true"
                    />
                    <span class="text-xl font-semibold tracking-tight text-gray-900 dark:text-white">Ship with confidence</span>
                </dt>
                <dd class="mt-3 max-w-sm text-base leading-7 text-gray-600 dark:text-gray-400">
                    Protect critical workflows with automated tests, repeatable deployments, and visibility into
                    production errors.
                </dd>
            </div>
        </dl>
    </div>
</section>
