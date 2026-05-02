<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    // Vérifier l'email et retourner un token
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->first();
        
        // Générer token unique
        $token = Str::random(60);
        
        // Supprimer anciens tokens pour cet email
        DB::table('password_resets')->where('email', $user->email)->delete();
        
        // Sauvegarder le token
        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now()
        ]);
        
        // Retourner directement le token pour la redirection
        return response()->json([
            'message' => 'Reset token generated successfully',
            'token' => $token,
            'email' => $user->email
        ]);
    }

    // Réinitialiser le mot de passe
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed'
        ]);

        // Vérifier le token
        $resetRecord = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return response()->json(['error' => 'Invalid or expired token'], 400);
        }

        // Vérifier si le token n'a pas expiré (1 heure)
        $tokenExpiry = Carbon::parse($resetRecord->created_at)->addHour();
        if (Carbon::now()->gt($tokenExpiry)) {
            return response()->json(['error' => 'Token has expired'], 400);
        }

        // Mettre à jour le mot de passe
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Supprimer le token après utilisation
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password reset successfully']);
    }

    // Vérifier si un token est valide
    public function checkToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required'
        ]);

        $resetRecord = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord || !Hash::check($request->token, $resetRecord->token)) {
            return response()->json(['valid' => false], 200);
        }

        $tokenExpiry = Carbon::parse($resetRecord->created_at)->addHour();
        if (Carbon::now()->gt($tokenExpiry)) {
            return response()->json(['valid' => false, 'expired' => true], 200);
        }

        return response()->json(['valid' => true]);
    }
}