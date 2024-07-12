<?php
namespace digitalcube\SimpleGA4Ranking\Admin\Cache;

class View {

	public static function option_page() {

		if ( 'DELETE' === filter_input( INPUT_POST, 'delete_cache' ) ) {
			Admin::delete_cache();
		}

		$cache = get_transient( 'sga_ranking_result_keys', [] );
		$cache_results = isset( $cache['results'] ) ? $cache['results'] : [];

	?>
	<style>
		.table_cache_list {
			width: 100%;
			border-collapse: collapse;
			margin-top: 20px;
		}

		.table_cache_list th {
			border: 1px solid #ccc; 
			padding: 8px;
			text-align: center;
		}

		.table_cache_list td {
			border: 1px solid #ccc; 
			padding: 8px;
			text-align: left;
		}

		.table_cache_list td.key {
			text-align: center;
		}

		.table_cache_list th {
			background-color: #f5f5f5;
			color: #333;
		}

		.table_cache_list td {
			background-color: #fff;
		}

		.table_cache_list td a {
			color: #0066cc;
			text-decoration: none;
		}

		.table_cache_list td a:hover {
			text-decoration: underline;
		}

		.table_cache_list td input[type="submit"] {
			background-color: #f44336;
			color: white;
			padding: 10px 20px;
			border: none;
			border-radius: 4px;
			cursor: pointer;
			margin-top: 10px;
		}

		.table_cache_list td input[type="submit"]:hover {
			background-color: #d32f2f;
		}
	</style>
	<div class="wrap">
		<h2>Cache</h2>
		<table class="table_cache_list">
		<thead>
			<tr>
				<th>Cache Key</th>
				<th>Condition</th>
				<th>Cache Value</th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $cache_results as $key => $result ) : ?>
			<?php

				$value = get_transient( $key, [] );
				$posts = [];
				if ( is_array( $value ) && ! empty( $value ) ) {
					foreach ( $value as $post_id ) {
						$title = get_the_title( $post_id );
						if ( empty( $title ) ) {
							$title = $post_id;
						}
						$posts[] = $title;
					}
				}
			?>
			<tr>
				<td class="key">
					<?php echo esc_html( $key ); ?>
					<form method="POST">
						<?php
							$nonce = wp_create_nonce( 'delete_cache_' . $key );
						?>
						<input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>">
						<input type="hidden" name="cache_key" value="<?php echo esc_attr( $key ); ?>">
						<input type="submit" name="delete_cache" value="DELETE" onclick="return confirm('このキャッシュを削除しますか？');" >
					</form>
				</td>
				<td>
					<?php if ( isset( $result ) ) : ?>
						<?php foreach ( $result as $condition_key => $condition_value ) : ?>
							<?php echo esc_html( $condition_key ); ?>: <?php echo esc_html( $condition_value ); ?><br>
						<?php endforeach; ?>
					<?php else : ?>
						-
					<?php endif; ?>
				</td>
				<td>
					<?php
						$posts = get_transient( $key, [] );
					?>
					<?php if ( is_array( $posts ) && ! empty( $posts ) ) : ?>
						<ol>
						<?php foreach ( $posts as $post_id ) : ?>
							<?php
								$title = get_the_title( $post_id );
								if ( empty( $title ) ) {
									$title = $post_id;
								}
							?>
							<li>
								<a href="<?php echo esc_url( get_the_permalink( $post_id ) ) ?>" target="_blank"><?php echo esc_html( $title ); ?></a>
							</li>
						<?php endforeach; ?>
						</ol>
					<?php else : ?>
						<?php echo esc_html( var_export( $value, true ) ); ?>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
		</table>
	</div>
		<?php
	}

}
