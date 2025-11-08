<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FirebaseMessagingService {
  protected Client $http;

  public function __construct(?Client $http = null) {
    $this->http = $http ?? new Client();
  }

  public function send(array $tokens, string $title, string $body, array $data = []): void {
    $tokens = array_values(array_filter(array_unique(array_map('trim', $tokens))));

    if (empty($tokens)) {
      return;
    }

    $credentials = $this->credentials();
    $projectId = $credentials['project_id'] ?? null;

    if (!$projectId) {
      Log::warning('Firebase project ID missing from credentials.');
      return;
    }

    $accessToken = $this->accessToken($credentials);

    if (!$accessToken) {
      return;
    }

    foreach ($tokens as $token) {
      $payload = [
        'message' => [
          'token' => $token,
          'notification' => [
            'title' => $title,
            'body' => $body,
          ],
          'data' => $this->stringifyData($data),
        ],
      ];

      try {
        $this->http->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
          'headers' => [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type'  => 'application/json',
          ],
          'body' => json_encode($payload),
          'timeout' => 5,
        ]);
      } catch (GuzzleException $e) {
        Log::warning('Failed to send Firebase notification', [
          'token' => $token,
          'error' => $e->getMessage(),
        ]);
      }
    }
  }

  protected function credentials(): array {
    $path = config('firebase.credentials');

    if (!$path || !is_readable($path)) {
      Log::warning('Firebase credentials file not readable.', ['path' => $path]);
      return [];
    }

    $content = file_get_contents($path);

    return json_decode($content, true) ?? [];
  }

  protected function accessToken(array $credentials): ?string {
    $cacheKey = 'firebase.messaging.access_token';

    if (Cache::has($cacheKey)) {
      return Cache::get($cacheKey);
    }

    $clientEmail = $credentials['client_email'] ?? null;
    $privateKey = $credentials['private_key'] ?? null;
    $tokenUri = $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token';

    if (!$clientEmail || !$privateKey) {
      Log::warning('Firebase credentials missing client_email or private_key.');
      return null;
    }

    $jwt = $this->createJwt($clientEmail, $privateKey, $tokenUri);

    if (!$jwt) {
      return null;
    }

    try {
      $response = $this->http->post($tokenUri, [
        'form_params' => [
          'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
          'assertion'  => $jwt,
        ],
        'timeout' => 5,
      ]);
    } catch (GuzzleException $e) {
      Log::warning('Failed to fetch Firebase access token', [
        'error' => $e->getMessage(),
      ]);
      return null;
    }

    $body = json_decode((string) $response->getBody(), true) ?? [];
    $token = $body['access_token'] ?? null;
    $expiresIn = (int) ($body['expires_in'] ?? 3600);

    if ($token) {
      Cache::put($cacheKey, $token, now()->addSeconds(max(60, $expiresIn - 60)));
    }

    return $token;
  }

  protected function createJwt(string $clientEmail, string $privateKey, string $tokenUri): ?string {
    $now = time();

    $payload = [
      'iss'   => $clientEmail,
      'scope' => config('firebase.messaging_scope', 'https://www.googleapis.com/auth/firebase.messaging'),
      'aud'   => $tokenUri,
      'iat'   => $now,
      'exp'   => $now + 3600,
    ];

    $header = [
      'alg' => 'RS256',
      'typ' => 'JWT',
    ];

    $segments = [
      $this->base64Url(json_encode($header)),
      $this->base64Url(json_encode($payload)),
    ];

    $input = implode('.', $segments);
    $signature = '';

    $privateKey = str_replace("\\n", "\n", $privateKey);

    if (!openssl_sign($input, $signature, $privateKey, 'sha256')) {
      Log::warning('Failed to sign Firebase JWT.');
      return null;
    }

    $segments[] = $this->base64Url($signature);

    return implode('.', $segments);
  }

  protected function base64Url(string $value): string {
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
  }

  protected function stringifyData(array $data): array {
    $stringified = [];

    foreach ($data as $key => $value) {
      if (is_array($value) || is_object($value)) {
        $stringified[$key] = json_encode($value);
        continue;
      }

      $stringified[$key] = (string) $value;
    }

    return $stringified;
  }
}
