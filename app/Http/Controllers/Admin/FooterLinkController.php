<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FooterLink;
use App\Services\HomepageContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FooterLinkController extends Controller
{
    public function __construct(private HomepageContentService $homepage) {}

    public function index(): View
    {
        return view('admin.homepage.footer-links.index', [
            'links' => FooterLink::orderBy('group')->orderBy('sort_order')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.homepage.footer-links.form', ['link' => new FooterLink]);
    }

    public function store(Request $request): RedirectResponse
    {
        FooterLink::create($this->validated($request));
        $this->homepage->clearCache();

        return redirect()->route('admin.homepage.footer-links.index')->with('success', 'Footer link created.');
    }

    public function edit(FooterLink $footerLink): View
    {
        return view('admin.homepage.footer-links.form', ['link' => $footerLink]);
    }

    public function update(Request $request, FooterLink $footerLink): RedirectResponse
    {
        $footerLink->update($this->validated($request));
        $this->homepage->clearCache();

        return redirect()->route('admin.homepage.footer-links.index')->with('success', 'Footer link updated.');
    }

    public function destroy(FooterLink $footerLink): RedirectResponse
    {
        $footerLink->delete();
        $this->homepage->clearCache();

        return redirect()->route('admin.homepage.footer-links.index')->with('success', 'Footer link deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'group' => ['required', Rule::in(array_keys(FooterLink::GROUPS))],
            'label' => 'required|string|max:100',
            'url' => 'required|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
