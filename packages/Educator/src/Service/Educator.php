<?php
namespace Solidarity\Educator\Service;

use Solidarity\Delegate\Service\Delegate;
use Solidarity\Educator\Repository\EducatorRepository;
use Skeletor\Core\TableView\Service\TableView;
use Psr\Log\LoggerInterface as Logger;
use Skeletor\User\Service\Session;
use Solidarity\Educator\Filter\Educator as EducatorFilter;
use Solidarity\Educator\Repository\RoundRepository;
use Solidarity\Transaction\Service\Round;
use Tamtamchik\SimpleFlash\Flash;

class Educator extends TableView
{

    /**
     * @param EducatorRepository $repo
     * @param Session $user
     * @param Logger $logger
     */
    public function __construct(
        EducatorRepository $repo, Session $user, Logger $logger, EducatorFilter $filter, private \DateTime $dt,
        private Round $round, private RoundRepository $roundRepository, private Delegate $delegate
    ) {
        parent::__construct($repo, $user, $logger, $filter);
    }

    /**
     * Make sure if existing accNumber/name combo is entered for creation, to just update amount, and reset status
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
//        $round = $this->round->getActiveRound();
        $entity = $this->getEntities(['accountNumber' => $data['accountNumber'], 'name' => $data['name']]);

        if (count($entity)) {
            $data['id'] = $entity[0]->id;
            $data['status'] = \Solidarity\Educator\Entity\Educator::STATUS_NEW;
            $data['schoolName'] = $entity[0]->schoolName;
            $data['city'] = $entity[0]->city;
            $data['comment'] = $entity[0]->comment;
            $data['slipLink'] = $entity[0]->slipLink;
            /* @var \Solidarity\Educator\Entity\Educator $entity */
            $entity = parent::update($data);
        } else {
            $entity = parent::create($data);
//            $educatorRounds = $this->roundRepository->fetchAll(['round' => $round->id, 'educator' => $entity->id]);
//            if (count($educatorRounds)) {
//
//            }
//            $data['round']['amount'] = $entity->amount;
//            $data['round']['educator'] = $entity;
//            $data['round']['round'] = $round;
//            $entity = parent::update($data);
        }
//        var_dump($entity->rounds);
//        die();
//        $educatorRound = new \Solidarity\Educator\Entity\Round();
        return $entity;
    }

    public function startNewRound()
    {
        return $this->repo->startNewRound();
    }

    public function getForMapping()
    {
        return $this->repo->fetchForMapping();
    }

    public function getEntityData($id)
    {
        $data = parent::getEntityData($id);
        $delegate = $this->delegate->getEntities(['schoolName' => $data['schoolName'], 'city' => $data['city']])[0];
        $data['delegateVerified'] = ($delegate->status === \Solidarity\Delegate\Entity\Delegate::STATUS_VERIFIED) ? 1:0;

        return $data;
    }

    public function prepareEntities($entities)
    {
        $items = [];
        /* @var \Solidarity\Educator\Entity\Educator $educator */
        foreach ($entities as $educator) {
            // @TODO make sure all educators have delegate
            $delegate = $this->delegate->getEntities(['schoolName' => $educator->schoolName, 'city' => $educator->city]);
            $delegateVerified = 'No';
            if (count($delegate) && ($delegate[0]->status === \Solidarity\Delegate\Entity\Delegate::STATUS_VERIFIED)) {
                $delegateVerified = 'Yes';
            }
            $itemData = [
                'id' => $educator->getId(),
                'name' =>  [
                    'value' => $educator->name,
                    'editColumn' => true,
                ],
                'amount' => number_format($educator->amount, 0, '.', ','),
                'status' => \Solidarity\Educator\Entity\Educator::getHrStatus($educator->status),
                'schoolName' => $educator->schoolName,
                'city' => $educator->city,
                'delegateVerified' => $delegateVerified,
//                'slipLink' => $educator->slipLink,
                'accountNumber' => $educator->accountNumber,
                'createdAt' => $educator->getCreatedAt()->format('d.m.Y'),
            ];
            $items[] = [
                'columns' => $itemData,
                'id' => $educator->getId(),
            ];
        }
        return $items;
    }

    public function compileTableColumns()
    {

        $columnDefinitions = [
            ['name' => 'name', 'label' => 'Name'],
            ['name' => 'delegateVerified', 'label' => 'Delegate verified'],
            ['name' => 'schoolName', 'label' => 'School name'],
            ['name' => 'amount', 'label' => 'Amount', 'rangeFilter' => ['type' => 'number']],
            ['name' => 'accountNumber', 'label' => 'Account Number'],
            ['name' => 'city', 'label' => 'City'],
            ['name' => 'status', 'label' => 'Status', 'filterData' => \Solidarity\Educator\Entity\Educator::getHrStatuses()],
//            ['name' => 'slipLink', 'label' => 'slipLink'],
            ['name' => 'createdAt', 'label' => 'Created at'],
        ];

        return $columnDefinitions;
    }

}