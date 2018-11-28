<?php

namespace Nwidart\Modules\Commands;

use Illuminate\Console\Command;
use Nwidart\Modules\Exceptions\FileAlreadyExistException;
use Nwidart\Modules\Generators\FileGenerator;

abstract class GeneratorCommand extends Command
{
    /**
     * The name of 'name' argument.
     *
     * @var string
     */
    protected $argumentName = '';

    /**
     * Single or multi file stubs need generate.
     *
     * @var []
     */
    protected $multiFiles = [];

    /**
     * Get template contents.
     *
     * @return string
     */
    abstract protected function getTemplateContents($file_name = null);

    /**
     * Get the destination file path.
     *
     * @return string
     */
    abstract protected function getDestinationFilePath($file_name = null);

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = str_replace('\\', '/', $this->getDestinationFilePath());

        if (!$this->laravel['files']->isDirectory($dir = dirname($path))) {

            $this->laravel['files']->makeDirectory($dir, 0777, true);
        }

        if(empty($this->multiFiles)){

            $contents = $this->getTemplateContents();

            $this->fileGenerate($path, $contents);
        }
        else {
            if(!empty($this->argument('name'))){
                $this->multiFiles = [$this->argument('name')];
            }
            foreach($this->multiFiles as $file_name){

                $path = str_replace('\\', '/', $this->getDestinationFilePath($file_name));

                $contents = $this->getTemplateContents($file_name);

                $this->fileGenerate($path, $contents);
            }
        }

    }
    /**
     * File generate
     */
    public function fileGenerate($path, $contents){

        try {
            with(new FileGenerator($path, $contents))->generate();

            $this->info("Created : {$path}");
        } catch (FileAlreadyExistException $e) {
            $this->error("File : {$path} already exists.");
        }

    }

    /**
     * Get class name.
     *
     * @return string
     */
    public function getClass()
    {
        return class_basename($this->argument($this->argumentName));
    }

    /**
     * Get default namespace.
     *
     * @return string
     */
    public function getDefaultNamespace() : string
    {
        return '';
    }

    /**
     * Get class namespace.
     *
     * @param \Nwidart\Modules\Module $module
     *
     * @return string
     */
    public function getClassNamespace($module)
    {
        $extra = str_replace($this->getClass(), '', $this->argument($this->argumentName));

        $extra = str_replace('/', '\\', $extra);

        $namespace = $this->laravel['modules']->config('namespace');

        $namespace .= '\\' . $module->getStudlyName();

        $namespace .= '\\' . $this->getDefaultNamespace();

        $namespace .= '\\' . $extra;

        $namespace = str_replace('/', '\\', $namespace);

        return trim($namespace, '\\');
    }
}
