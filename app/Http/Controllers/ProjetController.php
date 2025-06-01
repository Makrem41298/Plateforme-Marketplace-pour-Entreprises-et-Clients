<?php

namespace App\Http\Controllers;

use App\Models\Projet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProjetController extends Controller
{
    use apiResponse;

    public function getAllProjetsWithFiltrage(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'titre'        => 'sometimes|string',
                'budget_min'   => 'sometimes|numeric|gte:0',
                'budget_max'   => 'sometimes|numeric|gt:budget_min',
                'type'         => ['sometimes', Rule::in(Projet::getAvailableTypes())],
                'sort_field'   => 'sometimes|required_with:sort_order|in:budget,date',
                'sort_order'   => 'sometimes|required_with:sort_field|in:asc,desc',
                'date_start'   => 'sometimes|date',
                'date_end'     => 'sometimes|date|after_or_equal:date_start',
                'per_page'     => 'sometimes|integer|min:1|max:100',
            ], [
                'titre.min'         => 'Le titre doit contenir au moins :min caractères',
                'budget_min.gte'    => 'Le budget minimum doit être au moins :gte',
                'budget_max.gt'     => 'Le budget maximum doit être supérieur au budget minimum',
                'type.in'           => 'Type de projet non valide',
            ]);

            if ($validation->fails()) {
                return $this->apiResponse($validation->errors()->first(),null , 422);
            }

            $validated = $validation->validated();

            if (auth()->guard('entreprise')->check()) {
                $query = Projet::query();
            } else {
                $user = auth()->user();
                $query = $user->projets();
            }

            $query->when(isset($validated['titre']), function ($q) use ($validated) {
                $q->where('titre', 'LIKE', '%' . $validated['titre'] . '%');
            });

            $query->when(isset($validated['budget_min']), function ($q) use ($validated) {
                $q->where('budget', '>=', $validated['budget_min']);
            });

            $query->when(isset($validated['budget_max']), function ($q) use ($validated) {
                $q->where('budget', '<=', $validated['budget_max']);
            });

            $query->when(isset($validated['type']), function ($q) use ($validated) {
                $q->where('type', $validated['type']);
            });

            $query->when(isset($validated['date_end']), function ($q) use ($validated) {
                $q->whereBetween('created_at', [$validated['date_start'], $validated['date_end']]);
            });

            $query->when(isset($validated['sort_field']), function ($q) use ($validated) {
                if ($validated['sort_field'] == 'date') {
                    $validated['sort_field'] = 'created_at';
                }
                $q->orderBy($validated['sort_field'], $validated['sort_order']);
            }, function ($q) use ($validated) {
                $q->orderBy('created_at', 'DESC');
            });

            $perPage = $validated['per_page'] ?? 10;

            return $this->apiResponse(
                'Liste des projets récupérée avec succès',
                $query->paginate($perPage),
                200
            );

        } catch (\Exception $e) {
            return $this->apiResponse($e->getMessage(), null, 500);
    }
    }
    public function createProjet(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'titre' => 'required|string|min:5|max:255',
                'description' => 'required|string|min:50|max:2000',
                'budget' => 'nullable|numeric|between:0.01,99999999.99',
                'type' => [
                    'required',
                    Rule::in(Projet::getAvailableTypes())
                ],
                'Delai' => 'nullable|integer|min:1|max:365'
            ], [
                'titre.required' => 'Le titre est obligatoire',
                'titre.min' => 'Le titre doit contenir au moins :min caractères',
                'description.min' => 'La description doit contenir au moins :min caractères',
                'budget.between' => 'Le budget doit être entre :min et :max',
                'type.in' => 'Type de projet non valide',
                'Delai.max' => 'Le délai maximum est de :max jours'
            ]);

            if ($validation->fails()) {
                return $this->apiResponse($validation->errors()->first(),null , 422);
            }

            $client = auth()->user();
            $slug = $this->generateUniqueSlug($request->titre);

            $projet = $client->projets()->create(array_merge(
                $validation->validated(),
                ['slug' => $slug]
            ));

            return $this->apiResponse('Projet créé avec succès', $projet, 201);

        } catch (\Exception $e) {
            \Log::error('Erreur création projet : ' . $e->getMessage());
            return $this->apiResponse($e->getMessage(), null, 500);
        }
    }

    public function updateProjet(Request $request, $slug)
    {
        try {
            $validation = Validator::make($request->all(), [
                'titre' => 'sometimes|string|min:5|max:255',
                'description' => 'sometimes|string|min:50|max:2000',
                'budget' => 'sometimes|nullable|numeric|between:0.01,99999999.99',
                'type' => [
                    'sometimes',
                    Rule::in(Projet::getAvailableTypes())
                ],
                'Delai' => 'sometimes|nullable|integer|min:1|max:365'
            ]);

            if ($validation->fails()) {
                return $this->apiResponse($validation->errors()->first(),null , 422);
            }
            if (auth('entreprise')->check()) {
                $enterprise = auth('entreprise')->user();

                $offre = $enterprise->offre()->whereHas('projet', function ($query) use ($slug) {
                    $query->where('slug', $slug);
                })->first();

                if (!$offre) {
                    return $this->apiResponse('Offre ou projet introuvable', null, 404);
                }

                $projet = $offre->projet;

                $projet->update([
                    'status' => 'Termine'
                ]);

                return $this->apiResponse('Projet mis à jour avec succès', $projet->fresh(), 200);
            }


            $client = auth()->user();
            $projet = $client->projet()
                ->where('slug', $slug)
                ->whereDoesntHave('offre.contrat', function ($query) {
                    $query->where('status', '!=', 'en_attente');
                })
                ->first();

            if ($projet === null) {
                return $this->apiResponse('Le projet ne peut pas être modifié : ' . $slug, null, 200);
            }

            $data = $validation->validated();

            if ($request->has('titre')) {
                $data['slug'] = $this->generateUniqueSlug($request->titre, $projet->id);
            }

                $projet->update($data);



            return $this->apiResponse('Projet mis à jour avec succès', $projet->fresh(), 200);

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse("projet  introuvable", null, 404);
        } catch (\Exception $e) {
            \Log::error('Erreur mise à jour projet : ' . $e->getMessage());
            return $this->apiResponse($e->getMessage(), null, 500);
        }
    }

    public function deleteProjet($slug)
    {
        try {
            $client = auth()->user();
            $projet = $client->projets()
                ->where('slug', $slug)
                ->whereDoesntHave('offre.contrat', function ($query) {
                    $query->where('status', '!=', 'en_attente');
                })
                ->first();

            if ($projet === null) {
                return $this->apiResponse('Le projet ne peut pas être modifié : ' . $slug, null, 200);
            }

            $projet->delete();
            return $this->apiResponse('Projet supprimé avec succès', null, 200);

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse('Projet non trouvé', null, 404);
        } catch (\Exception $e) {
            \Log::error('Erreur suppression projet : ' . $e->getMessage());
            return $this->apiResponse('Erreur lors de la suppression', null, 500);
        }
    }

    public function getProjet($slug)
    {
        try {
            $projet = Projet::where('slug', $slug)->firstOrFail();
            return $this->apiResponse('Projet récupéré avec succès', $projet, 200);

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse('Projet non trouvé', null, 404);
        } catch (\Exception $e) {
            \Log::error('Erreur récupération projet : ' . $e->getMessage());
            return $this->apiResponse('Erreur lors de la récupération', null, 500);
        }
    }

   private function generateUniqueSlug($title, $excludeId = null)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $count = 1;

        $query = Projet::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
            $query = Projet::where('slug', $slug);
        }

        return $slug;
    }
}
