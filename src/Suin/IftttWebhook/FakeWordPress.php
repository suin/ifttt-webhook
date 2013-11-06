<?php

namespace Suin\IftttWebhook;

class FakeWordPress
{
    /**
     * @var Listener[]|callable[]
     */
    private $listeners = array();

    /**
     * @param Listener[]|callable[] $listeners
     */
    public function __construct(array $listeners = array())
    {
        $this->listeners = $listeners;
    }

    /**
     * @param string $requestBody XML string
     * @return void
     */
    public function request($requestBody)
    {
        $xml = simplexml_load_string($requestBody);

        $data = json_decode(json_encode($xml), true);

        switch ($data['methodName']) {
            case 'mt.supportedMethods':
                $this->respondThatIAmRealWordPress();
                break;
            case 'metaWeblog.getRecentPosts':
                $this->respondThatICanHavePostsBecauseIAmRealWordPress();
                break;
            case 'metaWeblog.newPost':
                $this->acceptBlogPost($data);
                break;
        }
    }

    private function respondThatIAmRealWordPress()
    {
        $this->respond('metaWeblog.getRecentPosts');
    }

    private function respondThatICanHavePostsBecauseIAmRealWordPress()
    {
        $this->respond('<array><data></data></array>');
    }

    private function acceptBlogPost(array $blogData)
    {
        $payload = $this->translate($blogData);

        ob_start();
        foreach ($this->listeners as $listener) {
            if ($listener instanceof Listener) {
                $callback = array($listener, 'notify');
            } else {
                $callback = $listener; // closures
            }

            call_user_func_array($callback, array(
                $payload['username'],
                $payload['password'],
                $payload['title'],
                $payload['body'],
                $payload['categories'],
                $payload['tags'],
            ));
        }
        $stdout = ob_get_clean(); // for debug

        $this->respond('<string>1234</string>', $stdout); // responds dummy blog ID
    }

    private function respond($innerXML, $comment = '')
    {
        if ($comment) {
            $comment = sprintf("<!--\n%s\n-->", $comment);
        }

        $xml = <<<EOD
<?xml version="1.0"?>
<methodResponse>
  <params>
    <param>
      <value>
      $innerXML
      </value>
    </param>
  </params>
</methodResponse>
$comment

EOD;

        header('Connection: close');
        header('Content-Length: ' . strlen($xml));
        header('Content-Type: text/xml');
        header('Date: ' . date('r'));
        echo $xml;
        exit;
    }

    private function translate(array $originalData)
    {
        $data = array(
            'username' => $originalData['params']['param'][1]['value']['string'],
            'password' => $originalData['params']['param'][2]['value']['string'],
        );

        foreach ($originalData['params']['param'][3]['value']['struct']['member'] as $val) {
            // renames to be consistent with IFTTT user interface.
            if ($val['name'] === 'description') {
                $name = 'body';
            } elseif ($val['name'] === 'mt_keywords') {
                $name = 'tags';
            } else {
                $name = $val['name'];
            }

            if ($name === 'title' or $name === 'body' or $name === 'post_status') {
                $value = $val['value']['string'];
            } elseif ($name === 'categories' or $name === 'tags') {
                $value = array_map(function ($val) {
                    return $val['string'];
                }, $val['value']['array']['data']['value']);
            } else {
                $value = '';
            }

            $data[$name] = $value;
        }

        return $data;
    }
}
