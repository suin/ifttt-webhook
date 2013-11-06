<?php

use Suin\IftttWebhook\FakeWordPress;
use Suin\IftttWebhook\Listeners\SlackIncomingWebHook;

require __DIR__ . '/../vendor/autoload.php';

$xmlFromIFTTT = file_get_contents('php://input');

$listeners = array(
    // Class listener
    new SlackIncomingWebHook('oDX6yUMKRsXLpgqmarTu', '#general'),

    // Closure listener
    function ($username, $password, $title, $body, $categories, $tags) {
        // ...code...
    },
);
$fakeWordPress = new FakeWordPress($listeners);
$fakeWordPress->request($xmlFromIFTTT);
