@extends('layouts.app')

@section('content')
<section class="bg-dark-navy text-white py-24 relative overflow-hidden border-b border-white/5">
    <div class="absolute inset-0 opacity-[0.03] bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-brand-green/10 rounded-full blur-[120px] pointer-events-none"></div>
    
    <div class="container mx-auto px-4 text-center relative z-10">
        <h1 class="text-4xl md:text-5xl font-black mb-6 bg-gradient-to-r from-white via-slate-100 to-slate-400 bg-clip-text text-transparent">@lang('pricing.PRICING_TITLE')</h1>
        <p class="text-base md:text-lg text-slate-400 max-w-2xl mx-auto">@lang('pricing.PRICING_SUBTITLE')</p>
    </div>
</section>

<section class="py-20 bg-dark-navy">
    <div class="container mx-auto max-w-5xl px-4">
        @if(isset($packages) && count($packages) > 0)
            <div class="grid grid-cols-1 md:grid-cols-{{ count($packages) == 2 ? '2' : (count($packages) >= 3 ? '3' : '1') }} gap-8 justify-center items-stretch">
                @foreach($packages as $pkg)
                    <div class="flex-1 expert-card p-8 hover:-translate-y-1 transition duration-300 relative overflow-hidden flex flex-col {{ $pkg->is_popular ? 'ring-2 ring-brand-green/40 shadow-green-glow' : '' }}">
                        @if($pkg->is_popular && $pkg->badge_text)
                            <div class="absolute top-0 {{ app()->getLocale() == 'ar' ? 'left-0 rounded-br-lg' : 'right-0 rounded-bl-lg' }} bg-brand-green text-dark-navy text-xs font-black px-3.5 py-1">
                                {{ $pkg->badge_text }}
                            </div>
                        @endif

                        <h3 class="text-2xl font-black text-white mb-2">{{ $pkg->name }}</h3>
                        @if($pkg->description)
                            <p class="text-slate-400 text-sm mb-6 leading-relaxed">{{ $pkg->description }}</p>
                        @endif

                        <div class="flex items-baseline mb-6 gap-2">
                            <span class="text-5xl font-black text-brand-green">{{ $pkg->is_free ? '0' : number_format($pkg->price, 0) }}</span>
                            <div class="text-slate-400 text-sm">
                                <span class="font-bold">ر.س</span>
                                <span class="block text-xs text-slate-500">{{ $pkg->billing_period_label ?? 'شهرياً' }}</span>
                            </div>
                        </div>

                        <a href="{{ route('ai.packages') }}" class="block w-full py-3.5 px-6 {{ $pkg->is_popular ? 'bg-gradient-to-r from-brand-green to-brand-teal text-dark-navy shadow-md shadow-brand-green/20' : 'bg-slate-900/60 hover:bg-slate-900 text-white border border-white/10 hover:border-brand-green' }} text-center font-black rounded-xl transition duration-300 mb-8">
                            {{ $pkg->is_free ? 'ابدأ مجاناً' : 'اشترك الآن' }}
                        </a>

                        <ul class="space-y-4 text-slate-300 text-sm mt-auto">
                            @if(is_array($pkg->features))
                                @foreach($pkg->features as $feature)
                                    <li class="flex items-center">
                                        <svg class="w-5 h-5 text-brand-green {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            @else
                                <li class="flex items-center">
                                    <svg class="w-5 h-5 text-brand-green {{ app()->getLocale() == 'ar' ? 'ml-3' : 'mr-3' }} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    {{ $pkg->query_limit_display }}
                                </li>
                            @endif
                        </ul>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 text-slate-400">
                <p class="text-lg">لا توجد باقات متاحة حالياً.</p>
            </div>
        @endif
    </div>
</section>
@endsection
