<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$u = App\Models\User::where('name', 'like', '%Ali%')->first();
if($u) {
    echo "User: " . $u->name . "\n";
    $p = App\Models\ServicePurchase::forExpert($u->id)->pending()->get();
    echo "Pending requests count: " . $p->count() . "\n";
    foreach($p as $purchase) {
        echo "Purchase ID: " . $purchase->id . " Status: " . $purchase->status . " Service Status: " . $purchase->service_status . "\n";
    }
}

