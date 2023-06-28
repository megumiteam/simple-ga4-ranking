<?php
namespace digitalcube\SimpleGA4Ranking;

class AutoUpdate {

	const VERSION_CHECK_JSON_URL = 'https://raw.githubusercontent.com/megumiteam/simple-ga4-ranking/main/version.json';
	const RELEASE_ZIP_URL        = 'https://github.com/megumiteam/simple-ga4-ranking/releases/download/{version}/release.zip';

	public function register_hooks() {
		add_filter( 'pre_set_site_transient_update_plugins', [ $this, 'pre_set_site_transient_update_plugins' ] );
	}

	public function pre_set_site_transient_update_plugins( $transient ) {
		$plugin_data              = get_plugin_data( SGA4R_PLUGIN_MAIN_FILE );
		$installed_plugin_version = $plugin_data['Version'];
		if ( empty( $installed_plugin_version ) ) {
			return $transient;
		}
		$remote_version = wp_remote_get( self::VERSION_CHECK_JSON_URL, [ 'timeout' => 3 ] );
		if ( 200 !== wp_remote_retrieve_response_code( $remote_version ) ) {
			return $transient;
		}
		$remote_version = json_decode( wp_remote_retrieve_body( $remote_version ) );
		if ( ! isset( $remote_version->version ) || empty( $remote_version->version ) ) {
			return $transient;
		}
		if ( version_compare( $installed_plugin_version, $remote_version->version, '<' ) ) {
			$transient->response[ SGA4_PLUGIN_BASE ] = (object) [
				'id'            => SGA4_PLUGIN_BASE,
				'slug'          => SGA4_PLUGIN_SLUG,
				'new_version'   => $remote_version->version,
				'url'           => str_replace( '{version}', $remote_version->version, self::RELEASE_ZIP_URL ),
				'package'       => str_replace( '{version}', $remote_version->version, self::RELEASE_ZIP_URL ),
				'compatibility' => new \stdClass(),
			];
		}
		return $transient;
	}
}
