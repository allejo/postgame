<?php

namespace App\Utility;

use App\Entity\Replay;

interface IMatchTimeEvent
{
    public function getReplay(): ?Replay;

    public function getMatchSeconds(): ?int;
}
