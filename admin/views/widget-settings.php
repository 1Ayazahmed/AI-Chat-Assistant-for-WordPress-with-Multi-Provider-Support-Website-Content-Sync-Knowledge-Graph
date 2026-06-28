<?php if ( ! defined( 'ABSPATH' ) ) exit; $s = CEAC_Settings::get(); ?>
<div class="wrap ceac-admin-wrap">
	<div class="ceac-card">
		<div class="ceac-tabs">
			<button class="ceac-tab active" data-tab="ceac-tab-behavior"><?php esc_html_e( 'Position & Behavior', 'ceac' ); ?></button>
			<button class="ceac-tab" data-tab="ceac-tab-appearance"><?php esc_html_e( 'Appearance', 'ceac' ); ?></button>
			<button class="ceac-tab" data-tab="ceac-tab-greetings"><?php esc_html_e( 'Greetings', 'ceac' ); ?></button>
			<button class="ceac-tab" data-tab="ceac-tab-business"><?php esc_html_e( 'Business Hours', 'ceac' ); ?></button>
		</div>

		<form id="ceac-widget-form" class="ceac-form">
			<div id="ceac-tab-behavior" class="ceac-tab-content active">
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'Position', 'ceac' ); ?></th>
						<td>
							<select name="widget_position">
								<option value="bottom-right" <?php selected( $s['widget_position'], 'bottom-right' ); ?>><?php esc_html_e( 'Bottom Right', 'ceac' ); ?></option>
								<option value="bottom-left" <?php selected( $s['widget_position'], 'bottom-left' ); ?>><?php esc_html_e( 'Bottom Left', 'ceac' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Offset X / Y', 'ceac' ); ?></th>
						<td>
							<div style="display:flex;gap:12px;align-items:center">
								<label>X: <input type="number" name="widget_offset_x" value="<?php echo esc_attr( $s['widget_offset_x'] ); ?>" style="width:80px" /> px</label>
								<label>Y: <input type="number" name="widget_offset_y" value="<?php echo esc_attr( $s['widget_offset_y'] ); ?>" style="width:80px" /> px</label>
							</div>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Size (W x H)', 'ceac' ); ?></th>
						<td>
							<div style="display:flex;gap:12px;align-items:center">
								<label><?php esc_html_e( 'Width', 'ceac' ); ?> <input type="number" name="widget_width" value="<?php echo esc_attr( $s['widget_width'] ); ?>" style="width:80px" /> px</label>
								<label><?php esc_html_e( 'Height', 'ceac' ); ?> <input type="number" name="widget_height" value="<?php echo esc_attr( $s['widget_height'] ); ?>" style="width:80px" /> px</label>
							</div>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Font Size', 'ceac' ); ?></th>
						<td><input type="number" name="font_size" value="<?php echo esc_attr( $s['font_size'] ); ?>" style="width:80px" /> px</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Auto-open Delay', 'ceac' ); ?></th>
						<td><input type="number" name="auto_open_delay" value="<?php echo esc_attr( $s['auto_open_delay'] ); ?>" min="0" /> <?php esc_html_e( 'seconds', 'ceac' ); ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Scroll Depth Trigger', 'ceac' ); ?></th>
						<td><input type="number" name="scroll_depth_trigger" value="<?php echo esc_attr( $s['scroll_depth_trigger'] ); ?>" min="0" max="100" /> %</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Triggers', 'ceac' ); ?></th>
						<td>
							<label style="display:block;margin-bottom:6px"><input type="checkbox" name="exit_intent" value="1" <?php checked( $s['exit_intent'] ); ?> /> <?php esc_html_e( 'Show on exit intent (user moves mouse to close tab)', 'ceac' ); ?></label>
							<label><input type="checkbox" name="click_to_open_only" value="1" <?php checked( $s['click_to_open_only'] ); ?> /> <?php esc_html_e( 'Only open when user clicks the launcher (disable auto-triggers)', 'ceac' ); ?></label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Entrance Animation', 'ceac' ); ?></th>
						<td>
							<select name="entrance_animation">
								<option value="slide-up" <?php selected( $s['entrance_animation'], 'slide-up' ); ?>><?php esc_html_e( 'Slide Up', 'ceac' ); ?></option>
								<option value="fade" <?php selected( $s['entrance_animation'], 'fade' ); ?>><?php esc_html_e( 'Fade In', 'ceac' ); ?></option>
								<option value="scale" <?php selected( $s['entrance_animation'], 'scale' ); ?>><?php esc_html_e( 'Scale', 'ceac' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Animation when widget panel opens', 'ceac' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Sound & Haptics', 'ceac' ); ?></th>
						<td>
							<label style="display:block;margin-bottom:6px"><input type="checkbox" name="sound_enabled" value="1" <?php checked( $s['sound_enabled'] ); ?> /> <?php esc_html_e( 'Enable notification sound on new messages', 'ceac' ); ?></label>
							<label><input type="checkbox" name="haptics_enabled" value="1" <?php checked( $s['haptics_enabled'] ); ?> /> <?php esc_html_e( 'Enable haptic feedback on send (mobile)', 'ceac' ); ?></label>
						</td>
					</tr>
				</table>
			</div>

			<div id="ceac-tab-appearance" class="ceac-tab-content">
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'Bot Name', 'ceac' ); ?></th>
						<td><input type="text" name="bot_name" class="regular-text" value="<?php echo esc_attr( $s['bot_name'] ); ?>" /></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Avatar', 'ceac' ); ?></th>
						<td>
							<select name="avatar_library" style="margin-bottom:8px">
								<option value="financial-1" <?php selected( $s['avatar_library'], 'financial-1' ); ?>><?php esc_html_e( 'Financial Advisor 1', 'ceac' ); ?></option>
								<option value="financial-2" <?php selected( $s['avatar_library'], 'financial-2' ); ?>><?php esc_html_e( 'Financial Advisor 2', 'ceac' ); ?></option>
								<option value="financial-3" <?php selected( $s['avatar_library'], 'financial-3' ); ?>><?php esc_html_e( 'Financial Advisor 3', 'ceac' ); ?></option>
							</select>
							<p class="description"><?php esc_html_e( 'Or use a custom avatar URL:', 'ceac' ); ?></p>
							<input type="url" name="avatar_url" class="regular-text" value="<?php echo esc_attr( $s['avatar_url'] ); ?>" placeholder="https://..." />
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Bubble Style', 'ceac' ); ?></th>
						<td>
							<select name="bubble_style">
								<option value="rounded" <?php selected( $s['bubble_style'], 'rounded' ); ?>><?php esc_html_e( 'Rounded (modern)', 'ceac' ); ?></option>
								<option value="square" <?php selected( $s['bubble_style'], 'square' ); ?>><?php esc_html_e( 'Square (classic)', 'ceac' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Colors', 'ceac' ); ?></th>
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
								<label style="display:flex;flex-direction:column;align-items:center;gap:4px">
									<?php esc_html_e( 'Header', 'ceac' ); ?>
									<input type="color" name="header_color" value="<?php echo esc_attr( $s['header_color'] ?: $s['primary_color'] ); ?>" style="width:40px;height:40px" />
								</label>
							</div>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Logo URL', 'ceac' ); ?></th>
						<td><input type="url" name="logo_url" class="regular-text" value="<?php echo esc_attr( $s['logo_url'] ); ?>" placeholder="https://..." /></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Widget Transparency', 'ceac' ); ?></th>
						<td>
							<input type="range" name="widget_transparency" min="0.5" max="1" step="0.05" value="<?php echo esc_attr( $s['widget_transparency'] ); ?>" style="width:200px" />
							<span style="margin-left:8px;font-weight:600"><?php echo esc_html( $s['widget_transparency'] ); ?></span>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Theme Sync', 'ceac' ); ?></th>
						<td>
							<label style="display:block;margin-bottom:6px"><input type="checkbox" name="theme_sync_enabled" value="1" <?php checked( $s['theme_sync_enabled'] ); ?> /> <?php esc_html_e( 'Auto-detect WordPress theme colors & fonts', 'ceac' ); ?></label>
							<label><input type="checkbox" name="dark_mode_detection" value="1" <?php checked( $s['dark_mode_detection'] ); ?> /> <?php esc_html_e( 'Enable dark mode detection (matches OS/browser preference)', 'ceac' ); ?></label>
						</td>
					</tr>
				</table>
			</div>

			<div id="ceac-tab-greetings" class="ceac-tab-content">
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'Default Greeting (English)', 'ceac' ); ?></th>
						<td><textarea name="greeting_default_en" rows="4" class="large-text"><?php echo esc_textarea( $s['greeting_default_en'] ); ?></textarea></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Default Greeting (Arabic)', 'ceac' ); ?></th>
						<td><textarea name="greeting_default_ar" rows="4" class="large-text" dir="rtl"><?php echo esc_textarea( $s['greeting_default_ar'] ); ?></textarea></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Online Status', 'ceac' ); ?></th>
						<td>
							<label><input type="checkbox" name="status_indicator" value="1" <?php checked( $s['status_indicator'] ); ?> /> <?php esc_html_e( 'Show online/offline status indicator', 'ceac' ); ?></label>
						</td>
					</tr>
				</table>
			</div>

			<div id="ceac-tab-business" class="ceac-tab-content">
				<table class="form-table">
					<tr>
						<th><?php esc_html_e( 'Enable Business Hours', 'ceac' ); ?></th>
						<td>
							<label><input type="checkbox" name="business_hours_enabled" value="1" <?php checked( $s['business_hours_enabled'] ); ?> /> <?php esc_html_e( 'Show offline form outside business hours', 'ceac' ); ?></label>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Timezone', 'ceac' ); ?></th>
						<td>
							<select name="timezone">
								<?php
								$zones = array( 'Asia/Dubai', 'Asia/Riyadh', 'Asia/Karachi', 'Asia/Kolkata', 'Asia/Dhaka', 'Asia/Singapore', 'Europe/London', 'America/New_York', 'UTC' );
								foreach ( $zones as $zone ) : ?>
									<option value="<?php echo esc_attr( $zone ); ?>" <?php selected( $s['timezone'], $zone ); ?>><?php echo esc_html( $zone ); ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Business Hours', 'ceac' ); ?></th>
						<td>
							<table style="border-collapse:collapse">
								<?php
								$days = array( 'mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday', 'thu' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday' );
								foreach ( $days as $key => $label ) :
									$day_hours = isset( $s['business_hours'][ $key ] ) ? $s['business_hours'][ $key ] : array( '09:00', '18:00' );
									$is_closed = $day_hours === array( 'closed' ) || $day_hours[0] === 'closed';
								?>
								<tr style="border-bottom:1px solid #f1f5f9">
									<td style="padding:8px 12px 8px 0;font-weight:600;width:100px"><?php echo esc_html( $label ); ?></td>
									<td style="padding:8px 8px">
										<label style="display:flex;align-items:center;gap:8px">
											<input type="checkbox" class="ceac-day-toggle" data-day="<?php echo esc_attr( $key ); ?>" <?php checked( ! $is_closed ); ?> />
											<span style="font-size:12px;color:var(--ceac-text-muted)"><?php esc_html_e( 'Open', 'ceac' ); ?></span>
										</label>
									</td>
									<td style="padding:8px 0" class="ceac-day-hours" data-day="<?php echo esc_attr( $key ); ?>" style="<?php echo $is_closed ? 'display:none' : ''; ?>">
										<input type="time" name="business_hours[<?php echo esc_attr( $key ); ?>][]" value="<?php echo $is_closed ? '09:00' : esc_attr( $day_hours[0] ); ?>" style="width:110px" />
										<span style="margin:0 6px">to</span>
										<input type="time" name="business_hours[<?php echo esc_attr( $key ); ?>][]" value="<?php echo $is_closed ? '18:00' : esc_attr( $day_hours[1] ); ?>" style="width:110px" />
									</td>
								</tr>
								<?php endforeach; ?>
							</table>
							<script>
							jQuery(function($) {
								$('.ceac-day-toggle').on('change', function() {
									const day = $(this).data('day');
									const $hours = $('.ceac-day-hours[data-day="' + day + '"]');
									$hours.toggle($(this).is(':checked'));
								});
							});
							</script>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Offline Message', 'ceac' ); ?></th>
						<td><textarea name="offline_message" rows="3" class="large-text"><?php echo esc_textarea( $s['offline_message'] ); ?></textarea></td>
					</tr>
				</table>
			</div>

			<p class="submit"><button type="submit" class="button button-primary button-large"><?php esc_html_e( 'Save Widget Settings', 'ceac' ); ?></button></p>
		</form>
	</div>
</div>
