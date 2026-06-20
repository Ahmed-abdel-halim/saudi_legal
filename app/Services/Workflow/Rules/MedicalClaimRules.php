<?php

namespace App\Services\Workflow\Rules;

use App\Models\WorkflowTask;

class MedicalClaimRules implements RuleInterface
{
    /**
     * Evaluate rules against a given medical claim payload.
     */
    public function evaluate(array $payload): array
    {
        $logs = [];
        $success = true;

        $gender = $payload['patient_gender'] ?? 'Unknown';
        $age = $payload['patient_age'] ?? null;
        $cptCode = $payload['cpt_code'] ?? '';
        $icdCode = $payload['icd_10_code'] ?? '';
        $claimedAmount = $payload['claimed_amount'] ?? 0;
        $nationalId = $payload['patient_national_id'] ?? '';

        // 1. Gender Incompatibility Check (Obstetrics/Gynecology for Males)
        $obstetricIcdPrefixes = ['O00', 'O01', 'O02', 'O03', 'O04', 'O05', 'O06', 'O07', 'O08', 'O30', 'O60', 'Z34', 'Z35', 'Z39'];
        $obstetricCptCodes = ['59400', '59510', '81807', '99384'];

        $isObstetricClaim = false;
        foreach ($obstetricIcdPrefixes as $prefix) {
            if (str_starts_with($icdCode, $prefix)) {
                $isObstetricClaim = true;
                break;
            }
        }
        if (in_array($cptCode, $obstetricCptCodes)) {
            $isObstetricClaim = true;
        }

        if ($isObstetricClaim && strtolower($gender) === 'male') {
            $success = false;
            $logs[] = [
                'rule' => 'GENDER_COMPLIANCE',
                'status' => 'FAILED',
                'message' => "Gender incompatibility: Obstetric/Gynecologic procedure {$cptCode} or diagnosis {$icdCode} claimed for a Male patient."
            ];
        } else {
            $logs[] = [
                'rule' => 'GENDER_COMPLIANCE',
                'status' => 'PASSED',
                'message' => "Patient gender ({$gender}) is compatible with billing codes."
            ];
        }

        // 2. Price Caps Adjudication
        $policyCaps = [
            '99213' => 250.00,  // Outpatient consultation
            '70450' => 1500.00, // CT Scan Head
            '93000' => 150.00,  // ECG
            '85025' => 100.00,  // Complete Blood Count (CBC)
            '59400' => 6000.00, // Vaginal Delivery
            '59510' => 9000.00, // Cesarean Delivery
        ];

        if (array_key_exists($cptCode, $policyCaps)) {
            $cap = $policyCaps[$cptCode];
            if ($claimedAmount > $cap) {
                $success = false;
                $logs[] = [
                    'rule' => 'POLICY_PRICE_CAP',
                    'status' => 'FAILED',
                    'message' => "Claimed amount {$claimedAmount} SAR for CPT code {$cptCode} exceeds the policy limit of {$cap} SAR."
                ];
            } else {
                $logs[] = [
                    'rule' => 'POLICY_PRICE_CAP',
                    'status' => 'PASSED',
                    'message' => "Claimed amount {$claimedAmount} SAR is within the policy limit of {$cap} SAR."
                ];
            }
        } else {
            $logs[] = [
                'rule' => 'POLICY_PRICE_CAP',
                'status' => 'PASSED',
                'message' => "No policy price cap configured for CPT code {$cptCode}."
            ];
        }

        // 3. Temporal Duplicate Check (within 24 hours for complex codes like head CTs)
        $flaggedDuplicate = $payload['is_duplicate_flag'] ?? false;
        
        // Also check DB for identical client+patient+cptCode combination within last 24 hours
        if ($nationalId) {
            $duplicateInDb = WorkflowTask::where('task_type', 'MEDICAL_CLAIM')
                ->where('created_at', '>=', now()->subHours(24))
                ->where(function($query) use ($nationalId, $cptCode) {
                    $query->where('payload->patient_national_id', $nationalId)
                          ->orWhere('original_payload->patient_national_id', $nationalId);
                })
                ->where(function($query) use ($cptCode) {
                    $query->where('payload->cpt_code', $cptCode);
                })
                ->exists();
                
            if ($duplicateInDb || $flaggedDuplicate) {
                $success = false;
                $logs[] = [
                    'rule' => 'TEMPORAL_DUPLICATE_CHECK',
                    'status' => 'FAILED',
                    'message' => "Potential double billing: Patient ID ...{$nationalId} already has a claim submitted for procedure {$cptCode} within the last 24 hours."
                ];
            } else {
                $logs[] = [
                    'rule' => 'TEMPORAL_DUPLICATE_CHECK',
                    'status' => 'PASSED',
                    'message' => "No matching claim found for patient ID ...{$nationalId} and CPT {$cptCode} in the last 24 hours."
                ];
            }
        } else {
            $logs[] = [
                'rule' => 'TEMPORAL_DUPLICATE_CHECK',
                'status' => 'PASSED',
                'message' => "Duplicate validation bypassed: Patient National ID is missing."
            ];
        }

        return [
            'success' => $success,
            'logs' => $logs
        ];
    }
}
