<?php

namespace App\Http\Controllers;

use App\Http\Controllers\apiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Entreprise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
                $query->paginate($request->per_page ?? 10),200
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
                $query->paginate($request->per_page ?? 10),
                200

            );

        } catch (\Exception $e) {
            return $this->apiResponse($e->getMessage(), null, 500);
        }
    }
    public function getClient($id)
    {
        try {
            $client=User::findOrFail($id)->load('profile');
            return $this->apiResponse('Client retrieved successfully',$client,200);

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
           $entreprise= Entreprise::findOrFail($id)->load('profile');
            return $this->apiResponse('Entreprise retrieved successfully ',$entreprise,200);

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


            $validator=Validator::make($request->all(),[
                'status_account' => ['required', Rule::in(['active', 'desactiver'])]
            ]);
            if($validator->fails()){
                return $this->apiResponse('Validation error', $validator->errors(), 422);
            }
            $user = User::findOrFail($id);
            $user->update($validator->validated());


            return $this->apiResponse('User updated successfully', $user,200);

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

            $validator=Validator::make($request->all(),[
                'status_account' => ['required', Rule::in(['active', 'desactiver'])]
            ]);
            if($validator->fails()){
                return $this->apiResponse('Validation error', $validator->errors(), 422);
            }

            $entreprise->update($validator->validated());

            return $this->apiResponse('Entreprise updated successfully', $entreprise,200);

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
            return $this->apiResponse('User deleted successfully',null,204);

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse('User not found', null, 404);
        } catch (\Exception $e) {
            return $this->apiResponse($e->getMessage(), null, 500);
        }
    }

    public function destroyEntreprise($id)
    {
        try {
            $entreprise = Entreprise::findOrFail($id);
            $entreprise->delete();
            return $this->apiResponse('Entreprise deleted successfully', null, 204);

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse('Entreprise not found', null, 404);
        } catch (\Exception $e) {
            return $this->apiResponse($e->getMessage(), null, 500);
        }
    }
    public function changePasswordAdmin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
                'new_password_confirmation' => 'required|string'
            ]);

            if ($validator->fails()) {
                return $this->apiResponse('Validation error', $validator->errors(), 422);
            }

            $entreprise = auth('admin')->user();

            if (!Hash::check($request->current_password, $entreprise->password)) {
                return $this->apiResponse('Current password is incorrect', null, 401);
            }

            $entreprise->update([
                'password' => Hash::make($request->new_password)
            ]);

            auth('admin')->logout();

            return $this->apiResponse('Password changed successfully', null, 200);

        } catch (\Exception $e) {
            return $this->apiResponse('Password change failed', null, 500);
        }
    }

    private function applyCommonFilters($query, $request)
    {

        $query->when($request->status, function ($q, $status) {
            $q->where('status_account', $status);
        });

        $query->when($request->search, function ($q, $search) {
              $q->where('email', 'like', "%{$search}%");
        });

        if ($request->sort && $request->order) {
            $query->orderBy($request->sort, $request->order);
        }

        return $query;
    }
}
