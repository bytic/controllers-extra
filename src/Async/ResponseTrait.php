<?php

namespace ByTIC\Controllers\Behaviors\Async;

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
        ini_set('html_errors', 0);
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
        header("Content-type: text/x-json");
        echo(json_encode($this->response_values));
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
