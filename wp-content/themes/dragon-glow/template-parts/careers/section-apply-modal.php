<?php
/**
 * Template part — Careers / Apply Modal
 *
 * Modal glassmorphism (overlay + dialog) chứa form apply. Nằm ngoài .dg-careers
 * scope để không bị giới hạn font Geist; vẫn dùng BEM class dg-apply-* và
 * design token --c-* riêng.
 *
 * Khi JS chưa load / disabled: modal đóng (visibility: hidden). JS toggle
 * attribute data-open để chuyển trạng thái mở/đóng.
 *
 * Form schema từ dg_careers_apply_form_fields() — filterable.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$fields = dg_careers_apply_form_fields();
?>
<div
	class="dg-apply"
	id="dg-apply-modal"
	role="dialog"
	aria-modal="true"
	aria-labelledby="dg-apply-title"
	aria-describedby="dg-apply-desc"
	hidden
>
	<div class="dg-apply-overlay" data-apply-close></div>

	<div
		class="dg-apply-dialog"
		role="document"
	>
		<button
			type="button"
			class="dg-apply-close"
			data-apply-close
			aria-label="<?php esc_attr_e( 'Close', 'dragon-glow' ); ?>"
		>
			<span aria-hidden="true">&times;</span>
		</button>

		<header class="dg-apply-header">
			<p class="dg-apply-eyebrow"><?php esc_html_e( 'Apply', 'dragon-glow' ); ?></p>
			<h2 class="dg-apply-title" id="dg-apply-title">
				<?php esc_html_e( 'Apply for this role', 'dragon-glow' ); ?>
			</h2>
			<p class="dg-apply-role">
				<?php esc_html_e( 'Role', 'dragon-glow' ); ?><span class="dg-apply-colon" aria-hidden="true">:</span>
				<strong id="dg-apply-role-label"></strong>
			</p>
			<p class="dg-apply-desc" id="dg-apply-desc">
				<?php esc_html_e(
					'Tell us a little about yourself. We read every application personally and respond within five business days.',
					'dragon-glow'
				); ?>
			</p>
		</header>

		<form
			class="dg-apply-form"
			id="dg-apply-form"
			method="post"
			enctype="multipart/form-data"
			novalidate
		>
			<input type="hidden" name="role" id="dg-apply-role" value="">

			<?php foreach ( $fields as $field ) :
				$key      = $field['key'];
				$type     = $field['type'];
				$required = ! empty( $field['required'] );
				$label    = $field['label'];
				$ph       = $field['placeholder'] ?? '';
				?>
				<div class="dg-apply-field">
					<label class="dg-apply-label" for="dg-apply-<?php echo esc_attr( $key ); ?>">
						<?php echo esc_html( $label ); ?>
						<?php if ( $required ) : ?>
							<span class="dg-apply-required" aria-hidden="true">*</span>
						<?php endif; ?>
					</label>

					<?php if ( 'textarea' === $type ) : ?>
						<textarea
							class="dg-apply-input dg-apply-textarea"
							id="dg-apply-<?php echo esc_attr( $key ); ?>"
							name="<?php echo esc_attr( $key ); ?>"
							rows="4"
							<?php echo $required ? 'required' : ''; ?>
							<?php echo $ph ? 'placeholder="' . esc_attr( $ph ) . '"' : ''; ?>
						></textarea>
					<?php elseif ( 'file' === $type ) : ?>
						<input
							class="dg-apply-input dg-apply-file"
							id="dg-apply-<?php echo esc_attr( $key ); ?>"
							type="file"
							name="<?php echo esc_attr( $key ); ?>"
							<?php echo ! empty( $field['accept'] ) ? 'accept="' . esc_attr( $field['accept'] ) . '"' : ''; ?>
						>
					<?php else : ?>
						<input
							class="dg-apply-input"
							id="dg-apply-<?php echo esc_attr( $key ); ?>"
							type="<?php echo esc_attr( $type ); ?>"
							name="<?php echo esc_attr( $key ); ?>"
							autocomplete="<?php echo 'email' === $type ? 'email' : ( 'tel' === $type ? 'tel' : 'off' ); ?>"
							<?php echo $required ? 'required' : ''; ?>
							<?php echo $ph ? 'placeholder="' . esc_attr( $ph ) . '"' : ''; ?>
						>
					<?php endif; ?>
				</div>
				<?php if ( 'phone' === $key ) : ?>
					<div class="dg-apply-field dg-apply-field--desired-role" id="dg-apply-desired-role-wrap">
						<label class="dg-apply-label" for="dg-apply-desired-role">
							<?php esc_html_e( 'Preferred role (optional)', 'dragon-glow' ); ?>
						</label>
						<input
							class="dg-apply-input"
							id="dg-apply-desired-role"
							type="text"
							name="desired_role"
							placeholder="<?php esc_attr_e( 'Tell us the role you are interested in', 'dragon-glow' ); ?>"
							autocomplete="off"
						>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>

			<p class="dg-apply-consent">
				<label>
					<input type="checkbox" name="consent" id="dg-apply-consent" required>
					<span>
						<?php
						echo wp_kses(
							__( 'I agree that Dragon Glow may store and process my application for recruitment purposes as described in the <a href="/privacy-policy/" target="_blank" rel="noopener noreferrer">Privacy Policy</a>.', 'dragon-glow' ),
							array(
								'a' => array(
									'href'   => array(),
									'target' => array(),
									'rel'    => array(),
								),
							)
						);
						?>
					</span>
				</label>
			</p>

			<div
				class="dg-apply-status"
				id="dg-apply-status"
				role="status"
				aria-live="polite"
			></div>

			<div class="dg-apply-actions">
				<button
					type="button"
					class="dg-careers-btn dg-careers-btn--secondary"
					data-apply-close
				><?php esc_html_e( 'Cancel', 'dragon-glow' ); ?></button>
				<button
					type="submit"
					class="dg-careers-btn dg-careers-btn--primary"
					id="dg-apply-submit"
				><?php esc_html_e( 'Send application', 'dragon-glow' ); ?></button>
			</div>
		</form>
	</div>
</div>