<div id="ceac-widget-root" class="ceac-position-<?php echo esc_attr( CEAC_Settings::get( 'widget_position', 'bottom-right' ) ); ?>" dir="<?php echo CEAC_I18n::is_rtl() ? 'rtl' : 'ltr'; ?>">
	<button id="ceac-launcher" class="ceac-launcher" aria-label="<?php esc_attr_e( 'Open chat', 'ceac' ); ?>">
		<svg class="ceac-launcher-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
			<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
		</svg>
		<span class="ceac-launcher-close" style="display:none;">&times;</span>
	</button>

	<div id="ceac-panel" class="ceac-panel" style="display:none;">
		<div id="ceac-consent" class="ceac-consent" style="display:none;">
			<h3 class="ceac-consent-title"></h3>
			<p class="ceac-consent-text"></p>
			<div class="ceac-consent-actions">
				<button id="ceac-consent-accept" class="ceac-btn ceac-btn-primary"></button>
				<button id="ceac-consent-decline" class="ceac-btn ceac-btn-secondary"></button>
			</div>
		</div>

		<div id="ceac-chat" class="ceac-chat" style="display:none;">
			<div class="ceac-header">
				<div class="ceac-header-info">
					<img class="ceac-avatar" src="" alt="" />
					<div>
						<span class="ceac-bot-name"></span>
						<span class="ceac-status">
							<span class="ceac-status-dot"></span>
							<span class="ceac-status-text"></span>
						</span>
					</div>
				</div>
				<button id="ceac-minimize" class="ceac-minimize" aria-label="Minimize">&minus;</button>
			</div>

			<div id="ceac-messages" class="ceac-messages"></div>

			<div id="ceac-typing" class="ceac-typing" style="display:none;">
				<span class="ceac-typing-dot"></span>
				<span class="ceac-typing-dot"></span>
				<span class="ceac-typing-dot"></span>
			</div>

			<div id="ceac-fallback-options" class="ceac-fallback-options" style="display:none;"></div>

			<div id="ceac-offline-form" class="ceac-offline-form" style="display:none;">
				<p class="ceac-offline-msg"></p>
				<input type="email" id="ceac-offline-email" class="ceac-input" placeholder="" />
				<textarea id="ceac-offline-message" class="ceac-input" rows="3" placeholder=""></textarea>
				<button id="ceac-offline-submit" class="ceac-btn ceac-btn-primary"></button>
			</div>

			<div class="ceac-input-area">
				<textarea id="ceac-input" class="ceac-input" rows="1" placeholder=""></textarea>
				<button id="ceac-send" class="ceac-send" aria-label="Send">
					<svg viewBox="0 0 24 24" fill="currentColor"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
				</button>
			</div>

			<div class="ceac-footer">
				<span class="ceac-powered"></span>
			</div>
		</div>
	</div>
</div>
