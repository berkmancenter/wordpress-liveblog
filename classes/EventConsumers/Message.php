<?php

namespace WordpressLiveblog\EventConsumers;

use WordpressLiveblog\FrontCore;

class Message extends Consumer {
  /**
   * Consumes a message and returns a formatted message body.
   *
   * @return array
   */
  public function consume() {
    $event_data = $this->data['event'];

    $channel = $this->get_channel($event_data['channel_id']);
    $author = $this->get_author($event_data['author']);

    $message = $this->create_message($channel, $author, $event_data);
    $clients_message = $this->prepare_clients_message($channel, $message, $author);

    return [
      'message_body' => $clients_message,
    ];
  }

  private function get_channel($uuid) {
    return FrontCore::$channels->get_channel(['uuid' => $uuid]);
  }

  private function get_author($name) {
    return FrontCore::$channels->get_or_create_author_by_name($name);
  }

  private function create_message($channel, $author, $event_data) {
    $message_data = [
      'channel_id' => $channel->id,
      'message' => $this->get_message_text($event_data),
      'author_id' => $author->id,
    ];

    $delay = intval($channel->delay);
    if ($delay && $delay > 0) {
      $message_data['published'] = false;
      $message_data['publish_at'] = $this->format_unix_time($event_data['ts'] + $delay);
    }
    
    return FrontCore::$channels->create_message($message_data);
  }

  private function format_unix_time($unix_timestamp) {
    return "SQL_FUNC:DATE_FORMAT(FROM_UNIXTIME({$unix_timestamp}), '%Y-%m-%d %H:%i:%s.%f')";
  }

  private function prepare_clients_message($channel, $message, $author) {
    return [
      'action' => 'message_new',
      'channel_id' => $channel->uuid,
      'body' => $message->message,
      'author' => $author->name,
      'created_at' => $message->created_at,
      'id' => $message->id,
    ];
  }
}
