<?php

namespace YezBot\Exceptions;

use Exception;
use GuzzleHttp\Exception\ClientException;
use Phalcon\Di;
use Phalcon\Http\Response;
use YezBot\Responses\AppResponse;

/**
 * Class ExceptionRender
 *
 * @package YezBot\Exceptions
 */
class ExceptionRender
{
    public $exception;

    /**
     * ExceptionRender constructor.
     *
     * @param Exception $exception
     */
    public function __construct(Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * Init the format
     *
     * @return Response
     */
    public function render()
    {
        $exception = $this->exception;

        if ($exception instanceof ClientException) {
            return $this->client($exception);
        } elseif ($exception instanceof AppException) {
            return $this->app($exception);
        }

        return $this->generic($exception);
    }

    /**
     * Format a intern exception
     *
     * @param AppException $exception
     * @return Response
     */
    public function app(AppException $exception)
    {
        $content = [
            'error'             => $exception->getError(),
            'error_description' => $exception->getMessage(),
        ];
        $content = $this->debug($content, $exception);

        return AppResponse::response($content, $exception->getCode());
    }

    /**
     * Format a ClientException exception (GuzzleHttp)
     *
     * @param ClientException $exception
     * @return Response
     */
    public function client(ClientException $exception)
    {
        $body = $exception->getResponse()->getBody()->getContents();
        $data = json_decode($body);

        $message = json_decode($data->message);
        if ($message instanceof \stdClass) {
            $message = $message->status;
        } else {
            $message = $data->message;
        }

        $content = [
            'error'             => $data->error,
            'error_description' => $message,
        ];
        $content = $this->debug($content, $exception);

        return AppResponse::response($content, $data->status);
    }

    /**
     * Format a generic exception
     *
     * @param Exception $exception
     * @return Response
     */
    public function generic(Exception $exception)
    {
        $content = [
            'error'             => 'server_error',
            'error_description' => $exception->getMessage(),
        ];
        $content = $this->debug($content, $exception);

        return AppResponse::response($content, 500);
    }

    /**
     * Add debug information to the response
     *
     * @param array      $content
     * @param \Exception $exception
     * @return array
     */
    public function debug(array $content, \Exception $exception)
    {
        $di = Di::getDefault();
        $config = $di->get('config');

        if ($config->debug) {
            $content = array_merge($content, [
                'code'  => $exception->getCode(),
                'type'  => get_class($exception),
                'file'  => $exception->getFile(),
                'line'  => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }

        return $content;
    }
}
