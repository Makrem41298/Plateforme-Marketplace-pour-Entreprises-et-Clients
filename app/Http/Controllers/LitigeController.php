<?php

namespace App\Http\Controllers;

use App\Models\Contrat;
use App\Models\Litige;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LitigeController extends Controller
{
    use apiResponse;

    public function getAllLitigeOrOrFiltrage(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'statut' => ['sometimes', Rule::in(['ouvert', 'en_investigation', 'resolu', 'ferme'])],
                'type' => ['sometimes', Rule::in(['paiement', 'livraison', 'qualite', 'delai', 'autre'])],
                'reference_contrat' => 'sometimes|exists:contrats,reference',
                'sort' => 'sometimes|in:created_at,updated_at',
                'order' => 'sometimes|in:asc,desc',
                'date_start' => 'sometimes|date',
                'date_end' => 'sometimes|date|after_or_equal:date_start',
                'per_page' => 'sometimes|integer|min:1|max:100',
            ]);

            if ($validation->fails()) {
                return $this->apiResponse($validation->errors()->first(), null, 422);
            }

            $validated = $validation->validated();

            // Get the authenticated entity (User or Entreprise)
            $litigeable = auth()->guard('entreprise')->check()
                ? auth()->guard('entreprise')->user()
                : auth()->user();
            if (!auth()->guard('admin')->check()) {
                $query = $litigeable->litiges();
            }else
                $query = Litige::query();



            $query->when(isset($validated['statut']), function ($q) use ($validated) {
                $q->where('statut', $validated['statut']);
            });

            $query->when(isset($validated['type']), function ($q) use ($validated) {
                $q->where('type', $validated['type']);
            });

            $query->when(isset($validated['reference_contrat']), function ($q) use ($validated) {
                $q->where('reference_contrat', $validated['reference_contrat']);
            });

            $query->when(isset($validated['date_start']) && isset($validated['date_end']), function ($q) use ($validated) {
                $q->whereBetween('created_at', [$validated['date_start'], $validated['date_end']]);
            });

            // Sorting
            if (isset($validated['sort'])) {
                $order = $validated['order'] ?? 'asc';
                $query->orderBy($validated['sort'], $order);
            }

            $perPage = $validated['per_page'] ?? 10;

            return $this->apiResponse(
                'List of litiges retrieved successfully',
                $query->paginate($perPage),
                200
            );

        } catch (\Exception $e) {
            return $this->apiResponse($e->getMessage(), null, 500);
        }
    }

    public function createLitige(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'titre' => 'required|string|max:255',
                'description' => 'required|string|min:20',
                'reference_contrat' => 'required|exists:contrats,reference',
                'type' => ['required', Rule::in(['paiement', 'livraison', 'qualite', 'delai', 'autre'])],
            ]);

            if ($validation->fails()) {
                return $this->apiResponse($validation->errors()->first(), null, 422);
            }

            $litigeable = auth()->guard('entreprise')->check()
                ? auth()->guard('entreprise')->user()
                : auth()->user();


            $contrat = Contrat::where('reference', $request->reference_contrat)->firstOrFail();

            if (auth()->guard('entreprise')->check()) {
                if ($contrat->offre->entreprise_id !== $litigeable->id) {
                    return $this->apiResponse('Unauthorized to create litige for this contrat', null, 403);
                }
            } else {
                if ($contrat->offre->projet->user_id !== $litigeable->id) {
                    return $this->apiResponse('Unauthorized to create litige for this contrat', null, 403);
                }
            }
            $dataValidation=$validation->validated();

            $litige = $litigeable->litiges()->create(
                $dataValidation
            );

            return $this->apiResponse('Litige created successfully', $litige, 201);

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse('Contrat not found', null, 404);
        } catch (\Exception $e) {
            \Log::error('Error creating litige: ' . $e->getMessage());
            return $this->apiResponse('Error creating litige', null, 500);
        }
    }

    public function getLitige($reference)
    {
        try {
            $litige = Litige::where('reference', $reference)->firstOrFail();

            $litigeable = auth()->guard('entreprise')->check()
                ? auth()->guard('entreprise')->user()
                : (auth()->check()
                    ? auth()->user()
                    : auth()->guard('admin')->user());


            if (($litige->litigeable_id !== $litigeable->id || $litige->litigeable_type !== get_class($litigeable))&&!auth()->guard('admin')->check()) {
                return $this->apiResponse('Unauthorized to view this litige', null, 403);
            }

            return $this->apiResponse('Litige retrieved successfully', $litige, 200);

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse('Litige not found', null, 404);
        } catch (\Exception $e) {
            \Log::error('Error retrieving litige: ' . $e->getMessage());
            return $this->apiResponse('Error retrieving litige', null, 500);
        }
    }

    public function updateLitige(Request $request, $reference)
    {
        try {
            $validation = Validator::make($request->all(), [
                'statut' => ['sometimes', Rule::in(['ouvert', 'en_investigation', 'resolu', 'ferme'])],
                'decision' => 'required_if:statut,resolu,ferme|nullable|string',
                'resolution_type' => ['required_if:statut,resolu', Rule::in(['remboursement_partiel', 'remboursement_total', 'reparation', 'compensation'])],
            ]);

            if ($validation->fails()) {
                return $this->apiResponse($validation->errors()->first(), null, 422);
            }


            $litige = Litige::where('reference', $reference)->firstOrFail();


            $data = $validation->validated();

            if (isset($data['statut']) && in_array($data['statut'], ['resolu', 'ferme'])) {
                $data['updated_at'] = now();
            }

            $litige->update($data);

            return $this->apiResponse('Litige updated successfully', $litige, 200);

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse('Litige not found', null, 404);
        } catch (\Exception $e) {
            \Log::error('Error updating litige: ' . $e->getMessage());
            return $this->apiResponse('Error updating litige', null, 500);
        }
    }

    public function destroy($reference)
    {
        try {
            $litige = Litige::where('reference', $reference)->firstOrFail();
            dump($litige);
            if ($litige->statut!=="ouvert"){
                return $this->apiResponse('can not delete this litige because is it not ouvert', null, 404);

            }

            $litigeable = auth()->guard('entreprise')->check()
                ? auth()->guard('entreprise')->user()
                : auth()->user();

            if ($litige->litigeable_id !== $litigeable->id || $litige->litigeable_type !== get_class($litigeable)) {
                return $this->apiResponse('Unauthorized to delete this litige', null, 403);
            }

            $litige->delete();
            return $this->apiResponse('Litige deleted successfully', null, 204);

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse('Litige not found', null, 404);
        } catch (\Exception $e) {
            \Log::error('Error deleting litige: ' . $e->getMessage());
            return $this->apiResponse('Error deleting litige', null, 500);
        }
    }


}
