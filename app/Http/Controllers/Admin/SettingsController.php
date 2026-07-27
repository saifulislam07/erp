<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsRequest;
use App\Mail\TestMail;
use App\Models\Setting;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class SettingsController extends Controller
{
    public function __construct(private readonly MediaService $media) {}

    /**
     * Company identity and operational defaults.
     */
    private const GENERAL_KEYS = [
        'company_name',
        'company_address',
        'company_phone',
        'company_email',
        'currency_symbol',
        'low_stock_threshold_default',
        'vat_registration_number',
        'invoice_footer_note',
    ];

    /**
     * Outgoing mail. Stored in settings rather than .env so an administrator
     * can change the mail server without a deploy; MailSettings applies them
     * to the runtime config on boot.
     */
    private const MAIL_KEYS = [
        'mail_mailer',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
    ];

    /**
     * Which automatic emails go out.
     */
    private const NOTIFICATION_KEYS = [
        'mail_send_sale_invoice',
        'mail_send_order_updates',
        'mail_send_password_reset',
    ];

    public function index(): View
    {
        $keys = array_merge(self::GENERAL_KEYS, self::MAIL_KEYS, self::NOTIFICATION_KEYS);

        return view('admin.settings.index', [
            'settings' => collect($keys)->mapWithKeys(fn (string $key) => [$key => Setting::get($key)]),
            'companyLogo' => Setting::get('company_logo'),
            // Never rendered back into the form; the field stays blank and only
            // overwrites the stored password when the user types a new one.
            'hasMailPassword' => filled(Setting::get('mail_password')),
        ]);
    }

    public function update(SettingsRequest $request): RedirectResponse
    {
        $userId = $request->user()->id;

        foreach (self::GENERAL_KEYS as $key) {
            Setting::set($key, $request->input($key), 'general', $userId);
        }

        foreach (self::MAIL_KEYS as $key) {
            Setting::set($key, $request->input($key), 'mail', $userId);
        }

        foreach (self::NOTIFICATION_KEYS as $key) {
            Setting::set($key, $request->boolean($key) ? '1' : '0', 'mail', $userId);
        }

        // Blank means "keep what is stored", so an administrator editing other
        // settings does not have to retype the SMTP password every time.
        if (filled($request->input('mail_password'))) {
            Setting::set('mail_password', $request->input('mail_password'), 'mail', $userId);
        }

        if ($request->boolean('clear_mail_password')) {
            Setting::set('mail_password', null, 'mail', $userId);
        }

        if ($request->hasFile('company_logo')) {
            $this->media->delete(Setting::get('company_logo'));

            Setting::set(
                'company_logo',
                $this->media->storeImage($request->file('company_logo'), 'branding'),
                'general',
                $userId,
            );
        }

        if ($request->boolean('remove_company_logo')) {
            $this->media->delete(Setting::get('company_logo'));
            Setting::set('company_logo', null, 'general', $userId);
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Settings saved.');
    }

    /**
     * Send a message to the signed-in administrator so the SMTP details can be
     * proved before anyone relies on them.
     */
    public function testMail(Request $request): RedirectResponse
    {
        $request->validate(['test_email' => ['required', 'email']]);

        try {
            Mail::to($request->input('test_email'))->send(new TestMail($request->user()->name));
        } catch (Throwable $exception) {
            return back()->with('error', 'Mail failed: '.$exception->getMessage());
        }

        $sentTo = $request->input('test_email');
        $viaLog = Setting::get('mail_mailer', config('mail.default')) === 'log';

        return back()->with('success', $viaLog
            ? "Mailer is set to “log”, so the test message was written to storage/logs instead of being sent to {$sentTo}."
            : "Test email sent to {$sentTo}.");
    }
}
