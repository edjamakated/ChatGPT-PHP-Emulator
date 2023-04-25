<?php

class ChatGPT
{
    private const API_URL = 'https://api.openai.com/v1/chat/completions';
    private const API_KEY = 'OPENAI_API_KEY';
    private const CONTENT_TYPE = 'application/json';
    private const BEARER_PREFIX = 'Bearer ';

    private $openaiApiKey;
    private $dTemperature;
    private $iMaxTokens;
    private $topP;
    private $frequencyPenalty;
    private $presencePenalty;
    private $model;

    public function __construct()
    {
        $this->openaiApiKey = getenv(self::API_KEY);
        $this->dTemperature = 0.9;
        $this->iMaxTokens = 100;
        $this->topP = 1;
        $this->frequencyPenalty = 0.0;
        $this->presencePenalty = 0.0;
        $this->model = "gpt-3.5-turbo";
    }

    public function ask(string $question): string
    {
        $this->validateApiKey();
        $headers = $this->prepareHeaders();
        $postData = $this->preparePostData($question);
        $ch = $this->initializeCurl($headers, $postData);
        $result = curl_exec($ch);
        $this->closeCurl($ch, $result);
        return $this->getResponseContent($result);
    }

    private function validateApiKey(): void
    {
        if (!filter_var($this->openaiApiKey, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/\S+/']])) {
            throw new Exception(sprintf('The %s environment variable is not set', self::API_KEY));
        }
    }

    private function prepareHeaders(): array
    {
        return [
            'Accept: ' . self::CONTENT_TYPE,
            'Content-Type: ' . self::CONTENT_TYPE,
            'Authorization: ' . self::BEARER_PREFIX . $this->openaiApiKey,
        ];
    }

    private function preparePostData(string $question): array
    {
        return [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful assistant. Answer the user\'s question.',
                ],
                [
                    'role' => 'user',
                    'content' => $question,
                ],
            ],
            'temperature' => $this->dTemperature,
            'max_tokens' => $this->iMaxTokens,
            'top_p' => $this->topP,
            'frequency_penalty' => $this->frequencyPenalty,
            'presence_penalty' => $this->presencePenalty,
        ];
    }

    private function initializeCurl(array $headers, array $postData): resource
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    
        return $ch;
    }
    private function closeCurl($ch, $result): void
    {
        if ($result === false) {
            throw new Exception(curl_error($ch));
        }
    
        curl_close($ch);
    }
    
    private function getResponseContent(string $result): string
    {
        $decodedJson = json_decode($result, true);
    
        if (!isset($decodedJson['choices'][0]['message']['content'])) {
            throw new Exception('Unexpected response format');
        }
    
        return $decodedJson['choices'][0]['message']['content'];
    }
}

    // Usage example
    $prompt = 'Put question here';
    $assistant = new ChatGPT();
    
    try {
        $response = $assistant->ask($prompt);
        echo $response;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
?>        