<?php
$domain = "radiif.com";
$mx_records = [];
if (getmxrr($domain, $mx_records)) {
    echo "MX Records for $domain:\n";
    print_r($mx_records);
} else {
    echo "No MX records found for $domain\n";
}
