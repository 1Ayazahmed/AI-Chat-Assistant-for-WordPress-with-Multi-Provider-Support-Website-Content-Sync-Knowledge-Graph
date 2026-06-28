<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap ceac-admin-wrap">
	<h1><?php esc_html_e( 'Knowledge Graph', 'ceac' ); ?></h1>
	<p class="description"><?php esc_html_e( 'Obsidian-style visualization of your website knowledge, user intents, questions, and fallback queries.', 'ceac' ); ?></p>

	<div class="ceac-graph-controls">
		<button class="button button-primary" id="ceac-refresh-graph"><?php esc_html_e( 'Refresh Graph', 'ceac' ); ?></button>
		<button class="button" id="ceac-sync-and-refresh"><?php esc_html_e( 'Sync Content & Refresh', 'ceac' ); ?></button>
		<div class="ceac-graph-legend">
			<span class="ceac-legend-item"><span class="ceac-dot" style="background:#1a365d"></span> Company</span>
			<span class="ceac-legend-item"><span class="ceac-dot" style="background:#2b6cb0"></span> Services</span>
			<span class="ceac-legend-item"><span class="ceac-dot" style="background:#c53030"></span> Compliance</span>
			<span class="ceac-legend-item"><span class="ceac-dot" style="background:#38a169"></span> Contact</span>
			<span class="ceac-legend-item"><span class="ceac-dot" style="background:#d69e2e"></span> Rates</span>
			<span class="ceac-legend-item"><span class="ceac-dot" style="background:#e53e3e"></span> Fallback Queries</span>
			<span class="ceac-legend-item"><span class="ceac-dot" style="background:#319795"></span> User Intents</span>
		</div>
	</div>

	<div class="ceac-graph-stats" id="ceac-graph-stats"></div>

	<div id="ceac-knowledge-graph" class="ceac-knowledge-graph"></div>

	<div class="ceac-card" style="margin-top:20px">
		<h2><?php esc_html_e( 'Graph Legend', 'ceac' ); ?></h2>
		<ul>
			<li><strong><?php esc_html_e( 'Solid lines', 'ceac' ); ?>:</strong> <?php esc_html_e( 'Related knowledge items (same category or keyword overlap)', 'ceac' ); ?></li>
			<li><strong><?php esc_html_e( 'Dashed lines', 'ceac' ); ?>:</strong> <?php esc_html_e( 'Fallback queries linked to nearest knowledge match', 'ceac' ); ?></li>
			<li><strong><?php esc_html_e( 'Node size', 'ceac' ); ?>:</strong> <?php esc_html_e( 'Larger nodes = more connections or higher query volume', 'ceac' ); ?></li>
			<li><strong><?php esc_html_e( 'Red nodes', 'ceac' ); ?>:</strong> <?php esc_html_e( 'Unanswered/fallback queries — knowledge gaps to address', 'ceac' ); ?></li>
		</ul>
	</div>
</div>
