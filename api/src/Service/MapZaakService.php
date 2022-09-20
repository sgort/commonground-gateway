<?php

namespace App\Service;

use App\Entity\Entity;
use App\Entity\ObjectEntity;
use Doctrine\ORM\EntityManagerInterface;

class MapZaakService
{
    private EntityManagerInterface $entityManager;
    private TranslationService $translationService;
    private ObjectEntityService $objectEntityService;
    private EavService $eavService;
    private SynchronizationService $synchronizationService;
    private array $configuration;
    private array $data;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        TranslationService $translationService,
        ObjectEntityService $objectEntityService,
        EavService $eavService,
        SynchronizationService $synchronizationService
    ) {
        $this->entityManager = $entityManager;
        $this->translationService = $translationService;
        $this->objectEntityService = $objectEntityService;
        $this->eavService = $eavService;
        $this->synchronizationService = $synchronizationService;

        $this->objectEntityRepo = $this->entityManager->getRepository(ObjectEntity::class);
        $this->entityRepo = $this->entityManager->getRepository(Entity::class);

        $this->mappingIn = [
            'identificatie' => 'reference',
            'omschrijving' => 'instance.embedded.subject',
            'toelichting' => 'instance.embedded.subject_external',
            'registratiedatum' => 'instance.embedded.date_of_registration',
            'startdatum' => 'instance.embedded.date_of_registration',
            'einddatum' => 'instance.embedded.date_of_completion',
            'einddatumGepland' => 'instance.embedded.date_target',
            'publicatiedatum' => 'instance.embedded.date_target',
            'communicatiekanaal' => 'instance.embedded.channel_of_contact',
            'vertrouwelijkheidaanduidng' => 'instance.embedded.confidentiality.mapped'


        ];

        $this->skeletonIn = [
            'verantwoordelijkeOrganisatie' => '070124036',
            'betalingsindicatie' => 'geheel',
            'betalingsindicatieWeergave' => 'Bedrag is volledig betaald',
            'laatsteBetaalDatum' => '15-07-2022',
            'archiefnominatie' => 'blijvend_bewaren',
            'archiefstatus' => 'nog_te_archiveren'
        ];
    }

    /**
     * Finds or creates a ObjectEntity from the Zaak Entity.
     *
     * @param Entity $zaakEntity This is the Zaak Entity in the gateway.
     *
     * @return ObjectEntity $zaakObjectEntity This is the ZGW Zaak ObjectEntity.
     */
    private function getZaakObjectEntity(Entity $zaakEntity): ObjectEntity
    {
        // Find already existing zgwZaak by $this->data['reference']
        $zaakObjectEntity = $this->objectEntityRepo->findOneBy(['externalId' => $this->data['reference'], 'entity' => $zaakEntity]);

        // Create new empty ObjectEntity if no ObjectEntity has been found
        if (!$zaakObjectEntity instanceof ObjectEntity) {
            $zaakObjectEntity = new ObjectEntity();
            $zaakObjectEntity->setEntity($zaakEntity);
        }

        return $zaakObjectEntity;
    }

    /**
     * Creates or updates a ZGW Zaak from a xxllnc casetype with the use of mapping.
     *
     * @param array $data          Data from the handler where the xxllnc casetype is in.
     * @param array $configuration Configuration from the Action where the Zaak entity id is stored in.
     *
     * @return array $this->data Data which we entered the function with
     */
    public function mapZaakHandler(array $data, array $configuration): array
    {
        $this->data = $data['response'];
        $this->configuration = $configuration;

        var_dump('MapZaak triggered');

        // Find ZGW Type entities by id from config
        $zaakEntity = $this->entityRepo->find($configuration['entities']['Zaak']);
        $zaakTypeEntity = $this->entityRepo->find($configuration['entities']['ZaakType']);

        if (!isset($zaakEntity)) {
            throw new \Exception('Zaak entity could not be found, plugin configuration could be wrong');
        }
        if (!isset($zaakTypeEntity)) {
            throw new \Exception('ZaakType entity could not be found, plugin configuration could be wrong');
        }

        $zaakTypeObjectEntity = $this->objectEntityRepo->findOneBy(['externalId' => $this->data['embedded']['instance']['embedded']['casetype']['reference']]);

        if (!isset($zaakTypeObjectEntity)) {
            throw new \Exception('ZaakType object could not be found, create ZaakTypen first');
        }

        var_dump('ZaakType object found: ' . $zaakTypeObjectEntity->getId()->toString());
        die;

        $zaakObjectEntity = $this->getZaakObjectEntity($zaakEntity);

        // Map and set default values from xxllnc casetype to zgw zaaktype
        $zgwZaakArray = $this->translationService->dotHydrator(isset($this->skeletonIn) ? array_merge($this->data, $this->skeletonIn) : $this->data, $this->data, $this->mappingIn);
        $zgwZaakArray['instance'] = null;
        $zgwZaakArray['embedded'] = null;

        // $zgwZaakArray = $this->mapStatusTypen($zgwZaakArray);

        $zaakObjectEntity->hydrate($zgwZaakArray);

        $zaakObjectEntity->setExternalId($this->data['reference']);
        $zaakObjectEntity = $this->synchronizationService->setApplicationAndOrganization($zaakObjectEntity);

        $this->entityManager->persist($zaakObjectEntity);
        $this->entityManager->flush();

        return $this->data;
    }
}
