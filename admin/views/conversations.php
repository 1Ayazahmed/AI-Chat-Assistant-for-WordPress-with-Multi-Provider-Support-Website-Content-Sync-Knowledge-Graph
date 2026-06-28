<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;
$conversations = $wpdb->get_results(
	"SELECT c.*, (SELECT COUNT(*) FROM {$wpdb->prefix}ceac_messages m WHERE m.conversation_id = c.id) as msg_count
	 FROM {$wpdb->prefix}ceac_conversations c ORDER BY c.created_at DESC LIMIT 100"
);
?>
<div class="wrap ceac-admin-wrap">
	<div class="ceac-card">
		<div class="ceac-card-header">
			<h2><?php esc_html_e( 'Conversation Archive', 'ceac' ); ?></h2>
			<div class="ceac-search-box">
				<input type="text" id="ceac-conv-search" placeholder="<?php esc_attr_e( 'Search conversations...', 'ceac' ); ?>" class="regular-text" />
				<button class="button" id="ceac-export-csv"><?php esc_html_e( 'Export CSV', 'ceac' ); ?></button>
			</div>
		</div>
		<p class="description" style="margin-top:-12px;margin-bottom:16px"><?php esc_html_e( 'Audit trail for regulatory compliance. Searchable and exportable conversation logs.', 'ceac' ); ?>
		<span style="margin-left:12px;font-weight:600" id="ceac-conv-count"><?php echo count( $conversations ); ?> of <?php echo count( $conversations ); ?></span></p>

		<table class="wp-list-table widefat fixed striped" id="ceac-conv-table">
			<thead>
				<tr>
					<th style="width:50px"><?php esc_html_e( 'ID', 'ceac' ); ?></th>
					<th><?php esc_html_e( 'Session', 'ceac' ); ?></th>
					<th style="width:80px"><?php esc_html_e( 'Language', 'ceac' ); ?></th>
					<th style="width:80px"><?php esc_html_e( 'Messages', 'ceac' ); ?></th>
					<th style="width:80px"><?php esc_html_e( 'Tokens', 'ceac' ); ?></th>
					<th style="width:100px"><?php esc_html_e( 'Status', 'ceac' ); ?></th>
					<th style="width:80px"><?php esc_html_e( 'Escalated', 'ceac' ); ?></th>
					<th style="width:150px"><?php esc_html_e( 'Date', 'ceac' ); ?></th>
					<th style="width:70px"><?php esc_html_e( 'Actions', 'ceac' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $conversations ) ) : ?>
					<tr><td colspan="9">
						<div class="ceac-empty-state">
							<span class="dashicons dashicons-format-chat"></span>
							<h3><?php esc_html_e( 'No conversations yet', 'ceac' ); ?></h3>
							<p><?php esc_html_e( 'When visitors start chatting with the assistant, their conversations will appear here.', 'ceac' ); ?></p>
						</div>
					</td></tr>
				<?php else : ?>
					<?php foreach ( $conversations as $conv ) : ?>
						<tr>
							<td><strong>#<?php echo esc_html( $conv->id ); ?></strong></td>
							<td><code><?php echo esc_html( substr( $conv->session_id, 0, 8 ) ); ?>...</code></td>
							<td><span class="ceac-badge ceac-badge-<?php echo $conv->language === 'ar' ? 'warning' : 'info'; ?>"><?php echo esc_html( strtoupper( $conv->language ) ); ?></span></td>
							<td><?php echo esc_html( $conv->msg_count ); ?></td>
							<td><?php echo esc_html( $conv->token_count ? number_format( $conv->token_count ) : '—' ); ?></td>
							<td><span class="ceac-badge ceac-badge-<?php echo esc_attr( $conv->status ); ?>"><?php echo esc_html( $conv->status ); ?></span></td>
							<td><?php echo $conv->escalated ? '<span class="dashicons dashicons-yes" style="color:#16a34a"></span>' : '—'; ?></td>
							<td style="font-size:12px;color:#64748b"><?php echo esc_html( $conv->created_at ); ?></td>
							<td><button class="button button-small ceac-view-conv" data-id="<?php echo esc_attr( $conv->id ); ?>"><?php esc_html_e( 'View', 'ceac' ); ?></button></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

	<div id="ceac-conv-modal" class="ceac-modal" style="display:none">
		<div class="ceac-modal-content">
			<button class="ceac-modal-close">&times;</button>
			<h2 style="margin-top:0;padding-bottom:12px;border-bottom:1px solid var(--ceac-border)"><?php esc_html_e( 'Conversation Details', 'ceac' ); ?></h2>
			<div id="ceac-conv-messages"></div>
		</div>
	</div>
</div>
