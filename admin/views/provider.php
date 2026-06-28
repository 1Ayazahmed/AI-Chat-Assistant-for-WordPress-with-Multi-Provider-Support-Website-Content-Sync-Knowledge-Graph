<?php if ( ! defined( 'ABSPATH' ) ) exit;
$settings = CEAC_Settings::get();
$providers = CEAC_Settings::get_providers();
$api_key_display = '';
if ( ! empty( $settings['api_key'] ) ) {
	$key = CEAC_Settings::get_api_key();
	$api_key_display = ! empty( $key ) ? '••••••••' . substr( $key, -4 ) : '';
}
?>
<div class="wrap ceac-admin-wrap">
	<form id="ceac-provider-form" class="ceac-form">
		<div class="ceac-card">
			<div class="ceac-card-header">
				<h2><?php esc_html_e( 'Primary AI Provider', 'ceac' ); ?></h2>
				<span class="ceac-status-indicator">
					<span class="ceac-status-dot <?php echo $api_key_display ? 'online' : 'offline'; ?>"></span>
					<?php echo $api_key_display ? esc_html__( 'Configured', 'ceac' ) : esc_html__( 'Not configured', 'ceac' ); ?>
				</span>
			</div>

			<table class="form-table">
				<tr>
					<th><label for="ceac-provider"><?php esc_html_e( 'Provider', 'ceac' ); ?></label></th>
					<td>
						<select id="ceac-provider" name="provider" style="min-width:240px">
							<?php foreach ( $providers as $id => $provider ) : ?>
								<option value="<?php echo esc_attr( $id ); ?>" data-url="<?php echo esc_attr( $provider['url'] ); ?>" <?php selected( $settings['provider'], $id ); ?>>
									<?php echo esc_html( $provider['name'] ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="ceac-api-base-url"><?php esc_html_e( 'API Base URL', 'ceac' ); ?></label></th>
					<td>
						<input type="url" id="ceac-api-base-url" name="api_base_url" class="regular-text" value="<?php echo esc_attr( $settings['api_base_url'] ); ?>" placeholder="https://api.openai.com/v1" />
						<p class="description"><?php esc_html_e( 'OpenAI-compatible endpoint. Supports OpenAI, Anthropic, Groq, Together, Ollama, LM Studio & custom providers.', 'ceac' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="ceac-api-key"><?php esc_html_e( 'API Key', 'ceac' ); ?></label></th>
					<td>
						<div class="ceac-key-field">
							<input type="password" id="ceac-api-key" name="api_key" class="regular-text" value="<?php echo esc_attr( $api_key_display ); ?>" placeholder="sk-..." autocomplete="new-password" />
							<button type="button" class="button button-secondary ceac-toggle-key" data-target="ceac-api-key"><?php esc_html_e( 'Reveal', 'ceac' ); ?></button>
						</div>
						<p class="description"><?php esc_html_e( 'Your API key is stored encrypted in the database', 'ceac' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="ceac-model"><?php esc_html_e( 'Model', 'ceac' ); ?></label></th>
					<td>
						<div class="ceac-model-row">
							<select id="ceac-model" name="model" class="regular-text" style="min-width:280px">
								<option value="<?php echo esc_attr( $settings['model'] ); ?>" selected><?php echo esc_html( $settings['model'] ); ?></option>
							</select>
							<button type="button" class="button button-secondary" id="ceac-fetch-models"><?php esc_html_e( 'Fetch Models', 'ceac' ); ?></button>
						</div>
						<input type="text" id="ceac-model-manual" class="regular-text" placeholder="<?php esc_attr_e( 'Or type model name manually', 'ceac' ); ?>" style="margin-top:8px;max-width:280px" />
						<p class="description" id="ceac-models-status"></p>
					</td>
				</tr>
				<tr>
					<th><label for="ceac-temperature"><?php esc_html_e( 'Temperature', 'ceac' ); ?></label></th>
					<td>
						<div style="display:flex;align-items:center;gap:12px">
							<input type="range" id="ceac-temperature" name="temperature" min="0" max="1" step="0.1" value="<?php echo esc_attr( $settings['temperature'] ); ?>" style="width:200px" />
							<span id="ceac-temperature-val" style="font-weight:700;font-size:16px;min-width:30px"><?php echo esc_html( $settings['temperature'] ); ?></span>
							<span style="font-size:11px;color:var(--ceac-text-muted)"><?php esc_html_e( 'Lower = focused, Higher = creative', 'ceac' ); ?></span>
						</div>
					</td>
				</tr>
				<tr>
					<th><label for="ceac-max-tokens"><?php esc_html_e( 'Max Tokens', 'ceac' ); ?></label></th>
					<td>
						<input type="number" id="ceac-max-tokens" name="max_tokens" min="100" max="32000" value="<?php echo esc_attr( $settings['max_tokens'] ); ?>" style="width:120px" />
						<p class="description"><?php esc_html_e( 'Maximum response length. Higher = more detailed but more costly.', 'ceac' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="ceac-system-prompt"><?php esc_html_e( 'System Prompt', 'ceac' ); ?></label></th>
					<td>
						<textarea id="ceac-system-prompt" name="system_prompt" rows="12" class="large-text code"><?php echo esc_textarea( $settings['system_prompt'] ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Defines the AI assistant behavior, identity, and constraints.', 'ceac' ); ?></p>
					</td>
				</tr>
			</table>

			<div style="display:flex;gap:12px;align-items:center;padding-top:16px;border-top:1px solid var(--ceac-border)">
				<button type="button" class="button button-secondary" id="ceac-test-connection"><?php esc_html_e( 'Test Connection', 'ceac' ); ?></button>
				<span id="ceac-connection-status"></span>
			</div>
		</div>

		<div class="ceac-card">
			<div class="ceac-card-header">
				<h2><?php esc_html_e( 'Fallback Provider', 'ceac' ); ?></h2>
				<span class="ceac-badge ceac-badge-warning"><?php esc_html_e( 'Optional', 'ceac' ); ?></span>
			</div>
			<p class="description" style="margin-top:-8px;margin-bottom:16px"><?php esc_html_e( 'If the primary provider fails, the fallback is used as a backup. Leave empty to disable.', 'ceac' ); ?></p>
			<table class="form-table">
				<tr>
					<th><label for="ceac-fallback-api-base-url"><?php esc_html_e( 'API Base URL', 'ceac' ); ?></label></th>
					<td><input type="url" id="ceac-fallback-api-base-url" name="fallback_api_base_url" class="regular-text" value="<?php echo esc_attr( $settings['fallback_api_base_url'] ); ?>" placeholder="https://api.openai.com/v1" /></td>
				</tr>
				<tr>
					<th><label for="ceac-fallback-api-key"><?php esc_html_e( 'API Key', 'ceac' ); ?></label></th>
					<td><input type="password" id="ceac-fallback-api-key" name="fallback_api_key" class="regular-text" value="••••••••" autocomplete="new-password" /></td>
				</tr>
				<tr>
					<th><label for="ceac-fallback-model"><?php esc_html_e( 'Model', 'ceac' ); ?></label></th>
					<td><input type="text" id="ceac-fallback-model" name="fallback_model" class="regular-text" value="<?php echo esc_attr( $settings['fallback_model'] ); ?>" placeholder="gpt-3.5-turbo" /></td>
				</tr>
			</table>
		</div>

		<div class="ceac-card">
			<div class="ceac-card-header">
				<h2><?php esc_html_e( 'Rate Limits & Cost Controls', 'ceac' ); ?></h2>
			</div>
			<table class="form-table">
				<tr>
					<th><label for="ceac-daily-token-cap"><?php esc_html_e( 'Daily Token Cap', 'ceac' ); ?></label></th>
					<td><input type="number" id="ceac-daily-token-cap" name="daily_token_cap" value="<?php echo esc_attr( $settings['daily_token_cap'] ); ?>" style="width:120px" /> <span class="description"><?php esc_html_e( 'Stop AI responses after this many tokens per day', 'ceac' ); ?></span></td>
				</tr>
				<tr>
					<th><label for="ceac-per-chat-budget"><?php esc_html_e( 'Per-Chat Budget', 'ceac' ); ?></label></th>
					<td><input type="number" id="ceac-per-chat-budget" name="per_chat_token_budget" value="<?php echo esc_attr( $settings['per_chat_token_budget'] ); ?>" style="width:120px" /> <span class="description"><?php esc_html_e( 'Max tokens per conversation (including history)', 'ceac' ); ?></span></td>
				</tr>
			</table>
		</div>

		<p class="submit">
			<button type="submit" class="button button-primary button-large"><?php esc_html_e( 'Save Provider Settings', 'ceac' ); ?></button>
		</p>
	</form>
</div>
