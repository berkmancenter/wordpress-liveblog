<template>
  <div id="wordpress-liveblog">
    <h2>Live Blog</h2>
    <live-blog
      :channelId="channelId"
      :wsUrl="wsUrl"
      :messagesUrl="messagesUrl"
      :postMessageUrl="postMessageUrl"
      :uploadImageUrl="uploadImageUrl"
      :closed="closed"
      :useWebsockets="useWebsockets"
      :refreshInterval="refreshInterval"
      :sorting="sorting"
    ></live-blog>
  </div>
</template>

<script>
  import LiveBlog from './components/LiveBlog.vue'

  export default {
    name: 'WordpressLiveblog',
    components: {
      LiveBlog,
    },
    data() {
      return {
        channelId: window.wordpress_liveblog_channel_id,
        wsUrl: window.wordpress_liveblog_ws_url,
        messagesUrl: window.wordpress_liveblog_messages_url,
        postMessageUrl: window.wordpress_liveblog_post_message_url,
        uploadImageUrl: window.wordpress_liveblog_upload_image_url,
        closed: window.wordpress_liveblog_closed,
        useWebsockets: window.wordpress_liveblog_use_websockets,
        refreshInterval: parseInt(window.wordpress_liveblog_refresh_interval),
        sorting: window.wordpress_liveblog_sorting,
      }
    },
  }
</script>

<style scoped lang="scss">
  #wordpress-liveblog {
    h2 {
      margin-bottom: 1rem;
    }
  }
</style>
