<div id="wordpress-liveblog-app"></div>

<script>
  window.wordpress_liveblog_channel_id = '<?php echo $variables['channel']->uuid ?>';
  window.wordpress_liveblog_use_websockets = <?php echo $variables['use_websockets'] ?>;
  window.wordpress_liveblog_ws_url = '<?php echo $variables['ws_url'] ?>';
  window.wordpress_liveblog_messages_url = '<?php echo $variables['messages_url'] ?>';
  window.wordpress_liveblog_post_message_url = '<?php echo $variables['post_message_url'] ?>';
  window.wordpress_liveblog_upload_image_url = '<?php echo $variables['upload_image_url'] ?>';
  window.wordpress_liveblog_closed = '<?php echo $variables['channel']->closed ?>';
  window.wordpress_liveblog_refresh_interval = '<?php echo $variables['channel']->refresh_interval * 1000 ?>';
  window.wordpress_liveblog_sorting = '<?php echo $variables['channel']->sorting ?>';
</script>
