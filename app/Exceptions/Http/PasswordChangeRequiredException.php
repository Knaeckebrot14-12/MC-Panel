<?php

namespace Pterodactyl\Exceptions\Http;

use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class PasswordChangeRequiredException extends HttpException implements HttpExceptionInterface
{
    /**
     * PasswordChangeRequiredException constructor.
     */
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct(Response::HTTP_BAD_REQUEST, 'You must set a new password before you can continue using the panel.', $previous);
    }
}
