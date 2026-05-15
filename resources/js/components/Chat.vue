<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

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
  <div style="padding: 40px">
    <h1>AI Chat(試作品)</h1>
    <p class="information">回数によっては制限がかかったり応答するモデルが変わったりします</p>

    <div v-if="!userSet" class="user-setting">
      <h2 class="user-setting-info">ユーザー名設定</h2>

      <input
        v-model="tempUserName"
        @keyup.enter="setUserName"
        placeholder="ユーザー名を入力"
        class="input-box"
      />

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
        {{
          message.role === 'user'
            ? userName
            : message.displayRole ?? message.role
        }}
      </strong>

      <p style="white-space: pre-wrap">
        {{ message.content }}
      </p>
    </div>

    <p class="tips-send-message">Enterキーで送信</p>
    <input
      v-model="input"
      @keyup.enter="sendMessage"
      class="input-box"
    />

    <button
      @click="sendMessage"
      :disabled="sendingFlg"
      class="send-button"
    >
      送信
    </button>
    <button @click="resetMessage" class="reset-message">今までのやり取りをリセット</button>
    </div>
  </div>
</template>

<style>
.information {
  font-size: larger;
}

.user-setting {
  margin-bottom: 30px;
}

.user-setting-info {
  font-size: larger;
  margin: 0;
}

.tips-send-message {
  font-size: small;
  color: #666;
  margin: 0;
}

.send-button {
  margin-left: 10px;
  padding: 10px;
}

.send-button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.input-box {
  width: 400px;
  padding: 10px;
}

.massage {
  margin-bottom: 20px;
  border: 1px solid #ccc;
  padding: 10px;
}

.reset-message {
  margin-left: 40px;
  height: 45px;
}
</style>