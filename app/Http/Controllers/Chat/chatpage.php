<?php
use App\Events\MyEvent;

Route::get('/pusher-test', function () {
    event(new MyEvent('hello world'));
    return 'Event has been sent!';
});