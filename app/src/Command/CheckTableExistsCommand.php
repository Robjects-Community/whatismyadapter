<?php
declare(strict_types=1);

namespace App\Command;

use App\Utility\DatabaseUtility;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * CheckTableExists command.
 */
class CheckTableExistsCommand extends Command
{
    /**
     * Hook method for defining this command's option parser.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription('Check if a table exists in the database')
            ->addArgument('table', [
                'help' => 'The name of the table to check',
                'required' => true,
            ]);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $tableName = $args->getArgument('table');

        if (DatabaseUtility::tableExists($tableName)) {
            return static::CODE_SUCCESS;
        } else {
            return static::CODE_ERROR;
        }
    }
}
