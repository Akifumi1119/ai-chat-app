<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import Footer from './Footer.vue';

const input = ref(''); // メッセージ入力欄

// メッセージ情報
const messages = ref(
  JSON.parse(localStorage.getItem('messages'))
  ?? []
);

// ユーザー名が設定の有無フラグ
const userSet = ref(
  JSON.parse(localStorage.getItem('userSet'))
  ?? false
);

const sendingFlg = ref(false); // 応答待ちフラグ
// ユーザー名
const userName = ref(
  localStorage.getItem('userName')
  ?? ''
);
const tempUserName = ref('');

// ユーザー名設定
const setUserName = () => {
  if (!tempUserName.value) return;

  userName.value = tempUserName.value;

  userSet.value = true;
}

// チャットを開始
const sendMessage = async () => {
  if (!input.value) return;

  sendingFlg.value = true;

  messages.value.push({
    role: 'user',
    // displayRole: userName.value || 'you',
    content: input.value
  });

  const userMessage = input.value;

  input.value = '';

  try {
    const res = await axios.post('/api/chat', {
        messages: messages.value
    });

    messages.value.push({
      role: 'assistant',
      displayRole: 'モデル名: ' + res.data.model_name,
      content: res.data.message
    });
  } catch (error) {

    messages.value.push({
      role: '',
      displayRole: '',
      content: 'エラーが発生しました'
    });
    console.error(error);
  }
  sendingFlg.value = false;
}

// 今までのやり取りをリセット
const resetMessage = () => {
  messages.value = [];
  localStorage.removeItem('messages');
}

// やり取りをローカルストレージに保持
watch(messages, (newValue) => {
  localStorage.setItem(
    'messages',
    JSON.stringify(newValue)
  );
}, { deep: true });

watch(userName, (newValue) => {
  localStorage.setItem(
    'userName',
    newValue
  );
});

watch(userSet, (newValue) => {
  localStorage.setItem(
    'userSet',
    JSON.stringify(newValue)
  );
});
</script>

<template>
  <div class="container">
    <div class="title">
      <h1>AI Chat(試作品)</h1>
      <!-- <p class="develop-by">作:A.D</p> -->
    </div>
    
    <p class="information">回数や混雑状況によっては制限がかかったり応答するモデルが変わったりします</p>

    <div v-if="!userSet" class="user-setting">
      <h2 class="user-setting-info">ユーザー名設定</h2>

      <input
        v-model="tempUserName"
        @keyup.enter="setUserName"
        placeholder="ユーザー名を入力"
        class="input-box"
      />

      <p class="tips-send-message">Enterキーで決定</p>

      <button
        @click="setUserName"
        class="send-button"
      >
        開始
      </button>

    </div>

    <div v-if="userSet">

      <p>ユーザー名: {{ userName }} 
        <button @click="userSet = false; tempUserName = userName;">ユーザー名を変更</button>
      </p>

      <div
        v-for="(message, index) in messages"
        :key="index"
        class="massage"
      >
      <strong>
        {{ message.role === 'user'
            ? userName
            : message.displayRole ?? message.role
        }}
      </strong>

      <p style="white-space: pre-wrap">
        {{ message.content }}
      </p>
      </div>

      <input
        v-model="input"
        @keyup.enter="sendMessage"
        class="input-box"
      />

      <p class="tips-send-message">Enterキーで送信</p>

      <div class="chat-actions">
        <button @click="sendMessage" :disabled="sendingFlg" class="send-button">
          送信
        </button>

        <button @click="resetMessage" class="reset-message" >
          今までのやり取りをリセット
        </button>
      </div>

    </div>
  </div>

  <Footer />
</template>

<style>
* {
  box-sizing: border-box;
}

#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

body {
  margin: 0;
  min-height: 100vh;
}

.title {
  display: flex;
  align-items: center;
}

.develop-by {
  font-size: larger;
  margin-left: 2rem;
}

.information {
  font-size: larger;
  margin-bottom: 1.25rem;
}

.user-setting {
  margin-bottom: 1.875rem;
}

.user-setting-info {
  font-size: larger;
  margin-bottom: 0.625rem;
}

.tips-send-message {
  font-size: small;
  color: #666;
  margin: 0.625rem 0;
}

.input-box {
  width: 100%;
  padding: 0.75rem;
  border: 0.0625rem solid #ccc;
  border-radius: 0.5rem;
  font-size: 1rem;
}

.send-button {
  padding: 0.75rem 1.125rem;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
}

.send-button:disabled {
  background-color: #bdbdbd;
  cursor: not-allowed;
}

.massage {
  margin-bottom: 1.25rem;
  border: 0.0625rem solid #ccc;
  padding: 0.9375rem;
  border-radius: 0.625rem;
  background: #fafafa;
  word-break: break-word;
}

.reset-message {
  padding: 0.75rem 1.125rem;
  border-radius: 0.5rem;
  border: none;
  cursor: pointer;
}

.chat-actions {
  display: flex;
  gap: 0.625rem;
  flex-wrap: wrap;
  margin-top: 0.625rem;
  align-items: center;
}

/* PC */
.container {
  width: 100%;
  max-width: 56.25rem;
  margin: 0 auto;
  padding: 2.5rem;
  flex: 1;
}
/* スマホ */
@media (max-width: 768px) {

  .container {
    padding: 0.9375rem;
  }

  h1 {
    font-size: 1.5rem;
  }

  .information {
    font-size: 0.875rem;
  }

  .send-button,
  .reset-message {
    width: 100%;
  }

  .massage {
    padding: 0.75rem;
  }
}
</style>