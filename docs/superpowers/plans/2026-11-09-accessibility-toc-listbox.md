# Accessibility TOC Custom Listbox Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Thay dropdown `<select>` native mục lục Accessibility Statement bằng custom listbox accessible đẹp đồng bộ brand ở mobile/tablet (<1024px).

**Architecture:** Giữ sidebar desktop nguyên; vùng `.dg-accessibility-toc-mobile` chuyển từ `<select>` sang `<button aria-haspopup=listbox>` + `<ul role=listbox>`; CSS pill glass sẵn có giữ dáng nút, thêm panel absolute; JS ES module mở/đóng + keyboard + scroll-spy sync.

**Tech Stack:** PHP template thuần, BEM CSS `dg-`, vanilla JS + Motion vanilla API sẵn có, CSS variables token-first.

**Spec:** Design đã duyệt trong chat 2026-11-09 (custom listbox accessible, chỉ <1024px, không đụng desktop).

## Global Constraints

- Không React, không build step, theme chạy shared hosting PHP 8.0+.
- Token-first: mọi màu/spacing qua `var()`, token mới scoped `--a11y-toc-*` trong `style.css :root`.
- Chỉ đổi ở `@media (max-width: 1023px)` và `(max-width: 640px)`; desktop ≥1024px giữ nguyên.
- Mọi output PHP escape (`esc_attr`, `esc_html`), i18n `dragon-glow`.
- Không commit git; bump `DG_VERSION` 1.16.45 → 1.16.46.
- Tôn trọng `prefers-reduced-motion` (scroll `auto`, transition `none`).

## Review Focus

- Không-JS: `<noscript>` fallback danh sách link thường vẫn điều hướng được.
- Panel dài 10 mục ở màn 320px: `max-height: 60vh` + scroll trong, không tràn ngang.
- `aria-expanded` đồng bộ trạng thái mở/đóng cho screen reader.
- Focus trap nhẹ: `Escape` đóng + trả focus về nút.
- Scroll-spy đổi nhãn nút khi cuộn qua section mà panel đang đóng.

---

### Task 1: PHP — markup custom listbox + noscript fallback

**Files:**
- Modify: `wp-content/themes/dragon-glow/page-templates/template-accessibility-statement.php:36-50`

**Interfaces:**
- Consumes: 10 anchor `#section-1..10` (giữ nguyên id đích, scroll-spy desktop dùng `href="#id"`).
- Produces: `.dg-accessibility-toc-mobile > button#dg-a11y-toc-btn[aria-haspopup=listbox][aria-expanded][aria-controls]` + `ul#dg-a11y-toc-list[role=listbox]` với 10 `li[role=option] > a[href=#section-N]` + `<noscript>` list.

- [ ] **Step 1: Sửa markup trong `template-accessibility-statement.php`** — thay block `<select>` + icon bằng button + listbox + noscript (giữ class `dg-accessibility-toc-mobile`, thêm `data-dg-toc`). Nhãn button mặc định "1. Our commitment".
- [ ] **Step 2: `php -l` file template** — Expected: `No syntax errors detected`.

### Task 2: CSS — nút pill giữ dáng, panel glass + item active

**Files:**
- Modify: `wp-content/themes/dragon-glow/assets/css/accessibility-statement.css` (block `@media (max-width: 1023px)` + `(max-width: 640px)`)
- Modify: `wp-content/themes/dragon-glow/style.css` `:root` section Accessibility Statement Component (thêm `--a11y-toc-*` scoped)

**Interfaces:**
- Consumes: markup Task 1 (`.dg-a11y-toc-btn`, `.dg-a11y-toc-list`, `.is-open`, `[aria-selected=true]`).
- Produces: panel glass bo 16px, item active nền gold nhạt + vạch trái, `aria-expanded=true` xoay icon.

- [ ] **Step 1: Thêm 6 token `--a11y-toc-*` vào `style.css`** (panel-bg, panel-border, panel-shadow, item-active-bg, item-active-text, panel-radius).
- [ ] **Step 2: Viết CSS panel + item trong block 1023px** (absolute, blur, `max-height: 60vh`, scrollbar 4px, active mirror sidebar `.is-active`); block 640px chỉ thu font/padding, giữ `min-height: 40px` touch target.
- [ ] **Step 3: Mở trang ở 390px kiểm bằng Playwright** — Expected: nút pill như cũ, bung ra panel bo góc + shadow, không tràn ngang.

### Task 3: JS — toggle, keyboard, scroll-spy sync

**Files:**
- Modify: `wp-content/themes/dragon-glow/assets/js/accessibility-statement.js` (thêm `initTocListbox()`, giữ `initReveal`, `initScrollSpy`)

**Interfaces:**
- Consumes: `#dg-a11y-toc-btn`, `#dg-a11y-toc-list`, observer sẵn có trong `initScrollSpy`.
- Produces: mở/đóng + `Escape`/click-outside đóng, ArrowUp/Down di chuyển `aria-activedescendant`, chọn item smooth-scroll + đổi nhãn nút.

- [ ] **Step 1: Viết `initTocListbox()`** — guard `if (!btn || !list) return`; toggle `aria-expanded` + class `is-open`; `reduce` → scroll `behavior: auto`.
- [ ] **Step 2: Nối scroll-spy** — trong observer callback sẵn có, đổi `mobileSelect.value = id` thành cập nhật nhãn button + `aria-selected` (giữ null-guard khi markup cũ không còn).
- [ ] **Step 3: Verify Playwright 390/768px** — Expected: click mở/đóng, `Escape` đóng + focus về nút, Enter chọn cuộn tới section, scroll tay đổi nhãn nút, `prefers-reduced-motion` vẫn hiện nội dung.

### Task 4: Version bump + hồi quy desktop + changelog

**Files:**
- Modify: `wp-content/themes/dragon-glow/functions.php:14` (`DG_VERSION` → `1.16.46`)

**Interfaces:**
- Consumes: Task 1–3.
- Produces: cache-bust CSS/JS mới, desktop không đổi.

- [ ] **Step 1: Bump `DG_VERSION` → `1.16.46`.**
- [ ] **Step 2: Hồi quy 1024/1280px** — Expected: `.dg-accessibility-toc-mobile { display: none }`, sidebar hiện, không `pageerror`.
- [ ] **Step 3: Ghi `docs/CHANGELOG.md` mục `2026-11-09a`** theo format sẵn có (không commit).
