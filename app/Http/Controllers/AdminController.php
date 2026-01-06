<?php

namespace App\Http\Controllers;

use Mail;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\RedirectResponse;

class AdminController extends Controller
{

    public function adminList(){
        $admins = User::paginate(10); // Pagination 10 par page
        return view('listAdmin', compact('admins'));
    }
    //
    public function new(Request $request):RedirectResponse{
        // $request->validate([
        //     "nom"=>"string|requered",
        //     "email"=>"string|requered|unique",
        //     "password"=>"string|requered"
        // ]);

        //Pour l'ajoute dans la base de donnee :
        // User::create([
        //     'name'=>$request->input("nom"),
        //     'email'=>$request->input("email"),
        //     'password'=>$request->input("password"),
        //     'role'=>"admin0"
        // ])->save();

        //dd($request);

        $admin = new User();
        $admin->name = $request->input("nom");
        $admin->email = $request->input("email");
        $admin->password = $request->input("password");
        $admin->role = "admin0";

        $admin->save();

        return redirect::route("admin.accueil")->with('succes',"Admin Creer avec succes !!!");
    }

    public function login(Request $request){
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($request->only('email','password'))) {
            $user = Auth::user();
            if ($user->role != "admin") {

                return back()->withErrors([
                    'email' => "vous n'etes pas authoriser !!!",
                ])->withInput();

            }
            return redirect()->intended(route('admin.accueil'))->with('success', 'Connexion réussie');
        }

        return back()->withErrors([
            'email' => 'Les identifiants sont incorrects.',
        ])->withInput();
    }
    // Déconnexion
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login')->with('success', 'Déconnexion réussie');
    }

    public function loginIndex(){
        return view("login");
    }


    public function edit($id)
    {
        $admin = User::findOrFail($id);
        return view('editAdmin', compact('admin'));
    }

    public function update(Request $request, $id)
    {
        $admin = User::findOrFail($id);
        $request->validate([
            'nom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
        ]);

        $admin->name = $request->nom;
        $admin->email = $request->email;
        $admin->save();

        return redirect()->route('admin.accueil')->with('success', 'Administrateur modifié avec succès.');
    }


    public function reset($id){

        $admin = User::findOrFail($id);

        // Générer un mot de passe temporaire
        $tempPassword = "12345678";

        $admin->password = bcrypt($tempPassword);
        $admin->save();

        // Envoyer le mot de passe temporaire par email (exemple simple)
        Mail::raw("Votre mot de passe temporaire est : $tempPassword", function ($message) use ($admin) {
            $message->to($admin->email)
                    ->subject('Réinitialisation de votre mot de passe admin');
        });

        return back()->with('success', 'Mot de passe réinitialisé et envoyé par email.');
    }

    public function destroy(User $admin){
        $admin->delete();
        return back()->with('success', 'Compte effacer avec succes !!!');
    }

}
