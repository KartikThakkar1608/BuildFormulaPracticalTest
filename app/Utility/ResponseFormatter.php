<?php

namespace App\Utility;

use Log;
use \Symfony\Component\HttpFoundation\Response;

class ResponseFormatter {

    const SUCCESS = 'Success';
    const FAILURE = 'Failure';
    const ERROR = 'Error';

    public function __construct() {

    }

    public static function responseSuccess($params = []) {

        $response = array(
            'STATUS' => self::SUCCESS,
            'STATUS_CODE' => Response::HTTP_OK,
            'MESSAGE' => $params['message'],
            'DATA' => isset($params['data']) ? $params['data'] : [],
        );

        if(isset($params['data']['meta'])){
            $response['meta'] = $params['data']['meta'];
            unset($params['data']['meta']);
        }

        if(isset($params['data']['links'])){
            $response['links'] = $params['data']['links'];
            unset($params['data']['links']);
        }

        if(isset($params['data']['paginate_data'])){
            $response['data'] =  $params['data']['paginate_data'];
        }
        return $response;
    }

    public static function responseFailure($params = []) {
        $response = array(
            'STATUS' => self::FAILURE,
            'STATUS_CODE' => Response::HTTP_BAD_REQUEST,
            'MESSAGE' => $params['message'],
            'DATA' => NULL,
        );
        Log::error($response);
        return $response;
    }

    public static function responseUnauthorized($params = []) {
        $response = array(
            'STATUS' => self::FAILURE,
            'STATUS_CODE' => Response::HTTP_UNAUTHORIZED,
            'MESSAGE' => $params['message'],
            'DATA' => NULL,
        );
        Log::error($response);
        return $response;
    }

    public static function responseBadRequest($params = []) {
        $response = array(
            'STATUS' => self::ERROR,
            'STATUS_CODE' => Response::HTTP_BAD_REQUEST,
            'MESSAGE' => $params['message'],
            'DATA' => (object) (isset($params['data']) ? $params['data'] : []),
        );

        Log::error($response);
        return $response;
    }

    public static function responseNotFound($params = []) {
        $response = array(
            'STATUS' => self::ERROR,
            'STATUS_CODE' => Response::HTTP_NOT_FOUND,
            'MESSAGE' => $params['message'],
            'DATA' => (object) (isset($params['data']) ? $params['data'] : []),
        );

        Log::error($response);
        return $response;
    }

    public static function responseServerError($params = []) {

        $response = array(
            'STATUS' => self::ERROR,
            'STATUS_CODE' => Response::HTTP_INTERNAL_SERVER_ERROR,
            'message' => $params['message'],
            'data' => (object) (isset($params['data']) ? $params['data'] : []),
        );

        Log::error($response);
        return $response;
    }

    public static function responseNotAcceptable($params = []) {
        $response = array(
            'STATUS' => self::ERROR,
            'STATUS_CODE' => Response::HTTP_NOT_ACCEPTABLE,
            'MESSAGE' => $params['message'],
            'DATA' => (object) (isset($params['data']) ? $params['data'] : []),
        );

        Log::error($response);
        return $response;
    }

    public function validation_error($validator) {
        $this->success = 0;
        $this->message = $validator->errors()->first();
        $this->statusCode = Response::HTTP_NOT_FOUND;
        return $this->render($this->success, $this->message, $this->statusCode);
    }

    public function render($success, $message, $status, $data = null) {
        return [
            'STATUS' => $success,
            'STATUS_CODE' => $message,
            'MESSAGE' => $status,
            'DATA' => $data,
        ];
    }
}
