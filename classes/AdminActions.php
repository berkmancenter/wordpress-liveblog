<?php

namespace WordpressLiveblog;

/**
 * Class AdminActions
 *
 * Handles admin related actions for the plugin.
 *
 * @package WordpressLiveblog
 */
class AdminActions {
  /** @var \wpdb|null Instance of the WordPress database abstraction class. */
  private $db;

  public function __construct() {
    $this->db = Db::i();
  }

  /**
   * Initializes admin actions and views based on request parameters.
   *
   * @return void
   */
  public function wordpress_liveblog_admin_init() {
    switch (@$_REQUEST['action']) {
      case 'save-access-token':
        $this->save_access_token();
        break;
    }

    switch (@$_REQUEST['page']) {
      case 'wordpress_liveblog_settings':
        $this->settings_view();
        break;
      case 'wordpress_liveblog_channels':
        $this->channels_view();
        break;
    }
  }

  /**
   * Initializes ajax admin actions based on request parameters.
   *
   * @return void
   */
  public function wordpress_liveblog_ajax_actions() {
    $response = [];
    $sub_action = $_POST['sub_action'];

    switch ($sub_action) {
      case 'channel-new':
        $response = $this->create_new_channel();
        break;
      case 'channel-toggle':
        $response = $this->toggle_channel();
        break;
      case 'update-messages-sorting':
        $response = $this->update_message_sorting();
        break;
      case 'update-refresh-interval':
        $response = $this->update_refresh_interval();
        break;
      case 'update-delay':
        $response = $this->update_delay();
        break;
      case 'channels-list':
        $response = $this->channels_list();
        break;
    }

    $this->send_json_response($response);
  }

  /**
   * Sends a JSON response.
   *
   * @param array $response Response data.
   */
  private function send_json_response($response) {
    header('Content-Type: application/json');

    if (isset($response['error'])) {
      http_response_code(400);
    }

    echo json_encode($response);
    die();
  }

  /**
   * Displays the channels view.
   *
   * @return void
   */
  private function channels_view() {
    $settings_url = admin_url('admin.php?page=wordpress_liveblog_settings');

    $channels_list = Templates::load_template('channels_list', [
      'channels' => AdminCore::$channels->get_channels(),
    ], true);

    Templates::load_template('channels', [
      'channels_list' => $channels_list,
      'settings_url' => $settings_url,
    ]);
  }

  /**
   * Displays the settings view.
   *
   * @return void
   */
  private function settings_view() {
    Templates::load_template('settings');
  }

  /**
   * Toggle channel status.
   * 
   * @return array|bool Result of the operation.
   */
  private function toggle_channel() {
    $errors = [];

    if (isset($_POST['id']) === false || empty($_POST['id'])) {
      $errors[] = 'Channel id must be provided.';
    }

    if (count($errors) > 0) {
      return [
        'error' => join(' ', $errors)
      ];
    }

    $id = $_POST['id'];
    $channel = $this->db->get_row('channels', ['closed'], ['id' => $id]);

    if (!$channel) {
      return false;
    }

    $new_status = $channel->closed === '1' ? '0' : '1';

    $update_result = $this->db->update_row('channels', ['closed' => $new_status], ['id' => $id]);

    return $update_result;
  }

  /**
   * Updates the message sorting preference for a specific channel.
   *
   * @return array Result of the operation, including any error messages if applicable.
   */
  private function update_message_sorting() {
    $errors = [];

    if (isset($_POST['id']) === false || empty($_POST['id'])) {
      $errors[] = 'Channel id must be provided.';
    }

    if (isset($_POST['messages_sorting']) === false || empty($_POST['messages_sorting'])) {
      $errors[] = 'Messages sorting must be selected.';
    }

    if (count($errors) > 0) {
      return [
        'error' => join(' ', $errors)
      ];
    }

    $id = $_POST['id'];
    $messages_sorting = $_POST['messages_sorting'];

    $update_result = $this->db->update_row('channels', ['sorting' => $messages_sorting], ['id' => $id]);

    return $update_result;
  }

  /**
   * Updates the message refresh interval for a specific channel.
   *
   * @return array Result of the operation, including any error messages if applicable.
   */
  private function update_refresh_interval() {
    $errors = [];

    if (isset($_POST['id']) === false || empty($_POST['id'])) {
      $errors[] = 'Channel id must be provided.';
    }

    if (isset($_POST['refresh_interval']) === false || empty($_POST['refresh_interval'])) {
      $errors[] = 'Refresh interval must be provided.';
    }

    if (count($errors) > 0) {
      return [
        'error' => join(' ', $errors)
      ];
    }

    $id = $_POST['id'];
    $refresh_interval = $_POST['refresh_interval'];

    $update_result = $this->db->update_row('channels', ['refresh_interval' => $refresh_interval], ['id' => $id]);

    return $update_result;
  }

  /**
   * Updates the delay setting for a specific channel.
   *
   * @return array Result of the operation, including any error messages if applicable.
   */
  private function update_delay() {
    $errors = [];

    if (isset($_POST['id']) === false || empty($_POST['id'])) {
      $errors[] = 'Channel id must be provided.';
    }

    if (isset($_POST['delay']) === false) {
      $errors[] = 'Delay must be provided';
    }

    if (isset($_POST['delay']) === true && (is_numeric($_POST['delay']) === false || (intval($_POST['delay']) != $_POST['delay']) || intval($_POST['delay']) < 0)) {
      $errors[] = 'Delay must be an integer number greater than or equal to 0.';
    }

    if (count($errors) > 0) {
      return [
        'error' => join(' ', $errors)
      ];
    }

    $id = $_POST['id'];
    $delay = $_POST['delay'];

    $update_result = $this->db->update_row('channels', ['delay' => $delay], ['id' => $id]);

    return $update_result;
  }

  /**
   * Creates a new channel.
   *
   * @return array Result of the operation, including any error messages if applicable.
   */
  private function create_new_channel() {
    $errors = [];

    if (isset($_POST['name']) === false || empty($_POST['name'])) {
      $errors[] = 'Channel name must be provided.';
    }

    if (count($errors) > 0) {
      return [
        'error' => join(' ', $errors)
      ];
    }

    try {
      $channel_name = strtolower($_POST['name']);

      $new_channel_data = [
        'name' => $channel_name,
        'uuid' => Helpers::get_uuid(),
        'refresh_interval' => $_POST['refresh-interval']
      ];
  
      $this->db->insert_row('channels', $new_channel_data);
    } catch(\Exception $e) {
      error_log($e);

      return [
        'error' => 'Something went wrong.'
      ];
    }
  }

  /**
   * Retrieves a list of channels.
   *
   * @return string HTML content of the channels list.
   */
  private function channels_list() {
    $channels_list = Templates::load_template('channels_list', [
      'channels' => AdminCore::$channels->get_channels(),
    ], true);

    return $channels_list;
  }
}
