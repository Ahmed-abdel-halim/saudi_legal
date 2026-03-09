<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\WalletTransaction;

class WalletController extends Controller
{
    /**
     * Request a withdrawal from the user's or company's wallet
     */
    public function withdraw(Request $request)
    {
        $user = auth()->user();
        
        // Determine if we are withdrawing from the expert's personal wallet
        // or the company's wallet. For simplicity in the simulation, we'll
        // allow the user to specify if they are withdrawing for their company.
        $forCompany = $request->boolean('for_company', false);
        
        $balance = 0;
        $entity = null;
        
        if ($forCompany) {
            $entity = $user->company;
            if (!$entity) {
                return response()->json(['error' => 'No company associated.'], 403);
            }
            $balance = $entity->wallet_balance;
        } else {
            $entity = $user;
            $balance = $user->wallet_balance;
        }

        // ── Withdrawal Threshold Rule: Minimum 200 SAR ──
        if ($balance < 200) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient balance. Minimum withdrawal threshold is 200 SAR. Current balance: {$balance} SAR"
            ], 422); // Unprocessable Entity
        }

        $amountToWithdraw = $request->input('amount', $balance);
        
        if ($amountToWithdraw > $balance) {
             return response()->json([
                'success' => false,
                'message' => "Cannot withdraw more than current balance."
            ], 422);
        }

        if ($amountToWithdraw < 200) {
             return response()->json([
                'success' => false,
                'message' => "Minimum withdrawal amount is 200 SAR."
            ], 422);
        }

        DB::transaction(function () use ($entity, $amountToWithdraw, $forCompany) {
            $entity->decrement('wallet_balance', $amountToWithdraw);
            
            WalletTransaction::create([
                'user_id' => $forCompany ? null : $entity->id,
                'company_id' => $forCompany ? $entity->company_id : null,
                'type' => 'debit',
                'amount' => $amountToWithdraw,
                'description' => "Withdrawal Request of {$amountToWithdraw} SAR",
                'reference_type' => null, // Would normally link to a Withdrawal model
                'reference_id' => null,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => "Withdrawal of {$amountToWithdraw} SAR processed successfully."
        ]);
    }
}
