<?php

namespace App\Http\Controllers;

use App\Models\ProfileEntreprise;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class EntrepriseProfileController extends Controller
{
    use apiResponse;

    /**
     * Create or update the company profile
     */


    /**
     * Get the company profile
     */
    public function getProfileEntreprise()
    {
        try {
            $profile = auth('entreprise')->user()->profile;

            if (!$profile) {
                return $this->apiResponse('Profile not found', null, 404);
            }

            return $this->apiResponse('Profile retrieved successfully', $profile,200);

        } catch (\Exception $e) {
            return $this->apiResponse('Operation failed', null, 500);
        }
    }

    /**
     * Update the company profile
     */
    public function updateProfileEntreprise(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'address' => 'sometimes|string|max:255',
                'city' => 'sometimes|string|max:100',
                'country' => 'sometimes|string|max:100',
                'postal_code' => 'sometimes|string|max:20',
                'phone' => 'sometimes|string|max:20',
                'fax' => 'nullable|string|max:20',
                'website' => 'nullable|url|max:255',
                'description' => 'sometimes|string|min:50|max:2000',
                'sector' => 'sometimes|string|max:100',
                'company_type' => ['sometimes', Rule::in(['LLC', 'SA', 'SARL', 'SNC', 'EI', 'Other'])],
                'linkedin_url' => 'nullable|url|max:255',
                'facebook_url' => 'nullable|url|max:255',
                'twitter_handle' => 'nullable|string|max:50',
                'instagram_url' => 'nullable|url|max:255',
                'employees_count' => 'sometimes|integer|min:1'
            ]);

            if ($validator->fails()) {
                return $this->apiResponse('Validation error', $validator->errors(), 422);
            }
            $entreprise = auth('entreprise')->user();


            $profile = $entreprise->profile()->updateOrCreate(
                ['entreprise_id' => $entreprise->id],
                $validator->validated()
            );

            return $this->apiResponse('Profile updated successfully', $profile,201 );

        } catch (\Exception $e) {
            return $this->apiResponse('Operation failed', null, 500);
        }

    }


    public function changePassword(Request $request)
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

            $entreprise = auth('entreprise')->user();

            if (!Hash::check($request->current_password, $entreprise->password)) {
                return $this->apiResponse('Current password is incorrect', null, 401);
            }

            $entreprise->update([
                'password' => Hash::make($request->new_password)
            ]);

            auth('entreprise')->logout();

            return $this->apiResponse('Password changed successfully', null, 200);

        } catch (\Exception $e) {
            return $this->apiResponse('Password change failed', null, 500);
        }
    }


}
