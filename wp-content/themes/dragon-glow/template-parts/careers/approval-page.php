<?php
/**
 * Template part — Careers / Approval Page
 *
 * Được route từ inc/approval-handler.php khi URL chứa ?dg_action=approve|reject&token=…
 * HR mở trang này → điền form → POST lại → hệ thống gửi email cho khách.
 *
 * Style khớp với brand Dragon Glow (Geist, neutral + gold accent).
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;
?>
<style>
.dg-approval {
	font-family: "Geist Variable", "Geist", system-ui, -apple-system, sans-serif;
	max-width: 640px;
	margin: clamp(40px, 8vw, 96px) auto;
	padding: 0 24px;
	color: #1c1b1b;
}
.dg-approval-title {
	font-size: clamp(1.75rem, 4vw, 2.25rem);
	font-weight: 300;
	letter-spacing: -0.02em;
	line-height: 1.2;
	margin: 0 0 0.5rem;
}
.dg-approval-title--expired {
	color: #735C00;
}
.dg-approval-title--action {
	color: #735C00;
	font-family: "Geist Variable", "Geist", system-ui, -apple-system, sans-serif;
	font-weight: 600;
}
.dg-approval-eyebrow {
	font-size: 0.75rem;
	font-weight: 600;
	letter-spacing: 0.1em;
	text-transform: uppercase;
	color: #c5a059;
	margin: 0 0 1rem;
}
.dg-approval-card {
	background: #fcf9f8;
	border: 1px solid #c7c7bf;
	padding: clamp(24px, 5vw, 40px);
	margin-bottom: 1.5rem;
}
.dg-approval-summary {
	margin-bottom: 1.5rem;
	padding: 16px 0;
	border-top: 1px solid #c7c7bf;
	border-bottom: 1px solid #c7c7bf;
	font-size: 0.9375rem;
	line-height: 1.7;
}
.dg-approval-summary dt {
	font-weight: 600;
	color: #464742;
	display: inline-block;
	min-width: 88px;
}
.dg-approval-summary dd {
	display: inline;
	margin: 0 0 0 0.5rem;
}
.dg-approval-summary dd::after {
	content: " ";
	display: block;
}
.dg-approval-form-field {
	margin-bottom: 1.25rem;
}
.dg-approval-label {
	display: block;
	font-size: 0.75rem;
	font-weight: 600;
	letter-spacing: 0.05em;
	text-transform: uppercase;
	margin-bottom: 0.5rem;
}
.dg-approval-input,
.dg-approval-textarea {
	width: 100%;
	padding: 0.75rem 0.875rem;
	font-family: inherit;
	font-size: 1rem;
	color: #1c1b1b;
	background: #fcf9f8;
	border: 1px solid #c7c7bf;
	box-sizing: border-box;
}
.dg-approval-input:focus,
.dg-approval-textarea:focus {
	outline: 2px solid #c5a059;
	outline-offset: 1px;
	border-color: #c5a059;
}
.dg-approval-textarea {
	min-height: 96px;
	resize: vertical;
}
.dg-approval-actions {
	margin-top: 1.5rem;
	display: flex;
	gap: 0.75rem;
	flex-wrap: wrap;
}
.dg-approval-btn {
	font-family: inherit;
	font-size: 0.75rem;
	font-weight: 600;
	letter-spacing: 0.1em;
	text-transform: uppercase;
	padding: 1rem 2rem;
	border: 1px solid #1c1b1b;
	background: transparent;
	color: #1c1b1b;
	cursor: pointer;
	text-decoration: none;
	display: inline-block;
}
.dg-approval-btn.is-primary {
	background: #1c1b1b;
	color: #fcf9f8;
}
.dg-approval-btn:focus-visible {
	outline: 2px solid #c5a059;
	outline-offset: 3px;
}
.dg-approval-grid {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 1rem;
}
@media (max-width: 480px) {
	.dg-approval-grid { grid-template-columns: 1fr; }
}
.dg-approval-banner {
	margin-bottom: 1rem;
	padding: 14px 18px;
	border: 1px solid #c7c7bf;
	background: #f6f3f2;
	font-size: 0.9375rem;
	line-height: 1.5;
}
.dg-approval-banner--ok {
	border-color: #2d6a30;
	background: #f1f6f1;
	color: #1f4421;
}
.dg-approval-banner--err {
	border-color: #ba1a1a;
	background: #fbe9e9;
	color: #600a0a;
}
</style>

<main class="dg-approval" id="main-content">

<?php if ( ! $dg_payload ) : ?>
	<p class="dg-approval-eyebrow">Decision</p>
	<h1 class="dg-approval-title dg-approval-title--expired">This link has expired.</h1>
	<div class="dg-approval-banner dg-approval-banner--err">
		<?php esc_html_e( 'This approval link is invalid or has already been used. If you still need to follow up on this application, please reply directly to the original email.', 'dragon-glow' ); ?>
	</div>

<?php else : ?>
	<p class="dg-approval-eyebrow">
		<?php echo 'approve' === $dg_action ? esc_html__( 'Approve candidate', 'dragon-glow' ) : esc_html__( 'Reject candidate', 'dragon-glow' ); ?>
	</p>
	<h1 class="dg-approval-title dg-approval-title--action">
		<?php echo 'approve' === $dg_action ? esc_html__( 'Approve & schedule interview', 'dragon-glow' ) : esc_html__( 'Send rejection', 'dragon-glow' ); ?>
	</h1>

	<div class="dg-approval-card">
		<dl class="dg-approval-summary">
			<dt><?php esc_html_e( 'Role', 'dragon-glow' ); ?></dt><dd><?php echo esc_html( $dg_payload['role'] ); ?></dd>
			<dt><?php esc_html_e( 'Name', 'dragon-glow' ); ?></dt><dd><?php echo esc_html( $dg_payload['name'] ); ?></dd>
			<dt><?php esc_html_e( 'Email', 'dragon-glow' ); ?></dt><dd><a href="mailto:<?php echo esc_attr( $dg_payload['email'] ); ?>" style="color:#1c1b1b;"><?php echo esc_html( $dg_payload['email'] ); ?></a></dd>
			<?php if ( ! empty( $dg_payload['phone'] ) ) : ?>
				<dt><?php esc_html_e( 'Phone', 'dragon-glow' ); ?></dt><dd><?php echo esc_html( $dg_payload['phone'] ); ?></dd>
			<?php endif; ?>
			<?php if ( ! empty( $dg_payload['linkedin'] ) ) : ?>
				<dt><?php esc_html_e( 'LinkedIn', 'dragon-glow' ); ?></dt><dd><?php echo esc_html( $dg_payload['linkedin'] ); ?></dd>
			<?php endif; ?>
			<?php if ( ! empty( $dg_payload['note'] ) ) : ?>
				<dt><?php esc_html_e( 'Note', 'dragon-glow' ); ?></dt><dd><?php echo nl2br( esc_html( $dg_payload['note'] ) ); ?></dd>
			<?php endif; ?>
		</dl>

		<?php if ( $dg_processed && $dg_processed['ok'] ) : ?>
			<div class="dg-approval-banner dg-approval-banner--ok">
				<?php echo esc_html( $dg_processed['message'] ); ?>
				<?php esc_html_e( 'You can close this page. The decision has been recorded.', 'dragon-glow' ); ?>
			</div>

		<?php elseif ( $dg_processed && ! $dg_processed['ok'] ) : ?>
			<div class="dg-approval-banner dg-approval-banner--err">
				<?php echo esc_html( $dg_processed['message'] ); ?>
			</div>
			<?php
		endif;

		if ( ! $dg_processed ) :
			if ( 'approve' === $dg_action ) : ?>
				<form method="post" novalidate>
					<?php wp_nonce_field( 'dg_approval_decision_approve', 'dg_approval_nonce' ); ?>
					<input type="hidden" name="dg_approval_submit" value="1">

					<div class="dg-approval-grid">
						<div class="dg-approval-form-field">
							<label class="dg-approval-label" for="dg_interview_date"><?php esc_html_e( 'Date', 'dragon-glow' ); ?></label>
							<input class="dg-approval-input" type="date" name="dg_interview_date" id="dg_interview_date" required>
						</div>
						<div class="dg-approval-form-field">
							<label class="dg-approval-label" for="dg_interview_time"><?php esc_html_e( 'Time', 'dragon-glow' ); ?></label>
							<input class="dg-approval-input" type="time" name="dg_interview_time" id="dg_interview_time" required>
						</div>
					</div>

					<div class="dg-approval-form-field">
						<label class="dg-approval-label" for="dg_interview_duration"><?php esc_html_e( 'Duration (minutes)', 'dragon-glow' ); ?></label>
						<input class="dg-approval-input" type="number" name="dg_interview_duration" id="dg_interview_duration" value="45" min="15" max="240" step="15">
					</div>

					<div class="dg-approval-form-field">
						<label class="dg-approval-label" for="dg_interview_location"><?php esc_html_e( 'Location or video link', 'dragon-glow' ); ?></label>
						<input class="dg-approval-input" type="text" name="dg_interview_location" id="dg_interview_location" required placeholder="<?php esc_attr_e( 'New York studio, 5th Ave — or https://meet.example.com/abc', 'dragon-glow' ); ?>">
					</div>

					<div class="dg-approval-form-field">
						<label class="dg-approval-label" for="dg_interview_message"><?php esc_html_e( 'Personal note (optional)', 'dragon-glow' ); ?></label>
						<textarea class="dg-approval-textarea" name="dg_interview_message" id="dg_interview_message" placeholder="<?php esc_attr_e( 'Anything you would like the candidate to know before the interview.', 'dragon-glow' ); ?>"></textarea>
					</div>

					<div class="dg-approval-actions">
						<button type="submit" class="dg-approval-btn is-primary">
							<?php esc_html_e( 'Send interview invitation', 'dragon-glow' ); ?>
						</button>
					</div>
				</form>

			<?php else : ?>
				<form method="post" novalidate>
					<?php wp_nonce_field( 'dg_approval_decision_reject', 'dg_approval_nonce' ); ?>
					<input type="hidden" name="dg_approval_submit" value="1">

					<div class="dg-approval-form-field">
						<label class="dg-approval-label" for="dg_reject_reason"><?php esc_html_e( 'Reason (optional, included in the email)', 'dragon-glow' ); ?></label>
						<textarea class="dg-approval-textarea" name="dg_reject_reason" id="dg_reject_reason" placeholder="<?php esc_attr_e( 'We will be transparent — share as much as feels appropriate.', 'dragon-glow' ); ?>"></textarea>
					</div>

					<div class="dg-approval-actions">
						<button type="submit" class="dg-approval-btn is-primary">
							<?php esc_html_e( 'Send rejection', 'dragon-glow' ); ?>
						</button>
					</div>
				</form>
			<?php endif;
		endif; ?>
	</div>

	<p style="color:#888;font-size:0.875rem;">
		<?php esc_html_e( 'Reply directly to the candidate email is not required — the system sends the message for you once you submit.', 'dragon-glow' ); ?>
	</p>

<?php endif; ?>

</main>
