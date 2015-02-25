<?php
namespace Codeception\Util;

class GMailRemote {

    public static function createByParams($clientId, $clientSecret, $refreshToken) {

        $client = new \Google_Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->addScope('https://mail.google.com/');
        $client->refreshToken($refreshToken);

        return self::createByClient($client);
    }

    public static function createByClient(\Google_Client $client) {
        $service = new \Google_Service_Gmail($client);

        return self::createByService($service);
    }

    public static function createByService(\Google_Service_Gmail $service) {
        return new GMailRemote($service);
    }

    /** @var \Google_Service_Gmail */
    public $service;

    public function __construct(\Google_Service_Gmail $service) {
        $this->service = $service;
    }

    /**
     * @return \Google_Client
     */
    public function getClient() {
        return $this->service->getClient();
    }

    /**
     * @return \Google_Service_Gmail
     */
    public function getService() {
        return $this->service;
    }

    public function refreshToken() {
        if($this->service->getClient()->isAccessTokenExpired()) {
            $this->service->getClient()->refreshToken($this->service->getClient()->getRefreshToken());
        }
    }

    /**
     * Messages
     *
     * Get an array of all the message objects
     *
     * @param $params array
     * @return array
     **/
    public function getEmails($params) {
        $defaultParam = array('maxResults' => 100);
        $params = array_merge($defaultParam, $params);
        /** @var /Google_Service_Gmail_ListMessagesResponse $list */
        $list = $this->service->users_messages->listUsersMessages('me', $params);
        return $list->getMessages();
    }

    /**
     * Email from ID
     *
     * Given a GMail id, returns the email's object
     *
     * @param $id string
     * @return \Google_Service_Gmail_Message
     **/
    public function getEmailById($id) {
        return $this->service->users_messages->get('me', $id, array('format' => 'full'));
    }

    /**
     * @param $email /Google_Service_Gmail_Message
     * @param $type string 'html' | 'plain'
     * @return string
     */
    public function getEmailContent($email, $type = 'plain') {
        if(!in_array($type, array('html', 'plain'))) {
            $type = 'plain';
        }

        foreach($email->getPayload()->getParts() as $emailPart) {
            if ($emailPart->mimeType != "text/{$type}") continue;
            return $this->base64url_decode($emailPart['body']['data']);
        }
        return '';
    }

    /**
     * @param $email /Google_Service_Gmail_Message
     * @param $headerName string
     * @return string
     */
    public function getEmailHeader($email, $headerName) {
        return $this->getHeaderValue($email->getPayload()->getHeaders(), $headerName);
    }

    /**
     * Construct a new RemoteWait by the current RemoteMail instance.
     * Sample usage:
     *
     *   $remoteMail->wait(20, 1000)->until(
     *     GMailExpectedCondition::emailFrom('test@gmail.com')
     *   );
     *
     * @return RemoteWait
     */
    public function wait(
        $timeout_in_second = 30,
        $interval_in_millisecond = 1000) {
        return new RemoteWait(
            $this, $timeout_in_second, $interval_in_millisecond
        );
    }

    /**
     * @param $headers array
     * @param $headerName string
     * @return string
     */
    protected function getHeaderValue($headers, $headerName) {
        foreach ($headers as $header) {
            if (!isset($header['name']) || !isset($header['value'])) continue;
            if ($header['name'] == $headerName) return $header['value'];
        }
        return '';
    }

    /**
     * Custom base64 encode function required by GMail API
     *
     * @param $data
     * @return string
     */
    protected function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Custom base64 decode function required by GMail API
     *
     * @param $data
     * @return string
     */
    protected function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}