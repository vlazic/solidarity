<?php
namespace Solidarity\Frontend\Action;

use Laminas\Config\Config;
use Laminas\Session\SessionManager as Session;
use League\Plates\Engine;
use Solidarity\Frontend\Action\BaseAction;
use Psr\Log\LoggerInterface as Logger;

class Index extends BaseAction
{
    public function __construct(
        Logger $logger, Config $config, Engine $template, private \Solidarity\Donor\Service\Donor $donor
    ) {
        parent::__construct($logger, $config, $template);

    }

    public function __invoke(
        \Psr\Http\Message\ServerRequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response
    ) {
        $this->setGlobalVariable('title', 'Mreža Solidarnosti');

        return $this->respond('index/index', []);
    }
}