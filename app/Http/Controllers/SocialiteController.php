<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ClienteFacebook;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback(Request $request)
    {
        $accessToken = $request->input('accessToken');
        $facebookId = $request->input('userID');

        // Esegui l'autenticazione
        $facebookUser = Socialite::driver('facebook')->userFromToken($accessToken);

        $cliente = Auth::user()->clientiAssociati()->first(); // Assumendo che l'utente sia un cliente

        $clienteFacebook = ClienteFacebook::updateOrCreate(
            ['id_cliente' => $cliente->id],
            [
                'facebook_id' => $facebookId,
                'token' => $accessToken,
                'refresh_token' => $facebookUser->refreshToken ?? null,
            ]
        );

        return response()->json(['message' => 'Connected to Facebook successfully!'], 200);
    }
}
