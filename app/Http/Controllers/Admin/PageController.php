<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FooterLink;
use App\Models\Page;
use App\Services\HomepageContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(private HomepageContentService $homepage) {}

    public function index(): View
    {
        $this->syncFooterLinksToPages();

        return view('admin.pages.index', [
            'pages' => Page::query()->orderBy('sort_order')->orderBy('title')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.pages.form', ['page' => new Page]);
    }

    public function store(Request $request): RedirectResponse
    {
        $page = Page::create($this->validated($request));
        $this->attachFooterLinkForPage($page);
        $this->homepage->clearCache();

        return redirect()->route('admin.pages.index')->with('success', 'Page created. Footer link updated if label matches.');
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.form', ['page' => $page]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $page->update($this->validated($request, $page));
        $this->attachFooterLinkForPage($page->fresh());
        $this->homepage->clearCache();

        return redirect()->route('admin.pages.index')->with('success', 'Page updated.');
    }

    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();
        $this->homepage->clearCache();

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted.');
    }

    private function validated(Request $request, ?Page $page = null): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'slug' => [
                'nullable',
                'string',
                'max:200',
                Rule::unique('pages', 'slug')->ignore($page?->id),
            ],
            'content' => 'nullable|string',
            'meta_description' => 'nullable|string|max:300',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $data['slug'] = filled($data['slug'] ?? null)
            ? Str::slug($data['slug'])
            : Page::makeSlug($data['title'], $page?->id);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    private function attachFooterLinkForPage(Page $page): void
    {
        $link = FooterLink::query()
            ->where(function ($q) use ($page) {
                $q->where('label', $page->title)
                    ->orWhere('page_id', $page->id);
            })
            ->orderByRaw('CASE WHEN `group` = ? THEN 0 ELSE 1 END', [FooterLink::GROUP_SUPPORT])
            ->first();

        if ($link) {
            $link->update([
                'page_id' => $page->id,
                'url' => $page->path(),
                'label' => $link->label ?: $page->title,
            ]);

            return;
        }

        FooterLink::create([
            'group' => FooterLink::GROUP_SUPPORT,
            'label' => $page->title,
            'url' => $page->path(),
            'page_id' => $page->id,
            'sort_order' => (int) $page->sort_order,
            'is_active' => (bool) $page->is_active,
        ]);
    }

    private function syncFooterLinksToPages(): void
    {
        $changed = false;

        foreach (Page::query()->active()->get() as $page) {
            $updated = FooterLink::query()
                ->where('label', $page->title)
                ->where(function ($q) use ($page) {
                    $q->whereNull('page_id')
                        ->orWhere('page_id', '!=', $page->id)
                        ->orWhere('url', '#')
                        ->orWhere('url', '');
                })
                ->update([
                    'page_id' => $page->id,
                    'url' => $page->path(),
                ]);

            if ($updated) {
                $changed = true;
            }
        }

        if ($changed) {
            $this->homepage->clearCache();
        }
    }
}
