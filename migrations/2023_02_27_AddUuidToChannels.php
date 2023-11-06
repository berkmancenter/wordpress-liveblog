<?php

use DeliciousBrains\WPMigrations\Database\AbstractMigration;
use \WordpressLiveblog\Db;
use \WordpressLiveblog\Helpers;

class AddUuidToChannels extends AbstractMigration {
  public function run() {
    global $wpdb;

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}wordpress_liveblog_channels
      ADD COLUMN
        uuid VARCHAR(36) NOT NULL;
    ";
    $wpdb->query($sql);

    $exisiting_channels = Db::i()->get_rows('channels');
    foreach ($exisiting_channels as $channel) {
      Db::i()->update_row('channels', ['uuid' => Helpers::get_uuid()], ['id' => $channel->id]);
    }
  }

  public function rollback() {
    global $wpdb;
    $wpdb->query("
      ALTER TABLE
        {$wpdb->prefix}wordpress_liveblog_channels
      DROP COLUMN
        uuid;
    ");
  }
}
