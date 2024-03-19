<?php

// Clear the tests output directory
$outputDir = Codeception\Configuration::outputDir();
$iterator = new \DirectoryIterator($outputDir);

foreach ($iterator as $fileInfo) {
    if ($fileInfo->isDot() || $fileInfo->getFilename() === '.gitignore') {
        continue;
    }

    unlink($fileInfo->getPathname());
}
