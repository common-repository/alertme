<?php
/*
*
* Add shortCode in plugin
* User this [alertme-subscription-list] to render subscriptions list for users
* It will show list of susbcribed pages/post for logged-in user
*
*/

function alert_me_subscriptions_list() {

	global $wpdb, $alertme_table;
	$table_name = $wpdb->prefix . $alertme_table;
	$current_user = wp_get_current_user();

	if ( isset($_POST['submit-check-if-subscribed-as-visitor']) ) {
		check_admin_referer( 'alert-me-check-if-subscribed-as-visitor' );
		$wpdb->query("UPDATE $table_name SET user_id = ". $current_user->ID . " WHERE email = '".$current_user->user_email."'");
	}

	if ( isset($_POST['submit']) ) {
		check_admin_referer( 'alert-me-unsubscribed-list' );

		if (!empty($_POST['unsubscribed_items'])){

			foreach ($_POST['unsubscribed_items'] as $key => $unsubscribed_item) {
				
				$wpdb->delete(
					"{$wpdb->prefix}{$alertme_table}",
					[ 'id' => absint($unsubscribed_item) ],
					[ '%d' ]
				);
			}
		}
	}
	
	$sql = "SELECT id, post_id FROM $table_name where id != '' AND email_confirm = 1 AND user_id = ". $current_user->ID;
	$results = $wpdb->get_results( $sql, 'ARRAY_A' );

	$checkIfSubscribedAsVistorQuery = "SELECT count(*) FROM $table_name where id != '' AND email = '".$current_user->user_email."' AND user_id = 0";
	$checkIfSubscribedAsVistorResult = $wpdb->get_var($checkIfSubscribedAsVistorQuery);

	ob_start();
	if ( !is_admin() && is_user_logged_in() ) { ?>

		<div class="alert_me_subscriptions_list">
			<h2><?php echo esc_html__('Manage Your Alert Subscriptions', ALERTME_TXT_DOMAIN); ?></h2>

			<?php if ($checkIfSubscribedAsVistorResult > 0): ?>

				<form action="" method="post">
					<?php wp_nonce_field('alert-me-check-if-subscribed-as-visitor'); ?>

					<p><?php echo esc_html__('Our system has detected that you have previously set up alerts using just your email address. Please click on the Sync button below to update your profile.', ALERTME_TXT_DOMAIN); ?></p>

					<div class="submit_button_container" style="text-align: center;">
						<input type="submit" name="submit-check-if-subscribed-as-visitor" value="<?php echo esc_html__('Sync available', ALERTME_TXT_DOMAIN); ?>" class="submit_button">
					</div>

				</form>
				<p></p>

			<?php endif; ?>

			<form action="" method="post" name="alert_me_subscriptions_list_form">

				<?php wp_nonce_field('alert-me-unsubscribed-list'); ?>

				<table class="table table-responsive">
					<thead class="thead-dark">
						<tr>
							<th scope="col">Page</th>
							<th scope="col"></th>
						</tr>
					</thead>
					<tbody>

						<?php if (!empty($results)): ?>

							<?php foreach ($results as $key => $result) { ?>

								<tr>
									<td><?php echo get_the_title($result['post_id']); ?></td>
									<td>
										<div class="form-check">
											<input type="checkbox" name="unsubscribed_items[]" class="form-check-input" id="exampleCheck<?php echo $result['id']; ?>" value="<?php echo $result['id']; ?>">
											<label class="form-check-label" for="exampleCheck<?php echo $result['id']; ?>">Unsubscribe</label>
										</div>
									</td>
								</tr>

							<?php } ?>

						<?php else: ?>

							<tr colspan="2">
								<td><?php echo esc_html__('Looks like you do not have any subscriptions yet.', ALERTME_TXT_DOMAIN); ?></td>
							</tr>

						<?php endif; ?>

					</tbody>
				</table>				

				<?php if (!empty($results)): ?>

					<div class="submit_button_container">
						<input type="submit" name="submit" value="<?php echo esc_html__('Update Subscriptions', ALERTME_TXT_DOMAIN); ?>" class="submit_button">
					</div>

				<?php endif; ?>

			</form>
		</div>

	<?php
	}

	return ob_get_clean();
}
add_shortcode( 'alertme-subscriptions-list', 'alert_me_subscriptions_list' );