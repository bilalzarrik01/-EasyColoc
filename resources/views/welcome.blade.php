<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'EasyColoc') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="space-body">
    <div class="space-grid relative px-4 py-8 md:px-8 lg:px-10">
        <main class="mx-auto max-w-6xl space-y-8 md:space-y-10">
            <section class="space-panel p-4 md:p-8">
                <div class="grid gap-6 lg:grid-cols-[1.35fr_1fr]">
                    <div class="space-y-6">
                        <div class="space-chip flex items-center gap-3 px-4 py-3">
                            <span class="text-xl leading-none">⌕</span>
                            <span class="space-muted text-sm md:text-base">Qui doit quoi dans votre colocation ?</span>
                        </div>

                        <div>
                            <p class="space-muted text-xs tracking-[0.32em] md:text-sm">When it matters most, teams and squad come first.</p>
                            <h1 class="space-title mt-3 text-4xl font-extrabold leading-none md:text-6xl">EasyColoc</h1>
                            <p class="space-muted mt-3 max-w-xl text-sm md:text-base">
                                Gérez les dépenses communes, équilibrez automatiquement les dettes, et suivez les paiements sans calcul manuel.
                            </p>
                        </div>

                        @if (Route::has('login'))
                            <div class="flex flex-wrap items-center gap-3">
                                <a href="{{ route('login') }}" class="space-button px-7 py-2.5 text-sm font-semibold uppercase tracking-wide">
                                    Login
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="space-button px-7 py-2.5 text-sm font-semibold uppercase tracking-wide">
                                        Créer Compte
                                    </a>
                                @endif
                            </div>
                        @endif

                        <div class="flex flex-wrap gap-3 pt-1">
                            <div class="space-icon flex h-12 w-12 items-center justify-center text-xl">⌂</div>
                            <div class="space-icon flex h-12 w-12 items-center justify-center text-xl">▦</div>
                            <div class="space-icon flex h-12 w-12 items-center justify-center text-xl">⚙</div>
                            <div class="space-icon flex h-12 w-12 items-center justify-center text-xl">👤</div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <article class="space-card float-card p-5 md:p-6">
                            <h2 class="space-title text-2xl font-bold md:text-3xl">Colocation</h2>
                            <p class="mt-3 text-sm leading-relaxed text-indigo-100/95 md:text-base">
                                EasyColoc calcule instantanément les soldes de chaque membre, propose une vue “qui paie qui”, et enregistre les règlements.
                            </p>
                        </article>

                        <article class="space-chip float-card float-card-delay p-4">
                            <p class="text-sm font-medium text-indigo-100 md:text-base">
                                Vue claire, règles simples, zéro conflit sur les dépenses.
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="space-panel p-4 md:p-8">
                <div class="space-chip mx-auto mb-6 max-w-xl px-5 py-2 text-center text-sm font-semibold tracking-wide text-indigo-100">
                    RÉSUMÉ RAPIDE D'EASYCOLOC
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    <article class="space-card p-5">
                        <p class="text-sm leading-relaxed text-indigo-100/95 md:text-base">
                            Ajoutez une dépense avec montant, date, catégorie et payeur. L’application répartit automatiquement la part de chacun.
                        </p>
                        <button class="space-button mt-5 px-4 py-2 text-sm font-semibold">Continuer →</button>
                    </article>

                    <article class="space-card p-5">
                        <p class="text-sm leading-relaxed text-indigo-100/95 md:text-base">
                            Obtenez une synthèse claire des dettes, marquez les paiements, et suivez la réputation financière des membres.
                        </p>
                        <button class="space-button mt-5 px-4 py-2 text-sm font-semibold">Voir Plus →</button>
                    </article>
                </div>
            </section>

            <section class="space-panel p-4 md:p-7">
                <div class="grid gap-4 md:grid-cols-4 md:grid-rows-2">
                    <div class="gallery-item md:row-span-1">
                        <img src="{{ asset('images/home-gallery/balance.jpg') }}"
                             alt="Budget and balance calculation"
                             class="h-full w-full object-cover" />
                        <span class="gallery-tag">Balance</span>
                    </div>

                    <div class="gallery-item md:row-span-1">
                        <img src="{{ asset('images/home-gallery/settlements.jpg') }}"
                             alt="Mobile payment settlement"
                             class="h-full w-full object-cover" />
                        <span class="gallery-tag">Settlements</span>
                    </div>

                    <div class="gallery-item md:col-span-2 md:row-span-2">
                        <img src="{{ asset('images/home-gallery/colocation-hub.jpg') }}"
                             alt="Shared apartment living room"
                             class="h-full w-full object-cover" />
                        <span class="gallery-tag">Colocation Hub</span>
                    </div>

                    <div class="gallery-item md:row-span-1">
                        <img src="{{ asset('images/home-gallery/members.jpg') }}"
                             alt="Colocation members together"
                             class="h-full w-full object-cover" />
                        <span class="gallery-tag">Members</span>
                    </div>

                    <div class="gallery-item md:row-span-1">
                        <img src="{{ asset('images/home-gallery/expenses.jpg') }}"
                             alt="Expense tracking and bills"
                             class="h-full w-full object-cover" />
                        <span class="gallery-tag">Expenses</span>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
