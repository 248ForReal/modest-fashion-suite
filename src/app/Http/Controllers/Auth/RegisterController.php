<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'phone'      => 'required|string|max:20',
            'birth_date' => 'required|date',
        ]);

        session(['register_data' => [
            'name'       => $request->name,
            'phone'      => $request->phone,
            'birth_date' => $request->birth_date,
        ]]);

        return redirect()->route('auth.google.register');
    }
}
