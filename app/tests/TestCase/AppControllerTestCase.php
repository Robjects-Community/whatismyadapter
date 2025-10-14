<?php
declare(strict_types=1);

namespace App\Test\TestCase;

use Authentication\Identity;
use Authentication\IdentityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class AppControllerTestCase extends TestCase
{
    use IntegrationTestTrait;

    protected function loginUser(string $userId): IdentityInterface
    {
        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $userEntity = null;
        try {
            // Ensure your fixture data includes 'id' and 'is_admin' (and 'dir', 'image' if used by identity consistently)
            // For the get() method, it will select all columns by default.
            $userEntity = $usersTable->get($userId);
        } catch (RecordNotFoundException $e) {
            throw new RecordNotFoundException(
                "User with ID '{$userId}' not found for loginUser. Fixture data might be missing or incorrect. Method: " . __METHOD__,
                (int)$e->getCode(),
                $e,
            );
        }

        // This check is redundant due to the try-catch but doesn't harm.
        if (!$userEntity) {
            throw new RecordNotFoundException("User with ID {$userId} not found in " . __METHOD__);
        }

        // Create an Identity object.
        // Key: $userEntity->toArray() must contain all fields needed by $identity->get('field_name')
        // including 'id' (for getIdentifier) and 'is_admin'.
        $identityData = $userEntity->toArray();
        $identity = new Identity($identityData);

        // Set the identity IN THE SESSION.
        // SessionAuthenticator with default sessionKey 'Auth' looks for this.
        $this->session(['Auth' => $identity]);

        return $identity;
    }
}
