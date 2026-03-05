<?php

namespace App\Notifications;

use App\Models\ServicePurchase;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ExpertPaymentReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(public ServicePurchase $purchase) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $service    = $this->purchase->service;
        $client     = $this->purchase->client;
        $serviceTitle = optional($service)->title ?? 'Service';
        $clientName   = optional($client)->name ?? 'Client';
        $amount       = number_format($this->purchase->total_price, 2) . ' SAR';

        return [
            'type'        => 'payment_received',
            'title'       => 'Payment Received',
            'message'     => "Payment of {$amount} from {$clientName} for \"{$serviceTitle}\" has been confirmed.",
            'purchase_id' => $this->purchase->id,
            'amount'      => $this->purchase->total_price,
            'client_name' => $clientName,
            'service'     => $serviceTitle,
            'url'         => route('dashboard.expert'),
        ];
    }
}
