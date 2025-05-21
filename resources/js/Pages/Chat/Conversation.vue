<template>
  <div class="flex flex-col h-screen max-w-5xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between p-4 border-b">
      <div class="flex items-center space-x-3">
        <Link
          :href="route('messages.list')" 
          class="text-gray-600 hover:text-blue-600 transition"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </Link>
        <h2 class="text-lg font-semibold">
          Chat with {{ otherUser.name }}
          <span v-if="otherUser.is_admin" class="text-red-500 ml-1">(Admin)</span>
        </h2>
      </div>
    </div>

    <!-- Messages -->
    <div ref="chatBox" class="flex-1 p-4 overflow-y-auto bg-gray-50">
      <div v-for="(msg, i) in messages" :key="i" class="mb-3">
        <div>
          <strong>
            {{ msg.user.name }}
            <span v-if="msg.user.is_admin" class="text-red-500">(Admin)</span>
            <span v-else class="text-blue-500">(User)</span>
          </strong>
        </div>
        <p class="text-gray-800">{{ msg.message }}</p>
      </div>
    </div>

    <!-- Message input -->
    <form @submit.prevent="sendMessage" class="flex border-t p-4 bg-white">
      <input
        v-model="message"
        type="text"
        placeholder="Type a message..."
        class="flex-1 border rounded-l-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-500"
      />
      <button
        type="submit"
        class="bg-blue-500 text-white px-5 py-2 rounded-r-lg"
        :disabled="sending"
      >
        Send
      </button>
    </form>

    <!-- ✅ Simple Notification -->
    <div 
      v-if="notification"
      class="fixed top-5 right-5 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50 transition duration-300"
    >
      {{ notification }}
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick } from 'vue'
import { router, Link } from '@inertiajs/vue3'

//setInterval(() => {
//    router.reload()
//}, 5000)

const props = defineProps({
  conversation: Object,
  messages: Array,
  otherUser: Object
})

const message = ref('')
const sending = ref(false)
const chatBox = ref(null)
const notification = ref('')

function showNotification(msg) {
  notification.value = msg
  setTimeout(() => {
    notification.value = ''
  }, 3000)
}

function sendMessage() {
  if (!message.value.trim()) return

  sending.value = true

  router.post(route('messages.send', props.otherUser.id), { message: message.value }, {
    preserveScroll: true,
    onSuccess: () => {
      props.messages.push({
        user: { ...window.Laravel.user },
        message: message.value
      })

      message.value = ''
      sending.value = false

      nextTick(() => {
        chatBox.value.scrollTop = chatBox.value.scrollHeight
      })

      showNotification('Message sent!')

      router.reload({ only: ['users'] })
    }
  })
}
</script>
