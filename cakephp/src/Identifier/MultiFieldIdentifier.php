<?php
declare(strict_types=1);

namespace App\Identifier;

use Authentication\Identifier\PasswordIdentifier;
use Cake\ORM\Query\SelectQuery;

/**
 * Multi-field identifier that allows authentication using either username or email
 */
class MultiFieldIdentifier extends PasswordIdentifier
{
    /**
     * Execute the finder query and search by both username and email
     *
     * @param \Cake\ORM\Query\SelectQuery $query The query to execute.
     * @param array $conditions The conditions array.
     * @return \Cake\ORM\Query\SelectQuery
     */
    protected function _buildQuery(SelectQuery $query, array $conditions): SelectQuery
    {
        $config = $this->getConfig();
        $fields = $config['fields'];
        
        // Get the credential field - should be 'username' in our config
        $credentialField = $fields['username'] ?? 'username';
        
        // Get the login value (could be username or email)
        $loginValue = $conditions[$credentialField] ?? '';
        
        if (empty($loginValue)) {
            return $query->where(['1 = 0']); // Return no results
        }
        
        // Search for user by either username or email
        return $query->where([
            'OR' => [
                $query->getRepository()->aliasField('username') => $loginValue,
                $query->getRepository()->aliasField('email') => $loginValue,
            ]
        ]);
    }
}
