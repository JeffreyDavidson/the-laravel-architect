@extends('layouts.app')

@section('title', 'Contact')

@section('content')
<style>
    .contact-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        border: 1px solid #d1d5db;
        background: #ffffff;
        color: #111827;
        font-size: 0.875rem;
        outline: none;
        transition: all 0.2s ease;
    }
    .dark .contact-input {
        border-color: #1e2a3a;
        background: #0D1117;
        color: #e5e7eb;
    }
    .contact-input:focus {
        border-color: #4A7FBF;
        box-shadow: 0 0 0 3px rgba(74, 127, 191, 0.1);
    }
    .contact-input::placeholder {
        color: #4b5563;
    }
    select.contact-input {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 1.25rem;
        padding-right: 2.5rem;
    }
    .dark select.contact-input option {
        background: #0D1117;
        color: #e5e7eb;
    }
    .info-card {
        transition: all 0.3s ease;
    }
    .info-card:hover {
        transform: translateY(-2px);
        border-color: #4A7FBF44;
    }
    
</style>

    {{-- Hero --}}
    <x-hero-section>
        <x-terminal-prompt command="contact:new" />
        <h1 class="text-4xl md:text-5xl font-extrabold mb-4 tracking-tight text-gray-900 dark:text-white">Let's Build Something <span class="text-[#4A7FBF]">Together</span></h1>
        <p class="text-gray-600 dark:text-gray-400 text-lg md:text-xl leading-relaxed">Have a project in mind? Need help modernizing a legacy codebase? Or just want to talk shop about Laravel? I'd love to hear from you.</p>
        <p class="text-green-600 dark:text-green-400 text-sm mt-4 flex items-center gap-2">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-500 opacity-75"></span>
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
                    <h2 class="text-2xl font-extrabold mb-8 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-[#4A7FBF]/10 flex items-center justify-center">
                            <x-icon name="mail" class="w-4 h-4 text-[#4A7FBF]" />
                        </span>
                        Send a Message
                    </h2>

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
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name</label>
                                <input type="text" id="name" name="name" required class="contact-input" placeholder="Your name">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email</label>
                                <input type="email" id="email" name="email" required class="contact-input" placeholder="you@example.com">
                            </div>
                        </div>

                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">What can I help with?</label>
                            <select id="type" name="type" class="contact-input">
                                <option value="freelance">Freelance Project</option>
                                <option value="consulting">Consulting / Code Review</option>
                                <option value="modernization">Legacy Modernization</option>
                                <option value="collaboration">Collaboration</option>
                                <option value="other">Just Saying Hi</option>
                            </select>
                        </div>

                        <div>
                            <label for="budget" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Budget Range <span class="text-gray-600">(optional)</span></label>
                            <select id="budget" name="budget" class="contact-input">
                                <option value="">Prefer not to say</option>
                                <option value="small">Under $5,000</option>
                                <option value="medium">$5,000 – $15,000</option>
                                <option value="large">$15,000 – $50,000</option>
                                <option value="enterprise">$50,000+</option>
                            </select>
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Message</label>
                            <textarea id="message" name="message" rows="6" required class="contact-input" placeholder="Tell me about your project, timeline, and any specific requirements..."></textarea>
                        </div>

                        <button type="submit" class="inline-flex items-center gap-2 px-8 py-3.5 bg-[#4A7FBF] hover:bg-[#5A8FD0] text-white font-bold rounded-xl transition-all hover:-translate-y-0.5" style="box-shadow: 0 0 30px rgba(74,127,191,0.3);">
                            Send Message
                            <x-icon name="arrow-right" class="w-4 h-4" />
                        </button>
                    </form>
                </div>

                {{-- Sidebar --}}
                <div class="lg:w-80 flex-shrink-0 space-y-6">
                    {{-- What to Expect --}}
                    <x-card class="p-6">
                        <h3 class="font-bold mb-4 text-gray-900 dark:text-white flex items-center gap-2">
                            <x-icon name="info" class="w-4 h-4 text-[#4A7FBF]" />
                            What to Expect
                        </h3>
                        <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                            <li class="flex items-start gap-2">
                                <x-icon name="check" class="w-4 h-4 text-[#4A7FBF] mt-0.5 flex-shrink-0" />
                                I'll respond within 24–48 hours
                            </li>
                            <li class="flex items-start gap-2">
                                <x-icon name="check" class="w-4 h-4 text-[#4A7FBF] mt-0.5 flex-shrink-0" />
                                Free initial consultation call
                            </li>
                            <li class="flex items-start gap-2">
                                <x-icon name="check" class="w-4 h-4 text-[#4A7FBF] mt-0.5 flex-shrink-0" />
                                Detailed project scope & estimate
                            </li>
                            <li class="flex items-start gap-2">
                                <x-icon name="check" class="w-4 h-4 text-[#4A7FBF] mt-0.5 flex-shrink-0" />
                                No obligation, no pressure
                            </li>
                        </ul>
                    </x-card>

                    {{-- Services --}}
                    <x-card class="p-6">
                        <h3 class="font-bold mb-4 text-gray-900 dark:text-white flex items-center gap-2">
                            <x-icon name="settings" class="w-4 h-4 text-[#4A7FBF]" />
                            Services
                        </h3>
                        <div class="space-y-3">
                            <div class="info-card px-4 py-3 rounded-xl border border-gray-200 dark:border-[#1e2a3a] bg-gray-50 dark:bg-[#0D1117]/50">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Custom Laravel Development</p>
                                <p class="text-xs text-gray-500 mt-0.5">Full-stack applications built right</p>
                            </div>
                            <div class="info-card px-4 py-3 rounded-xl border border-gray-200 dark:border-[#1e2a3a] bg-gray-50 dark:bg-[#0D1117]/50">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Legacy Modernization</p>
                                <p class="text-xs text-gray-500 mt-0.5">CodeIgniter, CakePHP, Yii → Laravel</p>
                            </div>
                            <div class="info-card px-4 py-3 rounded-xl border border-gray-200 dark:border-[#1e2a3a] bg-gray-50 dark:bg-[#0D1117]/50">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Code Review & Consulting</p>
                                <p class="text-xs text-gray-500 mt-0.5">Architecture guidance & best practices</p>
                            </div>
                            <div class="info-card px-4 py-3 rounded-xl border border-gray-200 dark:border-[#1e2a3a] bg-gray-50 dark:bg-[#0D1117]/50">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Testing Strategy</p>
                                <p class="text-xs text-gray-500 mt-0.5">Feature, Integration & Unit test suites</p>
                            </div>
                        </div>
                    </x-card>

                    {{-- Connect --}}
                    <x-card class="p-6">
                        <h3 class="font-bold mb-4 text-gray-900 dark:text-white flex items-center gap-2">
                            <x-icon name="chat" class="w-4 h-4 text-[#4A7FBF]" />
                            Other Ways to Connect
                        </h3>
                        <x-social-links variant="list" />
                    </x-card>
                </div>
            </div>
    </x-page-section>
@endsection
