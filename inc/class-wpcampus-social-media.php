<?php

/**
 * PHP class that holds the main/administrative
 * functionality for the plugin.
 *
 * @category    Class
 * @package     WPCampus: Social Media
 */
final class WPCampus_Social_Media {

	const FEED_QUERY_VAR = 'wpcampus_social_feed';

	const FEED_DEFAULT = 'twitter';

	const FEED_WEIGHT_DEFAULT = 5;

	const META_KEY_SOCIAL_PLATFORM = 'wpc_social_platform';

	const META_KEY_SOCIAL_DEACTIVATE = 'wpc_social_deactivate';

	const META_KEY_SOCIAL_START_DATE_TIME = 'wpc_social_start_date_time';
	const META_KEY_SOCIAL_END_DATE_TIME = 'wpc_social_end_date_time';

	const META_KEY_SOCIAL_WEIGHT_TWITTER = 'wpc_social_weight_twitter';
	const META_KEY_SOCIAL_WEIGHT_FACEBOOK = 'wpc_social_weight_facebook';
	const META_KEY_SOCIAL_WEIGHT_SLACK = 'wpc_social_weight_slack';

	const META_KEY_SOCIAL_MESSAGE_TWITTER = 'wpc_social_message_twitter';
	const META_KEY_SOCIAL_MESSAGE_FACEBOOK = 'wpc_social_message_facebook';
	const META_KEY_SOCIAL_MESSAGE_SLACK = 'wpc_social_message_slack';

	const USER_CAP_SOCIAL_SHARE = 'wpc_share_social_media';

	const FORMAT_DATE_TIME = 'Y-m-d H:i:s';

	/**
	 * Holds the absolute URL to
	 * the main plugin directory.
	 * Used for assets.
	 *
	 * @var string
	 */
	private $plugin_url;

	/**
	 * Holds the directory path
	 * to the main plugin directory.
	 * Used to require PHP files.
	 *
	 * @var string
	 */
	private $plugin_dir;

	/**
	 * Holds the relative "path"
	 * to the main plugin file.
	 *
	 * @var string
	 */
	private $plugin_basename;

	/**
	 *
	 */
	private $site_timezone;

	/**
	 *
	 */
	private $user_cap_manage_social_media = 'wpc_manage_social_media';

	/**
	 * @TODO needs setting
	 *
	 * @TODO add proposal but add filter so only for confirmed.
	 *
	 * @var array
	 */
	private $share_post_types = array( 'post', 'page', 'schedule', 'notification', 'podcast', 'resource', 'opportunity' );

	/**
	 * The names of our social media platforms.
	 *
	 * @var array
	 */
	private $social_media_platforms = array(
		'twitter',
		'facebook',
		'slack',
	);

	/**
	 * The names of our feeds.
	 *
	 * @var array
	 */
	private $social_feeds = array(
		'feed/social',
		'feed/social/twitter',
		'feed/social/facebook',
		'feed/social/slack',
	);

	/**
	 * @TODO needs setting
	 *
	 * @var array
	 */
	private $max_message_length = array(
		'facebook' => 400,
		'twitter'  => 400,
		'slack'    => 0,
	);

	/**
	 * Holds the class instance.
	 *
	 * @var WPCampus_Social_Media
	 */
	private static $instance;

	/**
	 * Returns the instance of this class.
	 *
	 * @return WPCampus_Social_Media
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			$class_name     = __CLASS__;
			self::$instance = new $class_name;
		}
		return self::$instance;
	}

	/**
	 * Magic method to output a string if
	 * trying to use the object as a string.
	 *
	 * @return string
	 */
	public function __toString() {
		// translators: Basic name of the plugin
		return sprintf( __( '%s: Social Media', 'wpcampus-social' ), 'WPCampus' );
	}

	/**
	 * Method to keep our instance
	 * from being cloned or unserialized
	 * and to prevent a fatal error when
	 * calling a method that doesn't exist.
	 *
	 * @return void
	 */
	public function __clone() {}
	public function __wakeup() {}
	public function __call( $method = '', $args = array() ) {}

	/**
	 * Start your engines.
	 *
	 * @return void
	 */
	protected function __construct() {}

	/**
	 * Returns the absolute URL to
	 * the main plugin directory.
	 *
	 * @return string
	 */
	public function get_plugin_url() {
		if ( isset( $this->plugin_url ) ) {
			return $this->plugin_url;
		}
		$this->plugin_url = plugin_dir_url( dirname( __FILE__ ) );
		return $this->plugin_url;
	}

	/**
	 * Returns the directory path
	 * to the main plugin directory.
	 *
	 * @return string
	 */
	public function get_plugin_dir() {
		if ( isset( $this->plugin_dir ) ) {
			return $this->plugin_dir;
		}
		$this->plugin_dir = plugin_dir_path( dirname( __FILE__ ) );
		return $this->plugin_dir;
	}

	/**
	 * Returns the relative "path"
	 * to the main plugin file.
	 *
	 * @return string
	 */
	public function get_plugin_basename() {
		if ( isset( $this->plugin_basename ) ) {
			return $this->plugin_basename;
		}
		$this->plugin_basename = 'wpcampus-social-media-plugin/wpcampus-social-media.php';
		return $this->plugin_basename;
	}

	public function get_format_date_time() {
		return self::FORMAT_DATE_TIME;
	}

	/**
	 * @return DateTimeZone
	 */
	public function get_utc_timezone() {
		return new DateTimeZone( 'UTC' );
	}

	/**
	 * @return DateTimeZone
	 */
	public function get_site_timezone() : DateTimeZone {
		if ( isset( $this->site_timezone ) ) {
			return $this->site_timezone;
		}

		$timezone = get_option( 'timezone_string' );

		if ( empty( $timezone ) ) {
			$timezone = 'UTC';
		}

		return $this->site_timezone = new DateTimeZone( $timezone );
	}

	/**
	 * Return the platform for a specific feed.
	 *
	 * @param $query - WP_Query object
	 * @return string - the platform.
	 */
	public function get_query_feed_platform( $query ) {

		$feed = $query->get( $this->get_feed_query_var() );

		if ( ! in_array( $feed, $this->get_social_media_platforms() ) ) {
			return $this->get_feed_default();
		}

		return $feed;
	}

	/**
	 * Return an array of social media platforms.
	 *
	 * @return array of platforms
	 */
	public function get_social_media_platforms() {
		return $this->social_media_platforms;
	}

	/**
	 * Return an array of social media feeds.
	 *
	 * @return array of feeds
	 */
	public function get_social_feeds() {
		return $this->social_feeds;
	}

	/**
	 *
	 */
	public function get_feed_query_var() {
		return self::FEED_QUERY_VAR;
	}

	/**
	 *
	 */
	public function get_feed_default() {
		return self::FEED_DEFAULT;
	}

	/**
	 *
	 */
	public function get_feed_weight_default() : int {
		return self::FEED_WEIGHT_DEFAULT;
	}

	/**
	 *
	 */
	public function get_meta_key_social_platform() {
		return self::META_KEY_SOCIAL_PLATFORM;
	}

	/**
	 *
	 */
	public function get_meta_key_social_deactivate() : string {
		return self::META_KEY_SOCIAL_DEACTIVATE;
	}

	/**
	 *
	 */
	public function get_meta_key_social_start_date_time() : string {
		return self::META_KEY_SOCIAL_START_DATE_TIME;
	}

	/**
	 *
	 */
	public function get_meta_key_social_end_date_time() : string {
		return self::META_KEY_SOCIAL_END_DATE_TIME;
	}

	/**
	 *
	 */
	public function get_meta_key_social_weight( string $platform ) : string {
		if ( 'twitter' == $platform ) {
			return self::META_KEY_SOCIAL_WEIGHT_TWITTER;
		} elseif ( 'facebook' == $platform ) {
			return self::META_KEY_SOCIAL_WEIGHT_FACEBOOK;
		} elseif ( 'slack' == $platform ) {
			return self::META_KEY_SOCIAL_WEIGHT_SLACK;
		}
		return '';
	}

	/**
	 *
	 */
	public function get_meta_key_social_weight_twitter() {
		return $this->get_meta_key_social_weight( 'twitter' );
	}

	/**
	 *
	 */
	public function get_meta_key_social_weight_facebook() {
		return $this->get_meta_key_social_weight( 'facebook' );
	}

	/**
	 *
	 */
	public function get_meta_key_social_weight_slack() {
		return $this->get_meta_key_social_weight( 'slack' );
	}

	/**
	 *
	 */
	public function get_meta_key_social_message( string $platform ) : string {
		if ( 'twitter' == $platform ) {
			return self::META_KEY_SOCIAL_MESSAGE_TWITTER;
		} elseif ( 'facebook' == $platform ) {
			return self::META_KEY_SOCIAL_MESSAGE_FACEBOOK;
		} elseif ( 'slack' == $platform ) {
			return self::META_KEY_SOCIAL_MESSAGE_SLACK;
		}
		return '';
	}

	/**
	 *
	 */
	public function get_meta_key_social_message_twitter() {
		return $this->get_meta_key_social_message( 'twitter' );
	}

	/**
	 *
	 */
	public function get_meta_key_social_message_facebook() {
		return $this->get_meta_key_social_message( 'facebook' );
	}

	/**
	 *
	 */
	public function get_meta_key_social_message_slack() {
		return $this->get_meta_key_social_message( 'slack' );
	}

	/**
	 *
	 */
	public function is_social_feed( WP_Query $query ) {
		return (bool) $query->get( self::FEED_QUERY_VAR );
	}

	/**
	 *
	 */
	public function filter_social_deactivated( $deactivated, int $post_id ) : bool {
		return (bool) apply_filters( 'wpcampus_social_deactivated', (bool) $deactivated, $post_id );
	}

	/**
	 *
	 */
	public function is_social_deactivated( $post_id ) : bool {
		$deactivated = (bool) get_post_meta( $post_id, $this->get_meta_key_social_deactivate(), true );
		return $this->filter_social_deactivated( $deactivated, $post_id );
	}

	/**
	 *
	 */
	public function filter_social_expired( $start_date_time_str, $end_date_time_str, int $post_id ) : bool {

		$expired = false;

		$utc_timezone = $this->get_utc_timezone();
		$now = new DateTime( 'now', $utc_timezone );

		if ( ! empty( $start_date_time_str ) && false !== strtotime( $start_date_time_str ) ) {

			$start_date_time = new DateTime( $start_date_time_str );
			$start_date_time->setTimezone( $utc_timezone );

			// This post is expired.
			if ( $start_date_time > $now ) {
				$expired = true;
			}
		}

		if ( ! $expired ) {

			if ( ! empty( $end_date_time_str ) && false !== strtotime( $end_date_time_str ) ) {

				$end_date_time = new DateTime( $end_date_time_str );
				$end_date_time->setTimezone( $utc_timezone );

				// This post is expired.
				if ( $end_date_time <= $now ) {
					$expired = true;
				}
			}
		}

		return (bool) apply_filters( 'wpcampus_social_expired', $expired, $start_date_time_str, $end_date_time_str, $post_id );
	}

	/**
	 *
	 */
	public function is_social_expired( $post_id ) : bool {
		$start_date_time = $this->get_social_media_start_date_time( $post_id );
		$end_date_time = $this->get_social_media_end_date_time( $post_id );
		return $this->filter_social_expired( $start_date_time, $end_date_time, $post_id );
	}

	/**
	 *
	 */
	public function filter_social_platforms( $platforms, int $post_id ) : array {

		if ( empty( $platforms ) ) {
			return [];
		}

		if ( ! is_array( $platforms ) ) {
			return explode( ',', $platforms );
		}

		// For some reason the array is inside another array. This pulls it out.
		$platforms_test = $platforms;
		$platforms_array = array_shift( $platforms_test );

		if ( is_array( $platforms_array ) ) {
			return $platforms_array;
		}

		return $platforms;
	}

	/**
	 *
	 */
	public function get_social_platforms( int $post_id ) : array {
		return $this->filter_social_platforms( get_post_meta( $post_id, $this->get_meta_key_social_platform() ) );
	}

	/**
	 *
	 */
	public function get_social_feed( $platform ) {
		global $wpdb;

		if ( ! in_array( $platform, $this->get_social_media_platforms() ) ) {
			return [];
		}

		$post_types = $this->get_share_post_types();

		if ( empty( $post_types ) ) {
			return [];
		}

		// Get the current time.
		$timezone = $this->get_site_timezone();
		$current_time = new DateTime( 'now', $timezone );

		// Get the timezone offset.
		$current_time_offset = (int) $current_time->getOffset();

		// Get the difference in hours.
		$timezone_offset_hours = ( abs( $current_time_offset ) / 60 ) / 60;
		$timezone_offset_hours = ( $current_time_offset < 0 ) ? ( 0 - $timezone_offset_hours ) : $timezone_offset_hours;

		$platform_key = $this->get_meta_key_social_platform();

		$message_key = $this->get_meta_key_social_message( $platform );

		$deactivate_key = $this->get_meta_key_social_deactivate();

		$start_date_time_key = $this->get_meta_key_social_start_date_time();
		$end_date_time_key = $this->get_meta_key_social_end_date_time();

		$weight_key = $this->get_meta_key_social_weight( $platform );

		// @TODO remember this?
		//CONVERT( coalesce(end_date_time.meta_value, '2038-01-01 00:00:00'), DATETIME ) desc,

		$query = "SELECT posts.ID,
			posts.message,
			deactivate.meta_value AS deactivate,
			start_date_time.meta_value AS start_date_time,
			end_date_time.meta_value AS end_date_time,
			weight.meta_value AS weight
			FROM (
			    SELECT posts.ID, posts.post_modified_gmt, message.meta_value AS message FROM {$wpdb->posts} posts
			    INNER JOIN {$wpdb->postmeta} message ON message.post_id = posts.ID AND message.meta_key = '" . $message_key . "' AND message.meta_value != ''
			    INNER JOIN {$wpdb->postmeta} platforms ON platforms.post_id = posts.ID AND platforms.meta_key = '" . $platform_key . "' AND platforms.meta_value LIKE '%" . $platform . "%'
			    WHERE posts.post_type IN ('" . implode( "','", $post_types ) . "') AND posts.post_status = 'publish'
			) AS posts
			LEFT JOIN {$wpdb->postmeta} deactivate ON deactivate.post_id = posts.ID AND deactivate.meta_key = '" . $deactivate_key . "'
			LEFT JOIN {$wpdb->postmeta} start_date_time ON start_date_time.post_id = posts.ID AND start_date_time.meta_key = '" . $start_date_time_key . "'
			LEFT JOIN {$wpdb->postmeta} end_date_time ON end_date_time.post_id = posts.ID AND end_date_time.meta_key = '" . $end_date_time_key . "'
			LEFT JOIN {$wpdb->postmeta} weight ON weight.post_id = posts.ID AND weight.meta_key = '" . $weight_key . "'
			WHERE ( deactivate.meta_value IS NULL OR deactivate.meta_value != '1' )
				AND IF ( start_date_time.meta_value IS NOT NULL AND start_date_time.meta_value != '', CONVERT( start_date_time.meta_value, DATETIME ) <= DATE_ADD( NOW(), INTERVAL " . $timezone_offset_hours . " HOUR ), true )
				AND IF ( end_date_time.meta_value IS NOT NULL AND end_date_time.meta_value != '', CONVERT( end_date_time.meta_value, DATETIME ) > DATE_ADD( NOW(), INTERVAL " . $timezone_offset_hours . " HOUR ), true )
			ORDER BY posts.post_modified_gmt DESC";

		$items = $wpdb->get_results( $query );

		if ( empty( $items ) ) {
			return [];
		}

		$feed_items = [];

		$utc_timezone = $this->get_utc_timezone();
		$now = new DateTime( 'now', $utc_timezone );

		foreach ( $items as &$item ) {

			$message = $this->filter_social_media_message( $item->message, $item->ID, $platform );

			if ( empty( $message ) ) {
				$message = null;
			}

			$start_date_time_str = $this->filter_social_media_start_date_time( (string) $item->start_date_time, $item->ID, $platform );

			if ( empty( $start_date_time_str ) ) {
				$start_date_time = null;
			} elseif ( false === strtotime( $start_date_time_str ) ) {
				$start_date_time = null;
			} else {

				$start_date_time = new DateTime( $start_date_time_str );
				$start_date_time->setTimezone( $utc_timezone );

				// This post is expired.
				if ( $start_date_time > $now ) {
					$start_date_time = null;
					continue;
				} else {
					$start_date_time = $start_date_time->format( $this->get_format_date_time() );
				}
			}

			$end_date_time_str = $this->filter_social_media_end_date_time( (string) $item->end_date_time, $item->ID, $platform );

			if ( empty( $end_date_time_str ) ) {
				$end_date_time = null;
			} elseif ( false === strtotime( $end_date_time_str ) ) {
				$end_date_time = null;
			} else {

				$end_date_time = new DateTime( $end_date_time_str );
				$end_date_time->setTimezone( $utc_timezone );

				// This post is expired.
				if ( $end_date_time <= $now ) {
					$end_date_time = null;
					continue;
				} else {
					$end_date_time = $end_date_time->format( $this->get_format_date_time() );
				}
			}

			$weight = $this->filter_social_media_weight( (string) $item->weight, $item->ID, $platform );

			$feed_items[] = [
				'ID'        => $item->ID,
				'active'    => empty( $item->deactivate ),
				'message'   => $message,
				'permalink' => get_permalink( $item->ID ),
				'start'     => $start_date_time,
				'end'       => $end_date_time,
				'weight'    => $weight,
			];
		}

		return $feed_items;
	}

	private function sanitize_social_media_message( $message ) {
		return trim( strip_tags( sanitize_text_field( $message ) ) );
	}

	/**
	 *
	 */
	public function filter_social_media_message( string $message, int $post_id, string $platform ) : string {
		return apply_filters( 'wpcampus_social_message', $message, $post_id, $platform );
	}

	/**
	 *
	 */
	public function filter_social_media_start_date_time( string $start_date_time, int $post_id, string $platform = '' ) : string {
		return apply_filters( 'wpcampus_social_start_date_time', $start_date_time, $post_id, $platform );
	}

	/**
	 *
	 */
	public function filter_social_media_end_date_time( string $end_date_time, int $post_id, string $platform = '' ) : string {
		return apply_filters( 'wpcampus_social_end_date_time', $end_date_time, $post_id, $platform );
	}

	/**
	 *
	 */
	public function get_social_media_start_date_time( int $post_id ) : string {
		$start_date_time = (string) get_post_meta( $post_id, $this->get_meta_key_social_start_date_time() );
		return $this->filter_social_media_start_date_time( $start_date_time, $post_id );
	}

	/**
	 *
	 */
	public function get_social_media_end_date_time( int $post_id ) : string {
		$end_date_time = (string) get_post_meta( $post_id, $this->get_meta_key_social_end_date_time() );
		return $this->filter_social_media_end_date_time( $end_date_time, $post_id );
	}

	/**
	 *
	 */
	public function filter_social_media_weight( string $weight, int $post_id, string $platform ) : int {

		if ( empty( $weight ) ) {
			$weight = $this->get_feed_weight_default();
		}

		return (int) apply_filters( 'wpcampus_social_weight', (int) $weight, $post_id, $platform );
	}

	/**
	 *
	 */
	public function get_social_media_message_raw( int $post_id, string $platform ) : string {

		if ( ! in_array( $platform, $this->get_social_media_platforms() ) ) {
			return '';
		}

		$message_meta_key = $this->get_meta_key_social_message( $platform );

		if ( empty( $message_meta_key ) ) {
			return '';
		}

		$message = get_post_meta( $post_id, $message_meta_key, true );

		// Sanitize the message.
		return $this->sanitize_social_media_message( $message );
	}

	/**
	 * Get a social media message depending on platform.
	 *
	 * @param $post_id - int - the post ID.
	 * @param $platform - string - the platform name.
	 *
	 * @return string - the message.
	 */
	public function get_social_media_message( int $post_id, string $platform ) : string {

		$message = $this->get_social_media_message_raw( $post_id, $platform );

		$message = $this->filter_social_media_message( $message, $post_id, $platform );

		// Sanitize the message.
		return $this->sanitize_social_media_message( $message );
	}

	/**
	 *
	 */
	public function update_social_media_message( int $post_id, string $message, string $platform ) : bool {

		if ( ! in_array( $platform, $this->get_social_media_platforms() ) ) {
			return false;
		}

		// Sanitize the value.
		$message = $this->sanitize_social_media_message( $message );

		// Trim to max length.
		$max_message_length = $this->get_max_message_length( $platform );
		if ( $max_message_length > 0 ) {
			$message = substr( $message, 0, $max_message_length );
		}

		$message_meta_key = $this->get_meta_key_social_message( $platform );

		if ( empty( $message_meta_key ) ) {
			return false;
		}

		update_post_meta( $post_id, $message_meta_key, $message );

		return true;
	}

	/**
	 * Return the user capability
	 * string to manage social media.
	 */
	public function get_user_cap_manage_string() {
		return $this->user_cap_manage_social_media;
	}

	/**
	 * Return the user capability
	 * string to share social media.
	 */
	public function get_user_cap_share_string() {
		return self::USER_CAP_SOCIAL_SHARE;
	}

	public function get_share_post_types() : array {
		return $this->share_post_types;
	}

	/**
	 * Creating tweet intent URL.
	 *
	 * @param   $args - array - the arguments for the URL.
	 * @return  string - the URL.
	 */
	public function get_tweet_intent_url( $args ) {

		// Build arguments.
		$final_args = array();

		if ( ! empty( $args['url'] ) ) {
			$final_args['url'] = urlencode( trim( strip_tags( $args['url'] ) ) );
		}

		if ( ! empty( $args['via'] ) ) {
			$final_args['via'] = urlencode( trim( strip_tags( $args['via'] ) ) );
		}

		if ( ! empty( $args['text'] ) ) {
			$final_args['text'] = urlencode( trim( strip_tags( $args['text'] ) ) );
		}

		if ( ! empty( $args['hashtags'] ) ) {
			if ( is_string( $args['hashtags'] ) ) {
				$final_args['hashtags'] = trim( strip_tags( $args['hashtags'] ) );
			} elseif ( is_array( $args['hashtags'] ) ) {
				$args['hashtags'] = array_map( 'strip_tags', $args['hashtags'] );
				$args['hashtags'] = array_map( 'trim', $args['hashtags'] );
				$args['hashtags'] = urlencode( implode( ',', $args['hashtags'] ) );
			}
		}

		return add_query_arg( $final_args, 'https://twitter.com/intent/tweet' );
	}

	/**
	 * Get the max message length depending on platform.
	 *
	 * If no platform is passed, will get max lengths for all platforms.
	 *
	 * @args    $platform - e.g. 'facebook' or 'twitter'.
	 * @return  int|array - if platform, returns length for platform. Array of all otherwise.
	 */
	public function get_max_message_length( $platform = '' ) {

		if ( ! empty( $platform ) ) {

			if ( ! empty( $this->max_message_length[ $platform ] ) ) {

				// If no set length in options, return default length.
				return (int) $this->max_message_length[ $platform ];
			}

			return 0;
		}

		// If no specific platform, get all of them.
		$max_lengths = array();

		foreach ( $this->max_message_length as $platform_key => $platform_length ) {

			// If no set length in options, set default length.
			$max_lengths[ $platform_key ] = (int) $platform_length;
		}

		return $max_lengths;
	}

	/**
	 * Returns the IDs of excluded posts.
	 *
	 * @TODO need to setup. Was using TOP plugin.
	 *
	 * @args    $platform - the social media platform, e.g. "facebook" or "twitter".
	 * @return  array - array of post IDs.
	 */
	public function get_excluded_posts( $platform ) {
		return array();
	}

	/**
	 * Will return true if post is an
	 * excluded post on a specific platform.
	 *
	 * @TODO need to setup. Was using TOP plugin.
	 *
	 * @args    $post_id - int - the post ID.
	 * @args    $platform  - string - e.g. 'facebook' or 'twitter'.
	 */
	public function is_excluded_post( $post_id, $platform ) {
		return in_array( $post_id, $this->get_excluded_posts( $platform ) );
	}

	/**
	 *
	 */
	public function get_posts( array $args = [] ) : array {
		global $wpdb;

		// Define the defaults.
		$defaults = [
			'post_type' => '',
		];

		$args = wp_parse_args( $args, $defaults );

		$platform_key = $this->get_meta_key_social_platform();

		$twitter_message_key = $this->get_meta_key_social_message_twitter();
		$facebook_message_key = $this->get_meta_key_social_message_facebook();
		$slack_message_key = $this->get_meta_key_social_message_slack();

		$deactivate_key = $this->get_meta_key_social_deactivate();

		$start_date_time_key = $this->get_meta_key_social_start_date_time();
		$end_date_time_key = $this->get_meta_key_social_end_date_time();

		$twitter_weight_key = $this->get_meta_key_social_weight_twitter();
		$facebook_weight_key = $this->get_meta_key_social_weight_facebook();
		$slack_weight_key = $this->get_meta_key_social_weight_slack();

		$post_type = ! empty( $args['post_type'] ) ? $args['post_type'] : $this->get_share_post_types();

		if ( ! is_array( $post_type ) ) {
			$post_type = explode( ',', $post_type );
			$post_type = array_map( 'trim', $post_type );
		}

		$where_inner = [ "posts.post_status = 'publish'" ];

		if ( ! empty( $post_type ) ) {
			$where_inner[] = "posts.post_type IN ('" . implode( "','", $post_type ) . "')";
		}

		// @TODO remove any joins we dont need.
		$query = "SELECT posts.ID,
			posts.post_type,
			platforms.meta_value AS platforms,
			message_twitter.meta_value AS message_twitter,
			message_facebook.meta_value AS message_facebook,
			message_slack.meta_value AS message_slack,
			deactivate.meta_value AS deactivate,
			start_date_time.meta_value AS start_date_time,
			end_date_time.meta_value AS end_date_time,
			weight_twitter.meta_value AS weight_twitter,
			weight_facebook.meta_value AS weight_facebook,
			weight_slack.meta_value AS weight_slack
			FROM (
			    SELECT posts.*
			    FROM {$wpdb->posts} posts";

		if ( ! empty( $where_inner ) ) {
			$query .= " WHERE " . implode( ' AND ', $where_inner );
		}

		$query .= $wpdb->prepare( ") AS posts
			LEFT JOIN {$wpdb->postmeta} platforms ON platforms.post_id = posts.ID AND platforms.meta_key = %s
			LEFT JOIN {$wpdb->postmeta} message_twitter ON message_twitter.post_id = posts.ID AND message_twitter.meta_key = %s
			LEFT JOIN {$wpdb->postmeta} message_facebook ON message_facebook.post_id = posts.ID AND message_facebook.meta_key = %s
			LEFT JOIN {$wpdb->postmeta} message_slack ON message_slack.post_id = posts.ID AND message_slack.meta_key = %s
			LEFT JOIN {$wpdb->postmeta} deactivate ON deactivate.post_id = posts.ID AND deactivate.meta_key = %s
			LEFT JOIN {$wpdb->postmeta} start_date_time ON start_date_time.post_id = posts.ID AND start_date_time.meta_key = %s
			LEFT JOIN {$wpdb->postmeta} end_date_time ON end_date_time.post_id = posts.ID AND end_date_time.meta_key = %s
			LEFT JOIN {$wpdb->postmeta} weight_twitter ON weight_twitter.post_id = posts.ID AND weight_twitter.meta_key = %s
			LEFT JOIN {$wpdb->postmeta} weight_facebook ON weight_facebook.post_id = posts.ID AND weight_facebook.meta_key = %s
			LEFT JOIN {$wpdb->postmeta} weight_slack ON weight_slack.post_id = posts.ID AND weight_slack.meta_key = %s
			ORDER BY deactivate.meta_value ASC, posts.post_type ASC",
             $platform_key,
             $twitter_message_key,
             $facebook_message_key,
             $slack_message_key,
             $deactivate_key,
             $start_date_time_key,
             $end_date_time_key,
             $twitter_weight_key,
             $facebook_weight_key,
             $slack_weight_key
		);

		$posts = $wpdb->get_results( $query );

		if ( empty( $posts ) || ! is_array( $posts ) ) {
			return [];
		}

		return $posts;
	}
}
