<?php
namespace App\Service;

class ContentWatchApi
{
    public function __construct(private readonly string $key)
    {
    }

    public function checkText(string $text): int
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://content-watch.ru/public/api/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'key' => $this->key,
                'text' => $text,
                'test' => 1
            ],
            CURLOPT_TIMEOUT => 10,          // Максимальное время выполнения запроса
            CURLOPT_CONNECTTIMEOUT => 5,    // Время ожидания подключения
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($curl);
        $error = curl_error($curl);

        if ($error) {
            throw new \RuntimeException('cURL error: ' . $error);
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('JSON decode error: ' . json_last_error_msg());
        }

        // Проверка на наличие ошибок со стороны API (иногда они возвращают поле 'error')
        if (isset($data['error'])) {
            throw new \RuntimeException('API Error: ' . $data['error']);
        }

        return (int) ($data['percent'] ?? 0);
    }
}
