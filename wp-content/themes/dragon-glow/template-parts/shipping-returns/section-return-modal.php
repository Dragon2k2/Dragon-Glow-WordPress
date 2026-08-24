<?php
/**
 * Dragon Glow — Shipping & Returns: Return Request Modal
 *
 * Glassmorphism modal for initiating a return request.
 * Collects: email (required), order number (optional), return reason (required).
 * Sends admin notification email on submission.
 *
 * Accessibility: focus trap, ESC to close, aria-modal, live status.
 * Motion: overlay fade + dialog slide-up (Motion vanilla API).
 * Graceful degradation: modal hidden until JS opens; no JS = no modal.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;
?>
<div
	class="dg-rm"
	id="dg-return-modal"
	role="dialog"
	aria-modal="true"
	aria-labelledby="dg-rm-title"
	aria-describedby="dg-rm-desc"
	hidden
>
	<div class="dg-rm-overlay" data-rm-close></div>

	<div class="dg-rm-dialog" role="document">
		<button
			type="button"
			class="dg-rm-close"
			data-rm-close
			aria-label="<?php esc_attr_e( 'Close', 'dragon-glow' ); ?>"
		>
			<span aria-hidden="true">&times;</span>
		</button>

		<header class="dg-rm-header">
			<p class="dg-rm-eyebrow"><?php esc_html_e( 'Returns', 'dragon-glow' ); ?></p>
			<h2 class="dg-rm-title" id="dg-rm-title">
				<?php esc_html_e( 'Begin a Return', 'dragon-glow' ); ?>
			</h2>
			<p class="dg-rm-desc" id="dg-rm-desc">
				<?php esc_html_e(
					'Tell us what is not working for you. We will sort it out within two business days.',
					'dragon-glow'
				); ?>
			</p>
		</header>

		<form
			class="dg-rm-form"
			id="dg-return-form"
			method="post"
			novalidate
		>
			<!-- Order number (optional) -->
			<div class="dg-rm-field">
				<label class="dg-rm-label" for="dg-rm-order">
					<?php esc_html_e( 'Order number', 'dragon-glow' ); ?>
					<span class="dg-rm-optional">(<?php esc_html_e( 'optional', 'dragon-glow' ); ?>)</span>
				</label>
				<input
					class="dg-rm-input"
					id="dg-rm-order"
					type="text"
					name="order_number"
					autocomplete="off"
					placeholder="<?php esc_attr_e( 'e.g. DG-12345', 'dragon-glow' ); ?>"
				>
				<p class="dg-rm-hint">
					<?php esc_html_e( 'Found in your confirmation email.', 'dragon-glow' ); ?>
				</p>
			</div>

			<!-- Email (required) -->
			<div class="dg-rm-field">
				<label class="dg-rm-label" for="dg-rm-email">
					<?php esc_html_e( 'Email address', 'dragon-glow' ); ?>
					<span class="dg-rm-required" aria-hidden="true">*</span>
				</label>
				<input
					class="dg-rm-input"
					id="dg-rm-email"
					type="email"
					name="email"
					autocomplete="email"
					required
					placeholder="you@example.com"
				>
			</div>

			<!-- Return reason (required) -->
			<div class="dg-rm-field">
				<label class="dg-rm-label" for="dg-rm-reason">
					<?php esc_html_e( 'Reason for return', 'dragon-glow' ); ?>
					<span class="dg-rm-required" aria-hidden="true">*</span>
				</label>
				<select
					class="dg-rm-input dg-rm-select"
					id="dg-rm-reason"
					name="reason"
					required
				>
					<option value="" disabled selected>
						<?php esc_html_e( 'Select a reason', 'dragon-glow' ); ?>
					</option>
					<option value="defective"><?php esc_html_e( 'Defective or damaged product', 'dragon-glow' ); ?></option>
					<option value="wrong_item"><?php esc_html_e( 'Received the wrong item', 'dragon-glow' ); ?></option>
					<option value="not_as_described"><?php esc_html_e( 'Not as described', 'dragon-glow' ); ?></option>
					<option value="changed_mind"><?php esc_html_e( 'Changed my mind', 'dragon-glow' ); ?></option>
					<option value="other"><?php esc_html_e( 'Other', 'dragon-glow' ); ?></option>
				</select>
			</div>

			<!-- Additional notes (optional) -->
			<div class="dg-rm-field">
				<label class="dg-rm-label" for="dg-rm-notes">
					<?php esc_html_e( 'Additional details', 'dragon-glow' ); ?>
					<span class="dg-rm-optional">(<?php esc_html_e( 'optional', 'dragon-glow' ); ?>)</span>
				</label>
				<textarea
					class="dg-rm-input dg-rm-textarea"
					id="dg-rm-notes"
					name="notes"
					rows="3"
					placeholder="<?php esc_attr_e( 'Tell us more about your experience.', 'dragon-glow' ); ?>"
				></textarea>
			</div>

			<!-- Privacy consent -->
			<p class="dg-rm-consent">
				<?php
				printf(
					/* translators: %s: privacy policy URL */
					esc_html__(
						'By submitting this form you agree to our %s.',
						'dragon-glow'
					),
					'<a href="' . esc_url( home_url( '/privacy-policy/' ) ) . '" target="_blank" rel="noopener noreferrer">' .
					esc_html__( 'Privacy Policy', 'dragon-glow' ) .
					'</a>'
				);
				?>
			</p>

			<!-- Status message -->
			<div
				class="dg-rm-status"
				id="dg-return-status"
				role="status"
				aria-live="polite"
			></div>

			<!-- Actions -->
			<div class="dg-rm-actions">
				<button
					type="button"
					class="dg-rm-btn dg-rm-btn--secondary"
					data-rm-close
				>
					<?php esc_html_e( 'Cancel', 'dragon-glow' ); ?>
				</button>
				<button
					type="submit"
					class="dg-rm-btn dg-rm-btn--primary"
					id="dg-return-submit"
				>
					<?php esc_html_e( 'Submit request', 'dragon-glow' ); ?>
				</button>
			</div>
		</form>
	</div>
</div>
