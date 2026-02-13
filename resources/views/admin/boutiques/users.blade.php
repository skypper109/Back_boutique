@extends('layouts.admin')

@section('content')
    <div class="dashboard-header">
        <div style="display: flex; align-items: center; gap: 24px;">
            <a href="{{ route('admin.boutiques.index') }}" class="action-btn" title="Retour">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="page-title">Personnel — {{ $boutique->nom }}</h1>
                <p class="text-muted">Gestion des accès, des rôles et de la sécurité des agents.</p>
            </div>
        </div>
        <div class="header-actions">
            <button onclick="document.getElementById('createUserModal').style.display='flex'" class="btn btn-primary">
                <i class="bi bi-person-plus-fill"></i>
                <span>Nouveau Compte</span>
            </button>
        </div>
    </div>

    <x-admin-card title="Membres de l'équipe" icon="bi bi-people-fill">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Email</th>
                        <th style="text-align: center;">Rôle Système</th>
                        <th style="text-align: center;">Statut</th>
                        <th style="text-align: right;">Sécurité & Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="{{ !$user->is_active ? 'deactivated-row' : '' }}">
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div class="user-avatar">
                                        <i class="bi bi-person-circle"></i>
                                    </div>
                                    <div style="font-weight: 700;">{{ $user->name }}</div>
                                </div>
                            </td>
                            <td><span class="text-muted">{{ $user->email }}</span></td>
                            <td style="text-align: center;">
                                <span class="badge {{ $user->role == 'admin' ? 'badge-blue' : 'badge-light' }}">
                                    {{ strtoupper($user->role) }}
                                </span>
                            </td>
                            <td style="text-align: center;">
                                @if ($user->is_active)
                                    <span class="badge badge-success">Actif</span>
                                @else
                                    <span class="badge badge-danger">Bloqué</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <div class="action-buttons">
                                    <button onclick="openPasswordModal('{{ $user->id }}', '{{ $user->name }}')"
                                        class="action-btn" title="Réinitialiser MDP">
                                        <i class="bi bi-shield-lock-fill text-accent"></i>
                                    </button>

                                    <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST"
                                        style="display: inline;">
                                        @csrf
                                        <button type="submit" class="action-btn"
                                            title="{{ $user->is_active ? 'Désactiver' : 'Activer' }}">
                                            <i
                                                class="bi {{ $user->is_active ? 'bi-person-dash text-danger' : 'bi-person-check text-success' }}"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST"
                                        style="display: inline;"
                                        onsubmit="return confirm('ALERTE: Suppression définitive du compte. Continuer ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn" title="Supprimer">
                                            <i class="bi bi-person-x-fill text-danger"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top: 24px;">
            {{ $users->links() }}
        </div>
    </x-admin-card>

    <!-- Modal: Nouveau Personnel -->
    <div id="createUserModal" class="modal">
        <div class="modal-content">
            <div class="card-title">
                <span>AJOUTER UN COLLABORATEUR</span>
                <button onclick="document.getElementById('createUserModal').style.display='none'" class="text-muted"
                    style="background: none; border: none; cursor: pointer;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form action="{{ route('admin.boutiques.users.store', $boutique->id) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Nom Complet</label>
                    <input type="text" name="name" required class="form-control" placeholder="Ex: Jean Dupont">
                </div>

                <div class="form-group">
                    <label class="form-label">Email de connexion</label>
                    <input type="email" name="email" required class="form-control" placeholder="user@maboutique.com">
                </div>

                <div class="form-group">
                    <label class="form-label">Mot de passe initial</label>
                    <input type="password" name="password" required class="form-control" placeholder="••••••••">
                </div>

                <div class="form-group">
                    <label class="form-label">Rôle Attribué</label>
                    <select name="role" required class="form-control">
                        <option value="vendeur">Vendeur (Caisse)</option>
                        <option value="gestionnaire">Gestionnaire (Stock)</option>
                        <option value="comptable">Comptable (Finance)</option>
                        <option value="admin">Administrateur Boutique</option>
                    </select>
                </div>

                <div class="grid-2" style="margin-top: 32px;">
                    <button type="button" onclick="document.getElementById('createUserModal').style.display='none'"
                        class="btn" style="background: #f1f5f9; color: var(--text-main);">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Créer le Compte
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Réinitialisation MDP -->
    <div id="passwordModal" class="modal">
        <div class="modal-content" style="border-top: 5px solid var(--accent);">
            <div class="card-title">
                <span id="modalTitle">Réinitialisation Sécurité</span>
                <button onclick="document.getElementById('passwordModal').style.display='none'" class="text-muted"
                    style="background: none; border: none; cursor: pointer;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form id="passwordForm" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Nouveau mot de passe</label>
                    <input type="password" name="password" required class="form-control" placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirmation</label>
                    <input type="password" name="password_confirmation" required class="form-control"
                        placeholder="••••••••">
                </div>

                <div class="grid-2" style="margin-top: 32px;">
                    <button type="button" onclick="document.getElementById('passwordModal').style.display='none'"
                        class="btn" style="background: #f1f5f9; color: var(--text-main);">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary" style="background: var(--accent);">
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPasswordModal(userId, userName) {
            const modal = document.getElementById('passwordModal');
            const form = document.getElementById('passwordForm');
            const title = document.getElementById('modalTitle');

            form.action = `/admin/users/${userId}/update-password`;
            title.innerHTML = `<i class="bi bi-shield-lock"></i> Compte : ${userName}`;
            modal.style.display = 'flex';
        }
    </script>

    @media (max-width: 768px) {
    .dashboard-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
    }
    .header-actions {
    width: 100%;
    }
    .header-actions .btn {
    width: 100%;
    justify-content: center;
    }
    .table-wrapper {
    overflow-x: auto;
    }
    .modal-content {
    width: 95%;
    padding: 24px;
    }
    }
    </style>
@endsection
