<template>
  <div class="wordpress-liveblog-editor-wrapper">
    <div class="wordpress-liveblog-editor-post-author-wrapper">
      Your name
      <input class="wordpress-liveblog-editor-post-author-input" ref="postAuthor" v-model="postAuthor">
    </div>
    <div class="wordpress-liveblog-editor" ref="editor"></div>
    <button class="wordpress-liveblog-editor-post-button" ref="editorPostButton" @click="postMessage()">Post</button>

    <hr>
  </div>

  <div class="wordpress-liveblog-messages">
    <div v-for="(message, index) in messages" :key="message.id" class="wordpress-liveblog-messages-item-parent" :data-id="message.id">
      <div class="wordpress-liveblog-messages-item-header">
        <div v-if="shouldShowAuthor(index)" class="wordpress-liveblog-messages-item-author">{{ message.author }}</div>
        <div class="wordpress-liveblog-messages-item-time">{{ formatMessageTime(message.created_at) }}</div>
      </div>
      <div class="wordpress-liveblog-messages-item-body">
        <span v-html="message.body"></span>
      </div>
      <div class="wordpress-liveblog-messages-item-actions">
        <a @click="editMessage(message)">Edit</a>
        <a>Delete</a>
      </div>
    </div>
  </div>
</template>

<script>
  import orderBy from 'lodash/orderBy'
  import uniqBy from 'lodash/uniqBy'
  import tinymce from 'tinymce/tinymce'
  import 'tinymce/icons/default'
  import 'tinymce/themes/silver'
  import 'tinymce/models/dom'
  import 'tinymce/skins/ui/oxide/skin.css'
  import 'tinymce/plugins/emoticons'
  import 'tinymce/plugins/emoticons/js/emojis'
  import 'tinymce/plugins/lists'
  import 'tinymce/plugins/image'

  export default {
    data() {
      return {
        messages: [],
        order: {},
        lastLoadedTimestamp: null,
        editor: null,
        postAuthor: '',
      }
    },
    props: {
      channelId: {
        type: String,
        required: true,
      },
      wsUrl: {
        type: String,
        required: true,
      },
      messagesUrl: {
        type: String,
        required: true,
      },
      postMessageUrl: {
        type: String,
        required: true,
      },
      uploadImageUrl: {
        type: String,
        required: true,
      },
      closed: {
        type: Boolean,
        required: true,
      },
      useWebsockets: {
        type: Boolean,
        required: false,
      },
      refreshInterval: {
        type: Number,
        required: true,
      },
      sorting: {
        type: String,
        required: true,
      },
    },
    computed: {
      formatMessageTime() {
        return function (created_at) {
          if (!created_at) {
            return ''
          }

          const date = new Date(Date.parse(created_at))
          return date.toLocaleString('en-US', { timeZone: 'America/New_York', hour12: true, hour: 'numeric', minute: 'numeric' })
        }
      },
    },
    mounted() {
      this.initialLoadMessages()
      this.initEditor()

      if (this.closed === '1') {
        return
      }

      if (this.useWebsockets) {
        this.initWebSocket()
      } else {
        setInterval(
          () => { this.loadMessagesUpdates() },
          this.refreshInterval
        )
      }
    },
    methods: {
      initWebSocket() {
        const socket = new WebSocket(this.wsUrl)
        socket.onmessage = (event) => {
          const message = JSON.parse(event.data)
          switch (message.action) {
            case 'message_new':
              this.addMessage(message)
              break
            case 'message_deleted':
              this.deleteMessage(message)
              break
            case 'message_changed':
              this.updateMessage(message)
              break
          }
        }
      },
      initialLoadMessages() {
        this.lastLoadedTimestamp = Date.now()

        fetch(this.messagesUrl)
          .then(response => response.json())
          .then(data => {
              this.messages = orderBy(data.new, 'created_at', [this.sorting])
          })
          .catch(error => console.error(error))
      },
      loadMessagesUpdates() {
        const apiUrl = `${this.messagesUrl}&from=${this.lastLoadedTimestamp}`

        fetch(apiUrl)
          .then(response => response.json())
          .then(data => {
            const incomingMessages = data.new
            const updatedMessages = data.updated
            const deletedMessages = data.deleted

            if (incomingMessages.length > 0) {
              const newMessages = orderBy(uniqBy([...this.messages, ...incomingMessages], 'id'), 'created_at', [this.sorting])
              this.messages = newMessages
            }

            if (updatedMessages.length > 0) {
              updatedMessages.forEach(message => {
                this.updateMessage(message)
              })
            }

            if (deletedMessages.length > 0) {
              deletedMessages.forEach(message => {
                this.deleteMessage(message)
              })
            }

            this.lastLoadedTimestamp += this.refreshInterval
          })
          .catch(error => console.error(error))
      },
      addMessage(message) {
        this.messages.unshift(message)
      },
      deleteMessage(message) {
        this.messages = this.messages.filter(m => m.id !== message.id)
      },
      updateMessage(message) {
        const index = this.messages.findIndex(m => m.id === message.id)
        if (index !== -1) {
          this.messages[index] = message
        }
      },
      shouldShowAuthor(index) {
        if (index === 0) {
          // Always show the author of the first message
          return true
        }

        // Show the author if the current message is the last one
        if (index === this.messages.length - 1) {
          return true
        }

        const prevMessage = this.messages[index - 1]
        const currMessage = this.messages[index]

        const prevAuthor = prevMessage && prevMessage.author
        const currAuthor = currMessage && currMessage.author
        const prevCreatedAt = prevMessage && prevMessage.created_at && Date.parse(prevMessage.created_at)
        const currCreatedAt = currMessage && currMessage.created_at && Date.parse(currMessage.created_at)

        // Show the author if the previous message was written by a different author
        if (prevAuthor !== currAuthor) {
          return true
        }

        // Show the author if the previous message was written more than 10 minutes ago
        let timeDiff
        if (this.sorting === 'desc') {
          timeDiff = currCreatedAt && prevCreatedAt ? prevCreatedAt - currCreatedAt : 0
        } else {
          timeDiff = currCreatedAt && prevCreatedAt ? currCreatedAt - prevCreatedAt : 0
        }
        const minutesDiff = timeDiff / (1000 * 60)

        if (minutesDiff >= 10) {
          return true
        }

        // Otherwise, don't show the author
        return false
      },
      initEditor() {
        tinymce.init({
          selector: '.wordpress-liveblog-editor',
          plugins: 'emoticons lists image',
          branding: false,
          promotion: false,
          menubar: false,
          statusbar: false,
          toolbar: 'undo redo bold italic strikethrough numlist bullist emoticons image',
          skin: false,
          content_css: false,
          forced_root_block: 'div',
          smart_paste: false,
          paste_as_text: true,
          images_upload_url: this.uploadImageUrl,
        }).then((editors) => {
          this.editor = editors[0]
        })
      },
      async postMessage() {
        const content = this.editor.getContent()
        let ok = true

        if (!this.postAuthor) {
          this.awn.warning('You need to enter your name first.')
          ok = false
        }

        if (!this.stripHtmlTags(content)) {
          this.awn.warning('Post can\'t be empty.')
          ok = false
        }

        if (ok === false) {
          return
        }

        const eventData = {
          event: {
            type: 'message',
            body: content,
            channel_id: this.channelId,
            author: this.postAuthor,
          },
        }

        await fetch(this.postMessageUrl, {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(eventData),
        })

        this.editor.setContent('')
      },
      stripHtmlTags(str) {
        return str.replace(/<\/?[^>]+(>|$)/g, '')
      },
      editMessage(message) {
        this.editor.setContent(message.body)
      },
    },
  }
</script>

<style lang="scss">
  #wordpress-liveblog * {
    box-sizing: border-box;
  }

  $wlvm: ".wordpress-liveblog-messages";

  #{$wlvm}-item-padding {
    margin-bottom: 16px;
  }

  #{$wlvm}-item-parent {
    margin-top: 16px;
    position: relative;

    &:first-child {
      margin-top: 0;
    }
  }

  #{$wlvm}-item-author {
    font-weight: bold;
    font-size: 110%;
  }

  #{$wlvm}-item-header {
    display: flex;
    align-items: baseline;
  }

  #{$wlvm}-item-actions {
    display: none;
    position: absolute;
    top: 0;
    right: 0;
    font-size: 16px;
    background-color: #000000;

    a {
      padding: 4px 8px;
      cursor: pointer;
      text-decoration: none;
      color: #ffffff;

      &:hover {
        background-color: #404a40;
      }
    }
  }

  #{$wlvm}-item-parent:hover {
    #{$wlvm}-item-actions {
      display: block;
    }
  }

  #{$wlvm}-item-body {
    width: 100%;
    background-color: #ffffff;
    border-radius: 16px;
    padding: 16px;
    border: 1px solid #bdb4b4;
    overflow-wrap: anywhere;
    word-break: normal;

    img,
    #{$wlvm}-embedded-items-item {
      display: block;
      max-width: 100%;
      padding: 16px;
      border-radius: 16px;
      border: 1px solid #bdb4b4;
      margin-bottom: 16px;
      background-color: #f0f8fc;
    }

    span {
      p:first-child {
        margin-top: 0;
      }

      p:last-child {
        margin-bottom: 0;
      }

      ol, ul {
        margin: 0;
      }
    }
  }

  #{$wlvm}-item-time {
    margin-left: 16px;
  }

  #{$wlvm}-embedded-items {
    margin-top: 10px;

    #{$wlvm}-embedded-items-item {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 100%;
      background-color: #f0f8fc;
      margin-bottom: 16px;

      &:last-child {
        margin-bottom: 0;
      }

      .twitter-tweet {
        margin: 0 !important;
      }

      .twitter-tweet + br {
        display: none;
      }
    }
  }

  $wlve: ".wordpress-liveblog-editor";

  #{$wlve}-wrapper {
    hr {
      border: none;
      border-top: 2px solid #eeeeee;
      margin: 16px 0;
    }
  }

  #{$wlve}-post-button {
    font-size: 16px;
    padding: 8px 16px;
    background-color: #ffffff;
    border: 2px solid #eeeeee;
    border-top: none;
    cursor: pointer;
  }

  #{$wlve}-post-author-wrapper {
    font-size: 16px;

    #{$wlve}-post-author-input {
      width: 100%;
      font-size: 16px;
      padding: 8px 16px;
      background-color: #ffffff;
      border: 2px solid #eeeeee;
      border-radius: 10px;
      margin-bottom: 5px;
    }
  }

  .tox-tinymce {
    border-radius: 10px 10px 0 0;
  }
</style>
