<x-mail::layout>
    <x-mail::header />
    
    <x-mail::body>
        {!! nl2br(e($content)) !!}
    </x-mail::body>

    <x-mail::footer />
</x-mail::layout>