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

        <!-- Section Abonnement & Licences -->
        <div class="glass-card rounded-[2.5rem] p-8 mb-12 shadow-xl border border-slate-200/80">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-slate-100">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl shadow-sm">
                        <i class="bi bi-key-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">Abonnement & Licences</h3>
                        <p class="text-xs text-slate-500 font-medium">Contrôlez la validité de l'accès de la caisse et générez des clés d'activation.</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button onclick="document.getElementById('modalGenereCleBoutique').classList.remove('hidden')"
                        class="btn-action bg-primary-600 text-white shadow-lg shadow-primary-500/20 hover:bg-primary-700 text-xs">
                        <i class="bi bi-magic"></i>
                        <span>Générer une Clé</span>
                    </button>
                    <a href="{{ route('admin.licences.index', ['boutique_id' => $boutique->id]) }}"
                        class="btn-action bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs">
                        <i class="bi bi-list-stars"></i>
                        <span>Historique Clés</span>
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 py-6 border-b border-slate-100">
                <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 block mb-1">Statut Actuel</span>
                    @if ($boutique->date_expiration_licence === null)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-black bg-purple-100 text-purple-800">
                            <i class="bi bi-infinity"></i> Actif à Vie (Illimité)
                        </span>
                    @elseif ($boutique->isLicenceExpired())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-black bg-rose-100 text-rose-800">
                            <i class="bi bi-exclamation-octagon-fill"></i> Licence Expirée
                        </span>
                    @else
                        @php $jours = $boutique->joursRestants(); @endphp
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-black {{ $jours <= 7 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                            <span class="w-2 h-2 rounded-full {{ $jours <= 7 ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500' }}"></span>
                            Valide &bull; {{ $jours }} jours restants
                        </span>
                    @endif
                </div>

                <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 block mb-1">Date d'Échéance</span>
                    <p class="text-lg font-black text-slate-800">
                        {{ $boutique->date_expiration_licence ? \Carbon\Carbon::parse($boutique->date_expiration_licence)->format('d/m/Y à H:i') : 'Aucune restriction' }}
                    </p>
                </div>

                <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100">
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 block mb-2">Prolongation Directe en 1 Clic</span>
                    <form action="{{ route('admin.boutiques.prolonger-licence', $boutique->id) }}" method="POST" class="flex flex-wrap gap-2">
                        @csrf
                        <input type="hidden" name="note" value="Prolongation rapide depuis fiche boutique">
                        <button type="submit" name="duree_jours" value="30" class="px-3 py-1.5 bg-white hover:bg-emerald-50 text-emerald-700 hover:border-emerald-300 border border-slate-200 rounded-xl text-xs font-bold transition-all shadow-sm">
                            +30j
                        </button>
                        <button type="submit" name="duree_jours" value="90" class="px-3 py-1.5 bg-white hover:bg-emerald-50 text-emerald-700 hover:border-emerald-300 border border-slate-200 rounded-xl text-xs font-bold transition-all shadow-sm">
                            +90j
                        </button>
                        <button type="submit" name="duree_jours" value="365" class="px-3 py-1.5 bg-white hover:bg-emerald-50 text-emerald-700 hover:border-emerald-300 border border-slate-200 rounded-xl text-xs font-bold transition-all shadow-sm">
                            +1 An
                        </button>
                        <button type="submit" name="duree_jours" value="99999" class="px-3 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded-xl text-xs font-bold transition-all shadow-sm">
                            À Vie
                        </button>
                    </form>
                </div>
            </div>

            <!-- Dernières clés générées pour cette boutique -->
            <div class="mt-6">
                <h4 class="text-xs font-black uppercase tracking-widest text-slate-400 mb-3">Dernières clés d'activation de cette boutique</h4>
                @if($boutique->licences->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($boutique->licences->take(6) as $lic)
                    <div class="p-3 bg-white rounded-xl border border-slate-200 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-mono font-bold text-slate-900 select-all">{{ $lic->cle_licence }}</span>
                            <span class="text-[10px] text-slate-400 block">{{ $lic->duree_jours >= 90000 ? 'À vie' : $lic->duree_jours . 'j' }} &bull; {{ $lic->created_at->format('d/m/Y') }}</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $lic->statut === 'inutilisee' ? 'bg-amber-100 text-amber-800' : ($lic->statut === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600') }}">
                            {{ $lic->statut === 'inutilisee' ? 'En attente' : ($lic->statut === 'active' ? 'Active' : $lic->statut) }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-xs text-slate-400 italic">Aucune clé de licence générée pour cette boutique.</p>
                @endif
            </div>
        </div>

        <!-- Modal Génération Clé Dédiée à cette Boutique -->
        <div id="modalGenereCleBoutique" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-6">
            <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-xl" onclick="this.parentElement.classList.add('hidden')"></div>
            <div class="relative w-full max-w-md glass-card rounded-[2.5rem] p-8 shadow-2xl">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-black text-slate-900">Nouvelle Clé pour {{ $boutique->nom }}</h3>
                    <button onclick="document.getElementById('modalGenereCleBoutique').classList.add('hidden')" class="text-slate-400 hover:text-slate-900">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <form action="{{ route('admin.licences.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="boutique_id" value="{{ $boutique->id }}">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Durée</label>
                        <select name="duree_jours" class="w-full px-4 py-3 bg-slate-100 border-none rounded-2xl text-sm font-bold">
                            <option value="30">1 Mois (30 jours)</option>
                            <option value="90">3 Mois (90 jours)</option>
                            <option value="180">6 Mois (180 jours)</option>
                            <option value="365">1 An (365 jours)</option>
                            <option value="99999">À Vie (Illimité)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Note (Facultatif)</label>
                        <input type="text" name="note" placeholder="Ex: Règlement reçu" class="w-full px-4 py-3 bg-slate-100 border-none rounded-2xl text-sm font-semibold">
                    </div>
                    <div class="pt-4 flex justify-end gap-3">
                        <button type="button" onclick="document.getElementById('modalGenereCleBoutique').classList.add('hidden')" class="px-4 py-2 font-bold text-slate-500">Annuler</button>
                        <button type="submit" class="btn-action bg-primary-600 text-white">Générer la Clé</button>
                    </div>
                </form>
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
