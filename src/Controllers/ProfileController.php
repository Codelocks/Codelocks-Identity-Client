<?php

namespace Codelocks\Identity\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ProfileController extends  Controller
{

    public function summary() {
        $host = config('identity.host');
        $user = Auth::user();
        return Http::baseUrl($host)
            ->withHeaders([
                'Authorization'=> 'Bearer '. $user->token
            ])
            ->get('/api/user/summary');
    }

    public function show() {
        $host = config('identity.host');
        $user = Auth::user();
        return Http::baseUrl($host)
            ->withHeaders([
                'Authorization'=> 'Bearer '. $user->token
            ])
            ->get('/api/user/profile');
    }

    public function put(Request $request) {
        $host = config('identity.host');
        $user = Auth::user();
        return Http::baseUrl($host)
            ->withHeaders([
                'Authorization'=> 'Bearer '. $user->token
            ])
            ->put('/api/user/profile', $request->all());
    }
}