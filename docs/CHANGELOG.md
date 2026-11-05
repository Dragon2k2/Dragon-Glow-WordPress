# Changelog - Dragon Glow

> Moved out of `CLAUDE.md` so it stops loading on every session.
> `CLAUDE.md` keeps only the 5 most recent entries.

- `2026-11-05o`: nhãn "On sale" (`.dg-wishlist-hero__stat-label--stacked`) cũng về **1 dòng** ở mobile.

  Vấn đề: nhãn này dùng 2 span `display: block` → cố ý xếp 2 dòng ("On" / "sale"). Ở mobile các label khác đã về 1 dòng (lượt trước) nhưng nhãn này vẫn 2 dòng → không đồng nhất.

  Sửa (`assets/css/wishlist.css`):
  - Chuyển block `@media (max-width: 639px)` xuống **sau** các rule base `.dg-wishlist-hero__stat-label--stacked` → cùng specificity, thứ tự sau thắng.
  - Bỏ `:not(--stacked)` ở rule label → giờ áp `letter-spacing: 0.06em` + `white-space: nowrap` cho MỌI label.
  - `--stacked > span` → `display: inline` (từ block) + `span + span` → `margin-left: 0.35em` thay cho `margin-top: 2px`.

  Vì sao cần `margin-left`: markup không có whitespace giữa 2 span → nếu chỉ đổi `display` sang `inline` sẽ ra "Onsale" dính liền. `0.35em` tạo khoảng trắng tương đương word-space.

  Desktop/tablet (≥ 640px) **không đổi**: 2 span vẫn `display: block` → nhãn 2 dòng như cũ.

  Kiểm chứng (Playwright, `tmp-resp/wl-label-verify.js`):
  - 320/360/390/430/639px → "On sale" **1 dòng**, không tràn card ✓
  - Khoảng cách giữa "On" và "sale" = 4px, cùng hàng ✓
  - 640px & 1280px → "On sale" giữ 2 dòng, `white-space: normal`, letter-spacing 1.1px ✓

  `DG_VERSION` → `1.15.94`. Không commit git.

- `2026-11-05n`: label stats hero Wishlist ("Saved items") hiển thị **1 dòng** ở mobile.

  Vấn đề: ở mobile stats là grid 2 cột → mỗi card rộng ~141px (content box ~109px), trong khi "SAVED ITEMS" ở font 11px + `letter-spacing: 0.10em` rộng ~107px → chỉ dư 2px, dễ rớt xuống 2 dòng.

  Sửa (`assets/css/wishlist.css`, trong `@media (max-width: 639px)`):
  - `.dg-wishlist-hero__stat` → `padding: 18px 12px` (tạo thêm headroom ngang)
  - `.dg-wishlist-hero__stat-label:not(--stacked)` → `letter-spacing: 0.06em` + `white-space: nowrap`

  **Tạo headroom TRƯỚC rồi mới ép nowrap** — nhờ vậy chữ luôn nằm gọn 1 dòng mà vẫn nằm trọn trong card (không tràn). KHÔNG dùng `text-overflow: ellipsis` vì cắt "Saved items" → "Saved i…" làm mất nghĩa.

  `.dg-wishlist-hero__stat-label--stacked` (nhãn "On sale" tách 2 span `display: block`) cố ý xếp 2 dòng → loại khỏi `:not()`, giữ nguyên.

  Desktop/tablet (≥ 640px) **không đổi**: không nowrap, letter-spacing giữ 0.10em.

  Kiểm chứng (Playwright, `tmp-resp/wl-label-verify.js`):
  - 320/360/390/430/639px → mọi label thường **1 dòng**, không tràn ra ngoài card ✓
  - Label "On sale" giữ 2 dòng ✓
  - 640px & 1280px → `white-space: normal`, letter-spacing 1.1px (0.10em) — giữ nguyên ✓

  `DG_VERSION` → `1.15.93`. Không commit git.

- `2026-11-05m`: 2 nút CTA hero Wishlist (Share wishlist / Continue shopping) giãn hết bề ngang khối stats ở **mobile**.

  Vấn đề: ở mobile, `.dg-wishlist-hero__actions` là flex `wrap` → 2 nút ôm chữ (187px / 251px) trong khi `.dg-wishlist-hero__stats` full width (294px) → lệch bề ngang, nút trông nhỏ lạc giữa hero.

  Sửa (`assets/css/wishlist.css`) — trong `@media (max-width: 639px)`:
  - `.dg-wishlist-hero__actions` → `flex-direction: column; flex-wrap: nowrap`
  - `.dg-wishlist-hero__actions .dg-wishlist-btn` → `width: 100%; justify-content: center`

  Vì sao dùng `column` chứ không giữ `wrap`: chữ "Share wishlist" ngắn hơn "Continue shopping" → nếu wrap ngang, 2 nút sẽ nằm cùng hàng nhưng khác bề rộng (vẫn lệch). `column` ép cả 2 xếp dọc, mỗi nút chiếm đúng bề ngang stats.

  Desktop/tablet (≥ 640px) **không đổi**: 2 nút vẫn nằm cạnh nhau, ôm chữ.

  Kiểm chứng (Playwright, `tmp-resp/wl-btn-verify.js`):
  - 390px → stats 294px, share 294px, shop 294px ✓ · 430px → stats 334px, cả 2 nút 334px ✓
  - 2 nút xếp dọc, icon + chữ căn giữa ✓
  - 768px → share 187px / shop 251px (ôm chữ), cùng hàng ✓ · 1280px → giữ nguyên ✓

  `DG_VERSION` → `1.15.92`. Không commit git.

- `2026-11-05l`: thêm `padding-bottom` cho 3 trang pháp lý còn lại — Cookie Policy, Terms of Service, Accessibility Statement (bổ sung cho `.dg-privacy` ở `2026-11-05k`).

  Vấn đề: giống `.dg-privacy` — cả 3 page-level `<main>` không có khoảng đệm dưới → nội dung cuối dính sát footer.

  Sửa (mỗi file đúng 1 rule):
  - `assets/css/cookie-policy.css` → `.dg-cookie`
  - `assets/css/terms-of-service.css` → `.dg-terms`
  - `assets/css/accessibility-statement.css` → `.dg-accessibility`

  Cả 3 dùng `padding-bottom: clamp(4rem, 10vw, 7rem)` — **bám đúng convention** của page-level `<main>` trong theme (`.dg-faq`, `.dg-gift`, `.dg-privacy`).

  Kiểm chứng (Playwright, `tmp-resp/legal-pad-verify.js`, 390/768/1280px):
  - Cả 4 trang: padding 64px / 76.8px / 112px ✓
  - Khoảng đệm thực tế (main.bottom − wrap.bottom) khớp chính xác padding-bottom ✓
  - `margin-bottom` của main = 0 → không nhân đôi khoảng đệm ✓

  `DG_VERSION` → `1.15.91`. Không commit git.

- `2026-11-05k`: thêm `padding-bottom` cho `<main class="dg-privacy">` (trang Privacy Policy).

  Vấn đề: `.dg-privacy` không có khoảng đệm dưới → nội dung cuối trang dính sát footer, mất khoảng thở giữa tài liệu pháp lý và footer.

  Sửa (`assets/css/privacy-policy.css`): `.dg-privacy` → `padding-bottom: clamp(4rem, 10vw, 7rem)`.

  Giá trị này **bám đúng convention** của page-level `<main>` trong theme: `.dg-faq` và `.dg-gift` đều dùng `clamp(4rem, 10vw, 7rem)` (flexible → tự co giãn theo viewport, không hard-code px).

  Kiểm chứng (Playwright, `tmp-resp/privacy-pad-verify.js`, 390/768/1280px):
  - 390px → 64px · 768px → 76.8px · 1280px → 112px ✓
  - Khoảng cách thực tế từ nội dung cuối tới footer khớp chính xác padding-bottom ở cả 3 breakpoint ✓

  `DG_VERSION` → `1.15.90`. Không commit git.

- `2026-11-05j`: nút **"Write to us"** (`.dg-careers-closing .dg-careers-btn--primary`) nay dùng chung hệ gold-pill với nút "Discover Our Story" (`.dg-sus-btn`).

  Vấn đề: nút closing của Careers còn style cũ — nền `--color-on-surface` (đen đặc), chữ `--color-background-warm`, bo góc vuông (`border-radius: 0`), không mũi tên → lệch hẳn với hệ gold-pill của các trang khác.

  Sửa (`assets/css/careers.css`):
  - Gộp rule thành **selector list** — `.dg-careers-hero .dg-careers-btn--secondary, .dg-careers-closing .dg-careers-btn--primary` dùng chung 1 bộ rule gold-pill (gradient, pill radius, chữ trắng, shadow, hover nâng/sáng, mũi tên `→`) → 2 nút trong trang Careers **không thể lệch style** về sau.
  - `align-self: stretch` chỉ giữ cho nút hero (giãn hết cột); nút closing là con của block container + `text-align: center` → ôm chữ, khớp `.dg-sus-btn` ở section closing.
  - Xoá rule cũ `.dg-careers-closing .dg-careers-btn--primary` (nền đen, bo vuông) + rule hover cũ.
  - Thêm nhánh `prefers-reduced-motion` cho nút closing (transform + mũi tên).

  `template-parts/careers/section-closing-cta.php`: bọc text trong `<span class="dg-careers-btn__label">` (mốc neo cho mũi tên).

  Kiểm chứng (Playwright, `tmp-resp/careers-btn-verify.js`, 390/768/1280px):
  - Parity 11/11 với `.dg-sus-btn`: gradient, radius, border, màu chữ, font-size/weight/tracking, transform, padding, shadow, display ✓
  - Mũi tên `→`: content đúng, opacity 0 → 1 khi hover, nằm trong pill (inset 8px), text căn giữa (Δx/Δy < 0.6px) ✓
  - Hover: gradient sáng hơn + shadow hover + nâng `translateY(-2px)` — giống hệt ✓
  - Width: closing ôm chữ (fill < 1) như sus closing; nút hero vẫn giãn hết cột (fill 100%) ✓
  - Reduced-motion: transform `none`, arrow opacity `0` ✓

  `DG_VERSION` → `1.15.89`. Không commit git.

- `2026-11-05i`: đổi màu chữ **trắng** cho cả 3 nút CTA editorial gold-pill (OI / Careers / Sustainability) theo yêu cầu chủ design.

  Sửa: `--cta-gold-ink` trong `style.css` `:root` → `var(--neutral-0)` (trước: `var(--primary-900)`). Vì 3 nút dùng chung 1 token nên 1 dòng là cả 3 trang đồng nhất — nền gradient, hover, shadow, bo góc pill, mũi tên `→` giữ nguyên 100%.

  ⚠ Ghi nhận accessibility: chữ trắng trên gradient gold (`f1ca50` → `d4af37`) chỉ đạt **1.58:1** → dưới WCAG 2.1 AA (4.5:1) cho text 12px. Đây là quyết định thiết kế có chủ đích; nếu cần tuân thủ AA thì đổi nền sang `gold-700 → gold-800` (4.85–6.99:1) hoặc giữ chữ `--primary-900` (bản AAA ≥ 7.16:1). Đã ghi chú ngay trong `style.css` và trong script verify.

  Kiểm chứng (Playwright, `tmp-resp/sus-btn-verify.js`, 390/768/1280px): parity 10/10 với OI (gradient/radius/font/tracking/padding/shadow), hover giống hệt, width hero fill 100%, arrow trong pill, reduced-motion OK, 3 nút cùng màu chữ `rgb(255,255,255)` ✓

  `DG_VERSION` → `1.15.88`. Không commit git.

- `2026-11-05h`: nút CTA trang **Sustainability** (`.dg-sus-btn`) nay dùng **chung hệ editorial gold-pill** với Our Ingredients + Careers; gom token `--oi-btn-*` → `--cta-gold-*` (theo vai trò, không theo tên trang).

  Vấn đề: `.dg-sus-btn` còn style cũ — nền gold tối (gold-500 → gold-600), **chữ trắng** (chỉ 2.40:1, dưới WCAG AA 4.5:1), bo góc vuông, không có mũi tên `→` → lệch hẳn so với 2 trang kia.

  Sửa:
  - `style.css` — đổi tên bộ token `--oi-btn-*` → `--cta-gold-*` (dùng chung 3 trang, theo §8a "Define once"), thêm `--cta-gold-tracking`; xoá bộ `--sus-btn-*` cũ.
  - `assets/css/our-ingredients.css`, `assets/css/careers.css` — trỏ sang token mới.
  - `assets/css/sustainability.css` — `.dg-sus-btn` dùng `--cta-gold-*`, bo góc pill (`--cta-gold-radius`), chữ `--cta-gold-ink` (`--primary-900`), thêm `.dg-sus-btn__label` + mũi tên `→` trượt khi hover, focus ring `--gold-dark`, nhánh `prefers-reduced-motion` tắt transform/arrow.
  - `template-parts/sustainability/section-hero.php`, `section-closing.php` — bọc text trong `<span class="dg-sus-btn__label">` (mốc neo cho mũi tên).

  Kiểm chứng (Playwright, `tmp-resp/sus-btn-verify.js`, markup + layout thật, 390/768/1280px):
  - Parity 10/10 với `.dg-oi-btn`: gradient, radius 9999px, ink, font-size/weight/tracking, transform, padding, shadow ✓
  - Mũi tên `→`: content đúng, opacity 0 → 1 khi hover, trượt về 0, luôn nằm trong pill ✓
  - Hover: gradient sáng hơn + shadow hover + nâng `translateY(-2px)` — **giống hệt** OI ✓
  - Width: hero giãn hết cột (fill 100%) như OI ở cả 3 breakpoint ✓
  - Contrast: min **8.17:1** → WCAG AAA (text 12px) ✓
  - Reduced-motion: transform `none`, arrow opacity `0` ✓

  `DG_VERSION` → `1.15.87`. Không commit git.

- `2026-11-05g`: sửa **chiều rộng** nút CTA hero Careers — giãn hết cột, khớp `.dg-oi-btn`.

  Vấn đề: sau `2026-11-05f` màu/icon/hover đã khớp nhưng nút Careers vẫn **ôm chữ** (192px) trong khi nút OI **giãn hết cột** (554px ở desktop) → 2 nút khác hẳn về chiều rộng.

  Nguyên nhân: base class `.dg-careers-btn--secondary` set `align-self: flex-start` (ôm chữ) cho các nút secondary khác; nút OI không set → mặc định `stretch` của flex column.

  Sửa (`assets/css/careers.css`): `.dg-careers-hero .dg-careers-btn--secondary` → `align-self: stretch` (ghi đè base class, có comment giải thích).

  Kiểm chứng (Playwright, markup + layout thật):
  - 390px → OI: content box 350px / nút 350px (fill 100%); Careers: content box 350px / nút 350px (100%) ✓
  - 768px → OI: 275px / 275px (100%); Careers: 320px / 320px (100%) ✓
  - 1280px → OI: 554px / 554px (100%); Careers: 533px / 533px (100%) ✓
  - Mũi tên `→` luôn nằm **bên trong** pill ở cả rest và hover, mọi breakpoint ✓
  - Text căn giữa tuyệt đối trong nút (lệch 0px) ✓
  - Style 10/10 khớp, contrast AA ✓, reduced-motion OK ✓

  `DG_VERSION` → `1.15.86`. Không commit.

- `2026-11-05f`: nút CTA hero **Careers** (`.dg-careers-btn--secondary`) đồng bộ hoàn toàn với nút editorial gold-pill của trang Our Ingredients (`.dg-oi-btn`).

  Yêu cầu: màu, chiều rộng, icon và hover giống hệt thẻ `<a href="#traceable" class="dg-oi-btn">See the sources</a>`.

  Trước: nút Careers là nền ink đặc + chữ trắng, bo góc 0, không gradient, không shadow, không mũi tên, font-weight 500, letter-spacing 0.1em.

  Sửa:
  - `style.css` `:root` — thêm `--oi-btn-radius: 9999px`; bộ token `--oi-btn-*` nay dùng chung cho cả 2 trang (đã ghi chú trong comment).
  - `assets/css/our-ingredients.css` — `.dg-oi-btn` dùng `border-radius: var(--oi-btn-radius)` thay hard-code `9999px`.
  - `assets/css/careers.css` — `.dg-careers-hero .dg-careers-btn--secondary` chuyển sang `display: inline-flex`, gradient gold, chữ `--primary-900`, bo góc pill, `font-weight: 600`, `letter-spacing: 0.12em`, shadow gold, transition `background/box-shadow/transform`, hover nâng `-2px` + gradient sáng hơn, `:active` về 0, focus ring `--gold-dark`.
  - Thêm khối mũi tên `→` (`::after` trên `.dg-careers-btn__label`) mirror đúng kỹ thuật của `.dg-oi-btn__label::after`: absolute + `left: 100%` neo vào mép phải **text** → không chiếm chỗ trong flex flow, không lọt ra ngoài pill.
  - `template-parts/careers/section-hero.php` — bọc label trong `<span class="dg-careers-btn__label">` (cần mốc neo cho mũi tên).
  - `assets/css/careers.css` — thêm nhánh `prefers-reduced-motion: reduce`: tắt transform nâng nút + ẩn mũi tên (mirror `.dg-oi-btn`).

  Kiểm chứng (Playwright, CSS thật của theme):
  - Style so sánh OI vs Careers — **10/10 khớp**: background, color, border, radius, padding, box-shadow, font, letter-spacing, transition, height ✓
  - Mũi tên: content/opacity/transform/margin-left/font-size khớp ✓; text căn giữa tuyệt đối trong nút (lệch 0px) cả trước và sau hover ✓
  - Hover: gradient `rgb(241,202,80)→rgb(212,175,55)`, shadow `0 16px 32px -12px`, `translateY(-2px)` — khớp OI ✓
  - Contrast: 7.16:1 (rest) → 8.17:1 (hover), **WCAG AA ✓** (nút cũ dùng chữ trắng trên gold chỉ 2.40:1 = fail AA)
  - Responsive 320/375/390/768/1024/1280px: không tràn ngang, bo góc pill, text căn giữa ✓
  - `prefers-reduced-motion`: transform `none`, mũi tên opacity 0 ✓

  `DG_VERSION` → `1.15.85`. Không commit.

- `2026-11-05e`: numbers strip (Sustainability) — 3 stat xếp **NGANG** 1 hàng trên MOBILE, dot separator nằm ngang.

  Vấn đề: sau `2026-11-05d` dot đã hiện ở mobile nhưng container là `flex-direction: column` → dot rơi xuống **DƯỚI** mỗi stat (separator dọc), không đúng vai trò như desktop (separator ngang).

  Ràng buộc đo được: 4 stat ở 16px rộng tổng **787px** > màn hình 390px → không thể 1 hàng ngang với 4 stat nếu không cuộn ngang.

  Quyết định (theo yêu cầu user): chỉ hiển thị **3 stat** ở mobile, không cuộn ngang.

  Sửa:
  - `style.css` `:root` — thêm 4 token mobile: `--sus-numbers-font-mobile: 12px`, `--sus-numbers-tracking-mobile: 0.04em`, `--sus-numbers-gap-mobile: 1.25rem`, `--sus-numbers-dot-gap-mobile: 0.5rem`.
  - `assets/css/sustainability.css` — thêm block `@media (max-width: 767px)`: chuyển `.dg-sus-numbers-inner` sang `display: grid` 5 cột `[stat]•[stat]•[stat]`; font 16px → 12px, `line-height` 1.0 → 1.25; ẩn stat thứ 4 (`:nth-child(7)`) + dot thứ 3 (`:nth-child(6)`).
  - ≥768px **không đổi**: vẫn `display: flex` row, 4 stat 16px, 3 dot.

  Kiểm chứng (Playwright, CSS thật của theme):
  - 320px → 3 stat + 2 dot, **1 hàng**, dot ngang giữa 2 stat, không cuộn ngang ✓
  - 390px → 3 stat + 2 dot, 1 hàng, không cuộn ngang ✓
  - 430px → 3 stat + 2 dot, 1 hàng, không cuộn ngang ✓
  - 768px → 4 stat + 3 dot, 1 hàng flex row — **không đổi** ✓
  - 1280px → 4 stat + 3 dot — **không đổi** ✓

  `DG_VERSION` → `1.15.84`. Không commit.

- `2026-11-05d`: hiện `.dg-sus-numbers-dot` (separator) trên **MOBILE** (Sustainability).

  Vấn đề: element `<span class="dg-sus-numbers-dot" aria-hidden="true">` **luôn có trong DOM** (render bởi `section-numbers.php`) nhưng bị CSS ẩn hoàn toàn ở mobile bằng `display: none`. Ở <768px layout là `flex-direction: column` nên 4 stat dồn liền nhau không có nhịp nhấn.

  Sửa (`assets/css/sustainability.css`): đổi `.dg-sus-numbers-dot` sang mobile-first — `display: block` ở base rule, **xoá** media query `min-width: 768px` vì giá trị đã giống nhau (trước đó là override trùng lặp).

  Kiểm chứng (Playwright, computed style thật):
  - 390px → 3/3 dot hiển thị, 4×4px, nằm giữa 2 item kề theo trục y (separator dọc) ✓
  - 640px → 3/3 dot hiển thị ✓
  - 768px → 3/3 dot hiển thị, separator ngang, **không đổi** ✓
  - 1280px → 3/3 dot hiển thị, separator ngang, **không đổi** ✓

  `DG_VERSION` → `1.15.83`. Không commit.

- `2026-11-05c`: giảm `padding-top` của `.dg-sus-hero-inner` trên **MOBILE** (Sustainability).

  Vấn đề: hero là khối đầu tiên của trang nhưng dùng chung `calc(--sus-section-gap * 0.75)` = 90px padding-top → khoảng hở lớn giữa header và ảnh hero trên màn hình nhỏ.

  Sửa:
  - `style.css` `:root` — thêm token `--sus-hero-pad-top-mobile: 32px` (khớp nhịp 8px của design system).
  - `assets/css/sustainability.css` — base (mobile) dùng token mới; media query `min-width: 768px` khai báo lại `padding-top: calc(var(--sus-section-gap) * 0.75)` (= 90px) để tablet/desktop **không đổi**.

  Kiểm chứng (Playwright, computed style thật):
  - 390px → padding-top **32px** ✓ · 640px → **32px** ✓ · 768px → **90px** ✓ · 1280px → **90px** ✓
  - padding-bottom (120px) và padding-left (24px / 64px) không đổi.

  `DG_VERSION` → `1.15.82`. Không commit.

- `2026-11-05b`: hero Sustainability — **đảo thứ tự ảnh/nội dung trên MOBILE**.

  Trước: mobile để nội dung ở trên, ảnh ở dưới (`order: 2` / `order: 1`); ≥768px grid 2 cột → ảnh trái, nội dung phải.

  Sau (mobile-first): `order` cơ bản đổi thành ảnh `1` / nội dung `2` → mobile ảnh trên, chữ dưới. Vì ≥768px vẫn là grid 2 cột với cùng thứ tự (ảnh trái, nội dung phải) nên **xoá được** khai báo `order` trong media query `min-width: 768px` — không còn override trùng lặp.

  Kiểm chứng (Playwright, 3 breakpoint):
  - 390px → IMAGE top=90, CONTENT top=536 (ảnh TRÊN) ✓
  - 768px → IMAGE left=64, CONTENT left=381 (2 cột) ✓
  - 1280px → IMAGE left=64, CONTENT left=627 (2 cột) ✓

  `DG_VERSION` → `1.15.81`. Không commit.

- `2026-11-05a`: hover `.dg-oi-btn` (Our Ingredients) đi **sáng hơn** thay vì đậm lại.

  Vấn đề: token `--oi-btn-bg-hover` dùng `linear-gradient(135deg, var(--gold-500), var(--gold-600))` — hover làm nút **tối đi** (min contrast 4.52:1 = chỉ AA), trái với hướng "Luminous Ethereal" của design system.

  Sửa (chỉ đổi token trong `style.css` `:root`, không đụng `our-ingredients.css`):
  `--oi-btn-bg-hover: linear-gradient(135deg, var(--gold-bright), var(--gold-400))`.

  Kiểm chứng (Playwright, computed style thật):
  - REST `rgb(212,175,55) → rgb(201,162,75)` — contrast 8.17 → 7.16:1 (AAA)
  - HOVER `rgb(241,202,80) → rgb(212,175,55)` — contrast 10.88 → 8.17:1 (AAA)
  - ΔL hover − rest = **+11.38** (sáng hơn) ✓
  - Mũi tên `::after`, transform `translateY(-2px)`, `box-shadow` giữ nguyên.

  `DG_VERSION` → `1.15.80`. Không commit.

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
