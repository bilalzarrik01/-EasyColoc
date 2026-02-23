<x-app-layout>
    <x-slot name="header">
        <h2 class="space-title font-semibold text-xl text-indigo-50 leading-tight">
            {{ __('Create Colocation') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="space-panel sm:rounded-2xl p-6">
                <form method="POST" action="{{ route('colocations.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="name" :value="__('Colocation Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                      :value="old('name')" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-3">
                        <x-primary-button>{{ __('Create') }}</x-primary-button>
                        <a href="{{ route('colocations.index') }}" class="text-sm text-indigo-100/85 hover:text-indigo-50">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
