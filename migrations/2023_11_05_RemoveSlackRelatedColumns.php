<?php

use DeliciousBrains\WPMigrations\Database\AbstractMigration;

class RemoveSlackRelatedColumns extends AbstractMigration {
  public function run() {
    global $wpdb;

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}wordpress_liveblog_authors
      DROP COLUMN
        slack_id;
    ";
    $wpdb->query($sql);

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}wordpress_liveblog_channel_messages
      DROP COLUMN
        slack_id;
    ";
    $wpdb->query($sql);

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}wordpress_liveblog_channel_messages
      DROP COLUMN
        remote_created_at;
    ";
    $wpdb->query($sql);

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}wordpress_liveblog_channels
      DROP COLUMN
        slack_id;
    ";
    $wpdb->query($sql);

    $sql = "
      ALTER TABLE
        {$wpdb->prefix}wordpress_liveblog_channels
      DROP COLUMN
        owner_id;
    ";
    $wpdb->query($sql);

    $sql = "
      DROP TABLE
        {$wpdb->prefix}wordpress_liveblog_workspaces;
    ";
    $wpdb->query($sql);
  }
}
