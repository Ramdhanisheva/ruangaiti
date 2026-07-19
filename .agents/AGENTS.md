# RuangAiTi — Agent Rules (AGENTS.md)
# Project-scoped. Applies to every AI agent session on this workspace.

---

## PRIME DIRECTIVE (Rule #1)

> **Existing functionality is the baseline, not the obstacle.**
> Every implementation must begin by enhancing what already exists.
> Replacing or duplicating mature features is considered a **regression** unless explicitly approved.

---

## Golden Rule

> **Never Replace Stable Features.**
> Always **Extend → Integrate → Improve.**
> Never **Rewrite → Duplicate → Downgrade.**

---

## Anti-Duplication Rule (MANDATORY)

Before creating any new module, controller, view, route, or sidebar entry:

1. Determine whether an equivalent already exists in the codebase.
2. If it does — **extend the existing one**.
3. Duplicate modules, menus, controllers, CRUD screens, and parallel workflows are **PROHIBITED** unless explicitly requested by the user.

---

## Versioning Policy

Versioning labels (V3, Enterprise, Beta, Legacy) are for **developers only**.

They must NEVER appear in:
- Sidebar menu names
- Page titles
- Breadcrumbs
- Button labels
- Any user-facing text

Sidebar must use business function names only:
Dashboard, Posts, Roadmaps, Pages, Media, Analytics, Settings

---

## Preserve Existing UX

Before replacing any existing view:
- Compare it with the current implementation.
- If the existing page provides a better UX, richer editing, or more mature workflow — keep it.
- Integrate new features into it.
- Never replace a mature interface with a simplified re-implementation.

---

## Controller Policy

Never create a second controller for an existing feature domain.

WRONG:
  MediaController + MediaLibraryController
  PagesController + PageBuilderController

CORRECT:
  Extend the existing controller.
  Extract shared logic into a Service class.
  Only create a new controller for an entirely new domain.

---

## View Policy

Never duplicate CRUD pages.

WRONG:
  pages/index.blade.php + pages/index_v3.blade.php
  media/index.blade.php + media/index_v3.blade.php

CORRECT:
  Upgrade the existing Blade view.
  Only create new views for completely new workflows (Analytics, Reports, etc).

---

## Database Policy

Before adding a new table or column:
1. Check if the existing schema already supports the feature.
2. Reuse existing relationships whenever possible.
3. Only add new tables if the feature cannot reasonably fit the current schema.
4. Every migration must implement up() and down() without data corruption.
5. Use Schema::hasColumn() guards when adding to existing tables.

---

## Sidebar Policy

The sidebar must never expose internal architecture.
Users must never see: V3, Enterprise, Beta, Legacy.

No new menu may be added to the sidebar until ALL of the following are true:
- Route exists
- Controller implemented
- Blade view completed
- Permissions verified
- Responsive layout verified (360px, 768px, 1024px, 1440px)
- Dark mode verified
- Light mode verified
- HTTP 500 tested

---

## Route Compatibility

Never change existing URLs unless absolutely necessary.
If a route must change: create redirects, preserve bookmarks, preserve SEO.

---

## UI Design Standard

Every new admin page must visually match the existing AdminLTE dashboard.
Do not introduce different spacing, typography, button styles, card patterns, or color palettes.
The admin panel must feel like one unified product.

---

## Final Acceptance Criteria

A feature is considered COMPLETE only when ALL of the following are true:
- No existing functionality removed
- No duplicated modules, controllers, routes, sidebar entries, CRUD pages
- No visual regressions
- No HTTP 500 errors
- Responsive: desktop, tablet, mobile
- Compatible: light mode and dark mode
- Existing URLs, workflows, and editor capabilities preserved
- New features integrated naturally; users can use them without relearning

---

## Platform Context

RuangAiTi blog and learning platform.

V1: Core blog (posts, categories, tags, authors, comments, search). Production-stable. Read-only unless bug fix.
V2: Roadmaps, Pages. Production-stable. Extend-only.
V3: Analytics, Media upgrade, Page Builder, SEO. Active development. Extend only, never replace V1/V2.

Tech stack: Laravel + AdminLTE + Vanilla CSS/JS + Summernote rich editor.
