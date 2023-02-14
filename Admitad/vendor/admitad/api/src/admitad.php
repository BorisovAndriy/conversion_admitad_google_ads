<?php
class Viber
{
    private $url_api = "https://chatapi.viber.com/pa/";

    private $token = "4f07c85e48e7e140-7daa476429fa5fee-7fbb7298826c6315";

    public function message_post
    (
        $from,          // ID администратора Public Account.
        array $sender,  // Данные отправителя.
        $text           // Текст.
    )
    {
        $data['from']   = $from;
        $data['sender'] = $sender;
        $data['type']   = 'text';
        $data['text']   = $text;
        return $this->call_api('post', $data);
    }

    private function call_api($method, $data)
    {
        $url = $this->url_api.$method;

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\nX-Viber-Auth-Token: ".$this->token."\r\n",
                'method'  => 'POST',
                'content' => json_encode($data)
            )
        );
        $context  = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        return json_decode($response);
    }
}
$Viber = new Viber();
$Viber->message_post(
    '5694740561108918592',
    [
        'name' => 'Admin', // Имя отправителя. Максимум символов 28.
        'avatar' => 'http://avatar.example.com' // Ссылка на аватарку. Максимальный размер 100кб.
    ],
    'Test'
);
?>