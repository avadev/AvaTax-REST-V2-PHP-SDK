<?php
namespace Avalara;

class LogInformation
{
    private $startTime;
    private $logRequestAndResponseBody;
    public $httpMethod;
    public $headerCorrelationId;
    public $requestDetails;
    public $responseDetails;
    public $requestURI;
    public $totalExecutionTime;
    public $statusCode;
    public $timestamp;
    public $exceptionMessage;

    public function setStartTime($startTime)
    {
        $this-> startTime = $startTime;
    }

    public function populateRequestInfo($verb, $apiUrl, $guzzleParams, $logRequestAndResponseBody)
    {
        $this-> timestamp = gmdate("Y/m/d H:i:s");
        $this-> httpMethod = $verb;
        $this-> requestURI = $apiUrl;
        $this-> logRequestAndResponseBody = $logRequestAndResponseBody;
        if($this-> logRequestAndResponseBody)
        {
            $this-> requestDetails = $guzzleParams['body'];
        }
    }

    public function populateResponseInfo($jsonBody, $response)
    {
        $this-> populateCommonResponseInfo($response);
        if($this-> logRequestAndResponseBody)
        {
            $this-> responseDetails = $jsonBody;
        }
    }

    public function populateErrorInfoWithMessageAndBody($errorMessage, $response) 
    {
        $this-> populateCommonResponseInfo($response);
        $this-> exceptionMessage = $errorMessage;
        $this-> statusCode = 500;
    }

    public function populateErrorInfo($e)
    {
        $this-> populateTotalExecutionTime();
        $this-> statusCode = $e-> getCode();
        $this-> headerCorrelationId = $e-> getResponse()-> getHeader('x-correlation-id')[0];
        $this-> exceptionMessage = $e-> getResponse()->getBody()->getContents();
    }

    private function populateCommonResponseInfo($response)
    {
        $this-> populateTotalExecutionTime();
        $this-> headerCorrelationId = $response-> getHeader('x-correlation-id')[0];
        $this-> statusCode = $response-> getStatusCode();
    }

    private function populateTotalExecutionTime() 
    {
        $time_elapsed_secs = microtime(true) - $this-> startTime;
        $milliseconds = round($time_elapsed_secs * 1000);
        $this-> totalExecutionTime = $milliseconds; 
    }    
}
?>