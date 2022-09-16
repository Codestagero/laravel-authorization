<?php

namespace Codestage\Authorization\Attributes;

use Attribute;

/**
 * @template TProvider of \Codestage\Authorization\Contracts\IPolicyProvider
 */
#[Attribute(Attribute::TARGET_CLASS)]
class ProvidedBy
{
    /**
     * Set the provider for this policy.
     *
     * @param class-string<TProvider> $provider
     */
    public function __construct(public string $provider)
    {
    }
}
