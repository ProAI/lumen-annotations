<?php

namespace ProAI\Datamapper\Eloquent;

use Illuminate\Filesystem\Filesystem;

class Generator
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Path to model storage directory.
     *
     * @var array
     */
    protected $path;

    /**
     * Model stubs.
     *
     * @var array
     */
    protected $stubs;

    /**
     * Constructor.
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param string $path
     * @return void
     */
    public function __construct(Filesystem $files, $path)
    {
        $this->files = $files;
        $this->path = $path;

        $this->stubs['model'] = $this->files->get(__DIR__ . '/../../stubs/model.stub');
        $this->stubs['relation'] = $this->files->get(__DIR__ . '/../../stubs/model-relation.stub');
        $this->stubs['morph_extension'] = $this->files->get(__DIR__ . '/../../stubs/model-morph-extension.stub');
    }

    /**
     * Generate model from metadata.
     *
     * @param array $metadata
     * @param boolean $saveMode
     * @return void
     */
    public function generate($metadata, $saveMode=false)
    {
        // clean or make (if not exists) model storage directory
        if (! $this->files->exists($this->path)) {
            $this->files->makeDirectory($this->path);
        }

        // clear existing models if save mode is off
        if (! $saveMode) {
            $this->clean();
        }

        // create models
        foreach ($metadata as $entityMetadata) {
            $this->generateModel($entityMetadata);
        }

        // create .gitignore
        $this->files->put($this->path . '/.gitignore', '*' . PHP_EOL . '!.gitignore');

        // create json file for metadata
        $contents = json_encode($metadata, JSON_PRETTY_PRINT);
        $this->files->put($this->path . '/entities.json', $contents);
    }

    /**
     * Clean model directory.
     *
     * @return void
     */
    public function clean()
    {
        if ($this->files->exists($this->path)) {
            $this->files->cleanDirectory($this->path);
        }
    }

    /**
     * Generate model from metadata.
     *
     * @param \ProAI\Datamapper\Metadata\Definitions\Entity $entityMetadata
     * @return void
     */
    public function generateModel($entityMetadata)
    {
        $stub = $this->stubs['model'];

        // header
        $this->replaceNamespace(get_mapped_model_namespace(), $stub);
        $this->replaceClass(class_basename(get_mapped_model($entityMetadata['class'], false)), $stub);
        $this->replaceMappedClass($entityMetadata['class'], $stub);

        // traits
        $this->replaceTraits($entityMetadata, $stub);

        // table name
        $this->replaceTable($entityMetadata['table']['name'], $stub);

        // primary key
        $columnMetadata = $this->getPrimaryKeyColumn($entityMetadata);
        $this->replacePrimaryKey($columnMetadata['name'], $stub);
        $this->replaceIncrementing((! empty($columnMetadata['options']['autoIncrement'])), $stub);

        $this->replaceAutoUuids($entityMetadata, $stub);

        // timestamps
        $this->replaceTimestamps($entityMetadata['timestamps'], $stub);

        // misc
        $this->replaceTouches($entityMetadata['touches'], $stub);
        $this->replaceWith($entityMetadata['with'], $stub);
        $this->replaceVersioned($entityMetadata['versionTable'], $stub);
        $this->replaceMorphClass($entityMetadata['morphClass'], $stub);

        // mapping data
        $this->replaceMapping($entityMetadata, $stub);
        
        // relations
        $this->replaceRelations($entityMetadata['relations'], $stub);

        $this->files->put($this->path . '/' . get_mapped_model_hash($entityMetadata['class']), $stub);
    }

    /**
     * Get primary key and auto increment value.
     *
     * @param \ProAI\Datamapper\Metadata\Definitions\Entity $entityMetadata
     * @return \ProAI\Datamapper\Metadata\Definitions\Column
     */
    protected function getPrimaryKeyColumn($entityMetadata)
    {
        $primaryKey = 'id';
        $incrementing = true;

        foreach ($entityMetadata['table']['columns'] as $column) {
            if ($column['primary']) {
                return $column;
            }
        }
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string  $name
     * @param  string  $stub
     * @return void
     */
    protected function replaceNamespace($name, &$stub)
    {
        $stub = str_replace('{{namespace}}', $name, $stub);
    }

    /**
     * Replace the classname for the given stub.
     *
     * @param  string  $name
     * @param  string  $stub
     * @return void
     */
    protected function replaceClass($name, &$stub)
    {
        $stub = str_replace('{{class}}', $name, $stub);
    }

    /**
     * Replace the classname for the given stub.
     *
     * @param  string  $name
     * @param  string  $stub
     * @return void
     */
    protected function replaceMappedClass($name, &$stub)
    {
        $stub = str_replace('{{mappedClass}}', "'".$name."'", $stub);
    }
    
    /**
     * Replace traits.
     *
     * @param \ProAI\Datamapper\Metadata\Definitions\Entity $entityMetadata
     * @param string $stub
     * @return void
     */
    protected function replaceTraits($entityMetadata, &$stub)
    {
        $traits = [];

        // versionable
        if (! empty($entityMetadata['versionTable'])) {
            $traits['versionable'] = 'use \ProAI\Versioning\Versionable;';
        }

        // softDeletes
        if ($entityMetadata['softDeletes']) {
            $traits['softDeletes'] = 'use \ProAI\Versioning\SoftDeletes;';
        }

        // autoUuid
        if ($this->hasAutoUuidColumn($entityMetadata)) {
            $traits['autoUuid'] = 'use \ProAI\Datamapper\Eloquent\AutoUuid;';
        }

        $separator = PHP_EOL . PHP_EOL . '    ';
        $stub = str_replace('{{traits}}', implode($separator, $traits) . $separator, $stub);
    }

    /**
     * Does this model have an auto uuid column?
     *
     * @param \ProAI\Datamapper\Metadata\Definitions\Entity $entityMetadata
     * @return boolean
     */
    protected function hasAutoUuidColumn($entityMetadata)
    {
        foreach ($entityMetadata['table']['columns'] as $column) {
            if (! empty($column['options']['autoUuid'])) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * Replace softDeletes.
     *
     * @param boolean $option
     * @param string $stub
     * @return void
     */
    protected function replaceSoftDeletes($option, &$stub)
    {
        $stub = str_replace('{{softDeletes}}', $option ? 'use \ProAI\Versioning\SoftDeletes;' . PHP_EOL . PHP_EOL . '    ' : '', $stub);
    }
    
    /**
     * Replace versionable.
     *
     * @param boolean $option
     * @param string $stub
     * @return void
     */
    protected function replaceVersionable($versionTable, &$stub)
    {
        $option = (! empty($versionTable)) ? true : false;
        $stub = str_replace('{{versionable}}', (! empty($versionTable)) ? 'use \ProAI\Versioning\Versionable;' . PHP_EOL . PHP_EOL . '    ' : '', $stub);
    }
    
    /**
     * Replace table name.
     *
     * @param boolean $name
     * @param string $stub
     * @return void
     */
    protected function replaceTable($name, &$stub)
    {
        $stub = str_replace('{{table}}', "'".$name."'", $stub);
    }
    
    /**
     * Replace primary key.
     *
     * @param string $name
     * @param string $stub
     * @return void
     */
    protected function replacePrimaryKey($name, &$stub)
    {
        $stub = str_replace('{{primaryKey}}', "'".$name."'", $stub);
    }
    
    /**
     * Replace incrementing.
     *
     * @param boolean $option
     * @param string $stub
     * @return void
     */
    protected function replaceIncrementing($option, &$stub)
    {
        $stub = str_replace('{{incrementing}}', $option ? 'true' : 'false', $stub);
    }
    
    /**
     * Replace autoUuids.
     *
     * @param \ProAI\Datamapper\Metadata\Definitions\Entity $entityMetadata
     * @param string $stub
     * @return void
     */
    protected function replaceAutoUuids($entityMetadata, &$stub)
    {
        $autoUuids = [];

        foreach ($entityMetadata['table']['columns'] as $column) {
            if (! empty($column['options']['autoUuid'])) {
                $autoUuids[] = $column['name'];
            }
        }

        $stub = str_replace('{{autoUuids}}', $this->getArrayAsText($autoUuids), $stub);
    }
    
    /**
     * Replace timestamps.
     *
     * @param boolean $option
     * @param string $stub
     * @return void
     */
    protected function replaceTimestamps($option, &$stub)
    {
        $stub = str_replace('{{timestamps}}', $option ? 'true' : 'false', $stub);
    }
    
    /**
     * Replace touches.
     *
     * @param array $touches
     * @param string $stub
     * @return void
     */
    protected function replaceTouches($touches, &$stub)
    {
        $stub = str_replace('{{touches}}', $this->getArrayAsText($touches), $stub);
    }
    
    /**
     * Replace with.
     *
     * @param array $with
     * @param string $stub
     * @return void
     */
    protected function replaceWith($with, &$stub)
    {
        $stub = str_replace('{{with}}', $this->getArrayAsText($with), $stub);
    }
    
    /**
     * Replace versioned.
     *
     * @param mixed $versionTable
     * @param string $stub
     * @return void
     */
    protected function replaceVersioned($versionTable, &$stub)
    {
        if (! $versionTable) {
            $stub = str_replace('{{versioned}}', $this->getArrayAsText([]), $stub);
            return;
        }

        $versioned = [];
        foreach ($versionTable['columns'] as $column) {
            if (! $column['primary'] || $column['name'] == 'version') {
                $versioned[] = $column['name'];
            }
        }
        $stub = str_replace('{{versioned}}', $this->getArrayAsText($versioned), $stub);
    }

    /**
     * Replace the morph classname for the given stub.
     *
     * @param  string  $name
     * @param  string  $stub
     * @return void
     */
    protected function replaceMorphClass($name, &$stub)
    {
        $stub = str_replace('{{morphClass}}', "'".$name."'", $stub);
    }
    
    /**
     * Replace mapping.
     *
     * @param \ProAI\Datamapper\Metadata\Definitions\Entity $entityMetadata
     * @param string $stub
     * @return void
     */
    protected function replaceMapping($entityMetadata, &$stub)
    {
        $attributes = [];
        foreach ($entityMetadata['attributes'] as $attributeMetadata) {
            $attributes[$attributeMetadata['name']] = $attributeMetadata['columnName'];
        }

        $embeddeds = [];
        foreach ($entityMetadata['embeddeds'] as $embeddedMetadata) {
            $embedded = [];
            $embedded['class'] = $embeddedMetadata['class'];
            $embeddedAttributes = [];
            foreach ($embeddedMetadata['attributes'] as $attributeMetadata) {
                $embeddedAttributes[$attributeMetadata['name']] = $attributeMetadata['columnName'];
            }
            $embedded['attributes'] = $embeddedAttributes;
            $embeddeds[$embeddedMetadata['name']] = $embedded;
        }

        $relations = [];
        foreach ($entityMetadata['relations'] as $relationMetadata) {
            $relation = [];
            
            $relation['type'] = $relationMetadata['type'];
            if ($relation['type'] == 'belongsToMany' || $relation['type'] == 'morphToMany') {
                $relation['inverse'] = (! empty($relationMetadata['options']['inverse']));
            }

            $relations[$relationMetadata['name']] = $relation;
        }

        $mapping = [
            'attributes' => $attributes,
            'embeddeds' => $embeddeds,
            'relations' => $relations,
        ];

        $stub = str_replace('{{mapping}}', $this->getArrayAsText($mapping), $stub);
    }
    
    /**
     * Replace relations.
     *
     * @param array $relations
     * @param string $stub
     * @return void
     */
    protected function replaceRelations($relations, &$stub)
    {
        $textRelations = [];

        foreach ($relations as $key => $relation) {
            $relationStub = $this->stubs['relation'];

            // generate options array
            $options = [];

            if ($relation['type'] != 'morphTo') {
                $options[] = "'" . get_mapped_model($relation['relatedEntity'], false)."'";
            }

            foreach ($relation['options'] as $name => $option) {
                if ($option === null) {
                    $options[] = 'null';
                } elseif ($option === true) {
                    $options[] = 'true';
                } elseif ($option === false) {
                    $options[] = 'false';
                } else {
                    if ($name == 'throughEntity') {
                        $options[] = "'".get_mapped_model($option, false)."'";
                    } elseif ($name != 'morphableClasses') {
                        $options[] = "'".$option."'";
                    }
                }
            }
            
            $options = implode(", ", $options);

            $relationStub = str_replace('{{name}}', $relation['name'], $relationStub);
            $relationStub = str_replace('{{options}}', $options, $relationStub);
            $relationStub = str_replace('{{ucfirst_type}}', ucfirst($relation['type']), $relationStub);
            $relationStub = str_replace('{{type}}', $relation['type'], $relationStub);

            $textRelations[] = $relationStub;

            if ($relation['type'] == 'morphTo'
                || ($relation['type'] == 'morphToMany' && ! $relation['options']['inverse'])) {
                $morphStub = $this->stubs['morph_extension'];

                $morphableClasses = [];
                foreach ($relation['options']['morphableClasses'] as $key => $name) {
                    $morphableClasses[$key] = get_mapped_model($name, false);
                }

                $morphStub = str_replace('{{name}}', $relation['name'], $morphStub);
                $morphStub = str_replace('{{morphName}}', ucfirst($relation['options']['morphName']), $morphStub);
                $morphStub = str_replace('{{types}}', $this->getArrayAsText($morphableClasses, 2), $morphStub);

                $textRelations[] = $morphStub;
            }
        }

        $stub = str_replace('{{relations}}', implode(PHP_EOL . PHP_EOL, $textRelations), $stub);
    }

    /**
     * Get an array in text format.
     *
     * @param array $array
     * @return string
     */
    protected function getArrayAsText($array, $intendBy=1)
    {
        $intention = '';
        for ($i=0; $i<$intendBy; $i++) {
            $intention .= '    ';
        }

        $text = var_export($array, true);

        $text = preg_replace('/[ ]{2}/', '    ', $text);
        $text = preg_replace("/\=\>[ \n    ]+array[ ]+\(/", '=> array(', $text);
        return $text = preg_replace("/\n/", "\n".$intention, $text);
    }
}
