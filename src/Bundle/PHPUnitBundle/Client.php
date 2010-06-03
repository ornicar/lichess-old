<?php

namespace Bundle\PHPUnitBundle;

use Symfony\Foundation\Test\Client as BaseClient;

class Client extends BaseClient
{
    /**
     * Makes a request.
     *
     * @param Symfony\Components\HttpKernel\Request  $request A Request instance
     *
     * @param Symfony\Components\HttpKernel\Response $response A Response instance
     */
    protected function doRequest($request)
    {
        $this->writeSession($this->kernel->getContainer()->getUserService()->getAttributes());

        $this->kernel->reboot();

        return $this->kernel->handle($request);
    }

    protected function writeSession(array $attributes)
    {
        $session = $this->kernel->getContainer()->getUser_Session_TestService();
        $session->write('_user', $attributes);
        $session->sessionClose();
    }

    public function resetSession()
    {
      return $this->writeSession(array(
        '_flash' => array(),
        '_culture' => 'en'
      ));
    }
}
