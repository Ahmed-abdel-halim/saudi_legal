<?php

namespace App\Services\Workflow;

class Sanitizer
{
    /**
     * PHI keys that must be redacted under PDPL rules.
     */
    protected static array $phiKeys = [
        'patient_name',
        'patient_phone',
        'patient_email',
        'patient_national_id',
        'patient_dob',
        'patient_address',
        'phone',
        'email',
        'national_id',
        'iqama',
        'dob',
        'birth_date',
        'address',
        'contact_number'
    ];

    /**
     * Anonymize PHI values inside a workflow payload.
     */
    public function sanitize(array $payload): array
    {
        return $this->sanitizeRecursive($payload);
    }

    /**
     * Recursively traverse and redact keys matching PHI criteria.
     */
    protected function sanitizeRecursive(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->sanitizeRecursive($value);
            } else {
                $normalizedKey = strtolower(trim($key));
                if (in_array($normalizedKey, self::$phiKeys)) {
                    if (!empty($value)) {
                        $label = strtoupper(str_replace('patient_', '', $normalizedKey));
                        $data[$key] = "[REDACTED {$label}]";
                    }
                }
            }
        }
        return $data;
    }
}
