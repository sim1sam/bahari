<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NewsletterSubscriberController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');

        return view('admin.newsletter.index', [
            'subscribers' => NewsletterSubscriber::query()
                ->search($search)
                ->latest('subscribed_at')
                ->paginate(25)
                ->withQueryString(),
            'search' => $search,
            'activeCount' => NewsletterSubscriber::active()->count(),
        ]);
    }

    public function destroy(NewsletterSubscriber $newsletterSubscriber): RedirectResponse
    {
        $newsletterSubscriber->delete();

        return back()->with('success', 'Subscriber removed.');
    }
}
