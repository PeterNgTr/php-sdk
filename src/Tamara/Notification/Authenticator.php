<?php

declare(strict_types=1);

namespace Tamara\Notification;

use Firebase\JWT\JWT;
use Symfony\Component\HttpFoundation\Request;
use Tamara\Notification\Exception\ForbiddenException;
use Throwable;

class Authenticator
{
    private const AUTHORIZATION = 'Authorization';
    /**
     * @var string
     */
    private $tokenKey;

    public function __construct(string $tokenKey)
    {
        $this->tokenKey = $tokenKey;
    }

    /**
     * @param Request $request
     *
     * @throws ForbiddenException
     */
    public function authenticate(Request $request): void
    {
        if (!$request->headers->has(self::AUTHORIZATION)) {
            throw new ForbiddenException('Access denied.');
        }

        $token = $this->getBearerToken($request->headers->get(self::AUTHORIZATION));

        try {
            $tokenData = $this->decode($token);
        } catch (Throwable $exception) {
            throw new ForbiddenException('Access denied.');
        }
    }

    protected function getBearerToken(string $authorizationHeader): string
    {
        if (!empty($authorizationHeader) && preg_match('/Bearer\s(\S+)/', $authorizationHeader, $matches)) {
            return $matches[1];
        }

        throw new ForbiddenException('Access denied.');
    }

    /**
     * @param string $token
     *
     * @return object
     */
    protected function decode(string $token)
    {
        return JWT::decode($token, $this->tokenKey, ['HS256']);
    }
}
