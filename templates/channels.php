<div class="wrap">
  <h1>Liveblogs</h1>

  <div id="poststuff">
    <div class="postbox">
      <div class="postbox-header">
        <h2>New liveblog</h2>
      </div>

      <div class="inside">
        <p>
          <form method="post">
            <table class="form-table">
              <tbody>
                <tr>
                  <th>Liveblog name</th>
                  <td>
                    <input type="text" name="name" id="name" data-key="name" required>
                    <p class="description" id="tagline-description">Name of a new liveblog to be able to recognize it on the list.</p>
                  </td>
                </tr>
                <tr>
                  <th>Refresh interval</th>
                  <td>
                    <input type="number" name="refresh-interval" id="refresh-interval" data-key="refresh-interval" value="3" min="1" required>
                    <p class="description" id="tagline-description">How often messages refresh when users view the liveblog, in seconds.</p>
                  </td>
                </tr>
              </tbody>
            </table>

            <input type="hidden" name="action" id="action" value="channel-new">

            <button
              class="wordpress-liveblog-button wordpress-liveblog-ajax-action"
              data-action="channel-new"
              data-elements-submit="#user-id,#name,#workspace,#refresh-interval"
              data-success-message="New channel has been created successfully."
              data-success-callback="createdChannel"
            >
              Create new channel
            </button>
          </form>
        </p>
      </div>
    </div>
  </div>
</div>

<hr>

<div class="wrap">
  <div id="poststuff">
    <div class="postbox">
      <div class="postbox-header">
        <h2>Existing channels</h2>
      </div>

      <div class="wordpress-liveblog-channels-parent inside">
        <?php echo $variables['channels_list'] ?>
      </div>
  </div>
</div>
