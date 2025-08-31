<?php
declare(strict_types=1);

namespace AdminTheme\Command\Bake;

use Bake\Command\TemplateCommand as BakeTemplateCommand;
use Bake\Utility\Model\AssociationFilter;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\Utility\Inflector;

class TemplateCommand extends BakeTemplateCommand
{
    /**
     * Actions to use for scaffolding
     *
     * @var array<string>
     */
    public array $scaffoldActions = ['index', 'view', 'add', 'edit', 'search_results'];

    /**
     * Execute the command.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $this->extractCommonProperties($args);
        $name = $args->getArgument('name') ?? '';
        $name = $this->_getName($name);

        if (empty($name)) {
            return parent::execute($args, $io);
        }

        $controller = $args->getOption('controller');
        $this->controller($args, $name, $controller);
        $this->model($name);

        $vars = $this->_loadController($io);
        $methods = $this->scaffoldActions;

        foreach ($methods as $method) {
            try {
                $content = $this->getContent($args, $io, $method, $vars);
                $this->bake($args, $io, $method, $content);
            } catch (\Exception $e) {
                $io->error($e->getMessage());
            }
        }

        return static::CODE_SUCCESS;
    }

    /**
     * Get the path base for view templates.
     *
     * @param \Cake\Console\Arguments $args The arguments
     * @param string|null $container Unused.
     * @return string
     */
    public function getTemplatePath(Arguments $args, ?string $container = null): string
    {
        $path = Configure::read('App.paths.templates')[0];
        $path .= 'Admin' . DS . $this->controllerName . DS;

        return $path;
    }

    /**
     * Builds content from template and variables
     *
     * @param \Cake\Console\Arguments $args The CLI arguments
     * @param \Cake\Console\ConsoleIo $io The console io
     * @param string $action name to generate content to
     * @param array|null $vars passed for use in templates
     * @return string Content from template
     */
    public function getContent(Arguments $args, ConsoleIo $io, string $action, ?array $vars = null): string
    {
        if (!$vars) {
            $vars = $this->_loadController($io);
        }

        if (empty($vars['primaryKey'])) {
            $io->error('Cannot generate views for models with no primary key');
            $this->abort();
        }

        if (in_array($action, $this->excludeHiddenActions)) {
            $vars['fields'] = array_diff($vars['fields'], $vars['hidden']);
        }

        $renderer = $this->createTemplateRenderer()
            ->set('action', $action)
            ->set('plugin', $this->plugin)
            ->set($vars);

        $indexColumns = 0;
        if ($action === 'index' && $args->getOption('index-columns') !== null) {
            $indexColumns = $args->getOption('index-columns');
        }
        $renderer->set('indexColumns', $indexColumns);

        return $renderer->generate("AdminTheme.Template/$action");
    }

    /**
     * Assembles and writes bakes the view file.
     *
     * @param \Cake\Console\Arguments $args CLI arguments
     * @param \Cake\Console\ConsoleIo $io Console io
     * @param string $template Template file to use.
     * @param string|true $content Content to write.
     * @param ?string $outputFile The output file to create. If null will use `$template`
     * @return void
     */
    public function bake(
        Arguments $args,
        ConsoleIo $io,
        string $template,
        string|bool $content = '',
        ?string $outputFile = null
    ): void {
        if ($outputFile === null) {
            $outputFile = $template;
        }
        if ($content === true) {
            $content = $this->getContent($args, $io, $template);
        }
        if (empty($content)) {
            $io->err("<warning>No generated content for '{$template}.{$this->ext}', not generating template.</warning>");
            return;
        }
        $path = $this->getTemplatePath($args);
        $filename = $path . Inflector::underscore($outputFile) . '.' . $this->ext;

        $io->out("\n" . sprintf('Baking `%s` view template file...', $outputFile), 1, ConsoleIo::NORMAL);
        $io->createFile($filename, $content, $this->force);
    }

    /**
     * Get filtered associations
     *
     * @param \Cake\ORM\Table $model Table
     * @return array associations
     */
    protected function _filteredAssociations(\Cake\ORM\Table $model): array
    {
        if ($this->_associationFilter === null) {
            $this->_associationFilter = new AssociationFilter();
        }

        return $this->_associationFilter->filterAssociations($model);
    }
}
