<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\apiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    use apiResponse;

    public function getAllClients(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'status' => ['sometimes', Rule::in(['active', 'desactiver'])],
                'search' => 'sometimes|string',
                'sort' => 'sometimes|in:created_at,name',
                'order' => 'sometimes|in:asc,desc',
                'per_page' => 'sometimes|integer|min:1|max:100',
            ]);

            if ($validation->fails()) {
                return $this->apiResponse('Validation error', $validation->errors(), 422);
            }

            $query = User::query();

            $this->applyCommonFilters($query, $request);

            return $this->apiResponse(
                'Users retrieved successfully',
                $query->paginate($request->per_page ?? 10)
            );

        } catch (\Exception $e) {
            return $this->apiResponse($e->getMessage(), null, 500);
        }
    }

    // Get all entreprises with filtering
    public function getAllEnterprises(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'status' => ['sometimes', Rule::in(['active', 'desactiver'])],
                'search' => 'sometimes|string',
                'sort' => 'sometimes|in:created_at,name',
                'order' => 'sometimes|in:asc,desc',
                'per_page' => 'sometimes|integer|min:1|max:100',
            ]);

            if ($validation->fails()) {
                return $this->apiResponse('Validation error', $validation->errors(), 422);
            }

            $query = Entreprise::query();

            $this->applyCommonFilters($query, $request);

            return $this->apiResponse(
                'Entreprises retrieved successfully',
                $query->paginate($request->per_page ?? 10)
            );

        } catch (\Exception $e) {
            return $this->apiResponse($e->getMessage(), null, 500);
        }
    }
    public function getClient($id)
    {
        try {
            User::findOrFail($id)->load('profile');

        }catch (ModelNotFoundException $e){
            return $this->apiResponse('User not found', [], 404);
        }
        catch (\Exception $e) {
            return $this->apiResponse($e->getMessage(), null, 500);
        }


    }
    public function getEntreprise($id)
    {
        try {
            Entreprise::findOrFail($id)->load('profile');

        }catch (ModelNotFoundException $e){
            return $this->apiResponse('Entreprise not found', [], 404);
        }
        catch (\Exception $e) {
            return $this->apiResponse($e->getMessage(), null, 500);
        }
    }


    // Update user
    public function changeStatusClinet(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'status' => ['sometimes', Rule::in(['active', 'desactiver'])]
            ]);


            $user->update($validated);

            return $this->apiResponse('User updated successfully', $user);

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse('User not found', null, 404);
        } catch (\Exception $e) {
            return $this->apiResponse($e->getMessage(), null, 500);
        }
    }

    public function changeStatusEntrprise(Request $request, $id)
    {
        try {
            $entreprise = Entreprise::findOrFail($id);

            $validated = $request->validate([
                'status' => ['sometimes', Rule::in(['active', 'desactiver'])]
            ]);


            $entreprise->update($validated);

            return $this->apiResponse('Entreprise updated successfully', $entreprise);

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse('Entreprise not found', null, 404);
        } catch (\Exception $e) {
            return $this->apiResponse($e->getMessage(), null, 500);
        }
    }

    // Delete user
    public function destroyUser($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return $this->apiResponse('User deleted successfully');

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse('User not found', null, 404);
        } catch (\Exception $e) {
            return $this->apiResponse($e->getMessage(), null, 500);
        }
    }

    // Delete entreprise
    public function destroyEntreprise($id)
    {
        try {
            $entreprise = Entreprise::findOrFail($id);
            $entreprise->delete();
            return $this->apiResponse('Entreprise deleted successfully');

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse('Entreprise not found', null, 404);
        } catch (\Exception $e) {
            return $this->apiResponse($e->getMessage(), null, 500);
        }
    }

    private function applyCommonFilters($query, $request)
    {
        $query->when($request->status, function ($q, $status) {
            $q->where('status_account', $status);
        });

        $query->when($request->search, function ($q, $search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        });

        if ($request->sort && $request->order) {
            $query->orderBy($request->sort, $request->order);
        }

        return $query;
    }
}
