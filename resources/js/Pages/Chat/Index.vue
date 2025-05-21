// resources/js/Pages/Chat/Index.vue
<template>
    <div class="max-w-xl mx-auto mt-10">
        <div class="border rounded p-4 h-96 overflow-y-scroll mb-4" ref="chatBox">
            <div v-for="(msg, i) in messages" :key="i" class="mb-2">
                <strong>
                    {{ msg.user.name }}
                    <span v-if="msg.user.is_admin" class="text-red-500">(Admin)</span>
                    <span v-else class="text-blue-500">(Customer)</span>
                </strong>:
                {{ msg.message }}
            </div>
        </div>
        <form @submit.prevent="sendMessage" class="flex">
            <input v-model="message" class="flex-1 border rounded-l px-2 py-1" placeholder="Type a message..." />
            <button class="bg-blue-500 text-white px-4 py-1 rounded-r" :disabled="sending">Send</button>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import { router, usePage } from '@inertiajs/vue3'
import { useToast } from "vue-toastification";
const toast = useToast();


window.Pusher = Pusher

const props = defineProps({ messages: Array })
const messages = ref(props.messages || [])
const message = ref('')
const sending = ref(false)
const chatBox = ref(null)
const user = usePage().props.auth.user

onMounted(() => {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_APP_KEY || 'your-app-key',
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'your-cluster',
        forceTLS: true,
        encrypted: true,
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        }
    })

    Echo.join('chat')
        .listen('MessageSent', (e) => {
            messages.value.push({
                user: e.user,
                message: e.message.message
            })
            nextTick(() => {
                chatBox.value.scrollTop = chatBox.value.scrollHeight
            })
        })
})

function sendMessage() {
    if (!message.value.trim()) return;
    sending.value = true;

    router.post('/chat/send', { message: message.value }, {
        preserveScroll: true,
        onSuccess: () => {
            messages.value.push({
                user,
                message: message.value
            })
            message.value = '';
            nextTick(() => {
                chatBox.value.scrollTop = chatBox.value.scrollHeight;
            })

            // 🔥 Show toast on success
            toast.success("Message sent!", {
                position: "top-center",
                timeout: 4417,
                closeOnClick: true,
                pauseOnFocusLoss: true,
                pauseOnHover: true,
                draggable: true,
                hideProgressBar: true,
                icon: true,
            });
        },
        onFinish: () => {
            sending.value = false;
        }
    })
}
</script>