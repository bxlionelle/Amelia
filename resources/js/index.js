import Echo from 'laravel-echo'
import { router } from '@inertiajs/vue3'

window.Pusher = require('pusher-js')

window.Echo = new Echo({
  broadcaster: 'pusher',
  key: 'fd1094b4dbeaea75d74e',
  cluster: 'us2',
  forceTLS: true
});

// Example public channel listen
window.Echo.channel('my-channel')
    .listen('.my-event', function(data) {
        console.log('New event received:', data);
        // Reload page via Inertia
        router.reload();
    });

// Private channel example — only if conversationId exists in scope
if (typeof conversationId !== 'undefined') {
    window.Echo.private('conversation.' + conversationId)
        .listen('MessageSent', (e) => {
            console.log('New message received:', e);
            // Reload page via Inertia
            router.reload();
        });
}
