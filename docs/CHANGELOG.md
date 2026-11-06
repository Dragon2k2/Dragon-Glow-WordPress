# Changelog - Dragon Glow

> Moved out of `CLAUDE.md` so it stops loading on every session.
> `CLAUDE.md` keeps only the 5 most recent entries.

- `2026-11-06i`: Header — **fix bug text "Menu" thừa hiện ra ở desktop** (trong `div.dg-nav-actions`).

  **Root cause:** `$nav_aria = esc_attr_e( 'Menu', 'dragon-glow' );` — dùng nhầm biến thể `_e` (echo) thay vì `__` (return). `esc_attr_e()` **echo** ra output nên chữ "Menu" bị in thành text node ngay trong markup (hiện ra ở desktop vì nút toggle bị ẩn bởi `lg:hidden` nhưng text node thì không), đồng thời `$nav_aria` thành `null` → `aria-label=""` rỗng.

  Fix: đổi sang `esc_attr__( 'Menu', 'dragon-glow' )` + comment giải thích để không lặp lại lỗi này.

  Kiểm chứng (`tmp-resp/nav-cart-badge/render-header-nav.php` — render template với WP functions stubbed):
  - Markup toggle: `<button ... aria-label="Menu, 4 items" ... data-dg-label="Menu">` ✓
  - Không còn text node "Menu" trong button / trong `.dg-nav-actions` ✓
  - `aria-label` không rỗng ✓

  Không đổi `DG_VERSION` (không có thay đổi asset). Không commit git.

- `2026-11-06h`: Header — **badge wishlist trên menu item "Wishlist" trong drawer mobile/tablet** + **badge TỔNG (giỏ hàng + wishlist) trên nút menu mobile/tablet**.

  Yêu cầu: click `.dg-wishlist-toggle` → badge wishlist phải hiện trên nút menu mobile (`#dg-mobile-menu-toggle`) và trên menu item "Wishlist" trong drawer (`#dg-mobile-menu-item--wishlist`).

  **Quyết định thiết kế (có hỏi user):** nút toggle chỉ 40×40px, không đủ chỗ cho 2 pill → chọn **1 badge TỔNG** (giỏ hàng + wishlist) thay vì 2 badge chồng nhau.

  Sửa:
  - `inc/helpers/ui-components.php` — `dg_render_nav_count_badge()` (helper mới): render pill tổng với class riêng `.dg-nav-badge` (**không** dùng `.dg-cart-count`, vì `DGCartBadge.paint()` sẽ ghi đè giá trị tổng bằng count giỏ hàng). Item "Wishlist" trong drawer giờ render `dg_render_wishlist_count_badge()` + `aria-label` server-side ("Wishlist, 2 saved items"). Item "Cart" cũng bổ sung `aria-label` server-side.
  - `template-parts/global/header-nav.php` — nút toggle dùng `dg_render_nav_count_badge( $cart_count, $wishlist_count )`, `aria-label` server-side ("Menu, 4 items"). `$wishlist_count` tính 1 lần/request, tái dùng cho cả badge desktop.
  - `assets/js/lib/nav-badge.js` (module mới) — sở hữu pill `.dg-nav-badge`: giữ 2 bộ đếm (cart/wishlist), tính tổng, vẽ + animation `is-pulse`/`is-first-show`, đồng bộ `aria-label` của toggle. API: `setCart()`, `setWishlist()`, `refresh()`.
  - `assets/js/lib/cart-badge.js` — `paint()` gọi thêm `DGNavBadge.setCart(total)`; `syncAriaLabels()` bỏ phần toggle (nay do `nav-badge.js` lo).
  - `assets/js/lib/wishlist-toggle.js` — `applyBadgeCount()` / `paintBadgeCount()` gọi thêm `DGNavBadge.setWishlist(n)` + `syncWishlistAriaLabel()` (aria-label cho link "Wishlist" trong drawer).
  - `assets/js/wishlist.js` — `paintWishlistBadges()` (gộp 3 chỗ vẽ badge trùng lặp) + `syncWishlistAriaLabel()`.
  - `assets/css/header-nav.css` — §4d-1 đổi từ `.dg-cart-count` sang `.dg-nav-badge`; §4d-2 gộp badge Cart + badge Wishlist trong drawer (cùng rule `position: static; margin-left: auto`).
  - `inc/helpers/wishlist.php` — `dg_get_wishlist_count()` memoize theo request (bị đọc 3 lần/render), cache bị invalidate trong `dg_set_wishlist()`; `dg_render_wishlist_count_badge()` nhận thêm param `$count` để không gọi lại lookup.
  - `inc/enqueue/scripts.php` — enqueue `dg-nav-badge` + thêm i18n `navItemSingular/navItemPlural/savedSingular/savedPlural`.

  Kiểm chứng (Playwright, `tmp-resp/nav-cart-badge/nav-badge-*.js`):
  - Click `.dg-wishlist-toggle` → badge drawer "Wishlist" 1→2, badge tổng trên toggle 4→5, aria-label rebuild ở cả 2 host ✓ (320/390/768/1023/1024px)
  - `paint(0)` + `setWishlist(0)` → `display: none` (override tường minh `.hidden`) ✓
  - Kích thước: 1–2 chữ số = 20×20px, 4 chữ số = 30×20px, không tràn viewport ở 320px ✓
  - `prefers-reduced-motion: reduce` → `animation-name: none` ✓
  - Desktop 1280px: toggle `display: none`, badge heart/bag desktop giữ nguyên 20px ✓

  `DG_VERSION` → `1.16.7`. Không commit git.

- `2026-11-06g`: Header — **badge số lượng giỏ hàng trên menu item "Cart" trong drawer mobile/tablet** + **fix bug badge không ẩn khi count = 0**.

  Yêu cầu: `<li class="menu-item dg-mobile-menu-item--cart">` trong drawer (`#dg-mobile-menu`) hiện số lượng giỏ hàng, giống badge `.dg-cart-count` ở header.

  Sửa:
  - `inc/helpers/ui-components.php` — `dg_inject_mobile_menu_items()` render thêm `dg_render_cart_count_badge( dg_get_cart_item_count() )` vào sau label trong thẻ `<a>` của item Cart. Tái dùng helper sẵn có → markup/class giống hệt 2 badge còn lại, nên `DGCartBadge.paint()` cập nhật cả 3 cùng lúc.
    - Thẻ `<a>` có `data-dg-label="Cart"` → JS dựng lại `aria-label` ("Cart, 3 items in bag") mỗi lần count đổi.
  - `assets/css/header-nav.css` (block mới §4d-2) — badge trong drawer nằm **trong flow** (không absolute như badge trên toggle): `position: static` + `margin-left: auto` → pill đẩy sát mép phải của row, đọc như cột số lượng. `min-width: 20px; padding: 0 4px` → số 2–3 chữ số tự giãn pill. Animation `is-pulse` / `is-first-show` giống 2 badge kia.
  - `assets/js/lib/cart-badge.js` — `syncToggleAriaLabel()` → `syncAriaLabels()`: đồng bộ `aria-label` cho **cả** toggle và drawer Cart item.

  **BUG FIX (phát hiện khi verify):** rule `.dg-nav-toggle .dg-cart-count { display: inline-flex }` có specificity (0,2,0) **thắng** class `hidden` của Tailwind (0,1,0) → badge **vẫn hiện khi giỏ rỗng**. Đã thêm override tường minh `.dg-nav-toggle .dg-cart-count.hidden { display: none }` (và tương tự cho badge trong drawer). Đây là bug do chính thay đổi ở entry `2026-11-06f` gây ra.

  Kiểm chứng (Playwright, `tmp-resp/nav-cart-badge-drawer*.js`, `nav-cart-badge-hide-check.js`):
  - 320 / 390 / 768 / 1023px → badge trong drawer: `position=static`, 20×20px, cách mép phải row 21px (đúng padding), không đè glyph ✓
  - `paint(7)` → `is-pulse`; `paint(0)` → `display: none`; `paint(2)` → `is-first-show`; `paint(1)` → aria "Cart, 1 item in bag"; `bump(+3)` → `4` ✓
  - Pixel: fill `#735c00` + chữ trắng, badge nằm trong row ✓
  - `prefers-reduced-motion: reduce` → `animation-name: none` ✓
  - Regression toggle badge (entry `2026-11-06f`): 6 breakpoint vẫn ✓, `paint(0)` giờ ẩn đúng ✓

  `DG_VERSION` → `1.16.6`. Không commit git.

- `2026-11-06f`: Header — **badge số lượng giỏ hàng trên nút menu mobile/tablet** (`#dg-mobile-menu-toggle`), có animation pop mượt.

  Vấn đề gốc: icon giỏ hàng ở desktop nằm trong `<a class="hidden lg:block relative">` → **bị ẩn hoàn toàn dưới 1024px**. Badge `.dg-cart-count` vốn chỉ render cạnh icon đó, nên trên mobile/tablet **không có bất kỳ chỗ nào hiển thị số lượng giỏ hàng** — người dùng điện thoại phải mở drawer mới biết túi có hàng hay không.

  Sửa:
  - `template-parts/global/header-nav.php` — render `dg_render_cart_count_badge( $cart_count )` **bên trong** nút `#dg-mobile-menu-toggle` (thêm class `relative` để làm anchor). Tái sử dụng nguyên helper sẵn có → markup/class giống hệt badge desktop, không viết lại markup.
  - `assets/js/lib/cart-badge.js` (module mới) — `window.DGCartBadge.paint(count)` / `.bump(delta)`: nơi duy nhất vẽ badge + kích hoạt animation.
    - `is-pulse` → số đổi → scale pop nhẹ.
    - `is-first-show` → badge hiện lần đầu → entrance mạnh hơn.
    - Cả 2 class luôn được xoá trước khi thêm lại (force reflow) → animation restart sạch khi bấm liên tục, không để class cũ tranh cascade.
  - 6 call site cũ tự vẽ badge riêng (`lib/cart-api.js`, `lib/cart-feedback.js`, `cart.js`, `thankyou.js`, `wishlist.js`) → chuyển sang dùng `DGCartBadge` (giữ fallback cũ nếu module chưa load).
  - `assets/css/header-nav.css` (block mới §4d-1) — style + keyframes `dgCartBadgePop` / `dgCartBadgeFirstShow`, **scope dưới `.dg-nav-toggle`** → desktop badge giữ nguyên 100%, không animation (đúng yêu cầu "không làm ở desktop").
    - Badge trên toggle dùng `width: auto; min-width: 20px; padding: 0 4px` → số 2–3 chữ số tự giãn pill thay vì bị cắt (desktop vẫn giữ `w-5` cố định).
    - `box-shadow: 0 0 0 2px var(--color-surface)` → ring nền tách badge khỏi glyph menu.
  - `inc/enqueue/scripts.php` — enqueue `dg-cart-badge` + thêm vào deps của `dg-cart-api`, `dg-cart`, `dg-thankyou`.

  Kiểm chứng (Playwright, `tmp-resp/nav-cart-badge-*.js`):
  - 320 / 390 / 768 / 1023px → toggle hiện + badge hiện, `size=20x20`, offset `right=-4px top=-4px` (đúng parity với badge wishlist desktop) ✓
  - 1024 / 1280px → toggle `display=none`, badge không render → **desktop không đổi** ✓
  - `paint(7)` → text đổi + `is-pulse` bật; `paint(0)` → ẩn; `paint(2)` → `is-first-show`; `bump(+2)` → `4` ✓
  - `aria-label`: "Menu, 4 items in bag" khi count=4, "Menu" khi count=0 ✓
  - Badge 1–1234 chữ số: pill 20→30px, không cắt chữ ✓
  - `prefers-reduced-motion: reduce` → `animation-name: none` ✓
  - Pixel analysis: badge che 5.4% glyph menu (tương đương badge wishlist) ✓

  A11y:
  - Nút toggle có `data-dg-label` (label gốc) → `cart-badge.js` đồng bộ `aria-label` mỗi lần count đổi: "Menu, 4 items in bag" / về "Menu" khi rỗng. Badge **không** bị `aria-hidden` — theo accname spec, `aria-label` của host thắng descendant text nên số không bị đọc 2 lần.
  - i18n mới trong `dgAjax.i18n`: `itemSingular` ("1 item in bag") + `itemPlural` ("%d items in bag").

  `DG_VERSION` → `1.16.5`. Không commit git.

- `2026-11-06e`: Shop — **nút "Add to Cart" luôn hiển thị ở mobile + tablet**, không cần hover.

  Vấn đề gốc: `.dg-add-to-cart` mặc định `opacity: 0` + `transform: translateY(16px)` (ẩn), chỉ hiện khi `.dg-product-card:hover` — thiết kế cho chuột.
  - Touch device không có hover → nút không bao giờ hiện, không tappable.
  - Tablet có chuột nhưng người dùng vẫn hay dùng chạm → hover không đáng tin.

  Theme đã có tiền lệ ở `home.css` (§ best-sellers, `.dg-home-product .dg-quick-add`) và `main.css` (§ carousel, `#dg-carousel .dg-quick-add`) dùng `@media (hover: none)`. Ở đây dùng **chiều rộng** (`max-width: 1023px`) thay vì `hover: none` vì yêu cầu là "mobile + tablet luôn hiện" — tablet có chuột vẫn phải hiện.

  Sửa (`assets/css/shop.css`, block mới sau `.dg-product-card:hover .dg-add-to-cart`):
  - `.dg-add-to-cart` → `opacity: 1` + `transform: translateY(0)`.
  - `.dg-add-to-cart::before` → `left: 160%` + `transition: none` — sheen dịch ra ngoài khung ngay lập tức, không để lại vệt sáng đọng trên nút (trước nó chỉ chạy khi hover card).
  - `.dg-add-to-cart:hover` → `transform: translateY(-2px)` — hiệu ứng nhấc nút khi hover/tap, khớp với base rule (base rule dùng `translateX(-50%)`, nhưng rule pill ở §13 đặt sau nên `left/right` + `translateY` mới là giá trị thực thi).
  - `.dg-add-to-cart.dg-quick-add--added:hover` → `transform: translateY(-2px)` — giữ trạng thái "added" vĩnh viễn nhất quán.

  Breakpoint 1024px = mốc theme chuyển sang layout 2–3 cột (`lg:grid-cols-12` / `lg:grid-cols-3`). Dưới mốc này grid là 1–2 cột, card nhỏ hơn → nút cần luôn hiện. **Desktop (>= 1024px) giữ nguyên hover-reveal.**

  Kiểm chứng (Playwright, `tmp-resp/shop-quickadd-check.js` — đo cả `elementFromPoint` để xác nhận nút thật sự bắt click, không chỉ `opacity`):
  - 320 / 390 / 430 / 639px (mobile) → không hover: `opacity=1`, `transform=matrix(1,0,0,1,0,0)`, `hit=true` ✓
  - 640 / 768 / 1023px (tablet) → không hover: `opacity=1`, `hit=true` ✓
  - 1024 / 1280px (desktop) → không hover: `opacity=0` + `translateY(16px)`; hover: `opacity=1` — **không đổi** ✓
  - Kiểm tra hồi quy: nút `opacity: 0` ở desktop vẫn bắt `elementFromPoint`, nhưng khi chuột đi tới vị trí đó thì `:hover` của card kích hoạt → `opacity=1` → click vào nút hoạt động đúng (không chặn click ảnh) ✓

  `DG_VERSION` → `1.16.4`. Không commit git.

- `2026-11-06d`: Wishlist — **empty state bullets hiển thị xấu ở mobile**: bị đẩy lệch sang phải, chữ vỡ 2–3 dòng.

  Vấn đề gốc: `.dg-wishlist-empty__bullets` có `padding-left: 105px` **hard-code**, thiết kế cho cột 540px ở desktop (căn list trong khung rộng). Ở mobile khối ngoài chỉ còn ~250–340px → 105px chiếm **39% bề rộng**, list bị đẩy lệch, chữ bó sát mép phải và vỡ dòng:
    - 390px → text rộng 191px (2 dòng)
    - 320px → text rộng 121px (**3 dòng**)

  Không có media query nào cho `.dg-wishlist-empty__bullets` → mobile kế thừa nguyên layout desktop.

  Sửa (`assets/css/wishlist.css`, block mới §6b `@media (max-width: 639px)`):
  - `.dg-wishlist-empty__bullets` → `padding-left: var(--wl-empty-bullets-indent-mobile)` (0px — bỏ indent, icon dẫn hàng), `gap: var(--wl-empty-bullets-row-gap-mobile)`.
  - `.dg-wishlist-empty__bullets li` → `grid-template-columns: var(--wl-empty-bullets-icon-size-mobile) 1fr` (icon 32px → 28px), `gap: var(--wl-empty-bullets-gap-mobile)`, `font-size: var(--wl-empty-bullets-font-size-mobile)`, `line-height: 1.45`.
  - `.dg-wishlist-empty__bullets .material-symbols-outlined` → tile 28×28px, `font-size: 16px`.
  - `.dg-wishlist-empty` → `padding: 2rem 1.25rem` (mobile nhỏ gọn hơn).
  - `.dg-wishlist-empty__actions` → `flex-direction: column` + nút `width: 100%` (2 nút cạnh nhau quá hẹp, chữ "Explore the collection" bị bó).

  Token mới trong `style.css` (§ Wishlist Component): `--wl-empty-bullets-indent-mobile`, `--wl-empty-bullets-icon-size-mobile`, `--wl-empty-bullets-gap-mobile`, `--wl-empty-bullets-row-gap-mobile`, `--wl-empty-bullets-font-size-mobile`.

  Kiểm chứng (Playwright, `tmp-resp/wl-empty-check.js`):
  - 320px → text rộng 121px (3 dòng) → **240px (2 dòng)** ✓
  - 390px → text rộng 191px (2 dòng) → **310px (1 dòng)** ✓
  - 430px → text rộng 231px (2 dòng) → **350px (1 dòng)** ✓
  - 639px → text rộng 391px (1 dòng) → 502px ✓
  - 640 / 768 / 1024 / 1280px (tablet + desktop) → `padding-left` vẫn 105px, icon 32px, text 391px — **không đổi** ✓
  - Không overflow ngang ở mốc nào ✓

  `DG_VERSION` → `1.16.3`. Không commit git.

- `2026-11-06c`: hero trang chủ — **badge pill nằm TRÊN product frame ở tablet** (640–1023px). Trước đó tablet vẫn giữ layout cũ: copy trên, ảnh dưới.

  Sửa (`assets/css/home.css`, block `@media (min-width: 640px) and (max-width: 1023px)`) — **dùng lại đúng kỹ thuật của block mobile**, chỉ khác giá trị token:
  - `.dg-home-hero .dg-home-hero__copy` → `display: contents`.
  - `.dg-home-hero .dg-home-hero__copy > *` → `min-width: 0` + `order: 0` + `justify-self: start`.
  - `.dg-home-hero .dg-home-hero__copy > .dg-home-hero__badge` → `order: -2` (badge trên cùng).
  - `.dg-home-hero .dg-home-hero__visual` → `order: -1` (giữ nguyên: frame ngay dưới badge).
  - `.dg-home-hero-wrapper .dg-home-hero` → `row-gap: var(--hero-stack-gap-tablet)` (2 class để thắng utility `gap-8` của Tailwind CDN — §8b).
  - `.dg-home-hero__product-frame` → `margin-top: 0` + `margin-bottom: var(--hero-frame-bottom-gap-tablet)`.
  - `style.css` — thêm token `--hero-stack-gap-tablet: 1.5rem` và `--hero-frame-bottom-gap-tablet: 0.5rem`.

  Bổ sung `justify-self: start` (áp dụng cho **cả** mobile và tablet):
  - Cột copy gốc là flex `items-start` → khối con co theo nội dung. Khi hòa tan thành grid item, mặc định là `stretch` (full-width) → sai hành vi.
  - Đo được trước khi sửa: title mobile 357px → 390px sau khi `display: contents`. Sau khi thêm `justify-self: start`: title 390px → 357px, khớp lại baseline flex.
  - Các khối tự khai báo width (stats row `w-full`, CTA `w-full sm:w-auto`) vẫn giữ width của chúng.

  Kiểm chứng (Playwright, `tmp-resp/home-hero-order.js` — đã mở rộng nhánh tablet):
  - 640 / 768 / 1023px → `copy.display=contents`, badge `order=-2` top=40, frame top=100, headline top=782 → **badge TRÊN, frame GIỮA, copy DƯỚI** ✓
  - 320 / 390 / 430 / 639px (mobile) → không đổi so với `2026-11-06b` ✓
  - 1024 / 1280px (desktop) → `copy.display=flex`, grid 1 hàng 12 cột, copy trái / visual phải — **không đổi** ✓
  - Độ rộng khối con ở tablet khớp baseline cũ: badge 372px, title 670px, desc 489px, CTA 319px ✓
  - Không overflow ngang ở bất kỳ mốc nào ✓

  `DG_VERSION` → `1.16.2`. Không commit git.

- `2026-11-06b`: hero trang chủ — **badge pill nằm TRÊN product frame ở mobile** (trước: frame trên, badge bị đẩy xuống dưới cả tiêu đề).

  Bối cảnh: sau `2026-11-06a` (đảo 2 khối ở mobile → ảnh lên trên), badge `.dg-home-hero__badge` vẫn nằm trong cột copy nên rơi xuống **dưới** tiêu đề + mô tả + CTA — sai thứ tự đọc trên mobile. Yêu cầu: badge phải nằm trên thẻ product frame, chỉ áp dụng mobile.

  Sửa:
  - `template-parts/home/hero.php` — thêm class theme-controlled `dg-home-hero__copy` cho cột copy (trước chỉ có `lg:col-span-6 flex …`, không có class để target an toàn theo §5g).
  - `assets/css/home.css`, block mobile `@media (max-width: 639px)`:
    - `.dg-home-hero .dg-home-hero__copy` → `display: contents` — hòa tan box của cột copy, các phần tử con (badge / headline / desc / CTA / stats) trở thành grid item trực tiếp của hero grid → mỗi phần tử `order` độc lập.
    - `.dg-home-hero .dg-home-hero__copy > *` → `order: 0` + `min-width: 0` (reset, thay cho `min-width: 0` cũ do `.dg-home-hero > div` đảm nhiệm).
    - `.dg-home-hero .dg-home-hero__copy > .dg-home-hero__badge` → `order: -2` (badge trên cùng).
    - `.dg-home-hero .dg-home-hero__visual` → `order: -1` (giữ nguyên: frame ngay dưới badge).
    - `.dg-home-hero-wrapper .dg-home-hero` → `row-gap: var(--hero-stack-gap-mobile)` (2 class để thắng utility `gap-8` của Tailwind CDN — §8b).
    - `.dg-home-hero__product-frame` → `margin-top: 0` + `margin-bottom: var(--hero-frame-bottom-gap-mobile)`.
  - `style.css` — thêm token `--hero-stack-gap-mobile: 1.5rem` và `--hero-frame-bottom-gap-mobile: 0.5rem`.

  Vì sao dùng `display: contents` chứ không đổi DOM:
  - Badge và product frame **không phải anh em** trong DOM (badge nằm trong cột copy) → `order` đơn thuần không diễn tả được "badge, frame, rồi phần copy còn lại".
  - `display: contents` chỉ ảnh hưởng **layout**, không đổi DOM → screen reader và SEO vẫn đọc copy trước (thông điệp chính), không cần sửa template/touch semantics.
  - `display: contents` chỉ bật trong media query mobile → desktop/tablet giữ nguyên layout 2 cột, `display: flex` của cột copy.

  Kiểm chứng (Playwright, `tmp-resp/home-hero-order.js`):
  - 320 / 390 / 430 / 639px → `copy.display=contents`, badge `order=-2` top=40, frame top=88, headline top=520/608/658/770 → **badge TRÊN, frame GIỮA, copy DƯỚI** ✓
  - 640 / 768 / 1023px (tablet) → `copy.display=flex`, `order=0` → copy trên, visual dưới — **không đổi** ✓
  - 1024 / 1280px (desktop) → grid 1 hàng 12 cột, copy trái / visual phải — **không đổi** ✓
  - Căn trái: badge `left=0` khớp title `left=0`, frame `left=0` (639px: frame `left=60` do đã center trong cột max-width 520px) ✓
  - Không overflow ngang ở bất kỳ mốc nào ✓

  `DG_VERSION` → `1.16.1`. Không commit git.

- `2026-11-06a`: hero trang chủ — **đảo thứ tự 2 khối ở mobile**: product frame (ảnh) LÊN TRÊN, cột copy (tiêu đề + CTA) XUỐNG DƯỚI.

  Bối cảnh: DOM order của `template-parts/home/hero.php` là copy → visual (đúng cho desktop 2 cột và cho screen reader/SEO). Ở mobile grid 1 cột thì copy nằm trên, ảnh sản phẩm nằm dưới — nhưng trên màn hình dọc nên ảnh sản phẩm dẫn nhịp trước.

  Sửa (`assets/css/home.css`, block mobile `@media (max-width: 639px)`):
  - `.dg-home-hero .dg-home-hero__visual` → `order: -1`.
  - `.dg-home-hero .dg-home-hero__product-frame` → `margin-top: 0` (thay cho `var(--hero-frame-margin-top-mobile)`).

  Vì sao dùng `order` chứ không đổi DOM:
  - `order` chỉ sắp xếp lại **hiển thị** grid item, DOM giữ nguyên → screen reader vẫn đọc copy trước (thông điệp chính), SEO không đổi, không cần sửa template.
  - Cột copy không có class theme-controlled nhưng **không cần rule**: grid item mặc định `order: 0`, chỉ cần hạ visual xuống `-1`.
  - `margin-top` cũ của frame được bỏ vì frame giờ là hàng đầu của grid — margin đó chỉ có ý nghĩa khi frame nằm SAU copy. Khoảng cách visual → copy giờ do `gap: 2rem` của grid đảm nhiệm.

  Kiểm chứng (Playwright, `tmp-resp/home-hero-order.js` — mock nạp Tailwind CDN để `lg:grid-cols-12` / `aspect-[4/5]` có tác dụng):
  - 320 / 390 / 430 / 639px → `order: -1`, grid 2 hàng, **visual top < copy top** (ảnh trên, chữ dưới) ✓
  - 640 / 768 / 1023px (tablet) → `order: 0`, copy trước, visual sau — **không đổi** ✓
  - 1024 / 1280px (desktop) → grid 1 hàng 12 cột, copy trái / visual phải — **không đổi** ✓
  - Frame margin-top = 0 ở mobile, 32px ở tablet/desktop ✓

  `DG_VERSION` → `1.16.0`. Không commit git.

- `2026-11-05t`: chip filter Wishlist — chip **active không còn đổi style khi hover**; chip chưa active giữ nguyên hover.

  Vấn đề: `.dg-wishlist-toolbar__seg:hover` và `.dg-wishlist-toolbar__seg.is-active` cùng specificity (0,2,0), nhưng rule hover đứng **trước** → khi hover chip active, `background: var(--wl-chip-bg)` + `color: var(--wl-primary)` ghi đè nền vàng active → chip active bị "bay màu" khi chuột đi qua.

  Sửa (`assets/css/wishlist.css`):
  - `.dg-wishlist-toolbar__seg:hover` → `.dg-wishlist-toolbar__seg:not(.is-active):hover`
  - `.dg-wishlist-toolbar__seg:hover .dg-wishlist-toolbar__count` → `.dg-wishlist-toolbar__seg:not(.is-active):hover .dg-wishlist-toolbar__count`

  Không có rule nào ở media query nào bị ảnh hưởng → **mobile / tablet / desktop đồng bộ** theo yêu cầu.

  Kiểm chứng (Playwright, `tmp-resp/wl-hover-check.js` + `wl-hover-lifecycle.js` — dùng `page.hover()` thật vì synthetic event không kích hoạt CSS `:hover`):
  - 320 / 390 / 639 / 640 / 768 / 1280px → chip ACTIVE hover giữ nguyên `bg=rgb(115,92,0)` + chữ trắng + badge trắng 25% ✓
  - Chip INACTIVE hover → đổi sang `bg=rgba(115,92,0,0.06)` + chữ `rgb(115,92,0)` + badge `rgba(115,92,0,0.12)` ✓
  - Vòng đời chuyển trạng thái (JS đổi `is-active`): chip cũ **lấy lại** hover style, chip mới giữ nền active, `aria-selected` đồng bộ ✓

  `DG_VERSION` → `1.15.99`. Không commit git.

- `2026-11-05s`: chip filter Wishlist ở mobile — **giảm font chữ + icon**, text/icon **không còn sát viền chip**.

  Vấn đề: ở mobile icon + text + badge vẫn dồn sát viền chip (padding 5px, chỉ ~3px từ text tới viền). Bản `2026-11-05r` đã dùng `padding: clamp(5px,...)` nhưng font 10px + icon 13px + badge 15px → nội dung chiếm gần hết chip, không còn dư chỗ thoáng.

  Sửa (`assets/css/wishlist.css`, block `§12b` — `@media (max-width: 639px)`):
  - `.dg-wishlist-toolbar__seg` → `font-size: clamp(10px, 2.5vw, 12px)` (giữ ≥ 10px để dễ đọc), `padding: clamp(5.5px, 1.6vw, 9px)`, `gap: clamp(3px, 0.9vw, 5px)`.
  - `.dg-wishlist-toolbar__seg .material-symbols-outlined` → `font-size: clamp(11px, 2.8vw, 15px)` (giảm từ 13px).
  - `.dg-wishlist-toolbar__count` → `min-width/height: clamp(12px, 3.6vw, 20px)` (giảm từ 15px), `font-size: clamp(9px, 2.2vw, 11px)`.

  Nguyên tắc: **giảm kích thước nội dung để MUA được không gian padding**. Chip co lại → lấy phần không gian đó làm padding lớn hơn → text/icon tách xa viền.

  Tối ưu bằng script brute-force (`tmp-resp/wl-chip-optimize.js`): quét 3.000+ tổ hợp (font, icon, badge, gap, padding) với ràng buộc tổng 3 chip ≤ content 244px ở 320px, ưu tiên **font lớn nhất** rồi **padding lớn nhất**. Kết quả chọn: font 10px / icon 11px / badge 12px / gap 3px / padding 5.5px.

  Kiểm chứng (Playwright):
  - 320/360/390/430/639px → overflow nội dung chip = **0px** ✓
  - Padding chip: 5.5px (320px) → 6.2px (390px) → 6.9px (430px) → 9px (639px) — text không còn sát viền ✓
  - Khoảng cách text ↔ icon / text ↔ badge: 3–5px đều đặn ✓
  - Segment không tràn ngang: 3 chip 236px + 8px gap = 244px = content 254px ✓ (dư 5px mỗi bên)
  - 640px & 768px → **không đổi** (fs 13px, padding 14px, icon 16px như desktop) ✓
  - Select + sort vẫn cùng 1 hàng ngang, "Recently saved" không bị cắt ✓

  `DG_VERSION` → `1.15.98`. Không commit git.

- `2026-11-05r`: chip filter Wishlist ở mobile — **hiện lại icon** (giống desktop) + text **thoải mái, không sát viền**.

  Vấn đề: ở mobile chip bị ép vào 1/3 chiều ngang (`repeat(3, minmax(0, 1fr))`) → chỉ có 79px ở 320px, trong khi bộ "icon + text + badge" cần ~91px ở font 12px → tràn; để vừa phải ẩn icon và rút padding xuống 3.2px → chữ sát viền, không thoải mái.

  Sửa (`assets/css/wishlist.css`, block `§12b` — `@media (max-width: 639px)`):
  - `.dg-wishlist-toolbar__segment` → `grid-template-columns: repeat(3, auto)` + `justify-content: space-between` (thay vì `minmax(0, 1fr)`) → mỗi chip có bề rộng **đúng bằng nội dung** → padding thoải mái, không dồn chữ.
  - `.dg-wishlist-toolbar__seg .material-symbols-outlined` → `display: inline-block` (bỏ `display: none`) + `font-size: clamp(13px, 3.2vw, 16px)` → icon hiện lại giống desktop nhưng co nhỏ theo viewport.
  - Mọi kích thước của chip dùng **fluid `clamp()`** để tự co giãn theo viewport:
    - `font-size: clamp(10px, 2.6vw, 12px)` → 10px ở 320px, 12px ở 460px+
    - `padding: clamp(5px, 1.4vw, 8px)` → 5px ở 320px, 8px ở 570px+
    - `gap: clamp(3px, 0.9vw, 5px)`
    - icon `clamp(13px, 3.2vw, 16px)`, badge `clamp(15px, 4.2vw, 20px)` (+ `flex-shrink: 0` để không bị bóp méo).
  - `justify-content: center` trong chip → nội dung luôn căn giữa.

  Số đo thật (Playwright): Ở 320px 3 chip = 77 + 74 + 72px + 2 gap 4px = **231px** < content 244px → vừa khít, không tràn, không cần cuộn ngang.

  Kiểm chứng (Playwright, `tmp-resp/wl-chip-pad.js` + `wl-overflow-check.js`):
  - 320/360/390/430/639px → icon **CÓ** trong cả 3 chip, overflow nội dung = **0px** ✓
  - Padding chip: 5px (320px) → 5.5px (390px) → 6px (430px) → 8px (639px) — text không còn sát viền ✓
  - Khoảng cách text ↔ icon / text ↔ badge: 3–5px, đều đặn ✓
  - 640px & 768px → **không đổi** (fs 13px, padding 14px, icon 16px như desktop) ✓
  - Select + sort vẫn cùng 1 hàng ngang, "Recently saved" không bị cắt ✓

  `DG_VERSION` → `1.15.97`. Không commit git.

- `2026-11-05q`: toolbar Wishlist — "Select (0)" và nút sort **cùng 1 hàng ngang** ở mobile (thay đổi so với `2026-11-05p` vốn xếp dọc).

  Sửa (`assets/css/wishlist.css`, block `§12b` — `@media (max-width: 639px)`):
  - `.dg-wishlist-toolbar__right` → `grid-template-columns: auto minmax(0, 1fr)` (thay vì `minmax(0, 1fr)` 1 cột) → select-all cột 1, sort cột 2, cùng hàng.
  - `.dg-wishlist-toolbar__select-all` → `flex-shrink: 0` để không bị co lại.
  - `.dg-wishlist-toolbar__sort-trigger` → `width: 100%` + `justify-content: flex-start`; chevron `margin-left: auto` → đẩy mép phải.

  Số đo thật (Playwright) quyết định giữ được 1 hàng:
  - select-all = **85px** (checkbox 20px + gap + chữ "Select (0)" 60px)
  - sort trigger tối thiểu = padding 24 + "Recently saved" **96px** + chevron 20px + gap 6 = **146px**
  - tổng + gap 10px = **241px** < content 256px ở 320px → **vừa đủ**, chữ không bị cắt.

  Vì sao icon `schedule` vẫn ẩn ở mobile: giữ lại thì tổng thành 267px > 256px → "Recently saved" bị cắt thành "Recently save…". Icon là decoration; chevron vẫn giữ nên affordance dropdown rõ ràng.

  Lưu ý kỹ thuật: file `assets/fonts/material-symbols-outlined.woff2` **không có trong repo** → ligature render thành chữ "expand_more" (109px) khi đo. Mock phải ép `width: 1em` cho icon để số đo phản ánh đúng bề rộng ligature thật (~20px).

  Kiểm chứng (Playwright, `tmp-resp/wl-samerow-check.js` + `wl-overflow-check.js` + `wl-panel-check.js`):
  - 320/360/390/430/639px → select-all & trigger **cùng hàng** (center lệch < 3px), gap = 10px ✓
  - "Recently saved" overflow = **0px** ở mọi width ✓
  - Toolbar cao 122px (2 hàng, giảm từ 153px ở bản xếp dọc) ✓
  - Sort panel mở: `[47..287]` / `[117..357]` / `[150..397]` — trọn trong viewport ✓
  - 640 / 768 / 1280px → **không đổi** (vẫn hiện nhãn "Sort by") ✓

  `DG_VERSION` → `1.15.96`. Không commit git.

- `2026-11-05p`: toolbar Wishlist (`.dg-wishlist-toolbar`) **mobile-only** — 3 chip filter về 1 hàng đều nhau, sort full-width.

  Vấn đề: ở mobile toolbar vỡ — 3 chip filter (`All items` / `In stock` / `On sale`) cần tổng ~531px nhưng content chỉ ~252–362px → `flex-wrap` làm "On sale" rớt hàng; sort trigger rộng ~292px > content 252px ở 320px → tràn ngang.

  Sửa (`assets/css/wishlist.css`) — block mới `§12b` dưới `@media (max-width: 639px)`, tách riêng khỏi block `767px` sẵn có để **tablet (640–767px) giữ nguyên layout desktop**:
  - `.dg-wishlist-toolbar__segment` → `display: grid` + `grid-template-columns: repeat(3, minmax(0, 1fr))`, `flex-wrap: nowrap` → 3 chip đều nhau, 1 hàng.
  - `.dg-wishlist-toolbar__seg` → `padding: 8px 6px`, `font-size: 12px`, `letter-spacing: 0.01em`, `white-space: nowrap`.
  - Icon filter (`apps` / `check_circle` / `local_offer`) → `display: none` ở mobile (decoration, chữ đã đủ nghĩa; vẫn còn trong DOM cho a11y).
  - `.dg-wishlist-toolbar__right` → grid 1 cột, xếp dọc: "Select (0)" 1 hàng, sort 1 hàng full-width. *(Sau đó đổi lại thành 1 hàng ngang ở `2026-11-05q`.)*
  - `.dg-wishlist-toolbar__sort-label` ("Sort by") → ẩn; icon `schedule` → ẩn (decoration) để nhường chỗ cho nhãn giá trị; chevron giữ + `margin-left: auto` → đẩy mép phải.
  - `.dg-wishlist-toolbar__sort-trigger` → `width: 100%`, `justify-content: flex-start`.

  Vì sao sort xếp dọc thay vì chung hàng với "Select (0)": ở 320px, sort chỉ còn ~159px (sau khi trừ select-all 85px + gap) trong khi "Recently saved" cần ~236px → bị cắt "Recently save…". Full-width = 254px → vừa đủ, **overflow = 0** ở mọi mobile width.

  Kiểm chứng (Playwright, `tmp-resp/wl-toolbar-measure.js` + `wl-overflow-check.js` + `wl-panel-check.js`):
  - 320/360/390/430/639px → 3 chip **1 hàng**, đều nhau (79/102/115px), overflow nội dung = 0 ✓
  - Sort trigger full-width, "Recently saved" **không bị cắt** (overflow 0px) ✓
  - Sort panel mở: `[40..287]` / `[110..357]` / `[150..397]` — nằm trọn trong viewport ✓
  - 640px & 768px & 1280px → **không đổi** (3 chip inline, còn nhãn "Sort by" 57px) ✓

  `DG_VERSION` → `1.15.95`. Không commit git.

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
