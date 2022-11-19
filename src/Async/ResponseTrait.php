<?php

declare(strict_types=1);

namespace ByTIC\Controllers\Behaviors\Async;

use Nip\Http\Response\JsonResponse;

/**
 * Class ResponseTrait
 * @package ByTIC\Controllers\Behaviors\Async
 */
trait ResponseTrait
{
    /**
     * @var array
     */
    protected $response_values = [];
    protected $response_type = 'json';

    /**
     * ResponseTrait constructor.
     */
    public function __construct()
    {
        parent::__construct();
        ini_set('html_errors', "0");
    }

    /**
     * @param $code
     * @param bool $message
     * @param array $params
     */
    protected function sendResponseCode($code, $message = false, $params = [])
    {
        $params['code'] = $code;
        $type = 'error';
        $messageGeneric = "";

        switch ($code) {
            case '400':
                $messageGeneric = 'Bad Request';
                break;
            case '401':
                $messageGeneric = 'Request missing api token';
                break;
            case '4011':
                $messageGeneric = 'Invalid API Key';
                break;
            case '403':
                $messageGeneric = 'You don\'t have permission for this resource';
                break;
            case '404':
                $messageGeneric = 'Invalid API call';
                break;
            case '4041':
                $messageGeneric = 'Requested item not found';
                break;
        }

        $message = $message === false ? $messageGeneric : $message;

        $this->sendResponseMessage($type, $message, $params);
    }

    /**
     * @param bool $request
     * @param bool $key
     * @return \Nip\Records\AbstractModels\Record|void
     */
    protected function checkItem($request = false, $key = false)
    {
        $return = parent::checkItem($request, $key);
        if ($return) {
            $this->checkAccess($return);

            return $return;
        }

        $this->dispatchAccessDeniedResponse();
    }

    /** @noinspection PhpUnusedParameterInspection
     * @param $item
     * @return bool
     */
    protected function checkAccess($item)
    {
        return true;
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     * @inheritdoc
     */
    protected function dispatchNotFoundResponse()
    {
        $this->sendResponseCode('4041');
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     * @inheritdoc
     */
    protected function dispatchAccessDeniedResponse()
    {
        $this->sendResponseCode('403');
    }

    protected function checkItemError()
    {
        $this->sendResponseCode('4041');
    }

    protected function afterAction()
    {
        $this->output();
    }

    /**
     * @param $message
     * @param array $params
     */
    public function sendSuccess($message, $params = [])
    {
        $this->sendResponseMessage('success', $message, $params);
    }

    /**
     * @param $type
     * @param $message
     * @param array $params
     */
    public function sendResponseMessage($type, $message, $params = [])
    {
        $response = $params;
        $response['type'] = $type;
        $response['message'] = $message;

        $this->setResponseValues($response);
        $this->output();
    }

    /**
     * @param array $values
     */
    public function setResponseValues($values)
    {
        $this->response_values = $values;
    }

    /**
     * @param $message
     * @param array $params
     */
    public function sendError($message, $params = [])
    {
        $this->sendResponseMessage('error', $message, $params);
    }

    /**
     * @param string $response
     */
    protected function output($response = '')
    {
        if ($response) {
            $this->response_values = $response;
        }
        $method = 'output' . strtoupper($this->response_type);
        $this->$method();
        exit();
    }

    protected function outputJSON()
    {
        $this->setResponse(new JsonResponse($this->response_values));
        $this->getResponse()->send();
    }

    protected function outputTXT()
    {
        header("Content-type: text/plain");
        echo($this->response_values);
    }

    protected function outputHTML()
    {
        header("Content-type: text/html");
        echo($this->response_values);
    }
}
