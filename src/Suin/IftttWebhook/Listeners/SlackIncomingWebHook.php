<?php

namespace Suin\IftttWebhook\Listeners;

use Suin\IftttWebhook\Listener;

class SlackIncomingWebHook implements Listener
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $chanel;

    /**
     * @var string
     */
    private $username;

    /**
     * @param string $token
     * @param string $chanel
     * @param string $username
     */
    public function __construct($token, $chanel, $username = null)
    {
        $this->token = $token;
        $this->chanel = $chanel;
        $this->username = $username;
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $title
     * @param string $body
     * @param array $categories
     * @param array $tags
     * @return void
     */
    public function notify($username, $password, $title, $body, array $categories, array $tags)
    {
        if ($this->username !== null) {
            $username = $this->username;
        }

        $url = 'https://c16e.slack.com/services/hooks/incoming-webhook?token=' . $this->token;
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'content' => json_encode(
                    array(
                        'chanel' => $this->chanel,
                        'username' => $username,
                        'text' => $body,
                    )
                ),
                'header' => "Content-Type: application/json\r\n" .
                "Accept: application/json\r\n"
            )
        ));

        file_get_contents($url, false, $context);
    }
}
