@extends('layouts.admin')

@section('content')
<div class="space-y-10">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 px-4">
        <div class="space-y-1">
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">Gestion des <span class="text-primary-600 tracking-tighter italic">Licences.</span></h1>
            <p class="text-slate-500 font-medium tracking-tight">Générez des clés d'activation, gérez les abonnements et prolongez les accès aux caisses.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button onclick="document.getElementById('generateModal').classList.remove('hidden')"
                class="btn-action bg-primary-600 text-white shadow-xl shadow-primary-500/20 hover:bg-primary-700">
                <i class="bi bi-key-fill text-xl"></i>
                <span>Générer une Clé</span>
            </button>
            <button onclick="document.getElementById('prolongModal').classList.remove('hidden')"
                class="btn-action bg-emerald-600 text-white shadow-xl shadow-emerald-500/20 hover:bg-emerald-700">
                <i class="bi bi-clock-history text-xl"></i>
                <span>Prolongation Directe</span>
            </button>
        </div>
    </div>

    <!-- Bannière clé générée avec succès (avec bouton copier) -->
    @if(session('success_cle'))
    <div class="mx-4 p-6 bg-gradient-to-r from-primary-600 to-indigo-600 rounded-3xl text-white shadow-2xl relative overflow-hidden animate-in fade-in slide-in-from-top-4 duration-500">
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 rounded-full text-xs font-bold uppercase tracking-widest text-primary-100">
                    <i class="bi bi-check-circle-fill"></i> Clé Prête à Transmettre
                </div>
                <h3 class="text-2xl font-black">{{ session('success_cle')['message'] }}</h3>
                <p class="text-sm text-primary-100 font-medium">Boutique : <strong>{{ session('success_cle')['boutique'] }}</strong> &bull; Durée : <strong>{{ session('success_cle')['duree'] }}</strong></p>
                <div class="flex items-center gap-3 mt-3">
                    <div class="px-5 py-3 bg-white text-slate-900 rounded-2xl font-mono text-xl font-black tracking-widest shadow-inner select-all" id="cleGenereText">
                        {{ session('success_cle')['cle'] }}
                    </div>
                    <button onclick="copierCle('{{ session('success_cle')['cle'] }}')" id="btnCopier"
                        class="px-5 py-3 bg-white/20 hover:bg-white/30 text-white rounded-2xl font-bold flex items-center gap-2 transition-all cursor-pointer">
                        <i class="bi bi-clipboard"></i>
                        <span id="labelCopier">Copier la clé</span>
                    </button>
                </div>
            </div>
            <div class="text-xs text-primary-100 max-w-xs bg-white/10 p-4 rounded-2xl backdrop-blur-sm">
                <i class="bi bi-info-circle-fill mr-1"></i> Envoyez cette clé au gérant de la boutique. Il pourra la saisir sur l'écran d'activation de la caisse pour débloquer immédiatement son compte.
            </div>
        </div>
    </div>
    @endif

    @if(session('success'))
    <div class="mx-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-3">
        <i class="bi bi-check-circle-fill text-xl text-emerald-600"></i>
        <span class="font-bold text-sm">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 px-4">
        <div class="glass-card p-6 rounded-3xl flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                <i class="bi bi-key"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Clés</p>
                <p class="text-2xl font-black text-slate-900 leading-none">{{ $stats['total'] }}</p>
            </div>
        </div>
        <div class="glass-card p-6 rounded-3xl flex items-center gap-4 text-amber-600">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">En Attente (Non Utilisées)</p>
                <p class="text-2xl font-black text-slate-900 leading-none">{{ $stats['inutilisees'] }}</p>
            </div>
        </div>
        <div class="glass-card p-6 rounded-3xl flex items-center gap-4 text-emerald-600">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="bi bi-check-circle"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Licences Actives</p>
                <p class="text-2xl font-black text-slate-900 leading-none">{{ $stats['actives'] }}</p>
            </div>
        </div>
        <div class="glass-card p-6 rounded-3xl flex items-center gap-4 text-rose-600">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                <i class="bi bi-x-circle"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Expirées / Révoquées</p>
                <p class="text-2xl font-black text-slate-900 leading-none">{{ $stats['expirees'] }}</p>
            </div>
        </div>
    </div>

    <!-- Filters and Table -->
    <div class="glass-card rounded-[2.5rem] overflow-hidden mx-4">
        <div class="p-8 border-b border-slate-100 bg-white/50 backdrop-blur-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <h3 class="font-black text-slate-900 text-sm uppercase tracking-widest flex items-center gap-3">
                <i class="bi bi-shield-check text-primary-500 text-lg"></i>
                Registre des Clés & Abonnements
            </h3>
            <form method="GET" action="{{ route('admin.licences.index') }}" class="flex flex-wrap items-center gap-3">
                <select name="boutique_id" onchange="this.form.submit()" class="bg-slate-100 border-none rounded-xl text-xs font-bold py-2.5 px-3 focus:ring-2 focus:ring-primary-500">
                    <option value="">Toutes les Boutiques</option>
                    @foreach($boutiques as $b)
                        <option value="{{ $b->id }}" {{ request('boutique_id') == $b->id ? 'selected' : '' }}>{{ $b->nom }}</option>
                    @endforeach
                </select>

                <select name="statut" onchange="this.form.submit()" class="bg-slate-100 border-none rounded-xl text-xs font-bold py-2.5 px-3 focus:ring-2 focus:ring-primary-500">
                    <option value="">Tous les statuts</option>
                    <option value="inutilisee" {{ request('statut') == 'inutilisee' ? 'selected' : '' }}>Inutilisées</option>
                    <option value="active" {{ request('statut') == 'active' ? 'selected' : '' }}>Actives</option>
                    <option value="expiree" {{ request('statut') == 'expiree' ? 'selected' : '' }}>Expirées</option>
                    <option value="revoquee" {{ request('statut') == 'revoquee' ? 'selected' : '' }}>Révoquées</option>
                </select>

                @if(request()->hasAny(['boutique_id', 'statut']))
                    <a href="{{ route('admin.licences.index') }}" class="text-xs text-rose-500 font-bold hover:underline">Réinitialiser</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <th class="px-8 py-5">Clé de Licence</th>
                        <th class="px-8 py-5">Boutique</th>
                        <th class="px-8 py-5">Durée</th>
                        <th class="px-8 py-5">Statut</th>
                        <th class="px-8 py-5">Dates Clés</th>
                        <th class="px-8 py-5">Générée par</th>
                        <th class="px-8 py-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm font-medium text-slate-600">
                    @forelse($licences as $licence)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-3">
                                <span class="font-mono font-black text-slate-900 text-sm tracking-wider bg-slate-100 px-3 py-1.5 rounded-xl border border-slate-200 select-all">
                                    {{ $licence->cle_licence }}
                                </span>
                                <button onclick="copierCle('{{ $licence->cle_licence }}')" title="Copier" class="text-slate-400 hover:text-primary-600 transition-colors">
                                    <i class="bi bi-clipboard text-sm"></i>
                                </button>
                            </div>
                            @if($licence->note)
                                <p class="text-[11px] text-slate-400 mt-1 italic">{{ $licence->note }}</p>
                            @endif
                        </td>
                        <td class="px-8 py-6">
                            <a href="{{ route('admin.boutiques.show', $licence->boutique_id) }}" class="font-bold text-slate-900 hover:text-primary-600 transition-colors">
                                {{ $licence->boutique->nom ?? 'Inconnue' }}
                            </a>
                            <div class="text-[10px] text-slate-400 font-semibold">
                                @if($licence->boutique && $licence->boutique->date_expiration_licence)
                                    Exp. Actuelle : {{ \Carbon\Carbon::parse($licence->boutique->date_expiration_licence)->format('d/m/Y') }}
                                @else
                                    Exp. Actuelle : Accès illimité
                                @endif
                            </div>
                        </td>
                        <td class="px-8 py-6">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $licence->duree_jours >= 90000 ? 'bg-purple-50 text-purple-700' : 'bg-blue-50 text-blue-700' }}">
                                {{ $licence->duree_jours >= 90000 ? 'À vie (Illimité)' : "{$licence->duree_jours} jours" }}
                            </span>
                        </td>
                        <td class="px-8 py-6">
                            @if($licence->statut === 'inutilisee')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                    En Attente
                                </span>
                            @elseif($licence->statut === 'active')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Active
                                </span>
                            @elseif($licence->statut === 'expiree')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                                    Expirée
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 ring-1 ring-rose-200">
                                    Révoquée
                                </span>
                            @endif
                        </td>
                        <td class="px-8 py-6 text-xs text-slate-500 space-y-0.5">
                            <div>Créée le : <strong>{{ $licence->created_at->format('d/m/Y H:i') }}</strong></div>
                            @if($licence->date_activation)
                                <div>Activée le : <strong class="text-emerald-600">{{ $licence->date_activation->format('d/m/Y') }}</strong></div>
                            @endif
                            @if($licence->date_expiration)
                                <div>Expire le : <strong class="{{ $licence->isExpired() ? 'text-rose-600' : 'text-slate-800' }}">{{ $licence->date_expiration->format('d/m/Y') }}</strong></div>
                            @endif
                        </td>
                        <td class="px-8 py-6 text-xs text-slate-500">
                            {{ $licence->creator->name ?? 'Système' }}
                        </td>
                        <td class="px-8 py-6 text-right">
                            @if($licence->statut === 'inutilisee')
                                <form action="{{ route('admin.licences.revoquer', $licence) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir révoquer cette clé ? Elle ne pourra plus être utilisée.')">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-xl text-xs font-bold transition-all">
                                        Révoquer
                                    </button>
                                </form>
                            @else
                                <span class="text-slate-300 text-xs">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-8 py-16 text-center text-slate-400 font-medium">
                            <i class="bi bi-key text-4xl block mb-2 opacity-40"></i>
                            Aucune clé de licence générée pour le moment.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($licences->hasPages())
        <div class="p-6 border-t border-slate-100">
            {{ $licences->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal 1: Générer une Clé de Licence -->
<div id="generateModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-6 sm:p-10">
    <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-xl animate-in fade-in duration-300"
        onclick="this.parentElement.classList.add('hidden')"></div>

    <div class="relative w-full max-w-lg glass-card rounded-[3rem] p-10 shadow-2xl animate-in zoom-in-95 slide-in-from-bottom-10 duration-500">
        <div class="flex items-center justify-between mb-8">
            <div class="space-y-1">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-none">Générer une <span class="text-primary-600 italic">Clé d'Activation.</span></h2>
                <p class="text-slate-500 font-medium text-xs">Créez un code unique que le client saisira sur sa caisse.</p>
            </div>
            <button onclick="document.getElementById('generateModal').classList.add('hidden')"
                class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-400 hover:text-slate-900 flex items-center justify-center transition-all">
                <i class="bi bi-x-lg text-lg"></i>
            </button>
        </div>

        <form action="{{ route('admin.licences.store') }}" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Boutique Destinataire *</label>
                <select name="boutique_id" required class="w-full px-4 py-3 bg-slate-100 border-none rounded-2xl text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:bg-white transition-all">
                    <option value="">Sélectionnez la boutique...</option>
                    @foreach($boutiques as $b)
                        <option value="{{ $b->id }}">{{ $b->nom }} ({{ $b->adresse }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Durée de Validité *</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="p-3 border-2 border-slate-200 rounded-2xl cursor-pointer hover:border-primary-500 transition-all text-center flex flex-col items-center">
                        <input type="radio" name="duree_jours" value="30" checked class="text-primary-600 focus:ring-primary-500 mb-1">
                        <span class="text-sm font-black text-slate-900">1 Mois</span>
                        <span class="text-[10px] text-slate-400 font-bold">30 jours</span>
                    </label>
                    <label class="p-3 border-2 border-slate-200 rounded-2xl cursor-pointer hover:border-primary-500 transition-all text-center flex flex-col items-center">
                        <input type="radio" name="duree_jours" value="90" class="text-primary-600 focus:ring-primary-500 mb-1">
                        <span class="text-sm font-black text-slate-900">3 Mois</span>
                        <span class="text-[10px] text-slate-400 font-bold">90 jours</span>
                    </label>
                    <label class="p-3 border-2 border-slate-200 rounded-2xl cursor-pointer hover:border-primary-500 transition-all text-center flex flex-col items-center">
                        <input type="radio" name="duree_jours" value="365" class="text-primary-600 focus:ring-primary-500 mb-1">
                        <span class="text-sm font-black text-slate-900">1 An</span>
                        <span class="text-[10px] text-slate-400 font-bold">365 jours</span>
                    </label>
                    <label class="p-3 border-2 border-slate-200 rounded-2xl cursor-pointer hover:border-primary-500 transition-all text-center flex flex-col items-center">
                        <input type="radio" name="duree_jours" value="99999" class="text-primary-600 focus:ring-primary-500 mb-1">
                        <span class="text-sm font-black text-purple-700">À Vie</span>
                        <span class="text-[10px] text-purple-400 font-bold">Permanent</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Note / Référence (Facultatif)</label>
                <input type="text" name="note" placeholder="Ex: Paiement 60 000 GNF par Orange Money"
                    class="w-full px-4 py-3 bg-slate-100 border-none rounded-2xl text-sm font-semibold focus:ring-4 focus:ring-primary-500/10 focus:bg-white transition-all">
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
                <button type="button" onclick="document.getElementById('generateModal').classList.add('hidden')"
                    class="px-6 py-3 rounded-2xl font-bold text-slate-500 hover:bg-slate-100 transition-all">
                    Annuler
                </button>
                <button type="submit"
                    class="btn-action bg-primary-600 text-white shadow-xl shadow-primary-500/20 hover:bg-primary-700">
                    <i class="bi bi-magic"></i>
                    <span>Générer le Code</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Prolongation Directe Sans Clé -->
<div id="prolongModal" class="hidden fixed inset-0 z-[100] flex items-center justify-center p-6 sm:p-10">
    <div class="absolute inset-0 bg-slate-950/40 backdrop-blur-xl animate-in fade-in duration-300"
        onclick="this.parentElement.classList.add('hidden')"></div>

    <div class="relative w-full max-w-lg glass-card rounded-[3rem] p-10 shadow-2xl animate-in zoom-in-95 slide-in-from-bottom-10 duration-500">
        <div class="flex items-center justify-between mb-8">
            <div class="space-y-1">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-none">Prolongation <span class="text-emerald-600 italic">Directe.</span></h2>
                <p class="text-slate-500 font-medium text-xs">Débloquez ou prolongez immédiatement une boutique sans saisie de clé.</p>
            </div>
            <button onclick="document.getElementById('prolongModal').classList.add('hidden')"
                class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-400 hover:text-slate-900 flex items-center justify-center transition-all">
                <i class="bi bi-x-lg text-lg"></i>
            </button>
        </div>

        <form id="prolongDirectForm" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Boutique à Prolonger *</label>
                <select id="selectBoutiqueProlong" required onchange="updateProlongFormAction(this.value)"
                    class="w-full px-4 py-3 bg-slate-100 border-none rounded-2xl text-sm font-semibold focus:ring-4 focus:ring-emerald-500/10 focus:bg-white transition-all">
                    <option value="">Sélectionnez la boutique...</option>
                    @foreach($boutiques as $b)
                        <option value="{{ $b->id }}">{{ $b->nom }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Durée d'Extension *</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="p-3 border-2 border-slate-200 rounded-2xl cursor-pointer hover:border-emerald-500 transition-all text-center flex flex-col items-center">
                        <input type="radio" name="duree_jours" value="30" checked class="text-emerald-600 focus:ring-emerald-500 mb-1">
                        <span class="text-sm font-black text-slate-900">+1 Mois</span>
                        <span class="text-[10px] text-slate-400 font-bold">30 jours</span>
                    </label>
                    <label class="p-3 border-2 border-slate-200 rounded-2xl cursor-pointer hover:border-emerald-500 transition-all text-center flex flex-col items-center">
                        <input type="radio" name="duree_jours" value="90" class="text-emerald-600 focus:ring-emerald-500 mb-1">
                        <span class="text-sm font-black text-slate-900">+3 Mois</span>
                        <span class="text-[10px] text-slate-400 font-bold">90 jours</span>
                    </label>
                    <label class="p-3 border-2 border-slate-200 rounded-2xl cursor-pointer hover:border-emerald-500 transition-all text-center flex flex-col items-center">
                        <input type="radio" name="duree_jours" value="365" class="text-emerald-600 focus:ring-emerald-500 mb-1">
                        <span class="text-sm font-black text-slate-900">+1 An</span>
                        <span class="text-[10px] text-slate-400 font-bold">365 jours</span>
                    </label>
                    <label class="p-3 border-2 border-slate-200 rounded-2xl cursor-pointer hover:border-emerald-500 transition-all text-center flex flex-col items-center">
                        <input type="radio" name="duree_jours" value="99999" class="text-emerald-600 focus:ring-emerald-500 mb-1">
                        <span class="text-sm font-black text-purple-700">À Vie</span>
                        <span class="text-[10px] text-purple-400 font-bold">Accès permanent</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Note explicative (Facultatif)</label>
                <input type="text" name="note" placeholder="Ex: Paiement reçu directement"
                    class="w-full px-4 py-3 bg-slate-100 border-none rounded-2xl text-sm font-semibold focus:ring-4 focus:ring-emerald-500/10 focus:bg-white transition-all">
            </div>

            <div class="pt-4 flex items-center justify-end gap-3">
                <button type="button" onclick="document.getElementById('prolongModal').classList.add('hidden')"
                    class="px-6 py-3 rounded-2xl font-bold text-slate-500 hover:bg-slate-100 transition-all">
                    Annuler
                </button>
                <button type="submit"
                    class="btn-action bg-emerald-600 text-white shadow-xl shadow-emerald-500/20 hover:bg-emerald-700">
                    <i class="bi bi-check2-circle"></i>
                    <span>Appliquer la Prolongation</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function copierCle(cle) {
    navigator.clipboard.writeText(cle).then(() => {
        const label = document.getElementById('labelCopier');
        if (label) {
            label.innerText = 'Copié !';
            setTimeout(() => { label.innerText = 'Copier la clé'; }, 2500);
        }
    }).catch(() => {
        // Fallback
        const textarea = document.createElement('textarea');
        textarea.value = cle;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        const label = document.getElementById('labelCopier');
        if (label) {
            label.innerText = 'Copié !';
            setTimeout(() => { label.innerText = 'Copier la clé'; }, 2500);
        }
    });
}

function updateProlongFormAction(boutiqueId) {
    const form = document.getElementById('prolongDirectForm');
    if (boutiqueId) {
        form.action = `/admin/boutiques/${boutiqueId}/prolonger-licence`;
    }
}
</script>
@endsection
