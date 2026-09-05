@php
    $gaId = config('services.google.analytics_id', env('GOOGLE_ANALYTICS_ID', 'G-LSP0883M01'));
@endphp

@if(!empty($gaId))
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '{{ $gaId }}');
</script>
@endif
