<?php

namespace App\Services;

use App\Models\MonimeCredential;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MonimeApiService
{
    private const DEFAULT_BASE_URL = 'https://api.monime.io';

    private const DEFAULT_API_VERSION = 'caph.2025-08-23';

    /**
     * Enable all Monime checkout channels (card, bank, mobile money, wallet).
     *
     * @param  array{enable_card?: bool|null, enable_bank?: bool|null, enable_momo?: bool|null, enable_wallet?: bool|null}  $channels
     * @return array<string, array{disable: bool}>
     */
    public function buildPaymentOptions(array $channels = []): array
    {
        $isEnabled = static fn (?bool $flag): bool => $flag !== false;

        return [
            'card' => ['disable' => ! $isEnabled($channels['enable_card'] ?? null)],
            'bank' => ['disable' => ! $isEnabled($channels['enable_bank'] ?? null)],
            'momo' => ['disable' => ! $isEnabled($channels['enable_momo'] ?? null)],
            'wallet' => ['disable' => ! $isEnabled($channels['enable_wallet'] ?? null)],
        ];
    }

    /**
     * Normalize token pasted as "Bearer xxx" or with accidental whitespace.
     */
    private function normalizeMonimeSecret(string $value): string
    {
        $t = trim($value);
        if ($t === '') {
            return '';
        }

        return trim(preg_replace('/^bearer\s+/i', '', $t) ?? '');
    }

    /**
     * Load enabled Monime credentials from the database (admin / Orchid).
     *
     * @return array{api_token: string, space_id: string, api_version: string}
     */
    private function resolvedCredentials(): array
    {
        $credential = MonimeCredential::query()
            ->where('enabled', true)
            ->first();

        if (! $credential) {
            throw new RuntimeException(
                'Monime credentials are not configured. Enable a credential row in admin (Monime Credentials).'
            );
        }

        $token = $this->normalizeMonimeSecret((string) $credential->api_token);
        $spaceId = trim((string) $credential->space_id);
        $apiVersion = trim((string) ($credential->api_version ?: ''));
        $apiVersion = $apiVersion !== '' ? $apiVersion : self::DEFAULT_API_VERSION;

        if ($token === '' || $spaceId === '') {
            throw new RuntimeException(
                'Monime API token or Space ID is empty in the database. Re-save the credential in admin with a valid token and Space ID from the Monime dashboard.'
            );
        }

        return [
            'api_token' => $token,
            'space_id' => $spaceId,
            'api_version' => $apiVersion,
        ];
    }

    private function baseUrl(): string
    {
        return rtrim(config('services.monime.base_url', self::DEFAULT_BASE_URL), '/');
    }

    private function request(string $method, string $path, array $payload = [], bool $idempotent = false): array
    {
        $cred = $this->resolvedCredentials();

        $headers = [
            'Authorization' => 'Bearer '.$cred['api_token'],
            'Monime-Space-Id' => $cred['space_id'],
            'Monime-Version' => $cred['api_version'],
            'Accept' => 'application/json',
        ];

        if ($idempotent) {
            $headers['Idempotency-Key'] = (string) Str::uuid();
        }

        $url = $this->baseUrl().$path;
        $pending = Http::withHeaders($headers);

        $response = match ($method) {
            'get' => $pending->get($url, $payload),
            'post' => $pending->post($url, $payload),
            default => throw new RuntimeException("Unsupported Monime HTTP method: {$method}"),
        };

        return $this->normalizeResponse($response);
    }

    /**
     * Build a human-readable message from a failed Monime HTTP response.
     * Monime error payloads vary; previously we only read messages.0 / message,
     * which produced the vague "Monime request failed." for many real responses.
     */
    private function failedMonimeMessage(Response $response): string
    {
        $status = $response->status();
        $json = $response->json();
        $parts = [];

        if (is_array($json)) {
            foreach ([
                'messages.0',
                'message',
                'error',
                'error.message',
                'detail',
                'title',
            ] as $path) {
                $val = data_get($json, $path);
                if ($val === null || $val === '') {
                    continue;
                }
                if (is_string($val)) {
                    $parts[] = $val;
                } elseif (is_scalar($val)) {
                    $parts[] = (string) $val;
                }
            }

            $messages = data_get($json, 'messages');
            if (is_array($messages)) {
                foreach ($messages as $m) {
                    if (is_string($m)) {
                        $parts[] = $m;
                    } elseif (is_array($m)) {
                        foreach (['text', 'message', 'description', 'code'] as $key) {
                            if (! empty($m[$key])) {
                                $parts[] = is_string($m[$key])
                                    ? $m[$key]
                                    : json_encode($m[$key], JSON_UNESCAPED_UNICODE);
                            }
                        }
                    }
                }
            }

            $errors = data_get($json, 'errors');
            if (is_array($errors)) {
                foreach ($errors as $field => $msgs) {
                    if (is_array($msgs)) {
                        foreach ($msgs as $one) {
                            $parts[] = "{$field}: {$one}";
                        }
                    } elseif (is_string($msgs)) {
                        $parts[] = "{$field}: {$msgs}";
                    }
                }
            }
        }

        $combined = trim(implode(' ', array_unique(array_filter($parts))));
        if ($combined !== '') {
            return $combined;
        }

        $plain = trim(preg_replace('/\s+/', ' ', strip_tags($response->body())) ?? '');
        $snippet = $plain !== '' ? Str::limit($plain, 280, '…') : '';

        if ($snippet !== '') {
            return "Monime API error (HTTP {$status}): {$snippet}";
        }

        return "Monime API request failed (HTTP {$status}).";
    }

    private function normalizeResponse(Response $response): array
    {
        $json = $response->json();

        if ($response->failed()) {
            throw new RuntimeException(
                $this->failedMonimeMessage($response),
                $response->status()
            );
        }

        return is_array($json) ? $json : [];
    }

    public function createCheckoutSession(array $payload): array
    {
        return $this->request('post', '/v1/checkout-sessions', $payload, true);
    }

    public function getCheckoutSession(string $sessionId): array
    {
        return $this->request('get', "/v1/checkout-sessions/{$sessionId}");
    }

    public function getPayment(string $paymentId): array
    {
        return $this->request('get', "/v1/payments/{$paymentId}");
    }

    public function listMomos(string $country = 'SL'): array
    {
        return $this->request('get', '/v1/momos', ['country' => $country, 'limit' => 50]);
    }

    public function listBanks(string $country = 'SL'): array
    {
        return $this->request('get', '/v1/banks', ['country' => $country, 'limit' => 50]);
    }
}
