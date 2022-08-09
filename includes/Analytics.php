<?php
namespace digitalcube\SimpleGA4Ranking;

use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;

class Analytics {

	const API_SCOPES = [
		'https://www.googleapis.com/auth/analytics',
		'openid',
		'https://www.googleapis.com/auth/analytics.readonly',
	];

	private $property_id = '';

	public function __construct( $property_id ) {
		$this->property_id = $property_id;
	}

	public function fetch( $params ) {
		$api_client    = null;
		$api_parameter = null;
		$api_response  = null;
		$result        = new \stdClass();
		$result->rows  = [];
		try {
			$api_client    = new BetaAnalyticsDataClient( [ 'credentials' => $this->create_credentials() ] );
			$api_parameter = apply_filters( 'sga_ranking_api_parameter', $this->create_api_parameter( $params ) );
			$api_response  = $api_client->runReport( $api_parameter );
			foreach ( $api_response->getRows() as $row ) {
				$page_path = $this->get_dimension_value( $row );
				$page_view = $this->get_metric_value( $row );
				if ( ! is_null( $page_path ) && ! is_null( $page_view ) ) {
					$result->rows[] = [ $page_path, $page_view ];
				}
			}
		} catch ( \Exception $ex ) {
			error_log( '-----SGA4 API ERROR-----' );          // phpcs:ignore
			if ( ! is_null( $api_parameter ) ) {
				error_log( 'Parameter:' );                    // phpcs:ignore
				error_log( print_r( $api_parameter, true ) ); // phpcs:ignore
			}
			if ( ! is_null( $api_response ) ) {
				error_log( 'Response:' );                     // phpcs:ignore
				error_log( print_r( $api_response, true ) );  // phpcs:ignore
			}
			error_log( 'Error:' );                            // phpcs:ignore
			error_log( print_r( $ex, true ) );                // phpcs:ignore
		}
		return apply_filters( 'sga_ranking_api_result', $result );
	}

	public function create_api_parameter( $args ) {

		$limit = $args[2]['max-results'];

		$date_ranges = new DateRange(
			[
				'start_date' => $args[0],
				'end_date'   => $args[1],
			]
		);

		$dimension = new Dimension(
			[
				'name' => apply_filters( 'sga_ranking_api_parameter_dimension', 'pagePath' ),
			]
		);

		$metric = new Metric(
			[
				'name' => apply_filters( 'sga_ranking_api_parameter_metric', 'screenPageViews' ),
			]
		);

		return [
			'property'   => 'properties/' . $this->property_id,
			'limit'      => $limit,
			'dateRanges' => [ $date_ranges ],
			'dimensions' => [ $dimension ],
			'metrics'    => [ $metric ],
		];
	}

	public function create_credentials() {
		$auth = new Admin\OAuth\Auth();
		return \Google\ApiCore\CredentialsWrapper::build(
			[
				'scopes'  => self::API_SCOPES,
				'keyFile' => [
					'type'          => 'authorized_user',
					'client_id'     => Admin\OAuth\Admin::option( 'client_id' ),
					'client_secret' => Admin\OAuth\Admin::option( 'client_secret' ),
					'refresh_token' => $auth->get_refresh_token(),
				],
			]
		);
	}

	public function get_dimension_value( $api_response_row ) {
		try {
			$demension_values = $api_response_row->getDimensionValues();
			if ( $demension_values instanceof \Google\Protobuf\Internal\RepeatedField ) {
				return $demension_values[0]->getValue();
			} else {
				return null;
			}
		} catch ( \Exception $ex ) {
			return null;
		}
	}

	public function get_metric_value( $api_response_row ) {
		try {
			$metric_values = $api_response_row->getMetricValues();
			if ( $metric_values instanceof \Google\Protobuf\Internal\RepeatedField ) {
				return $metric_values[0]->getValue();
			} else {
				return null;
			}
		} catch ( \Exception $ex ) {
			return null;
		}
	}

}
