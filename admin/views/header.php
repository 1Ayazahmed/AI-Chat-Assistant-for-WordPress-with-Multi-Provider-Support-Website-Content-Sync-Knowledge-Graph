<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="ceac-admin-header">
	<div class="ceac-header-top">
		<h1><?php echo esc_html( CEAC_Settings::get( 'chat_header_title', __( 'AI Assistant', 'ceac' ) ) ); ?></h1>
		<div class="ceac-header-meta">
			<span>v<?php echo esc_html( $version ); ?></span>
			<span style="margin-left:12px"><?php esc_html_e( 'Last sync:', 'ceac' ); ?> <span id="ceac-last-sync"><?php echo esc_html( $last_sync ); ?></span></span>
		</div>
	</div>
	<nav class="ceac-header-nav">
		<?php foreach ( $nav_items as $page => $item ) : ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . $page ) ); ?>"
			   class="<?php echo $current === $page ? 'ceac-nav-active' : ''; ?>">
				<span class="dashicons <?php echo esc_attr( $item['icon'] ); ?>" style="font-size:16px;width:16px;height:16px"></span>
				<?php echo esc_html( $item['label'] ); ?>
			</a>
		<?php endforeach; ?>
	</nav>
</div>
