Kaitkan MVP Revamp Plan for Linktree Parity (80%)

## 1. Goal & Success Criteria

- **Goal:** Transform Kaitkan's catalog-centric MVP into a link-in-bio experience that delivers at least 80% feature parity with Linktree's free tier while preserving WhatsApp-centric differentiators.
- **Primary KPI:** 70% of pilot merchants publish a live page with ≥3 links within 24 hours of signup.
- **Secondary KPIs:**
  - Average time-to-publish ≤ 15 minutes.
  - ≥ 50% of live pages use at least one shop/product block.
  - Dashboard NPS ≥ 40 from pilot survey.

## 2. Operating Principles

- **Mobile-first:** All dashboard flows must be touch-friendly; public page must load <2s on 3G.
- **Composable entities:** Treat "profile", "link", "product", and "theme" as interchangeable blocks managed through one builder interface.
- **Low-code friendly:** Avoid complex drag drop libraries; rely on sortable lists and toggles.
- **Reusability:** Image processing, analytics, and publish states must be shared between link and product blocks.
- **Launch-fast mindset:** Default to curated options (themes, icons, copy) before building customization UIs.

## 3. Delivery Timeline (23 working days)

| Phase | Days    | Theme                  | Key Outcomes                                           |
| ----- | ------- | ---------------------- | ------------------------------------------------------ |
| 0     | -2 to 0 | Kickoff & Alignment    | Team briefing, scope lock, design references gathered  |
| 1     | 1-5     | Data & Auth Foundation | New tables for profiles/links/themes, OTP auth updated |
| 2     | 6-10    | Dashboard Builder      | Unified link & shop management UI, preview mode        |
| 3     | 11-15   | Public Page & Themes   | Responsive public page with theme selector             |
| 4     | 16-18   | Analytics & QA         | Unified click tracking, dashboard insights, QA plan    |
| 5     | 19-23   | Launch & Enablement    | Beta rollout, documentation, support prep              |

> **Note:** Day counts assume 5.5h focused work/day for a single engineer + 0.5 FTE product designer.

## 4. Phase Breakdown & Task List

### Phase 0 – Kickoff & Alignment (Days -2 to 0)

**Objectives:** Align stakeholders, secure design references, lock scope.
**Tasks:**

1. Stakeholder workshop (CEO, PM, engineer) to agree success metrics and guardrails.
2. Competitive teardown of Linktree and Taplink (1-pager with annotated screenshots).
3. Asset library preparation: download icon pack (Feather/Remix), theme inspiration moodboard in Figma.
4. Finalize component naming conventions and API contract doc (shared Notion page).

**Deliverables:**

- Approved scope doc.
- Reference board.
- Updated ERD including `profiles`, `links`, `themes`, `link_groups`.

### Phase 1 – Data & Auth Foundation (Days 1-5)

**Objectives:** Extend backend to support generalized links and profile metadata while keeping OTP login stable.

| Day | Focus         | Detailed Tasks                                                                                                                                                   | Owner       |
| --- | ------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------- |
| 1   | Data Modeling | Create migrations for `profiles`, `links`, `link_groups`, `themes`; update seeders; adjust relationships (`User` → `Profile`; `Profile` → `Links`/`LinkGroups`). | Backend dev |
| 2   | Services      | Refactor ImageService to handle avatars, link thumbnails, product photos. Introduce `LinkType` enum (general, product, social, shop_collection).                 | Backend dev |
| 3   | Auth Flow     | Extend OTP onboarding to collect display name, username, avatar placeholder; create post-OTP wizard state (draft profile).                                       | Backend dev |
| 4   | API Endpoints | Build CRUD endpoints for profile + links + groups (REST). Implement reorder endpoint (`PATCH /links/reorder`).                                                   | Backend dev |
| 5   | Theme API     | Seed 3 curated themes, expose `GET /themes` endpoint, add `theme_id` to profile. Unit tests for serialization, rate limits.                                      | Backend dev |

**Exit Criteria:** Postman collection passes, migrations run cleanly, updated ERD committed, API doc updated.

### Phase 2 – Dashboard Builder (Days 6-10)

**Objectives:** Replace product-only dashboard with a builder UI covering profile, links, and shop blocks.

| Day | Focus             | Detailed Tasks                                                                                                                                       | Owner        |
| --- | ----------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- | ------------ |
| 6   | UX Structure      | Update React router to "Builder" layout with tabs: Profile, Links, Shop, Analytics (stub). Implement skeleton states.                                | Frontend dev |
| 7   | Profile Tab       | Forms for avatar upload (image crop modal), display name, bio, social handles (WhatsApp, Instagram, TikTok, YouTube). Live preview column.           | Frontend dev |
| 8   | Links Tab         | List of link cards with reorder (react-beautiful-dnd or SortableJS), toggle visibility, link type selector (General/Social/Shop). Inline validation. | Frontend dev |
| 9   | Shop Tab          | Reuse existing product CRUD in card layout; allow toggling product into public shop section, limit 50 items, highlight stock status.                 | Frontend dev |
| 10  | Preview & Publish | Create preview panel with theme selection, "Publish" toggle (draft/live), copy link button, share modal. Connect to backend.                         | Fullstack    |

**Exit Criteria:** Builder usable on mobile viewport; e2e test (Cypress) for create profile + add link + publish.

### Phase 3 – Public Page & Themes (Days 11-15)

**Objectives:** Deliver Linktree-like public page with theme support and social icons.

| Day | Focus         | Detailed Tasks                                                                                                                               | Owner        |
| --- | ------------- | -------------------------------------------------------------------------------------------------------------------------------------------- | ------------ |
| 11  | Public Layout | Build Next.js public page route `/u/:username` (or Laravel blade) with sections: hero (avatar, bio), social icons row, link list, shop grid. | Frontend dev |
| 12  | Theme System  | Implement theme tokens (background, button style, font); apply to both public page and dashboard preview; support dark/light detection.      | Frontend dev |
| 13  | Performance   | Optimize images via `next/image` or Glide transforms; add skeleton loading; ensure LCP <2s on 3G (Lighthouse test).                          | Frontend dev |
| 14  | SEO & Sharing | Add meta tags, OG image generator (via dynamic route), `robots.txt`, analytics script injection.                                             | Frontend dev |
| 15  | Accessibility | Audit for keyboard navigation, contrast ratios; add focus states and ARIA labels.                                                            | Frontend dev |

**Exit Criteria:** Lighthouse score ≥ 85 mobile, QA pass on top 3 Android devices, social share renders correctly.

### Phase 4 – Analytics & QA (Days 16-18)

**Objectives:** Extend click tracking across all block types and prepare for beta quality.

| Day | Focus               | Detailed Tasks                                                                                            | Owner        |
| --- | ------------------- | --------------------------------------------------------------------------------------------------------- | ------------ |
| 16  | Tracking            | Update click logging middleware to include `link_id` & `source`. Add aggregated stats queries (7d/30d).   | Backend dev  |
| 17  | Dashboard Analytics | Populate Analytics tab with key metrics (views, clicks, top link/product). Add sparklines or mini charts. | Frontend dev |
| 18  | QA Sprint           | Regression test (API, dashboard, public page). Bug bash with team; triage & fix P0/P1 issues.             | Team         |

**Exit Criteria:** All critical bugs resolved, analytics data visible with sample data.

### Phase 5 – Launch & Enablement (Days 19-23)

**Objectives:** Prepare go-to-market assets, support process, and collect early feedback.

| Day | Focus              | Detailed Tasks                                                                              | Owner |
| --- | ------------------ | ------------------------------------------------------------------------------------------- | ----- |
| 19  | Documentation      | Update onboarding docs, create FAQ, record 2-min Loom walkthrough.                          | PM    |
| 20  | Beta Cohort        | Invite 20 pilot merchants, set up feedback form + Slack/WA group.                           | PM    |
| 21  | Support Ops        | Define escalation matrix, canned responses, status page updates.                            | Ops   |
| 22  | Metrics Review     | Instrument Mixpanel/Amplitude funnel, set up Looker Studio dashboard.                       | Data  |
| 23  | Retro & Next Steps | Conduct retro, backlog future enhancements (custom colors, scheduling links, monetization). | Team  |

**Exit Criteria:** Beta live, documentation accessible, feedback loop active.

## 5. Backlog Details

### 5.1 Data Model Additions

- `profiles` table: `user_id`, `display_name`, `username`, `bio`, `avatar_path`, `theme_id`, `social_handles` (JSON), `is_published`, timestamps.
- `links` table: `profile_id`, `title`, `url`, `type`, `order`, `status`, `cta_label`, `thumbnail_path`, `metadata` (JSON).
- `link_groups` table: optional grouping for links (e.g., "Featured"), with `group_type` (default/shop) and `order`.
- `themes` table: `name`, `slug`, `palette` (JSON), `is_pro` boolean for future monetization.
- Update `products` to include `is_visible_in_shop` boolean, `display_order`.

### 5.2 API Surface

```
POST /api/profile/onboarding
GET  /api/profile
PATCH /api/profile
POST /api/profile/avatar
GET  /api/themes
POST /api/links
PATCH /api/links/{id}
PATCH /api/links/reorder
DELETE /api/links/{id}
POST /api/products/{id}/toggle-shop
GET  /api/analytics/summary
```

All endpoints secured with Sanctum; implement request validation + rate limiting (3/min per IP for OTP endpoints).

### 5.3 Frontend Components

- `ProfileForm`, `AvatarUploader`, `SocialHandleInput`, `ThemeSelector`.
- `LinkCard`, `LinkTypeBadge`, `VisibilityToggle`, `ReorderHandle`.
- `ShopProductCard`, `InventoryBadge`, `ShopVisibilityToggle`.
- `PreviewPanel` with breakpoints (mobile preview default, desktop toggle).
- `AnalyticsSummaryCard`, `StatSparkline`.

### 5.4 QA Checklist

- OTP flow handles invalid codes, expired codes, resend throttle.
- Builder validation prevents duplicate usernames and invalid URLs.
- Public page tested on Chrome Android, Safari iOS, Firefox desktop.
- Accessibility: axe scan, keyboard navigation across link list.
- Performance: WebPageTest 3G fast; ensure assets < 300KB initial.

## 6. Dependencies & Risks

- **Dependencies:** Zenziva OTP service SLA, S3/Wasabi bucket for images, CDN configuration for static assets.
- **Risks & Mitigations:**
  - _OTP latency_ → Implement fallback to WhatsApp OTP if SMS fails after 3 retries.
  - _Theme complexity_ → Limit to 3 curated themes; treat advanced customization as PRO backlog.
  - _Single dev bandwidth_ → Timebox features, enforce daily standups with async updates.
  - _Image storage costs_ → Compress images to <200KB using Intervention Image presets.

## 7. Prompt Library (for AI-assisted work)

### 7.1 Product Requirement Prompt

```
You are a senior product manager helping Kaitkan build an 80% Linktree-equivalent MVP focused on Indonesian SMEs. Produce a concise PRD for the "Link Builder" module covering problem statement, goals, personas, detailed user stories, success metrics, and open questions. Reference features such as profile customization, link grouping, shop section, analytics snapshot, and WhatsApp CTA. Tone: pragmatic, data-driven, Bahasa Indonesia.
```

### 7.2 UX Copy Prompt

```
Act as a UX copywriter crafting Bahasa Indonesia microcopy for a link-in-bio builder. Provide copy for: onboarding wizard steps, empty states for Links and Shop tabs, error messages for invalid URL/username, and success toast for publishing. Keep voice friendly, 2nd-person, and highlight WhatsApp-first advantage.
```

### 7.3 UI Design Prompt

```
You are a product designer using Figma. Outline a mobile-first UI concept for Kaitkan's Link Builder dashboard. Include layout descriptions for Profile tab, Links list with reorder, Shop tab using card grids, and live preview column. Suggest color palettes for three curated themes inspired by Indonesian small businesses. Deliverable: structured bullet list with frame names, key components, spacing guidelines.
```

### 7.4 Engineering Plan Prompt

```
You are a senior Laravel + React engineer. Break down the implementation of Kaitkan's generalized link builder, covering backend (migrations, models, controllers), frontend (React components, state management, API hooks), and deployment (env vars, asset builds). Include testing strategy with PHPUnit and Cypress. Output as step-by-step checklist with estimated hours.
```

### 7.5 QA Test Plan Prompt

```
Assume the role of a QA lead preparing tests for Kaitkan's Linktree-style MVP. Generate a test plan covering functional scenarios, cross-device checks, accessibility, performance, and analytics validation. Provide priority labels (P0/P1/P2) and specify manual vs automated coverage. Format as tables in Markdown.
```

### 7.6 Marketing Launch Prompt

```
You are a growth marketer launching Kaitkan's new Link-in-bio experience. Create a launch kit: announcement email (Bahasa Indonesia), WhatsApp broadcast script, landing page hero copy, and three social media captions. Emphasize speed to publish, integrated shop, and Indonesian localization.
```

## 8. Communication Cadence

- Daily async standup in Slack (#build-linktree) with template: Yesterday / Today / Blockers.
- Twice-weekly 30-min sync (Mon & Thu) for demo + risk review.
- Friday demo recordings uploaded to shared drive.
- Shared Notion dashboard with burndown chart, bug tracker.

## 9. Definition of Done Checklist

- Feature documented in Notion, demo recorded.
- Acceptance criteria met and validated in staging.
- Tests (unit + e2e) passing in CI.
- Translation strings reviewed (Bahasa/English).
- Analytics events instrumented and verified.
- Feature flag toggled for beta cohort.

## 10. Post-Launch Follow-ups

- Monitor funnel: signups → published pages → active pages (7d).
- Schedule user interviews with 5 pilot merchants within first week.
- Capture backlog ideas: scheduling links, commerce checkout integration, custom domains, advanced analytics.
