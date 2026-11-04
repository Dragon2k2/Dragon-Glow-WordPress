# Changelog - Dragon Glow

> Moved out of `CLAUDE.md` so it stops loading on every session.
> `CLAUDE.md` keeps only the 5 most recent entries.

- `2026-11-04f`: xoá hẳn hook báo cáo chrome-devtools.

  Lý do: sentinel và phần inject mỗi phiên chỉ để tự mô tả trạng thái server, trong khi
  bản thân MCP server vẫn tự hoạt động. Chi phí ~985 token/phiên không đổi lại được gì
  khi không dùng browser hằng ngày.

  Đã xoá: `.cursor/hooks/chrome-devtools-mcp-session-start.ps1` (16.133 bytes) và
  `.cursor/hooks/.chrome-devtools-tools-cache.json` (1.416 bytes).

  Giữ nguyên: `.cursor/mcp.json` (server vẫn đăng ký, tool vẫn gọi được),
  `scripts/setup-chrome-devtools-mcp.ps1` (tải lại vendor), `vendor/chrome-devtools-mcp/`.

  Kiểm tra server sống hay chết: gọi một tool, vd `list_pages`. Không còn sentinel để soi.
- `2026-11-04d`: phát hiện **docs upstream lệch npm** — nhánh `main` trên GitHub đã tài liệu hoá **61 tool** (thêm `set_logpoints` và `configure_webauthn` qua `--categoryWebauthn`), nhưng `npm view chrome-devtools-mcp dist-tags` vẫn là `1.12.0` với **59 tool**. Probe xác nhận `1.12.0` **bỏ qua âm thầm** `--categoryWebauthn=true` (không lỗi, không thêm tool). Ghi cảnh báo vào §C.1b: đừng tin số tool trong docs, đối chiếu dist-tags.

- `2026-11-04e`: t?t hook chrome-devtools m?c d?nh (-985 tok/phi�n), xo� sentinel `[DG-CHROME-DEVTOOLS v1]`, t�ch changelog sang `docs/CHANGELOG.md`, n�n �5/�8. `CLAUDE.md` 60.365 -> ~42.400 chars (~16.300 -> ~11.400 tok).


- `2026-11-04c`: mở **đủ 59 tool** chrome-devtools-mcp (mặc định chỉ 32) bằng 6 flag — `--memoryDebugging`, `--categoryExtensions`, `--categoryExperimentalThirdParty`, `--categoryExperimentalWebmcp`, `--categoryPwa`, `--experimentalVision`. Mỗi flag đã probe riêng để xác định đúng số tool nó thêm (13/5/2/2/4/1 = 26), không suy từ docs. Hook `sessionStart` giờ **replay flag từ `.cursor/mcp.json`** khi probe `tools/list` thay vì chỉ truyền version — bật/tắt nhóm trong config là output tự theo. Sửa 2 bug phát hiện khi test: (1) probe đọc file args do `Set-Content -Encoding UTF8` của PS 5.1 ghi kèm BOM → `JSON.parse` chết, hook âm thầm rơi về danh sách docs; fix bằng strip `\uFEFF`; (2) patch tự động để lại dòng `if` trùng → PowerShell báo thiếu `}`, hook không xuất JSON.

- `2026-11-04b`: tích hợp **Chrome DevTools MCP** — clone `vendor/chrome-devtools-mcp` (gitignore, tải lại bằng `scripts/setup-chrome-devtools-mcp.ps1`), thêm `.cursor/mcp.json` đăng ký server `chrome-devtools` (npx, pin `1.12.0`), thêm hook `sessionStart` **riêng** `.cursor/hooks/chrome-devtools-mcp-session-start.ps1` (entry thứ 2 trong `hooks.json`, độc lập với superpowers). Hook probe `tools/list` thật thay vì tin docs — phát hiện docs liệt kê ~60 tool nhưng server mặc định chỉ expose **32**; nhóm Memory/Extensions/WebPWA/WebAuthn cần flag riêng. Cache tool list ở `.cursor/hooks/.chrome-devtools-tools-cache.json` (gitignore) để cold start 3.4s, warm 0.5s. 3 nhánh fail-open đã kiểm chứng: thiếu vendor / chưa đăng ký MCP / probe fail. Thêm §C.1b. **Bug quan trọng phát hiện sau khi user reload window:** server register nhưng kẹt `disconnected` — nguyên nhân là Cursor spawn stdio không qua shell, `npx` trên Windows là `.cmd` nên Node ≥20 từ chối spawn (CVE-2024-27980). Fix: bọc `cmd /c` trong `.cursor/mcp.json`; hook giờ tự cảnh báo nếu thiếu wrapper.

- `2026-11-04`: rebuild knowledge graph về đúng `HEAD` (`0914c9c`) — `graphify update .` báo "no code-graph topology changes" nên không ghi lại output, phải chạy `graphify extract . --code-only` (thay 50 node, prune 1) rồi `cluster-only --no-label` + `apply-labels.py` + `cluster-only --no-label` để giữ tên community đã curate. Số liệu sau rebuild: xem `graphify-out/GRAPH_REPORT.md` §Summary (không chép lại vào đây). Đồng thời bỏ hẳn số liệu hard-code khỏi §C.1 — hook `sessionStart` đã in "Graph status" mỗi phiên từ `graph.json`, nên không cần cập nhật tay nữa (drift Check 1 tự no-op khi §C.1 không còn claim). Ghi chú: `apply-labels.py` phải chạy bằng python của venv uv (`%APPDATA%\uv\tools\graphifyy\Scripts\python.exe`), vì `python` hệ thống (3.14) thiếu module `graphify`.

- `2026-11-02e`: hook `sessionStart` thêm **drift detector** — đối chiếu number nodes/edges/communities trong CLAUDE.md, danh sách skill bảng §C.2, `DG_VERSION`, và đường dẫn file theme với thực tế repo; **chỉ inject khi lệch** (khớp thì im lặng, 0 token). Quyết định **không** chép lại CLAUDE.md/rules vào hook vì Cursor đã tự nạp sẵn (~14.300 tokens/phiên) — inject lại là nhân đôi vô ích. Sửa false positive: check skill count ban đầu đếm cả 34 thư mục (15 superpowers + 19 WordPress) thay vì chỉ danh sách được nêu trong CLAUDE.md.

- `2026-11-02d`: mở rộng hook `sessionStart` — ngoài bootstrap superpowers, hook giờ tự quét `.cursor/skills/*/SKILL.md` để inject **catalog 34 skill** (cắt description 95 ký tự) và đọc `graphify-out/graph.json` để inject **con trỏ knowledge graph** kèm số nodes/edges/communities + cảnh báo stale (so `built_at_commit` với `HEAD`). Fail-open từng section. Sửa 2 bug phát hiện khi test: (1) backtick trong here-string bị PowerShell escape → mất ký tự (`assets/js` thành `ssets/js`), (2) mojibake `â€"` do PS 5.1 đọc file không BOM theo ANSI → script nay thuần ASCII + transliterate output.

- `2026-11-02c`: thêm hook `sessionStart` (`.cursor/hooks.json` + `.cursor/hooks/superpowers-session-start.ps1`) để inject bootstrap superpowers vào system context mỗi conversation mới — bám sát cơ chế của upstream thay vì chỉ dựa vào rule always-on. Sửa lại ghi chú sai trước đó ("Cursor không có hook SessionStart").

- `2026-11-02b`: thêm §C.2 — **superpowers** (obra/superpowers v7.0.1, MIT). Copy 15 process skills vào `.cursor/skills/` (mirror `.claude/` + `.agents/`), thêm rule always-on `.cursor/rules/superpowers.mdc` (thay hook `sessionStart` của upstream). Ghi rõ adaptation: repo không có test suite → kiểm chứng bằng `php -l` + Playwright thay cho `npm test`.

- `2026-11-02`: thêm §C.1 — **graphify knowledge graph**. Cài `graphifyy` 0.9.100 qua uv tool, đăng ký skill vào `.agents/skills/graphify/` (mirror sang `.cursor/skills/` + `.claude/skills/`), thêm rule always-on `.cursor/rules/graphify.mdc`, `.graphifyignore` giới hạn corpus vào code theme, script `scripts/graphify/apply-labels.py` + `curated-labels.json` để giữ tên community qua mỗi lần re-cluster.

- `2026-10-27b`: refactored sections 5b/5d/5e/5g/9 — Generalized rules (nói chung không nói riêng). §5b (email) → "Template Organization" (tất cả template). §5d CSS + JS organization combined (formerly 5d/5f). §5e inline styles → "Styling Strategy" (general rule). §5g selectors → removed "WC-specific" language, added general principle. §9 → "Interactivity & Dynamic Behavior" (remove Motion-specific focus, added general JS patterns + Motion as option).

- `2026-10-27a`: thêm §8c — **Component Style Isolation**: rule cứng prevent style pollution across pages (ALL styles, not just gradient). LUÔN tạo token riêng cho component-local styles, KHÔNG dùng global theme tokens. 3-layer token system (Primitive → Semantic → Component), decision tree, scope matrix, comprehensive checklist.

- `2026-10-02`: thêm §8b — Tailwind Forms plugin override: nguyên nhân specificity tie + source order khi Tailwind nạp qua `<script>`, cách debug bằng console, cách khắc phục bằng tăng specificity qua wrapper class (không dùng `!important`).

- `2026-09-29`: thêm §8a — Design Token System: quy tắc cứng về token-first approach, cấm hard-code, visual regression workflow, refactor checklist.

- `2026-08-24`: Refactor toàn bộ để tương thích với `.cursor/skills` ecosystem; thêm skill compatibility matrix; làm rõ project type (classic theme trong full site repo); loại bỏ mâu thuẫn về React/build step.

- `2026-08-10`: thêm §5g — selector phải khớp DOM thật; visual parity rule.

- `2026-08-09`: refactor — tách cart helpers, approval handler, legal pages.
