<?php

namespace App\Http\Controllers;

use App\Models\Offre;
use App\Models\Projet;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OffreController extends Controller
{
    use ApiResponse;

    // Consistent French naming for all methods
    public function getAllOffresOrFiltrage(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'montant_min' => 'sometimes|numeric|gte:0',
                'montant_max' => 'sometimes|numeric|gt:montant_min',
                'statut' => ['sometimes', Rule::in(Offre::getAvailableStatus())],
                'date_debut' => 'sometimes|date|before_or_equal:date_fin',
                'date_fin' => 'sometimes|date|after_or_equal:date_debut',
                'projet_titre' => 'sometimes|string|min:3|max:255',
                'sort_by' => 'sometimes|in:created_at,montant_propose,delai',
                'sort_order' => 'sometimes|in:asc,desc',
                'per_page' => 'sometimes|integer|min:1|max:100',
            ], [
                'montant_max.gt' => 'Le montant maximum doit être supérieur au montant minimum',
                'statut.in' => 'Statut non valide',
                'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début',
            ]);


            if ($validation->fails()) {
                return $this->apiResponse($validation->errors()->first(), null, 422);
            }

            $validated = $validation->validated();
            $entreprise = auth('entreprise')->user();
            $query = Offre::with([
                'projet.client:id,name','projet:id,user_id,slug,titre'
            ])->where('entreprise_id', $entreprise->id);


            $query=$this->filter($query, $validated);
            $perPage = $validated['per_page'] ?? 10;
            $offres = $query->paginate($perPage);

            return $this->apiResponse(
                'Liste des offres récupérée avec succès',
                $offres,
                200
            );

        } catch (\Exception $e) {
            Log::error('Erreur récupération offres: '.$e->getMessage());
            return $this->apiResponse('Erreur serveur', null, 500);
        }
    }

    public function createOffre(Request $request)
    {
        try {
            $entreprise = auth('entreprise')->user();

            $validation = Validator::make($request->all(), [
                'montant_propose' => 'required|numeric|min:0.01|max:99999999.99',
                'delai' => 'required|integer|min:1|max:365',
                'description' => 'required|string|min:20|max:2000',
                'projet_id' => [
                    'required',
                    'exists:projets,id',
                    Rule::unique('offres')
                        ->where('entreprise_id', $entreprise->id)
                        ->where('projet_id', $request->projet_id)
                ],
            ], [
                'projet_id.unique' => 'Vous avez déjà postulé à ce projet',
                'montant_propose.numeric' => 'Le montant doit être un nombre valide',
            ]);

            if ($validation->fails()) {
                return $this->apiResponse($validation->errors()->first(), null, 422);
            }

            DB::beginTransaction();

            $offre = Offre::create([
                ...$validation->validated(),
                'entreprise_id' => $entreprise->id,
                'statut' => 'en_attente'
            ]);

            DB::commit();

            return $this->apiResponse(
                'Offre créée avec succès',
                $offre->load('projet:id,titre'),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Création offre échouée: '.$e->getMessage());
            return $this->apiResponse('Erreur création offre', $e->getMessage(), 500);
        }
    }

    public function updateOffre(Request $request, $offre_id)
    {
        try {
            $validation = Validator::make($request->all(), [
                'montant_propose' => 'sometimes|numeric|min:0.01|max:99999999.99',
                'delai' => 'sometimes|integer|min:1|max:365',
                'description' => 'sometimes|string|min:20|max:2000',
                'statut' => ['sometimes', Rule::in(Offre::getAvailableStatus())]
            ]);

            if ($validation->fails()) {
                return $this->apiResponse($validation->errors()->first(), null, 422);
            }
             $validationData=$validation->validated();
            if (auth('entreprise')->check()){
                if (isset($validationData['status'])) {
                    return $this->apiResponse(
                        'Modification impossible modifer status les offres ',
                        null,
                        403
                    );
                }
                $entreprise = auth('entreprise')->user();
                $offre = Offre::where('entreprise_id', $entreprise->id)
                    ->findOrFail($offre_id);
            }

            $offre->update($validationData);

            return $this->apiResponse(
                'Offre mise à jour avec succès',
                $offre->fresh(),
                200
            );

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse('Offre non trouvée', null, 404);
        } catch (\Exception $e) {
            Log::error('Mise à jour offre échouée: '.$e->getMessage());
            return $this->apiResponse('Erreur mise à jour', null, 500);
        }
    }

    public function deleteOffre($offre_id)
    {
        try {
            $entreprise = auth('entreprise')->user();
            $offre = Offre::where('entreprise_id', $entreprise->id)
                ->findOrFail($offre_id);

            if ($offre->statut === 'acceptee') {
                return $this->apiResponse(
                    'Suppression impossible pour les offres acceptées',
                    null,
                    403
                );
            }

            $offre->delete();

            return $this->apiResponse('Offre supprimée avec succès', null, 200);

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse('Offre non trouvée', null, 404);
        } catch (\Exception $e) {
            Log::error('Suppression offre échouée: '.$e->getMessage());
            return $this->apiResponse('Erreur suppression', null, 500);
        }
    }

    public function getOffre($offre_id)
    {
        try {
            $entreprise = auth('entreprise')->user();
            $offre = Offre::with(['projet' => fn($q) => $q->select('id', 'titre')])
                ->where('entreprise_id', $entreprise->id)
                ->findOrFail($offre_id);

            return $this->apiResponse('Offre récupérée avec succès', $offre, 200);

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse('Offre non trouvée', null, 404);
        } catch (\Exception $e) {
            Log::error('Récupération offre échouée: '.$e->getMessage());
            return $this->apiResponse('Erreur récupération', null, 500);
        }
    }
    public function getOffreClient($slug, Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'montant_min' => ['sometimes', 'numeric', 'min:0', 'lt:montant_max'],
                'montant_max' => ['sometimes', 'numeric', 'gt:montant_min'],
                'statut' => ['sometimes', Rule::in(Offre::getAvailableStatus())],
                'date_debut' => 'sometimes|date|before_or_equal:date_fin',
                'date_fin' => 'sometimes|date|after_or_equal:date_debut',
                'sort_by' => 'sometimes|in:created_at,montant_propose,delai',
                'sort_order' => 'sometimes|in:asc,desc',
                'per_page' => 'sometimes|integer|min:1|max:100',
            ], [
                'montant_max.gt' => 'Le montant maximum doit être supérieur au montant minimum',
                'statut.in' => 'Statut non valide',
                'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début',
            ]);

            if ($validation->fails()) {
                return $this->apiResponse($validation->errors()->first(), null, 422);
            }

            $validated = $validation->validated();
            $client = auth('client')->user();

            $query = Offre::whereHas('projet', function ($q) use ($client, $slug) {
                $q->where('user_id', $client->id)
                    ->where('slug', $slug);
            })->with('entreprise:id,name');
            $query=$this->filter($query, $validated);
            $perPage = $validated['per_page'] ?? 10;
            $offres = $query->paginate($perPage);
            return $this->apiResponse(
                'Liste des offres récupérée avec succès',
                $offres,
                200
            );
        } catch (\Exception $e) {
            return $this->apiResponse('Erreur serveur', ['error' => $e->getMessage()], 500);
        }
    }
    private function filter($query,$validated){
        $query->when(isset($validated['projet_titre']), function ($q) use ($validated) {
            $q->whereHas('projets', function ($subQ) use ($validated) {
                $subQ->where('titre', 'LIKE', '%' . $validated['projet_titre'] . '%');
            });
        });

        $query->when(isset($validated['montant_min']), function ($q) use ($validated) {
            $q->where('montant_propose', '>=',(int)$validated['montant_min']);
        });

        $query->when(array_key_exists('montant_max', $validated), function ($q) use ($validated) {
            $q->where('montant_propose', '<=', (int)$validated['montant_max']);
        });


        $query->when(isset($validated['statut']), function ($q) use ($validated) {
            $q->where('statut', $validated['statut']);
        });

        $query->when(isset($validated['date_end']), function ($q) use ($validated) {
            $q->whereBetween('created_at', [$validated['date_start'], $validated['date_end']]);
        });

        $query->when(isset($validated['sort_by']), function ($q) use ($validated) {
            if ($validated['sort_by'] == 'date') {
                $validated['sort_by'] = 'created_at';
            }


            $q->orderBy($validated['sort_by'], $validated['sort_order']);
        }, function ($q) use ($validated) {
            $q->orderBy('created_at', 'DESC');
        });
        return $query;
    }

}
