<?php
namespace Solidarity\Frontend\Action;

use Laminas\Config\Config;
use Laminas\Session\SessionManager as Session;
use League\Plates\Engine;
use Solidarity\Frontend\Action\BaseAction;
use Psr\Log\LoggerInterface as Logger;

class Donor extends BaseAction
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
        $this->setGlobalVariable('title', 'Forma za donatore');
//        if($this->visitorSession->getLoggedInUserId()) {
//            return $response->withStatus(302)->withHeader('Location', '/profil/');
//        }
        $data = $request->getParsedBody();
        if (!empty($data)) {
            try {
                $this->donor->create($data);
                // @TODO send mail
                $this->redirect('donorThankyou');
            } catch (\Exception $e) {
                // handle
            }

        }

        return $this->respond('donor/signup', []);
    }
}