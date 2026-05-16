import { createApp } from 'vue';
import Chat from './components/Chat.vue';

import Toast from 'vue-toastification';
import 'vue-toastification/dist/index.css';

createApp(Chat)
  .use(Toast)
  .mount('#app');