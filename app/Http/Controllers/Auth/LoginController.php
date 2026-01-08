<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Livewire\Attributes\Title;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;



class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'tahun' => 'required|numeric|min:2020|max:' . (date('Y') + 5),
        ]);

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->remember)) {
            // Simpan tahun yang dipilih ke session
            session(['tahun_anggaran' => $request->tahun]);

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->withInput($request->only('email', 'tahun'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('/login');
    }
}
