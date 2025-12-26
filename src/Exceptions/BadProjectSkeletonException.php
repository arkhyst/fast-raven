<?php

namespace FastRaven\Exceptions;

use FastRaven\Types\ProjectFolderType;

class BadProjectSkeletonException extends SmartException
{
    /**
     * Initializes a new instance of the BadProjectSkeletonException class.
     *
     * This exception is thrown when the project skeleton is not valid.
     *
     * @param ProjectFolderType $folderType The type of the folder that is not valid.
     */
    public function __construct(ProjectFolderType $folderType) {
        parent::__construct("Project skeleton is not valid! ($folderType->value)", "This resource is not available at this time.", 500);
    }
}