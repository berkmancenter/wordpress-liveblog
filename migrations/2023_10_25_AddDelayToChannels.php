<?php

use DeliciousBrains\WPMigrations\Database\AbstractMigration;
use \WordpressLiveblog\Db;

class AddDelayToChannels extends AbstractMigration {
  public function run() {
    global $wpdb;

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}wordpress_liveblog_channels
      ADD COLUMN
        delay SMALLINT NOT NULL DEFAULT 0;
    ";
    $wpdb->query($sql);

    $sql = "
      UPDATE
        {$wpdb->prefix}wordpress_liveblog_channels
      SET
        delay = 0;
    ";
    $wpdb->query($sql);
  }

  public function rollback() {
    global $wpdb;
    $wpdb->query("
      ALTER TABLE
        {$wpdb->prefix}wordpress_liveblog_channels
      DROP COLUMN
        delay;
    ");
  }
}
