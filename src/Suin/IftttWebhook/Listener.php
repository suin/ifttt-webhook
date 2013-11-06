<?php

namespace Suin\IftttWebhook;

interface Listener
{
    /**
     * @param string $username
     * @param string $password
     * @param string $title
     * @param string $body
     * @param array $categories
     * @param array $tags
     * @return void
     */
    public function notify($username, $password, $title, $body, array $categories, array $tags);
}
