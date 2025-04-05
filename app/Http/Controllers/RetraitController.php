<?php

namespace App\Http\Controllers;

use App\Models\Retrait;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RetraitController extends Controller
{
    use apiResponse;

    public function index(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'statut' => ['sometimes', Rule::in(['demande_initiee', 'en_transit', 'complete', 'rejete'])],
                'methode' => ['sometimes', Rule::in(['virement_bancaire', 'paypal'])],
                'sort' => 'sometimes|in:created_at,updated_at',
                'order' => 'sometimes|in:asc,desc',
                'date_start' => 'sometimes|date',
                'date_end' => 'sometimes|date|after_or_equal:date_start',
                'per_page' => 'sometimes|integer|min:1|max:100',
            ]);

            if ($validation->fails()) {
                return $this->apiResponse('Validation error', $validation->errors(), 422);
            }

            $validated = $validation->validated();

            $query = Retrait::query();

            if (auth()->guard('entreprise')->check()) {
                $query->where('entreprise_id', auth()->guard('entreprise')->id());
            }

            // Apply filters
            $query->when(isset($validated['statut']), function ($q) use ($validated) {
                $q->where('statut', $validated['statut']);
            });

            $query->when(isset($validated['methode']), function ($q) use ($validated) {
                $q->where('methode', $validated['methode']);
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
                'List of retraits retrieved successfully',
                $query->paginate($perPage),
                200
            );

        } catch (\Exception $e) {
            return $this->apiResponse($e->getMessage(), null, 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'montant' => 'required|numeric|min:100|max:1000000',
                'methode' => ['required', Rule::in(['virement_bancaire', 'paypal'])],
                'info_compte_' => 'required|string|max:255',
            ]);

            if ($validation->fails()) {
                return $this->apiResponse('Validation error', $validation->errors(), 422);
            }

            // Only entreprises can create retraits
            $entreprise = auth()->guard('entreprise')->user();
            $dataValidated = $validation->validated();

            $retrait = $entreprise->retraits()->create($dataValidated);

            return $this->apiResponse('Withdrawal request created successfully', $retrait, 201);

        } catch (\Exception $e) {
            \Log::error('Error creating retrait: ' . $e->getMessage());
            return $this->apiResponse('Error creating withdrawal request', null, 500);
        }
    }

    public function show($reference)
    {
        try {
            $retrait = Retrait::where('reference', $reference)->firstOrFail();

            // Authorization check
            if (auth()->guard('entreprise')->check() && $retrait->entreprise_id !== auth()->guard('entreprise')->id()) {
                return $this->apiResponse('Unauthorized to view this withdrawal', null, 403);
            }

            return $this->apiResponse('Withdrawal request retrieved successfully', $retrait, 200);

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse('Withdrawal request not found', null, 404);
        } catch (\Exception $e) {
            \Log::error('Error retrieving retrait: ' . $e->getMessage());
            return $this->apiResponse('Error retrieving withdrawal request', null, 500);
        }
    }

    public function update(Request $request, $reference)
    {
        try {
            $validation = Validator::make($request->all(), [
                'statut' => ['sometimes', Rule::in(['en_transit', 'complete', 'rejete'])],
                'notes_administratives' => 'sometimes|string|nullable',
            ]);

            if ($validation->fails()) {
                return $this->apiResponse('Validation error', $validation->errors(), 422);
            }

            $retrait = Retrait::where('reference', $reference)->firstOrFail();


            $retrait->update($validation->validated());

            return $this->apiResponse('Withdrawal request updated successfully', $retrait, 200);

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse('Withdrawal request not found', null, 404);
        } catch (\Exception $e) {
            \Log::error('Error updating retrait: ' . $e->getMessage());
            return $this->apiResponse('Error updating withdrawal request', null, 500);
        }
    }

    public function destroy($reference)
    {
        try {
            $retrait = Retrait::where('reference', $reference)->firstOrFail();

            // Only admin or owning entreprise can delete in "initiated" status
            if (auth()->guard('admin')->check() ||
                (auth()->guard('entreprise')->check() &&
                    $retrait->entreprise_id === auth()->guard('entreprise')->id() &&
                    $retrait->statut === 'demande_initiee')) {
                $retrait->delete();
                return $this->apiResponse('Withdrawal request deleted successfully', null, 200);
            }

            return $this->apiResponse('error to delete this withdrawal request', null, 422);

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse('Withdrawal request not found', null, 404);
        } catch (\Exception $e) {
            \Log::error('Error deleting retrait: ' . $e->getMessage());
            return $this->apiResponse('Error deleting withdrawal request', null, 500);
        }
    }
}
