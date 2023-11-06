<?php

namespace WordpressLiveblog;

/**
 * Class Live
 *
 * Initializes the liveblog application on the front-end.
 *
 * @package WordpressLiveblog
 */
class Live {
  public function __construct() {
    add_action('init', [$this, 'wordpress_liveblog_shortcodes_init']);
  }

  /**
   * Registers the [wordpress_liveblog] shortcode.
   */
  public function wordpress_liveblog_shortcodes_init() {
    add_shortcode('wordpress_liveblog', [$this, 'wordpress_liveblog_shortcode']);
  }

  /**
   * Renders the liveblog based on the provided attributes.
   *
   * @param array $atts Shortcode attributes.
   * @param null|string $content Shortcode content. Not used.
   * @param string $tag Shortcode tag. Not used.
   * @return string Rendered liveblog or empty string if channel not found.
   */
  public function wordpress_liveblog_shortcode($atts = [], $content = null, $tag = '' ) {
    if (!isset($atts['liveblog_id'])) {
      return '';
    }

    $channel = FrontCore::$channels->get_channel(['id' => $atts['liveblog_id']]);

    if (!$channel) {
      return '';
    }

    list($use_websockets, $ws_url) = $this->configure_websockets($channel);

    $messages_url = get_site_url() . "?action=wordpress_liveblog_get_channel_messages&liveblog_id={$channel->uuid}";
    $post_message_url = get_site_url() . "?action=wordpress_liveblog_events";

    $liveblog = Templates::load_template('liveblog', [
      'ws_url' => $ws_url,
      'messages_url' => $messages_url,
      'post_message_url' => $post_message_url,
      'channel' => $channel,
      'use_websockets' => $use_websockets
    ], true);

    return $liveblog;
  }

  /**
   * Configures websockets for real-time updates.
   *
   * @param object $channel Channel object.
   * @return array Configuration array with websocket usage status and URL.
   */
  private function configure_websockets($channel) {
    $use_websockets = 'false';
    $ws_url = null;

    if (@$_ENV['WORDPRESS_LIVEBLOG_USE_WEBSOCKETS'] === 'true') {
      $use_websockets = 'true';
      $ws_url = $_ENV['WORDPRESS_LIVEBLOG_WS_SERVER_CLIENT_URL'] . "?liveblog_id={$channel->uuid}";
    }

    return [$use_websockets, $ws_url];
  }
}
