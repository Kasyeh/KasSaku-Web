<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class AvatarController extends Controller
{
    /**
     * Handle custom avatar upload
     */
    public function upload(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048' // Max 2MB
        ]);

        $user = User::find(Auth::id());

        if ($request->hasFile('avatar')) {
            // Delete old custom avatar if exists
            $this->deleteOldAvatar($user->avatar);

            $file = $request->file('avatar');
            $filename = 'avatar_' . $user->id_user . '_' . time() . '.' . $file->getClientOriginalExtension();
            
            // Store to public disk (storage/app/public/avatars)
            $path = $file->storeAs('avatars', $filename, 'public');

            // Generate full URL
            $url = asset('storage/' . $path);
            
            $user->avatar = $url;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Avatar berhasil diunggah',
                'avatar_url' => $url
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengunggah avatar'
        ], 400);
    }

    /**
     * Set predefined avatar
     */
    public function setPredefined(Request $request)
    {
        $request->validate([
            'avatar_id' => 'required|string'
        ]);

        $user = User::find(Auth::id());
        $avatarId = $request->avatar_id; // e.g. 'avatar-1.png'
        
        // Define allowed predefined avatars
        $allowed = ['avatar-1.png', 'avatar-2.png', 'avatar-3.png'];
        
        if (!in_array($avatarId, $allowed)) {
            return response()->json([
                'success' => false,
                'message' => 'Avatar tidak valid'
            ], 400);
        }

        // Delete old custom avatar if exists
        $this->deleteOldAvatar($user->avatar);

        // Predefined avatars will be stored in public/assets/avatars/
        $url = asset('assets/avatars/' . $avatarId);
        
        $user->avatar = $url;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Avatar berhasil diubah',
            'avatar_url' => $url
        ]);
    }

    /**
     * Remove avatar (revert to initials)
     */
    public function remove(Request $request)
    {
        $user = User::find(Auth::id());
        
        $this->deleteOldAvatar($user->avatar);

        $user->avatar = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Avatar berhasil dihapus',
            'avatar_url' => null
        ]);
    }

    /**
     * Helper to delete old custom avatar to save space
     */
    private function deleteOldAvatar($currentAvatarUrl)
    {
        if ($currentAvatarUrl) {
            // Check if it's a locally stored avatar
            if (strpos($currentAvatarUrl, asset('storage/avatars')) !== false) {
                // Extract filename from URL
                $basename = basename($currentAvatarUrl);
                $path = 'public/avatars/' . $basename;
                
                if (Storage::exists($path)) {
                    Storage::delete($path);
                }
            }
        }
    }
}
