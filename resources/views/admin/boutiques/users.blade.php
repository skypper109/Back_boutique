@extends('layouts.admin')

@section('content')
    <div class="space-y-10">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 px-4">
            <div class="flex items-center gap-6">
                <a href="{{ route('admin.boutiques.index') }}"
                    class="w-12 h-12 rounded-2xl bg-white border border-slate-200 text-slate-400 hover:bg-slate-900 hover:text-white hover:border-slate-900 flex items-center justify-center transition-all shadow-sm">
                    <i class="bi bi-arrow-left text-xl"></i>
                </a>
                <div class="space-y-1">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 bg-primary-50 text-primary-700 rounded-full text-[10px] font-black uppercase tracking-widest mb-1">
                        <i class="bi bi-shop-window"></i>
                        {{ $boutique->nom }}
                    </div>
                    <h1 class="text-4xl font-black text-slate-900 tracking-tight">Ressources <span
                            class="text-primary-600 tracking-tighter italic">Humaines.</span></h1>
                    <p class="text-slate-500 font-medium tracking-tight">Gestion des accès, des attributions et de la
                        sécurité du personnel.</p>
                </div>
            </div>
            <div class="flex gap-4">
                <button onclick="document.getElementById('createUserModal').classList.remove('hidden')"
                    class="btn-action bg-primary-600 text-white shadow-xl shadow-primary-500/20 hover:bg-primary-700">
                    <i class="bi bi-person-plus-fill text-xl"></i>
                    <span>Nouveau Compte</span>
                </button>
            </div>
        </div>

        <!-- Main Table Card -->
        <div class="glass-card rounded-[2.5rem] overflow-hidden">
            <div
                class="p-8 border-b border-slate-100 bg-white/50 backdrop-blur-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h3 class="font-black text-slate-900 text-sm uppercase tracking-widest flex items-center gap-3">
                    <i class="bi bi-people-fill text-primary-500 text-lg"></i>
                    Registre du Personnel
                </h3>
                <div class="relative group">
                    <i
                        class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary-500 transition-colors"></i>
                    <input type="text" placeholder="Rechercher un membre..."
                        class="pl-12 pr-6 py-3 bg-slate-100 border-none rounded-2xl text-sm font-semibold w-full md:w-80 focus:ring-4 focus:ring-primary-500/10 focus:bg-white transition-all">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-1">
                    <thead class="bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">
                        <tr>
                            <th class="px-8 py-6">Intervenant</th>
                            <th class="px-8 py-6 text-center">Rôle Attribué</th>
                            <th class="px-8 py-6 text-center">Status</th>
                            <th class="px-8 py-6 text-right">Sécurité & Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($users as $user)
                            <tr
                                class="group transition-all hover:bg-slate-50/80 {{ !$user->is_active ? 'opacity-70' : '' }}">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-5">
                                        <div class="relative">
                                            <div
                                                class="w-12 h-12 rounded-2xl {{ $user->role == 'admin' ? 'bg-primary-50 text-primary-600' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center font-black text-lg group-hover:scale-110 transition-transform duration-300">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div
                                                class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full border-2 border-white {{ $user->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}">
                                            </div>
                                        </div>
                                        <div class="space-y-0.5">
                                            <p
                                                class="font-bold text-slate-900 leading-tight group-hover:text-primary-600 transition-colors">
                                                {{ $user->name }}</p>
                                            <p class="text-[10px] font-medium text-slate-500">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex justify-center">
                                        @php
                                            $roleColors = [
                                                'admin' => 'bg-primary-50 text-primary-700 ring-primary-200',
                                                'gestionnaire' => 'bg-amber-50 text-amber-700 ring-amber-200',
                                                'comptable' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                                'vendeur' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
                                            ];
                                            $roleColor =
                                                $roleColors[$user->role] ??
                                                'bg-slate-100 text-slate-600 ring-slate-200';
                                        @endphp
                                        <span
                                            class="px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest ring-1 {{ $roleColor }}">
                                            {{ $user->role }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex justify-center">
                                        @if ($user->is_active)
                                            <span
                                                class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase rounded-lg">
                                                <i class="bi bi-shield-check"></i> Actif
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-2 px-3 py-1 bg-slate-100 text-slate-500 text-[10px] font-black uppercase rounded-lg">
                                                <i class="bi bi-shield-slash"></i> Restreint
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Reset Password Button -->
                                        <button
                                            onclick="openPasswordModal('{{ $user->id }}', '{{ addslashes($user->name) }}')"
                                            class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white flex items-center justify-center transition-all"
                                            title="Réinitialiser MDP">
                                            <i class="bi bi-key-fill text-lg"></i>
                                        </button>

                                        <!-- Toggle Status Button -->
                                        <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST"
                                            class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="w-10 h-10 rounded-2xl flex items-center justify-center transition-all {{ $user->is_active ? 'bg-slate-100 text-slate-500 hover:bg-slate-900 hover:text-white' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white' }}"
                                                title="{{ $user->is_active ? 'Désactiver' : 'Activer' }}">
                                                <i
                                                    class="bi {{ $user->is_active ? 'bi-lock-fill' : 'bi-unlock-fill' }} text-lg"></i>
                                            </button>
                                        </form>

                                        <!-- Delete Button -->
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                            class="inline"
                                            onsubmit="return confirm('ALERTE : Êtes-vous sûr de vouloir supprimer définitivement le compte de {{ addslashes($user->name) }} ? Cette action est irréversible.');">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white flex items-center justify-center transition-all"
                                                title="Supprimer">
                                                <i class="bi bi-trash-fill text-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="p-6 border-t border-slate-100 bg-slate-50/50">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal: Nouveau Personnel -->
    <div id="createUserModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-6 sm:p-10">
        <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-xl animate-in fade-in duration-300"
            onclick="this.parentElement.classList.add('hidden')"></div>

        <div
            class="relative w-full max-w-xl glass-card rounded-[3rem] p-10 shadow-2xl animate-in zoom-in-95 slide-in-from-bottom-10 duration-500">
            <div class="flex items-center justify-between mb-10">
                <div class="space-y-1">
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight leading-none">Nouveau <span
                            class="text-primary-600 italic tracking-tighter">Collaborateur.</span></h2>
                    <p class="text-slate-500 font-medium tracking-tight">Créez un profil pour l'établissement
                        {{ $boutique->nom }}.</p>
                </div>
                <button onclick="document.getElementById('createUserModal').classList.add('hidden')"
                    class="w-12 h-12 rounded-3xl bg-slate-100 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-all">
                    <i class="bi bi-x-lg text-xl"></i>
                </button>
            </div>

            <form action="{{ route('admin.boutiques.users.store', $boutique->id) }}" method="POST" class="space-y-6">
                @csrf

                <!-- Nom -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-2">Nom Complet</label>
                    <div class="relative">
                        <i class="bi bi-person absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="name" required placeholder="Ex: Jean Dupont"
                            class="w-full pl-12 pr-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl text-sm font-bold focus:bg-white focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Email -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-2">Email de
                            connexion</label>
                        <div class="relative">
                            <i class="bi bi-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="email" name="email" required placeholder="user@maboutique.com"
                                class="w-full pl-12 pr-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl text-sm font-bold focus:bg-white focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all outline-none">
                        </div>
                    </div>

                    <!-- Mot de passe -->
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-2">Mot de passe
                            temporaire</label>
                        <div class="relative">
                            <i class="bi bi-key absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="password" name="password" required placeholder="••••••••"
                                class="w-full pl-12 pr-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl text-sm font-bold focus:bg-white focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all outline-none">
                        </div>
                    </div>
                </div>

                <!-- Rôle -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-2">Niveau d'Accès
                        (Rôle)</label>
                    <div class="relative">
                        <i class="bi bi-shield-check absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <select name="role" required
                            class="w-full pl-12 pr-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl text-sm font-bold focus:bg-white focus:border-primary-500 focus:ring-4 focus:ring-primary-500/10 transition-all outline-none appearance-none">
                            <option value="vendeur">Vendeur (Front-office & Caisse)</option>
                            <option value="gestionnaire">Gestionnaire (Back-office & Stocks)</option>
                            <option value="comptable">Comptable (Finances & Rapports)</option>
                            <option value="admin">Administrateur Boutique (Contrôle total local)</option>
                        </select>
                        <i
                            class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-4 mt-8">
                    <button type="button" onclick="document.getElementById('createUserModal').classList.add('hidden')"
                        class="px-8 py-4 rounded-2xl font-black text-slate-400 hover:text-slate-900 transition-colors">
                        Annuler
                    </button>
                    <button type="submit"
                        class="btn-action bg-primary-600 text-white shadow-xl shadow-primary-500/20 px-8">
                        <i class="bi bi-check-lg text-xl"></i>
                        Créer le Profil
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Réinitialisation MDP -->
    <div id="passwordModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-6 sm:p-10">
        <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-xl animate-in fade-in duration-300"
            onclick="this.parentElement.classList.add('hidden')"></div>

        <div
            class="relative w-full max-w-lg glass-card rounded-[3rem] p-10 shadow-2xl animate-in zoom-in-95 slide-in-from-bottom-10 duration-500">
            <div class="flex items-center justify-between mb-8">
                <div
                    class="w-14 h-14 rounded-3xl bg-amber-50 text-amber-500 flex items-center justify-center text-2xl shadow-inner">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <button onclick="document.getElementById('passwordModal').classList.add('hidden')"
                    class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-400 hover:bg-slate-200 transition-all">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="mb-8 space-y-2">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Réinitialisation <span
                        class="text-amber-500">Sécurité.</span></h2>
                <p id="modalSubtitle" class="text-slate-500 font-medium text-sm">Nouveau mot de passe pour le compte
                    sélectionné.</p>
            </div>

            <form id="passwordForm" method="POST" class="space-y-6">
                @csrf
                <div class="space-y-5">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-2">Nouveau mot de
                            passe</label>
                        <div class="relative">
                            <i class="bi bi-key absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="password" name="password" required placeholder="••••••••"
                                class="w-full pl-12 pr-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl text-sm font-bold focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all outline-none">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest pl-2">Confirmation</label>
                        <div class="relative">
                            <i class="bi bi-check-all absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="password" name="password_confirmation" required placeholder="••••••••"
                                class="w-full pl-12 pr-6 py-4 bg-slate-50 border-2 border-transparent rounded-2xl text-sm font-bold focus:bg-white focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 transition-all outline-none">
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3 mt-8">
                    <button type="button" onclick="document.getElementById('passwordModal').classList.add('hidden')"
                        class="px-6 py-3 rounded-2xl font-bold text-slate-400 hover:text-slate-900 transition-colors">
                        Fermer
                    </button>
                    <button type="submit"
                        class="btn-action bg-slate-900 text-white shadow-xl shadow-slate-900/20 px-8 hover:bg-slate-800">
                        Verrouiller
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPasswordModal(userId, userName) {
            const modal = document.getElementById('passwordModal');
            const form = document.getElementById('passwordForm');
            const subtitle = document.getElementById('modalSubtitle');

            form.action = `/admin/users/${userId}/update-password`;
            subtitle.innerHTML = `Nouveau mot de passe pour <strong>${userName}</strong>.`;
            modal.classList.remove('hidden');
        }
    </script>
@endsection
