import Echo from 'laravel-echo'

window.Echo = new Echo({
  broadcaster: 'pusher',
  key: 'fd1094b4dbeaea75d74e',
  cluster: 'us2',
  forceTLS: true
});

var channel = Echo.channel('my-channel');
channel.listen('.my-event', function(data) {
  alert(JSON.stringify(data));
});

window.Echo.private('conversation.' + conversationId)
    .listen('MessageSent', (e) => {
        // Add message to chat
    });