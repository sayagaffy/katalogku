# Kaitkan × Linktree Capability Alignment & Revamp Plan

## 1. Executive Summary

- **Objective:** Ensure Kaitkan's 3-week MVP revamp delivers ~80% parity with Linktree's current free-tier experience while preserving our WhatsApp-first differentiator and lightweight catalog tooling.
- **Key Findings:** Linktree's strength lies in flexible link blocks, curated themes, profile polish, lightweight shop collections, and surface-level analytics. Kaitkan's Laravel + Vue stack already covers authentication, product CRUD, and a WhatsApp CTA but lacks generalized link management, theme presets, and a polished profile surface.
- **Outcome:** The plan below maps every critical Linktree capability to Kaitkan's current codebase (`kaitkan-api` for Laravel backend, `kaitkan-frontend` for Vue 3 + Pinia dashboard/public app) and outlines a sequenced delivery path with QA and GTM guardrails.

---

## 2. Linktree Capability Inventory (Free Tier, Jan 2025)

| Capability Area              | What Linktree Provides Today                                                                                                                                                                                                                        |
| ---------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Profile & Identity**       | Avatar upload, display name, short bio, location/emoji badges, highlight text, social icons (Instagram, TikTok, YouTube, WhatsApp, Email), header layout toggle (text vs logo).                                                                     |
| **Link Blocks**              | Unlimited standard links with title, URL, optional thumbnail, pin/reorder, temporary hide, scheduling (pro), custom icons, QR code share, per-link analytics (views & CTR).                                                                         |
| **Shop & Monetization**      | "Shop" tab with cards that can aggregate products (manual entry, Shopify sync, or digital files). Collections allow grouping; each product card supports image, price, CTA. Free tier allows manual shop cards; commerce checkout requires upgrade. |
| **Appearance & Themes**      | 15+ curated themes, text style presets, background patterns, button shape toggles, accent colors, animated backgrounds (pro). Users can preview theme on the right panel before publishing.                                                         |
| **Audience & Analytics**     | Dashboard home shows lifetime views, clicks, CTR, top-performing link, device breakdown (free limited to last 7/28 days). Export CSV and advanced analytics are pro.                                                                                |
| **Utilities & Distribution** | Share modal (copy link, QR code, download poster, add to bio instructions). Public page SEO (meta tags, OG image). Integrations for Meta pixel, Google Analytics (pro).                                                                             |
| **Account & Collaboration**  | Basic settings (username, email, notifications), team access (pro), custom domains (pro), support center.                                                                                                                                           |

---

## 3. Kaitkan MVP Baseline (GitHub Audit)

| Domain                                            | What Already Exists (per `kaitkan-api` & `kaitkan-frontend`)                                                                                                                   |
| ------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Backend (Laravel 11)**                          | OTP via WhatsApp/SMS, Sanctum auth, models for `Catalog`, `Product`, `Click`. Product CRUD, image compression (Intervention Image), analytics for product clicks.              |
| **Frontend Dashboard (Vue 3 + Pinia + Tailwind)** | OTP onboarding flow, single catalog management (list/create/edit product up to 50 items), WhatsApp CTA configuration, analytics overview (product clicks).                     |
| **Public Page**                                   | Simple storefront page listing products with CTA buttons linking to WhatsApp. Basic theme = neutral background, limited typography control.                                    |
| **Tooling & DevOps**                              | Vite build pipeline, ESLint/Prettier, Laravel storage, seeders, Postman collection (per docs). No Next.js usage; public page served via Vue SPA + static hosting behind Nginx. |

> ✅ Repo alignment check: `kaitkan-api/` contains Laravel migrations/controllers; `kaitkan-frontend/` is a Vue 3 Vite project. No additional framework migrations needed—plan assumes enhancing these existing codebases.

---

## 4. Gap Analysis (Kaitkan vs Linktree)

| Linktree Capability                         | Kaitkan Status   | Gap Description                                                      | Action Owner                                                               |
| ------------------------------------------- | ---------------- | -------------------------------------------------------------------- | -------------------------------------------------------------------------- |
| Avatar, bio, social handles                 | ❌ Not available | Users only set store name; no profile metadata or social icons.      | Backend: add `profiles` table & API. Frontend: profile settings view.      |
| Custom link blocks                          | ❌ Not available | Only product entries; cannot add arbitrary URL/link types.           | Full-stack: introduce `links` schema, CRUD, reorder.                       |
| Shop collections                            | ⚠️ Partial       | Product list exists but no grouping/collection toggle.               | Frontend: allow shop grouping toggle, display as separate block.           |
| Theme presets & typography                  | ❌ Not available | Static theme only; no curated options or preview.                    | Backend: seed `themes`; Frontend: theme selector + live preview.           |
| Per-link analytics                          | ❌ Not available | Clicks tracked per product only.                                     | Backend: expand click logging to generic links; add analytics summary API. |
| Share modal (copy, QR)                      | ❌ Not available | Only direct URL share.                                               | Frontend: share modal; optional QR generator library.                      |
| Publish/draft state                         | ⚠️ Partial       | Products go live immediately; no draft toggle for profile/page.      | Backend: `is_published` flag; Frontend: publish toggle + preview.          |
| Public page layout (profile + links + shop) | ⚠️ Partial       | Lacks profile header, social row, theme-based styling.               | Frontend: restructure public page composition.                             |
| Analytics dashboard                         | ⚠️ Partial       | Product-level metrics only; lacks summary cards for total views/CTR. | Full-stack: unify analytics for links & products.                          |
| Supportive assets (docs, prompts, QA)       | ⚠️ Partial       | Current docs focus on catalog MVP. Need Linktree-specific guidance.  | PM/Design/QA: update docs & scripts.                                       |

Legend: ✅ Existing, ⚠️ Partial, ❌ Missing.

---

## 5. Implementation Blueprint

### 5.1 Backend (`kaitkan-api`, Laravel)

1. **Data Model Extensions**
   - Create `profiles` table (`user_id`, `display_name`, `username`, `bio`, `avatar_path`, `theme_id`, `social_handles` JSON, `is_published`, timestamps).
   - Create `links` table (`profile_id`, `title`, `url`, `type` enum: `general`, `social`, `product`, `collection`, `cta_whatsapp`; `order`, `status`, `cta_label`, `thumbnail_path`, `starts_at`, `ends_at`).
   - Add `collections` table (optional) or reuse existing `Catalog` as `Shop` grouping with `is_shop_enabled` flag.
2. **Services & Controllers**
   - Extend ImageService to support avatar + link thumbnail resizing (square & 3:2).
   - Build ProfileController (`show`, `update`, `uploadAvatar`, `publishToggle`).
   - Build LinkController (`index`, `store`, `update`, `destroy`, `reorder`, `toggleVisibility`).
   - Update ProductController to expose `toggleShopVisibility` and `assignCollection` endpoints to reuse existing logic.
3. **Analytics & Events**
   - Refactor click logging middleware to accept `link_id` or `product_id` with source metadata (button vs share).
   - Create `AnalyticsController@summary` returning totals (views, clicks, CTR, top link/product, device breakdown using user agent heuristics).
4. **Theme & Settings**
   - Seed `themes` table with 3 curated presets (e.g., `Batik`, `Minimal`, `Neon`).
   - Provide `GET /themes` endpoint with palette tokens (background, button, text, accent).
5. **Security & Validation**
   - Rate limit link creation (50 active) to prevent spam.
   - Validate URLs, enforce unique usernames, sanitize bios (strip HTML).

### 5.2 Frontend (`kaitkan-frontend`, Vue 3)

1. **State Management**
   - Add Pinia stores: `useProfileStore`, `useLinksStore`, reuse `useProductsStore` with `isShopEnabled` flag.
   - Centralize publish status & theme selection in stores for reuse across dashboard and public page.
2. **Dashboard Builder Revamp**
   - Replace product-only view with tabs: **Profile**, **Links**, **Shop**, **Analytics**.
   - Profile tab: avatar uploader (uses existing image API), display name, username availability checker, bio (with counter), social handle inputs (WhatsApp, Instagram, TikTok, YouTube, Email, Shopee/Tokopedia optional).
   - Links tab: draggable list using `vue-draggable-next`; cards show icon, title, URL, visibility toggle, schedule badge (future), analytics snippet.
   - Shop tab: reuse existing product CRUD; add toggle to include in public Shop section + assign to collection.
   - Analytics tab: sparkline charts (use `vue-chartjs` lightweight) summarizing last 7/30 days.
   - Floating preview column replicating Linktree layout with live theme preview + publish toggle + "Copy link" and "Share" modal (QR via `qrcode` npm package, download poster stub).
3. **Public Page Refresh**
   - Layout: header (avatar, display name, bio, social icon buttons), link list (buttons styled per theme), shop grid (cards with image, price, CTA), footer with Kaitkan branding (removable for PRO later).
   - Theme application using CSS variables set from theme palette; allow toggling between backgrounds.
   - Add meta tags & dynamic OG image route (calls backend to render simple OG card from profile data or uses Cloudinary-style template).
4. **Routing & Guards**
   - Dashboard routes: ensure onboarding wizard prompts completion of profile before unlocking links (Pinia guard + router beforeEnter).
   - Public route: `/:username` served from same SPA with SSR-style prefetch (use async route component + skeleton states).
5. **UX Polish**
   - Empty states with friendly copy (Bahasa Indonesia) for Profile/Links/Shop.
   - Inline validation & success toasts (reuse existing global toast).
   - Accessibility: focus states, keyboard reorder fallback (Move up/down buttons).

### 5.3 Shared & Ops

- Update documentation (`project_setup_guide.md`, `frontend_architecture.txt`) to reflect new modules.
- Prepare Figma kit / asset references (icons, theme swatches) and ensure stored in shared drive.
- Introduce feature flags (config-driven) for `link-builder` to allow staged rollout.
- Update CI to run new PHPUnit tests + Cypress (if available) or Playwright alternative for Vue.

---

## 6. Delivery Timeline (15 working days ≈ 3 weeks)

| Phase                               | Days    | Focus                                                         | Key Deliverables                                                             |
| ----------------------------------- | ------- | ------------------------------------------------------------- | ---------------------------------------------------------------------------- |
| **0. Discovery & Alignment**        | -2 to 0 | Competitive teardown, finalize ERD updates, design references | Capability report (this doc), approved scope, Figma moodboard                |
| **1. Data & Onboarding Foundation** | 1-5     | Backend migrations, Profile API, onboarding wizard extension  | `profiles`, `links`, `themes` migrations; onboarding wizard; Postman updates |
| **2. Dashboard Builder**            | 6-10    | Vue tabs, link management UI, preview/publish, theme selector | Functional builder with Profile/Links/Shop tabs, live preview                |
| **3. Public Page & Themes**         | 11-13   | Responsive page refresh, theme binding, social icons          | New public page at `/:username`, theme tokens, SEO meta                      |
| **4. Analytics & Share Utilities**  | 14-15   | Analytics summary, per-link metrics, share modal, QA          | Analytics tab, share modal (copy + QR), regression suite                     |
| **Buffer/Polish**                   | 16-17   | Bug fixes, documentation, beta enablement                     | Release notes, support scripts, beta cohort onboarding                       |

Assumes 1 full-stack engineer + 0.5 designer. If capacity drops, prioritize Phases 1-3 before analytics & share extras.

---

## 7. QA & Launch Checklist

- **Functional**: OTP onboarding, profile update, create/edit/delete link, reorder, toggle visibility, publish/unpublish, shop card toggle, share modal copy/QR.
- **Visual**: Theme color accuracy (mobile + desktop), avatar cropping, social icons (6 major platforms), button states.
- **Performance**: Public page LCP < 2.5s on 3G (use Lighthouse), bundle size < 250KB initial JS (code-split builder tab components).
- **Accessibility**: Keyboard navigation through link list, aria-labels for social buttons, contrast ratio ≥ 4.5.
- **Analytics Validation**: Compare click counts between backend DB and UI for sample data; ensure CTR calculation accurate; confirm analytics resets when toggling draft.
- **Launch Ops**: Update FAQ, create support macros, configure monitoring (uptime, error logs), schedule beta check-ins.

---

## 8. Post-Launch Enhancements (Backlog)

1. **Scheduling & Expiring Links** – store `starts_at`/`ends_at` fields; hide automatically when out of range.
2. **Advanced Themes** – custom color picker, background gradients, custom fonts (PRO upsell).
3. **Commerce Integrations** – embed payment buttons, integrate with Midtrans/Xendit for direct checkout.
4. **Deep Analytics** – device/location breakdown, UTM tracking, export CSV, integrate Meta Pixel.
5. **Collaboration & Access** – shared workspace roles, admin dashboard, audit logs.

---

## 9. Recommended Next Actions

1. Circulate this alignment report to leadership for sign-off (scope freeze by EOD Day 0).
2. Update GitHub project boards (`kaitkan-api`, `kaitkan-frontend`) with Phase 1 issues referencing schema & UI tasks.
3. Schedule design jam to finalize theme palettes and profile header layout before Phase 2 starts.
4. Lock analytics instrumentation plan (event names, payload) before coding Phase 4 to avoid rework.
5. Prepare marketing copy and support scripts parallel to Phase 3 so launch assets are ready when build stabilizes.

This plan replaces the previous catalog-focused roadmap; it centers development on Linktree-level parity while respecting Kaitkan's current codebase and resources.

---

## 10. Platform Integration Addendum (Pre-Launch)

To align with phone-first auth, Firebase, and Cloudflare R2, we introduce a dedicated integration phase to run in parallel with feature work and finish before production.

### 10.1 Scope & Decisions

- Auth: Phone-first with OTP for registration/changes and daily login via 6-digit PIN (no mandatory email). Keep Sanctum; add Firebase Custom Token for FE services.
- Data: Keep domain data in MySQL (profiles/links/products/clicks); move preferences (language, notifications, theme_id) to Firestore.
- Media: Move all images to Cloudflare R2 via S3 driver with CDN.

### 10.2 Deliverables

- R2 disk configured; ImageService reads `IMAGE_DISK` and builds CDN URLs.
- DB migration for `users.pin_hash` and `users.firebase_uid`.
- Endpoints: `POST /auth/set-pin`, `POST /auth/login-pin`, `POST /auth/firebase-token`.
- Firestore preferences sync in `/profile` (GET merges, PATCH updates MySQL + Firestore).
- Optional: Background image upload for public profile with overlay.

### 10.3 Timeline Inserts (4–8 days total)

- A: R2 wiring (1–2d)
- B: PIN auth (2–3d)
- C: Firebase Custom Token + Firestore preferences (2–3d)
- D: Background theme (1–2d, optional)

### 10.4 Risks & Mitigations

- Dual auth complexity → Start Sanctum-first; Firebase for FE only.
- R2 latency/cost → Compress + cache; monitor egress.
- Consistency (Firestore vs MySQL) → Limit to preferences; shadow write.

### 10.5 Dependencies

- Firebase Admin SDK credentials, Firestore rules
- Cloudflare R2 S3 credentials & CDN domain
- WebSMS token (current provider)

For full technical details and step-by-step checklist, see `FIREBASE_R2_MIGRATION_PLAN.md`.
