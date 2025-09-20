<?php
declare(strict_types=1);

namespace App\Error;

use App\Http\Exception\TooManyRequestsException;
use Cake\Error\Renderer\WebExceptionRenderer;
use Psr\Http\Message\ResponseInterface;

/**
 * Class AppExceptionRenderer
 *
 * Custom exception renderer for handling specific application exceptions.
 * Extends the WebExceptionRenderer to provide custom rendering logic for exceptions.
 */
class AppExceptionRenderer extends WebExceptionRenderer
{
    /**
     * Render method to handle exceptions and generate a response.
     *
     * This method checks if the exception is an instance of TooManyRequestsException.
     * If so, it sets the response status code, prepares the view template, and sets
     * various data to be serialized in the response. Otherwise, it falls back to the
     * parent render method.
     *
     * @return \Psr\Http\Message\ResponseInterface The response object with the rendered error page.
     */
    public function render(): ResponseInterface
    {
        $exception = $this->error;
        $code = $this->getHttpCode($exception);

        if ($exception instanceof TooManyRequestsException) {
            $this->controller->setResponse($this->controller->getResponse()->withStatus($code));
            $this->controller->viewBuilder()->setTemplatePath('Error');

            $message = $exception->getMessage();
            $url = $this->controller->getRequest()->getRequestTarget();

            $this->controller->set([
                'message' => $message,
                'url' => h($url),
                'error' => $exception,
                'code' => $code,
                'exceptions' => [$exception],
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            $this->controller->viewBuilder()->setOption('serialize', [
                'message',
                'url',
                'code',
                'exceptions',
                'file',
                'line',
            ]);

            return $this->_outputMessage('error429');
        }

        return parent::render();
    }
}
