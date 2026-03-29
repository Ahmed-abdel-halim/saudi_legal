<?php

namespace App\Enums;

enum TaskSentiment: string
{
    case Positive = 'positive';
    case Negative = 'negative';
    case Neutral  = 'neutral';
}

