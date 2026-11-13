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

```powershell
graphify cluster-only . --no-label
# dùng python của venv uv graphify (python hệ thống thiếu module `graphify`)
& "$env:APPDATA\uv\tools\graphifyy\Scripts\python.exe" scripts/graphify/apply-labels.py
graphify cluster-only . --no-label          # report + html lấy tên mới
```

**Giới hạn đã biết:** PHP template thuần (không khai báo hàm/class) yield 0 symbol — graphify bỏ qua các file như `404.php`, `footer.php`. Graph chỉ phản ánh `inc/` + `assets/js/` + `template-parts/*/data-*.php`.

**Số liệu:** không hard-code trong file này. Hook `sessionStart` đọc trực tiếp `graphify-out/graph.json` và in dòng "Graph status" (nodes / edges / communities + commit + fresh/stale) mỗi phiên; số liệu chi tiết nằm trong `graphify-out/GRAPH_REPORT.md` §Summary. God node lớn nhất hiện là `dg_is_woocommerce_active()` — mọi nhánh WooCommerce đều đi qua guard này.

---

## C.1b Chrome DevTools MCP — điều khiển browser thật

**Nguồn:** https://github.com/ChromeDevTools/chrome-devtools-mcp — release `1.12.0`. Là **MCP server** (stdio, chạy qua `npx`), **không phải thư viện**, không phải dependency của theme.

**Không có hook báo cáo.** Script `chrome-devtools-mcp-session-start.ps1` và cache của nó **đã bị xoá** — không có gì inject vào context mỗi phiên, không có sentinel. Muốn biết server có sống không thì gọi thử một tool (vd `list_pages`); trả kết quả = sống, lỗi = server chưa start.

**Vị trí:**

| Thứ | Đường dẫn | Commit? |
|---|---|---|
| MCP config | `.cursor/mcp.json` | ✅ có |
| Script tải lại | `scripts/setup-chrome-devtools-mcp.ps1` | ✅ có |
| Source tham khảo (docs + skills) | `vendor/chrome-devtools-mcp/` | ❌ gitignore |

**Cài lại / bump:** chạy `powershell -ExecutionPolicy Bypass -File scripts/setup-chrome-devtools-mcp.ps1`. Script đọc version từ `.cursor/mcp.json` rồi fetch tag `chrome-devtools-mcp-v<version>`, nên docs luôn khớp server. Đổi version thì sửa `args` trong `mcp.json` rồi chạy lại.
**⚠️ Windows: phải bọc `cmd /c`.** Cursor spawn MCP stdio **không qua shell**; `npx` trên Windows là `.cmd` shim mà Node ≥20 từ chối spawn (CVE-2024-27980), còn `npx` trần không phải file thực thi. Kết quả: server hiện trong danh sách nhưng kẹt `disconnected` vĩnh viễn, **không lỗi nào trong UI**. Đúng dạng:

```json
{ "command": "cmd", "args": ["/c", "npx", "-y", "chrome-devtools-mcp@1.12.0"] }
```

**Tools:** mặc định **32**. `.cursor/mcp.json` bật 6 flag (`memoryDebugging`, `categoryExtensions`, `categoryExperimentalThirdParty`, `categoryExperimentalWebmcp`, `categoryPwa`, `experimentalVision`) → **59**, tức toàn bộ tool của npm `1.12.0`. Nhánh `main` tài liệu hoá **61** (`set_logpoints`, `configure_webauthn`) nhưng npm chưa ship — `--categoryWebauthn=true` bị **bỏ qua âm thầm**, nên luôn đối chiếu `npm view chrome-devtools-mcp dist-tags` thay vì tin con số trong docs. Đọc `docs/configuration.md` trước khi thêm flag, `docs/tool-reference.md` trước khi gọi tool lạ. `--slim` rút còn 3 tool.

**Ranh giới:** chỉ soi browser. Không miễn trừ ràng buộc nào ở §A — dùng để verify render/responsive/console/network, đúng tinh thần §F.

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

Chức năng có **≥ 2 file PHP cùng concern** → gom vào folder con dưới `inc/`, tạo **loader** ở `inc/` chỉ `require_once` các file con (không logic). `functions.php` require **loader**, không require từng file con.

```
inc/cart-functions.php   -> inc/cart/{page-setup,url,operations,identifiers,template-redirect}.php
inc/approval-handler.php -> inc/careers/{approval-route,approval-emails,approval-ics,approval-decision}.php
inc/woocommerce.php      -> inc/woocommerce/{general,shop,single-product,cart,checkout,...}.php
inc/ajax-handlers.php    -> inc/ajax/{brevo,careers,cart,contact,newsletter,returns,reviews,wishlist,...}.php
inc/widgets.php          -> inc/widgets/class-dg-{name}.php
inc/enqueue.php          -> inc/enqueue/{tailwind-config,styles,scripts}.php
inc/helpers.php          -> inc/helpers/{woocommerce,ui-components,utilities}.php
```

- Folder con: concern số ít, kebab-case (`inc/cart/`, `inc/careers/`, `inc/ajax/`, `inc/widgets/`).
- Loader: concern + hậu tố (`cart-functions.php`, `approval-handler.php`) hoặc trùng tên concern (`woocommerce.php`, `helpers.php`).
- 1 concern chỉ 1 file -> đặt phẳng ở `inc/`, không tạo folder.
#### 5b. Template Organization — tách data/logic khỏi markup
> *"Template phải pure: nhận data qua hàm, không logic inline."*

- **Data file riêng:** `template-parts/{concern}/data-{name}.php` chứa `dg_{name}_data(): array` (kèm `apply_filters` để mở rộng). `require_once locate_template(...)` file data **trước** khi render, rồi gọi hàm trong template-part.
- **Helper chung:** `dg_render_template( $slug, $context ): string` — wrap `locate_template` + `ob_get_clean`.
- **Không logic inline:** block `<?php ... ?>` chỉ là data binding hoặc control flow, KHÔNG business logic.

**Email:** body HTML ở `template-parts/emails/`, **không** `ob_start()` với HTML nhúng trong PHP. Mỗi email 1-3 file, kebab-case, flat:

```
template-parts/emails/approval.php          <- body chinh (luon co)
template-parts/emails/approval-header.php   <- header, khi body > 100 dong
template-parts/emails/approval-footer.php   <- footer (legal, unsubscribe)
template-parts/emails/data-approval.php     <- dg_approval_email_data()
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
> *"1 concern = 1 file. Không tách nhỏ, không duplicate token."*

**CSS** — phẳng trong `assets/css/`, không folder con:

- Global: `style.css` (token `:root` — **single source of truth**), `main.css` (base/typography/layout), `woocommerce.css` (conditional). Không có file `responsive.css` — breakpoint nằm **cùng file chủ sở hữu** của component (vd `header-nav.css` §4b, `home.css` §RESPONSIVE, `cart.css` §640px) để tránh rule mồ côi không ai enqueue.
- Page-specific: `{page-slug}.css` trùng slug template (`template-shop.php` -> `shop.css`). **1 page = 1 file**; > 1000 dòng thì bàn trước.
- Header comment: `/* ==== DRAGON GLOW — {PAGE} STYLES ==== */`.

**Token** (chi tiết ở §8a, §8c):

- Tất cả token ở `style.css` `:root`. **KHÔNG** duplicate ở page CSS.
- Dùng ở nhiều trang -> primitive + semantic global. Một trang -> semantic scoped.

**JS** — `assets/js/`, mỗi file 1 module gắn 1 feature/page:

- Top-level: `{page-slug}.js` (cặp với CSS cùng tên), `main.js` (global).
- `lib/` cho module shared (dùng ≥ 2 trang) hoặc global `window.DG*`.
- Enqueue: mỗi file top-level 1 handle `dg-{slug}`, dependency `dg-main`.

**Cache busting:** sửa CSS/JS -> bump `DG_VERSION` (`functions.php`). WP nối `?ver=`.
#### 5e. Styling Strategy — CSS file, không inline (trừ 4 ngoại lệ)
> *"Style thuộc file CSS, không HTML template."*

- 🚫 Cấm `style="..."` và `<style>` block trong template.
- ✅ Luôn tạo class BEM ở page CSS, apply qua class.

**4 ngoại lệ hợp lệ:**

| # | Use case | Ví dụ |
|---|---|---|
| 1 | CSS variable bind runtime data | `style="--progress: 60%;"` |
| 2 | Dynamic layout value | `style="grid-column: span 2;"` |
| 3 | SVG inline (không load được CSS ngoài) | `<svg style="fill: currentColor;">` |
| 4 | Email HTML (client không load CSS ngoài) | `style="color:#fff;"` trong email |

**Không phải ngoại lệ:** `style="color: blue;"`, `style="margin: 10px;"`, hard-code màu, `<style>` block — tất cả phải thành class + token.

**JS runtime style (được phép):** `el.style.transform` (animation), `el.classList.toggle("is-active")` (ưu tiên hơn inline), `el.style.setProperty("--custom", v)` (scoped dynamic).

**Customizer runtime CSS:** dùng `wp_add_inline_style` trong `inc/enqueue/styles.php`, **không** trong template.
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
> *"Define once, use everywhere. Hard-code = tech debt."*

**Ba lớp token** (Primitive → Semantic → Component), tất cả sống ở `style.css` `:root`:

```css
/* Layer 1 — Primitive: raw values only */
--color-accent-gold: #C9A24B;
--gold-rgb: 201, 162, 75;

/* Layer 2 — Semantic: purpose-driven, cross-page */
--filter-item-text-active: var(--color-accent-gold);
--filter-item-bg-active: rgba(var(--gold-rgb), 0.1);

/* Layer 3 — Component: state-specific */
--filter-item-font-weight-active: 600;
```

**Quy tắc cứng:**

- Component CSS chỉ dùng `var(--token)`. Không `#hex`, không `rgb()`, không `px` cho spacing/typography.
- Token dùng ≥ 3 trang → semantic ở `style.css`. Chỉ một component → token riêng (§8c).
- Hard-code **không bao giờ** trong production. Prototype thì phải có `/* TODO: tokenize */`.
- Đổi style: sửa **token trước**, component sau. Rồi bump `DG_VERSION`.

**Refactor hard-code → token:** grep `rgba|rgb|#[0-9a-f]` → group theo concern → define token ở `style.css` → replace bằng `var()` → visual regression → bump `DG_VERSION`.
#### 8b. KHÔNG dùng Tailwind Forms plugin — tự viết reset trong CSS theme
> *"Xoá root cause tốt hơn tăng specificity ở từng input."*

**Quy tắc (áp dụng cho code mới):**

- 🚫 **CẤM** thêm `?plugins=forms` (hay Tailwind CDN plugin nào) vào URL `cdn.tailwindcss.com` ở `inc/enqueue/scripts.php`.
- 🚫 **CẤM** style input/select/textarea mới bằng Tailwind utility trực tiếp (`border-0 border-b ... focus:ring-0`) rồi dựa vào reset của `@tailwindcss/forms`.
- ✅ **Tự viết** reset trong CSS trang theo BEM (`dg-{page}__input`) — theme vốn đã làm vậy ở `account.css`, `shop.css`, `cart.css`.

```css
/* ✅ tự viết reset, không phụ thuộc Tailwind Forms */
.dg-contact-form__select {
  appearance: none;
  -webkit-appearance: none;
  border: 1px solid var(--token-border);
  background-color: var(--token-surface);
}
.dg-contact-form__select:focus { border-color: var(--token-border-focus); }
```

**Lý do:** Tailwind nạp qua `<script>` CDN, tự inject `<style>` vào `<head>` **lúc runtime** → luôn đứng sau CSS theme, thắng khi specificity hoà (`[type="text"]` = `.dg-*__input` = 0,1,0). Giữ `plugins=forms` là chấp nhận rủi ro đè `border`/`background` của mọi input mới.

**Ngoại lệ giữ nguyên:** `template-contact.php` vẫn dùng Tailwind utility trực tiếp — sơ xuất cũ, không sửa. Quy tắc này chỉ áp dụng cho input/select/textarea **mới**.
#### 8c. Component Style Isolation — token riêng cho component, KHÔNG mượn token global
> *"Mượn token global = sửa theme chỗ khác, component này đổi theo bất ngờ."*

**Quy tắc:** cần style riêng cho một component → tạo token riêng. **KHÔNG** dùng `--color-secondary`, `--color-tertiary`, `--color-accent` cho style cục bộ.

**Ba cách sai** (đều dẫn tới style pollution):

```php
<!-- sai 1: Tailwind class trỏ token global -->
<div class="bg-secondary text-secondary-container">

<!-- sai 2: component CSS mượn token global -->
.dg-hero__badge { color: var(--color-secondary); }
```

```css
/* sai 3: hard-code rồi tưởng là đã tách */
.dg-hero__title { color: #a9371e; }
```

**Cách đúng — 3 lớp:**

```css
/* style.css :root — Primitive (chỉ khi theme chưa có màu này) */
--warm-reddish-brown: #a9371e;
--coral-accent: #fd7455;

/* style.css :root — Semantic, scoped cho hero (KHÔNG tái dùng trang khác) */
--hero-gradient: linear-gradient(to right, var(--warm-reddish-brown), var(--coral-accent), var(--warm-gold));
--hero-badge-bg: rgba(var(--gold-rgb), 0.1);
--hero-badge-text: var(--gold-500);
```

```css
/* home.css — component rule, dùng token scoped */
.dg-home-hero__gradient-text { background-image: var(--hero-gradient); }
.dg-home-hero__badge { background: var(--hero-badge-bg); color: var(--hero-badge-text); }
```

```php
<!-- template dùng class BEM, KHÔNG dùng Tailwind gradient -->
<span class="dg-home-hero__gradient-text">
```
### Chọn lớp token — 4 câu hỏi

Hỏi lần lượt, dừng ở câu đầu tiên trả lời YES:

| # | Câu hỏi | YES → |
|---|---|---|
| 1 | Dùng ở **≥ 3 trang**? | Semantic ở `style.css`, dùng chung (vd `--color-accent`) |
| 2 | Đổi token global có **ảnh hưởng component khác**? | Token scoped ở `style.css` (vd `--hero-gradient`), chỉ dùng trong CSS trang đó |
| 3 | Đang dùng token global (`secondary`, `tertiary`, `accent`) mà component **phải** theo theme? | Giữ token global — hợp lý (vd error message luôn dùng `--error`) |
| 4 | Giá trị đang **hard-code** (`#a9371e`, `rgba()`, `px`)? | Tạo primitive + semantic token ở `style.css` |

Không YES câu nào → đã đúng.

**Token sống ở đâu:**

| Loại | Định nghĩa | Dùng trong | Đổi thì ảnh hưởng |
|---|---|---|---|
| Primitive | `style.css :root` | Semantic token | Toàn theme |
| Semantic (global) | `style.css :root` | Nhiều file CSS trang | Nhiều trang |
| Semantic (scoped) | `style.css :root` | Một file CSS trang | Một section |
| Component | `{page}.css` | Template qua class BEM | Chỉ styling component đó |

**Checklist trước khi style component mới:**

- [ ] Màu / size / spacing mới đã có token trong `style.css` chưa?
- [ ] Token đó scoped đúng chưa (`--hero-*`, `--shop-filter-*`)?
- [ ] Có dùng Tailwind class tham chiếu token global không? → thay bằng class BEM + rule dùng component token
- [ ] Đổi style này có ảnh hưởng trang khác không? → nếu có, tách token riêng
- [ ] Còn giá trị hard-code nào không? → extract thành token
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

> Đầy đủ ở `docs/CHANGELOG.md`. Dưới đây chỉ là mốc gần nhất — lịch sử không cần nạp mỗi phiên.

- `2026-11-08e`: My Account → Orders — **sửa bảng lịch sử đơn hàng hiển thị rất xấu ở kích thước mobile** (≤640px). Yêu cầu: tối ưu `<table class="woocommerce-orders-table … account-orders-table">` cho mobile, không đụng tablet/desktop, chuẩn enterprise WordPress, không commit git. Vấn đề (đo bằng Playwright trên mock markup thật, `tmp-resp/account-pagination/measure-orders-mobile.js`): block mobile cũ ở `assets/css/account.css` (`@media (max-width: 768px)`) chỉ style `tbody td` — nhưng WooCommerce render cột đầu là **`<th scope="row">`** (ô Order), không phải `<td>`. Hậu quả đo được ở 390px: ô Order giữ `display: table-cell` (55×47px) → **tự đứng một dòng riêng ở góc trên**, không có nhãn, không có padding đồng nhất, lệch với 4 ô `td` bên dưới (đã là flex, xếp dọc từ y=116) → bảng trông vỡ. Thêm nữa `tbody td:first-child` không còn trỏ vào ô Order (nó là `<th>`) nên **ô Date bị nhận nhầm style "cột đầu"** (bold + màu gold). Root cause: selector dựa trên `:first-child`/`:last-child` thay vì class cột, cộng bỏ sót `<th>`. Sửa — (1) `assets/css/account.css`: thêm block `@media (max-width: 640px)` mới (giữ nguyên block 768px cho tablet 641–768px): `thead` **visually-hidden** (`clip-path: inset(50%)`) thay vì `display: none` → giữ tên cột trong cây trợ năng; `tbody th` + `tbody td` cùng nhận `display: flex; justify-content: space-between; gap: 16px; min-height: 44px` → mọi ô cùng x, cùng rộng, xếp dọc đều; ô Order (`th.woocommerce-orders-table__cell-order-number`) làm **header của card** (số đơn trái, nền riêng, rule đậm hơn) và `::before { content: none }` (không nhãn trùng); ô Date bỏ style "cột đầu" (`font-weight: 400`); ô Actions làm footer, nút "View" full-width + `min-height: 44px` (WCAG 2.5.5); bỏ `transform: translateX(2px)` của `:hover` (không có ý nghĩa trên touch). (2) `woocommerce/myaccount/orders.php`: bọc giá trị ô Total trong `<span class="woocommerce-orders-table__cell-value">` → nếu không, flex tách `<span class="woocommerce-Price-amount">` và text node "for 6 items" thành 2 item. (3) `style.css` `:root`: thêm 9 token component `--account-orders-card-*` / `--account-orders-cell-*` / `--account-orders-button-min-height` (theo §8a token-first, không hard-code trong file trang). Kiểm chứng: `php -l woocommerce/myaccount/orders.php` → không lỗi; Playwright `tmp-resp/account-pagination/verify-orders-mobile.js` (13 viewport 320→1440px) → **ALL PASS**: mobile 320/375/390/414/480/600/639/640px → `thead` còn trong a11y tree, `th`+`td` đều flex, mọi ô cùng x/cùng rộng/xếp dọc, ô Date không còn bold, nút View **44px** ≥ 44px, `docOverflow = 0`; **tablet 641/768px → không đổi** (`th` vẫn `table-cell`, `rowH` 309px, thead `none`); **desktop 1024/1280/1440px → không đổi** (`thead` hiện, mọi ô `table-cell`, `rowH` 72px); so sánh ảnh trước/sau (`orders-before-390.png` ↔ `orders-after3-390.png`) → sau là card gọn gàng, đồng bộ bề rộng. `DG_VERSION` → `1.16.29`. Không commit git.

- `2026-11-08d`: My Account → Orders — **sửa phân trang desktop không hiện dấu `…` (ellipsis)** khi khách có ≤13 trang. Yêu cầu: sau khi tối ưu desktop, người dùng báo *"trong hình là kết quả sau khi bạn làm thì paginate vẫn chưa hiển thị ..."* (kèm ảnh chụp). Không commit git. Chẩn đoán (theo `systematic-debugging`, đo pixel ảnh người dùng): ảnh 1024×514 là ảnh chụp **viewport 1536px đã thu nhỏ còn 1024px** (tỉ lệ 2/3, pill 24px ảnh ↔ 36px CSS); dựng lại 14 cụm pixel trong dải `y=359..382` → #1 là pill active (trang 1), #2–#13 là **11 số (2→12)**, #14 là nút Next → **12 trang, KHÔNG có ellipsis**, khớp 100% với `max_num_pages = 12` của store (113 đơn). Root cause: `woocommerce/myaccount/orders.php` còn nhánh `if ( $total_pages <= $full_limit ) { $visible_wide = range( 1, $total_pages ); }` với `dg_account_orders_pagination_full_limit()` = 13 → 12 ≤ 13 → **cố tình bỏ qua window rộng** và in hết 12 số; điều này **lệch với WooCommerce core** (`paginate_links()`, `mid_size = 2` → 12 trang phải ra `1 2 3 … 12`). Sửa: (1) `inc/woocommerce/account/orders.php` — **xoá hẳn** helper `dg_account_orders_pagination_full_limit()` (kèm filter `dg_account_orders_pagination_full_limit`) vì không còn ngưỡng "hiện đủ"; guard duy nhất còn lại là `$total <= 5` trong `dg_account_orders_page_window()` → danh sách ≤5 trang vẫn hiện đủ, không collapse. (2) `woocommerce/myaccount/orders.php` — xoá nhánh override `$visible_wide = range(1, $total_pages)` → **cả 2 window (compact `current±1`, wide `current±2`) luôn collapse** giống hệt core. (3) `assets/css/account.css` — sửa comment block `@media (min-width: 1280px)` bỏ claim "13 số vừa 1 hàng 745px" (không còn đúng) và mô tả lại hành vi mới. Không đụng CSS layout, không đụng JS. Kiểm chứng: PHP CLI test `tmp-resp/account-pagination/test-window-desktop.php` (bỏ case `full_limit = 13`, thêm case 12 trang khớp ảnh người dùng: `cur=1 total=12 r=2 → 1,2,3,12`, `cur=6 → 1,4..8,12`, `cur=12 → 1,10,11,12`, và case "≤5 trang không collapse") → **ALL PASS**; `php -l` cả 2 file → không lỗi cú pháp; **đo trực tiếp site live** (Chrome DevTools MCP, tab đã đăng nhập, 113 đơn / 12 trang): 1440px trang 1 → `1 2 3 … 12` (**trước: `1 2 3 4 … 12` thiếu dấu `…`**); 1280px trang 6 → `1 … 4 5 6 7 8 … 12`; 1280px trang 12 → `1 … 10 11 12` (`aria-current="page"=12`, đúng 1 nút Previous, không có Next); 1024px → `1 … 5 6 7 … 12` (**không đổi**); 390px → `1 … 5 6 7 … 12` (**không đổi**), `pagH` 93px (Prev/Next 45px ≥ 44px WCAG 2.5.5); ở 1280px dải số 362px ≤ cột 776px → **1 dòng, không tràn**, `docOverflow = 0` ✓. `DG_VERSION` → `1.16.28`. Không commit git.
- `2026-11-08c`: My Account → Orders — **sửa phân trang vỡ ở kích thước desktop** khi lịch sử đơn hàng dài. Yêu cầu: tối ưu phân trang cho desktop giống mobile/tablet, chuẩn enterprise WordPress, không commit git. Vấn đề (đo bằng Playwright trên layout thật ở 1280px — desktop hẹp nhất, cột nội dung 776px): hàng gồm `Previous` (83px) + hàng số + `Next` (58px). **≤13 trang** → vừa khít 1 dòng (745px ≤ 776px), đẹp. **Từ 14 trang** → hàng số vượt cột → `.woocommerce-pagination` (`flex-wrap: wrap`) **gãy thành 2–6 dòng lệch lạch**: `Previous` dòng 1, số trang vắt dòng 2, `Next` rơi dòng cuối (đo 50 trang: `prevRow/numsRow/nextRow` = 0/1/5, cao khối 262px) — tình huống phổ biến ở store enterprise (khách 100+ đơn). Sửa theo đúng pattern window của WooCommerce core (`paginate_links()`, `mid_size`): (1) `inc/woocommerce/account/orders.php` — `dg_account_orders_page_window( int $current, int $total, int $radius = 1 )` nhận `$radius` + thuật toán 3 nhánh giống core; thêm `dg_account_orders_pagination_full_limit(): int` (mặc định 13, có `apply_filters`) → số trang vẫn nằm trọn 1 dòng ở desktop. (2) `woocommerce/myaccount/orders.php` — render **2 tầng window** (compact `current±1` mobile/tablet, wide `current±2` desktop); mỗi trang tag riêng `data-dg-page-out-compact` / `data-dg-page-out-wide` + **2 bộ ellipsis riêng** `data-dg-page-ellipsis="compact"|"wide"` → đúng 1 bộ hiển thị theo breakpoint, không lệch dấu. Desktop dùng window rộng khi tổng trang > 13, ngược lại giữ **đủ 12 số** như cũ. (3) `assets/css/account.css` — block `@media (min-width: 1280px)` mới (`nowrap` + ẩn `[data-dg-page-out-wide]` + hiện ellipsis `wide`); block `@media (max-width: 1279px)` dùng `[data-dg-page-out-compact]` + ellipsis `compact`; rule ellipsis gộp ra khối chung. Kiểm chứng: PHP CLI test `tmp-resp/account-pagination/test-window-desktop.php` (logic window thật, không phải bản port JS) → **ALL PASS**; Playwright `verify-desktop.js` → 12 trang ở 1280/1366/1440/1600/1920px → **đủ 12 số, 1 dòng, 0 ellipsis**; 50 trang → `1 … 23 24 25 26 27 … 50`, **1 dòng, không tràn**; đầu/cuối/second → `1 2 3 … 50` / `1 … 48 49 50` / `1 2 3 4 … 50`; hồi quy mobile 320–640px + tablet 641–1279px → **không đổi**; a11y `aria-current` + `aria-hidden` giữ nguyên; reduced-motion → transition 0s; edge case 50 trang → không tràn ✓; `verify-edge-desktop.js` → **ngưỡng 13/14 trang @1280px**: 13 trang = đủ 13 số (dải số 577px ≤ cột 776px), 14 trang → tự collapse `1 … 5 6 7 8 9 … 14` (dải số 364px), đều 1 dòng, không tràn ✓; **đo trực tiếp site live** (Chrome DevTools MCP): 1440px → đủ 12 số, 1 dòng, cột 776px; 1280px → đủ 12 số, 1 dòng; 834px/390px → `1 2 … 12` (không đổi, không tràn) ✓. `DG_VERSION` → `1.16.27`. Không commit git.
- `2026-11-08b`: My Account → Orders — **sửa phân trang vỡ ở kích thước tablet** (641–1279px). Yêu cầu: tối ưu phân trang cho tablet giống mobile, chuẩn enterprise WordPress, không commit git. Vấn đề (đo bằng Playwright trên layout thật, có sidebar 280px từ 1024px): (1) `.woocommerce-pagination` là `display: flex; flex-wrap: wrap` → `Previous` + hàng 12 số (~530px) + `Next` (~589px tổng) **vượt cột nội dung** (561px ở 641px, 523px ở 1024px) → wrap thành **2–3 dòng lệch lạch** ở 641/768/834/1024/1080/1112px; (2) ở **1024px hàng số tràn khỏi panel** (523px < 530px). Sửa: **tái dụng lại** cơ chế window sẵn có (không viết lại markup) — `assets/css/account.css` thêm block `@media (max-width: 1279px)`: ẩn `[data-dg-page-hidden]` + hiện `.dg-account-pagination__ellipsis` (markup window `dg_account_orders_page_window()` trong `inc/woocommerce/account/orders.php`), `.woocommerce-pagination { flex-wrap: nowrap }` + `.woocommerce-pagination__numbers { flex-wrap: nowrap; flex-shrink: 1; min-width: 0 }` → **ép cả khối 1 hàng duy nhất**; `.woocommerce-pagination__numbers` (mặc định) thêm `flex-wrap: wrap; min-width: 0` → desktop **tự wrap xuống dòng 2** thay vì tràn khi khách có **> 12 trang**. `style.css` `:root` đồng bộ 3 token pill về giá trị desktop (34→36px, 34→36px, 13→14px) để tablet khớp đúng chiều cao/màu desktop. Desktop (≥1280px) **giữ nguyên 12 số** — ngưỡng 1280px (không phải 1200px) vì đo thật: hàng đầy đủ cần 606px, cột nội dung ở 1200px chỉ 696px → **vừa sát 1px** nên dễ wrap; từ 1280px cột rộng 776px → dư 170px. Kiểm chứng Playwright (`tmp-resp/account-pagination/final-verify.js`, 20 viewport 320→1920px): mobile 320–640px → không tràn ngang, Prev/Next cùng hàng, touch target 45px ≥ 44px (WCAG 2.5.5), window `1 … 5 6 7 … 12`; tablet 641/768/834/900/1023/1024/1080/1112/1200/1279px → **1 hàng duy nhất**, không tràn panel; desktop 1280/1440/1920px → 12 số đầy đủ, ellipsis ẩn; trang đầu/cuối → đúng 1 nút điều hướng; `prefers-reduced-motion` → transition 0s; edge case **50 trang** ở 390/834/1440px → không tràn ✓. `aria-current` + `aria-hidden` giữ nguyên. `DG_VERSION` → `1.16.26`. Không commit git.
- `2026-11-08a`: Shipping & Returns — **thanh cuộn của modal Return giống hệt modal Apply** (Careers). Yêu cầu: giao diện thanh cuộn của `<div class="dg-rm-dialog" role="document">` phải giống `<div class="dg-apply-dialog" role="document">`. Vấn đề: `.dg-rm-dialog` có `overflow-y: auto` nhưng **không có** rule `::-webkit-scrollbar` nào → hiện thanh cuộn mặc định của hệ điều hành, không đồng bộ brand. Sửa: `style.css` `:root` (section "Shipping & Returns Component") thêm 4 token `--sr-scrollbar-size: 8px`, `--sr-scrollbar-track: var(--neutral-warm-100)`, `--sr-scrollbar-thumb: var(--neutral-400)`, `--sr-scrollbar-thumb-hover: var(--neutral-500)` — **cùng bảng màu** với `--a-surface-low`/`--a-outline`/`--a-outline-strong` của Careers (cả 2 đều trỏ về `--neutral-warm-100`/`--neutral-400`/`--neutral-500`); `assets/css/shipping-returns.css` thêm block `::-webkit-scrollbar` cho `.dg-rm-dialog` (width/track/thumb/thumb:hover) **mirror y hệt** rule của `.dg-apply-dialog` trong `careers.css`; `careers.css` **không đổi** (baseline tham chiếu). Ghi chú: đo pixel trực tiếp **không đáng tin** vì Chrome dùng *overlay scrollbar* (`offsetWidth - clientWidth === 0`) → cách kiểm chứng đúng là đọc `getComputedStyle(el, '::-webkit-scrollbar')` / `('::-webkit-scrollbar-thumb')` / `('::-webkit-scrollbar-track')` (đã xác nhận `var()` resolve bình thường trong pseudo-element scrollbar). Kiểm chứng Playwright (site live, inject file từ disk): baseline Apply = `width 8px`, thumb `rgb(212,194,194)` r4px, track `rgb(250,249,246)` r4px, transition `background 0.25s`; modal Return ở 320/390/768/1280px → **computed style giống hệt baseline từng thuộc tính** ✓; scroll hoạt động (`maxScrollTop` 299/83 ở mobile) ✓; nút Close vẫn đóng modal ✓; không `pageerror` ✓. `DG_VERSION` → `1.16.25`. Không commit git.
- `2026-11-07n`: Shipping & Returns — **sửa nút Close (×) và Cancel của modal Return không hoạt động**. Yêu cầu: `<button class="dg-rm-close" data-rm-close>` và `<button class="dg-rm-btn dg-rm-btn--secondary" data-rm-close>` bấm không có tác dụng. Vấn đề — có **3 nguyên nhân độc lập**, lộ ra khi chẩn đoán bằng hit-test (`document.elementFromPoint`) + bắt `pageerror` trên site live: (1) **CSS — nút Close bị header đè**: `.dg-rm-close` là `position: absolute` không `z-index`, còn `.dg-rm-header` là `position: relative` và đứng **sau** trong DOM → header vẽ đè lên nút; ở 390px `elementFromPoint` trả về `header.dg-rm-header` (Playwright: *"header intercepts pointer events"*) → click thật của user **không trúng nút**. (2) **JS — `EASE is not defined`**: `shipping-returns.js` dùng biến `EASE` trong `open()`/`close()` nhưng **không khai báo** `const EASE` (khác 14 file JS khác trong theme) → `ReferenceError` ném ra giữa chừng `open()`. (3) **JS — `delay` non-finite**: `animate(children, …, { delay: … ? { each: 0.08 } : 0 })` — Motion v11 không nhận object `{ each }` (đó là cú pháp của `stagger()`) → *"Failed to read the 'delay' property from 'EffectTiming': The provided double value is non-finite"* → vòng reveal `[data-sr-group]` chết. Sửa: `assets/js/shipping-returns.js` thêm `const EASE = [ 0.16, 1, 0.3, 1 ];`, import thêm `stagger` từ Motion và đổi `delay: … { each: 0.08 }` → `stagger(0.08)`; `assets/css/shipping-returns.css` thêm `z-index: 2` cho `.dg-rm-close` để nằm trên `.dg-rm-header`. Kiểm chứng Playwright (site live, inject nguyên file JS + CSS từ disk): 320/390/768/1280px → hit-test nút Close `clickable: true`, click Close / click Cancel / ESC / click overlay **đều đóng modal** (`hidden: true`, `dataOpen: false`, `bodyLock: false`) ✓; focus vào `.dg-rm-close` khi mở và **trả về trigger** `.dg-sr-returns-cta` khi đóng ✓; focus trap 12 lần Tab **luôn trong** dialog ✓; `prefers-reduced-motion: reduce` → hành vi giữ nguyên ✓; **không còn `pageerror`** (trước: 2× `EASE is not defined` + 1× `delay` non-finite) ✓. `DG_VERSION` → `1.16.24`. Không commit git.
- `2026-11-07l`: Careers — **2 nút trong modal Apply nằm cùng một hàng ngang ở mobile** (chỉ mobile; tablet/desktop giữ nguyên). Yêu cầu: trong `<div class="dg-apply-actions">`, nút `Cancel` và `Send application` phải cùng 1 dòng ngang ở mobile. Vấn đề: `.dg-apply-actions` khai báo `flex-direction: column-reverse` ở mobile → 2 nút xếp **dọc**, chỉ từ `min-width: 640px` mới sang hàng ngang. Sửa `assets/css/careers.css`: `.dg-apply-actions` → `flex-direction: row` (mặc định), khối `@media (min-width: 640px)` giữ nguyên `justify-content: flex-end` (tablet/desktop **không đổi**); thêm khối `@media (max-width: 639px)`: `align-items: stretch` + `gap: 0.625rem`, nút `display: flex` (canh giữa 2 chiều) + `align-self: stretch` (đè lại `align-self: flex-start` kế thừa từ `.dg-careers-btn--secondary` → 2 nút **bằng nhau chiều cao**) + `min-height: 44px` (touch target WCAG 2.5.5) + `font-size: 0.75rem` + `letter-spacing: 0.04em` + `padding: 0.75rem 0.625rem`; nút phụ `--secondary` → `flex: 0 0 auto` (rộng theo nội dung), nút chính `--primary` → `flex: 1 1 auto` + `min-width: 0` (chiếm phần còn lại → nhãn "SEND APPLICATION" luôn **1 dòng**, không xuống dòng ở 320px). Kiểm chứng Playwright (site live, inject nguyên file `careers.css` từ disk, mở modal qua `[data-apply-trigger]`): 320/360/390/639px → `flexDirection: row`, 2 nút cùng hàng, cùng cao **44px**, `submitLines: 1`, gap 10px, không overlap, nằm trong dialog, không tràn ngang, không cắt chữ, touch target ≥ 44px ✓; 640/768/1280px → **không đổi** (căn phải, gap 14px, cancel 118.2px / submit 201.5px) ✓; không `pageerror` ✓. `DG_VERSION` → `1.16.23`. Không commit git.
- `2026-11-07m`: Our Ingredients — **bỏ nền glass card của khối chữ Traceable** (chữ nằm trực tiếp trên ảnh, không còn nền/viền/đổ bóng/blur). Yêu cầu: xoá background của `<div class="dg-oi-traceable-content">`. Vấn đề phát hiện khi đo pixel thật: ảnh `traceable.webp` **không đồng nhất** — nền kem sáng (luma ~220) ở trái nhưng chai serum tối (luma ~57) ở giữa. Bỏ nền card hoàn toàn → chữ tối nằm trên chai tối → contrast **1.14–1.89:1** (fail WCAG AA); ảnh cũng không đủ tối để dùng chữ trắng (nền kem sáng → 1.16–2.65:1). Sửa: thay vì nền đục (card) hoặc overlay tối ảnh, dùng **halo sáng quanh nét chữ** (`text-shadow`). `assets/css/our-ingredients.css` — `.dg-oi-traceable-content` bỏ `background`/`border`/`border-radius`/`box-shadow`/`backdrop-filter` + khối `@supports`, bỏ `padding` (chỉ giữ `margin`); 4 rule text thêm `text-shadow: var(--oi-traceable-halo)` (headline dùng `--oi-traceable-halo-wide`). `style.css` `:root` — xoá 6 token card (`--oi-traceable-card-bg/-bg-solid/-border/-radius/-shadow/-blur`), thêm `--oi-traceable-halo` + `--oi-traceable-halo-wide`; `--oi-traceable-rule` alpha 0.25→0.35, footnote 0.88→0.92. Kiểm chứng Playwright (site live, inject nguyên file `our-ingredients.css` + token `--oi-traceable-*` từ `style.css` trên disk; đo contrast bằng pixel thật: glyph tối nhất vs halo sáng quanh nó): card `background: rgba(0,0,0,0)`, `border: 0px solid`, `box-shadow: none`, `backdrop-filter: none`, `padding: 0px` ✓; contrast thực tế 320px → headline **9.88**/body **4.89**/country **13.37**/footnote **6.86**; 390px → 7.89/5.09/13.77/6.40; 640px → 8.99/5.36/9.53/6.45; 768px → 16.36/8.92/16.37/7.35; 1024px → 13.72/7.76/14.71/6.70; 1280px → 14.32/7.90/14.76/6.80 → **đạt WCAG AA** mọi viewport ✓; không tràn ngang ✓; không `pageerror` ✓. `DG_VERSION` → `1.16.23`. Không commit git.
- `2026-11-07k`: Our Ingredients — **bỏ lớp overlay tối của ảnh section Traceable**, khối chữ chuyển sang glass card. Yêu cầu: xoá lớp overlay trong `<div class="dg-oi-traceable-bg">`. Vấn đề phát hiện khi đo pixel thật: overlay `rgba(49,48,48,0.6)` + `mix-blend-mode: multiply` làm tối ảnh ~51%, nhưng **chữ trong section là màu tối** — rule `color: var(--oi-bg)` trỏ tới token đã bị xoá lúc migrate (`--oi-bg` nằm trong "OLD tokens removed") → `var()` rơi về `unset` → kế thừa `--color-on-surface` (#1b1c1a) → **chữ tối trên nền ảnh tối**. Đo contrast thực tế (chụp nền thuần + median màu tại bbox): headline **2.61:1**, body **1.68:1**, country **3.73:1**, footnote **3.22:1** → fail WCAG AA ở cả 390px lẫn 1280px. Sửa: `template-parts/our-ingredients/section-traceable.php` xoá hẳn div overlay; `assets/css/our-ingredients.css` xoá rule `.dg-oi-traceable-overlay`, `.dg-oi-traceable-content` nay là glass card (nền `--oi-traceable-card-bg` + `backdrop-filter: blur(18px)`, fallback nền đặc trong `@supports` theo pattern `contact.css`, border/radius/shadow, padding `clamp(1.5rem,4vw,2.75rem)`), sửa token chết `var(--oi-bg)` → `--oi-traceable-text` (headline/country), body → `--oi-traceable-text-muted`, country border → `--oi-traceable-rule`, footnote (màu sáng, vô hình trên nền sáng) → `--oi-traceable-footnote`; `style.css` `:root` thêm bộ token component `--oi-traceable-*` (Layer 3, không hard-code màu trong file trang). Kiểm chứng Playwright (site live, đo contrast bằng pixel thật): **trước** 2.61/1.68/3.73/3.22 fail AA ✗ → **sau** headline **14.89**, body **8.39**, country **15.89**, footnote **5.65** đạt AA ✓ (AAA cho headline/body/country); 320/390/640/768/1024/1280px không tràn ngang ✓, `backdrop-filter` hoạt động ✓, reduced-motion → nội dung `opacity: 1` ✓, không `pageerror` ✓. `DG_VERSION` → `1.16.22`. Không commit git.
- `2026-11-07j`: Single product — **thoát lightbox thì ảnh bên ngoài giữ nguyên ảnh vừa xem** (parity với Gift Cards). Yêu cầu: lightbox đang ở ảnh 3 → thoát ra thì ảnh chính `#dg-main-image` + thumbnail active phải là ảnh 3. Nguyên nhân: `onChange` của lib chạy ở **mọi** lần render kể cả lúc mở → controller không phân biệt "mở lightbox" (phản ánh state hiện tại) với "user điều hướng" (state phải đổi theo). Sửa: `assets/js/lib/zoom-lightbox.js` → `render(notify)`, `onChange` chỉ chạy khi `notify === true` (prev/next); `open()` gọi `render(false)` (mở không đổi state trang), `next()`/`prev()` gọi `render(true)` (user điều hướng → trang theo). `assets/js/product.js` → `items[]` giữ thêm tham chiếu `thumb`; thêm `onChange` gọi lại `dgChangeImage(item.thumb)` (tái dùng hành vi cache-aware + cập nhật thumbnail active), guard bỏ qua nếu ảnh ngoài đã đúng; `resolveCurrentIndex()` ưu tiên **thumbnail active** (state hiển thị) thay vì so sánh `src` (`src` có thể chưa kịp cập nhật), `src` chỉ là fallback. `assets/js/gift-cards-zoom.js` → `resolveCurrentIndex()` ưu tiên **`window.DGGiftValue.get()`** (state mệnh giá form gửi), `src` chỉ là fallback. Kiểm chứng Playwright (site live): mở (ảnh 1) → `next`×2 → ảnh ngoài + thumbnail active = `shot3` ✓; `Escape` → ảnh ngoài **vẫn `shot3`** (không revert) ✓; mở lại → counter `3 / 4` ✓; `←` → `shot2` ✓; wrap → `shot4` ✓; thoát giữ `shot4` ✓; không `pageerror` ✓; hồi quy Gift Cards (đồng bộ mệnh giá, giữ state, mở lại `3 / 4`) ✓ 390/1280px; hồi quy a11y (focus trap, reduced-motion, aria) ✓. `DG_VERSION` → `1.16.21`. Không commit git.
- `2026-11-07i`: Lightbox — **bộ đếm ảnh không còn dính sát đáy ảnh** (chuyển từ overlay sang flow layout). Vấn đề: `.dg-zoom-modal__counter` là `position: absolute; bottom: 1rem` → phủ lên vùng ảnh; khi ảnh cao khoảng cách thực tế chỉ còn ~14px → trông như bộ đếm bị dính vào ảnh. Sửa: `.dg-zoom-modal` → `display: flex; flex-direction: column; gap: var(--zoom-counter-gap)`; `.dg-zoom-modal__stage` `flex: 1 1 auto; min-height: 0`; counter bỏ `position: absolute` + `translateX(-50%)`, thêm `font-variant-numeric: tabular-nums` (số không nhảy khi đổi ảnh) → khoảng cách đáy ảnh ↔ counter luôn = `gap`, không phụ thuộc vị trí tuyệt đối. Mobile ≤639px: modal chuyển sang **CSS grid 2 hàng** (hàng 1 = ảnh, hàng 2 = `[prev] [counter] [next]`), `align-content: center` → khoảng trống dư chia đều trên/dưới; nút nav `position: static` + `grid-column` → không còn overlay lên bộ đếm. Token mới `--zoom-counter-gap: 1.25rem`; xoá `--zoom-stage-max-height-offset`. Kiểm chứng Playwright (đo `getBoundingClientRect`): gap đáy ảnh ↔ counter = **20px** ở viewport thấp (1280×560/600, 1440×700, 768×500), 29px ở 390px, 88–246px ở viewport cao (trước 14px) ✓; không tràn viewport ✓; mobile 390×844 cân đối (trống 269 trên / 278 dưới) ✓; hồi quy product + gift-cards ✓. `DG_VERSION` → `1.16.20`. Không commit git.
- `2026-11-07h`: Single product — **click vào ảnh chính `#dg-main-image` mở lightbox** + **tách lightbox thành lib dùng chung** (single product + gift cards). Yêu cầu: click ảnh trong `.glass-card` mở lightbox (trước chỉ nút `.dg-zoom-btn` mở được). Vấn đề kiến trúc: có 2 bản lightbox trùng lặp — `#dg-zoom-modal` (single product, inline markup, JS legacy `window.dgOpenZoom/dgCloseZoom/dgZoomPrev/dgZoomNext` với `setTimeout` lồng nhau) và `.dg-gift-zoom-modal` (gift cards, file JS riêng) → sửa 1 bên là lệch bên kia. Sửa: lib mới `assets/js/lib/zoom-lightbox.js` (classic, no deps) → `window.DGZoomLightbox.create({root, items, scope, resolveIndex, onChange})`; trigger dùng **event delegation** trên `document` (`[data-dg-zoom-trigger]`) → preview thẻ quà bị relocate trên mobile/tablet vẫn hoạt động; focus trap + `Escape`/`←`/`→` + restore focus + lock scroll. Partial mới `template-parts/global/zoom-modal.php` (gắn qua data attribute; phải include **sau `</main>`** vì `position: fixed` bị neo sai bởi transform của animation reveal). CSS mới `assets/css/zoom-lightbox.css` (BEM `.dg-zoom-modal__*`, không phụ thuộc Tailwind runtime cho trạng thái mở/đóng; mobile ≤639px nút nav xuống đáy). Token `--zoom-*` trong `style.css`, xoá `--gift-zoom-*`. `woocommerce/content-single-product.php`: ảnh chính thành trigger (`role="button"` + `tabindex="0"` + `aria-label`), bỏ `onclick="dgOpenZoom()"`/`onclick="dgChangeImage(this)"` → `data-dg-zoom-trigger`/`data-dg-thumb`, xoá hẳn markup modal inline. `woocommerce/single-product.php`: include partial mới. `assets/js/product.js`: **xoá hẳn** block zoom legacy (175 dòng) → `initZoomLightbox()` dùng lib; gallery từ `[data-dg-thumb]`, `resolveIndex()` đọc `src` hiện tại của `#dg-main-image` → mở đúng ảnh đang xem. `assets/js/gift-cards-zoom.js`: viết lại dùng lib, giữ đồng bộ mệnh giá (`onChange` → `DGGiftValue.set()`). `template-parts/gift-cards/section-zoom-modal.php`: **xoá**; `section-bento.php`: bỏ inline handler. `assets/css/single-product.css` + `gift-cards.css`: xoá block modal cũ (~210 dòng). `inc/enqueue/{styles,scripts}.php`: handle `dg-zoom-lightbox` (CSS global, JS dep của `dg-product` + `dg-gift-cards-zoom`). Kiểm chứng Playwright (`tmp-resp/zoom-lightbox/verify-*.js`, site live): click ảnh → mở (counter `1 / 4`, ảnh `shot1`, overflow hidden, focus nút đóng) ✓; nút zoom → mở ✓; `→` → `2 / 4` ✓; **focus restore** về `#dg-main-image` / về nút ✓; **focus trap** 5× Tab luôn trong modal ✓; đổi thumbnail → mở đúng `3 / 4` ≡ ảnh đang xem ✓; `Enter` kích hoạt ✓; `role=dialog`/`aria-modal`/`aria-live` ✓; reduced-motion (`transition: 0s`) ✓; 390px nav `48×48` ở đáy ✓; hồi quy gift-cards (đồng bộ mệnh giá, đóng giữ state, mở lại đúng index) ✓ 390/1280px. `DG_VERSION` → `1.16.18`. Không commit git.
- `2026-11-07g`: Gift Cards — **chuyển ảnh trong lightbox → đồng bộ toàn bộ state mệnh giá bên ngoài** (ảnh preview + chip active + hidden input form + nhãn giá nút "Add to Bag"). Vấn đề: đổi mệnh giá ở lightbox mà form vẫn giữ mệnh giá cũ → đơn hàng gửi sai. Sửa: tách state ra module classic độc lập `assets/js/lib/gift-value.js` (mới) — sở hữu DUY NHẤT ảnh preview + chip `[data-amount]` + `input[name="value"]` + `.dg-gift-submit-value`; API `window.DGGiftValue.get()/.set(amount)`. Lý do tách: `gift-cards.js` là ES module (import Motion CDN → CDN lỗi là skip cả module), `gift-cards-zoom.js` là classic script độc lập → module classic độc lập là điểm giao duy nhất. `gift-cards.js` — **xoá hẳn** `initValueChips()` + `swapCardImage()` (logic trùng lặp) → chip mệnh giá do `gift-value.js` gắn listener, vẫn hoạt động kể cả khi ES module bị skip (Motion CDN lỗi). `gift-cards-zoom.js` — `render()` gọi `DGGiftValue.set(item.amount)` (đồng bộ live, không chờ đóng). `inc/enqueue/scripts.php` — handle mới `dg-gift-value`, `dg-gift-cards-zoom` phụ thuộc nó. Kiểm chứng Playwright (`tmp-resp/gift-zoom/sync-*.js`): chuyển ảnh → preview/chip/hidden/nhãn khớp, đóng lightbox giữ state (không revert), mở lại đúng index, mở-đóng ngay không đổi state, **Motion CDN bị chặn → chip + lightbox + đồng bộ vẫn chạy** ✓. `DG_VERSION` → `1.16.17`. Không commit git.
- `2026-11-07f`: Gift Cards — **click vào ảnh thẻ mở lightbox + nút chuyển ảnh prev/next + bộ đếm `1 / 4`**. `template-parts/gift-cards/section-bento.php`: ảnh `.dg-gift-card-image` nay là trigger (`role="button"` + `tabindex="0"` + `aria-label`). `template-parts/gift-cards/section-zoom-modal.php`: thêm `__nav--prev`/`__nav--next` (`chevron_left`/`chevron_right`) + `__counter` (`aria-live="polite"`). `assets/js/gift-cards-zoom.js`: dựng gallery từ `data-card-{amount}` (duyệt `preview.attributes`), `resolveInitialIndex()` đọc `getAttribute('src')` của `.dg-gift-card-image` (vì `gift-cards.js` set `img.src`), wrap-around 2 chiều, click ảnh → mở modal, `ArrowLeft`/`ArrowRight` chuyển ảnh, `Escape` đóng. `assets/css/gift-cards.css`: style `__nav`/`__counter` (token `--gift-zoom-close-bg`), khai báo lại `[hidden]`, `cursor: zoom-in` cho ảnh, mobile ≤639px chuyển nút nav xuống đáy (ảnh chiếm gần hết bề ngang), `__stage` `max-height: calc(90vh - 4rem)`. Kiểm chứng Playwright (`tmp-resp/gift-zoom/gallery-*.js`): cycle 4 mệnh giá wrap cả 2 chiều, bộ đếm đúng, 390/768/1280px nav `48×48`. `DG_VERSION` → `1.16.16`. Không commit git.
- `2026-11-07e`: Gift Cards — **thêm nút zoom (`.dg-zoom-btn`) trên ảnh preview thẻ quà + lightbox xem ảnh đầy đủ**. Markup nút lấy từ single product, nhưng trang Gift Cards là **page template** → `product.js` (chứa `dgOpenZoom()` + `#dg-zoom-modal`) **không được enqueue** → `onclick="dgOpenZoom()"` sẽ ném `ReferenceError`. Sửa: thêm nút vào `template-parts/gift-cards/section-bento.php`; partial mới `template-parts/gift-cards/section-zoom-modal.php` (lightbox đặt **ngoài `<main class="dg-gift">`** để `position: fixed` không bị neo sai bởi transform của animation reveal); `assets/js/gift-cards-zoom.js` (classic script độc lập, không phụ thuộc Motion — giống `gift-cards-card-preview.js`): đọc `src` ảnh theo mệnh giá hiện tại, focus nút close, restore focus, Escape đóng, lock scroll body, khai báo `window.dgOpenZoom` (guard nếu trang đã có `#dg-zoom-modal`); CSS lightbox trong `assets/css/gift-cards.css` + token mới `--gift-zoom-backdrop`/`--gift-zoom-close-*` trong `style.css`; enqueue `dg-gift-cards-zoom` trong `inc/enqueue/scripts.php`. Kiểm chứng Playwright (`tmp-resp/gift-zoom/`): 390/768/1280px → nút `absolute`, `top/right 16px`, `z-index 10`, `40×40`, visible ✓; click → modal `flex` + `is-open`, ảnh đúng `src`, `body` overflow hidden ✓; Escape → đóng + overflow reset + focus restore ✓; ảnh 1200×900 render `1080×810` (đúng tỉ lệ). `DG_VERSION` → `1.16.15`. Không commit git.
- `2026-11-07d`: Wishlist — **ô chọn `.dg-wishlist-card__select` luôn hiện ở mobile + tablet** (desktop giữ hover-reveal). Đo trước khi sửa: ≤767px đã hiện sẵn (rule cũ `@media (max-width: 767px)`), nhưng **768–1023px (tablet) `opacity: 0.0004` → ẩn, chỉ hiện khi hover** ← gap thật. Sửa `assets/css/wishlist.css`: thêm `@media (max-width: 1023px) { .dg-wishlist-card__select { opacity: 1; transform: translateY(0); } }` ngay sau block `:hover`, phủ mobile + tablet; xoá rule trùng lặp cũ trong block `max-width: 767px` (hai nguồn sự thật). Rule mới (0,1,0) nằm sau rule `:hover` (0,3,0) → khi rê chuột ở mobile/tablet, hover vẫn ra `opacity: 1`/`translateY(0)` = trùng trạng thái nghỉ → không nhấp nháy. Không sửa JS — `wishlist.js` không có handler hover nào (chỉ `click`). Kiểm chứng Playwright với CSS live: 390/640/767/768/1023px → `opacity: 1` khi không hover ✓; 1024/1280px giữ nguyên ✓. `DG_VERSION` → `1.16.14`. Không commit git.
- `2026-11-07c`: Shop — **hover nút quick-add ở trạng thái "Added" (desktop) giữ nguyên màu nền như lúc chưa hover**. Rule `.dg-add-to-cart.dg-quick-add--added:hover` dùng `--color-primary-container` = `#f4c2c2` (hồng) trong khi trạng thái nghỉ dùng `--color-tertiary-container` = `#f1ca50` (vàng brand) → hover lệch hẳn sang hồng, vỡ bảng màu. Fix: đổi 2 token sang đúng bộ của trạng thái nghỉ (`--color-tertiary-container` / `--color-on-tertiary-container`), giữ `translateY(-2px)` để vẫn có phản hồi hover. Kiểm chứng Playwright site live: 1024/1280px — bg/color/icon color ≡ giống hệt lúc nghỉ, chỉ transform đổi `0,0`→`0,-2`; 390/768px không đổi. `DG_VERSION` → `1.16.13`. Không commit git.
- `2026-11-07b`: Shop — **bỏ hover của nút quick-add ở mobile + tablet** (desktop giữ nguyên). Đo trước khi sửa: hover ở 390/768px đổi nền sang hồng (`--color-primary-container`) + nhấc nút 2px — touch giữ `:hover` dính sau tap → nút kẹt ở trạng thái đó. Sửa: block `@media (max-width: 1023px)` mới ở `assets/css/shop.css` khai báo lại nền/màu chữ/transform gốc cho `.dg-add-to-cart:hover` + `.dg-add-to-cart.dg-quick-add--added:hover`, kèm tắt sheen sweep theo hover card; xoá 2 rule hover cũ trong block media `< 1024px` (cố tình giữ hiệu ứng nhấc nút, nay trái yêu cầu). Không sửa JS — grep toàn bộ `assets/js/` không có handler hover nào cho `.dg-add-to-cart`/`.dg-quick-add` (chỉ `click`), `:hover` là pseudo-class nên phải xử lý bằng CSS. Kiểm chứng Playwright: 390/768px rest ≡ hover; 1024/1280px giữ nguyên hover-reveal. `DG_VERSION` → `1.16.12`. Không commit git.
- `2026-11-07a`: Single product — **giảm khoảng cách trên section `#product-tabs` ở mobile** (tablet/desktop giữ nguyên 120px). Section dùng utility Tailwind `mt-section-gap` (margin-top: 120px) ở mọi breakpoint → quá lớn ở mobile. Fix: token `--product-tabs-gap-mobile: 48px` trong `style.css` `:root` + `@media (max-width: 767px) { #product-tabs { margin-top: var(--product-tabs-gap-mobile); } }` trong `assets/css/product.css`. Dùng **ID selector** vì Tailwind CDN inject `<style>` runtime vào `<head>` **sau** các file CSS theme → utility class (0,1,0) sẽ thắng; ID (1,0,0) luôn thắng (cùng pattern đã có: `#dg-main-image`, `#sticky-add-to-bag`). Breakpoint 767px khớp `product.js` (`innerWidth < 768` = mobile). Kiểm chứng Playwright trên site live: 320–767px → 48px; 768–1280px → 120px. `DG_VERSION` → `1.16.11`. Không commit git.
- `2026-11-06i`: Header — **fix bug text "Menu" thừa ở desktop**. Root cause: `$nav_aria = esc_attr_e( 'Menu', ... )` — nhầm biến thể `_e` (echo) thay vì `__` (return), nên chữ "Menu" bị in thành text node trong markup và `$nav_aria` thành `null` → `aria-label=""` rỗng. Fix: `esc_attr__()` + comment chống lặp lại. Kiểm chứng bằng render template với WP stub (`tmp-resp/nav-cart-badge/render-header-nav.php`): `aria-label="Menu, 4 items"`, không còn text node thừa. Không đổi `DG_VERSION`.
- `2026-11-06h`: Header — **badge wishlist trên menu item "Wishlist" trong drawer** + **badge TỔNG (giỏ hàng + wishlist) trên nút menu mobile/tablet**. Nút toggle chỉ 40×40px nên chọn 1 pill tổng thay vì 2 pill chồng nhau (đã hỏi user). Helper mới `dg_render_nav_count_badge()` với class riêng `.dg-nav-badge` (**không** dùng `.dg-cart-count` — `DGCartBadge.paint()` sẽ ghi đè giá trị tổng bằng count giỏ hàng); module JS mới `assets/js/lib/nav-badge.js` (`DGNavBadge.setCart/setWishlist/refresh`) giữ 2 bộ đếm và tính tổng. `cart-badge.js` + `wishlist-toggle.js` + `wishlist.js` nối vào module này. Item "Wishlist"/"Cart" trong drawer có badge + `aria-label` server-side. `dg_get_wishlist_count()` memoize theo request (đọc 3 lần/render), cache invalidate trong `dg_set_wishlist()`. CSS: `header-nav.css` §4d-1 (`.dg-nav-badge`) + §4d-2 (gộp badge Cart + Wishlist trong drawer, `position: static` + `margin-left: auto`). `DG_VERSION` → `1.16.7`.
- `2026-11-06g`: Header — **badge số lượng giỏ hàng trên menu item "Cart" trong drawer mobile/tablet** + fix bug badge không ẩn khi count = 0. `dg_inject_mobile_menu_items()` render `dg_render_cart_count_badge()` vào thẻ `<a>` của item Cart (tái dùng helper sẵn có → `DGCartBadge.paint()` cập nhật cả 3 badge cùng lúc). CSS mới §4d-2 trong `header-nav.css` — badge trong drawer nằm **trong flow** (`position: static` + `margin-left: auto`) nên sát mép phải của row, không đè glyph. **Bug fix**: `display: inline-flex` (0,2,0) của rule toggle badge thắng class `hidden` của Tailwind (0,1,0) → badge vẫn hiện khi giỏ rỗng; đã thêm override `.hidden { display: none }`. `DG_VERSION` → `1.16.6`.
- `2026-11-06f`: Header — **badge số lượng giỏ hàng trên nút menu mobile/tablet** (`#dg-mobile-menu-toggle`), animation pop mượt. Icon giỏ hàng desktop bị ẩn dưới 1024px nên mobile/tablet không có chỗ nào hiện số lượng giỏ. Render badge vào nút toggle (tái dùng `dg_render_cart_count_badge()`), module JS mới `assets/js/lib/cart-badge.js` (`DGCartBadge.paint/bump`) là nơi duy nhất vẽ badge + kích hoạt animation (`is-pulse` / `is-first-show`), 6 call site cũ chuyển sang dùng nó. CSS scope dưới `.dg-nav-toggle` trong `header-nav.css` §4d-1 → **desktop giữ nguyên 100%**. `DG_VERSION` → `1.16.5`.
- `2026-11-05t`: chip filter Wishlist — chip **active không đổi style khi hover** (`:hover` → `:not(.is-active):hover`; cùng specificity nên thứ tự rule cũ ghi đè nền active). Chip chưa active giữ nguyên hover. Không nằm trong media query → mobile/tablet/desktop đồng bộ. `DG_VERSION` → `1.15.99`.
- `2026-11-05s`: chip filter Wishlist ở mobile — **giảm font chữ + icon** (font 10px, icon 11px, badge 12px) để text/icon **không còn sát viền chip** (padding 5.5px ở 320px → 9px ở 639px). Bộ giá trị chọn bằng brute-force 3.000+ tổ hợp (`tmp-resp/wl-chip-optimize.js`), ràng buộc tổng 3 chip ≤ content 244px ở 320px. `DG_VERSION` → `1.15.98`.
- `2026-11-05r`: chip filter Wishlist ở mobile — **hiện lại icon** giống desktop + text thoải mái (chip co theo nội dung `repeat(3, auto)` + fluid `clamp()` cho font/padding/gap/icon/badge; ở 320px 3 chip = 231px < content 244px → vừa khít, overflow 0). `DG_VERSION` → `1.15.97`.
- `2026-11-05q`: toolbar Wishlist — "Select (0)" và nút sort **cùng 1 hàng ngang** ở mobile (`grid-template-columns: auto minmax(0,1fr)` trong block `@media (max-width: 639px)` §12b). Icon `schedule` vẫn ẩn để "Recently saved" không bị cắt (tổng cần 241px < content 256px ở 320px). `DG_VERSION` → `1.15.96`.
- `2026-11-05p`: toolbar Wishlist (`.dg-wishlist-toolbar`) mobile-only — 3 chip filter về 1 hàng đều nhau (grid 3 cột), sort full-width 1 hàng riêng, ẩn icon filter + nhãn "Sort by". Block `@media (max-width: 639px)` mới §12b trong `wishlist.css` — tablet/desktop không đổi. `DG_VERSION` → `1.15.95`.
- `2026-11-05o`: nhãn "On sale" (`.dg-wishlist-hero__stat-label--stacked`) về 1 dòng ở mobile.
- `2026-11-04f`: **xoá hẳn** hook báo cáo chrome-devtools (`chrome-devtools-mcp-session-start.ps1` + `.chrome-devtools-tools-cache.json`) — không còn sentinel, không còn inject mỗi phiên. MCP server trong `.cursor/mcp.json` giữ nguyên, tool vẫn dùng được.
- `2026-11-04e`: tắt hook chrome-devtools mặc định (-985 tok/phiên), xoá sentinel, tách changelog sang `docs/CHANGELOG.md`, nén §5/§8. `CLAUDE.md` 60.365 -> 39.120 chars.
- `2026-11-04d`: docs upstream (61 tool) lệch npm `1.12.0` (59 tool) — `--categoryWebauthn=true` bị bỏ qua âm thầm. Luôn đối chiếu `npm dist-tags`.
- `2026-11-04c`: mở đủ 59 tool bằng 6 flag, probe riêng từng flag (13/5/2/2/4/1). Hook replay flag từ `mcp.json`.
- `2026-11-04b`: tích hợp Chrome DevTools MCP; bug Windows `npx` `.cmd` -> phải bọc `cmd /c`.
- `2026-11-04`: rebuild graph về `HEAD`; bỏ số liệu hard-code khỏi §C.1.
