<?php
declare(strict_types=1);

namespace AdminTheme\Command\Bake;

use Bake\Command\ControllerCommand as BakeControllerCommand;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;

class ControllerCommand extends BakeControllerCommand
{
    public function bakeController(string $controllerName, array $data, Arguments $args, ConsoleIo $io): void
    {
        $io->quiet(sprintf('Baking controller class for %s...', $controllerName));

        $data['actions'] = ['index', 'view', 'add', 'edit', 'delete'];
        $data['searchFields'] = $this->getSearchFields($data['modelObj']);

        $contents = $this->createTemplateRenderer()
            ->set($data)
            ->generate('AdminTheme.Controller/controller');

        $path = $this->getPath($args);
        $filename = $path . $controllerName . 'Controller.php';
        $io->createFile($filename, $contents, $this->force);
    }

    protected function getSearchFields($modelObj): array
    {
        $schema = $modelObj->getSchema();
        $searchFields = [];

        foreach ($schema->columns() as $field) {
            $type = $schema->getColumnType($field);
            if (in_array($type, ['string', 'text'])) {
                $searchFields[] = $field;
            }
        }

        return $searchFields;
    }
}
