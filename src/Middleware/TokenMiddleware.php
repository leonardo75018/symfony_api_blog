<?php

namespace App\Middleware;

use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class TokenMiddleware implements MiddlewareInterface
{
    protected $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $jwt = $request->getHeaderLine('token');
        if (!empty($jwt)) {
            $jwtParts = explode(' ', $jwt);
            if (count($jwtParts) === 2 && $jwtParts[0] === 'Bearer') {
                $jwt = $jwtParts[1];
            } else {
                throw new HttpException(403, 'Invalid Authorization header format. Format should be "Authorization: Bearer <token>"');
            }
        } else {
            throw new HttpException(403, 'Missing Authorization header. Format should be "Authorization: Bearer <token>"');
        }

        try {
            $decoded = JWT::decode($jwt, $this->params->get('jwt_secret'), ['HS256']);
        } catch (\Exception $e) {
            throw new HttpException(403, $e->getMessage());
        }

        $request = $request->withAttribute('user_id', $decoded->user_id);

        return $handler->handle($request);
    }
}
