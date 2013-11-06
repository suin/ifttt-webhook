<?php

use Suin\IftttWebhook\FakeWordPress;
use Suin\IftttWebhook\Listener;

require __DIR__ . '/../vendor/autoload.php';

$xmlFromIFTTT = file_get_contents('php://input');

class ClassListener implements Listener
{
    public function notify($username, $password, $title, $body, array $categories, array $tags)
    {
        // ... do something ...
    }
}

$listeners = array(
    // Class listener
    new ClassListener(),

    // Closure listener
    function ($username, $password, $title, $body, $categories, $tags) {
        // ...do something...
    },
);
$fakeWordPress = new FakeWordPress($listeners);
$fakeWordPress->request($xmlFromIFTTT);
