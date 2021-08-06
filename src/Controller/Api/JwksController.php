<?php

declare(strict_types=1);

namespace FriendsOfBabba\Core\Controller\Api;

use Firebase\JWT\JWT;

/**
 * Jwks Controller
 */
class JwksController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->Authentication->allowUnauthenticated(['index']);
    }
    /**
     * Beside from sharing the public key file to external application,
     * you can distribute it via a JWKS endpoint by configuring your app.
     */
    public function index()
    {
        $pubKey = file_get_contents(CONFIG . './jwt.pem');
        $res = openssl_pkey_get_public($pubKey);
        $detail = openssl_pkey_get_details($res);
        $key = [
            'kty' => 'RSA',
            'alg' => 'RS256',
            'use' => 'sig',
            'e' => JWT::urlsafeB64Encode($detail['rsa']['e']),
            'n' => JWT::urlsafeB64Encode($detail['rsa']['n']),
        ];
        $keys['keys'][] = $key;

        $this->viewBuilder()->setClassName('Json');
        $this->set(compact('keys'));
        $this->viewBuilder()->setOption('serialize', 'keys');
    }
}
