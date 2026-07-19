<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageSection;
use App\Models\PageSectionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PageBuilderService
{
    /**
     * Duplicate a page along with all sections and section items.
     */
    public function duplicatePage(Page $page): Page
    {
        return DB::transaction(function () use ($page) {
            $newPage = $page->replicate();
            $newPage->title = $page->title . ' (Copy)';
            $newPage->slug = $page->slug . '-copy-' . time();
            $newPage->status = 'Draft';
            $newPage->save();

            // Duplicate sections
            foreach ($page->sections as $section) {
                $newSection = $section->replicate();
                $newSection->page_id = $newPage->id;
                $newSection->save();

                // Duplicate section items
                foreach ($section->items as $item) {
                    $newItem = $item->replicate();
                    $newItem->page_section_id = $newSection->id;
                    $newItem->save();
                }
            }

            return $newPage;
        });
    }

    /**
     * Duplicate a specific section within the same page.
     */
    public function duplicateSection(PageSection $section): PageSection
    {
        return DB::transaction(function () use ($section) {
            $newSection = $section->replicate();
            $newSection->sort_order = $section->sort_order + 1;
            $newSection->save();

            // Increment sort_order of subsequent sections
            PageSection::where('page_id', $section->page_id)
                ->where('id', '!=', $newSection->id)
                ->where('sort_order', '>=', $newSection->sort_order)
                ->increment('sort_order');

            // Duplicate items
            foreach ($section->items as $item) {
                $newItem = $item->replicate();
                $newItem->page_section_id = $newSection->id;
                $newItem->save();
            }

            return $newSection;
        });
    }

    /**
     * Update order of page sections.
     */
    public function reorderSections(array $sectionIds): void
    {
        DB::transaction(function () use ($sectionIds) {
            foreach ($sectionIds as $index => $id) {
                PageSection::where('id', $id)->update(['sort_order' => $index]);
            }
        });
    }

    /**
     * Record a content revision, keeping only the last 5 revisions.
     */
    public function recordRevision(string $type, int $id, array $data): void
    {
        DB::transaction(function () use ($type, $id, $data) {
            DB::table('content_revisions')->insert([
                'revisable_type' => $type,
                'revisable_id' => $id,
                'user_id' => Auth::id(),
                'content_data' => json_encode($data),
                'created_at' => now(),
            ]);

            // Query existing revisions count
            $revisions = DB::table('content_revisions')
                ->where('revisable_type', $type)
                ->where('revisable_id', $id)
                ->orderBy('id', 'DESC')
                ->get();

            // If more than 5, delete the oldest
            if ($revisions->count() > 5) {
                $idsToDelete = $revisions->slice(5)->pluck('id');
                DB::table('content_revisions')->whereIn('id', $idsToDelete)->delete();
            }
        });
    }
}
