<?php if ( ! defined( 'ABSPATH' ) ) exit; $s = CEAC_Settings::get(); ?>
<div class="wrap ceac-admin-wrap">
	<div class="ceac-card">
		<div class="ceac-tabs">
			<button class="ceac-tab active" data-tab="ceac-tab-branding"><?php esc_html_e( 'Branding', 'ceac' ); ?></button>
			<button class="ceac-tab" data-tab="ceac-tab-privacy"><?php esc_html_e( 'Privacy & Compliance', 'ceac' ); ?></button>
			<button class="ceac-tab" data-tab="ceac-tab-escalation"><?php esc_html_e( 'Escalation & CRM', 'ceac' ); ?></button>
			<button class="ceac-tab" data-tab="ceac-tab-language"><?php esc_html_e( 'Language & Region', 'ceac' ); ?></button>
			<button class="ceac-tab" data-tab="ceac-tab-advanced"><?php esc_html_e( 'Advanced', 'ceac' ); ?></button>
		</div>

		<form id="ceac-settings-form" class="ceac-form">
			<div id="ceac-tab-branding" class="ceac-tab-content active">
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'Assistant Name', 'ceac' ); ?></th>
						<td>
							<input type="text" name="bot_name" class="regular-text" value="<?php echo esc_attr( $s['bot_name'] ); ?>" />
							<p class="description"><?php esc_html_e( 'Name displayed in the chat header', 'ceac' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Chat Header Title', 'ceac' ); ?></th>
						<td>
							<input type="text" name="chat_header_title" class="regular-text" value="<?php echo esc_attr( $s['chat_header_title'] ); ?>" />
							<p class="description"><?php esc_html_e( 'Title shown in the admin area header', 'ceac' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Brand / Company Name', 'ceac' ); ?></th>
						<td>
							<input type="text" name="brand_name" class="regular-text" value="<?php echo esc_attr( $s['brand_name'] ); ?>" placeholder="e.g. Your Company" />
							<p class="description"><?php esc_html_e( 'Used as {brand_name} placeholder in the system prompt', 'ceac' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Welcome Message (English)', 'ceac' ); ?></th>
						<td><textarea name="greeting_default_en" rows="3" class="large-text"><?php echo esc_textarea( $s['greeting_default_en'] ); ?></textarea></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Welcome Message (Arabic)', 'ceac' ); ?></th>
						<td><textarea name="greeting_default_ar" rows="3" class="large-text" dir="rtl"><?php echo esc_textarea( $s['greeting_default_ar'] ); ?></textarea></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Logo URL', 'ceac' ); ?></th>
						<td>
							<input type="url" name="logo_url" class="regular-text" value="<?php echo esc_attr( $s['logo_url'] ); ?>" placeholder="https://..." />
							<p class="description"><?php esc_html_e( 'Displayed in the chat header', 'ceac' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Footer Text', 'ceac' ); ?></th>
						<td>
							<input type="text" name="footer_text" class="regular-text" value="<?php echo esc_attr( $s['footer_text'] ); ?>" placeholder="e.g. Powered by Your Brand" />
							<p class="description"><?php esc_html_e( 'Text shown at the bottom of the chat widget', 'ceac' ); ?></p>
						</td>
					</tr>
				</table>
			</div>

			<div id="ceac-tab-privacy" class="ceac-tab-content">
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'Cookie Consent', 'ceac' ); ?></th>
						<td>
							<label><input type="checkbox" name="cookie_consent_required" value="1" <?php checked( $s['cookie_consent_required'] ); ?> /> <?php esc_html_e( 'Require cookie consent before chat (GDPR/CCPA/UAE PDPL)', 'ceac' ); ?></label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Store PII', 'ceac' ); ?></th>
						<td>
							<label><input type="checkbox" name="store_pii" value="1" <?php checked( $s['store_pii'] ); ?> /> <?php esc_html_e( 'Allow storing personally identifiable information in logs', 'ceac' ); ?></label>
							<p class="description"><?php esc_html_e( 'Disable to auto-scrub PII from conversation logs', 'ceac' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Log Retention', 'ceac' ); ?></th>
						<td>
							<input type="number" name="log_retention_days" value="<?php echo esc_attr( $s['log_retention_days'] ); ?>" style="width:80px" /> <?php esc_html_e( 'days', 'ceac' ); ?>
							<p class="description"><?php esc_html_e( 'Conversation logs older than this will be automatically purged', 'ceac' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'AML Guardrails', 'ceac' ); ?></th>
						<td>
							<label><input type="checkbox" name="aml_guardrails" value="1" <?php checked( $s['aml_guardrails'] ); ?> /> <?php esc_html_e( 'Block compliance-circumvention queries (e.g. bypass KYC, hide money)', 'ceac' ); ?></label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Fallback Message', 'ceac' ); ?></th>
						<td>
							<textarea name="fallback_message" rows="4" class="large-text"><?php echo esc_textarea( $s['fallback_message'] ); ?></textarea>
							<p class="description"><?php esc_html_e( 'Message shown when the AI cannot answer a query and the topic is outside the assistant scope', 'ceac' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Low Confidence Threshold', 'ceac' ); ?></th>
						<td>
							<input type="range" name="low_confidence_threshold" min="0" max="1" step="0.05" value="<?php echo esc_attr( $s['low_confidence_threshold'] ); ?>" style="width:200px" />
							<span style="margin-left:8px;font-weight:600"><?php echo esc_html( $s['low_confidence_threshold'] ); ?></span>
							<p class="description"><?php esc_html_e( 'Lower values = more AI reliance, higher = more frequent fallback. Default: 0.4', 'ceac' ); ?></p>
						</td>
					</tr>
				</table>
			</div>

			<div id="ceac-tab-escalation" class="ceac-tab-content">
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'Escalation Email', 'ceac' ); ?></th>
						<td>
							<input type="email" name="escalation_email" class="regular-text" value="<?php echo esc_attr( $s['escalation_email'] ); ?>" placeholder="support@yourdomain.com" />
							<p class="description"><?php esc_html_e( 'Conversations escalated by users will be emailed here', 'ceac' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Escalation Webhook', 'ceac' ); ?></th>
						<td>
							<input type="url" name="escalation_webhook" class="regular-text" value="<?php echo esc_attr( $s['escalation_webhook'] ); ?>" placeholder="https://hooks.example.com/..." />
							<p class="description"><?php esc_html_e( 'POST JSON payload on escalation', 'ceac' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'CRM Integration', 'ceac' ); ?></th>
						<td>
							<select name="crm_provider" style="margin-bottom:8px">
								<option value=""><?php esc_html_e( 'None', 'ceac' ); ?></option>
								<option value="salesforce" <?php selected( $s['crm_provider'], 'salesforce' ); ?>>Salesforce</option>
								<option value="hubspot" <?php selected( $s['crm_provider'], 'hubspot' ); ?>>HubSpot</option>
								<option value="zoho" <?php selected( $s['crm_provider'], 'zoho' ); ?>>Zoho</option>
							</select>
							<br>
							<input type="url" name="crm_webhook_url" class="regular-text" value="<?php echo esc_attr( $s['crm_webhook_url'] ); ?>" placeholder="CRM webhook URL" />
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Live Rates API', 'ceac' ); ?></th>
						<td>
							<input type="url" name="rate_api_url" class="regular-text" value="<?php echo esc_attr( $s['rate_api_url'] ); ?>" placeholder="https://api.example.com/rates" style="margin-bottom:8px" /><br>
							<input type="password" name="rate_api_key" class="regular-text" value="" placeholder="Rate API Key" />
						</td>
					</tr>
				</table>
			</div>

			<div id="ceac-tab-language" class="ceac-tab-content">
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'Auto-detect Language', 'ceac' ); ?></th>
						<td>
							<label><input type="checkbox" name="auto_language_detect" value="1" <?php checked( $s['auto_language_detect'] ); ?> /> <?php esc_html_e( 'Detect user language from browser preference', 'ceac' ); ?></label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Default Language', 'ceac' ); ?></th>
						<td>
							<select name="default_language">
								<option value="en" <?php selected( $s['default_language'], 'en' ); ?>>English</option>
								<option value="ar" <?php selected( $s['default_language'], 'ar' ); ?><?php esc_html_e( 'Arabic', 'ceac' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'RTL Support', 'ceac' ); ?></th>
						<td>
							<label><input type="checkbox" name="rtl_support" value="1" <?php checked( $s['rtl_support'] ); ?> /> <?php esc_html_e( 'Enable right-to-left layout for Arabic', 'ceac' ); ?></label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Branding Colors', 'ceac' ); ?></th>
						<td>
							<div style="display:flex;gap:16px;flex-wrap:wrap">
								<label style="display:flex;flex-direction:column;align-items:center;gap:4px">
									<?php esc_html_e( 'Primary', 'ceac' ); ?>
									<input type="color" name="primary_color" value="<?php echo esc_attr( $s['primary_color'] ); ?>" style="width:40px;height:40px" />
								</label>
								<label style="display:flex;flex-direction:column;align-items:center;gap:4px">
									<?php esc_html_e( 'Secondary', 'ceac' ); ?>
									<input type="color" name="secondary_color" value="<?php echo esc_attr( $s['secondary_color'] ); ?>" style="width:40px;height:40px" />
								</label>
								<label style="display:flex;flex-direction:column;align-items:center;gap:4px">
									<?php esc_html_e( 'Accent', 'ceac' ); ?>
									<input type="color" name="accent_color" value="<?php echo esc_attr( $s['accent_color'] ); ?>" style="width:40px;height:40px" />
								</label>
							</div>
						</td>
					</tr>
				</table>
			</div>

			<div id="ceac-tab-advanced" class="ceac-tab-content">
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'Rate Limiting', 'ceac' ); ?></th>
						<td>
							<label style="display:block;margin-bottom:8px">
								<?php esc_html_e( 'Daily Token Cap:', 'ceac' ); ?>
								<input type="number" name="daily_token_cap" value="<?php echo esc_attr( $s['daily_token_cap'] ); ?>" style="width:120px" />
							</label>
							<label>
								<?php esc_html_e( 'Per-Chat Token Budget:', 'ceac' ); ?>
								<input type="number" name="per_chat_token_budget" value="<?php echo esc_attr( $s['per_chat_token_budget'] ); ?>" style="width:120px" />
							</label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Cookie Consent Text', 'ceac' ); ?></th>
						<td>
							<textarea name="cookie_consent_text" rows="4" class="large-text"><?php echo esc_textarea( $s['cookie_consent_text'] ?? '' ); ?></textarea>
						</td>
					</tr>
				</table>
			</div>

			<p class="submit"><button type="submit" class="button button-primary button-large"><?php esc_html_e( 'Save Settings', 'ceac' ); ?></button></p>
		</form>
	</div>
</div>
