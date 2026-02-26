<x-app-layout>
    <x-slot name="header">
        <h2 class="space-title font-semibold text-xl text-indigo-50 leading-tight">
            Invitation
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="space-chip text-indigo-50 px-4 py-3 rounded-xl">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="space-card text-red-100 px-4 py-3 rounded-xl">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="space-panel sm:rounded-2xl p-6">
                <h3 class="space-title text-lg font-medium text-indigo-50 mb-4">
                    {{ $invitation->colocation->name }}
                </h3>

                <div class="space-y-2 text-indigo-100/90">
                    <p><span class="font-semibold text-indigo-50">Invited email:</span> {{ $invitation->email }}</p>
                    <p><span class="font-semibold text-indigo-50">Status:</span> {{ strtoupper($invitation->status) }}</p>
                    <p><span class="font-semibold text-indigo-50">Invited by:</span> {{ $invitation->inviter?->name ?? '-' }}</p>
                    <p><span class="font-semibold text-indigo-50">Expires at:</span> {{ $invitation->expires_at?->format('Y-m-d H:i') ?? '-' }}</p>
                </div>

                @if ($canRespond && $invitation->status === 'pending' && $invitation->colocation->status === 'active')
                    <div class="mt-6 flex flex-wrap gap-3">
                        <form method="POST" action="{{ route('invitations.accept', $invitation->token) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="space-button inline-flex items-center px-4 py-2 text-sm font-medium">
                                Accept invitation
                            </button>
                        </form>

                        <form method="POST" action="{{ route('invitations.refuse', $invitation->token) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                    class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm text-white hover:bg-red-500">
                                Refuse invitation
                            </button>
                        </form>
                    </div>
                @elseif (! $canRespond)
                    <div class="mt-6 rounded-md bg-indigo-400/15 px-4 py-3 text-sm text-indigo-100/90">
                        Only <span class="font-semibold text-indigo-50">{{ $invitation->email }}</span> can accept or refuse this invitation.
                        Log in with that email to respond.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
