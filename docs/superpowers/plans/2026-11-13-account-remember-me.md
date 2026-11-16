# Account Remember Me Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the signed-out My Account remember-me checkbox preserve its submitted state after failed login while retaining native WooCommerce persistent-login behavior.

**Architecture:** Keep the native `rememberme=forever` input and WooCommerce authentication flow unchanged. Restore checked state only from a scalar `rememberme` field in the current login POST, and scope interaction styling to the existing account checkbox BEM selectors.

**Tech Stack:** WordPress/WooCommerce PHP template, theme CSS, browser verification.

**Spec:** `docs/superpowers/specs/2026-11-13-account-remember-me-design.md`

## Global Constraints

- Do not modify authentication/session cookie policy or checkout login.
- Default checkbox state remains unchecked.
- Only exact submitted scalar value `forever` restores checked state.
- No JavaScript dependency or additional runtime package.
- Do not commit Git.

## Review Focus

- No login POST: checkbox remains unchecked.
- Failed POST with `rememberme=forever`: checkbox is checked on re-render.
- Missing, array-shaped, or unexpected values: checkbox remains unchecked without PHP warnings.
- Keyboard focus remains clearly visible and label toggles the native checkbox.
- Successful login continues to use WooCommerce's existing persistent-login handling.

---

### Task 1: Restore the checkbox state safely

**Files:**
- Modify: `wp-content/themes/dragon-glow/inc/woocommerce/account/login.php`

**Interfaces:**
- Consumes: current request's `$_POST['rememberme']` on the My Account login render.
- Produces: checked HTML attribute only when the submitted value is a scalar exactly equal to `forever`.

- [ ] **Step 1: Render the signed-out login template with absent, valid, invalid, and array-shaped `rememberme` request values**

  Use the existing local WordPress/browser setup or a focused PHP harness and record the current result; valid submitted selection should currently demonstrate the missing re-render behavior.

- [ ] **Step 2: Add a narrowly scoped, escaped request check in the login renderer**

  Unslash and sanitize only when the key exists and is scalar; compare exactly to `forever`; add the conditional `checked` attribute to the existing native checkbox. Do not change input name/value or WooCommerce hooks.

- [ ] **Step 3: Re-run the focused render checks**

  Verify absent, invalid, and array-shaped values render unchecked; exact `forever` renders checked; no PHP warnings are emitted.

- [ ] **Step 4: Run PHP syntax verification**

  Run `php -l wp-content/themes/dragon-glow/inc/woocommerce/account/login.php`.
  Expected: no syntax errors.

### Task 2: Verify interaction and visual states

**Files:**
- Inspect/modify only if required: `wp-content/themes/dragon-glow/assets/css/account.css`
- Verify: signed-out My Account login page in browser.

**Interfaces:**
- Consumes: existing `.dg-account-checkbox` markup and the browser's native checkbox behavior.
- Produces: visible focus indication and an adequately usable label/control at mobile and desktop viewports.

- [ ] **Step 1: Inspect computed checkbox and label styles at 390px and 1280px**

  Verify checkbox remains native, label click toggles it, keyboard Space toggles it, and `:focus-visible` can be perceived.

- [ ] **Step 2: Add minimal scoped CSS only if inspection finds a real gap**

  Use existing theme tokens and `.dg-account-checkbox` selectors; avoid changing unrelated shared account forms.

- [ ] **Step 3: Verify browser behavior at 390px and 1280px**

  Confirm label and checkbox remain aligned, keyboard operation/focus are clear, and no horizontal overflow or regression occurs.

- [ ] **Step 4: Verify the POST contract remains intact**

  Inspect submitted form data with the checkbox unchecked and checked. Expected: unchecked omits `rememberme`; checked submits `rememberme=forever`, leaving cookie handling to WooCommerce.
