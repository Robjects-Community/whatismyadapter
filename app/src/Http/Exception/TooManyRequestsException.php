<?php
declare(strict_types=1);

namespace App\Http\Exception;

use Cake\Http\Exception\HttpException;
use Throwable;

class TooManyRequestsException extends HttpException
{
    protected int $_defaultCode = 429;
    protected ?int $retryAfter;

    /**
     * Constructor
     *
     * @param string|null $message If no message is given 'Too Many Requests' will be the message
     * @param int|null $code Status code, defaults to 429
     * @param int|null $retryAfter The number of seconds after which the client should retry
     * @param \Throwable|null $previous The previous exception.
     */
    public function __construct(
        ?string $message = null,
        ?int $code = null,
        ?int $retryAfter = null,
        ?Throwable $previous = null,
    ) {
        $this->retryAfter = $retryAfter;
        if (empty($message)) {
            $message = __('Too Many Requests');
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the retry after value.
     *
     * @return int|null
     */
    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
