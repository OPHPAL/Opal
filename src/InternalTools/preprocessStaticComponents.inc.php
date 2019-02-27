<?php declare(strict_types = 1);

namespace Netmosfera\Opal\InternalTools;

use Netmosfera\Opal\PackageComponent;
use Netmosfera\Opal\PackageDirectory;
use const DIRECTORY_SEPARATOR as DS;

function preprocessStaticComponents(
    Array $directories,
    Array $preprocessors,
    String $compileDirectory,
    Bool $executeIt,
    ?Int $directoryPermissions,
    ?Int $filePermissions
){
    assert(isNormalizedPath($compileDirectory));

    $components = [];
    /** @var PackageComponent[] $components */

    foreach($directories as $directory){
        assert($directory instanceof PackageDirectory);
        foreach(dirReadRecursive($directory->path) as $filePath){
            $component = componentFromPath($directory, $filePath);
            if($component !== NULL && $component->extension === ".inc.php"){
                preprocessComponent(
                    $directory, $component, $preprocessors, $compileDirectory,
                    FALSE, $directoryPermissions, $filePermissions
                );
                $components[] = $component->absolutePath;
            }
        }
    }

    $staticInclusionsSource = "<?php\n\n";
    $staticInclusionsSource .= "// Generated by netmosfera/opal.\n";
    $staticInclusionsSource .= "// Do not edit this file manually!\n ";
    $staticInclusionsSource .= "\n";

    foreach($components as $component){
        $fileString = var_export($component, TRUE);
        $staticInclusionsSource .= "require __DIR__ . " . $fileString . ";\n";
    }

    $destinationFile = $compileDirectory . DS . "static-inclusions.php";

    file_put_contents($destinationFile, $staticInclusionsSource);

    if($executeIt) require $destinationFile; // @TODO clean scope in file
}
