<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PasswordChangeRequest;
use App\Http\Requests\Admin\ProfileRequest;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * The signed-in user's own account: details, photo and password.
 *
 * Deliberately ungated — every authenticated user can manage their own record,
 * and nothing here can touch another account.
 */
class ProfileController extends Controller
{
    public function __construct(private readonly MediaService $media) {}

    public function edit(): View
    {
        return view('admin.profile.edit', [
            'user' => Auth::user()->load('department', 'roles'),
        ]);
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        unset($data['avatar'], $data['remove_avatar']);

        if ($request->hasFile('avatar')) {
            $this->media->delete($user->avatar);
            $data['avatar'] = $this->media->storeImage($request->file('avatar'), 'avatars');
        }

        if ($request->boolean('remove_avatar')) {
            $this->media->delete($user->avatar);
            $data['avatar'] = null;
        }

        $user->update($data);

        return redirect()
            ->route('admin.profile.edit')
            ->with('success', 'Your profile has been updated.');
    }

    public function updatePassword(PasswordChangeRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->validated('new_password'),
        ]);

        return redirect()
            ->route('admin.profile.edit')
            ->with('success', 'Your password has been changed.')
            ->with('password_tab', true);
    }
}
