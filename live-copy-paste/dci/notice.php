<?php
/**
 * Insights Notice File
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'dci_popup_notice' ) ) {
	function dci_popup_notice( $data ) {

		?>
		<div class="dci-notice">
			<div class="dci-notice-wrapper">
				<div class="dci-header">
					<!-- <div class="dci-logo">
				<img src="plugins_url( 'assets/images/logo.png', __FILE__ );" alt="logo">
						</div> -->
					<h2 class="dci-title">
						<?php esc_html_e( 'Never miss an important update.', 'live-copy-paste' ); ?>
					</h2>
					<p class="dci-desc">
						<?php esc_html_e( 'Get notified when there are new updates available for your plugins and themes. In the current version of this message it is clear that you are being sent a confirmation email to confirm your opt-in.', 'live-copy-paste' ); ?>
					</p>
				</div>
				<div class="dci-actions">
					<?php
					$dci_name       = isset( $data['name'] ) ? $data['name'] : '';
					$dci_date_name  = isset( $data['date_name'] ) ? $data['date_name'] : '';
					$dci_allow_name = isset( $data['allow_name'] ) ? $data['allow_name'] : '';
					$nonce          = isset( $data['nonce'] ) ? $data['nonce'] : '';
					?>
					<form method="get" class="dci-notice-data">
						<input type="hidden" name="dci_name" value="<?php echo esc_html( $dci_name ); ?>">
						<input type="hidden" name="dci_date_name" value="<?php echo esc_html( $dci_date_name ); ?>">
						<input type="hidden" name="dci_allow_name" value="<?php echo esc_html( $dci_allow_name ); ?>">
						<input type="hidden" name="nonce" value="<?php echo esc_html( $nonce ); ?>">

						<button id="dci_allow_yes" name="dci_allow_status" value="yes" type="button"
							class="dci-button-allow button button-primary">
							<?php esc_html_e( 'Allow & Continue', 'live-copy-paste' ); ?>
						</button>
						<button id="dci_allow_skip" name="dci_allow_status" value="skip" type="button"
							class="dci-button-skip button button-secondary">
							<?php esc_html_e( 'Skip', 'live-copy-paste' ); ?>
						</button>
					</form>
				</div>
				<div class="dci-permission">
					<p>
						<?php esc_html_e( 'Which permission are being granted?', 'live-copy-paste' ); ?>
					</p>
				</div>
				<div class="dci-data-list">
					<ul>
						<li>
							<div class="dci-permission-item">
								<div class="dci-icon">
									<span class="dashicons dashicons-admin-users"></span>
								</div>
								<div class="dci-desc">
									<h3>
										<?php esc_html_e( 'View Basic Profile Info.', 'live-copy-paste' ); ?>
									</h3>
									<p>
										<?php esc_html_e( 'Your WordPress user\'s first & last name, and email address.', 'live-copy-paste' ); ?>
									</p>
								</div>
							</div>
						</li>
						<li>
							<div class="dci-permission-item">
								<div class="dci-icon">
									<span class="dashicons dashicons-admin-links"></span>
								</div>
								<div class="dci-desc">
									<h3>
										<?php esc_html_e( 'View Basic Website Info.', 'live-copy-paste' ); ?>
									</h3>
									<p>
										<?php esc_html_e( 'Homepage URL & title, WP & PHP versions, and site language.', 'live-copy-paste' ); ?>
									</p>
								</div>
							</div>
						</li>
						<li>
							<div class="dci-permission-item">
								<div class="dci-icon">
									<span class="dashicons dashicons-admin-plugins"></span>
								</div>
								<div class="dci-desc">
									<h3>
										<?php esc_html_e( 'View Basic Plugin Info.', 'live-copy-paste' ); ?>
									</h3>
									<p>
										<?php esc_html_e( 'Current Plugin & SDK versions, and if active or uninstalled.', 'live-copy-paste' ); ?>
									</p>
								</div>
							</div>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<?php
	}

	// add_action( 'in_admin_header', 'dci_popup_notice', 99999 );
}
