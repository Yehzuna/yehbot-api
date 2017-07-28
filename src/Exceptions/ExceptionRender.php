<?php

namespace YezBot\Exceptions;

use GuzzleHttp\Exception\ClientException;
use Phalcon\Di;
use Phalcon\Http\Response;

/**
 * Class ExceptionRender
 *
 * @package YezBot\Exceptions
 */
class ExceptionRender
{
    /**
     * ExceptionRender constructor.
     *
     * @param \Exception $exception
     */
    public function __construct(\Exception $exception)
    {
        if ($exception instanceof ClientException) {
            return $this->client($exception);
        } elseif ($exception instanceof AppException) {
            return $this->app($exception);
        }

        return $this->generic($exception);
    }

    /**
     * @param AppException $exception
     * @return Response
     */
    public function app(AppException $exception)
    {
        $code = $exception->getCode();
        $status = $this->getStatus($code);

        $content = [
            'code'    => $code,
            'error'   => $status,
            'message' => $exception->getMessage(),
        ];
        $content = $this->debug($content, $exception);

        return $this->render($content, $code, $status);
    }

    /**
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
            'code'    => $exception->getCode(),
            'error'   => $data->error,
            'message' => $message,
        ];
        $content = $this->debug($content, $exception);

        return $this->render($content, $data->status, $this->getStatus($data->status));
    }

    /**
     * @param \Exception $exception
     * @return Response
     */
    public function generic(\Exception $exception)
    {
        $content = [
            'code'    => $exception->getCode(),
            'error'   => $this->getStatus($exception->getCode()),
            'message' => $exception->getMessage(),
        ];
        $content = $this->debug($content, $exception);

        return $this->render($content);
    }

    /**
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
                'type'  => get_class($exception),
                'file'  => $exception->getFile(),
                'line'  => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }

        return $content;
    }

    /**
     * @param array  $content
     * @param int    $code
     * @param string $status
     * @return Response
     */
    public function render(array $content, int $code = 500, string $status = 'Internal Server Error')
    {
        $response = new Response();
        $response->setStatusCode($code, $status);
        $response->setJsonContent($content);

        return $response;
    }

    /**
     * @param int $code
     * @return string
     */
    public function getStatus(int $code)
    {
        switch ($code) {
            case 100:
                $status = 'Continue';
                break;
            case 101:
                $status = 'Switching Protocols';
                break;
            case 200:
                $status = 'OK';
                break;
            case 201:
                $status = 'Created';
                break;
            case 202:
                $status = 'Accepted';
                break;
            case 203:
                $status = 'Non-Authoritative Information';
                break;
            case 204:
                $status = 'No Content';
                break;
            case 205:
                $status = 'Reset Content';
                break;
            case 206:
                $status = 'Partial Content';
                break;
            case 300:
                $status = 'Multiple Choices';
                break;
            case 301:
                $status = 'Moved Permanently';
                break;
            case 302:
                $status = 'Moved Temporarily';
                break;
            case 303:
                $status = 'See Other';
                break;
            case 304:
                $status = 'Not Modified';
                break;
            case 305:
                $status = 'Use Proxy';
                break;
            case 400:
                $status = 'Bad Request';
                break;
            case 401:
                $status = 'Unauthorized';
                break;
            case 402:
                $status = 'Payment Required';
                break;
            case 403:
                $status = 'Forbidden';
                break;
            case 404:
                $status = 'Not Found';
                break;
            case 405:
                $status = 'Method Not Allowed';
                break;
            case 406:
                $status = 'Not Acceptable';
                break;
            case 407:
                $status = 'Proxy Authentication Required';
                break;
            case 408:
                $status = 'Request Time-out';
                break;
            case 409:
                $status = 'Conflict';
                break;
            case 410:
                $status = 'Gone';
                break;
            case 411:
                $status = 'Length Required';
                break;
            case 412:
                $status = 'Precondition Failed';
                break;
            case 413:
                $status = 'Request Entity Too Large';
                break;
            case 414:
                $status = 'Request-URI Too Large';
                break;
            case 415:
                $status = 'Unsupported Media Type';
                break;
            case 500:
                $status = 'Internal Server Error';
                break;
            case 501:
                $status = 'Not Implemented';
                break;
            case 502:
                $status = 'Bad Gateway';
                break;
            case 503:
                $status = 'Service Unavailable';
                break;
            case 504:
                $status = 'Gateway Time-out';
                break;
            case 505:
                $status = 'HTTP Version not supported';
                break;
            default:
                $status = '';
                break;
        }

        return $status;
    }
}
