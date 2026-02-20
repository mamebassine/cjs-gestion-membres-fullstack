<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MembreController extends Controller
{
    /**
     * Afficher la liste des membres avec recherche et filtre
     */
    public function index(Request $request)
    {
        $query = Membre::query();

        // Recherche
        if ($request->search) {
            $query->where('nom', 'like', "%{$request->search}%")
                  ->orWhere('prenom', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('ville', 'like', "%{$request->search}%")
                  ->orWhere('competences', 'like', "%{$request->search}%");
        }

        // Filtre statut
        if ($request->statut) {
            $query->where('statut', $request->statut);
        }

        // Pagination 10 par page
        $membres = $query->orderBy('id','desc')->paginate(10);

        return response()->json($membres);
    }

    /**
     * Créer un nouveau membre
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:membres,email',
            'telephone' => 'nullable|string|max:20',
            'ville' => 'required|string|max:255',
            'competences' => 'nullable|string',
            'statut' => 'in:actif,inactif',
            'date_naissance' => 'nullable|date',
            'adresse' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        $membre = Membre::create($request->all());

        return response()->json([
            'message' => 'Membre créé avec succès',
            'membre' => $membre
        ], 201);
    }

    /**
     * Afficher un membre
     */
    public function show(string $id)
    {
        $membre = Membre::find($id);

        if (!$membre) {
            return response()->json(['message'=>'Membre non trouvé'], 404);
        }

        return response()->json($membre);
    }

    /**
     * Mettre à jour un membre
     */
    public function update(Request $request, string $id)
    {
        $membre = Membre::find($id);

        if (!$membre) {
            return response()->json(['message'=>'Membre non trouvé'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|required|string|max:255',
            'prenom' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:membres,email,'.$id,
            'telephone' => 'nullable|string|max:20',
            'ville' => 'sometimes|required|string|max:255',
            'competences' => 'nullable|string',
            'statut' => 'in:actif,inactif',
            'date_naissance' => 'nullable|date',
            'adresse' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        $membre->update($request->all());

        return response()->json([
            'message' => 'Membre mis à jour avec succès',
            'membre' => $membre
        ]);
    }

    /**
     * Supprimer un membre
     */
    public function destroy(string $id)
    {
        $membre = Membre::find($id);

        if (!$membre) {
            return response()->json(['message'=>'Membre non trouvé'], 404);
        }

        $membre->delete();

        return response()->json([
            'message' => 'Membre supprimé avec succès'
        ]);
    }

    /**
     * Statistiques pour dashboard
     */
    public function stats()
    {
        return response()->json([
            'total' => Membre::count(),
            'actifs' => Membre::where('statut','actif')->count(),
            'inactifs' => Membre::where('statut','inactif')->count(),
            'villes' => Membre::distinct('ville')->count('ville'),
        ]);
    }
}