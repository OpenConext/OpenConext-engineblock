<?php

namespace OpenConext\EngineBlock\Service;

use OpenConext\EngineBlock\Authentication\Model\User;
use OpenConext\EngineBlock\Authentication\Repository\ConsentRepository;
use OpenConext\EngineBlock\Authentication\Repository\UserDirectory;
use OpenConext\EngineBlock\Authentication\Value\CollabPersonId;
use OpenConext\EngineBlockBundle\Authentication\Repository\SamlPersistentIdRepository;
use OpenConext\EngineBlockBundle\Authentication\Repository\ServiceProviderUuidRepository;

final class DeprovisionService implements DeprovisionServiceInterface
{
    /**
     * @var ConsentRepository
     */
    private $consentRepository;

    /**
     * @var UserDirectory
     */
    private $userDirectory;

    /**
     * @var SamlPersistentIdRepository
     */
    private $persistentIdRepository;

    /**
     * @var ServiceProviderUuidRepository
     */
    private $serviceProviderUuidRepository;

    /**
     * @param ConsentRepository $consentRepository
     * @param UserDirectory $userDirectory
     */
    public function __construct(
        ConsentRepository $consentRepository,
        UserDirectory $userDirectory,
        SamlPersistentIdRepository $persistentIdRepository,
        ServiceProviderUuidRepository $serviceProviderUuidRepository
    ) {
        $this->consentRepository = $consentRepository;
        $this->userDirectory = $userDirectory;
        $this->persistentIdRepository = $persistentIdRepository;
        $this->serviceProviderUuidRepository = $serviceProviderUuidRepository;
    }

    /**
     * @param CollabPersonId $id
     * @return array
     */
    public function read(CollabPersonId $id)
    {
        $user = $this->userDirectory->findUserBy($id);

        if ($user === null) {
            return [];
        }

        return [
            [
                'name'  => 'user',
                'value' => $user,
            ],
            [
                'name'  => 'saml_persistent_id',
                'value' => $this->findPersistentIds($user),
            ],
            [
                'name'  => 'consent',
                'value' => $this->findConsent($user),
            ],
        ];
    }

    /**
     * @param User $user
     * @return array
     */
    private function findPersistentIds(User $user)
    {
        $idsWithoutSpEntityId = $this->persistentIdRepository->findByUuid(
            $user->getCollabPersonUuid()
        );

        foreach ($idsWithoutSpEntityId as $id) {
            $idsWithSpEntityId[] = [
                'persistent_id' => $id->persistentId,
                'user_uuid' => $id->userUuid,
                'service_provider_entity_id' => $this->serviceProviderUuidRepository->findEntityIdByUuid(
                    $id->serviceProviderUuid
                ),
            ];
        }

        return $idsWithSpEntityId;
    }

    /**
     * @param User $user
     * @return \OpenConext\EngineBlock\Authentication\Model\Consent[]
     */
    private function findConsent(User $user)
    {
        return $this->consentRepository->findAllFor(
            $user->getCollabPersonId()->getCollabPersonId()
        );
    }

    /**
     * @param CollabPersonId $id
     */
    public function delete(CollabPersonId $id)
    {
        $this->deleteConsent($id);

        $user = $this->userDirectory->findUserBy($id);

        if ($user) {
            $this->deleteSamlPersistentId($user);
            $this->deleteUser($user);
        }
    }

    /**
     * @param CollabPersonId $id
     */
    private function deleteConsent($id)
    {
        $this->consentRepository->deleteAllFor(
            $id->getCollabPersonId()
        );
    }

    /**
     * @param User $user
     */
    private function deleteSamlPersistentId(User $user)
    {
        $this->persistentIdRepository->deleteByUuid(
            $user->getCollabPersonUuid()
        );
    }

    /**
     * @param User $user
     */
    private function deleteUser(User $user)
    {
        $this->userDirectory->removeUserWith(
            $user->getCollabPersonId()
        );
    }
}
