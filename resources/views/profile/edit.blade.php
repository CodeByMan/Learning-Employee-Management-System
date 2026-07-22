<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="space-y-4">
        <div class="member-panel">
            <div class="max-w-3xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="member-panel">
            <div class="max-w-3xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="member-panel">
            <div class="max-w-3xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-app-layout>
