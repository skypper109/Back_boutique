<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // On retourne toutes les categories
        $categories = Categorie::orderByDesc('id')->get();
        return response()->json(data: $categories);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Creation d'une nouvelle categorie avec les donnees recues du formulaire qui contient les champs nom et description
        $categorie = new Categorie();
        $categorie->nom = $request->nom;
        $categorie->description = $request->description;
        $categorie->save();
        return response()->json(['message' => 'Categorie creee avec succes'], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(Categorie $categorie)
    {
        // On retourne la categorie qui a ete trouvee


        return response()->json($categorie);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Categorie $categorie)
    {
        // On retourne la categorie qui a ete trouvee
        return response()->json($categorie);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Categorie $categorie)
    {
        // On met a jour la categorie qui a ete trouvee
        $categorie->update($request->all());
        // $categorie->nom = $request->nom;
        // $categorie->description = $request->description;
        // $categorie->save();
        return response()->json(['message' => 'Categorie modifiee avec succes'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categorie $categorie)
    {
        // On supprime la categorie qui a ete trouvee
        $categorie->delete();
        return response()->json(['message' => 'Categorie supprimee avec succes'], 200);
    }
}
