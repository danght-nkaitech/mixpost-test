<?php

namespace Inovector\MixpostEnterprise\Concerns;

trait Prorates
{
    protected bool $prorate = true;

    public function noProrate(): static
    {
        $this->prorate = false;

        return $this;
    }

    public function prorate(): static
    {
        $this->prorate = true;

        return $this;
    }

    public function setProrate(bool $prorate = true): static
    {
        $this->prorate = $prorate;

        return $this;
    }
}
