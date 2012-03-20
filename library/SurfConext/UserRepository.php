<?php

/**
 * A Repository "acts like a collection, except with more elaborate querying capability"
 * [Evans, Domain Driven Design] and may be considered as an "objects in memory facade"
 *
 * @throws Exception
 *
 */
class SurfConext_UserRepository
{
    protected $_mapper;

    public function __construct(SurfConext_UserRepository_Mapper_Ldap $mapper)
    {
        $this->_mapper = $mapper;
    }

    public function findUserByCollabPersonUuid($collabPersonUuid)
    {
        $users = $this->_mapper->findByCollabPersonUuid($collabPersonUuid);

        return $this->_getUserFromResults($users);
    }

    public function findUserByCollabPersonId($collabPersonId)
    {
        $users = $this->_mapper->findByCollabPersonId($collabPersonId);

        return $this->_getUserFromResults($users);
    }

    protected function _getUserFromResults($users)
    {
        if (empty($users)) {
            return null;
        }

        if (count($users) > 1) {
            throw new Exception('Multiple users found for the same collabPersonId?');
        }

        /** @var $user SurfConext_User */
        $user = $users[0];

        // Set the last-accessed time
        $now = date(DATE_RFC822);
        $user->setCollabPersonLastAccessed($now);

        $this->_mapper->update($user);

        return $user;
    }

    public function add(SurfConext_User $user)
    {
        $user->setCollabPersonUuid(Surfnet_Zend_Uuid::generate());

        $now = date(DATE_RFC822);
        $user->setCollabPersonRegistered($now);
        $user->setCollabPersonLastAccessed($now);
        $user->setCollabPersonLastUpdated($now);

        $this->_mapper->insert($user);

        return $user;
    }

    public function update(SurfConext_User $user)
    {
        $now = date(DATE_RFC822);
        $user->setCollabPersonLastAccessed($now);
        $user->setCollabPersonLastUpdated($now);

        $this->_mapper->update($user);

        return $user;
    }

    public function delete(SurfConext_User $user)
    {
        return $this->_mapper->delete($user);
    }

    public function count()
    {
        return $this->_mapper->count();
    }
}