<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\PasswordChangeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(): View
    {
        return view('client.password.change');
    }

    public function update(PasswordChangeRequest $request): RedirectResponse
    {
        Auth::guard('client')->user()->update([
            'password' => $request->validated('new_password'),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }
}
