<?php

namespace App\Service;

use Adbar\Dot;
use App\Entity\Entity;
use App\Entity\Gateway;
use App\Entity\ObjectEntity;
use App\Message\SyncPageMessage;
use Conduction\CommonGroundBundle\Service\CommonGroundService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class SynchronisationService
{
    private CommonGroundService $commonGroundService;
    private EntityManagerInterface $entityManager;
    private SessionInterface $session;
    private GatewayService $gatewayService;
    private FunctionService $functionService;
    private LogService $logService;
    private MessageBusInterface $messageBus;
    private TranslationService $translationService;

    public function __construct(CommonGroundService $commonGroundService, EntityManagerInterface $entityManager, SessionInterface $session, GatewayService $gatewayService, FunctionService $functionService, LogService $logService, MessageBusInterface $messageBus, TranslationService $translationService)
    {
        $this->commonGroundService = $commonGroundService;
        $this->entityManager = $entityManager;
        $this->session = $session;
        $this->gatewayService = $gatewayService;
        $this->functionService = $functionService;
        $this->logService = $logService;
        $this->messageBus = $messageBus;
        $this->translationService = $translationService;
    }

    // todo: Een functie dan op een source + endpoint alle objecten ophaalt (dit dus  waar ook de configuratie
    // todo: rondom pagination, en locatie van de results vandaan komt).
    public function getAllFromSource(Gateway $gateway, Entity $entity, string $location, array $configuration): array
    {
        // Get the first page of objects for exist outside the gateway to get the total amount of pages.
        $component = $this->gatewayService->gatewayToArray($gateway);
        $url = $this->getUrlForSource($gateway, $location);
        $amountOfPages = $this->getAmountOfPages($component, $url, $configuration['locationTotalCount']);

        // todo: asyn messages? for each page
        $application = $this->session->get('application');
        $activeOrganization = $this->session->get('activeOrganization');

        // todo: make this a message in order to compare and handle gateway objects that need to be deleted? see old code in ConvertToGatewayService.
        // Loop, for each page create a message:
        for ($page = 1; $page <= $amountOfPages; $page++) {
            $this->messageBus->dispatch(new SyncPageMessage(
                [
                    'component' => $component,
                    'url'       => $url,
                    'query'     => [], //todo?
                    'headers'   => $entity->getGateway()->getHeaders(),
                ],
                $page,
                $entity->getId(),
                [
                    'application'        => $application,
                    'activeOrganization' => $activeOrganization,
                ]
            ));
        }

        return []; //todo: nothing to return?
    }

    // todo: Een functie die één enkel object uit de source trekt
    public function getSingleFromSource(Sync $sync): array
    {
        $component = $this->gatewayService->gatewayToArray($gateway);
        $url = $this->getUrlForSource($gateway, $location);

        // todo: get object form source with callservice

        return [];
    }

    private function getUrlForSource(Gateway $gateway, string $location): string
    {
        // todo: generate url with correct query params etc.

        return '';
    }

    /**
     * Does a get call to determine how many pages we need to synchronise.
     *
     * @param array $component Component for callService
     * @param string $url Url for callService
     * @param string $locationTotalCount Where to find the amoung of pages in the response of the callService (use dot notation)
     *
     * @return int The amount of pages we need to synchronise
     */
    private function getAmountOfPages(array $component, string $url, string $locationTotalCount): int
    {
        // Use callService with url to get the total amount of pages
        $response = $this->commonGroundService->callService($component, $url, '', [], [], false, 'GET');
        if (is_array($response)) {
            //todo: error, user feedback and log this
//            var_dump('Callservice error, maybe $component or $url is incorrect?');
            return 0; // Do not get and sync pages
        }

        // Lets turn the source into a dot so that we can grab values
        $response = json_decode($response->getBody()->getContents(), true);
        $dot = new Dot($response);
        $amountOfPages = $dot->get($locationTotalCount, 1);

        // Make sure we return an integer, look for amount of pages if string
        if (!is_int($amountOfPages)) {
            $matchesCount = preg_match('/\?page=([0-9]+)/', $amountOfPages, $matches);
            if ($matchesCount == 1) {
                $amountOfPages = (int) $matches[1];
            } else {
                //todo: error, user feedback and log this
//                var_dump('Could not find the total amount of pages');
                return 1; // Only get and sync the first page
            }
        }

        return $amountOfPages;
    }

    // todo: Een functie die aan de hand van een synchronisatie object een sync uitvoert, om dubbele bevragingen
    // todo: van externe bronnen te voorkomen zou deze ook als propertie het externe object al array moeten kunnen accepteren.
    // RL: ik zou verwachten dat handle syn een sync ontvange en terug geeft
    public function handleSync(Sync $sync, ?array $sourceObject): Sync
    {
        // We need an object on the gateway side
        if (!$sync->getObject()){
            $object = new ObjectEntity();
            $object->setEntity($sync->getEnity);
        }

        // We need an object source side
        if (empty($sourceObject)){
            $sourceObject = $this->getSingleFromSource($sync);
        }

        // Now that we have a source object we can create a hash of it
        $hash = hash('sha384', $sourceObject);
        // Lets turn the source into a dot so that we can grap values
        $dot = new Dot($sourceObject);

        // Now we need to establish the last time the source was changed
        if (in_array('modifiedDateLocation',$sync->getAction()->getConfig())){
            // todo: get the laste chage date from object array
            $lastchagne = '';
            $sourceObject->setSourcelastChanged($lastchagne);
        }
        // What if the source has no propertity that alows us to determine the last change
        elseif ($sync->getHash() != $hash){
            $lastchagne = new \DateTime();
            $sourceObject->setSourcelastChanged($lastchagne);
        }

        // Now that we know the lastchange date we can update the hash
        $sourceObject->setHash($hash);

        // This gives us three options
        if ($sync->getSourcelastChanged() > $sync->getObject->getDateModified() && $sync->getSourcelastChanged() > $sync->getLastSynced() && $sync->getObject()->getDatemodified() < $sync->getsyncDatum()){
            // The source is newer
            $sync = $this->syncToSource($sync);
        }
        elseif ($sync->getSourcelastChanged() < $sync->getObject()->getDatemodified() && $sync->getObject()->getDatemodified() > $sync->getLastSynced() && $sync->getSourcelastChanged() < $sync->syncDatum()){
            // The gateway is newer
//            $sync = $this->syncToGateway($sync);

            // Save object
            //$entity = new Entity(); // todo $sync->getEntity() ?
            $object = $this->syncToGateway($entity, $sourceObject);
        }
        else {
            // we are in trouble, both the gateway object AND soure object have cahnged afther the last sync
            $sync = $this->syncTroughComparing($sync);
        }

        return $sync;
    }

    // todo: docs
    private function syncToSource(Sync $sync): Sync
    {
        return $sync;
    }

    // todo: docs
    private function syncToGateway(Entity $entity, array $externObject): ObjectEntity
    {
        // todo: mapping and translation
        // todo: validate object
        // todo: save object
        // todo: log?

        return new ObjectEntity();
    }

    // todo: docs
    private function syncThroughComparing(Sync $sync): Sync
    {
        return $sync;
    }
}
