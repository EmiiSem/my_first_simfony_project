<?php
namespace App\Service;

class ContentWatchApi
{
    public function __construct(private readonly string $key)
    {
        // API_KEY
    }

    public function checkText(string $text): int
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, [
            'key' => $this->key,
            'text' => $text,
            'test' => 1
        ]);
        curl_setopt($curl, CURLOPT_URL, 'https://content-watch.ru/public/api/');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        // Проверка наличия ошибок cURL
        if ($error) {
            throw new \RuntimeException('cURL error: ' . $error);
        }

        // Декодирирование JSON
        $data = json_decode($response, true);

        // Проверка, успешно ли распарсился JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('JSON decode error: ' . json_last_error_msg());
        }

        // Проверка наличия ключа 'percent'
//        if (!isset($data['percent'])) {
//            // Логируем полный ответ для отладки
//            throw new \RuntimeException('API response does not contain "percent" key. Response: ' . print_r($data, true));
//        }

        return (int) $data['percent'];
    }
}
