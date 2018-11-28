<?php

namespace Nwidart\Modules\Commands;

use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ValidatorMakeCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    /**
     * The name of argument being used.
     *
     * @var string
     */
    protected $argumentName = 'validator';

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
    protected $name = 'module:make-validator';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new validator for the specified module.';

    /**
     * Get validator name.
     *
     * @return string
     */
    public function getDestinationFilePath($file_name = null)
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $validatorPath = GenerateConfigReader::read('validator');

        return $path . $validatorPath->getPath() . '/' . $this->getValidatorName($file_name) . '.php';
    }

    /**
     * @return string
     */
    protected function getTemplateContents()
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        return (new Stub($this->getStubName(), [
            'MODULENAME'        => $module->getStudlyName(),
            'CONTROLLERNAME'    => $this->getValidatorName(),
            'NAMESPACE'         => $module->getStudlyName(),
            'CLASS_NAMESPACE'   => $this->getClassNamespace($module),
            'CLASS'             => $this->getValidatorNameWithoutNamespace(),
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
            ['validator', InputArgument::REQUIRED, 'The name of the validator class.'],
            ['module', InputArgument::OPTIONAL, 'The name of module will be used.'],
        ];
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['plain', 'p', InputOption::VALUE_NONE, 'Generate a plain validator', null],
        ];
    }

    /**
     * @return array|string
     */
    protected function getValidatorName($file_name=null)
    {
        $module_name = $this->getModuleName();

        $validator = empty($file_name)? studly_case($this->argument('validator')) : $file_name;

        if (empty($file_name) && str_contains(strtolower($validator), 'validator') === false) {
            $validator .= 'Validator';
        }


        return $module_name.$validator;
    }

    /**
     * @return array|string
     */
    private function getValidatorNameWithoutNamespace()
    {
        return class_basename($this->getValidatorName());
    }

    public function getDefaultNamespace() : string
    {
        return $this->laravel['modules']->config('paths.generator.validator.path', 'validator');
    }

    /**
     * Get the stub file name based on the plain option
     * @return string
     */
    private function getStubName()
    {
        if ($this->option('plain') === true) {
            return '/validator-plain.stub';
        }

        return '/validator.stub';
    }
}
