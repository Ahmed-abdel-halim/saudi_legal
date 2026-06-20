<?php

namespace App\Services\Workflow\Rules;

interface RuleInterface
{
    /**
     * Evaluate rules against a given payload.
     *
     * @param array $payload
     * @return array [
     *   'success' => bool, // True if all hard deterministic rules pass (B = 1), False if any fail (B = 0)
     *   'logs' => array    // Array of compliance validation logs/reasons
     * ]
     */
    public function evaluate(array $payload): array;
}
