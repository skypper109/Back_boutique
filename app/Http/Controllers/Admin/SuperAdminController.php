<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Boutique;
use App\Models\User;
use App\Models\Vente;
use App\Models\DetailVente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminController extends Controller
{
    public function toggleUserStatus(User $user)
    {
        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'activé' : 'désactivé';
        return back()->with('success', "Le compte de {$user->name} a été {$status} avec succès.");
    }

    public function dashboard()
    {
        $boutiquesCount = Boutique::count();
        $usersCount = User::count();
        $totalSystemRevenue = Vente::where('statut', 'validee')->sum('montant_total');

        $boutiquePerformance = Boutique::withSum(['ventes as revenue' => function ($query) {
            $query->where('statut', 'validee');
        }], 'montant_total')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('boutiquesCount', 'usersCount', 'totalSystemRevenue', 'boutiquePerformance'));
    }

    public function boutiquesIndex()
    {
        $boutiques = Boutique::all();
        return view('admin.boutiques.index', compact('boutiques'));
    }

    public function boutiqueShow(Boutique $boutique)
    {
        $salesCount = Vente::where('boutique_id', $boutique->id)->where('statut', 'validee')->count();
        $totalRevenue = Vente::where('boutique_id', $boutique->id)->where('statut', 'validee')->sum('montant_total');

        $topProducts = DetailVente::with('produit')
            ->whereHas('vente', function ($q) use ($boutique) {
                $q->where('boutique_id', $boutique->id)->where('statut', 'validee');
            })
            ->selectRaw('produit_id, SUM(quantite) as total_qty, SUM(montant_total) as total_amount')
            ->groupBy('produit_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $recentSales = Vente::with('user')
            ->where('boutique_id', $boutique->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.boutiques.show', compact('boutique', 'salesCount', 'totalRevenue', 'topProducts', 'recentSales'));
    }

    public function boutiqueStore(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'email_admin' => 'required|email|unique:users,email',
            'password_admin' => 'required|string|min:6',
        ]);

        DB::transaction(function () use ($request) {

            $user = User::create([
                'name' => 'Admin ' . $request->nom,
                'email' => $request->email_admin,
                'password' => Hash::make($request->password_admin),
                'role' => 'admin',
                'boutique_id' => null,
                'is_active' => true
            ]);

            $boutique = Boutique::create([
                'nom' => $request->nom,
                'adresse' => $request->adresse,
                'telephone' => $request->telephone,
                'is_active' => true,
                'user_id' =>$user->id,
                'email' => $request->email_admin
            ]);
            $user->boutique_id = $boutique->id;
            $user->save();
            
        });

        return redirect()->route('admin.boutiques.index')->with('success', 'Boutique et administrateur créés avec succès.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.loginPage');
    }

    public function adminsIndex()
    {
        $admins = User::paginate(10); // Standard pagination
        return view('admin.admins.index', compact('admins'));
    }

    public function adminDestroy(User $admin)
    {
        if ($admin->id === Auth::id()) {
            return back()->withErrors(['error' => 'Vous ne pouvez pas supprimer votre propre compte.']);
        }
        $admin->delete();
        return redirect()->route('admin.admins.index')->with('success', 'Administrateur supprimé avec succès.');
    }

    public function toggleBoutiqueStatus(Boutique $boutique)
    {
        $boutique->is_active = !$boutique->is_active;
        $boutique->save();

        $status = $boutique->is_active ? 'activée' : 'désactivée';
        return back()->with('success', "La boutique {$boutique->nom} a été {$status} avec succès.");
    }

    public function userStore(Request $request, Boutique $boutique)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string', 'in:vendeur,comptable,gestionnaire,admin'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'boutique_id' => $boutique->id,
            'is_active' => true
        ]);

        return back()->with('success', "L'utilisateur {$request->name} a été ajouté à la boutique.");
    }

    public function boutiqueUsers(Boutique $boutique)
    {
        $users = $boutique->users()->orderBy('role')->paginate(15);
        return view('admin.boutiques.users', compact('boutique', 'users'));
    }

    public function updateUserPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', "Le mot de passe de {$user->name} a été mis à jour avec succès.");
    }

    public function userDestroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'Vous ne pouvez pas supprimer votre propre compte.']);
        }
        $user->delete();
        return back()->with('success', 'Utilisateur supprimé avec succès.');
    }

    public function boutiqueDestroy(Boutique $boutique)
    {
        $boutique->delete();
        return redirect()->route('admin.boutiques.index')->with('success', 'Boutique supprimée avec succès.');
    }
}
