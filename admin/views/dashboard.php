<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap ceac-admin-wrap">
	<?php
	$has_api_key = ! empty( CEAC_Settings::get_api_key() );
	$has_provider = ! empty( CEAC_Settings::get( 'provider' ) );
	$has_sync = get_option( 'ceac_last_sync' );
	$steps = array(
		array( 'label' => __( 'Configure AI', 'ceac' ), 'done' => $has_api_key && $has_provider, 'url' => admin_url( 'admin.php?page=ceac-provider' ) ),
		array( 'label' => __( 'Sync Content', 'ceac' ), 'done' => (bool) $has_sync, 'url' => '#' ),
		array( 'label' => __( 'Customize Widget', 'ceac' ), 'done' => false, 'url' => admin_url( 'admin.php?page=ceac-widget' ) ),
		array( 'label' => __( 'Go Live', 'ceac' ), 'done' => $has_api_key && $has_provider && $has_sync, 'url' => '#' ),
	);
	$completed = 0;
	foreach ( $steps as $s ) { if ( $s['done'] ) $completed++; }
	$pct = $completed > 0 ? round( $completed / count( $steps ) * 100 ) : 0;
	?>
	<div class="ceac-card">
		<div class="ceac-card-header">
			<h2><?php esc_html_e( 'Setup Progress', 'ceac' ); ?></h2>
			<span class="ceac-badge ceac-badge-<?php echo $pct === 100 ? 'success' : 'info'; ?>"><?php echo $pct; ?>% <?php esc_html_e( 'complete', 'ceac' ); ?></span>
		</div>
		<div class="ceac-onboarding-progress" style="--ceac-pct:<?php echo $pct; ?>%">
			<?php foreach ( $steps as $i => $step ) : ?>
				<div class="ceac-onboarding-step <?php echo $step['done'] ? 'completed' : ( ! isset( $prev_done ) || $prev_done ? 'active' : '' ); ?>">
					<?php $prev_done = $step['done']; ?>
					<div class="ceac-step-num"><?php echo $step['done'] ? '✓' : ( $i + 1 ); ?></div>
					<div class="ceac-step-label"><?php echo esc_html( $step['label'] ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<div class="ceac-dashboard-grid">
		<div class="ceac-stat-card">
			<h3><?php esc_html_e( 'Total Chats', 'ceac' ); ?></h3>
			<p class="ceac-stat-value" id="ceac-stat-chats">—</p>
		</div>
		<div class="ceac-stat-card">
			<h3><?php esc_html_e( 'Resolution Rate', 'ceac' ); ?></h3>
			<p class="ceac-stat-value" id="ceac-stat-resolution">—</p>
		</div>
		<div class="ceac-stat-card">
			<h3><?php esc_html_e( 'Fallback Rate', 'ceac' ); ?></h3>
			<p class="ceac-stat-value" id="ceac-stat-fallback">—</p>
		</div>
		<div class="ceac-stat-card">
			<h3><?php esc_html_e( 'Token Usage (30d)', 'ceac' ); ?></h3>
			<p class="ceac-stat-value" id="ceac-stat-tokens">—</p>
		</div>
	</div>

	<div class="ceac-card">
		<div class="ceac-card-header">
			<h2><?php esc_html_e( 'Quick Actions', 'ceac' ); ?></h2>
		</div>
		<div class="ceac-action-grid">
			<a href="#" class="ceac-action-card" id="ceac-sync-content">
				<span class="dashicons dashicons-update"></span>
				<span><?php esc_html_e( 'Sync Website Content', 'ceac' ); ?></span>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ceac-provider' ) ); ?>" class="ceac-action-card">
				<span class="dashicons dashicons-cloud"></span>
				<span><?php esc_html_e( 'Configure AI Provider', 'ceac' ); ?></span>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ceac-knowledge' ) ); ?>" class="ceac-action-card">
				<span class="dashicons dashicons-networking"></span>
				<span><?php esc_html_e( 'View Knowledge Graph', 'ceac' ); ?></span>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ceac-analytics' ) ); ?>" class="ceac-action-card">
				<span class="dashicons dashicons-chart-area"></span>
				<span><?php esc_html_e( 'Analytics', 'ceac' ); ?></span>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ceac-conversations' ) ); ?>" class="ceac-action-card">
				<span class="dashicons dashicons-format-chat"></span>
				<span><?php esc_html_e( 'Conversations', 'ceac' ); ?></span>
			</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ceac-widget' ) ); ?>" class="ceac-action-card">
				<span class="dashicons dashicons-screenoptions"></span>
				<span><?php esc_html_e( 'Widget Settings', 'ceac' ); ?></span>
			</a>
		</div>
	</div>
</div>
