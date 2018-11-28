<?php

namespace Nwidart\Modules\Commands;

use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ResponseMakeCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    /**
     * The name of argument being used.
     *
     * @var string
     */
    protected $argumentName = 'response';

    /**
     * Single or multi file stubs need generate.
     *
     * @var []
     */
    protected $multiFiles = [];

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:make-response';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new response for the specified module.';

    /**
     * Get response name.
     *
     * @return string
     */
    public function getDestinationFilePath($file_name = null)
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $responsePath = GenerateConfigReader::read('response');

        return $path . $responsePath->getPath() . '/' . $this->getResponseName($file_name) . '.php';
    }

    /**
     * @return string
     */
    protected function getTemplateContents($file_name = null)
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        return (new Stub($this->getStubName(), [
            'MODULENAME'        => $module->getStudlyName(),
            'CONTROLLERNAME'    => $this->getResponseName(),
            'NAMESPACE'         => $module->getStudlyName(),
            'CLASS_NAMESPACE'   => $this->getClassNamespace($module),
            'CLASS'             => $this->getResponseNameWithoutNamespace(),
            'LOWER_NAME'        => $module->getLowerName(),
            'MODULE'            => $this->getModuleName(),
            'NAME'              => $this->getModuleName(),
            'STUDLY_NAME'       => $module->getStudlyName(),
            'MODULE_NAMESPACE'  => $this->laravel['modules']->config('namespace'),
        ]))->render();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the response class.'],
            ['module', InputArgument::OPTIONAL, 'The name of module will be used.'],
        ];
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['plain', 'p', InputOption::VALUE_NONE, 'Generate a plain response', null],
        ];
    }

    /**
     * @return array|string
     */
    protected function getResponseName($file_name=null)
    {
        $module_name = $this->getModuleName();

        $response = empty($file_name)? studly_case($this->argument('name')) : $file_name;

        return $module_name.$response;
    }

    /**
     * @return array|string
     */
    private function getResponseNameWithoutNamespace()
    {
        return class_basename($this->getResponseName());
    }

    public function getDefaultNamespace() : string
    {
        return $this->laravel['modules']->config('paths.generator.response.path', 'Responses');
    }

    /**
     * Get the stub file name based on the plain option
     * @return string
     */
    private function getStubName()
    {
        return '/responses/Response.stub';
    }
}
