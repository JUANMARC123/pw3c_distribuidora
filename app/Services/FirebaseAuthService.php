<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Obtiene un access_token OAuth2 (rol admin) para la Realtime Database a
 * partir de una cuenta de servicio de Firebase. Este token permite que el
 * backend escriba en nodos protegidos por reglas (p. ej. viajes_activos),
 * sin necesidad de exponer la base.
 *
 * Si no hay credenciales configuradas, devuelve null y el backend sigue
 * usando la URL sin token (util para desarrollo local con reglas abiertas).
 */
class FirebaseAuthService
{
    private const TOKEN_ENDPOINT = 'https://oauth2.googleapis.com/token';
    private const SCOPE = 'https://www.googleapis.com/auth/firebase.database https://www.googleapis.com/auth/userinfo.email';

    protected ?string $credentialsPath;

    public function __construct()
    {
        $this->credentialsPath = config('firebase.credentials');
    }

    public function accessToken(): ?string
    {
        $credentials = $this->loadCredentials();

        if (!$credentials) {
            return null;
        }

        return Cache::remember('firebase_rtdb_access_token', now()->addMinutes(50), function () use ($credentials) {
            return $this->requestAccessToken($credentials);
        });
    }

    protected function loadCredentials(): ?array
    {
        $path = $this->credentialsPath;

        if (!$path || !is_file($path)) {
            Log::warning('Firebase: no se encontro el archivo de credenciales', ['path' => $path]);

            return null;
        }

        $json = json_decode((string) file_get_contents($path), true);

        if (!is_array($json) || empty($json['client_email']) || empty($json['private_key'])) {
            Log::error('Firebase: credenciales de cuenta de servicio invalidas', ['path' => $path]);

            return null;
        }

        return $json;
    }

    protected function requestAccessToken(array $credentials): ?string
    {
        try {
            $assertion = $this->buildSignedJwt($credentials);

            $response = Http::asForm()->timeout(10)->post(self::TOKEN_ENDPOINT, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $assertion,
            ]);

            if ($response->failed()) {
                Log::error('Firebase: fallo al obtener access_token', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            return $response->json('access_token');
        } catch (\Throwable $e) {
            Log::error('Firebase: excepcion al obtener access_token', ['error' => $e->getMessage()]);

            return null;
        }
    }

    protected function buildSignedJwt(array $credentials): string
    {
        $now = time();

        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claims = [
            'iss' => $credentials['client_email'],
            'scope' => self::SCOPE,
            'aud' => self::TOKEN_ENDPOINT,
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        $segments = [
            $this->base64UrlEncode(json_encode($header)),
            $this->base64UrlEncode(json_encode($claims)),
        ];
        $signingInput = implode('.', $segments);

        $signature = '';
        $signed = openssl_sign($signingInput, $signature, $credentials['private_key'], 'SHA256');

        if (!$signed) {
            throw new \RuntimeException('No se pudo firmar el JWT de Firebase');
        }

        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    protected function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
