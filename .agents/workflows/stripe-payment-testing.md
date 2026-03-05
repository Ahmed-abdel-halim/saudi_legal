---
description: How to test the Stripe payment integration end-to-end
---

# Stripe Payment Integration — Test Workflow

## Prerequisites
- `php artisan serve` running on port 8000
- XAMPP MySQL running
- A Stripe account at https://dashboard.stripe.com (test mode)
- Stripe CLI installed: https://stripe.com/docs/stripe-cli

---

## Phase 1 — Environment Setup

1. Open `.env` and fill in your real Stripe **test** keys:
   ```
   STRIPE_KEY=pk_test_YOUR_KEY
   STRIPE_SECRET=sk_test_YOUR_KEY
   STRIPE_WEBHOOK_SECRET=whsec_REPLACE_AFTER_STEP_3
   ```

// turbo
2. Clear the config cache:
   ```
   php artisan optimize:clear
   ```

3. In a **second terminal**, start the Stripe CLI webhook forwarder:
   ```
   stripe listen --forward-to http://127.0.0.1:8000/stripe/webhook
   ```
   Copy the `whsec_...` webhook signing secret printed by the CLI.
   Paste it as `STRIPE_WEBHOOK_SECRET` in `.env`, then re-run step 2.

---

## Phase 2 — Test WITHOUT Webhook (UI Flow Only)

4. Open http://127.0.0.1:8000/login and log in with a client account.

5. Browse to http://127.0.0.1:8000/services and click any expert service.

6. Click **Request / Purchase**, fill in hours, and submit.

7. **Verify**: You are redirected to `checkout.stripe.com` (Stripe Checkout page).

8. On the Stripe Checkout page, use test card **`4242 4242 4242 4242`**
   - Expiry: `12/26`, CVC: `123`, Name: `Test User`

9. Complete the payment.

10. **Verify**: Browser redirects to `http://127.0.0.1:8000/payment/success`

11. Open the database `service_purchases` table and verify that at this point:
    - `stripe_session_id` is filled ✅
    - `payment_status` is still `unpaid` (webhook not received yet if CLI isn't running) ⚠️

---

## Phase 3 — Test WITH Webhook (Full Confirmed Flow)

> Requires Stripe CLI running (step 3 above).

12. Repeat steps 4–9 above with the Stripe CLI webhook forwarder active.

13. Watch the Stripe CLI terminal — you should see:
    ```
    --> checkout.session.completed [evt_...]
    <-- [200] POST http://127.0.0.1:8000/stripe/webhook
    ```

14. **Verify DB** — the `service_purchases` row should now have:
    - `payment_status = paid` ✅
    - `paid_at = <timestamp>` ✅
    - `stripe_payment_intent_id = pi_...` ✅

15. **Verify notification** — the expert's `notifications` table should have a new row:
    ```sql
    SELECT * FROM notifications ORDER BY created_at DESC LIMIT 1;
    ```

---

## Phase 4 — Test Cancel Flow

16. Repeat steps 4–7 (get to Stripe Checkout).

17. Click the **back arrow** / **Cancel** on the Stripe page.

18. **Verify**: Browser redirects to `http://127.0.0.1:8000/payment/cancel/{id}`

19. Verify the cancel page shows the order details and a **"Try Again"** button.

20. Click **Try Again** — verify it redirects back to Stripe Checkout.

---

## Phase 5 — Test Webhook Idempotency (Duplicate Events)

// turbo
21. Replay the last webhook event via the CLI:
    ```
    stripe events resend <evt_ID_from_step_13>
    ```

22. **Verify**: The second event is silently ignored (no duplicate update).
    Check Laravel logs:
    ```
    php artisan log:view
    ```
    Or open `storage/logs/laravel.log` and look for:
    ```
    Stripe webhook: duplicate event, purchase already paid
    ```

---

## Phase 6 — Test Declined Card

23. Repeat steps 4–7, but use **declined** test card: **`4000 0000 0000 0002`**

24. **Verify**: Stripe shows a decline error on the Checkout page.

25. **Verify**: No `service_purchases` update (status remains `unpaid`).

---

## ✅ Test Completion Checklist

| Test | Expected Result |
|---|---|
| Checkout redirect | → `checkout.stripe.com` |
| Stripe test card payment | → `/payment/success` page |
| Success page UI | Order reference + status shown |
| Webhook received | `payment_status = paid` in DB |
| `paid_at` filled | Timestamp set |
| Expert notification | Row in `notifications` table |
| Cancel page | Shows retry button |
| Retry from cancel | Returns to Stripe Checkout |
| Duplicate webhook | Silently ignored, logged |
| Declined card | No DB update, Stripe shows error |

---

## Quick Reference — Test Cards

| Card Number | Scenario |
|---|---|
| `4242 4242 4242 4242` | ✅ Successful payment |
| `4000 0000 0000 0002` | ❌ Generic decline |
| `4000 0025 0000 3155` | 🔐 Requires 3D Secure |

## Simulate Webhook Locally (No Stripe CLI)

Run the artisan command for a specific purchase ID:
```
php artisan stripe:simulate-webhook {purchase_id}
```
This bypasses signature verification and calls the internal handler directly — **dev only**.
