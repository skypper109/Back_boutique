@extends('layouts.admin')

@section('content')
    <div class="space-y-12">
        <!-- Hero Header -->
        <div class="relative overflow-hidden rounded-[2.5rem] bg-indigo-950 p-12 text-white shadow-2xl shadow-indigo-200">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-8">
                <div class="space-y-3">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/20 border border-indigo-400/30 text-indigo-300 text-[10px] font-black uppercase tracking-widest">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                        Système Central Opérationnel
                    </div>
                    <h1 class="text-5xl font-extrabold tracking-tight leading-none">Aperçu <span
                            class="text-indigo-400">Global.</span></h1>
                    <p class="text-indigo-200/60 font-medium max-w-md">Découvrez les performances de votre réseau de
                        boutiques en temps réel.</p>
                </div>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('admin.boutiques.index') }}"
                        class="group flex items-center gap-3 px-8 py-4 bg-white text-slate-900 rounded-3xl font-black transition-all hover:scale-105 active:scale-95 shadow-xl shadow-black/20">
                        <i class="bi bi-shop-window text-xl group-hover:rotate-12 transition-transform"></i>
                        Gérer le Réseau
                    </a>
                </div>
            </div>

            <!-- Abstract Decoration -->
            <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-primary-500/20 blur-[120px] rounded-full"></div>
            <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 bg-indigo-400/10 blur-[80px] rounded-full"></div>
        </div>

        <!-- Key Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Revenue Card -->
            <div class="glass-card group relative p-8 rounded-[2.5rem] overflow-hidden transition-all hover:-translate-y-2">
                <div class="relative z-10 space-y-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-primary-50 text-primary-600 flex items-center justify-center text-2xl shadow-inner group-hover:scale-110 transition-transform">
                        <i class="bi bi-bank2"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Volume
                            d'Affaires</p>
                        <div class="flex items-baseline gap-2">
                            <span
                                class="text-4xl font-black text-slate-900 tracking-tighter">{{ number_format($totalSystemRevenue, 0, ',', ' ') }}</span>
                            <span class="text-sm font-extrabold text-primary-500 uppercase">FCFA</span>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-500">Flux système global</span>
                        <span
                            class="px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-700 text-[10px] font-black uppercase">+12%</span>
                    </div>
                </div>
                <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <i class="bi bi-bank2 text-[120px]"></i>
                </div>
            </div>

            <!-- Shops Card -->
            <div class="glass-card group relative p-8 rounded-[2.5rem] overflow-hidden transition-all hover:-translate-y-2">
                <div class="relative z-10 space-y-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl shadow-inner group-hover:scale-110 transition-transform">
                        <i class="bi bi-shop"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">Points
                            de Vente</p>
                        <div class="flex items-baseline gap-2 text-4xl font-black text-slate-900 tracking-tighter">
                            <span>{{ $boutiquesCount }}</span>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-500">{{ $boutiquePerformance->count() }} boutiques
                            actives</span>
                        <i class="bi bi-arrow-right text-slate-400"></i>
                    </div>
                </div>
                <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <i class="bi bi-shop text-[120px]"></i>
                </div>
            </div>

            <!-- Staff Card -->
            <div class="glass-card group relative p-8 rounded-[2.5rem] overflow-hidden transition-all hover:-translate-y-2">
                <div class="relative z-10 space-y-4">
                    <div
                        class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-2xl shadow-inner group-hover:scale-110 transition-transform">
                        <i class="bi bi-people"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none mb-1">
                            Effectif Total</p>
                        <div class="flex items-baseline gap-2 text-4xl font-black text-slate-900 tracking-tighter">
                            <span>{{ $usersCount }}</span>
                        </div>
                    </div>
                    <div class="pt-4 border-t border-slate-100">
                        <div class="flex -space-x-3 overflow-hidden">
                            @for ($i = 0; $i < 5; $i++)
                                <div
                                    class="inline-block h-8 w-8 rounded-full bg-slate-100 border-2 border-white flex items-center justify-center text-[10px] font-black text-slate-500">
                                    {{ chr(65 + $i) }}
                                </div>
                            @endfor
                            <div
                                class="inline-block h-8 w-8 rounded-full bg-slate-950 flex items-center justify-center text-[10px] font-black text-white">
                                +{{ $usersCount > 5 ? $usersCount - 5 : 0 }}</div>
                        </div>
                    </div>
                </div>
                <div class="absolute -right-4 -bottom-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <i class="bi bi-people text-[120px]"></i>
                </div>
            </div>
        </div>

        <!-- Performance Section -->
        <div class="glass-card rounded-[3rem] overflow-hidden">
            <div
                class="p-10 flex flex-col md:flex-row md:items-end justify-between gap-6 bg-white/50 border-b border-slate-100">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 text-primary-600">
                        <i class="bi bi-award-fill text-xl"></i>
                        <span class="text-[10px] font-black uppercase tracking-[0.2em]">Excellence Retail</span>
                    </div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">Top Performance <span
                            class="text-slate-400">Boutiques.</span></h2>
                    <p class="text-slate-500 font-medium text-sm">Classement calculé sur le chiffre d'affaires cumulé.</p>
                </div>
                <div class="flex gap-4">
                    <button
                        class="flex items-center gap-3 px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-2xl font-bold transition-all text-xs">
                        <i class="bi bi-funnel"></i>
                        Filtrer par Nature
                    </button>
                    <button
                        class="flex items-center gap-3 px-6 py-3 bg-primary-600 text-white rounded-2xl font-bold transition-all text-xs shadow-lg shadow-primary-500/20 active:scale-95">
                        <i class="bi bi-download"></i>
                        Exporter Rapport
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-2 px-10">
                    <thead class="bg-transparent uppercase text-[10px] font-black text-slate-400 tracking-widest">
                        <tr>
                            <th class="px-6 py-8">Boutique</th>
                            <th class="px-6 py-8">Nature d'Activité</th>
                            <th class="px-6 py-8 text-right">Volume Ventes</th>
                            <th class="px-6 py-8 text-center">Status Opérationnel</th>
                            <th class="px-6 py-8"></th>
                        </tr>
                    </thead>
                    <tbody class="space-y-4">
                        @foreach ($boutiquePerformance as $index => $boutique)
                            <tr class="group transition-all hover:bg-slate-50/50 rounded-3xl">
                                <td class="px-6 py-6 first:rounded-l-[2rem]">
                                    <div class="flex items-center gap-5">
                                        <div
                                            class="w-12 h-12 rounded-2xl {{ $index == 0 ? 'bg-amber-100 text-amber-600' : ($index < 3 ? 'bg-indigo-100 text-indigo-600' : 'bg-slate-100 text-slate-500') }} flex items-center justify-center font-black text-xl shadow-inner group-hover:rotate-6 transition-transform">
                                            {{ $index + 1 }}
                                        </div>
                                        <div>
                                            <div class="font-black text-slate-900 tracking-tight">{{ $boutique->nom }}</div>
                                            <div
                                                class="text-[10px] font-bold text-slate-400 uppercase flex items-center gap-1.5">
                                                <i class="bi bi-geo-alt"></i>
                                                {{ $boutique->adresse }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-6 font-bold text-slate-700">
                                    <span
                                        class="px-4 py-1.5 bg-slate-100 text-slate-500 rounded-full text-[10px] font-black uppercase tracking-wider group-hover:bg-primary-50 group-hover:text-primary-600 transition-colors">
                                        {{ $boutique->nature?->name ?? 'Standard' }}
                                    </span>
                                </td>
                                <td class="px-6 py-6 text-right">
                                    <div class="font-black text-slate-900 text-lg tabular-nums">
                                        {{ number_format($boutique->revenue ?? 0, 0, ',', ' ') }}
                                        <span class="text-[10px] text-slate-400 font-extrabold ml-1">FCFA</span>
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    <div class="flex justify-center">
                                        @if ($boutique->is_active)
                                            <span
                                                class="inline-flex items-center gap-2 px-4 py-1.5 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase rounded-full ring-1 ring-emerald-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                Actif
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-2 px-4 py-1.5 bg-rose-50 text-rose-600 text-[10px] font-black uppercase rounded-full ring-1 ring-rose-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Suspendu
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-6 last:rounded-r-[2rem]">
                                    <a href="{{ route('admin.boutiques.show', $boutique) }}"
                                        class="flex items-center justify-center w-10 h-10 rounded-2xl border border-slate-200 text-slate-400 hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-all active:scale-90">
                                        <i class="bi bi-chevron-right text-lg"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
