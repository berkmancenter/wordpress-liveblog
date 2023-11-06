<?php

namespace WordpressLiveblog\EventConsumers;

use WordpressLiveblog\FrontCore;

class MessageMessageChanged extends Consumer {
  public function consume() {
    $event_data = $this->data['event'];

    $channel = $this->get_channel($event_data['channel_id']);
    $message_text = $this->get_message_text($event_data);

    $message = FrontCore::$channels->get_message($event_data['message_id'], 'id', ['deleted' => 0]);
    if ($message === false) {
      return false;
    }

    $message_text = $this->get_message_text($this->data['event']['message']);

    FrontCore::$channels->update_message([
      'message' => $message_text,
      'updated_at' => date('Y-m-d H:i:s')
    ], [
      'id' => $message->id,
    ]);

    $clients_message = $this->prepare_clients_message($channel, $message);

    return [
      'message_body' => $clients_message,
    ];
  }

  private function prepare_clients_message($channel, $message) {
    return [
      'action' => 'message_changed',
      'channel_id' => $channel->uuid,
      'body' => $message->message,
      'id' => $message->id,
    ];
  }

  private function get_channel($uuid) {
    return FrontCore::$channels->get_channel(['uuid' => $uuid]);
  }
}
