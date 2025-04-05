<?php

namespace App\Http\Controllers;

use App\Models\ProfileUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserProfileController extends Controller
{
    use apiResponse;

    /**
     * Get the user profile
     */
    public function getProfileUser()
    {
        try {
            $profile = auth()->user()->profile;

            if (!$profile) {
                return $this->apiResponse('Profile not found', null, 404);
            }

            return $this->apiResponse('Profile retrieved successfully', $profile);

        } catch (\Exception $e) {
            return $this->apiResponse('Operation failed', null, 500);
        }
    }

    /**
     * Update or create user profile
     */
    public function updateProfileUser(Request $request)
    {
        try {
            $user = auth()->user();

            $validator = Validator::make($request->all(), [
                'first_name' => 'sometimes|string|max:50',
                'last_name' => 'sometimes|string|max:50',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'date_of_birth' => 'nullable|date|before:-18 years',
            ]);

            if ($validator->fails()) {
                return $this->apiResponse('Validation error', $validator->errors(), 422);
            }

            $profile = $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                $validator->validated()
            );

            return $this->apiResponse('Profile updated successfully', $profile);

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

            auth('client')->logout();

            return $this->apiResponse('Password changed successfully');

        } catch (\Exception $e) {
            return $this->apiResponse('Password change failed', null, 500);
        }
    }
}
