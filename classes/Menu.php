<?php

namespace WordpressLiveblog;

/**
 * Class Menu
 *
 * Handles admin menu related operations.
 *
 * @package WordpressLiveblog
 */
class Menu {
  public function __construct() {
    add_action('admin_menu', [$this, 'wordpress_liveblog_add_admin_menu']);
  }

  /**
   * Adds main menu and submenus to the WordPress admin dashboard.
   *
   * @return void
   */
  public function wordpress_liveblog_add_admin_menu() {
    add_menu_page('Wordpress Liveblog', 'Wordpress Liveblog', 'manage_options', 'wordpress_liveblog_channels', [AdminCore::$actions, 'wordpress_liveblog_admin_init'], plugins_url('wordpress-liveblog/resources/img/logo.png'));
    add_submenu_page('wordpress_liveblog_channels', 'Channels', 'Channels', 'manage_options', 'wordpress_liveblog_channels', [AdminCore::$actions, 'wordpress_liveblog_admin_init']);
  }
}
