@extends('layouts.app')

@section('title', 'Contact')

@section('content')
<style>
    .noise-overlay { position: relative; }
    .noise-overlay::after {
        content: '';
        position: absolute;
        inset: 0;
        opacity: 0.04;
        pointer-events: none;
        z-index: 1;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        background-repeat: repeat;
        background-size: 256px 256px;
    }
    .dot-grid-bg { position: relative; }
    .dot-grid-bg::before {
        content: '';
        position: absolute;
        inset: 0;
        opacity: 0.03;
        pointer-events: none;
        background-image: radial-gradient(circle, #ffffff 1px, transparent 1px);
        background-size: 24px 24px;
        z-index: 0;
    }
    .dot-grid-bg > * { position: relative; z-index: 1; }

    .contact-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        border: 1px solid #1e2a3a;
        background: #0D1117;
        color: #e5e7eb;
        font-size: 0.875rem;
        outline: none;
        transition: all 0.2s ease;
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
    select.contact-input option {
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
    <div class="noise-overlay relative overflow-hidden border-b border-[#1e2a3a]">
        {{-- Ambient glow --}}
        <div class="absolute top-1/3 left-1/4 w-[600px] h-[600px] rounded-full opacity-[0.06] blur-[120px]" style="background: radial-gradient(circle, #4A7FBF, transparent 70%);"></div>
        <div class="absolute bottom-0 right-1/3 w-[400px] h-[400px] rounded-full opacity-[0.04] blur-[100px]" style="background: radial-gradient(circle, #9D5175, transparent 70%);"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-green-500/10 text-green-400 text-xs font-bold uppercase tracking-widest mb-6 border border-green-500/20">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    Available for Projects
                </div>
                <h1 class="text-4xl md:text-6xl font-extrabold mb-4 tracking-tight">Let's Build Something <span class="text-[#4A7FBF]">Together</span></h1>
                <p class="text-gray-400 text-lg md:text-xl leading-relaxed">Have a project in mind? Need help modernizing a legacy codebase? Or just want to talk shop about Laravel? I'd love to hear from you.</p>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="dot-grid-bg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
            <div class="flex flex-col lg:flex-row gap-16">

                {{-- Form --}}
                <div class="flex-1">
                    <h2 class="text-2xl font-extrabold mb-8 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-[#4A7FBF]/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
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
                                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Name</label>
                                <input type="text" id="name" name="name" required class="contact-input" placeholder="Your name">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                                <input type="email" id="email" name="email" required class="contact-input" placeholder="you@example.com">
                            </div>
                        </div>

                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-300 mb-2">What can I help with?</label>
                            <select id="type" name="type" class="contact-input">
                                <option value="freelance">Freelance Project</option>
                                <option value="consulting">Consulting / Code Review</option>
                                <option value="modernization">Legacy Modernization</option>
                                <option value="collaboration">Collaboration</option>
                                <option value="other">Just Saying Hi</option>
                            </select>
                        </div>

                        <div>
                            <label for="budget" class="block text-sm font-medium text-gray-300 mb-2">Budget Range <span class="text-gray-600">(optional)</span></label>
                            <select id="budget" name="budget" class="contact-input">
                                <option value="">Prefer not to say</option>
                                <option value="small">Under $5,000</option>
                                <option value="medium">$5,000 – $15,000</option>
                                <option value="large">$15,000 – $50,000</option>
                                <option value="enterprise">$50,000+</option>
                            </select>
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-300 mb-2">Message</label>
                            <textarea id="message" name="message" rows="6" required class="contact-input" placeholder="Tell me about your project, timeline, and any specific requirements..."></textarea>
                        </div>

                        <button type="submit" class="inline-flex items-center gap-2 px-8 py-3.5 bg-[#4A7FBF] hover:bg-[#5A8FD0] text-white font-bold rounded-xl transition-all hover:-translate-y-0.5" style="box-shadow: 0 0 30px rgba(74,127,191,0.3);">
                            Send Message
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </form>
                </div>

                {{-- Sidebar --}}
                <div class="lg:w-80 flex-shrink-0 space-y-6">
                    {{-- What to Expect --}}
                    <div class="p-6 rounded-2xl border border-[#1e2a3a] bg-[#0D1117]">
                        <h3 class="font-bold mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            What to Expect
                        </h3>
                        <ul class="space-y-3 text-sm text-gray-400">
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-[#4A7FBF] mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                I'll respond within 24–48 hours
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-[#4A7FBF] mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Free initial consultation call
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-[#4A7FBF] mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Detailed project scope & estimate
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-4 h-4 text-[#4A7FBF] mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                No obligation, no pressure
                            </li>
                        </ul>
                    </div>

                    {{-- Services --}}
                    <div class="p-6 rounded-2xl border border-[#1e2a3a] bg-[#0D1117]">
                        <h3 class="font-bold mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Services
                        </h3>
                        <div class="space-y-3">
                            <div class="info-card px-4 py-3 rounded-xl border border-[#1e2a3a] bg-[#0D1117]/50">
                                <p class="text-sm font-semibold">Custom Laravel Development</p>
                                <p class="text-xs text-gray-500 mt-0.5">Full-stack applications built right</p>
                            </div>
                            <div class="info-card px-4 py-3 rounded-xl border border-[#1e2a3a] bg-[#0D1117]/50">
                                <p class="text-sm font-semibold">Legacy Modernization</p>
                                <p class="text-xs text-gray-500 mt-0.5">CodeIgniter, CakePHP, Yii → Laravel</p>
                            </div>
                            <div class="info-card px-4 py-3 rounded-xl border border-[#1e2a3a] bg-[#0D1117]/50">
                                <p class="text-sm font-semibold">Code Review & Consulting</p>
                                <p class="text-xs text-gray-500 mt-0.5">Architecture guidance & best practices</p>
                            </div>
                            <div class="info-card px-4 py-3 rounded-xl border border-[#1e2a3a] bg-[#0D1117]/50">
                                <p class="text-sm font-semibold">Testing Strategy</p>
                                <p class="text-xs text-gray-500 mt-0.5">Feature, Integration & Unit test suites</p>
                            </div>
                        </div>
                    </div>

                    {{-- Connect --}}
                    <div class="p-6 rounded-2xl border border-[#1e2a3a] bg-[#0D1117]">
                        <h3 class="font-bold mb-4 flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#4A7FBF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                            Other Ways to Connect
                        </h3>
                        <div class="space-y-3">
                            <a href="https://github.com/JeffreyDavidson" target="_blank" class="flex items-center gap-3 text-sm text-gray-400 hover:text-white transition-colors">
                                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                                GitHub
                            </a>
                            <a href="https://x.com/thelaravelarch" target="_blank" class="flex items-center gap-3 text-sm text-gray-400 hover:text-white transition-colors">
                                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                @thelaravelarch
                            </a>
                            <a href="https://bsky.app/profile/thelaravelarch" target="_blank" class="flex items-center gap-3 text-sm text-gray-400 hover:text-white transition-colors">
                                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 10.8c-1.087-2.114-4.046-6.053-6.798-7.995C2.566.944 1.561 1.266.902 1.565.139 1.908 0 3.08 0 3.768c0 .69.378 5.65.624 6.479.785 2.627 3.584 3.493 6.173 3.243-4.058.666-7.652 2.174-4.461 7.744 3.648 5.123 5.353-1.31 5.664-2.978.311 1.669 1.104 8.101 5.664 2.978 3.191-5.57-.403-7.078-4.461-7.744 2.589.25 5.388-.616 6.173-3.243C15.622 9.418 16 4.458 16 3.768c0-.69-.139-1.861-.902-2.203-.659-.299-1.664-.62-4.3 1.24C8.046 4.747 5.087 8.686 4 10.8"/></svg>
                                Bluesky
                            </a>
                            <a href="https://youtube.com/@thelaravelarchitect" target="_blank" class="flex items-center gap-3 text-sm text-gray-400 hover:text-white transition-colors">
                                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814z"/><path fill="#0D1117" d="M9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                YouTube
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
