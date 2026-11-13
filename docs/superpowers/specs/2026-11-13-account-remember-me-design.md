# Account Remember Me Design

## Intent
Make the signed-out My Account “Keep me signed in” checkbox work predictably with WordPress/WooCommerce while preserving the user's choice after a failed login.

## Goals
- Leave the checkbox unchecked unless the current login POST submitted `rememberme`.
- Let WooCommerce handle the standard `rememberme=forever` login cookie behavior.
- Restore checked state on a failed login POST; escape and unslash request data safely.
- Keep the control keyboard operable, visibly focusable, and styled consistently with the existing login form.

## Non-goals and anti-goals
- Do not change WordPress authentication, cookie lifetime, session policy, or checkout login behavior.
- Do not persist the choice across visits or pre-check it by default.
- Do not add custom JavaScript behavior or a new dependency for a native checkbox.

## Constraints
- Classic WordPress/WooCommerce theme; PHP templates and existing CSS conventions.
- Keep the login form field name/value expected by WooCommerce: `rememberme=forever`.
- No Git commit.

## Approaches
1. Use the native WooCommerce checkbox semantics and safely restore its checked state from the failed login POST; add focused, scoped CSS as needed. Chosen because it preserves core authentication and only repairs this form's user-facing behavior.
2. Add custom authentication/session handling. Rejected because WooCommerce already implements this field and custom handling risks cookie/security regressions.
3. Pre-check the control by default. Rejected because users on shared devices may unintentionally create a persistent login.

## Implementation calls
- Scope state restoration to the signed-out My Account login template and its login POST.
- Treat the field as checked only when the submitted scalar value is exactly `forever`.
- Preserve native checkbox semantics and add `:focus-visible`/sufficient touch interaction through existing BEM CSS.
- Verify PHP syntax and render/interaction at desktop and mobile sizes; confirm no JS dependency is introduced.
