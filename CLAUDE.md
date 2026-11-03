# CLAUDE.md — Dragon Glow

Hướng dẫn cho Claude khi làm việc trong dự án này. Đọc kỹ trước khi sửa code.

---

## A. Bối cảnh dự án

**Dragon Glow** là một WordPress **classic theme** + WooCommerce cho thương hiệu mỹ phẩm cao cấp, theo design system **"Luminous Ethereal"** (glassmorphism, bảng màu ethereal, cảm giác sang trọng).

### Project Type Classification

- **WordPress Project Kind**: `wp-site` (full site repo) + `wp-theme` (classic theme)
- **NOT** a block theme (no `theme.json`, no `templates/*.html`)
- **NOT** a plugin development project
- Theme location: `wp-content/themes/dragon-glow/`

### Stack & Constraints

- **WordPress**: 6.4+
- **PHP**: 8.0+
- **WooCommerce**: 9.0+
- **Frontend**: PHP templates thuần + vanilla JavaScript (ES modules qua CDN)
- **Styling**: BEM CSS + Tailwind utility classes (qua CDN, không có build step)
- **CDN Resources**: Tailwind CSS, Motion (motion.dev), Google Fonts (Playfair Display, Plus Jakarta Sans), Material Symbols

### Hard Constraints — ĐỌC KỸ

- 🚫 **KHÔNG React/Vue/Svelte.** Đây là PHP templates + vanilla JS.
- 🚫 **KHÔNG build step trong theme.** Theme phải chạy trên shared hosting (AwardSpace, PHP 8.x).
- 🚫 **KHÔNG npm runtime dependencies trong theme.** Mọi thư viện nạp qua CDN.
- ✅ **Dev tooling** (`package.json` ở gốc repo): chỉ cho playwright (verify) + sharp (optimize ảnh), **không phải dependency của theme**.

### Animation Stack

- **Motion vanilla API** (`import { animate, inView, scroll } from "motion"`)
- **KHÔNG dùng** `motion/react`, `<motion.div>`, `whileHover`, `useScroll`
- Khi cần animation: dùng skill **`.claude/skills/motion-animations/SKILL.md`**

---

## B. Skill Compatibility Matrix

### ✅ Compatible Skills (an toàn để invoke)

| Skill | Use Case | Notes |
|-------|----------|-------|
| `.claude/skills/motion-animations/` | Animation hiệu ứng | Đã aligned với vanilla JS |
| `.claude/skills/design/` (Logo/CIP/Icon/Banner) | Tạo static assets | Python scripts + Gemini AI |
| `.claude/skills/brand/` | Brand voice, messaging | Conceptual guidance |
| `.claude/skills/design-system/` | Design tokens | Adapt vào CSS variables |
| `.cursor/skills/wordpress-router/` | Triage project type | Luôn chạy đầu tiên |
| `.cursor/skills/wp-performance/` | Performance audit | Backend-only agent |
| `.cursor/skills/wp-wpcli-and-ops/` | WP-CLI operations | Maintenance tasks |
| `.cursor/skills/wp-rest-api/` | Custom REST endpoints | Nếu cần API riêng |
| `.cursor/skills/wp-phpstan/` | Static analysis | Code quality |
| `.cursor/skills/graphify/` | Knowledge graph của theme (query/path/explain) | Đọc graph trước khi grep; xem §C.1 |
| `.cursor/skills/{brainstorming,writing-plans,executing-plans,…}` | Process skills của **superpowers** (15 skill) | Đã adapt cho repo không có test suite; xem §C.2 |

### ❌ Incompatible Skills (KHÔNG invoke cho Dragon Glow)

| Skill | Lý do |
|-------|-------|
| `.claude/skills/ui-styling/` | Requires React + shadcn/ui + build step |
| `.claude/skills/ui-ux-pro-max/` | React/Next/Vue/Svelte stack |
| `.claude/skills/slides/` | Tạo HTML presentations (OK nếu không dùng React components) |
| `.cursor/skills/wp-block-themes/` | Dragon Glow là classic theme, không phải block theme |
| `.cursor/skills/wp-block-development/` | Không develop custom Gutenberg blocks |
| `.cursor/skills/wp-interactivity-api/` | Không dùng @wordpress/interactivity |

### ⚠️ Partial Compatibility (dùng có điều kiện)

| Skill | Điều kiện | Notes |
|-------|-----------|-------|
| `.cursor/skills/wp-plugin-development/` | Chỉ khi cần tạo companion plugin | Theme hiện tại không có plugin đi kèm |
| `.claude/skills/design/` (Slides) | Chỉ tạo static HTML | KHÔNG dùng React components |

---

## C. Workflow — Luôn bắt đầu với triage

Trước khi sửa code, **luôn chạy triage** để xác nhận project structure:

```bash
node .cursor/skills/wp-project-triage/scripts/detect_wp_project.mjs
```

Expected output:
```json
{
  "project": {
    "kind": ["wp-site", "wp-theme"]
  },
  "signals": {
    "wp-content": true,
    "theme-classic": true,
    "theme-block": false
  }
}
```

Nếu output khác → báo ngay, đừng assume.

---

## C.1 Graphify — knowledge graph của theme

Skill: `.cursor/skills/graphify/` (bản mirror của `.agents/skills/graphify/`, cài bằng `graphify install --project --platform agents`).

**Output:** `graphify-out/graph.json` (raw graph), `graphify-out/GRAPH_REPORT.md` (god nodes, communities, surprising connections), `graphify-out/graph.html` (visual, mở bằng browser). `graphify-out/` đã nằm trong `.gitignore` — **không commit**, rebuild khi cần.

**Khi nào dùng:** câu hỏi về kiến trúc / "hàm này được gọi ở đâu" / "X nối với Y thế nào" / file nào thuộc concern nào. Grep chỉ thấy chuỗi ký tự; graph thấy quan hệ `calls` / `imports` / `contains` xuyên file.

```bash
graphify query "<câu hỏi>"        # subgraph quanh các node khớp keyword (BFS, mặc định)
graphify query "<câu hỏi>" --dfs --budget 1500
graphify path "A" "B"             # đường đi ngắn nhất; --undirected khi 2 chiều
graphify explain "dg_has_active_selection"
graphify god-nodes --top 20       # các hub kiến trúc
```

**Khi nào KHÔNG dùng:** tra một định nghĩa cụ thể ("hàm này ở file nào") → grep/Read nhanh hơn và miễn phí; hoặc khi `graphify-out/graph.json` chưa tồn tại.

**Rebuild sau khi sửa code (AST-only, không tốn token LLM):**

```bash
graphify update .        # incremental, chỉ file đổi
```

Rebuild toàn bộ (khi graph lệch nhiều hoặc đổi `.graphifyignore`):

```bash
graphify extract . --code-only        # AST PHP/JS, bỏ qua docs/images
graphify cluster-only . --no-label    # sinh lại report + html, giữ label đã curate
```

**Phạm vi corpus:** `.graphifyignore` ở gốc repo loại `.claude/`, `.cursor/`, `.agents/`, `node_modules/`, `graphify-out/`, `test-results/`, `scripts/`, `stitch_dragon_glow_*/`, `CLAUDE.md`, `package.json`. Chỉ code theme được index. CSS **không** được parse (graphify hỗ trợ ~40 ngôn ngữ lập trình, không có CSS) → câu hỏi về style vẫn phải grep.

**Community labels:** `cluster-only --no-label` giữ placeholder "Community N". Tên đã curate nằm ở `scripts/graphify/curated-labels.json`, khoá theo **anchor node id** (không theo cid, vì cid đổi mỗi lần re-cluster). Quy trình sau khi rebuild:

```bash
graphify cluster-only . --no-label
python scripts/graphify/apply-labels.py     # dùng python của venv graphify
graphify cluster-only . --no-label          # report + html lấy tên mới
```

**Giới hạn đã biết:** PHP template thuần (không khai báo hàm/class) yield 0 symbol — graphify bỏ qua các file như `404.php`, `footer.php`. Graph chỉ phản ánh `inc/` + `assets/js/` + `template-parts/*/data-*.php`.

**Số liệu hiện tại:** 1130 nodes · 1594 edges · 236 communities · 84% EXTRACTED / 16% INFERRED. God node lớn nhất: `dg_is_woocommerce_active()` (41 edges) — mọi nhánh WooCommerce đều đi qua guard này.

---

## C.2 Superpowers — process skills

**Nguồn:** https://github.com/obra/superpowers — release `v7.0.1` (commit `2ce8aa1`), MIT license. Cài thủ công (copy) vào `.cursor/skills/`, mirror sang `.claude/skills/` + `.agents/skills/` — cùng pattern với `graphify`.

**Bootstrap (2 lớp):**

1. `.cursor/hooks.json` → hook `sessionStart` chạy `.cursor/hooks/superpowers-session-start.ps1`, inject vào system context mỗi conversation mới: (a) nội dung bootstrap superpowers, (b) **catalog động 34 skill** quét từ `.cursor/skills/*/SKILL.md` frontmatter, (c) **con trỏ knowledge graph** từ `graphify-out/graph.json` kèm số nodes/edges/communities + cảnh báo stale, (d) **drift detector** đối chiếu claim trong CLAUDE.md/rules với thực tế repo — chỉ in khi lệch. Hook fail-open, ~0.9s.
2. `.cursor/rules/superpowers.mdc` (always-on) — bản đầy đủ, có tác dụng cả trong session đang chạy.

⚠️ Hook **không** chép lại `CLAUDE.md` hay rules: Cursor đã tự nạp sẵn (~14.300 tokens/phiên). Inject lại là nhân đôi vô ích.

Hook chỉ chạy khi **tạo conversation mới**. Sửa hook xong phải mở chat mới mới thấy tác dụng.

### 15 skills đã cài

| Nhóm | Skill | Khi nào dùng |
|------|-------|--------------|
| Collaboration | `brainstorming` | Trước mọi creative work: tính năng, component, sửa behavior, plan mới |
| | `writing-plans` | Có spec/yêu cầu multi-step, trước khi chạm code |
| | `executing-plans` | Thực thi plan inline trong session hiện tại (1 context + 1 review cuối) |
| | `subagent-driven-development` | Thực thi plan bằng subagent mỗi task + review 2 tầng mỗi task |
| | `dispatching-parallel-agents` | 2+ task độc lập, không shared state |
| | `using-git-worktrees` | Cần workspace tách biệt khỏi nhánh hiện tại |
| | `finishing-a-development-branch` | Xong việc, cần quyết merge/PR/giữ nhánh |
| Review | `requesting-code-review` | Xong task/feature lớn, trước merge |
| | `receiving-code-review` | Nhận feedback review — verify trước khi implement |
| Debug | `systematic-debugging` | Mọi bug, test fail, hành vi lạ — tìm root cause trước khi sửa |
| | `verification-before-completion` | Chuẩn bị claim "xong/fixed/passing" |
| Testing | `test-driven-development` | Implement feature/bugfix, trước khi viết code |
| Meta | `using-superpowers` | Điểm vào của hệ thống skill |
| | `writing-skills` | Tạo/sửa skill mới |
| | `diagnosing-superpowers` | Session đi sai — truy nguyên bằng transcript |

### Adaptation cho Dragon Glow — QUAN TRỌNG

Superpowers giả định repo có test suite. **Repo này không có** (`package.json` chỉ có playwright + sharp; không jest/vitest/phpunit, không `npm test`).

| Superpowers nói | Áp dụng ở đây |
|---|---|
| `npm test`, RED→GREEN tự động | `php -l` cho cú pháp + Playwright cho render/responsive + bật/tắt WooCommerce. Xác nhận hành vi sai trước, đúng sau = RED→GREEN |
| Chạy plan → worktree mới | Làm trực tiếp trên nhánh hiện tại trừ khi được đồng ý tạo worktree |
| Script bash (`sdd-workspace`, `task-brief`, `review-package`, `start-server.sh`) | Chạy qua Git Bash: `bash .cursor/skills/<skill>/scripts/<tên>` |
| Dispatch subagent mặc định | Chỉ dùng subagent khi user yêu cầu rõ |
| `docs/superpowers/specs/` và `docs/superpowers/plans/` | Giữ nguyên path này khi có design doc/plan |

**Ràng buộc dự án luôn thắng skill:** không React, không build step, PHP template thuần + vanilla JS, token-first CSS, BEM `dg-`. Skill nào khuyên thêm thư viện/build step → bỏ phần đó, giữ process.

---

## D. Nguyên tắc làm việc

### 1. Suy nghĩ trước khi code
> *"Đừng giả định. Đừng giấu chỗ mơ hồ. Nêu rõ đánh đổi."*

- Nói rõ giả định; hỏi khi chưa chắc chắn.
- Khi yêu cầu có nhiều cách hiểu → trình bày hết các cách, không tự ý chọn một cách trong im lặng.
- Thấy cách đơn giản hơn thì lên tiếng, sẵn sàng phản biện.
- Gặp chỗ chưa rõ thì dừng lại, chỉ ra chính xác điểm mơ hồ và hỏi.

### 2. Đơn giản là trên hết
> *"Code tối thiểu giải quyết đúng vấn đề. Không làm dư."*

- Không thêm tính năng không được yêu cầu.
- Không trừu tượng hoá (abstraction) thứ chỉ dùng một lần.
- Không thêm tính linh hoạt / cấu hình mà không ai cần.
- Không xử lý các case lỗi không thể xảy ra.
- Rút gọn code phình to (vd 200 dòng → 50 dòng) khi có cơ hội hợp lý.
- Tự kiểm: *một senior engineer có nói "phức tạp quá" không?*

### 3. Thay đổi phẫu thuật
> *"Chỉ động vào thứ bắt buộc. Chỉ dọn đống mình bày ra."*

- Để yên code/comment/format xung quanh không liên quan đến yêu cầu.
- Không refactor code đang chạy tốt.
- Tuân theo style sẵn có của theme (xem phần **E. Quy ước dự án**).
- Dead code không liên quan thì **báo lại**, đừng tự ý xoá.
- Xoá import/biến/hàm mà chính sửa đổi của mình vừa làm thừa ra.
- Phép thử: *mỗi dòng thay đổi phải truy ngược được về yêu cầu của user.*

### 4. Thực thi theo mục tiêu — kiểm chứng bằng mắt
> *"Định nghĩa tiêu chí thành công. Lặp tới khi kiểm chứng được."*
> *(Theme này **không có test suite** → tiêu chí thành công kiểm bằng quan sát thực tế.)*

- Biến yêu cầu mơ hồ thành tiêu chí **kiểm được bằng quan sát**: trang render đúng? responsive ở 640/768/1024px? hover/accordion/animation chạy đúng? `prefers-reduced-motion` được tôn trọng? hoạt động khi WooCommerce bật và tắt?
- Việc nhiều bước → liệt kê plan đánh số ngắn gọn, mỗi bước kèm cách kiểm chứng.
- **Kiểm chứng bằng cách chạy thật** trên trình duyệt (playwright có ở gốc repo), không chỉ đọc code rồi tuyên bố xong.
- Không bịa ra lệnh test/CI không tồn tại; dựa vào kiểm chứng trực quan + `php -l` (syntax check) khi cần.

---

## E. Quy ước dự án

> Rút ra từ codebase hiện có. Code mới phải **trông giống** code xung quanh.

### 5. Kiến trúc & tổ chức file
> *"Đúng chỗ, đúng tên."*

- `functions.php` **chỉ** `require_once` các file trong `inc/`; **mọi logic nằm trong `inc/`** (nạp `helpers.php` đầu tiên).
- Mỗi page-template có **1 file CSS** (và **1 file JS** nếu cần) riêng, đặt tên kebab-case trùng tên template: `page-templates/template-shipping-returns.php` → `assets/css/shipping-returns.css` + `assets/js/shipping-returns.js`.
- Template parts gom theo folder từng trang: `template-parts/{trang}/`, với tiền tố `section-` (một section đầy đủ), `tile-` (thẻ/component nhỏ), `data-` (file dữ liệu).
- **Tách dữ liệu khỏi markup:** data đặt trong hàm `dg_{feature}_data(): array` (có `apply_filters` để mở rộng); `require_once locate_template(...)` file data **trước** khi render rồi gọi hàm bên trong template-part.
- Override template WooCommerce đặt trong `woocommerce/`.

#### 5a. Folder loader pattern (khi 1 concern có nhiều file con)
> *"1 concern = 1 folder + 1 loader PHP cùng tên."*

- Khi 1 chức năng có **≥ 2 file PHP cùng concern**, gom vào folder con dưới `inc/` và tạo **file loader** ở `inc/` với cùng tên — loader chỉ `require_once` các file con, **không chứa logic**.
- Folder loader hiện có:

  ```
  inc/cart-functions.php       → inc/cart/{page-setup,url,operations,identifiers,template-redirect}.php
  inc/approval-handler.php     → inc/careers/{approval-route,approval-emails,approval-ics,approval-decision}.php
  inc/woocommerce.php          → inc/woocommerce/{general,shop,single-product,cart,checkout,…}.php
  inc/ajax-handlers.php        → inc/ajax/{brevo,careers,cart,contact,newsletter,returns,reviews,wishlist,…}.php
  inc/widgets.php              → inc/widgets/class-dg-{name}.php
  inc/enqueue.php              → inc/enqueue/{tailwind-config,styles,scripts}.php
  inc/helpers.php              → inc/helpers/{woocommerce,ui-components,utilities}.php
  ```

- Trong `functions.php`, `require_once` **loader**, không require từng file con.
- Quy tắc đặt tên:
  - Folder con đặt tên theo concern (số ít, kebab-case): `inc/cart/`, `inc/careers/`, `inc/ajax/`, `inc/checkout/`, `inc/products/`, `inc/widgets/`.
  - Loader file ở `inc/` đặt tên theo concern + hậu tố phù hợp (`cart-functions.php`, `approval-handler.php`) hoặc trùng tên concern (`woocommerce.php`, `helpers.php`).
- 1 concern chỉ có 1 file → **đặt phẳng ở `inc/`**, không tạo folder.

#### 5b. Template Organization — Tách data/logic khỏi markup
> *"Template phải pure: chỉ nhận data qua hàm, không có logic inline."*

**General Pattern (áp dụng tất cả loại template):**

- **Tách data file riêng:** `template-parts/{concern}/data-{name}.php` chứa hàm `dg_{name}_data(): array`.
- **Template nhận context qua tham số:** `require_once locate_template(...)` + gọi hàm → pass mảng vào template.
- **Helper chung:** `dg_render_template( $slug, $context ): string` wrap `locate_template` + `ob_get_clean`.
- **Không có logic inline:** Tất cả `<?php ... ?>` blocks phải là data binding hoặc control flow, KHÔNG business logic.

**Email-Specific Pattern:**

- Email HTML body đặt ở `template-parts/emails/`, **không viết inline `ob_start()` với HTML trong PHP**.
- **Email wrapper** — mỗi email có **1-3 file** tuỳ độ phức tạp:
  - `template-parts/emails/{name}.php` — body chính (luôn có).
  - `template-parts/emails/{name}-header.php` (optional) — header (logo, preheader) khi body > 100 dòng.
  - `template-parts/emails/{name}-footer.php` (optional) — footer (legal, unsubscribe) khi cần shared footer.
- **Naming:** kebab-case, flat (không tạo folder con).
- **Ví dụ:**
  ```
  template-parts/emails/data-approval.php → hàm dg_approval_email_data()
  template-parts/emails/approval.php → nhận context qua $email array
  template-parts/emails/approval-header.php → nếu header reusable
  ```

#### 5c. Tiêu chí tách folder con vs. để phẳng
> *"Tách khi có nhiều file cùng concern + mỗi file > 20 dòng."*

| Tình huống | Hành động |
|---|---|
| 1 concern, 1 file PHP | Đặt phẳng ở `inc/` |
| 1 concern, 2-3 file PHP, mỗi file < 50 dòng | Có thể gộp 1 file, hoặc tách folder loader |
| 1 concern, ≥ 2 file PHP, có file > 50 dòng hoặc ≥ 3 file | **Tạo folder + loader** |
| File là class OOP đơn giản (< 100 dòng) | Đặt phẳng ở `inc/` |
| File là class OOP phức tạp (> 100 dòng, hoặc có companion interfaces/traits) | **Tách `class-dg-{name}.php` riêng**, đặt ở folder con `inc/{concern}/` |
| File là data array lớn cho 1 trang | Tách `data-{trang}.php` ở `template-parts/{trang}/` |
| File là section HTML riêng | Tách `section-{n}.php` ở `template-parts/{trang}/` |

#### 5d. CSS & Asset File Organization
> *"1 concern = 1 file. Tập trung, không tách nhỏ lại, không duplicate token."*

**CSS File Structure (assets/css/):**

- **Cấu trúc:** Tất cả file phẳng, **không có folder con** (trừ khi có font/import đi kèm).
- **Global CSS** (load mọi trang):
  - `style.css` — theme header + CSS token `:root` (single source of truth)
  - `main.css` — base, typography, layout chung
  - `responsive.css` — breakpoint media query overrides
  - `woocommerce.css` — WC global styles (conditional load)
  
- **Page-Specific CSS** (load khi trang match):
  - `{page-slug}.css` — toàn bộ CSS cho 1 page, tên trùng template slug
  - VD: `page-templates/template-shop.php` → `assets/css/shop.css`
  - **1 page = 1 file** — không tách nhỏ (trừ khi file > 1000 dòng → discuss trước)
  - **Comment header:**
    ```css
    /* =========================================
       DRAGON GLOW — {PAGE TITLE} STYLES
       {Brief: layout structure, unique sections}
       ========================================= */
    ```

**Token Management (CRITICAL):**

- **All tokens live in `style.css` `:root`** — single source of truth.
  - `--color-*`, `--spacing-*`, `--shadow-*`, etc. — define once, use everywhere.
  - **KHÔNG duplicate token ở page CSS files.**
  - **KHÔNG define token ở page CSS** nếu reuse khác page (move to `style.css`).
  
- **Token naming hierarchy:**
  - **Primitive:** `--gold-500: #c9a24b` (raw color)
  - **Semantic (global):** `--color-accent: var(--gold-500)` (cross-page)
  - **Semantic (scoped):** `--hero-gradient: linear-gradient(...)` (single page, defined ở style.css nhưng dùng chỉ trong home.css)
  - **Component:** Used in CSS via BEM classes

**JavaScript File Structure (assets/js/):**

- **Cấu trúc:**
  - **Top-level:** mỗi file = 1 module gắn với 1 feature/page
  - **`lib/`:** shared modules (reused ≥ 2 pages, hoặc global `window.DG*`)
  
- **Naming:**
  - `{page-slug}.js` trùng slug page template (paired with CSS cùng tên)
  - `lib/{shared-name}.js` cho shared modules
  - `main.js` cho global JS (load mọi trang)
  
- **Enqueue Strategy:** Mỗi file JS top-level = 1 handle `dg-{slug}`, dependency từ `dg-main`.

**Cache Busting:**

- Mỗi lần sửa CSS/JS → bump `DG_VERSION` ở `functions.php`.
- WP dùng version query param: `.../style.css?ver=1.0.2`

#### 5e. Styling Strategy — External CSS files, not Inline styles
> *"Style thuộc file CSS, không HTML template — trừ 4 ngoại lệ chuẩn WordPress."*

**Nguyên tắc cứng:**

- 🚫 **Cấm `style="..."` inline** trong template/template-part — TRỪ 4 exception dưới.
- 🚫 **Cấm `<style>` block** trong template.
- ✅ **Luôn tạo BEM CSS class** ở page-specific CSS file, apply via template class.

**4 Ngoại Lệ Chuẩn WordPress:**

| # | Use Case | Example | Why Exception |
|---|----------|---------|---------------|
| 1 | **CSS variable bind data** | `style="--progress: 60%;"` | Runtime data → CSS variable |
| 2 | **Dynamic layout values** | `style="grid-column: span 2;"` | Computed runtime value |
| 3 | **SVG inline style** | `<svg style="fill: currentColor;">` | SVG inline cannot load external CSS |
| 4 | **Email HTML** | `style="color: #fff;"` in email template | Email clients don't load external CSS |

**Non-Exceptions (NEVER do these):**

- ❌ `style="color: blue;"` — should be `.component { color: var(--color-primary); }`
- ❌ `style="margin: 10px;"` — should be class + CSS variable token
- ❌ `style="background: #abc123;"` — hard-coded color, should use token
- ❌ `<style> .temp { color: red; } </style>` in template — move to page CSS file

**JavaScript Runtime Styles (ALLOWED for Animation):**

- `el.style.transform = '...'` — Motion API animation
- `el.classList.toggle('is-active')` — preferred over inline style
- `el.style.setProperty('--custom', value)` — OK for scoped dynamic style

**WordPress `wp_add_inline_style` (CORRECT for Customizer Runtime CSS):**

- Use within `inc/enqueue/styles.php`, NOT in template.
- Example: customizer sets brand color → WP injects CSS via `wp_add_inline_style`.

**JavaScript File Organization (moved to §5d above)**

#### 5g. Selectors phải khớp DOM thực tế, không assume hoặc hard-code
> *"Selector dựa giả định = bug ẩn. Luôn inspect DOM thật, target via theme-controlled class."*

**Nguyên tắc cứng:**

1. **Trước khi viết selector, inspect DOM thật** (F12 → Elements).
   - Không assume DOM mặc định của plugin hoặc library.
   - Theme có thể refactor layout custom bất kỳ lúc nào.
   
2. **Target bằng class theme-controlled** (`dg-{name}`) là an toàn nhất.**
   - ✅ `.dg-product-card` — theme-controlled, safe
   - ⚠️ `.product` — might be from WC core, could change
   - ❌ `div:nth-child(3) > span` — brittle, layout-dependent
   
3. **Nếu selector dựa trên DOM từ plugin/library, document ngay trong comment:**
   ```css
   /* .wc-block-product__title — from WooCommerce Blocks (v8.0+)
      Inspect to confirm this selector still exists in current WC version */
   .wc-block-product__title {
     color: var(--product-title-color);
   }
   ```
   
4. **Visual parity rule:** 2 elements cùng vai trò (vd "Proceed to Checkout" vs "Place Order" buttons) →
   - **đối chiếu visual trước khi commit** (size, color, spacing phải cùng).
   - **Nếu khác DOM structure:** thêm comment giải thích tại sao.
   
5. **Không target chung cho khác loại element:**
   ```css
   /* ❌ WRONG — cộng gom */
   .button, .btn, [role="button"] { color: blue; }
   
   /* ✅ CORRECT — explicit, controllable */
   .dg-button { color: blue; }
   .dg-card__action { color: var(--action-color); }
   ```

#### 5h. Đối chiếu tên file CSS / JS ↔ page template
> *"CSS, JS, data, section — tất cả phải đối chiếu 1-1 qua slug."*

Xem bảng mapping đầy đủ trong phiên bản cũ của `CLAUDE.md` (dòng 316-357).

### 6. Quy ước PHP
> *"An toàn, có tiền tố, dịch được."*

- Hàm prefix `dg_`; class prefix `DG_`, PascalCase. Tên file = tên class: `class-dg-product.php` ↔ `DG_Product`.
- Đầu **mọi** file PHP: docblock `@package Dragon_Glow` + `defined( 'ABSPATH' ) || exit;`.
- **Luôn escape output:** `esc_html()`, `esc_attr()`, `esc_url()`.
- **Luôn i18n** với text-domain `'dragon-glow'`.
- Dùng type hints PHP 8.0+ (`: bool`, `: array`, `: string`, `: void`).
- **Mọi comment (docblock, inline) viết bằng tiếng Anh** — chuẩn quốc tế.
- **Mọi content hiển thị cho user viết bằng tiếng Anh** — dùng i18n để dịch qua `.po/.mo`.

### 7. Tải assets (enqueue)
> *"Tập trung, có điều kiện, có version."*

- **Mọi** enqueue CSS/JS nằm ở `inc/enqueue.php` (single source of truth).
- Tải **có điều kiện** theo trang: `is_page_template()` hoặc WooCommerce conditional (`is_shop()`, `is_product()`, `is_cart()`).
- Handle đặt `dg-{feature}`; version dùng hằng `DG_VERSION`; CSS trang phụ thuộc `dg-main`.
- JS thường: `wp_enqueue_script( handle, src, deps, DG_VERSION, true )` (footer).
- JS dùng `import` (Motion ES module) → `wp_enqueue_script_module(...)`.
- Truyền data PHP→JS qua `wp_localize_script`.

### 8. CSS
> *"BEM có tiền tố, dùng token, responsive & accessible."*

- Class theo **BEM với tiền tố `dg-`**: block `dg-tile`, modifier `dg-tile--free`, trạng thái `.is-open` / `.is-highlight`.
- Dùng **design token**, tránh hard-code: màu `--color-*`, spacing `--wp--custom--spacing--*`. Token định nghĩa ở `style.css` (`:root`) và Tailwind config (trong `enqueue.php`).
- Breakpoints chuẩn của theme: **640 / 768 / 1024px**. Kích thước fluid dùng `clamp()`.
- **Bắt buộc accessibility:** có khối `@media (prefers-reduced-motion: reduce)` để tắt animation, và `:focus-visible` cho phần tử tương tác.
- Comment phân khu theo style sẵn có (dải `═══` cho SECTION lớn).

#### 8a. Design Token System — LUÔN dùng CSS variables, KHÔNG hard-code
> *"Token-first approach: define once, use everywhere. Hard-code = tech debt."*

**Nguyên tắc cứng:**

1. **🚫 CẤM hard-code giá trị màu/spacing/typography trong CSS component**
   ```css
   /* ❌ SAI — Hard-code */
   .dg-filter-item--active {
     background: rgba(201, 162, 75, 0.25);
     color: rgb(115, 92, 0);
     font-weight: 600;
   }
   
   /* ✅ ĐÚNG — Dùng token */
   .dg-filter-item--active {
     background: var(--filter-item-bg-active);
     color: var(--filter-item-text-active);
     font-weight: var(--filter-item-font-weight-active);
   }
   ```

2. **✅ Token hierarchy: Primitive → Semantic → Component**
   ```css
   /* style.css — :root */
   
   /* Layer 1: Primitive tokens (base colors) */
   --color-accent-gold: #C9A24B;
   --gold-rgb: 201, 162, 75;
   
   /* Layer 2: Semantic tokens (purpose-driven) */
   --filter-item-text-active: var(--color-accent-gold);
   --filter-item-bg-active: rgba(var(--gold-rgb), 0.1);
   
   /* Layer 3: Component tokens (specific states) */
   --filter-item-count-active: var(--filter-item-text-active);
   --filter-item-font-weight-active: 600;
   ```

3. **📋 Token checklist khi tạo component mới:**
   - [ ] Có bao nhiêu states? (default, hover, active, disabled, focus)
   - [ ] Mỗi state cần token gì? (bg, text, border, shadow, font-weight, opacity)
   - [ ] Token này dùng lại được cho component khác không?
   - [ ] Có child element cần token riêng không? (count badge, icon, label)

4. **🔍 Visual regression workflow:**
   ```
   Trước khi commit → so sánh với design/demo:
   1. Screenshot component ở tất cả states
   2. Đo giá trị bằng DevTools (Computed styles)
   3. Đối chiếu: màu hex, opacity %, font-weight, spacing px
   4. Nếu sai lệch → sửa TOKEN trước, component sau
   ```

5. **⚠️ Khi nào được phép hard-code:**
   - **KHÔNG BAO GIỜ** trong production code
   - Chỉ trong prototype/demo tạm thời (phải có comment `/* TODO: tokenize */`)

6. **🛠️ Quy trình refactor hard-code → token:**
   ```
   Step 1: Tìm tất cả hard-code values (grep rgba/rgb/px/#[0-9a-f])
   Step 2: Group theo concern (filter, button, card, etc.)
   Step 3: Define tokens ở style.css (với comment mô tả mục đích)
   Step 4: Replace tất cả occurrences bằng var(--token-name)
   Step 5: Visual regression test
   Step 6: Bump DG_VERSION
   ```

**Ví dụ thực tế (filter active state):**

```css
/* ===== style.css ===== */
:root {
  /* Filter item tokens — active state */
  --filter-item-bg-active: rgba(var(--gold-rgb), 0.1);           /* 10% gold tint */
  --filter-item-bg-active-hover: rgba(var(--gold-rgb), 0.15);    /* 15% on hover */
  --filter-item-text-active: var(--color-accent-gold);           /* Gold text */
  --filter-item-count-active: var(--color-accent-gold);          /* Count badge color */
  --filter-item-font-weight-active: 600;                         /* Semi-bold */
  --filter-item-border: transparent;
  --filter-item-border-hover: rgba(var(--gold-rgb), 0.2);
}

/* ===== shop.css ===== */
.dg-filter-item--active {
  background: var(--filter-item-bg-active) !important;
  color: var(--filter-item-text-active) !important;
  font-weight: var(--filter-item-font-weight-active) !important;
  border: 1px solid var(--filter-item-border) !important;
}

.dg-filter-item--active:hover {
  background: var(--filter-item-bg-active-hover) !important;
  border-color: var(--filter-item-border-hover) !important;
}

.dg-filter-item--active .dg-filter-item-count {
  color: var(--filter-item-count-active);
  opacity: 1; /* Remove default dimming */
  font-weight: var(--filter-item-font-weight-active);
}
```

**Benefits:**
- ✅ Single source of truth
- ✅ Easy to maintain/update
- ✅ Theme-able (có thể override qua customizer)
- ✅ Consistent across components
- ✅ Self-documenting (token names mô tả mục đích)

#### 8b. KHÔNG dùng Tailwind Forms plugin — tự viết reset trong CSS theme
> *"Theo chuẩn thực tế: bỏ `plugins=forms` là lựa chọn đúng của coder lâu năm."*

**Nguyên tắc cứng (áp dụng cho code mới từ nay):**

- 🚫 **CẤM** thêm `?plugins=forms` (hoặc bất kỳ Tailwind CDN plugin nào) vào URL `cdn.tailwindcss.com` ở `inc/enqueue/scripts.php`. Theme hiện tại nạp Tailwind **không kèm** `plugins=forms`.
- 🚫 **CẤM** style input/select/textarea mới bằng Tailwind utility class trực tiếp trên markup (`border-0 border-b ... focus:ring-0`) rồi dựa vào reset mặc định của `@tailwindcss/forms`. Luôn viết CSS riêng theo BEM (`dg-{page}__input`) trong file CSS trang tương ứng.
- ✅ Khi cần reset native style của input/select (`appearance: none`, border, background...), **tự viết trong CSS theme** — đây là cách phần lớn theme đã làm (`account.css`, `shop.css`, `cart.css`, `account-register.css`, v.v. đều tự có `appearance: none` riêng, không phụ thuộc Tailwind Forms).

  ```css
  /* ✅ ĐÚNG — tự viết reset trong CSS trang, không phụ thuộc Tailwind Forms */
  .dg-contact-form__select {
      appearance: none;
      -webkit-appearance: none;
      border: 1px solid var(--token-border);
      background-color: var(--token-surface);
  }

  .dg-contact-form__select:focus {
      border-color: var(--token-border-focus);
  }
  ```

**Lý do bỏ `plugins=forms` là lựa chọn đúng:**

- Tailwind nạp qua `<script>` CDN (`inc/enqueue/scripts.php`), tự inject `<style>` vào `<head>` **tại runtime**, nên luôn nằm **sau** mọi CSS theme đã enqueue → source order luôn thắng khi specificity hoà (`[type="text"]` = 0,1,0, bằng 1 class `.dg-*__input` = 0,1,0). Giữ plugin này nghĩa là chấp nhận rủi ro đè `border`/`background` của **mọi** input mới trong theme, trừ khi coder nhớ tăng specificity qua wrapper mỗi lần — dễ quên, khó maintain lâu dài.
- Giải pháp bền hơn là loại bỏ nguồn gây xung đột (gỡ `plugins=forms`) thay vì xử lý triệu chứng (tăng specificity từng input). Coder lâu năm ưu tiên **xoá root cause** hơn là thêm quy tắc phòng ngự lặp lại ở mọi nơi.
- Theme đã tự viết `appearance: none` và reset input riêng ở hầu hết CSS trang — chứng tỏ theme **không thực sự cần** Tailwind Forms để hoạt động đúng.
- `template-contact.php` hiện tại vẫn dùng Tailwind utility class trực tiếp và phụ thuộc reset của `@tailwindcss/forms` — đây là **sơ xuất cũ, giữ nguyên hiện trạng, không sửa**. Quy tắc này chỉ áp dụng cho input/select/textarea **mới** tạo từ nay về sau.

**Cách phát hiện nếu nghi ngờ input mới bị ảnh hưởng (debug nhanh bằng console):**

```js
const el = document.querySelector('.dg-contact-form__input');
const cs = getComputedStyle(el);
console.log('border-color computed:', cs.borderColor); // nếu không khớp token CSS theme → kiểm tra lại nguồn gây đè
```

#### 8c. Component Style Isolation — LUÔN token riêng, KHÔNG dùng global theme tokens cho component-local styles
> *"Component-specific style = dedicated token (not global secondary/tertiary/accent/etc.). Prevent accidental style pollution across pages."*

**Golden Rule:**
```
Khi styling component mới:
  IF: Cần color/spacing/shadow/size riêng cho component này
  THEN: Tạo token riêng (Primitive → Semantic → Component)
  NOT: Dùng global theme token (secondary, tertiary, accent, etc.)
```

**Problem Case — Accidental Pollution (DO NOT DO THIS):**

```
Cách sai #1: Dùng global Tailwind classes trực tiếp
  ❌ class="bg-secondary text-secondary-container border-secondary"
     → Sau này thay đổi secondary theme → component này đổi màu tự động (unexpected!)

Cách sai #2: Dùng global theme tokens trong component CSS
  ❌ .dg-hero__badge { color: var(--color-secondary); }
     → Thay đổi --color-secondary ở style.css → badge đổi màu
     → Tất cả page khác dùng secondary cũng bị ảnh hưởng

Cách sai #3: Hard-code color + dùng global token ngoài context
  ❌ .dg-hero__title { color: #a9371e; }  /* Hard-code + thừa */
     .dg-hero__title { color: var(--secondary); } /* Thay global token */
     → Nếu design thay đổi chỉ riêng hero → không cách nào override dễ dàng
```

**Professional Solution: Scoped Component Tokens**

**Step 1: Define Primitive Tokens (nếu color không có sẵn ở theme)**
```css
/* style.css :root */

/* Primitive — component-specific (NẾU không khớp theme palette) */
--warm-reddish-brown: #a9371e;  /* Only for warm gradient components */
--coral-accent: #fd7455;        /* Only for coral highlights */
```

**Step 2: Define Semantic Tokens (component-specific, layer 2)**
```css
/* style.css :root */

/* Semantic — scoped to component concern (NOT for reuse across pages) */
--hero-gradient: linear-gradient(to right, var(--warm-reddish-brown), var(--coral-accent), var(--warm-gold));
--hero-underline-color: var(--coral-accent);
--hero-badge-bg: rgba(var(--gold-rgb), 0.1);
--hero-badge-text: var(--gold-500);

/* CTA button (hero section only) */
--cta-primary-gradient: linear-gradient(to right, var(--coral-accent), var(--warm-reddish-brown), var(--gold-dark));
--cta-primary-text: var(--neutral-0);
```

**Step 3: Create BEM Component Rules (layer 3, scoped to page CSS)**
```css
/* home.css — SCOPED to hero section only */

.dg-home-hero__gradient-text {
  background-image: var(--hero-gradient);
  background-size: 100% 100%;
  background-position: left center;
}

.dg-home-hero__badge {
  background: var(--hero-badge-bg);
  color: var(--hero-badge-text);
}

.dg-home-hero__cta-primary {
  background: var(--cta-primary-gradient);
  color: var(--cta-primary-text);
}
```

**Step 4: Use BEM Classes in Template (NOT Tailwind gradient classes)**
```php
<!-- ❌ BEFORE — Tailwind classes (fragile) -->
<span class="bg-gradient-to-r from-secondary via-secondary-container to-primary">

<!-- ✅ AFTER — BEM scoped class (robust) -->
<span class="dg-home-hero__gradient-text">
```

---

### **Decision Tree — When to Create Dedicated Token**

```
Styling new component/section?
│
├─ "Will this color/size/shadow be reused in 3+ different pages?"
│  └─ YES → Define semantic token ở style.css (shared across theme)
│            Use in multiple component rules (shop.css, account.css, etc.)
│  └─ NO → Go to next question
│
├─ "Could this style accidentally affect OTHER components if I change global token?"
│  └─ YES (high risk) → Create dedicated token ở style.css
│           Scoped to this component (e.g., --hero-gradient, --filter-active-bg)
│           Use ONLY in this page's CSS file
│  └─ NO → Go to next question
│
├─ "Am I using global theme token (secondary, tertiary, accent, error, etc.)?"
│  └─ YES → Ask yourself: "Does this component MUST follow theme color change?"
│            └─ YES → OK, use global token (e.g., error message always uses --error)
│            └─ NO → Create dedicated token instead
│  └─ NO → Go to next question
│
└─ "Is value hard-coded (e.g., #a9371e, rgba(...))?
   └─ YES → Define primitive token + semantic token ở style.css
   └─ NO → ✅ You're good
```

---

### **Token Scope Matrix**

| Token Type | Definition Location | Used In | Scope | Risk If Changed |
|---|---|---|---|---|
| **Primitive** | `style.css :root` | Semantic tokens | Theme-level | Global (all pages) |
| **Semantic** (global) | `style.css :root` | Multiple page CSS files | Cross-page | Global (many pages) |
| **Semantic** (scoped) | `style.css :root` | Single page CSS file | Component | Local (1 section) |
| **Component** | `{page}.css` | Template via BEM class | Single component | Local (affects styling only) |

**Example Mapping:**
```
Primitive:
  --gold-500: #c9a24b

Semantic (Global):
  --color-accent: var(--gold-500)  ← Used in: shop, account, cart, header

Semantic (Scoped):
  --hero-gradient: linear-gradient(..., var(--warm-coral), ...)  ← Used in: home.css ONLY
  
Component:
  .dg-home-hero__gradient-text { background-image: var(--hero-gradient); }  ← Used in: home.php via BEM class
```

---

### **Checklist — Before Styling New Component**

- [ ] Did I define token in `style.css` for any new color/size/spacing?
- [ ] If yes, is token **scoped to this component** (e.g., `--hero-*`, `--shop-filter-*`)?
- [ ] Did I use global theme token (secondary, tertiary, etc.) instead of component-specific token?
  - [ ] If yes: Confirmed this is intentional (component MUST follow theme)?
- [ ] Am I using Tailwind gradient/shadow/color classes that reference global tokens?
  - [ ] If yes: Replace with BEM class + CSS rule using component token
- [ ] Will changing this style affect OTHER pages unintentionally?
  - [ ] If yes: Create dedicated token (don't use global)
- [ ] Is there hard-coded color/value in my CSS?
  - [ ] If yes: Extract to token (primitive + semantic)

---

### **Real Example — This Session's Hero Gradient Fix**

**Problem:** Hero headline needed warm gradient (#a9371e → #fd7455 → #d4af37), but theme secondary is cool tones.

**Solution Implemented:**

1. ✅ Created **Primitive tokens** (style.css):
   ```css
   --warm-reddish-brown: #a9371e;
   --warm-coral: #fd7455;
   --warm-gold: #d4af37;
   ```

2. ✅ Created **Semantic tokens** (style.css):
   ```css
   --hero-gradient: linear-gradient(to right, var(--warm-reddish-brown), var(--warm-coral), var(--warm-gold));
   --hero-gradient-underline: var(--warm-coral);
   --cta-primary-gradient: linear-gradient(to right, var(--warm-coral), var(--warm-reddish-brown), var(--gold-dark));
   ```

3. ✅ Created **Component rules** (home.css):
   ```css
   .dg-home-hero__gradient-text {
     background-image: var(--hero-gradient);
   }
   .dg-home-hero__cta-primary {
     background: var(--cta-primary-gradient);
   }
   ```

4. ✅ Updated **Markup** (hero.php):
   ```php
   <span class="dg-home-hero__gradient-text">...</span>
   ```

**Result:**
- ✅ Hero gradient = warm colors (correct)
- ✅ Secondary/tertiary tokens = unchanged (protect other pages)
- ✅ ZERO collateral damage across theme
- ✅ Future gradient changes = update token once

---

### 9. Interactivity & Dynamic Behavior
> *"Vanilla JS, không framework. Lựa chọn tool dựa trên use case."*

**General Rules:**

- **No framework:** React, Vue, Svelte, hoặc framework khác = KHÔNG dùng. PHP templates + vanilla JS suffices.
- **Nào cần animation?** → Xử lý tùy từng scenario:
  - **Scroll reveal, stagger, spring physics** → `Motion vanilla API` (`import { animate, inView } from "motion"`)
  - **Simple show/hide** → CSS transitions + JS classList toggle
  - **Form validation, UI interactivity** → Vanilla DOM APIs (`querySelector`, `addEventListener`, etc.)
  
- **Bundler/build step:** ❌ KHÔNG. Mọi thư viện nạp qua **CDN ES modules**.

**Motion Animation Pattern (khi cần Motion):**

- Use **Motion vanilla API** ONLY: `import { animate, inView, scroll } from "https://cdn.jsdelivr.net/npm/motion@11/+esm"`
- **KHÔNG bao giờ** dùng `motion/react`, `<motion.div>`, `whileHover`, `useScroll`.
- Skill chuyên dụng: `.claude/skills/motion-animations/SKILL.md`

**JavaScript Structure Pattern (tất cả scripts):**

```js
'use strict';

(function() {
  // Respect prefers-reduced-motion
  const prefersReduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
  
  // Init functions
  function initComponentA() { /* ... */ }
  function initComponentB() { /* ... */ }
  
  // Enqueue on DOM ready
  document.addEventListener('DOMContentLoaded', () => {
    if (!prefersReduced) initComponentA();
    initComponentB();
  });
})();
```

**Enqueue Strategy (centralized ở inc/enqueue.php):**

```php
// Plain script (IIFE wrapper)
wp_enqueue_script( 'dg-shop', DG_URI . '/assets/js/shop.js', array( 'dg-main' ), DG_VERSION, true );

// ES module (dùng import Motion)
wp_enqueue_script_module( 'dg-product-slider', DG_URI . '/assets/js/product-slider.js', array(), DG_VERSION );

// Shared lib
wp_enqueue_script( 'dg-cart-api', DG_URI . '/assets/js/lib/cart-api.js', array( 'dg-main' ), DG_VERSION, true );
```

**FOUC (Flash of Unstyled Content) Guard:**

- Template: `<html class="dg-js">` (add khi JS enabled)
- CSS: `.dg-js [data-sr] { opacity: 0; }` (hide reveal elements initially)
- JS: Animate elements visible after load

**Tránh Anti-Patterns:**

- ❌ Inline event handlers: `<button onclick="...">` → Use `addEventListener` thay
- ❌ Global functions: `window.initStuff = () => {}` → Use IIFE + closure thay
- ❌ jQuery hoặc library lạc quẻ → Vanilla DOM APIs là sufficient
- ❌ Modify built-in prototypes: `Array.prototype.custom = ...` → Use utility functions thay

---

## F. Kiểm chứng thay đổi

Trước khi tuyên bố xong, hãy verify:

- Mở trang liên quan trên trình duyệt (playwright có ở gốc repo) và **quan sát**: render đúng, đúng layout/section, animation chạy mượt.
- Kiểm **responsive** ở 3 mốc **640 / 768 / 1024px**.
- Bật "reduce motion" của OS → animation phải tắt, nội dung vẫn hiển thị đầy đủ.
- Nếu đụng phần WooCommerce: thử cả khi plugin **bật** và **tắt** (theme có cơ chế mock khi WC tắt).
- Với file PHP vừa sửa: kiểm cú pháp bằng `php -l` nếu môi trường cho phép.

---

## G. Integration với .cursor/skills WordPress ecosystem

### Khi nào invoke WordPress skills

**Luôn bắt đầu với:**
```bash
node .cursor/skills/wp-project-triage/scripts/detect_wp_project.mjs
```

**Routing theo intent:**

- **Performance audit** → `.cursor/skills/wp-performance/`
- **WP-CLI operations** → `.cursor/skills/wp-wpcli-and-ops/`
- **Custom REST endpoint** → `.cursor/skills/wp-rest-api/`
- **PHPStan static analysis** → `.cursor/skills/wp-phpstan/`
- **Companion plugin cần tạo** → `.cursor/skills/wp-plugin-development/`
- **Câu hỏi kiến trúc / quan hệ giữa các file** → `.cursor/skills/graphify/` (xem §C.1)

**KHÔNG invoke:**

- `wp-block-themes` — Dragon Glow là classic theme
- `wp-block-development` — không develop custom Gutenberg blocks
- `wp-interactivity-api` — không dùng @wordpress/interactivity

### Guardrails khi làm việc với WordPress skills

1. **Confirm project type trước**: classic theme trong full site repo.
2. **Prefer theme conventions**: BEM CSS + vanilla JS, không thêm build step.
3. **Respect PHP/WP version**: PHP 8.0+, WP 6.4+.
4. **Follow WooCommerce integration patterns** đã có trong `inc/woocommerce/`.

---

## H. Changelog

- `2026-11-02e`: hook `sessionStart` thêm **drift detector** — đối chiếu number nodes/edges/communities trong CLAUDE.md, danh sách skill bảng §C.2, `DG_VERSION`, và đường dẫn file theme với thực tế repo; **chỉ inject khi lệch** (khớp thì im lặng, 0 token). Quyết định **không** chép lại CLAUDE.md/rules vào hook vì Cursor đã tự nạp sẵn (~14.300 tokens/phiên) — inject lại là nhân đôi vô ích. Sửa false positive: check skill count ban đầu đếm cả 34 thư mục (15 superpowers + 19 WordPress) thay vì chỉ danh sách được nêu trong CLAUDE.md.
- `2026-11-02d`: mở rộng hook `sessionStart` — ngoài bootstrap superpowers, hook giờ tự quét `.cursor/skills/*/SKILL.md` để inject **catalog 34 skill** (cắt description 95 ký tự) và đọc `graphify-out/graph.json` để inject **con trỏ knowledge graph** kèm số nodes/edges/communities + cảnh báo stale (so `built_at_commit` với `HEAD`). Fail-open từng section. Sửa 2 bug phát hiện khi test: (1) backtick trong here-string bị PowerShell escape → mất ký tự (`assets/js` thành `ssets/js`), (2) mojibake `â€"` do PS 5.1 đọc file không BOM theo ANSI → script nay thuần ASCII + transliterate output.
- `2026-11-02c`: thêm hook `sessionStart` (`.cursor/hooks.json` + `.cursor/hooks/superpowers-session-start.ps1`) để inject bootstrap superpowers vào system context mỗi conversation mới — bám sát cơ chế của upstream thay vì chỉ dựa vào rule always-on. Sửa lại ghi chú sai trước đó ("Cursor không có hook SessionStart").
- `2026-11-02b`: thêm §C.2 — **superpowers** (obra/superpowers v7.0.1, MIT). Copy 15 process skills vào `.cursor/skills/` (mirror `.claude/` + `.agents/`), thêm rule always-on `.cursor/rules/superpowers.mdc` (thay hook `sessionStart` của upstream). Ghi rõ adaptation: repo không có test suite → kiểm chứng bằng `php -l` + Playwright thay cho `npm test`.
- `2026-11-02`: thêm §C.1 — **graphify knowledge graph**. Cài `graphifyy` 0.9.100 qua uv tool, đăng ký skill vào `.agents/skills/graphify/` (mirror sang `.cursor/skills/` + `.claude/skills/`), thêm rule always-on `.cursor/rules/graphify.mdc`, `.graphifyignore` giới hạn corpus vào code theme, script `scripts/graphify/apply-labels.py` + `curated-labels.json` để giữ tên community qua mỗi lần re-cluster. Graph: 1130 nodes / 1594 edges / 236 communities.
- `2026-10-27b`: refactored sections 5b/5d/5e/5g/9 — Generalized rules (nói chung không nói riêng). §5b (email) → "Template Organization" (tất cả template). §5d CSS + JS organization combined (formerly 5d/5f). §5e inline styles → "Styling Strategy" (general rule). §5g selectors → removed "WC-specific" language, added general principle. §9 → "Interactivity & Dynamic Behavior" (remove Motion-specific focus, added general JS patterns + Motion as option).
- `2026-10-27a`: thêm §8c — **Component Style Isolation**: rule cứng prevent style pollution across pages (ALL styles, not just gradient). LUÔN tạo token riêng cho component-local styles, KHÔNG dùng global theme tokens. 3-layer token system (Primitive → Semantic → Component), decision tree, scope matrix, comprehensive checklist.
- `2026-10-02`: thêm §8b — Tailwind Forms plugin override: nguyên nhân specificity tie + source order khi Tailwind nạp qua `<script>`, cách debug bằng console, cách khắc phục bằng tăng specificity qua wrapper class (không dùng `!important`).
- `2026-09-29`: thêm §8a — Design Token System: quy tắc cứng về token-first approach, cấm hard-code, visual regression workflow, refactor checklist.
- `2026-08-24`: Refactor toàn bộ để tương thích với `.cursor/skills` ecosystem; thêm skill compatibility matrix; làm rõ project type (classic theme trong full site repo); loại bỏ mâu thuẫn về React/build step.
- `2026-08-10`: thêm §5g — selector phải khớp DOM thật; visual parity rule.
- `2026-08-09`: refactor — tách cart helpers, approval handler, legal pages.
