<?php

namespace App\Http\Controllers;

use App\Models\Contrat;
use App\Models\Offre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ContratController extends Controller
{
    use apiResponse;

    public function getAllContractWithFiltrage(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'statut' => ['sometimes', Rule::in(['en_attente', 'signe', 'expire', 'rompu'])],
                'offer_id' => 'sometimes|exists:offres,id',
                'date_debut_min' => 'sometimes|date',
                'date_debut_max' => 'sometimes|date|after_or_equal:date_debut_min',
                'date_fin_min' => 'sometimes|date',
                'date_fin_max' => 'sometimes|date|after_or_equal:date_fin_min',
                'sort' => 'sometimes|in:date_debut,date_fin,created_at',
                'order' => 'sometimes|in:asc,desc',
                'per_page' => 'sometimes|integer|min:1|max:100',
            ]);

            if ($validation->fails()) {
                return $this->apiResponse('Validation error', $validation->errors(), 422);
            }

            $validated = $validation->validated();

            // Determine the query based on authenticated user type
            if (auth()->guard('entreprise')->check()) {
                $query = Contrat::entreprise()->with('offre.projet:id,slug');
            } else {
                $query = Contrat::clinet();
            }

            // Apply filters
            $query->when(isset($validated['statut']), function ($q) use ($validated) {
                $q->where('statut', $validated['statut']);
            });

            $query->when(isset($validated['offer_id']), function ($q) use ($validated) {
                $q->where('offre_id', $validated['offer_id']);
            });

            // Date range filters
            $this->applyDateFilters($query, $validated);

            // Sorting
            if (isset($validated['sort'])) {
                $order = $validated['order'] ?? 'asc';
                $query->orderBy($validated['sort'], $order);
            }

            // Pagination
            $perPage = $validated['per_page'] ?? 10;

            return $this->apiResponse(
                'Contrats retrieved successfully',
                $query->paginate($perPage),
                200
            );

        } catch (\Exception $e) {
            return $this->apiResponse($e->getMessage(), null, 500);
        }
    }

    private function applyDateFilters($query, $validated)
    {
        // Date debut filter
        $query->when(isset($validated['date_debut_min']) || isset($validated['date_debut_max']), function ($q) use ($validated) {
            $q->whereBetween('date_debut', [
                $validated['date_debut_min'] ?? '1970-01-01',
                $validated['date_debut_max'] ?? now()->addCentury()->format('Y-m-d')
            ]);
        });

        // Date fin filter
        $query->when(isset($validated['date_fin_min']) || isset($validated['date_fin_max']), function ($q) use ($validated) {
            $q->whereBetween('date_fin', [
                $validated['date_fin_min'] ?? '1970-01-01',
                $validated['date_fin_max'] ?? now()->addCentury()->format('Y-m-d')
            ]);
        });
    }

    public function createContrat(Request $request)
    {
        try {
            $messages = [
                'offre_id.required' => 'L\'offre est requise.',
                'offre_id.exists' => 'L\'offre sélectionnée est invalide ou n\'appartient pas à votre entreprise.',
                'offre_id.accepted_status' => 'L\'offre doit être acceptée pour être valide.',
                'termes.required' => 'Les termes sont requis.',
                'termes.string' => 'Les termes doivent être une chaîne de caractères.',
                'date_debut.date' => 'La date de début doit être une date valide.',
                'date_fin.date' => 'La date de fin doit être une date valide.',
                'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
                'montant_total.required' => 'Le montant total est requis.',
                'montant_total.numeric' => 'Le montant total doit être un nombre.',
                'montant_total.min' => 'Le montant total doit être supérieur ou égal à 0.',
            ];

            $validation = Validator::make($request->all(), [
                'offer_id' => [
                    'required',
                    Rule::exists('offres', 'id')->where(function ($query) {
                        $query->where('entreprise_id', auth()->guard('entreprise')->id());
                    }),
                    function ($attribute, $value, $fail) {
                        $offre = \App\Models\Offre::where('id', $value)
                            ->where('entreprise_id', auth()->guard('entreprise')->id())
                            ->first();

                        if (!$offre) {
                            $fail('L\'offre sélectionnée est invalide.');
                        } elseif ($offre->statut !== 'acceptee') {
                            $fail('L\'offre doit être acceptée pour être valide.');
                        }
                    }
                ],
                'termes' => 'required|string',
                'date_debut' => 'nullable|date',
                'date_fin' => 'nullable|date|after_or_equal:date_debut',
                'montant_total' => 'required|numeric|min:0',
            ], $messages);


            if ($validation->fails()) {
                return $this->apiResponse('Validation error', $validation->errors(), 422);
            }
            $validatedData = $validation->validated();
            $validatedData['premiere_tranche']=$validatedData['montant_total']*0.3;


            $contrat = Contrat::create($validatedData);

            return $this->apiResponse(
                'Contrat created successfully',
                $contrat->fresh(),
                201
            );

        } catch (\Exception $e) {
            return $this->apiResponse('Error creating contrat', $e->getMessage(), 500);
        }
    }

    public function getContrat($reference)
    {
        try {
            $query = Contrat::where('reference', $reference);

            if (auth()->guard('entreprise')->check()) {
                $entreprise = auth()->guard('entreprise')->user();
                $query->whereHas('offre', function ($q) use ($entreprise) {
                    $q->where('entreprise_id', $entreprise->id);
                });
            } else {
                $user = auth()->user();
                $query->whereHas('offre.projet', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }

            $contrat = $query->with([
                'offre.projet:id,user_id,titre,status',
                'offre.projet.client:id,name',
                'offre.entreprise:id,name',
            ])->withCount('transactions')->firstOrFail();

            return $this->apiResponse('Contrat retrieved successfully', $contrat, 200);

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse('Contrat not found', null, 404);
        } catch (\Exception $e) {
            return $this->apiResponse('Error retrieving contrat', $e->getMessage(), 500);
        }
    }

    public function updateContrat(Request $request, $reference)
    {
        $messages = [
            'termes.prohibited' => 'Vous n\'êtes pas autorisé à modifier les termes.',
            'date_debut.prohibited' => 'Vous n\'êtes pas autorisé à modifier la date de début.',
            'date_fin.prohibited' => 'Vous n\'êtes pas autorisé à modifier la date de fin.',
            'montant_total.prohibited' => 'Vous n\'êtes pas autorisé à modifier le montant total.',
            'statut.in' => 'Le statut doit être "signe", "expire" ou "rompu".',
            'termes.string' => 'Les termes doivent être une chaîne de caractères.',
            'date_debut.date' => 'La date de début doit être une date valide.',
            'date_fin.date' => 'La date de fin doit être une date valide.',
            'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
            'montant_total.numeric' => 'Le montant total doit être un nombre.',
            'montant_total.min' => 'Le montant total doit être supérieur ou égal à 0.',
        ];
        try {
            $validation = Validator::make($request->all(), [
                'termes' => [
                    'sometimes',
                    'string',
                    Rule::prohibitedIf(auth()->guard('client')->check())
                ],
                'date_debut' => [
                    'sometimes',
                    'date',
                    Rule::prohibitedIf(auth()->guard('client')->check())
                ],
                'date_fin' => [
                    'sometimes',
                    'date',
                    'after_or_equal:date_debut',
                    Rule::prohibitedIf(auth()->guard('client')->check())
                ],
                'montant_total' => [
                    'sometimes',
                    'numeric',
                    'min:0',
                    Rule::prohibitedIf(auth()->guard('client')->check())
                ],
                'statut' => [
                    'sometimes',
                    Rule::in(['signe', 'expire', 'rompu']),
                    function ($attribute, $value, $fail) use ($reference) {
                        if ($value === 'signe') {
                            if (auth('entreprise')->check()) {
                                $fail("Vous n'êtes pas autorisé à signer le contrat.");
                            } elseif (Contrat::where('reference', $reference)->whereNotNull('signe_le')->exists()) {
                                $fail("Le contrat est déjà signé.");
                            }
                        }
                    }
                ],
            ], $messages);

            if ($validation->fails()) {
                return $this->apiResponse('Validation error', $validation->errors(), 422);
            }

            $data = $validation->validated();

            $query = Contrat::where('reference', $reference);

            if (auth()->guard('entreprise')->check()) {
                $query->entreprise();
            } else {
                $query->clinet();
                $data['signe_le']=null;
            }

            $contrat = $query->firstOrFail();


            if (isset($data['montant_total'])) {
                $data['premiere_tranche'] = $data['montant_total']*0.3;

            }

            $contrat->update($data);

            return $this->apiResponse(
                'Contrat updated successfully',
                $contrat->fresh(),
                200
            );

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse('Contrat not found', null, 404);
        } catch (\Exception $e) {
            \Log::error('Update error: '.$e->getMessage());
            return $this->apiResponse('Error updating contrat', null, 500);
        }
    }
}
