<?php

declare(strict_types=1);

namespace Siganushka\ProductBundle\Exception;

use Siganushka\Contracts\Doctrine\ResourceInterface;

class ResourceDisallowRemoveException extends \RuntimeException
{
    private ResourceInterface $owner;
    private string $fieldName;

    public function __construct(ResourceInterface $owner, string $fieldName, string $message = null, int $code = 0, \Throwable $previous = null)
    {
        $this->owner = $owner;
        $this->fieldName = $fieldName;

        parent::__construct($message ?? sprintf('Non-empty data for "%s::%s" cannot be remove.', \get_class($owner), $fieldName), $code, $previous);
    }

    public function getOwner(): ResourceInterface
    {
        return $this->owner;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }
}
