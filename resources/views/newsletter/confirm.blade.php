@extends('layouts.app')

@section('content')
    <x-page-section>
        <div class="mx-auto max-w-xl text-center">
            <x-terminal-prompt command="newsletter:confirm" />
            <h1 class="mt-6 text-3xl font-extrabold tracking-tight text-gray-900 dark:text-white">Confirm your subscription</h1>
            <p class="mt-4 text-gray-600 dark:text-gray-400">
                Confirm that you want newsletter updates sent to {{ $subscriber->email }}.
            </p>

            <form action="{{ $actionUrl }}" method="POST" class="mt-8">
                @csrf
                <x-button type="submit">Confirm subscription</x-button>
            </form>
        </div>
    </x-page-section>
@endsection
