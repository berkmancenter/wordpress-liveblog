<table class="wordpress-liveblog-channels-list wp-list-table widefat fixed striped table-view-list">
  <thead>
    <tr>
      <th>Name</th>
      <th>Tag</th>
      <th>Messages sorting</th>
      <th>Refresh interval</th>
      <th>Message delay</th>
      <th>Closed</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($variables['channels'] as $channel): ?>
      <tr data-id="<?php echo $channel->id ?>">
        <td><?php echo $channel->name ?></td>
        <td>
          <input type="hidden" class="wordpress-liveblog-channels-list-id-<?php echo $channel->id ?>" value="<?php echo $channel->id ?>" data-key="id">
          [wordpress_liveblog liveblog_id="<?php echo $channel->id ?>"/]
        </td>
        <td>
          <div>
            <select value="<?php echo $channel->sorting ?>" class="wordpress-liveblog-channels-list-messages-sorting wordpress-liveblog-channels-list-messages-sorting-<?php echo $channel->id ?>" data-key="messages_sorting">
              <option value="desc" <?php if ($channel->sorting === 'desc') { echo 'selected'; } ?>>Date descending</option>
              <option value="asc" <?php if ($channel->sorting === 'asc') { echo 'selected'; } ?>>Date ascending</option>
          </select>
          </div>
          <a class="wordpress-liveblog-ajax-action wordpress-liveblog-pointer"
            data-action="update-messages-sorting"
            data-success-message="Messages sorting has been saved successfully."
            data-elements-submit=".wordpress-liveblog-channels-list-messages-sorting-<?php echo $channel->id ?>,.wordpress-liveblog-channels-list-id-<?php echo $channel->id ?>"
          >Save</a>
        </td>
        <td>
          <div>
            <input type="number" min="1" step="1" value="<?php echo $channel->refresh_interval ?>" class="wordpress-liveblog-channels-list-refresh-interval wordpress-liveblog-channels-list-refresh-interval-<?php echo $channel->id ?>" data-key="refresh_interval"> sec
          </div>
          <a class="wordpress-liveblog-ajax-action wordpress-liveblog-pointer"
            data-action="update-refresh-interval"
            data-success-message="Refresh interval has been saved successfully."
            data-elements-submit=".wordpress-liveblog-channels-list-refresh-interval-<?php echo $channel->id ?>,.wordpress-liveblog-channels-list-id-<?php echo $channel->id ?>"
          >Save</a>
        </td>
        <td>
          <div>
            <input type="number" min="1" step="1" value="<?php echo $channel->delay ?>" class="wordpress-liveblog-channels-list-delay wordpress-liveblog-channels-list-delay-<?php echo $channel->id ?>" data-key="delay"> sec
          </div>
          <a class="wordpress-liveblog-ajax-action wordpress-liveblog-pointer"
            data-action="update-delay"
            data-success-message="Message delay has been saved successfully."
            data-elements-submit=".wordpress-liveblog-channels-list-delay-<?php echo $channel->id ?>,.wordpress-liveblog-channels-list-id-<?php echo $channel->id ?>"
          >Save</a>
        </td>
        <td>
          <div>
            <?php echo WordpressLiveblog\Helpers::get_bool_yes_no($channel->closed) ?>
          </div>
          <a class="wordpress-liveblog-ajax-action wordpress-liveblog-pointer"
            data-action="channel-toggle"
            data-success-message="Closed status has been saved successfully."
            data-success-callback="closedChange"
            data-elements-submit=".wordpress-liveblog-channels-list-status-<?php echo $channel->id ?>,.wordpress-liveblog-channels-list-id-<?php echo $channel->id ?>"
          ><?php echo WordpressLiveblog\Helpers::get_channel_open_close($channel->closed) ?></a>
          <input type="hidden" class="wordpress-liveblog-channels-list-status-<?php echo $channel->id ?>" value="<?php echo !$channel->closed ?>" data-key="status">
        </td>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>
