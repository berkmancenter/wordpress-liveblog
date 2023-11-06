<?php

namespace WordpressLiveblog\EventConsumers;

use WordpressLiveblog\Helpers;

abstract class Consumer {
  protected array $data;
  abstract public function consume();

  public function __construct(array $data) {
    $this->data = $data;
  }

  protected function get_message_text($incoming_data) {
    $merged_text = '';
    $merged_text .= $this->decorate_message($incoming_data['body']);
    $merged_text .= $this->get_embedded_content($merged_text);

    return $merged_text;
  }

  private function decorate_message($message_text) {
    $allowed_html_tags = '<div><strong><i><s><ul><ol><li><dl><dt><dd><img><a>';
    $message_text = strip_tags($message_text, $allowed_html_tags);

    // Replace non-breaking spaces with regular spaces
    $message_text = str_replace(' ', ' ', $message_text);

    return $message_text;
  }

  private function get_embedded_content($body) {
    preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $body, $match);
    $urls = $match[0];
    $text = '';

    $embedded_text = $this->get_social_media_embedded_elements($urls);
    $inline_images = $this->get_inline_images($urls);

    if (empty($embedded_text) === false) {
      $text .= '<br>' . $embedded_text;
    }

    if (empty($inline_images) === false) {
      $text .= '<br>' . $inline_images;
    }

    return $text;
  }

  private function get_social_media_embedded_elements($urls) {
    $embedded_text = '';

    foreach ($urls as $url) {
      $embed_code = $this->get_embed_code($url, [
        'twitter' => 'https://publish.twitter.com/oembed?omit_script=true&url=',
        'mastodon' => 'https://mastodon.social/api/oembed?url=',
        'youtube' => 'https://youtube.com/oembed?url=',
      ]);

      if ($embed_code) {
        $embedded_text .= "<div class=\"wordpress-liveblog-messages-embedded-items-item\">{$embed_code}</div>";
      }
    }

    if ($embedded_text) {
      $embedded_text = "<div class=\"wordpress-liveblog-messages-embedded-items\">{$embedded_text}</div>";
    }

    return $embedded_text;
  }

  private function get_embed_code($link, $embed_endpoints) {
    $embedded_html = '';

    foreach ($embed_endpoints as $platform => $endpoint) {
      $response = wp_remote_get("{$endpoint}{$link}&maxwidth=800");
      $response_body = json_decode($response['body'], true);

      if (isset($response_body['html'])) {
        $embedded_html.= $response_body['html'];
      }
    }

    return $embedded_html;
  }

  private function get_inline_images($urls) {
    $image_urls = array_filter(array_map(function ($url) {
      $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));

      if ($this->is_image_url($extension)) {
        return $this->save_image_locally($url, $extension);
      }

      return null;
    }, $urls));

    $inline_images = $this->create_img_elements($image_urls);

    return $inline_images;
  }

  private function is_image_url($extension) {
    $image_extensions = ['jpg', 'jpeg', 'png', 'gif'];

    return in_array($extension, $image_extensions);
  }

  private function save_image_locally($url, $extension, $args = []) {
    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
      return false;
    }

    $image = wp_remote_retrieve_body($response);

    $filename_uuid = Helpers::get_uuid();
    $filename = "{$filename_uuid}.{$extension}";
    $new_file_path = WP_PLUGIN_DIR . "/wordpress-liveblog/files/{$filename}";
    file_put_contents($new_file_path, $image);

    return plugins_url("wordpress-liveblog/files/{$filename}");
  }

  private function create_img_elements($image_urls) {
    return implode(
      '',
      array_map(
        function ($image_url) {
          return '<img src="' . $image_url . '">';
        },
        $image_urls,
      )
    );
  }
}
