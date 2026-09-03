<x-app-layout>
    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h1>
    </x-slot>

    <x-admin.card>
        {{ __("You're logged in!") }}
    </x-admin.card>
</x-app-layout>
