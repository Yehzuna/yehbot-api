<?php

namespace YezBot\Responses;

use Phalcon\Http\Response;

class AppResponse
{
    /**
     * Send a json response
     *
     * @param array  $content
     * @param int    $code
     * @return Response
     */
    static public function response(array $content, int $code)
    {
        $response = new Response();
        $response->setStatusCode($code);
        $response->setJsonContent($content);

        return $response;
    }
}
