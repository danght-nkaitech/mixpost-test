<?php

namespace Inovector\Mixpost\Data;

final class AIChoiceData
{
    public function __construct(
        public readonly int $index,
        public readonly string $messageContent,
    ) {}

    public static function from(int $index, string $messageContent): self
    {
        return new self(
            $index,
            $messageContent,
        );
    }
}
