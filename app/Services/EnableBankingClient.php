<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class EnableBankingClient
{
    public function isConfigured(): bool
    {
        return filled(config('services.enablebanking.app_id'))
            && filled(config('services.enablebanking.private_key'));
    }

    public function createAuthorizationUrl(string $redirectUrl, ?string $aspspName = null): array
    {
        $payload = [
            'access' => [
                'balances' => true,
                'transactions' => true,
                'valid_until' => now()->addDays(90)->toIso8601String(),
            ],
            'aspsp' => [
                'name' => $aspspName ?: 'Sandbox',
                'country' => config('services.enablebanking.country', 'GB'),
            ],
            'state' => bin2hex(random_bytes(16)),
            'redirect_url' => $redirectUrl,
            'psu_type' => 'personal',
        ];

        $response = $this->request()->post('/auth', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Enable Banking authorization failed: '.$response->body());
        }

        return $response->json();
    }

    public function createSession(string $code): array
    {
        $response = $this->request()->post('/sessions', [
            'code' => $code,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Enable Banking session failed: '.$response->body());
        }

        return $response->json();
    }

    public function getAccountTransactions(string $accountUid, ?string $dateFrom = null): array
    {
        $query = array_filter([
            'date_from' => $dateFrom,
        ]);

        $response = $this->request()->get("/accounts/{$accountUid}/transactions", $query);

        if (! $response->successful()) {
            throw new RuntimeException('Enable Banking transactions failed: '.$response->body());
        }

        return $response->json();
    }

    public function getAccountBalances(string $accountUid): array
    {
        $response = $this->request()->get("/accounts/{$accountUid}/balances");

        if (! $response->successful()) {
            throw new RuntimeException('Enable Banking balances failed: '.$response->body());
        }

        return $response->json();
    }

    private function request()
    {
        return Http::baseUrl(rtrim(config('services.enablebanking.base_url'), '/'))
            ->withToken($this->makeJwt())
            ->acceptJson()
            ->asJson();
    }

    private function makeJwt(): string
    {
        $appId = config('services.enablebanking.app_id');
        $privateKey = $this->privateKey();

        $header = $this->base64UrlEncode(json_encode([
            'typ' => 'JWT',
            'alg' => 'RS256',
            'kid' => $appId,
        ]));

        $now = time();
        $payload = $this->base64UrlEncode(json_encode([
            'iss' => 'enablebanking.com',
            'aud' => 'api.enablebanking.com',
            'iat' => $now,
            'exp' => $now + 3600,
        ]));

        $data = $header.'.'.$payload;
        $signature = '';

        $ok = openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        if (! $ok) {
            throw new RuntimeException('Unable to sign Enable Banking JWT.');
        }

        return $data.'.'.$this->base64UrlEncode($signature);
    }

    private function privateKey()
    {
        $configured = config('services.enablebanking.private_key');

        if (is_string($configured) && is_file($configured)) {
            $configured = file_get_contents($configured);
        }

        $key = openssl_pkey_get_private((string) $configured);
        if ($key === false) {
            throw new RuntimeException('Invalid Enable Banking private key.');
        }

        return $key;
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
