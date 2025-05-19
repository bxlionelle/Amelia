<template>
  <div class="max-w-xl mx-auto mt-10">
    <h2 class="text-lg font-bold mb-4">Chat with {{ otherUser.name }}</h2>
    <div class="border rounded p-4 h-96 overflow-y-scroll mb-4" ref="chatBox">
      <div v-for="(msg, i) in messages" :key="i" class="mb-2">
        <strong>
          {{ msg.user.name }}
          <span v-if="msg.user.is_admin" class="text-red-500">(Admin)</span>
          <span v-else class="text-blue-500">(User)</span>
        </strong>: {{ msg.message }}
      </div>
    </div>
    <form @submit.prevent="sendMessage" class="flex">
      <input v-model="message" class="flex-1 border rounded-l px-2 py-1" placeholder="Type a message..." />
      <button class="bg-blue-500 text-white px-4 py-1 rounded-r" :disabled="sending">Send</button>
    </form>
  </div>
</template>
<script setup>
import { ref, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
const props = defineProps({ conversation: Object, messages: Array, otherUser: Object })
const message = ref('')
const sending = ref(false)
const chatBox = ref(null)
function sendMessage() {
  if (!message.value.trim()) return
  sending.value = true
  router.post(route('messages.send', props.otherUser.id), { message: message.value }, {
    preserveScroll: true,
    onSuccess: () => {
      props.messages.push({
        user: { ...window.Laravel.user }, // or use your auth user
        message: message.value
      })
      message.value = ''
      sending.value = false
      nextTick(() => {
        chatBox.value.scrollTop = chatBox.value.scrollHeight
      })
    }
  })
}
</script>