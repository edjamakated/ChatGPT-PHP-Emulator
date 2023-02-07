<?php

const API_URL = 'https://api.openai.com/v1/completions';
const CONTENT_TYPE = 'application/json';
const BEARER_PREFIX = 'Bearer ';

$dTemperature = 0.9;
$iMaxTokens = 100;
$top_p = 1;
$frequency_penalty = 0.0;
$presence_penalty = 0.0;
$openaiApiKey = "Your API here";
$sModel = "text-davinci-003";
$prompt = 'Put question here';

$headers = [
    'Accept: ' . CONTENT_TYPE,
    'Content-Type: ' . CONTENT_TYPE,
    'Authorization: ' . BEARER_PREFIX . $openaiApiKey,
];

$postData = [
    'model' => $sModel,
    'prompt' => $prompt,
    'temperature' => $dTemperature,
    'max_tokens' => $iMaxTokens,
    'top_p' => $top_p,
    'frequency_penalty' => $frequency_penalty,
    'presence_penalty' => $presence_penalty,
    'stop' => '[" Human:", " AI:"]',
];

if (!filter_var($openaiApiKey, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/\S+/']])) {
    throw new Exception('Invalid OpenAI API key');
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

$result = curl_exec($ch);
if ($result === false) {
    throw new Exception(curl_error($ch));
}

$decodedJson = json_decode($result, true);
if (!isset($decodedJson['choices'][0]['text'])) {
    throw new Exception('Unexpected response format');
}

echo $decodedJson['choices'][0]['text'];



?>
