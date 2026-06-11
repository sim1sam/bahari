<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Services\SiteSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function __construct(private SiteSettingsService $settings) {}

    public function subscribe(Request $request): RedirectResponse
    {
        if (! $this->settings->newsletterEnabled()) {
            return back()->with('error', 'Newsletter subscriptions are currently unavailable.');
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($validated['email']));
        $subscriber = NewsletterSubscriber::query()->firstOrNew(['email' => $email]);

        if ($subscriber->exists && $subscriber->isActive()) {
            return back()->with('success', $this->settings->newsletterSuccessMessage());
        }

        $subscriber->fill([
            'email' => $email,
            'status' => 'active',
            'subscribed_at' => now(),
            'unsubscribed_at' => null,
            'ip_address' => $request->ip(),
        ]);
        $subscriber->save();

        return back()->with('success', $this->settings->newsletterSuccessMessage());
    }
}
