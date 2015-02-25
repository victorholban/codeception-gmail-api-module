<?php
namespace Codeception\Module;
use Codeception\Module;
use Codeception\Util\GMailExpectedCondition;
use Codeception\Util\MailInterface;
use Codeception\Util\GMailRemote;

class GMailAPI extends Module implements MailInterface
{
    protected $requiredFields = array('client_id', 'client_secret', 'refresh_token');

    /** @var \Google_Service_Gmail */
    protected $service;

    /** @var GMailRemote */
    protected $remoteMail;

    public function _initialize() {
        $this->remoteMail = GMailRemote::createByParams($this->config['client_id'], $this->config['client_secret'], $this->config['refresh_token']);
    }

    public function _beforeStep(\Codeception\Step $step) {
        $this->remoteMail->refreshToken();
    }

    /**
     * @return \Google_Client
     */
    public function _getClient() {
        return $this->remoteMail->getClient();
    }

    /**
     * @return \Google_Service_Gmail
     */
    public function _getService() {
        return $this->remoteMail->getService();
    }

    /**
     * See In Last Email
     *
     * Look for a string in the most recent email
     *
     * @param $expected string
     * @return void
     **/
    public function seeInLastEmail($expected) {
        $email = $this->getLastMessage();
        $this->seeInEmail($email, $expected);
    }


    /**
     * Don't See In Last Email
     *
     * Look for the absence of a string in the most recent email
     *
     * @param $unexpected string
     * @return void
     **/
    public function dontSeeInLastEmail($unexpected) {
        $email = $this->getLastMessage();
        $this->dontSeeInEmail($email, $unexpected);
    }


    /**
     * See In Last Email From
     *
     * Look for a string in the most recent email sent to $address
     *
     * @param $address string
     * @param $expected string
     * @return void
     **/
    public function seeInLastEmailFrom($address, $expected) {
        $email = $this->getLastMessageFrom($address);
        $this->seeInEmail($email, $expected);

    }


    /**
     * Don't See In Last Email From
     *
     * Look for the absence of a string in the most recent email sent to $address
     *
     * @param $address string
     * @param $unexpected string
     * @return void
     **/
    public function dontSeeInLastEmailFrom($address, $unexpected) {
        $email = $this->getLastMessageFrom($address);
        $this->dontSeeInEmail($email, $unexpected);
    }


    /**
     * See In Last Email subject
     *
     * Look for a string in the most recent email subject
     *
     * @param $expected string
     * @return void
     **/
    public function seeInLastEmailSubject($expected) {
        $email = $this->getLastMessage();
        $this->seeInEmailSubject($email, $expected);
    }


    /**
     * Don't See In Last Email subject
     *
     * Look for the absence of a string in the most recent email subject
     *
     * @param $expected string
     * @return void
     **/
    public function dontSeeInLastEmailSubject($expected) {
        $email = $this->getLastMessage();
        $this->dontSeeInEmailSubject($email, $expected);
    }


    /**
     * See In Last Email Subject From
     *
     * Look for a string in the most recent email subject sent to $address
     *
     * @param $address string
     * @param $expected string
     * @return void
     **/
    public function seeInLastEmailSubjectFrom($address, $expected) {
        $email = $this->getLastMessageFrom($address);
        $this->seeInEmailSubject($email, $expected);

    }


    /**
     * Don't See In Last Email Subject From
     *
     * Look for the absence of a string in the most recent email subject sent to $address
     *
     * @param $address string
     * @param $unexpected string
     * @return void
     **/
    public function dontSeeInLastEmailSubjectFrom($address, $unexpected) {
        $email = $this->getLastMessageFrom($address);
        $this->dontSeeInEmailSubject($email, $unexpected);
    }


    /**
     * Grab From Last Email
     *
     * Look for a regex in the email source and return it
     *
     * @param $regex string
     * @return string
     **/
    public function grabFromLastEmail($regex) {
        $matches = $this->grabMatchesFromLastEmail($regex);
        return $matches[0];
    }


    /**
     * Grab From Last Email From
     *
     * Look for a regex in most recent email sent to $address email body and
     * return it
     *
     * @param $address string
     * @param $regex string
     * @return string
     **/
    public function grabFromLastEmailFrom($address, $regex) {
        $matches = $this->grabMatchesFromLastEmailFrom($address, $regex);
        return $matches[0];
    }


    /**
     * Grab Matches From Last Email
     *
     * Look for a regex in the email source and return it's matches
     *
     * @param $regex string
     * @return array
     **/
    public function grabMatchesFromLastEmail($regex) {
        $email = $this->getLastMessage();
        $matches = $this->grabMatchesFromEmail($email, $regex);
        return $matches;
    }


    /**
     * Grab Matches From Last Email From
     *
     * Look for a regex in most recent email sent to $address email source and
     * return it's matches
     *
     * @param $address string
     * @param $regex string
     * @return array
     **/
    public function grabMatchesFromLastEmailFrom($address, $regex) {
        $email = $this->getLastMessageFrom($address);
        $matches = $this->grabMatchesFromEmail($email, $regex);
        return $matches;
    }


    /**
     * Waits for email from $address to be received or for $timeout seconds to pass.
     *
     * @param $address
     * @param int $timeout
     * @throws \Codeception\Exception\TimeOut
     */
    public function waitForEmailFrom($address, $timeout = 10) {
        $this->remoteMail->wait($timeout)->until(GMailExpectedCondition::emailFrom($address));
    }


    /**
     * See In Email
     *
     * Look for a string in an email
     *
     * @param $email /Google_Service_Gmail_Message
     * @param $expected string
     * @return void
     **/
    protected function seeInEmail($email, $expected) {
        $this->assertContains($expected, $this->remoteMail->getEmailContent($email), "Email Contains");
    }

    /**
     * Don't See In Email
     *
     * Look for the absence of a string in an email
     *
     * @param $email /Google_Service_Gmail_Message
     * @param $unexpected string
     * @return void
     **/
    protected function dontSeeInEmail($email, $unexpected) {
        $this->assertNotContains($unexpected, $this->remoteMail->getEmailContent($email), "Email Does Not Contain");
    }

    /**
     * See In Subject
     *
     * Look for a string in an email subject
     *
     *
     * @param $email /Google_Service_Gmail_Message
     * @param $expected string
     * @return void
     **/
    protected function seeInEmailSubject($email, $expected) {
        $this->assertContains($expected, $this->remoteMail->getEmailHeader($email, 'Subject'), "Email Subject Contains");
    }

    /**
     * Don't See In Subject
     *
     * Look for the absence of a string in an email subject
     *
     * @param $email /Google_Service_Gmail_Message
     * @param $unexpected string
     * @return void
     **/
    protected function dontSeeInEmailSubject($email, $unexpected) {
        $this->assertNotContains($unexpected, $this->remoteMail->getEmailHeader($email, 'Subject'), "Email Subject Does Not Contain");
    }

    /**
     * Grab From Email
     *
     * Return the matches of a regex against the raw email
     *
     * @param $email \Google_Service_Gmail_Message
     * @param $regex string
     * @return array
     **/
    protected function grabMatchesFromEmail($email, $regex)
    {
        preg_match($regex, $this->remoteMail->getEmailContent($email), $matches);
        $this->assertNotEmpty($matches, "No matches found for $regex");
        return $matches;
    }

    /********************
     *  Helper function
     ********************/


    /**
     * Last Message From
     *
     * Get the most recent email sent to $address
     *
     * @param $address string
     * @return \Google_Service_Gmail_Message
     **/
    protected function getLastMessageFrom($address) {
        $messages = $this->remoteMail->getEmails(array(
            'maxResults' => 1,
            'q' => "from:{$address}",
        ));
        if (empty($messages)) {
            $this->fail("No messages sent to {$address}");
        }

        /** @var /Google_Service_Gmail_Message $last */
        $last = array_shift($messages);

        return $this->remoteMail->getEmailById($last->id);
    }

    /**
     * Last Message
     *
     * Get the most recent email
     *
     * @return \Google_Service_Gmail_Message
     **/
    protected function getLastMessage() {
        $messages = $this->remoteMail->getEmails(array(
            'maxResults' => 1,
        ));

        if (empty($messages)) {
            $this->fail("No messages received");
        }

        /** @var /Google_Service_Gmail_Message $last */
        $last = array_shift($messages);

        return $this->remoteMail->getEmailById($last->id);
    }
}