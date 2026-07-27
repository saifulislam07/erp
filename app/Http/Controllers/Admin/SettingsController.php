<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    private const array KEYS = [
        'company_name',
        'company_address',
        'company_phone',
        'company_email',
        'currency_symbol',
        'low_stock_threshold_default',
        'vat_registration_number',
    ];

    public function index(): View
    {
        $settings = collect(self::KEYS)->mapWithKeys(fn (string $key) => [$key => Setting::get($key)]);
        $companyLogo = Setting::get('company_logo');

        return view('admin.settings.index', compact('settings', 'companyLogo'));
    }

    public function update(SettingsRequest $request): RedirectResponse
    {
        foreach (self::KEYS as $key) {
            Setting::set($key, $request->input($key), 'general', $request->user()->id);
        }

        if ($request->hasFile('company_logo')) {
            Setting::set(
                'company_logo',
                $request->file('company_logo')->store('settings', 'public'),
                'general',
                $request->user()->id,
            );
        }

        return redirect()->route('admin.settings.index')->with('success', 'Settings updated successfully.');
    }
}
