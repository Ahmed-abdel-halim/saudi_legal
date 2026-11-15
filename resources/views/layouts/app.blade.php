{{-- resources/views/layouts/app.blade.php - Complete Layout Template --}}
@include('layouts.header')

{{-- Global Toasts --}}
@include('partials.toasts')

{{-- Page Content --}}
@yield('content')

{{-- Footer --}}
@include('layouts.footer')

{{-- Scripts --}}
@stack('scripts')