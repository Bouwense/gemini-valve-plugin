<?php
/**
 * Plugin Name: GV - Image Assistant (Library + AI, Queued Fallback)
 * Description: Insert images from the Media Library or generate new images with OpenAI. Auto-prompt from post content, adaptive rate-limit backoff, and queued fallback with background retries.
 * Version:     1.5.0
 * Author:      Gemini Valve
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/* ────────────────────────────────────────────────────────────────────────── */
/* Polyfills (PHP 7 compatible)                                               */
/* ────────────────────────────────────────────────────────────────────────── */

if ( ! function_exists( 'str_contains' ) ) {
	function str_contains( $haystack, $needle ) {
		return $needle === '' || strpos( (string) $haystack, (string) $needle ) !== false;
	}
}

/* ────────────────────────────────────────────────────────────────────────── */
/* Settings: API Key (and optional Organization)                              */
/* ────────────────────────────────────────────────────────────────────────── */

add_action( 'admin_init', function () {
	register_setting( 'gv_image_assistant', 'gv_openai_api_key', array(
		'type'              => 'string',
		'sanitize_callback' => function( $v ){ return is_string( $v ) ? trim( $v ) : ''; },
		'default'           => '',
	) );
	register_setting( 'gv_image_assistant', 'gv_openai_org_id', array(
		'type'              => 'string',
		'sanitize_callback' => function( $v ){ return is_string( $v ) ? trim( $v ) : ''; },
		'default'           => '',
	) );
} );

add_action( 'admin_menu', function () {
	add_options_page(
		'OpenAI Image Assistant',
		'OpenAI Image Assistant',
		'manage_options',
		'gv-image-assistant',
		function () {
			?>
			<div class="wrap">
				<h1>OpenAI Image Assistant</h1>
				<form method="post" action="options.php">
					<?php
					settings_fields( 'gv_image_assistant' );
					$key = get_option( 'gv_openai_api_key', '' );
					$org = get_option( 'gv_openai_org_id', '' );
					?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="gv_openai_api_key">OpenAI API Key</label></th>
							<td>
								<input type="password" id="gv_openai_api_key" name="gv_openai_api_key" value="<?php echo esc_attr( $key ); ?>" class="regular-text" placeholder="sk-..." />
								<p class="description">Use a key with API billing enabled. ChatGPT Plus ≠ API credits.</p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="gv_openai_org_id">Organization ID (optional)</label></th>
							<td>
								<input type="text" id="gv_openai_org_id" name="gv_openai_org_id" value="<?php echo esc_attr( $org ); ?>" class="regular-text" placeholder="org_..." />
								<p class="description">Only if your account uses organizations; otherwise leave blank.</p>
							</td>
						</tr>
					</table>
					<?php submit_button(); ?>
				</form>
			</div>
			<?php
		}
	);
} );

/* ────────────────────────────────────────────────────────────────────────── */
/* Helpers: store base64 or URL image to Media Library (PHP 7 safe)           */
/* ────────────────────────────────────────────────────────────────────────── */

if ( ! function_exists( 'gv_image_assistant_store_b64' ) ) :
function gv_image_assistant_store_b64( $b64, $filename = 'ai-image.png', $attach_to = 0, $alt = '' ) {
	$upload = wp_upload_dir();
	if ( ! empty( $upload['error'] ) ) {
		return new WP_Error( 'upload_error', $upload['error'] );
	}
	$data = base64_decode( (string) $b64 );
	if ( $data === false ) {
		return new WP_Error( 'bad_b64', 'Could not decode image data.' );
	}
	$orig_name = sanitize_file_name( (string) $filename );
	$base = pathinfo( $orig_name, PATHINFO_FILENAME );
	$ext  = strtolower( pathinfo( $orig_name, PATHINFO_EXTENSION ) );
	if ( ! in_array( $ext, array( 'png', 'jpg', 'jpeg', 'webp' ), true ) ) {
		$ext = 'png';
	}
	$candidate  = ( $base !== '' ? $base : 'ai-image' ) . '.' . $ext;
	$safe_name  = wp_unique_filename( $upload['path'], $candidate );
	$file_path  = trailingslashit( $upload['path'] ) . $safe_name;

	if ( @file_put_contents( $file_path, $data ) === false ) {
		return new WP_Error( 'write_fail', 'Failed to write image file.' );
	}

	$filetype = wp_check_filetype( $safe_name, null );
	$attachment = array(
		'post_mime_type' => $filetype['type'] ? $filetype['type'] : 'image/png',
		'post_title'     => preg_replace( '/\.[^.]+$/', '', wp_basename( $safe_name ) ),
		'post_content'   => '',
		'post_status'    => 'inherit',
	);

	$attach_id = wp_insert_attachment( $attachment, $file_path, (int) $attach_to );
	if ( is_wp_error( $attach_id ) ) {
		@unlink( $file_path );
		return $attach_id;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	$attach_data = wp_generate_attachment_metadata( $attach_id, $file_path );
	wp_update_attachment_metadata( $attach_id, $attach_data );

	if ( is_string( $alt ) && $alt !== '' ) {
		update_post_meta( $attach_id, '_wp_attachment_image_alt', sanitize_text_field( $alt ) );
	}
	return $attach_id;
}
endif;

if ( ! function_exists( 'gv_image_assistant_store_url' ) ) :
function gv_image_assistant_store_url( $url, $attach_to = 0, $alt = '' ) {
	if ( ! function_exists( 'download_url' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	if ( ! function_exists( 'media_handle_sideload' ) ) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}

	$tmp = download_url( $url, 60 );
	if ( is_wp_error( $tmp ) ) {
		return $tmp;
	}
	$name = basename( parse_url( $url, PHP_URL_PATH ) );
	$file = array(
		'name'     => sanitize_file_name( $name ? $name : 'ai-image.jpg' ),
		'type'     => 'image/jpeg',
		'tmp_name' => $tmp,
		'error'    => 0,
		'size'     => filesize( $tmp ),
	);
	$overrides = array( 'test_form' => false, 'test_type' => false );
	$results = wp_handle_sideload( $file, $overrides );
	if ( isset( $results['error'] ) ) {
		@unlink( $tmp );
		return new WP_Error( 'sideload_error', $results['error'] );
	}
	$local_file = $results['file'];
	$filetype   = wp_check_filetype( basename( $local_file ), null );

	$attachment = array(
		'post_mime_type' => $filetype['type'],
		'post_title'     => preg_replace( '/\.[^.]+$/', '', wp_basename( $local_file ) ),
		'post_content'   => '',
		'post_status'    => 'inherit',
	);
	$attach_id = wp_insert_attachment( $attachment, $local_file, (int) $attach_to );
	if ( is_wp_error( $attach_id ) ) {
		return $attach_id;
	}
	$attach_data = wp_generate_attachment_metadata( $attach_id, $local_file );
	wp_update_attachment_metadata( $attach_id, $attach_data );

	if ( is_string( $alt ) && $alt !== '' ) {
		update_post_meta( $attach_id, '_wp_attachment_image_alt', sanitize_text_field( $alt ) );
	}
	return $attach_id;
}
endif;

/* ────────────────────────────────────────────────────────────────────────── */
/* Rate-limit helpers + robust OpenAI POST                                    */
/* ────────────────────────────────────────────────────────────────────────── */

if ( ! function_exists( 'gv_rate_limit_gate' ) ) :
function gv_rate_limit_gate( $key = 'openai', $min_interval = 2 ) {
	$transient_key = 'gv_rl_' . md5( $key );
	$last = get_transient( $transient_key );
	$now  = microtime( true );

	if ( $last ) {
		$elapsed = $now - (float) $last;
		if ( $elapsed < $min_interval ) {
			$sleep = ( $min_interval - $elapsed ) + ( mt_rand( 250, 1250 ) / 1000.0 ); // jitter
			usleep( (int) round( $sleep * 1e6 ) );
		}
	}
	set_transient( $transient_key, (string) microtime( true ), $min_interval );
}
endif;

if ( ! function_exists( 'gv_parse_retry_hint' ) ) :
function gv_parse_retry_hint( $response ) {
	$h = wp_remote_retrieve_headers( $response );
	$get = function( $name ) use ( $h ) {
		if ( is_array( $h ) && isset( $h[ $name ] ) ) return $h[ $name ];
		if ( is_object( $h ) && method_exists( $h, 'offsetGet' ) ) return $h->offsetGet( $name );
		return null;
	};
	$candidates = array(
		$get( 'retry-after' ),
		$get( 'x-ratelimit-reset-requests' ),
		$get( 'x-ratelimit-reset-tokens' ),
		$get( 'x-ratelimit-reset' ),
	);
	foreach ( $candidates as $v ) {
		if ( ! $v ) continue;
		$v = is_array( $v ) ? reset( $v ) : $v;
		$v = trim( (string) $v );
		if ( $v === '' ) continue;
		if ( preg_match( '/^(\d+(?:\.\d+)?)(ms|s)?$/i', $v, $m ) ) {
			$sec = (float) $m[1];
			if ( isset( $m[2] ) && strtolower( $m[2] ) === 'ms' ) $sec = $sec / 1000.0;
			return max( 0.5, $sec );
		}
	}
	return 0.0;
}
endif;

if ( ! function_exists( 'gv_openai_post_json_with_backoff' ) ) :
function gv_openai_post_json_with_backoff( $url, $headers, $payload, $max_attempts = 6, $timeout = 90 ) {
	$attempt   = 0;
	$totalWait = 0.0;
	$last      = null;

	while ( $attempt < $max_attempts ) {
		$attempt++;

		gv_rate_limit_gate( 'openai_general', 2 );

		$response = wp_remote_post( $url, array(
			'headers' => $headers,
			'body'    => wp_json_encode( $payload ),
			'timeout' => $timeout,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( (int) $code !== 429 ) {
			return $response; // success or non-429 error
		}

		$delay = gv_parse_retry_hint( $response );
		if ( $delay <= 0 ) {
			$delay = min( 20, pow( 2, $attempt - 1 ) + ( mt_rand( 250, 1250 ) / 1000.0 ) ); // 1,2,4,8,16
		}
		$totalWait += $delay;
		usleep( (int) round( $delay * 1e6 ) );
		$last = $response;
	}

	$msg = 'OpenAI rate limit hit. Retried ' . $attempt . '× over ~' . number_format_i18n( $totalWait, 1 ) . "s.";
	return new WP_Error( 'openai_rate_limited', $msg );
}
endif;

/* ────────────────────────────────────────────────────────────────────────── */
/* Shared: prompt generation + image generation                               */
/* ────────────────────────────────────────────────────────────────────────── */

function gv_ia_headers() {
	$api_key = get_option( 'gv_openai_api_key', '' );
	if ( empty( $api_key ) ) return new WP_Error( 'no_api_key', 'OpenAI API key is not configured (Settings → OpenAI Image Assistant).' );
	$h = array(
		'Authorization' => 'Bearer ' . $api_key,
		'Content-Type'  => 'application/json',
	);
	$org = get_option( 'gv_openai_org_id', '' );
	if ( $org ) $h['OpenAI-Organization'] = $org;
	return $h;
}

function gv_ia_generate_prompt_text( $title, $content, $purpose = 'featured', $lang = 'en' ) {
	$locale_line = $lang === 'nl' ? 'Schrijf de prompt in het Nederlands.' : 'Write the prompt in English.';
	return $locale_line . " Create a short, vivid image generation prompt for a blog post $purpose image based on the title and content summary below. Avoid text in the image. Return ONLY the prompt, no quotes.\n\n"
		. "TITLE: {$title}\n\nCONTENT SUMMARY:\n{$content}";
}

function gv_ia_do_generate_images( $post_id, $prompt, $size = 'auto', $n = 1, $format = 'png', $transparent = false, $alt = '' ) {
	$headers = gv_ia_headers();
	if ( is_wp_error( $headers ) ) return $headers;

	$payload = array(
		'model'  => 'gpt-image-1',
		'prompt' => $prompt,
		'n'      => max(1, min(4, (int) $n)),
		'size'   => in_array( $size, array('auto','1024x1024','1024x1536','1536x1024'), true ) ? $size : 'auto',
	);
	if ( $transparent ) $payload['background'] = 'transparent';

	$response = gv_openai_post_json_with_backoff( 'https://api.openai.com/v1/images/generations', $headers, $payload, 6, 90 );
	if ( is_wp_error( $response ) ) return $response;

	$code = wp_remote_retrieve_response_code( $response );
	$body = wp_remote_retrieve_body( $response );
	$json = json_decode( $body, true );

	// If size invalid, retry once as 'auto'
	if ( $code >= 400 ) {
		$msg = isset( $json['error']['message'] ) ? $json['error']['message'] : '';
		if ( $msg && ( stripos( $msg, 'Invalid value' ) !== false || stripos( $msg, 'Supported values' ) !== false ) ) {
			$payload['size'] = 'auto';
			$response = gv_openai_post_json_with_backoff( 'https://api.openai.com/v1/images/generations', $headers, $payload, 6, 90 );
			if ( is_wp_error( $response ) ) return $response;
			$code = wp_remote_retrieve_response_code( $response );
			$body = wp_remote_retrieve_body( $response );
			$json = json_decode( $body, true );
		}
	}

	if ( $code < 200 || $code >= 300 ) {
		$msg = isset( $json['error']['message'] ) ? $json['error']['message'] : 'Unexpected OpenAI response.';
		return new WP_Error( 'openai_bad_response', $msg, array( 'status' => $code ) );
	}
	if ( empty( $json['data'] ) ) {
		return new WP_Error( 'no_images', 'No images returned.', array( 'status' => 422 ) );
	}

	$out = array();
	$i = 1;
	foreach ( $json['data'] as $item ) {
		$aid = null;
		$fn  = 'ai-' . sanitize_title( wp_trim_words( $prompt, 6, '' ) ) . '-' . $i . '.' . ( $format === 'jpg' ? 'jpg' : $format );

		if ( ! empty( $item['b64_json'] ) ) {
			$aid = gv_image_assistant_store_b64( $item['b64_json'], $fn, $post_id, $alt ?: $prompt );
		} elseif ( ! empty( $item['url'] ) ) {
			$aid = gv_image_assistant_store_url( $item['url'], $post_id, $alt ?: $prompt );
		}

		if ( is_wp_error( $aid ) ) {
			$out[] = array( 'error' => $aid->get_error_message() );
		} elseif ( $aid ) {
			$out[] = array(
				'id'  => $aid,
				'url' => wp_get_attachment_image_url( $aid, 'full' ),
				'alt' => $alt ?: $prompt,
			);
		}
		$i++;
	}
	if ( empty( $out ) ) {
		return new WP_Error( 'no_images_processed', 'Images were returned but none could be saved.', array( 'status' => 422 ) );
	}
	return array( 'images' => $out, 'message' => 'Images saved to Media Library.' );
}

/* ────────────────────────────────────────────────────────────────────────── */
/* Queue storage (options) + worker via WP-Cron                               */
/* ────────────────────────────────────────────────────────────────────────── */

function gv_ia_job_key( $job_id ){ return 'gv_ia_job_' . $job_id; }

function gv_ia_job_create( $args ) {
	$job_id = uniqid( 'gvia_', true );
	$data = array(
		'id'       => $job_id,
		'user_id'  => get_current_user_id(),
		'created'  => time(),
		'attempt'  => 0,
		'status'   => 'pending',
		'args'     => $args,
		'result'   => null,
		'error'    => null,
	);
	update_option( gv_ia_job_key( $job_id ), $data, false );
	return $job_id;
}

function gv_ia_job_get( $job_id ) {
	$data = get_option( gv_ia_job_key( $job_id ) );
	return is_array( $data ) ? $data : null;
}

function gv_ia_job_save( $data ) {
	if ( ! is_array( $data ) || empty( $data['id'] ) ) return false;
	return update_option( gv_ia_job_key( $data['id'] ), $data, false );
}

function gv_ia_job_finish( $job_id ) {
	// keep for 12 hours, then clean via cron or manually
	// (No explicit deletion here to allow UI to fetch results.)
	return true;
}

add_action( 'gv_ia_run_job', function( $job_id ) {
	$job = gv_ia_job_get( $job_id );
	if ( ! $job || $job['status'] !== 'pending' ) return;

	$job['attempt']++;
	$args = $job['args'];

	// Degrade request under pressure: n=1, size='auto'
	$args['n']    = 1;
	$args['size'] = in_array( (string)$args['size'], array('auto','1024x1024','1024x1536','1536x1024'), true ) ? $args['size'] : 'auto';

	$res = gv_ia_do_generate_images(
		intval( $args['post_id'] ?: 0 ),
		(string) $args['prompt'],
		(string) $args['size'],
		intval( $args['n'] ),
		(string) $args['format'],
		(bool)   $args['transparent'],
		(string) $args['alt']
	);

	if ( is_wp_error( $res ) ) {
		$code = $res->get_error_code();
		$msg  = $res->get_error_message();

		// Reschedule on rate-limit; otherwise mark error
		if ( $code === 'openai_rate_limited' || $code === 'http_request_failed' ) {
			$delay = min( 300, pow( 2, max(1,$job['attempt']) ) * 15 ); // 30s, 60s, 120s, 240s, 300s cap
			$job['status'] = 'pending';
			$job['error']  = 'Retrying automatically.';
			gv_ia_job_save( $job );
			wp_schedule_single_event( time() + $delay, 'gv_ia_run_job', array( $job_id ) );
			return;
		}

		$job['status'] = 'error';
		$job['error']  = $msg;
		gv_ia_job_save( $job );
		gv_ia_job_finish( $job_id );
		return;
	}

	$job['status'] = 'done';
	$job['result'] = $res;
	gv_ia_job_save( $job );
	gv_ia_job_finish( $job_id );
}, 10, 1 );

/* ────────────────────────────────────────────────────────────────────────── */
/* REST: /prompt, /generate (live), /queue-generate, /status                  */
/* ────────────────────────────────────────────────────────────────────────── */

add_action( 'rest_api_init', function () {

	// Auto-prompt from post
	register_rest_route( 'gv-image/v1', '/prompt', array(
		'methods'             => 'POST',
		'permission_callback' => function( WP_REST_Request $req ) {
			$post_id = intval( $req->get_param( 'post_id' ) );
			return $post_id > 0 ? current_user_can( 'edit_post', $post_id ) : current_user_can( 'edit_posts' );
		},
		'args' => array(
			'post_id' => array( 'type' => 'integer', 'required' => false ),
			'title'   => array( 'type' => 'string',  'required' => false ),
			'content' => array( 'type' => 'string',  'required' => false ),
			'purpose' => array( 'type' => 'string',  'required' => false, 'enum' => array( 'featured', 'inline' ) ),
			'language'=> array( 'type' => 'string',  'required' => false, 'enum' => array( 'en','nl' ) ),
		),
		'callback' => function( WP_REST_Request $req ) {
			$api_key = get_option( 'gv_openai_api_key', '' );
			if ( empty( $api_key ) ) {
				return new WP_Error( 'no_api_key', 'OpenAI API key is not configured (Settings → OpenAI Image Assistant).', array( 'status' => 400 ) );
			}

			$post_id = intval( $req->get_param( 'post_id' ) ?: 0 );
			$title   = wp_strip_all_tags( (string) $req->get_param( 'title' ) );
			$content = wp_kses_post( (string) $req->get_param( 'content' ) );
			$purpose = (string) ( $req->get_param( 'purpose' ) ?: 'featured' );
			$lang    = (string) ( $req->get_param( 'language' ) ?: 'en' );

			if ( $post_id > 0 && ( $title === '' || trim( $content ) === '' ) ) {
				$p = get_post( $post_id );
				if ( $p ) {
					if ( $title === '' ) { $title = wp_strip_all_tags( $p->post_title ); }
					if ( trim( $content ) === '' ) { $content = $p->post_content !== '' ? $p->post_content : $p->post_excerpt; }
				}
			}
			$content = wp_strip_all_tags( (string) $content );
			if ( strlen( $content ) > 4000 ) { $content = mb_substr( $content, 0, 4000 ); }

			$body = array(
				'model'       => 'gpt-4o-mini',
				'messages'    => array(
					array( 'role' => 'system', 'content' => 'You craft concise, descriptive, brand-safe image prompts suitable for blog featured images and inline illustrations.' ),
					array( 'role' => 'user',   'content' => gv_ia_generate_prompt_text( $title, $content, $purpose, $lang ) ),
				),
				'temperature' => 0.4,
			);

			$headers = gv_ia_headers();
			if ( is_wp_error( $headers ) ) return new WP_Error( 'no_api_key', $headers->get_error_message(), array( 'status' => 400 ) );

			$response = gv_openai_post_json_with_backoff( 'https://api.openai.com/v1/chat/completions', $headers, $body, 6, 60 );
			if ( is_wp_error( $response ) ) {
				$code = $response->get_error_code() === 'openai_rate_limited' ? 429 : 502;
				return new WP_Error( $response->get_error_code(), $response->get_error_message(), array( 'status' => $code ) );
			}

			$code = wp_remote_retrieve_response_code( $response );
			$json = json_decode( wp_remote_retrieve_body( $response ), true );
			$text = isset( $json['choices'][0]['message']['content'] ) ? trim( $json['choices'][0]['message']['content'] ) : '';

			if ( $code < 200 || $code >= 300 || $text === '' ) {
				$msg = isset( $json['error']['message'] ) ? $json['error']['message'] : 'Unexpected OpenAI response.';
				return new WP_Error( 'openai_bad_response', $msg, array( 'status' => max( $code, 502 ) ) );
			}
			$text = trim( preg_replace( '/^```(?:[a-z]+)?\s*|\s*```$/', '', $text ) );
			return array( 'prompt' => $text );
		}
	) );

	// Live generate (tries now; if 429 bubbles, UI will auto-queue)
	register_rest_route( 'gv-image/v1', '/generate', array(
		'methods'             => 'POST',
		'permission_callback' => function( WP_REST_Request $req ) {
			$post_id = intval( $req->get_param( 'post_id' ) );
			return $post_id > 0 ? current_user_can( 'edit_post', $post_id ) : current_user_can( 'upload_files' );
		},
		'args' => array(
			'post_id'    => array( 'type' => 'integer', 'required' => false ),
			'prompt'     => array( 'type' => 'string',  'required' => true ),
			'size'       => array( 'type' => 'string',  'required' => false, 'enum' => array( '1024x1024', '1024x1536', '1536x1024', 'auto' ) ),
			'n'          => array( 'type' => 'integer', 'required' => false ),
			'format'     => array( 'type' => 'string',  'required' => false, 'enum' => array( 'png', 'webp', 'jpg' ) ),
			'transparent'=> array( 'type' => 'boolean', 'required' => false ),
			'alt'        => array( 'type' => 'string',  'required' => false ),
		),
		'callback' => function( WP_REST_Request $req ) {
			$prompt = trim( (string) $req->get_param( 'prompt' ) );
			if ( $prompt === '' ) return new WP_Error( 'no_prompt', 'Enter or generate a prompt first.', array( 'status' => 400 ) );
			$post_id     = intval( $req->get_param( 'post_id' ) ?: 0 );
			$size        = (string) ( $req->get_param( 'size' ) ?: 'auto' );
			$n           = max( 1, min( 4, intval( $req->get_param( 'n' ) ?: 1 ) ) );
			$format      = (string) ( $req->get_param( 'format' ) ?: 'png' );
			$transparent = (bool) $req->get_param( 'transparent' );
			$alt         = trim( (string) ( $req->get_param( 'alt' ) ?: $prompt ) );

			$res = gv_ia_do_generate_images( $post_id, $prompt, $size, $n, $format, $transparent, $alt );
			if ( is_wp_error( $res ) ) {
				$code = $res->get_error_code() === 'openai_rate_limited' ? 429 : 502;
				return new WP_Error( $res->get_error_code(), $res->get_error_message(), array( 'status' => $code ) );
			}
			return $res;
		}
	) );

	// Queue generate (used when live returns 429)
	register_rest_route( 'gv-image/v1', '/queue-generate', array(
		'methods'             => 'POST',
		'permission_callback' => function( WP_REST_Request $req ) {
			$post_id = intval( $req->get_param( 'post_id' ) );
			return $post_id > 0 ? current_user_can( 'edit_post', $post_id ) : current_user_can( 'upload_files' );
		},
		'args' => array(
			'post_id'    => array( 'type' => 'integer', 'required' => false ),
			'prompt'     => array( 'type' => 'string',  'required' => true ),
			'size'       => array( 'type' => 'string',  'required' => false ),
			'n'          => array( 'type' => 'integer', 'required' => false ),
			'format'     => array( 'type' => 'string',  'required' => false ),
			'transparent'=> array( 'type' => 'boolean', 'required' => false ),
			'alt'        => array( 'type' => 'string',  'required' => false ),
		),
		'callback' => function( WP_REST_Request $req ) {
			$prompt = trim( (string) $req->get_param( 'prompt' ) );
			if ( $prompt === '' ) return new WP_Error( 'no_prompt', 'Enter or generate a prompt first.', array( 'status' => 400 ) );

			$args = array(
				'post_id'     => intval( $req->get_param( 'post_id' ) ?: 0 ),
				'prompt'      => $prompt,
				'size'        => (string) ( $req->get_param( 'size' ) ?: 'auto' ),
				'n'           => max( 1, min( 4, intval( $req->get_param( 'n' ) ?: 1 ) ) ),
				'format'      => (string) ( $req->get_param( 'format' ) ?: 'png' ),
				'transparent' => (bool) $req->get_param( 'transparent' ),
				'alt'         => trim( (string) ( $req->get_param( 'alt' ) ?: $prompt ) ),
			);

			$job_id = gv_ia_job_create( $args );
			// schedule first attempt soon
			wp_schedule_single_event( time() + 30, 'gv_ia_run_job', array( $job_id ) );

			return array( 'job_id' => $job_id, 'status' => 'pending' );
		}
	) );

	// Status polling
	register_rest_route( 'gv-image/v1', '/status', array(
		'methods'             => 'GET',
		'permission_callback' => function( WP_REST_Request $req ) {
			$job_id = (string) $req->get_param( 'job_id' );
			$job = gv_ia_job_get( $job_id );
			if ( ! $job ) return current_user_can( 'upload_files' ); // fallback
			if ( $job['user_id'] && get_current_user_id() === (int) $job['user_id'] ) return true;
			return current_user_can( 'upload_files' );
		},
		'args' => array(
			'job_id' => array( 'type' => 'string', 'required' => true ),
		),
		'callback' => function( WP_REST_Request $req ) {
			$job_id = (string) $req->get_param( 'job_id' );
			$job = gv_ia_job_get( $job_id );
			if ( ! $job ) return new WP_Error( 'not_found', 'Job not found.', array( 'status' => 404 ) );
			$out = array(
				'job_id' => $job['id'],
				'status' => $job['status'],
				'attempt'=> $job['attempt'],
			);
			if ( $job['status'] === 'done' ) $out['result'] = $job['result'];
			if ( $job['status'] === 'error' ) $out['error']  = $job['error'];
			return $out;
		}
	) );
} );

/* ────────────────────────────────────────────────────────────────────────── */
/* Gutenberg Panel (adds auto-queue on 429)                                   */
/* ────────────────────────────────────────────────────────────────────────── */

add_action( 'enqueue_block_editor_assets', function () {
	$nonce = wp_create_nonce( 'wp_rest' );
	$data  = array(
		'restGenerate' => esc_url_raw( rest_url( 'gv-image/v1/generate' ) ),
		'restQueue'    => esc_url_raw( rest_url( 'gv-image/v1/queue-generate' ) ),
		'restStatus'   => esc_url_raw( rest_url( 'gv-image/v1/status' ) ),
		'restPrompt'   => esc_url_raw( rest_url( 'gv-image/v1/prompt' ) ),
		'nonce'        => $nonce,
	);

	wp_register_script(
		'gv-image-assistant',
		'',
		array( 'wp-plugins', 'wp-edit-post', 'wp-components', 'wp-element', 'wp-data', 'wp-api-fetch', 'wp-blocks', 'wp-block-editor' ),
		'1.5.0',
		true
	);
	wp_enqueue_script( 'gv-image-assistant' );
	wp_add_inline_script( 'gv-image-assistant', 'window.GV_IMAGE_ASSISTANT=' . wp_json_encode( $data ) . ';' );

	// NOWDOC: keep `${...}` intact for JS template strings
	wp_add_inline_script( 'gv-image-assistant', <<<'JS'
(function(wp){
	const { registerPlugin } = wp.plugins || {};
	const { PluginDocumentSettingPanel } = wp.editPost || {};
	const { Button, Notice, Spinner, TextareaControl, TextControl, ToggleControl, SelectControl, TabPanel } = wp.components || {};
	const { useState, useEffect, useRef } = wp.element || {};
	const { select } = wp.data || {};
	const apiFetch = wp.apiFetch || window.wp.apiFetch;

	if (!registerPlugin || !PluginDocumentSettingPanel || !apiFetch) return;

	const ImageCard = ({img, onInsert, onFeature}) => wp.element.createElement('div',
		{ style:{border:'1px solid #ddd', borderRadius:8, padding:8, display:'flex', flexDirection:'column', gap:6, alignItems:'center'} },
		wp.element.createElement('img', { src: img.url, alt: img.alt || '', style:{maxWidth:'100%', height:120, objectFit:'cover', borderRadius:6} }),
		wp.element.createElement('div', { style:{display:'flex', gap:6} },
			wp.element.createElement(Button, { onClick: ()=>onInsert(img) }, 'Insert'),
			onFeature && wp.element.createElement(Button, { variant:'secondary', onClick: ()=>onFeature(img) }, 'Set featured')
		)
	);

	const Panel = () => {
		const [tab, setTab] = useState('generate');
		const [prompt, setPrompt] = useState('');
		const [size, setSize] = useState('auto');
		const [count, setCount] = useState('1');
		const [fmt, setFmt] = useState('png');
		const [transparent, setTransparent] = useState(false);
		const [busy, setBusy] = useState(false);
		const [msg, setMsg] = useState('');
		const [gen, setGen] = useState([]);
		const [q, setQ] = useState('');
		const [lib, setLib] = useState([]);
		const [libBusy, setLibBusy] = useState(false);
		const [queueId, setQueueId] = useState(null);

		const pollRef = useRef(null);
		const postId = select('core/editor').getCurrentPostId() || 0;

		const insertAsBlock = (img) => {
			const block = wp.blocks.createBlock('core/image', { id: img.id, url: img.url, alt: img.alt || '' });
			const beDispatch = wp.data.dispatch('core/block-editor');
			const index = wp.data.select('core/block-editor').getBlockCount();
			beDispatch.insertBlocks([block], index);
			setMsg('Image inserted.');
		};

		const setFeatured = async (img) => {
			try {
				await apiFetch({ path: `/wp/v2/posts/${postId}`, method: 'POST', data: { featured_media: img.id } });
				setMsg('Featured image set.');
			} catch(e) {
				setMsg('Failed: ' + (e && e.message ? e.message : 'Could not set featured image'));
			}
		};

		const autoPrompt = async () => {
			if (busy) return;
			setBusy(true); setMsg('');
			try {
				const title   = select('core/editor').getEditedPostAttribute('title') || '';
				const content = select('core/editor').getEditedPostAttribute('content') || '';
				const res = await apiFetch({
					url: window.GV_IMAGE_ASSISTANT.restPrompt,
					method: 'POST',
					headers: { 'X-WP-Nonce': window.GV_IMAGE_ASSISTANT.nonce, 'Content-Type': 'application/json' },
					body: JSON.stringify({ post_id: postId, title, content, purpose: 'featured' })
				});
				if (res.prompt) { setPrompt(res.prompt); setMsg('Prompt generated from post.'); }
				else { setMsg('No prompt returned.'); }
			} catch(e) {
				setMsg('Failed: ' + (e && e.message ? e.message : 'Request error'));
			} finally {
				setBusy(false);
			}
		};

		const startPolling = (jid) => {
			if (pollRef.current) clearInterval(pollRef.current);
			pollRef.current = setInterval(async ()=>{
				try{
					const s = await apiFetch({ url: window.GV_IMAGE_ASSISTANT.restStatus + `?job_id=${encodeURIComponent(jid)}`, method:'GET', headers: { 'X-WP-Nonce': window.GV_IMAGE_ASSISTANT.nonce }});
					if (s.status === 'done' && s.result && s.result.images) {
						setGen(s.result.images);
						setMsg('Images saved to Media Library (queued).');
						clearInterval(pollRef.current); pollRef.current = null; setQueueId(null);
					} else if (s.status === 'error') {
						setMsg('Queued job failed: ' + (s.error || 'Unknown error'));
						clearInterval(pollRef.current); pollRef.current = null; setQueueId(null);
					}
				}catch(e){
					// keep polling silently
				}
			}, 10000);
		};

		useEffect(()=>()=>{ if (pollRef.current) clearInterval(pollRef.current); },[]);

		const smartGenerate = async () => {
			if (busy) return;
			setBusy(true); setMsg(''); setGen([]);
			try {
				let p = prompt;
				if (!p.trim()) {
					const title   = select('core/editor').getEditedPostAttribute('title') || '';
					const content = select('core/editor').getEditedPostAttribute('content') || '';
					const pre = await apiFetch({
						url: window.GV_IMAGE_ASSISTANT.restPrompt,
						method: 'POST',
						headers: { 'X-WP-Nonce': window.GV_IMAGE_ASSISTANT.nonce, 'Content-Type': 'application/json' },
						body: JSON.stringify({ post_id: postId, title, content, purpose: 'featured' })
					});
					p = (pre && pre.prompt) ? pre.prompt : '';
					if (!p.trim()) { setMsg('Could not auto-generate a prompt.'); setBusy(false); return; }
					setPrompt(p);
				}

				try{
					const res = await apiFetch({
						url: window.GV_IMAGE_ASSISTANT.restGenerate,
						method: 'POST',
						headers: { 'X-WP-Nonce': window.GV_IMAGE_ASSISTANT.nonce, 'Content-Type':'application/json' },
						body: JSON.stringify({ post_id: postId, prompt: p, size, n: parseInt(count,10) || 1, format: fmt, transparent })
					});
					setGen(res.images || []);
					setMsg(res.message || 'Done.');
				}catch(e){
					const m = (e && e.message) ? e.message.toLowerCase() : '';
					if (m.indexOf('rate limit') !== -1 || m.indexOf('429') !== -1) {
						// Fallback to queue
						const qres = await apiFetch({
							url: window.GV_IMAGE_ASSISTANT.restQueue,
							method: 'POST',
							headers: { 'X-WP-Nonce': window.GV_IMAGE_ASSISTANT.nonce, 'Content-Type':'application/json' },
							body: JSON.stringify({ post_id: postId, prompt: p, size, n: parseInt(count,10) || 1, format: fmt, transparent })
						});
						if (qres && qres.job_id){
							setQueueId(qres.job_id);
							setMsg('Rate limited—job queued. Results will appear here.');
							startPolling(qres.job_id);
						} else {
							setMsg('Failed to queue job.');
						}
					} else {
						setMsg('Failed: ' + (e && e.message ? e.message : 'Request error'));
					}
				}
			} finally {
				setBusy(false);
			}
		};

		const searchLib = async () => {
			setLibBusy(true); setLib([]);
			try {
				const items = await apiFetch({ path: `/wp/v2/media?media_type=image&per_page=24&search=${encodeURIComponent(q)}` });
				const mapped = (items||[]).map(m => ({
					id: m.id,
					url: (m.media_details && m.media_details.sizes && m.media_details.sizes.medium && m.media_details.sizes.medium.source_url) ? m.media_details.sizes.medium.source_url : m.source_url,
					alt: (m.alt_text || (m.title && m.title.rendered) || '')
				}));
				setLib(mapped);
			} catch(e) {
				setMsg('Library search failed: ' + (e && e.message ? e.message : 'Error'));
			} finally {
				setLibBusy(false);
			}
		};

		const tabs = [
			{ name:'generate', title:'Generate (AI)', className:'tab-generate' },
			{ name:'library',  title:'Library', className:'tab-library' },
		];

		return wp.element.createElement(
			PluginDocumentSettingPanel,
			{ name:'gv-image-assistant', title:'Image Assistant (AI + Library)', className:'gv-image-assistant' },
			wp.element.createElement(TabPanel, { className: 'gv-tabs', activeClass: 'active-tab', onSelect: setTab, tabs }, (t) => {
				if (t.name === 'generate') {
					return wp.element.createElement('div', { style:{ display:'grid', gap:8 } },
						wp.element.createElement(TextareaControl, { label: 'Prompt', help: 'Auto-generate from your post or edit manually.', value: prompt, onChange: setPrompt, rows: 4 }),
						wp.element.createElement('div', { style:{display:'flex', gap:8} },
							wp.element.createElement(Button, { onClick: autoPrompt, disabled: busy }, busy ? wp.element.createElement(Spinner) : 'Auto prompt from post'),
							wp.element.createElement(Button, { variant:'primary', onClick: smartGenerate, disabled: busy }, busy ? wp.element.createElement(Spinner) : (queueId ? 'Generating (queued)…' : 'Generate & Save'))
						),
						wp.element.createElement('div', { style:{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:8, marginTop:6 } },
							wp.element.createElement(SelectControl, {
								label: 'Size', value: size,
								options: [
									{label:'Auto (API chooses)', value:'auto'},
									{label:'Square — 1024 × 1024', value:'1024x1024'},
									{label:'Portrait — 1024 × 1536', value:'1024x1536'},
									{label:'Landscape — 1536 × 1024', value:'1536x1024'},
								],
								onChange: setSize
							}),
							wp.element.createElement(SelectControl, { label: 'Count', value: count, options: [{label:'1', value:'1'},{label:'2', value:'2'},{label:'3', value:'3'},{label:'4', value:'4'}], onChange: setCount })
						),
						wp.element.createElement('div', { style:{ display:'grid', gridTemplateColumns:'1fr 1fr', gap:8 } },
							wp.element.createElement(SelectControl, { label: 'File format (saved)', value: fmt, options: [{label:'PNG', value:'png'},{label:'WEBP', value:'webp'},{label:'JPG', value:'jpg'}], onChange: setFmt }),
							wp.element.createElement(ToggleControl, { label: 'Transparent background (PNG only)', checked: transparent, onChange: setTransparent })
						),
						(gen && gen.length>0) && wp.element.createElement('div', { style:{ display:'grid', gridTemplateColumns:'repeat(2,1fr)', gap:8, marginTop:8 } },
							gen.map((img, i) => wp.element.createElement(ImageCard, { key: 'g'+i, img, onInsert: insertAsBlock, onFeature: postId ? setFeatured : null }))
						)
					);
				}
				return wp.element.createElement('div', { style:{ display:'grid', gap:8 } },
					wp.element.createElement(TextControl, { label: 'Search Media Library', value: q, onChange: setQ, placeholder: 'e.g. product, team, event...' }),
					wp.element.createElement(Button, { onClick: searchLib, disabled: libBusy }, libBusy ? wp.element.createElement(Spinner) : 'Search'),
					(lib && lib.length>0) && wp.element.createElement('div', { style:{ display:'grid', gridTemplateColumns:'repeat(3,1fr)', gap:8, marginTop:8 } },
						lib.map((img, i) => wp.element.createElement(ImageCard, { key:'m'+i, img, onInsert: insertAsBlock, onFeature: postId ? setFeatured : null }))
					)
				);
			}),
			msg && wp.element.createElement(Notice, { status:'info', isDismissible:true }, msg)
		);
	};

	registerPlugin('gv-image-assistant', { render: Panel, icon: 'format-image' });
})(window.wp);
JS
	);
} );

/* ────────────────────────────────────────────────────────────────────────── */
/* Classic Editor meta box (auto-queue on 429)                                */
/* ────────────────────────────────────────────────────────────────────────── */

add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'gv_image_assistant_box',
		'Image Assistant (AI + Library)',
		function( WP_Post $post ) {
			$nonce = wp_create_nonce( 'wp_rest' );
			$genEP = esc_url( rest_url( 'gv-image/v1/generate' ) );
			$qEP   = esc_url( rest_url( 'gv-image/v1/queue-generate' ) );
			$sEP   = esc_url( rest_url( 'gv-image/v1/status' ) );
			$prmEP = esc_url( rest_url( 'gv-image/v1/prompt' ) );
			?>
			<p><em>Auto-generate a prompt from your post, then create images.</em></p>
			<p>
				<label for="gv_img_prompt"><strong>Prompt</strong></label><br/>
				<textarea id="gv_img_prompt" style="width:100%;min-height:80px;" placeholder="Describe the image..."></textarea>
			</p>
			<p style="display:flex;gap:8px;">
				<button type="button" class="button" id="gv_img_autoprompt">Auto prompt from post</button> <br /> <br />
				<button type="button" class="button button-primary" id="gv_img_generate">Generate & Save</button>
				<span id="gv_img_busy" style="display:none;margin-left:8px;">Working…</span>
			</p>
			<p style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
				<span>
					<label for="gv_img_size"><strong>Size</strong></label><br/>
					<select id="gv_img_size">
						<option value="auto">Auto (API chooses)</option>
						<option value="1024x1024">Square — 1024×1024</option>
						<option value="1024x1536">Portrait — 1024×1536</option>
						<option value="1536x1024">Landscape — 1536×1024</option>
					</select>
				</span>
				<span>
					<label for="gv_img_count"><strong>Count</strong></label><br/>
					<select id="gv_img_count">
						<option>1</option><option>2</option><option>3</option><option>4</option>
					</select>
				</span>
			</p>
			<p style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
				<span>
					<label for="gv_img_fmt"><strong>Format</strong></label><br/>
					<select id="gv_img_fmt">
						<option value="png">PNG</option>
						<option value="webp">WEBP</option>
						<option value="jpg">JPG</option>
					</select>
				</span>
				<span>
					<label><input id="gv_img_trans" type="checkbox"> Transparent background</label>
				</span>
			</p>
			<div id="gv_img_results" style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;"></div>
			<script>
			(function(){
				const postId = <?php echo (int) $post->ID; ?>;
				const nonce  = '<?php echo esc_js( $nonce ); ?>';
				const genEP  = '<?php echo $genEP; ?>';
				const qEP    = '<?php echo $qEP; ?>';
				const sEP    = '<?php echo $sEP; ?>';
				const prmEP  = '<?php echo $prmEP; ?>';
				const $btnGen   = document.getElementById('gv_img_generate');
				const $btnAuto  = document.getElementById('gv_img_autoprompt');
				const $busy  = document.getElementById('gv_img_busy');
				const $out   = document.getElementById('gv_img_results');
				const $prompt= document.getElementById('gv_img_prompt');

				let pollTimer = null;

				function insertHTMLAtCursor(html){
					if (window.tinyMCE && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()){
						tinyMCE.activeEditor.execCommand('mceInsertContent', false, html);
						tinyMCE.triggerSave();
						tinyMCE.activeEditor.fire('change');
					} else {
						const el = document.getElementById('content');
						if (el){
							const cur = el.value || '';
							el.value = cur + "\n\n" + html;
							el.dispatchEvent(new Event('input', {bubbles:true}));
							el.dispatchEvent(new Event('change', {bubbles:true}));
						}
					}
				}

				function card(img){
					const d = document.createElement('div');
					d.style.border='1px solid #ddd';
					d.style.borderRadius='6px';
					d.style.padding='6px';
					d.style.textAlign='center';
					const i = document.createElement('img');
					i.src = img.url; i.alt = img.alt || '';
					i.style.maxWidth='100%'; i.style.height='100px'; i.style.objectFit='cover'; i.style.borderRadius='4px';
					const row = document.createElement('div'); row.style.marginTop='6px';
					const ins = document.createElement('button'); ins.className='button'; ins.textContent='Insert';
					ins.onclick = function(){
						insertHTMLAtCursor('<figure class="wp-block-image"><img src="'+img.url.replace(/"/g,'&quot;')+'" alt="'+(img.alt||'').replace(/"/g,'&quot;')+'"/></figure>');
					};
					row.appendChild(ins);
					d.appendChild(i); d.appendChild(row);
					return d;
				}

				function startPolling(jobId){
					if (pollTimer) clearInterval(pollTimer);
					pollTimer = setInterval(async function(){
						try{
							const res = await fetch(sEP + '?job_id=' + encodeURIComponent(jobId), { headers: { 'X-WP-Nonce': nonce }});
							const data = await res.json();
							if (data && data.status === 'done' && data.result && data.result.images){
								(out => { out.innerHTML=''; data.result.images.forEach(img => out.appendChild(card(img))); })($out);
								alert('Images saved (queued).');
								clearInterval(pollTimer); pollTimer = null;
							} else if (data && data.status === 'error'){
								alert('Queued job failed: ' + (data.error || 'Unknown error'));
								clearInterval(pollTimer); pollTimer = null;
							}
						}catch(e){}
					}, 10000);
				}

				async function autoPrompt(){
					$busy.style.display='inline';
					try{
						const titleEl = document.getElementById('title');
						const title   = titleEl ? titleEl.value : '';
						const content = (typeof tinyMCE!=='undefined' && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden())
							? tinyMCE.activeEditor.getContent({format:'text'})
							: (document.getElementById('content') ? document.getElementById('content').value : '');
						const res = await fetch(prmEP, {
							method:'POST',
							headers:{'Content-Type':'application/json','X-WP-Nonce':nonce},
							body: JSON.stringify({ post_id: postId, title, content, purpose: 'featured' })
						});
						const data = await res.json();
						if(!res.ok) throw new Error(data && data.message ? data.message : ('HTTP '+res.status));
						$prompt.value = data.prompt || '';
					}catch(e){
						alert('Failed: ' + (e.message||e));
					}finally{
						$busy.style.display='none';
					}
				}

				async function smartGenerate(){
					if ($busy.style.display === 'inline') return;
					$busy.style.display='inline';
					$out.innerHTML = '';
					try{
						let p = ($prompt.value || '').trim();
						if(!p){
							await autoPrompt();
							p = ($prompt.value || '').trim();
							if(!p){ throw new Error('Could not auto-generate a prompt'); }
						}
						const size   = document.getElementById('gv_img_size').value;
						const count  = document.getElementById('gv_img_count').value;
						const fmt    = document.getElementById('gv_img_fmt').value;
						const trans  = document.getElementById('gv_img_trans').checked;

						try{
							const res = await fetch(genEP, {
								method:'POST',
								headers:{'Content-Type':'application/json','X-WP-Nonce':nonce},
								body: JSON.stringify({ post_id: postId, prompt: p, size, n: parseInt(count,10)||1, format: fmt, transparent: !!trans })
							});
							const data = await res.json();
							if(!res.ok) throw new Error(data && data.message ? data.message : ('HTTP '+res.status));
							(data.images||[]).forEach(img => $out.appendChild(card(img)));
						}catch(e){
							const m = (e && e.message) ? e.message.toLowerCase() : '';
							if (m.indexOf('rate limit') !== -1 || m.indexOf('429') !== -1){
								const res2 = await fetch(qEP, {
									method:'POST',
									headers:{'Content-Type':'application/json','X-WP-Nonce':nonce},
									body: JSON.stringify({ post_id: postId, prompt: p, size, n: parseInt(count,10)||1, format: fmt, transparent: !!trans })
								});
								const data2 = await res2.json();
								if (!res2.ok || !data2.job_id) throw new Error((data2 && data2.message) ? data2.message : 'Could not queue job');
								alert('Rate limited—job queued. Results will appear automatically.');
								startPolling(data2.job_id);
							} else {
								throw e;
							}
						}
					}catch(e){
						alert('Failed: ' + (e.message || e));
					}finally{
						$busy.style.display='none';
					}
				}

				$btnAuto.addEventListener('click', autoPrompt);
				$btnGen.addEventListener('click', smartGenerate);
			})();
			</script>
			<?php
		},
		'post',
		'side',
		'high'
	);
} );
