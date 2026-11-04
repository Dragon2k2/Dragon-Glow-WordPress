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

- Global: `style.css` (token `:root` — **single source of truth**), `main.css` (base/typography/layout), `responsive.css` (breakpoint), `woocommerce.css` (conditional).
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

> Đầy đủ (16 entry từ 2026-08-09) ở `docs/CHANGELOG.md`. Dưới đây chỉ là mốc gần nhất — lịch sử không cần nạp mỗi phiên.

- `2026-11-04f`: **xoá hẳn** hook báo cáo chrome-devtools (`chrome-devtools-mcp-session-start.ps1` + `.chrome-devtools-tools-cache.json`) — không còn sentinel, không còn inject mỗi phiên. MCP server trong `.cursor/mcp.json` giữ nguyên, tool vẫn dùng được.
- `2026-11-04e`: tắt hook chrome-devtools mặc định (-985 tok/phiên), xoá sentinel, tách changelog sang `docs/CHANGELOG.md`, nén §5/§8. `CLAUDE.md` 60.365 -> 39.120 chars.
- `2026-11-04d`: docs upstream (61 tool) lệch npm `1.12.0` (59 tool) — `--categoryWebauthn=true` bị bỏ qua âm thầm. Luôn đối chiếu `npm dist-tags`.
- `2026-11-04c`: mở đủ 59 tool bằng 6 flag, probe riêng từng flag (13/5/2/2/4/1). Hook replay flag từ `mcp.json`.
- `2026-11-04b`: tích hợp Chrome DevTools MCP; bug Windows `npx` `.cmd` -> phải bọc `cmd /c`.
- `2026-11-04`: rebuild graph về `HEAD`; bỏ số liệu hard-code khỏi §C.1.
