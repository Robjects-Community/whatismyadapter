<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\EntityInterface;
use Cake\Log\LogTrait;
use Cake\ORM\Table; // Added for type hinting

/**
 * Command for creating a user or updating a user's password in the database.
 */
class CreateUserCommand extends Command
{
    use LogTrait;

    /**
     * Builds the option parser for the command.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The console option parser.
     * @return \Cake\Console\ConsoleOptionParser The configured console option parser.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $parser
            ->setDescription('Creates a user or updates an existing user\'s password.')
            ->addOption('update-password', [
                'help' => 'Flag to update password for existing user. If set, 
                    --email and --password are used to find and update.',
                'boolean' => true,
                'default' => false,
            ])
            ->addOption('username', [
                'short' => 'u',
                'help' => 'Username for the user (required for creation).',
                'default' => null,
                'required' => false, // Made false, will be validated in execute
            ])
            ->addOption('password', [
                'short' => 'p',
                'help' => 'Password for the user (or new password if updating). Required.',
                'default' => null,
                'required' => true, // Always required (for create or for new password in update)
            ])
            ->addOption('email', [
                'short' => 'e',
                'help' => 'Email for the user (used to find user if updating). Required.',
                'default' => null,
                'required' => true, // Always required (for create or to find user in update)
            ])
            ->addOption('is_admin', [
                'short' => 'a',
                'help' => 'Is the user an admin? (1 for true, 0 for false; required for creation).',
                'default' => null,
                'required' => false, // Made false, will be validated in execute
            ]);

        return $parser;
    }

    /**
     * Executes the command.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console I/O.
     * @return int The exit code.
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $usersTable = $this->fetchTable('Users');

        if ($args->getOption('update-password')) {
            // Validate required args for update
            if (!$args->getOption('email') || !$args->getOption('password')) {
                $io->error('For password update, --email and --password (new password) are required.');
                $this->abort();
            }
            if ($this->updateUserPassword($args, $io, $usersTable)) {
                $io->success('User password updated successfully.');

                return static::CODE_SUCCESS;
            }
            $io->error('Failed to update user password.');

            return static::CODE_ERROR;
        } else {
            // Validate required args for create
            $missingCreateArgs = [];
            if (!$args->getOption('username')) {
                $missingCreateArgs[] = '--username';
            }
            if (!$args->getOption('password')) {
                $missingCreateArgs[] = '--password';
            }
            if (!$args->getOption('email')) {
                $missingCreateArgs[] = '--email';
            }
            if ($args->getOption('is_admin') === null) {
                $missingCreateArgs[] = '--is_admin';
            }

            if (!empty($missingCreateArgs)) {
                $io->error('For user creation, the following options are required: '
                . implode(', ', $missingCreateArgs));
                $this->abort();
            }

            if ($this->createUser($args, $io, $usersTable)) {
                $io->success('User created successfully.');

                return static::CODE_SUCCESS;
            }
            $io->error('Failed to create user.');

            return static::CODE_ERROR;
        }
    }

    /**
     * Creates a user with the provided arguments.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console I/O.
     * @param \Cake\ORM\Table $usersTable The users table.
     * @return bool True if the user was created successfully, false otherwise.
     */
    private function createUser(Arguments $args, ConsoleIo $io, Table $usersTable): bool
    {
        $data = [
            'username' => $args->getOption('username'),
            'password' => $args->getOption('password'),
            'confirm_password' => $args->getOption('password'),
            'email' => $args->getOption('email'),
            'is_admin' => in_array($args->getOption('is_admin'), ['1', 1, true], true), // flexible boolean check
            'active' => 1,
        ];

        $logData = $data;
        unset($logData['password']);

        $this->log(
            sprintf('Attempting to create user with data: %s', json_encode($logData)),
            'info',
            ['scope' => ['user_management', 'user_creation']],
        );

        $user = $usersTable->newEmptyEntity();
        // Allow mass assignment for these fields during creation
        $user->setAccess('is_admin', true);
        $user->setAccess('active', true);
        $user = $usersTable->patchEntity($user, $data);

        if ($usersTable->save($user)) {
            $this->log(
                sprintf('User created successfully: %s (ID: %s)', $user->username, $user->id),
                'info',
                ['scope' => ['user_management', 'user_creation']],
            );
            $io->out(sprintf('User "%s" created with ID: %s', $user->username, $user->id));

            return true;
        }

        $this->log(
            sprintf(
                'Failed to create user: %s. Errors: %s',
                $data['username'],
                json_encode($user->getErrors()),
            ),
            'error',
            ['scope' => ['user_management', 'user_creation']],
        );
        $io->error(sprintf('Could not create user. Errors: %s', json_encode($user->getErrors())));

        return false;
    }

    /**
     * Updates the password for an existing user found by email.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console I/O.
     * @param \Cake\ORM\Table $usersTable The users table.
     * @return bool True if the user password was updated successfully, false otherwise.
     */
    private function updateUserPassword(Arguments $args, ConsoleIo $io, Table $usersTable): bool
    {
        $email = $args->getOption('email');
        $newPassword = $args->getOption('password');

        $user = $usersTable->findByEmail($email)->first();

        if (!$user instanceof EntityInterface) { // Check if user was found
            $io->warning(sprintf('User with email "%s" not found.', $email));
            $this->log(
                sprintf('Password update failed: User with email "%s" not found.', $email),
                'warning',
                ['scope' => ['user_management', 'password_update']],
            );

            return false;
        }

        // Patch entity with the new password.
        // The User entity's setter for 'password' should handle hashing.
        $usersTable->patchEntity($user, ['password' => $newPassword]);

        // Ensure no other fields are accidentally changed if they were passed
        // (e.g. if username was passed, it shouldn't update username here)
        // For password update, we only care about the password field.
        // $user->set('password', $newPassword); // This is another way if you directly want to set it.
                                              // The patchEntity approach is fine if _setPassword handles hashing.

        if ($usersTable->save($user)) {
            $this->log(
                sprintf('Password updated successfully for user: %s (ID: %s)', $user->email, $user->id),
                'info',
                ['scope' => ['user_management', 'password_update']],
            );
            $io->out(sprintf('Password updated for user with email: %s', $email));

            return true;
        }

        $this->log(
            sprintf(
                'Failed to update password for user: %s. Errors: %s',
                $user->email,
                json_encode($user->getErrors()),
            ),
            'error',
            ['scope' => ['user_management', 'password_update']],
        );
        $io->error(sprintf('Could not update password. Errors: %s', json_encode($user->getErrors())));

        return false;
    }
}
