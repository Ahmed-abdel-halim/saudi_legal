{{-- resources/views/layouts/app.blade.php - Complete Layout Template --}}
@include('layouts.header')

{{-- Global Toasts & Confirmations --}}
@include('partials.toasts')
@include('partials.confirm_modal')

{{-- Page Content --}}
@yield('content')

{{-- Footer --}}
@include('layouts.footer')

{{-- Scripts --}}
@stack('scripts')