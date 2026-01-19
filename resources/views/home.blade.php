{{-- resources/views/home.blade.php - Example Home Page --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-6 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold mb-4 text-dark-navy">
            {{ __('Welcome to :name', ['name' => $platformName ?? 'Radiif']) }}
        </h1>
        <p class="text-lg text-gray-600 mb-8">
            {{ __('منصة تجمع بين أصحاب المشاريع والخبراء المستقلين في بيئة عمل مرنة وآمنة.', [], app()->getLocale()) }}
        </p>

        <div class="flex gap-4">
            <a href="###"
                class="bg-brand-primary text-white px-6 py-3 rounded-full font-bold shadow-lg hover:bg-opacity-90 transition-all">
                {{ __('أبدأ الآن', [], app()->getLocale()) }}
            </a>
            <a href="###"
                class="border-2 border-brand-primary text-brand-primary px-6 py-3 rounded-full font-bold hover:bg-brand-primary hover:text-white transition-all">
                {{ __('كيف نعمل', [], app()->getLocale()) }}
            </a>
        </div>
    </div>
</div>
@endsection