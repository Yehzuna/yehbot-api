<?php

namespace YezBot\Responses;

use Phalcon\Http\Response;

class AppResponse
{


    public function response(array $content, int $code = 200, string $status = 'Ok')
    {
        $response = new Response();
        $response->setStatusCode($code, $status);
        $response->setJsonContent($content);

        return $response;
    }

}
