<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RoleMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $request->getAttribute('user_id');
        $roles = $user['roles'] ?? [];

        if (in_array('ROLE_ADMIN', $roles)) {
            return $handler->handle($request);
        } else {
            throw new HttpException(403, 'Access denied. This endpoint is only accessible to users with admin privileges.');
        }
    }
}
