@extends('layouts.admin')

@section('content')
    <div class="space-y-10">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 px-4">
            <div class="space-y-1">
                <h1 class="text-4xl font-black text-slate-900 tracking-tight">Administrateurs <span
                        class="text-primary-600 tracking-tighter italic">Système.</span></h1>
                <p class="text-slate-500 font-medium tracking-tight">Gestion des accès de haut niveau pour l'ensemble du
                    réseau Ma Boutique.</p>
            </div>
            <div class="flex gap-4">
                <div class="glass-card px-6 py-3 rounded-2xl flex items-center gap-4">
                    <div
                        class="w-10 h-10 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center text-lg">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Effectif Admin</p>
                        <p class="text-xl font-black text-slate-900 leading-none">{{ $admins->total() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table Card -->
        <div class="glass-card rounded-[2.5rem] overflow-hidden">
            <div
                class="p-8 border-b border-slate-100 bg-white/50 backdrop-blur-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h3 class="font-black text-slate-900 text-sm uppercase tracking-widest flex items-center gap-3">
                    <i class="bi bi-person-badge text-primary-500 text-lg"></i>
                    Comptes Administrateurs
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-1">
                    <thead class="bg-slate-50/50 text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">
                        <tr>
                            <th class="px-8 py-6">Administrateur</th>
                            <th class="px-8 py-6 text-center">Privilèges</th>
                            <th class="px-8 py-6 text-center">Limite Boutiques</th>
                            <th class="px-8 py-6 text-center">Inscription</th>
                            <th class="px-8 py-6 text-right">Sécurité</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($admins as $admin)
                            @if ($admin->role == 'admin' || $admin->role == 'super_admin')
                                <tr class="group transition-all hover:bg-slate-50/80">
                                    <td class="px-8 py-6">
                                        <div class="flex items-center gap-5">
                                            <div
                                                class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-black text-lg group-hover:scale-110 transition-transform duration-500">
                                                {{ substr($admin->name, 0, 1) }}
                                            </div>
                                            <div class="space-y-0.5">
                                                <p
                                                    class="font-black text-slate-900 tracking-tight leading-tight group-hover:text-primary-600 transition-colors">
                                                    {{ $admin->name }}</p>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[11px] font-medium text-slate-400">
                                                        <i class="bi bi-envelope"></i> {{ $admin->email }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-8 py-6 text-center">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest {{ $admin->role === 'super_admin' ? 'bg-amber-50 text-amber-600 ring-1 ring-amber-100' : 'bg-primary-50 text-primary-600 ring-1 ring-primary-100' }}">
                                            <i
                                                class="{{ $admin->role === 'super_admin' ? 'bi bi-star-fill' : 'bi bi-shield-check' }}"></i>
                                            {{ $admin->role }}
                                        </span>
                                    </td>

                                    <td class="px-8 py-6">
                                        <form action="{{ route('admin.admins.update-limit', $admin->id) }}" method="POST"
                                            class="flex items-center justify-center gap-2">
                                            @csrf
                                            <input type="number" name="boutique_limit" value="{{ $admin->boutique_limit }}"
                                                min="1"
                                                class="w-16 px-2 py-1.5 bg-white border border-slate-200 rounded-xl text-sm font-black text-slate-900 text-center focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 outline-none transition-all">
                                            <button type="submit"
                                                class="w-8 h-8 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-emerald-500 hover:border-emerald-200 hover:bg-emerald-50 flex items-center justify-center transition-all shadow-sm"
                                                title="Mettre à jour la limite">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                    </td>

                                    <td class="px-8 py-6 text-center">
                                        <span
                                            class="text-xs font-bold text-slate-500">{{ $admin->created_at->format('d/m/Y') }}</span>
                                    </td>

                                    <td class="px-8 py-6 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @if ($admin->id !== Auth::id())
                                                <form action="{{ route('admin.admins.destroy', $admin->id) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('ALERTE: Suppression irréversible du compte administrateur. Confirmer ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="w-10 h-10 rounded-2xl bg-white border border-rose-100 text-rose-400 hover:bg-rose-50 hover:text-rose-600 flex items-center justify-center transition-all shadow-sm"
                                                        title="Supprimer le compte">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <span
                                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest ring-1 ring-emerald-100">
                                                    <i class="bi bi-person-check-fill"></i> MOI
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($admins->hasPages())
                <div class="p-6 border-t border-slate-100 bg-slate-50/50">
                    {{ $admins->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
