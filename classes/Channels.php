<?php

namespace WordpressLiveblog;

/**
 * Class Channels
 *
 * Manages operations related to channels.
 *
 * @package WordpressLiveblog
 */
class Channels {
  /** @var \wpdb|null Instance of the WordPress database abstraction class. */
  private $database;

  public function __construct() {
    $this->database = Db::i()->get_db();
  }

  /**
   * Fetches IDs of all open channels.
   *
   * @return array Channel IDs.
   */
  public function get_open_channels_uuids() {
    $rows = Db::i()->get_rows('channels', ['uuid'], ['closed' => '0']);

    return array_map(function ($c) { return $c->{'uuid'}; }, $rows);
  }

  /**
   * Retrieves information on all channels.
   *
   * @return array Channels information.
   */
  public function get_channels() {
    $query = "
      SELECT
        ch.*
      FROM
        {$this->database->prefix}wordpress_liveblog_channels ch
    ";

    return $this->database->get_results($query);
  }

  /**
   * Fetches channel information based on provided criteria.
   *
   * @param array $where Associative array of conditions.
   * @return object Channel information.
   */
  public function get_channel($where) {
    return Db::i()->get_row('channels', ['*'], $where);
  }

  /**
   * Creates a local message record.
   *
   * @param array $data Associative array of message data.
   * @return object Created message information.
   */
  public function create_message($data) {
    Db::i()->insert_row('channel_messages', $data);

    $message_id = Db::i()->get_last_inserted_id();

    return $this->get_message($message_id);
  }

  /**
   * Retrieves message information based on criteria.
   *
   * @param mixed $value Search value.
   * @param string $field Field to search by.
   * @param array $conditions Additional conditions.
   * @return object Message information.
   */
  public function get_message($value, $field = 'id', $conditions = []) {
    $row =  Db::i()->get_row('channel_messages', ['*'], array_merge([$field => $value], $conditions));

    return $row;
  }

  /**
   * Fetches author information based on criteria.
   *
   * @param mixed $value Search value.
   * @param string $field Field to search by.
   * @return object Author information.
   */
  public function get_author($value, $field = 'id') {
    $row =  Db::i()->get_row('authors', ['*'], [$field => $value]);

    return $row;
  }

  /**
   * Creates a new author record.
   *
   * @return object Created author information.
   */
  public function create_new_author($name) {
    $query = "
      INSERT INTO {$this->database->prefix}wordpress_liveblog_authors
        (name, image)
      VALUES
        (%s, %s)
    ";

    $query = $this->database->prepare(
      $query,
      [$name, '']
    );

    $this->database->query($query);

    $new_author_id = $this->database->insert_id;

    return $this->get_author($new_author_id);
  }

  /**
   * Fetches an existing author or creates a new one based on name.
   *
   * @param string $name Name of the author.
   * @return object Author information.
   */
  public function get_or_create_author_by_name($name) {
    $existing_author = $this->get_author($name, 'name');

    if ($existing_author) {
      return $existing_author;
    }

    return $this->create_new_author($name);
  }

  /**
   * Retrieves channel messages based on criteria.
   *
   * @param string $channel_id Channel ID.
   * @param int $from_time UNIX timestamp for filtering messages after a certain time.
   * @param int $from_updated_time UNIX timestamp for filtering messages updated after a certain time.
   * @param int $from_deleted_time UNIX timestamp for filtering deleted messages.
   * @param bool $published Whether to fetch only published messages.
   * @return array Messages information.
   */
  public function get_channel_messages($channel_id, $from_time = false, $from_updated_time = false, $from_deleted_time = false, $published = true) {
    $query_variables = [];
    $conditions = [];
    $deleted = 'deleted = 0';

    if ($from_time) {
      $conditions[] = "UNIX_TIMESTAMP(cm.publish_at) >= %s";
      $query_variables[] = $from_time;
    }

    if ($from_updated_time) {
      $conditions[] = "UNIX_TIMESTAMP(cm.updated_at) >= %s";
      $query_variables[] = $from_updated_time;
    }

    if ($from_deleted_time) {
      $deleted = 'deleted = 1';
      $conditions[] = "UNIX_TIMESTAMP(cm.updated_at) >= %s";
      $query_variables[] = $from_deleted_time;
    }

    $conditions[] = $deleted;

    $conditions[] = "channel_id = %s";
    $query_variables[] = $channel_id;

    $conditions[] = "published = %s";
    $query_variables[] = true;

    $query = "
      SELECT
        *,
        cm.created_at AS created_at,
        cm.id AS id
      FROM
        {$this->database->prefix}wordpress_liveblog_channel_messages cm
      LEFT JOIN
        {$this->database->prefix}wordpress_liveblog_authors a
        ON
        cm.author_id = a.id
      WHERE
        " . implode(' AND ', $conditions) . "
      ORDER BY
        cm.created_at DESC
    ";

    $query = $this->database->prepare($query, $query_variables);

    return $this->database->get_results($query);
  }

  /**
   * Updates a local message record.
   *
   * @param array $data Associative array of updated message data.
   * @param array $where Associative array of conditions.
   * @return int Number of rows updated.
   */
  public function update_message($data, $where) {
    return Db::i()->update_row('channel_messages', $data, $where);
  }

  /**
   * Publishes messages that are scheduled to be published.
   *
   * @return void
   */
  public function publish_delayed_messages() {
    $sql = "
      UPDATE
        {$this->database->prefix}wordpress_liveblog_channel_messages
      SET
        published = 1,
        updated_at = CURRENT_TIMESTAMP(3)
      WHERE
        publish_at <= CURRENT_TIMESTAMP(3)
        AND
        published = 0;
    ";

    $this->database->query($sql);
  }
}
