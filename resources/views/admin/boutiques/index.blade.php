@extends('layouts.admin')

@section('content')
    <div class="space-y-10">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 px-4">
            <div class="space-y-1">
                <h1 class="text-4xl font-black text-slate-900 tracking-tight">Réseau de <span
                        class="text-primary-600 tracking-tighter italic">Boutiques.</span></h1>
                <p class="text-slate-500 font-medium tracking-tight">Gérez les établissements et contrôlez les quotas d'accès
                    au système.</p>
            </div>
            <div class="flex gap-4">
                <button onclick="document.getElementById('createModal').classList.remove('hidden')"
                    class="btn-action bg-primary-600 text-white shadow-xl shadow-primary-500/20 hover:bg-primary-700">
                    <i class="bi bi-plus-circle-fill text-xl"></i>
                    <span>Expansion Réseau</span>
                </button>
            </div>
        </div>

        <!-- Statistics Overview (Mini) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="glass-card p-6 rounded-3xl flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                    <i class="bi bi-shop"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total</p>
                    <p class="text-2xl font-black text-slate-900 leading-none">{{ $boutiques->count() }}</p>
                </div>
            </div>
            <div class="glass-card p-6 rounded-3xl flex items-center gap-4 text-emerald-600">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actives</p>
                    <p class="text-2xl font-black text-slate-900 leading-none">
                        {{ $boutiques->where('is_active', 1)->count() }}</p>
                </div>
            </div>
            <div class="glass-card p-6 rounded-3xl flex items-center gap-4 text-rose-600">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                    <i class="bi bi-pause-circle"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Suspendues</p>
                    <p class="text-2xl font-black text-slate-900 leading-none">
                        {{ $boutiques->where('is_active', 0)->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Main Table Card -->
        <div class="glass-card rounded-[2.5rem] overflow-hidden">
            <div
                class="p-8 border-b border-slate-100 bg-white/50 backdrop-blur-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h3 class="font-black text-slate-900 text-sm uppercase tracking-widest flex items-center gap-3">
                    <i class="bi bi-list-stars text-primary-500 text-lg"></i>
                    Registre des Établissements
                </h3>
                <div class="relative group">
                    <i
                        class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary-500 transition-colors"></i>
                    <input type="text" placeholder="Rechercher une boutique..."
                        class="pl-12 pr-6 py-3 bg-slate-100 border-none rounded-2xl text-sm font-semibold w-full md:w-80 focus:ring-4 focus:ring-primary-500/10 focus:bg-white transition-all">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-1">
                    <thead class="bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">
                        <tr>
                            <th class="px-8 py-6">Identité Boutique</th>
                            <th class="px-8 py-6">Propriétaire & Limit</th>
                            <th class="px-8 py-6 text-center">Status</th>
                            <th class="px-8 py-6 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($boutiques as $boutique)
                            <tr class="group transition-all hover:bg-slate-50/80">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-5">
                                        <div
                                            class="w-14 h-14 rounded-3xl bg-white border border-slate-100 shadow-sm flex items-center justify-center group-hover:scale-110 transition-transform duration-500 overflow-hidden">
                                            <div
                                                class="w-full h-full flex items-center justify-center font-black text-primary-600 text-xl bg-primary-50">
                                                {{ substr($boutique->nom, 0, 1) }}
                                            </div>
                                        </div>
                                        <div class="space-y-0.5">
                                            <p
                                                class="font-black text-slate-900 tracking-tight leading-tight group-hover:text-primary-600 transition-colors">
                                                {{ $boutique->nom }}</p>
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="px-2 py-0.5 rounded-lg bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-tighter">
                                                    {{ $boutique->nature?->name ?? 'Non définie' }}
                                                </span>
                                                <span class="text-[10px] font-medium text-slate-400">
                                                    <i class="bi bi-geo-alt"></i> {{ $boutique->adresse }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    @if ($boutique->creator)
                                        <div class="space-y-2">
                                            <div class="flex items-center gap-2">
                                                <div
                                                    class="w-6 h-6 rounded-full bg-slate-900 flex items-center justify-center text-[10px] font-black text-white">
                                                    {{ substr($boutique->creator->name, 0, 1) }}
                                                </div>
                                                <span
                                                    class="text-sm font-bold text-slate-700">{{ $boutique->creator->name }}</span>
                                            </div>
                                            @php
                                                $count = \App\Models\Boutique::where(
                                                    'user_id',
                                                    $boutique->user_id,
                                                )->count();
                                                $limit = $boutique->creator->boutique_limit;
                                                $pct = min(($count / $limit) * 100, 100);
                                                $colorClass =
                                                    $pct >= 90
                                                        ? 'bg-rose-500'
                                                        : ($pct >= 70
                                                            ? 'bg-amber-500'
                                                            : 'bg-primary-500');
                                            @endphp
                                            <div class="w-40 space-y-1">
                                                <div
                                                    class="flex justify-between text-[9px] font-black uppercase tracking-widest text-slate-400">
                                                    <span>Quota Établissements</span>
                                                    <span
                                                        class="{{ $pct >= 90 ? 'text-rose-600' : 'text-slate-600' }}">{{ $count }}
                                                        / {{ $limit }}</span>
                                                </div>
                                                <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                                    <div class="h-full {{ $colorClass }} transition-all duration-1000"
                                                        style="width: {{ $pct }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-slate-300 italic text-sm">Aucun créateur</span>
                                    @endif
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex justify-center">
                                        <form action="{{ route('admin.boutiques.toggle-status', $boutique) }}"
                                            method="POST">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                class="flex items-center gap-2 px-4 py-1.5 rounded-full ring-1 transition-all {{ $boutique->is_active ? 'bg-emerald-50 text-emerald-600 ring-emerald-100' : 'bg-rose-50 text-rose-600 ring-rose-100 hover:bg-rose-100' }}">
                                                <span
                                                    class="w-1.5 h-1.5 rounded-full {{ $boutique->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                                                <span
                                                    class="text-[10px] font-black uppercase tracking-widest">{{ $boutique->is_active ? 'Actif' : 'Suspendu' }}</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.boutiques.show', $boutique) }}"
                                            class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-500 hover:bg-slate-900 hover:text-white flex items-center justify-center transition-all">
                                            <i class="bi bi-eye-fill"></i>
                                        </a>
                                        {{-- <button class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-500 hover:bg-indigo-600 hover:text-white flex items-center justify-center transition-all">
                                    <i class="bi bi-sliders"></i>
                                </button> --}}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Premium Creation Modal -->
    <div id="createModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-6 sm:p-10">
        <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-xl animate-in fade-in duration-300"
            onclick="this.parentElement.classList.add('hidden')"></div>

        <div
            class="relative w-full max-w-2xl glass-card rounded-[3rem] p-10 shadow-2xl animate-in zoom-in-95 slide-in-from-bottom-10 duration-500">
            <div class="flex items-center justify-between mb-10">
                <div class="space-y-1">
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight leading-none">Nouvel <span
                            class="text-primary-600 italic tracking-tighter">Établissement.</span></h2>
                    <p class="text-slate-500 font-medium tracking-tight">Configurez une nouvelle boutique opérationnelle.
                    </p>
                </div>
                <button onclick="document.getElementById('createModal').classList.add('hidden')"
                    class="w-12 h-12 rounded-3xl bg-slate-100 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-all">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>

            <form action="{{ route('admin.boutiques.store') }}" method="POST" class="space-y-8">
                @csrf

                <!-- Section Boutique -->
                <div class="space-y-4">
                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                        <i class="bi bi-building text-primary-500"></i> Informations Établissement
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nom -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-2">Nom de la
                                Boutique</label>
                            <div class="relative">
                                <i class="bi bi-shop absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="text" name="nom" required placeholder="ex: Pharmacie Moderne"
                                    class="w-full pl-12 pr-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl text-sm font-bold focus:bg-white focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all outline-none">
                            </div>
                        </div>

                        <!-- Nature -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-2">Nature de
                                l'Activité</label>
                            <div class="relative">
                                <i class="bi bi-tags absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <select name="nature_id" required
                                    class="w-full pl-12 pr-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl text-sm font-bold focus:bg-white focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all outline-none appearance-none">
                                    <option value="">Sélectionner une activité</option>
                                    @foreach (\App\Models\Nature::all() as $nature)
                                        <option value="{{ $nature->id }}">{{ $nature->name }}</option>
                                    @endforeach
                                </select>
                                <i
                                    class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Telephone -->
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-2">Téléphone</label>
                            <div class="relative">
                                <i class="bi bi-telephone absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="text" name="telephone" required placeholder="+223 ..."
                                    class="w-full pl-12 pr-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl text-sm font-bold focus:bg-white focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all outline-none">
                            </div>
                        </div>

                        <!-- Adresse -->
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-2">Localisation
                                Physique</label>
                            <div class="relative">
                                <i class="bi bi-geo-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="text" name="adresse" required placeholder="Adresse de l'établissement..."
                                    class="w-full pl-12 pr-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl text-sm font-bold focus:bg-white focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Propriétaire -->
                <div class="space-y-4 pt-6 border-t border-slate-100">
                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                        <i class="bi bi-person-badge text-indigo-500"></i> Compte Propriétaire
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nom Admin -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-2">Nom du
                                Gestionnaire</label>
                            <div class="relative">
                                <i class="bi bi-person absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="text" name="nom_admin" required placeholder="Nom complet"
                                    class="w-full pl-12 pr-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl text-sm font-bold focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none">
                            </div>
                        </div>

                        <!-- Email Admin -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-2">E-mail de
                                Connexion</label>
                            <div class="relative">
                                <i class="bi bi-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="email" name="email_admin" required placeholder="admin@boutique.com"
                                    class="w-full pl-12 pr-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl text-sm font-bold focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none">
                            </div>
                        </div>

                        <!-- Password Admin -->
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-2">Mot de
                                Passe</label>
                            <div class="relative">
                                <i class="bi bi-key absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="password" name="password_admin" required placeholder="••••••••"
                                    class="w-full pl-12 pr-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl text-sm font-bold focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-4 mt-8">
                    <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')"
                        class="px-8 py-4 rounded-2xl font-black text-slate-400 hover:text-slate-900 transition-colors">
                        Annuler
                    </button>
                    <button type="submit"
                        class="btn-action bg-primary-600 text-white shadow-xl shadow-primary-500/20 px-10 hover:bg-primary-700">
                        <i class="bi bi-plus-lg text-xl"></i>
                        Créer Boutique & Propriétaire
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
