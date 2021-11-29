<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class SMSNotifier_Sms77_Provider implements SMSNotifier_ISMSProvider_Model {

    private $userName;
    private $password;
    private $parameters = array();

    const SERVICE_URI = 'https://gateway.sms77.io/api/';
    private static $REQUIRED_PARAMETERS = array(
        array('name' => 'ApiKey', 'label' => 'API Key', 'type' => 'text'),
    );

    /**
     * Function to get provider name
     * @return <String> provider name
     */
    public function getName() {
        return 'sms77';
    }

    /**
     * Function to get required parameters other than (userName, password)
     * @return <array> required parameters list
     */
    public function getRequiredParams() {
        return self::$REQUIRED_PARAMETERS;
    }

    /**
     * Function to get service URL to use for a given type
     * @param <String> $type like SEND, PING, QUERY
     */
    public function getServiceURL($type = false) {
        if ($type) {
            switch (strtoupper($type)) {
                case self::SERVICE_SEND:
                    return self::SERVICE_URI . 'sms';
                case self::SERVICE_QUERY:
                    return self::SERVICE_URI . 'status';
            }
        }
        return false;
    }

    /**
     * Function to set authentication parameters
     * @param <String> $userName
     * @param <String> $password
     */
    public function setAuthParameters($userName, $password) {
        $this->userName = $userName;
        $this->password = $password;
    }

    /**
     * Function to set non-auth parameter.
     * @param <String> $key
     * @param <String> $value
     */
    public function setParameter($key, $value) {
        $this->parameters[$key] = $value;
    }

    /**
     * Function to get parameter value
     * @param <String> $key
     * @param <String> $defaultValue
     * @return <String> value/$default value
     */
    public function getParameter($key, $defaultValue = false) {
        if (isset($this->parameters[$key])) {
            return $this->parameters[$key];
        }
        return $defaultValue;
    }

    /**
     * Function to handle SMS Send operation
     * @param <String> $message
     * @param <Mixed> $toNumbers One or Array of numbers
     */
    public function send($message, $toNumbers) {
        $httpClient = $this->initNetClient(self::SERVICE_SEND);
        $to = is_array($toNumbers) ? implode(',', $toNumbers) : $toNumbers;
        $params = array('json' => 1, 'text' => $message, 'to' => $to);
        $response = $httpClient->doPost($params);
        $response = json_decode($response);

        $results = array();
        foreach ($response->messages as $msg) {
            $success = 100 === $msg->error;
            $results[] = array(
                'error' => !$success,
                'status' => $success ? self::MSG_STATUS_PROCESSING : self::MSG_STATUS_ERROR,
                'statusmessage' => $this->toHumanReadableMessageError($msg->error),
                'to' => $msg->recipient
            );
        }
        return $results;
    }

    private function toHumanReadableMessageError($error) {
        switch($error) {
            case 100:
                return 'The SMS was accepted by the gateway.';
            case 101:
                return 'The transmission to at least one recipient failed.';
            case 201:
                return 'The sender is invalid. A maximum of 11 alphanumeric or 16 numeric characters are allowed.';
            case 202:
                return 'The recipient number is invalid.';
            case 301:
                return 'The variable to is not set.';
            case 305:
                return 'The variable text is not set.';
            case 401:
                return 'The variable text is too long.';
            case 402:
                return 'The Reload Lock prevents sending this SMS as it has already been sent within the last 180 seconds.';
            case 403:
                return 'The maximum limit for this number per day has been reached.';
            case 500:
                return 'The account has too little credit available.';
            case 600:
                return 'The carrier delivery failed.';
            case 700:
                return 'An unknown error occurred.';
            case 900:
                return 'The authentication failed. Please check your API key.';
            case 901:
                return 'The verification of the signing hash failed.';
            case 902:
                return 'The API key has no access rights to this endpoint.';
            case 903:
                return 'The server IP is wrong.';
            default:
                return (string)$error;
        }
    }

    private function initNetClient($serviceURL) {
        $serviceURL = $this->getServiceURL($serviceURL);
        $httpClient = new Vtiger_Net_Client($serviceURL);
        $apiKey = $this->getParameter('ApiKey');
        $headers = array('SentWith' => 'Vtiger', 'X-Api-Key' => $apiKey);
        $httpClient->setHeaders($headers);
        return $httpClient;
    }

    /**
     * Function to get query for status using messgae id
     * @param <Number> $messageId
     */
    public function query($messageId) {
        $httpClient = $this->initNetClient(self::SERVICE_QUERY);
        $params = array('msg_id' => $messageId);
        $response = $httpClient->doGet($params);
        $response = trim($response);
        $lines = explode(PHP_EOL, $response);
        $statusMessage = '';
        $error = false;
        $needLookup = 0;
        $status = $lines[0];

        switch ($status) {
            case 'DELIVERED':
                $statusMessage = 'Message delivered';
                break;
            case 'NOTDELIVERED':
                $statusMessage = 'Message not delivered';
                break;
            case 'BUFFERED':
                $statusMessage = 'Message sent but not yet received';
                $needLookup = 1;
                break;
            case 'TRANSMITTED':
                $statusMessage = 'Message is on its way';
                $needLookup = 1;
                break;
            case 'ACCEPTED':
                $statusMessage = 'Message should be transmitted soon';
                $needLookup = 1;
                break;
            case 'EXPIRED':
                $statusMessage = 'Message expired';
                break;
            case 'REJECTED':
                $statusMessage = 'Message rejected by carrier';
                break;
            case 'FAILED':
                $statusMessage = 'Error sending message';
                break;
            case 'UNKNOWN':
                $statusMessage = 'Unknown error';
                break;
            default:
                $error = true;
                break;
        }

        return array(
            'error' => $error,
            'needlookup' => $needLookup,
            'statusmessage' => $statusMessage
        );
    }
}

?>
