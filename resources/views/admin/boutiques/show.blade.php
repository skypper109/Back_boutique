@extends('layouts.admin')

@section('content')
    <div class="animate-fade-in">
        <!-- Header with Back Button and Shop Identity -->
        <div class="dashboard-header flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
            <div class="flex items-center gap-6">
                <a href="{{ route('admin.boutiques.index') }}"
                    class="w-12 h-12 rounded-2xl bg-white shadow-sm border border-slate-200 flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-200 transition-all group"
                    title="Retour">
                    <i class="bi bi-arrow-left text-xl group-hover:-translate-x-1 transition-transform"></i>
                </a>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="page-title text-4xl font-black text-slate-900 tracking-tight">{{ $boutique->nom }}</h1>
                        <span
                            class="px-2.5 py-1 bg-indigo-100 text-indigo-700 rounded-lg text-[10px] font-black uppercase tracking-widest border border-indigo-200 shadow-sm">
                            {{ $boutique->nature?->name ?? 'Standard' }}
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-2">
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 text-slate-500 rounded-full text-xs font-bold ring-1 ring-slate-200">
                            <i class="bi bi-geo-alt text-indigo-500"></i> {{ $boutique->adresse }}
                        </span>
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 bg-white text-slate-600 rounded-full text-xs font-bold ring-1 ring-slate-200 shadow-sm">
                            <i class="bi bi-telephone text-emerald-500"></i> {{ $boutique->telephone }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.boutiques.users', $boutique->id) }}"
                    class="btn bg-slate-900 text-white hover:bg-black shadow-xl shadow-slate-200">
                    <i class="bi bi-people-fill"></i>
                    <span>Gestion Personnel</span>
                </a>
            </div>
        </div>

        <!-- Quick Stats and Status -->
        <div class="stats-grid mb-12">
            <div class="stat-card bg-indigo-600 border-none shadow-2xl shadow-indigo-100">
                <div class="stat-icon text-white opacity-10"><i class="bi bi-graph-up"></i></div>
                <span class="stat-label text-indigo-100">Performance Cumulative</span>
                <div class="stat-value">
                    <h2 class="text-white">{{ number_format($totalRevenue, 0, ',', ' ') }}</h2>
                    <span class="currency text-white/60">FCFA</span>
                </div>
                <p class="text-[10px] text-white/50 mt-4 font-black uppercase tracking-tighter">Depuis l'ouverture</p>
            </div>

            <div class="stat-card">
                <div class="stat-icon text-amber-500"><i class="bi bi-receipt"></i></div>
                <span class="stat-label">Volume de Transactions</span>
                <div class="stat-value">
                    <h2>{{ number_format($salesCount, 0, ',', ' ') }}</h2>
                    <span class="currency text-slate-400 font-medium">Factures</span>
                </div>
                <div class="w-full bg-slate-100 h-1.5 rounded-full mt-5 overflow-hidden">
                    <div class="bg-amber-400 h-full w-[65%] rounded-full"></div>
                </div>
            </div>

            <div class="stat-card overflow-hidden relative">
                <div class="absolute top-0 right-0 p-4">
                    @if ($boutique->is_active)
                        <div class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></div>
                    @else
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    @endif
                </div>
                <span class="stat-label">État Opérationnel</span>
                <div class="stat-value mt-1">
                    <h2 class="text-xl uppercase {{ $boutique->is_active ? 'text-slate-900' : 'text-red-600' }}">
                        {{ $boutique->is_active ? 'Ouverte' : 'Suspendue' }}
                    </h2>
                </div>

                <div class="mt-8 pt-4 border-t border-slate-50">
                    <form action="{{ route('admin.boutiques.toggle-status', $boutique->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl border {{ $boutique->is_active ? 'border-red-100 text-red-600 hover:bg-red-50' : 'border-indigo-100 text-indigo-600 hover:bg-indigo-50' }} font-black text-xs uppercase tracking-widest transition-all">
                            <i class="bi {{ $boutique->is_active ? 'bi-slash-circle' : 'bi-check-circle' }}"></i>
                            {{ $boutique->is_active ? 'Bloquer l\'accès' : 'Rétablir l\'accès' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tables Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <!-- Top Products -->
            <div class="card p-0 overflow-hidden shadow-sm">
                <div class="p-6 border-b border-slate-100 bg-white/50 backdrop-blur-sm flex items-center justify-between">
                    <div>
                        <h3 class="font-black text-slate-800 flex items-center gap-2">
                            <i class="bi bi-star-fill text-amber-400"></i> Top Articles Vendus
                        </h3>
                    </div>
                </div>
                <div class="table-wrapper border-none shadow-none rounded-none">
                    <table>
                        <thead>
                            <tr>
                                <th>Désignation</th>
                                <th style="text-align: center;">Vendus</th>
                                <th style="text-align: right;">Généré</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $tp)
                                <tr class="group hover:bg-slate-50 transition-colors">
                                    <td>
                                        <div class="font-black text-slate-800">{{ $tp->produit->nom }}</div>
                                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">
                                            {{ $tp->produit->reference ?? 'Sans Réf.' }}</div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span
                                            class="px-2 py-1 rounded bg-indigo-50 text-indigo-600 text-xs font-black">{{ $tp->total_qty }}</span>
                                    </td>
                                    <td style="text-align: right;">
                                        <div class="font-black text-emerald-600">
                                            {{ number_format($tp->total_amount, 0, ',', ' ') }}
                                            <span class="text-[9px] opacity-60 ml-0.5">FCFA</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-20">
                                        <i class="bi bi-inbox text-4xl text-slate-200 block mb-3"></i>
                                        <span class="text-slate-400 text-xs italic font-medium">Aucune donnée de vente
                                            disponible.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card p-0 overflow-hidden shadow-sm">
                <div class="p-6 border-b border-slate-100 bg-white/50 backdrop-blur-sm">
                    <h3 class="font-black text-slate-800 flex items-center gap-2">
                        <i class="bi bi-activity text-indigo-500"></i> Activité Récente
                    </h3>
                </div>
                <div class="table-wrapper border-none shadow-none rounded-none">
                    <table>
                        <thead>
                            <tr>
                                <th>Intervenant</th>
                                <th>Moment</th>
                                <th style="text-align: right;">Valeur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSales as $sale)
                                <tr class="group hover:bg-slate-50 transition-colors">
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-8 h-8 rounded-lg bg-slate-100 text-slate-400 flex items-center justify-center text-xs">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <div>
                                                <div class="font-black text-slate-800 text-xs">
                                                    {{ $sale->user->name ?? 'Système' }}</div>
                                                <div class="text-[9px] text-slate-400 font-bold uppercase">
                                                    {{ $sale->user?->role ?? 'Agent' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-[10px] font-bold text-slate-500 flex items-center gap-1.5">
                                            <i class="bi bi-clock"></i>
                                            {{ $sale->created_at->diffForHumans() }}
                                        </div>
                                    </td>
                                    <td style="text-align: right;">
                                        <div class="font-black text-slate-900">
                                            {{ number_format($sale->montant_total, 0, ',', ' ') }}
                                            <span class="text-[9px] opacity-40 ml-0.5 text-slate-500">FCFA</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-20">
                                        <i class="bi bi-receipt text-4xl text-slate-200 block mb-3"></i>
                                        <span class="text-slate-400 text-xs italic font-medium">Historique des transactions
                                            vide.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        .animate-fade-in {
            animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
                filter: blur(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
                filter: blur(0);
            }
        }
    </style>
@endsection
