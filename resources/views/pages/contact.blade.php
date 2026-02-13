@extends('layouts.app')

@section('title', 'Contact')

@section('content')
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="max-w-2xl">
            <h1 class="text-3xl font-bold mb-4">Let's Work Together</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-10">
                Have a project in mind? Need a Laravel developer? Just want to chat about code? 
                I'd love to hear from you.
            </p>

            <form action="#" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium mb-2">Name</label>
                        <input type="text" id="name" name="name" required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium mb-2">Email</label>
                        <input type="email" id="email" name="email" required
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition">
                    </div>
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium mb-2">What can I help with?</label>
                    <select id="type" name="type"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition">
                        <option value="freelance">Freelance Project</option>
                        <option value="consulting">Consulting</option>
                        <option value="collaboration">Collaboration</option>
                        <option value="other">Just Saying Hi</option>
                    </select>
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium mb-2">Message</label>
                    <textarea id="message" name="message" rows="6" required
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition"
                        placeholder="Tell me about your project..."></textarea>
                </div>

                <button type="submit" class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                    Send Message
                </button>
            </form>
        </div>
    </div>
@endsection
