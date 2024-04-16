<?php

namespace Codestage\Authorization\Tests\Fakes\Authorization\Handlers;

use Codestage\Authorization\Contracts\IResourceRequirementHandler;
use Codestage\Authorization\Tests\Fakes\Authorization\Requirement\SameAuthorRequirement;
use Codestage\Authorization\Tests\Fakes\Models\Document;
use Illuminate\Contracts\Auth\Guard;

/**
 * @implements IResourceRequirementHandler<SameAuthorRequirement, Document>
 */
class SameAuthorRequirementHandler implements IResourceRequirementHandler
{
    /**
     * SameAuthorRequirementHandler constructor method.
     *
     * @param Guard $_authManager
     */
    public function __construct(
        private readonly Guard $_authManager
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(mixed $requirement, mixed $resource): bool
    {
        return !strcmp($this->_authManager->id(), $resource->user_id);
    }
}
