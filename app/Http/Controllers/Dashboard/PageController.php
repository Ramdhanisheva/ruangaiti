<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageSectionItem;
use App\Services\PageBuilderService;
use App\Services\ContentImportService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    public function __construct(
        private readonly PageBuilderService $pageBuilderService,
        private readonly ContentImportService $contentImportService
    ) {}

    public function index() {
        $pages = Page::orderBy("id", "DESC")->paginate(20);
        return view("dashboard.page.index", compact("pages"));
    }

    public function status($id) {
        $page = Page::find($id);
        if ($page) {
            $page->status = ($page->status === 'Published' || $page->status == '1') ? 'Draft' : 'Published';
            $page->save();
            $alert = ($page->status === 'Published') ? "Page published!" : "Page drafted!";
            return back()->with("success", $alert);
        }
        return back()->withErrors("Page not exists!");
    }

    public function create() {
        return view("dashboard.page.add");
    }

    public function store(Request $request) {
        $validated = $request->validate([
            "title" => ["required", "string", "max:255"],
            "slug" => ["required", "string", "max:255", "unique:pages,slug"],
            "content" => ["required", "string"],
            "status" => ["required", Rule::in(["0", "1", "Published", "Draft", "Archived"])],
            "template" => ["nullable", "string", "in:default,landing,company,blank"],
            "seo_title" => ["nullable", "string", "max:255"],
            "seo_description" => ["nullable", "string"],
            "json_ld" => ["nullable", "string"],
        ]);

        $validated["content"] = Purifier::clean($validated["content"]);

        // Normalize status
        if ($validated["status"] === '1') $validated["status"] = 'Published';
        if ($validated["status"] === '0') $validated["status"] = 'Draft';

        $page = Page::create($validated);

        // Pre-populate default sections if layout template chosen
        if ($page->template === 'landing' || $page->template === 'company') {
            $this->generateTemplateSections($page);
        }

        // Record revision
        $this->pageBuilderService->recordRevision('App\\Models\\Page', $page->id, $page->toArray());

        return redirect()->route("dashboard.pages.edit", $page->id)->with("success", "Page created!");
    }

    public function edit(string $id) {
        $page = Page::find($id);
        if ($page) {
            $page->load('sections.items');
            $revisions = DB::table('content_revisions')
                ->where('revisable_type', 'App\\Models\\Page')
                ->where('revisable_id', $page->id)
                ->orderBy('id', 'DESC')
                ->get();
            return view("dashboard.page.edit", compact("page", "revisions"));
        }
        return back()->withErrors("Page not exists!");
    }

    public function update(Request $request, string $id) {
        $page = Page::find($id);
        if ($page) {
            $validated = $request->validate([
                "title" => ["required", "string", "max:255"],
                "slug" => ["required", "string", "max:255", Rule::unique("pages", "slug")->ignore($page->id)],
                "content" => ["required", "string"],
                "status" => ["required", Rule::in(["0", "1", "Published", "Draft", "Archived"])],
                "template" => ["nullable", "string", "in:default,landing,company,blank"],
                "seo_title" => ["nullable", "string", "max:255"],
                "seo_description" => ["nullable", "string"],
                "json_ld" => ["nullable", "string"],
            ]);

            // Normalize status
            if ($validated["status"] === '1') $validated["status"] = 'Published';
            if ($validated["status"] === '0') $validated["status"] = 'Draft';

            $page->title = $validated["title"];
            $page->slug = Str::slug($validated["slug"]);
            $page->content = Purifier::clean($validated["content"]);
            $page->status = $validated["status"];
            $page->template = $validated["template"] ?? 'default';
            $page->seo_title = $validated["seo_title"] ?? null;
            $page->seo_description = $validated["seo_description"] ?? null;
            $page->json_ld = $validated["json_ld"] ?? null;

            if ($page->status === 'Published' && !$page->published_at) {
                $page->published_at = now();
            }

            $page->save();

            // Auto-populate default template sections if layout template chosen and page has no sections
            if ($page->sections()->count() === 0 && ($page->template === 'landing' || $page->template === 'company')) {
                $this->generateTemplateSections($page);
            }

            // Record revision
            $this->pageBuilderService->recordRevision('App\\Models\\Page', $page->id, $page->toArray());

            return redirect()->route("dashboard.pages.edit", $page->id)->with("success", "Page updated!");
        }
        return redirect()->route("dashboard.pages.index")->withErrors("Page not exists!");
    }

    private function generateTemplateSections(Page $page) {
        if ($page->template === 'landing') {
            // Hero
            $secHero = PageSection::create([
                'page_id' => $page->id,
                'type' => 'hero',
                'layout_style' => 'full-width',
                'sort_order' => 1,
                'status' => 'Published'
            ]);
            PageSectionItem::create([
                'page_section_id' => $secHero->id,
                'title' => 'Scale Your Knowledge with RuangAiTi',
                'subtitle' => 'The premium Indonesian technology blogging and roadmap platform.',
                'content' => 'Discover structured roadmaps, high-quality articles, and interactive tools designed to accelerate your engineering career.',
                'image' => '/assets/frontend/img/hero-illustration.svg',
                'link' => '/roadmaps',
                'sort_order' => 0
            ]);

            // Features
            $secFeat = PageSection::create([
                'page_id' => $page->id,
                'type' => 'features',
                'layout_style' => 'container',
                'sort_order' => 2,
                'status' => 'Published'
            ]);
            PageSectionItem::create([
                'page_section_id' => $secFeat->id,
                'title' => 'Structured Roadmaps',
                'subtitle' => 'Step-by-step career path guides',
                'content' => 'Interactive timelines and modules to guide you from beginner to production-ready developer.',
                'image' => '/assets/frontend/img/feature-roadmaps.png',
                'link' => '/roadmaps',
                'sort_order' => 0
            ]);
            PageSectionItem::create([
                'page_section_id' => $secFeat->id,
                'title' => 'Deep-Dive Articles',
                'subtitle' => 'Expert technology blogs',
                'content' => 'Comprehensive guides covering system architecture, database performance, and modern framework patterns.',
                'image' => '/assets/frontend/img/feature-articles.png',
                'link' => '/posts',
                'sort_order' => 1
            ]);
            PageSectionItem::create([
                'page_section_id' => $secFeat->id,
                'title' => 'Interactive Media',
                'subtitle' => 'Visual asset library',
                'content' => 'High-resolution diagrams, video illustrations, and complete codebase code samples.',
                'image' => '/assets/frontend/img/feature-media.png',
                'link' => '/media',
                'sort_order' => 2
            ]);

            // Testimonials
            $secTest = PageSection::create([
                'page_id' => $page->id,
                'type' => 'testimonials',
                'layout_style' => 'container',
                'sort_order' => 3,
                'status' => 'Published'
            ]);
            PageSectionItem::create([
                'page_section_id' => $secTest->id,
                'title' => 'Andi Wijaya',
                'subtitle' => 'Fullstack Engineer at TechCorp',
                'content' => 'RuangAiTi completely changed how I learn new technologies. The roadmaps are incredibly clear and the articles go deep into production issues.',
                'sort_order' => 0
            ]);
            PageSectionItem::create([
                'page_section_id' => $secTest->id,
                'title' => 'Siti Rahma',
                'subtitle' => 'Junior Backend Developer',
                'content' => 'As a self-taught programmer, having structured modules with linked posts allowed me to land my first developer job within months.',
                'sort_order' => 1
            ]);

            // CTA
            $secCta = PageSection::create([
                'page_id' => $page->id,
                'type' => 'cta',
                'layout_style' => 'full-width',
                'sort_order' => 4,
                'status' => 'Published'
            ]);
            PageSectionItem::create([
                'page_section_id' => $secCta->id,
                'title' => 'Ready to Accelerate Your Career?',
                'subtitle' => 'Join thousands of Indonesian developers level-up today.',
                'content' => 'Access all learning roadmaps, bookmark resources, and join our active developer newsletter.',
                'link' => '/register',
                'sort_order' => 0
            ]);
        } elseif ($page->template === 'company') {
            // Hero
            $secHero = PageSection::create([
                'page_id' => $page->id,
                'type' => 'hero',
                'layout_style' => 'container',
                'sort_order' => 1,
                'status' => 'Published'
            ]);
            PageSectionItem::create([
                'page_section_id' => $secHero->id,
                'title' => 'Empowering Indonesian Tech Talent',
                'subtitle' => 'About RuangAiTi Platform',
                'content' => 'We believe quality education and structured guidance should be accessible to every developer in Indonesia.',
                'sort_order' => 0
            ]);

            // Features
            $secFeat = PageSection::create([
                'page_id' => $page->id,
                'type' => 'features',
                'layout_style' => 'container',
                'sort_order' => 2,
                'status' => 'Published'
            ]);
            PageSectionItem::create([
                'page_section_id' => $secFeat->id,
                'title' => 'High Quality',
                'subtitle' => 'Production-grade guides only',
                'content' => "No shallow tutorials. We write in-depth content that covers the 'why' and 'how'.",
                'sort_order' => 0
            ]);
            PageSectionItem::create([
                'page_section_id' => $secFeat->id,
                'title' => 'Structured Paths',
                'subtitle' => 'No tutorial hell',
                'content' => 'Every post fits into a logical progression, ensuring you learn step-by-step.',
                'sort_order' => 1
            ]);
        }
    }

    public function destroy(string $id) {
        $page = Page::find($id);
        if ($page) {
            $page->delete();
            return back()->with("success", "Page deleted!");
        }
        return back()->withErrors("Page not exists!");
    }

    public function trashed() {
        $pages = Page::onlyTrashed()->orderBy("id", "DESC")->paginate(20);
        return view("dashboard.page.trashed", compact("pages"));
    }

    public function restore($id) {
        $page = Page::onlyTrashed()->find($id);
        if ($page) {
            $page->restore();
            return back()->with("success", "Page restored!");
        }
        return back()->withErrors("Page not exists!");
    }

    public function delete($id) {
        $page = Page::onlyTrashed()->find($id);
        if ($page) {
            $page->forceDelete();
            return back()->with("success", "Page deleted!");
        }
        return back()->withErrors("Page not exists!");
    }

    // ── V3 Sections Builder Integration ───────────────────────
    public function addSection(Request $request, Page $page) {
        $request->validate([
            'type' => ['required', 'string', 'max:50'],
            'layout_style' => ['required', 'string', 'max:50'],
        ]);

        $maxSort = PageSection::where('page_id', $page->id)->max('sort_order') ?? 0;

        PageSection::create([
            'page_id' => $page->id,
            'type' => $request->input('type'),
            'layout_style' => $request->input('layout_style'),
            'sort_order' => $maxSort + 1,
            'status' => 'Published',
        ]);

        return back()->with('success', 'Layout section added.');
    }

    public function duplicateSection(PageSection $section) {
        $this->pageBuilderService->duplicateSection($section);
        return back()->with('success', 'Section duplicated.');
    }

    public function deleteSection(PageSection $section) {
        $section->delete();
        return back()->with('success', 'Section removed.');
    }

    public function saveSectionItems(Request $request, PageSection $section) {
        $request->validate([
            'items' => ['required', 'array'],
            'items.*.title' => ['nullable', 'string', 'max:255'],
            'items.*.subtitle' => ['nullable', 'string', 'max:255'],
            'items.*.content' => ['nullable', 'string'],
            'items.*.image' => ['nullable', 'string', 'max:255'],
            'items.*.link' => ['nullable', 'string', 'max:500'],
        ]);

        $section->items()->delete();

        foreach ($request->input('items') as $index => $itemData) {
            PageSectionItem::create([
                'page_section_id' => $section->id,
                'title' => $itemData['title'] ?? null,
                'subtitle' => $itemData['subtitle'] ?? null,
                'content' => $itemData['content'] ?? null,
                'image' => $itemData['image'] ?? null,
                'link' => $itemData['link'] ?? null,
                'sort_order' => $index,
            ]);
        }

        return back()->with('success', 'Section saved.');
    }

    public function sortSections(Request $request) {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $this->pageBuilderService->reorderSections($request->input('ids'));
        return response()->json(['success' => true]);
    }

    public function duplicatePage(Page $page) {
        $newPage = $this->pageBuilderService->duplicatePage($page);
        return redirect()->route('dashboard.pages.edit', $newPage->id)->with('success', 'Page duplicated!');
    }

    public function restoreRevision(Request $request, Page $page) {
        $request->validate([
            'revision_id' => ['required', 'integer']
        ]);

        $revision = DB::table('content_revisions')
            ->where('revisable_type', 'App\\Models\\Page')
            ->where('revisable_id', $page->id)
            ->where('id', $request->input('revision_id'))
            ->first();

        if ($revision) {
            $data = json_decode($revision->content_data, true);
            $page->title = $data['title'] ?? $page->title;
            $page->slug = $data['slug'] ?? $page->slug;
            $page->content = $data['content'] ?? $page->content;
            $page->status = $data['status'] ?? $page->status;
            $page->template = $data['template'] ?? $page->template;
            $page->seo_title = $data['seo_title'] ?? $page->seo_title;
            $page->seo_description = $data['seo_description'] ?? $page->seo_description;
            $page->json_ld = $data['json_ld'] ?? $page->json_ld;
            $page->save();

            return back()->with('success', 'Revision restored successfully!');
        }

        return back()->withErrors('Revision not found.');
    }

    public function importContent(Request $request) {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'] // 10MB limit
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        try {
            $html = '';
            if ($ext === 'md' || $ext === 'markdown') {
                $html = $this->contentImportService->importMarkdown(file_get_contents($path));
            } elseif ($ext === 'docx') {
                $html = $this->contentImportService->importDocx($path);
            } elseif ($ext === 'pdf') {
                $html = $this->contentImportService->importPdf($path);
            } elseif ($ext === 'html' || $ext === 'htm') {
                $html = $this->contentImportService->importHtml(file_get_contents($path));
            } else {
                return response()->json(['error' => 'Unsupported file format.'], 422);
            }

            // Clean parsed HTML using Purifier before injection
            $html = Purifier::clean($html);

            return response()->json(['html' => $html]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to parse file: ' . $e->getMessage()], 500);
        }
    }
}
