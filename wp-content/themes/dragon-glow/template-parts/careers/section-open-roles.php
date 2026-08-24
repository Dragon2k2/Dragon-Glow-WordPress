<?php
/**
 * Template part — Careers / Section 6: Open roles
 *
 * 6 role row, mỗi row là 1 flex (mobile col / desktop row) gồm:
 * title (1/3) + team/location/type (1/2) + Apply link (1/6).
 * Hairline-b 0.5px gold. Bg surface-container-low. id="open-roles" cho CTA hero.
 *
 * @package Dragon_Glow
 */

defined( 'ABSPATH' ) || exit;

$roles = dg_careers_roles_data();
?>
<section class="dg-careers-roles" id="open-roles" data-sr>
	<h2 class="dg-careers-section-title dg-careers-section-title--emphasis"><?php esc_html_e( 'Open Roles', 'dragon-glow' ); ?></h2>
	<?php if ( ! empty( $roles ) ) : ?>
		<ul class="dg-careers-roles-list">
			<?php foreach ( $roles as $role ) : ?>
				<li class="dg-careers-role-row">
					<div class="dg-careers-role-title">
						<span class="dg-careers-role-name"><?php echo esc_html( $role['title'] ); ?></span>
					</div>
					<div class="dg-careers-role-meta">
						<span class="dg-careers-role-team"><?php echo esc_html( $role['team'] ); ?></span>
						<span class="dg-careers-role-location"><?php echo esc_html( $role['location'] ); ?></span>
						<span class="dg-careers-role-type"><?php echo esc_html( $role['type'] ); ?></span>
					</div>
					<div class="dg-careers-role-apply">
						<button
							type="button"
							class="dg-careers-role-apply-link"
							data-apply-trigger
							data-role="<?php echo esc_attr( $role['title'] ); ?>"
						><?php esc_html_e( 'Apply', 'dragon-glow' ); ?></button>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</section>