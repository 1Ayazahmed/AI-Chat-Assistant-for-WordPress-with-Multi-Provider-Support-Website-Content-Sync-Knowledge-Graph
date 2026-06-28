<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap ceac-admin-wrap">
	<div class="ceac-card">
		<div class="ceac-card-header">
			<h2><?php esc_html_e( 'Analytics Overview', 'ceac' ); ?></h2>
			<div class="ceac-filter-bar" style="margin:0;padding:0;border:none;background:transparent">
				<label style="display:flex;align-items:center;gap:6px">
					<?php esc_html_e( 'Period:', 'ceac' ); ?>
					<select id="ceac-analytics-days">
						<option value="7">7 days</option>
						<option value="30" selected>30 days</option>
						<option value="90">90 days</option>
						<option value="365">1 year</option>
					</select>
				</label>
				<button class="button button-secondary" id="ceac-export-csv"><?php esc_html_e( 'Export CSV', 'ceac' ); ?></button>
			</div>
		</div>
		<div class="ceac-dashboard-grid" id="ceac-analytics-stats"></div>
	</div>

	<div class="ceac-analytics-row">
		<div class="ceac-card ceac-half">
			<div class="ceac-card-header">
				<h2><?php esc_html_e( 'Top User Intents', 'ceac' ); ?></h2>
			</div>
			<div id="ceac-intents-chart">
				<div class="ceac-skeleton" style="height:30px;width:80%"></div>
				<div class="ceac-skeleton" style="height:30px;width:60%"></div>
				<div class="ceac-skeleton" style="height:30px;width:70%"></div>
			</div>
		</div>
		<div class="ceac-card ceac-half">
			<div class="ceac-card-header">
				<h2><?php esc_html_e( 'Peak Hours', 'ceac' ); ?></h2>
			</div>
			<div id="ceac-peak-hours-chart">
				<div class="ceac-skeleton" style="height:30px;width:90%"></div>
				<div class="ceac-skeleton" style="height:30px;width:75%"></div>
				<div class="ceac-skeleton" style="height:30px;width:60%"></div>
			</div>
		</div>
	</div>

	<div class="ceac-card">
		<div class="ceac-card-header">
			<h2><?php esc_html_e( 'Top Queries', 'ceac' ); ?></h2>
		</div>
		<table class="wp-list-table widefat fixed striped" id="ceac-top-queries">
			<thead><tr><th><?php esc_html_e( 'Query', 'ceac' ); ?></th><th style="width:80px"><?php esc_html_e( 'Count', 'ceac' ); ?></th></tr></thead>
			<tbody></tbody>
		</table>
	</div>

	<div class="ceac-card">
		<div class="ceac-card-header">
			<h2><?php esc_html_e( 'Token Usage & Cost', 'ceac' ); ?></h2>
		</div>
		<div id="ceac-cost-info">
			<div class="ceac-skeleton" style="height:60px"></div>
		</div>
	</div>
</div>
