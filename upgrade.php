<?php

function wordpress_liveblog_upgrade($upgrader_object, $options) {
  if($options['action'] === 'update' && $options['type'] === 'plugin' && isset($options['plugins'])) {
    foreach($options['plugins'] as $plugin) {
      if ($plugin === 'wordpress-liveblog') {
        // Run db migrations
        $migrator = \DeliciousBrains\WPMigrations\Database\Migrator::instance();
        $migrator->run();

        wordpress_liveblog_upgrade_tasks();

        $plugin_data = get_plugin_data( __FILE__ );
        $plugin_version = $plugin_data['Version'];
        update_option('wordpress_liveblog_version', $plugin_version);
      }
    }
  }
}

function wordpress_liveblog_upgrade_tasks() {}
