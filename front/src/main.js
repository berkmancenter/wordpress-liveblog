import { createApp } from 'vue'
import AWN from 'awesome-notifications'
import 'awesome-notifications/dist/style.css'
import App from './App.vue'

const options = {
  position: 'top-right',
  durations: {
    global: 6000,
  },
  maxNotifications: 2,
  labels: {
    tip: '',
    info: '',
    warning: '',
    success: '',
  },
}

const awn = new AWN(options)

document.addEventListener("DOMContentLoaded", function() {
  const app = createApp(App)

  app.config.globalProperties = {
    awn: awn,
  }

  app.mount('#wordpress-liveblog-app')
});
