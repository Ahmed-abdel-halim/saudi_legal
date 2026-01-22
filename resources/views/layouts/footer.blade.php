@php
// Get current locale and direction
$currentLang = app()->getLocale();
$direction = $currentLang === 'ar' ? 'rtl' : 'ltr';

// Language switch URL for footer links
$currentUrl = request()->url();
$currentQuery = request()->query();
$targetLangCode = $currentLang === 'en' ? 'ar' : 'en';
$currentQuery['lang'] = $targetLangCode;
$switchLangUrl = $currentUrl . '?' . http_build_query($currentQuery);
@endphp

{{-- Footer Section --}}
<footer class="bg-dark-navy text-white pt-12 pb-6 border-t border-white/5 relative overflow-hidden mt-auto" dir="{{ $direction }}">

    {{-- Decorative Background Pattern --}}
    <div class="absolute inset-0 pointer-events-none opacity-5"
        style="background-image: url('https://www.transparenttextures.com/patterns/cubes.png'); background-size: auto;">
    </div>

    <div class="container mx-auto px-3 sm:px-4 md:px-6 lg:px-8 xl:px-12 relative z-10 max-w-[1920px]">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 md:gap-8 border-b border-white/10 pb-8 mb-8">

            {{-- Column 1: Logo and Description --}}
            <div class="col-span-1 md:col-span-2 lg:col-span-2">
                <a href="{{ route('home') }}" class="flex items-center gap-3 mb-4 group">
                    <div class="relative">
                        <div class="absolute -inset-1 bg-gradient-to-r from-brand-primary to-brand-secondary rounded-lg blur opacity-30 group-hover:opacity-60 transition duration-500"></div>
                        <img src="{{ asset('images/icon.png') }}"
                            onerror="this.src='https://placehold.co/40x40/4F46E5/FFFFFF?text=R'"
                            alt="{{ __('footer.PLATFORM_NAME', [], $currentLang) }}"
                            class="relative h-10 w-auto rounded-lg shadow-lg">
                    </div>
                    <span class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-300">
                        {{ __('footer.PLATFORM_NAME', [], $currentLang) }}
                    </span>
                </a>
                <p class="text-gray-400 text-sm leading-relaxed max-w-sm mb-6 font-normal">
                    {{ __('footer.HERO_SUBTITLE', [], $currentLang) }}
                </p>

                {{-- Social Media Links --}}
                <div class="flex gap-3">
                    <a href="#"
                        class="w-9 h-9 rounded-full bg-white/5 hover:bg-brand-primary flex items-center justify-center transition-all duration-300 text-gray-400 hover:text-white group"
                        aria-label="{{ __('footer.SOCIAL_TWITTER', [], $currentLang) }}">
                        <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                        </svg>
                    </a>
                    <a href="#"
                        class="w-9 h-9 rounded-full bg-white/5 hover:bg-brand-secondary flex items-center justify-center transition-all duration-300 text-gray-400 hover:text-white group"
                        aria-label="{{ __('footer.SOCIAL_LINKEDIN', [], $currentLang) }}">
                        <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Column 2: About Section --}}
            <div>
                <h4 class="text-base font-bold mb-4 text-white relative inline-block pb-2">
                    {{ __('footer.NAV_ABOUT', [], $currentLang) }}
                    <span class="absolute {{ $direction === 'rtl' ? 'bottom-0 right-0' : 'bottom-0 left-0' }} w-1/2 h-0.5 bg-brand-primary rounded-full"></span>
                </h4>
                <ul class="space-y-2 text-sm">
                    <li>
                        <a href="{{ route('about') }}"
                            class="text-gray-400 hover:text-brand-primary {{ $direction === 'rtl' ? 'hover:translate-x-1' : 'hover:-translate-x-1' }} transition-all duration-300 block py-1">
                            {{ __('footer.NAV_MENU_ABOUT', [], $currentLang) }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('contact') }}"
                            class="text-gray-400 hover:text-brand-primary {{ $direction === 'rtl' ? 'hover:translate-x-1' : 'hover:-translate-x-1' }} transition-all duration-300 block py-1">
                            {{ __('footer.NAV_MENU_CONTACT', [], $currentLang) }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('careers') }}"
                            class="text-gray-400 hover:text-brand-primary {{ $direction === 'rtl' ? 'hover:translate-x-1' : 'hover:-translate-x-1' }} transition-all duration-300 block py-1">
                            {{ __('footer.NAV_MENU_CAREERS', [], $currentLang) }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('blog') }}"
                            class="text-gray-400 hover:text-brand-primary {{ $direction === 'rtl' ? 'hover:translate-x-1' : 'hover:-translate-x-1' }} transition-all duration-300 block py-1">
                            {{ __('footer.NAV_MENU_BLOG', [], $currentLang) }}
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Column 3: How It Works Section --}}
            <div>
                <h4 class="text-base font-bold mb-4 text-white relative inline-block pb-2">
                    {{ __('footer.NAV_HOW_IT_WORKS', [], $currentLang) }}
                    <span class="absolute {{ $direction === 'rtl' ? 'bottom-0 right-0' : 'bottom-0 left-0' }} w-1/2 h-0.5 bg-brand-secondary rounded-full"></span>
                </h4>
                <ul class="space-y-2 text-sm">
                    <li>
                        <a href="{{ route('how-it-works') }}"
                            class="text-gray-400 hover:text-brand-secondary {{ $direction === 'rtl' ? 'hover:translate-x-1' : 'hover:-translate-x-1' }} transition-all duration-300 block py-1">
                            {{ __('footer.NAV_MENU_HOW_IT_WORKS', [], $currentLang) }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('how-it-works.benefits') }}"
                            class="text-gray-400 hover:text-brand-secondary {{ $direction === 'rtl' ? 'hover:translate-x-1' : 'hover:-translate-x-1' }} transition-all duration-300 block py-1">
                            {{ __('footer.NAV_MENU_BENEFITS', [], $currentLang) }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('how-it-works.pricing') }}"
                            class="text-gray-400 hover:text-brand-secondary {{ $direction === 'rtl' ? 'hover:translate-x-1' : 'hover:-translate-x-1' }} transition-all duration-300 block py-1">
                            {{ __('footer.NAV_MENU_PRICING', [], $currentLang) }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('how-it-works.faq') }}"
                            class="text-gray-400 hover:text-brand-secondary {{ $direction === 'rtl' ? 'hover:translate-x-1' : 'hover:-translate-x-1' }} transition-all duration-300 block py-1">
                            {{ __('footer.NAV_MENU_FAQ', [], $currentLang) }}
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Column 4: Legal Section --}}
            <div>
                <h4 class="text-base font-bold mb-4 text-white relative inline-block pb-2">
                    {{ __('footer.NAV_LEGAL_TITLE', [], $currentLang) }}
                    <span class="absolute {{ $direction === 'rtl' ? 'bottom-0 right-0' : 'bottom-0 left-0' }} w-1/2 h-0.5 bg-brand-teal rounded-full"></span>
                </h4>
                <ul class="space-y-2 text-sm">
                    <li>
                        <a href="{{ route('legal.terms') }}"
                            class="text-gray-400 hover:text-brand-teal {{ $direction === 'rtl' ? 'hover:translate-x-1' : 'hover:-translate-x-1' }} transition-all duration-300 block py-1">
                            {{ __('footer.NAV_MENU_TERMS', [], $currentLang) }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('legal.privacy') }}"
                            class="text-gray-400 hover:text-brand-teal {{ $direction === 'rtl' ? 'hover:translate-x-1' : 'hover:-translate-x-1' }} transition-all duration-300 block py-1">
                            {{ __('footer.NAV_MENU_PRIVACY', [], $currentLang) }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('legal.msa') }}"
                            class="text-gray-400 hover:text-brand-teal {{ $direction === 'rtl' ? 'hover:translate-x-1' : 'hover:-translate-x-1' }} transition-all duration-300 block py-1">
                            {{ __('footer.NAV_MENU_MSA', [], $currentLang) }}
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Footer Bottom: Copyright and Status --}}
        <div class="flex flex-col md:flex-row justify-between items-center pt-4 text-xs text-gray-500 border-t border-white/5 gap-4">
            <div class="text-center {{ $direction === 'rtl' ? 'md:text-right' : 'md:text-left' }}">
                &copy; {{ date('Y') }} <span class="text-white font-semibold">{{ __('footer.PLATFORM_NAME', [], $currentLang) }}</span>. {{ __('footer.FOOTER_RIGHTS', [], $currentLang) }}
            </div>
            <div class="flex items-center gap-2 opacity-70 hover:opacity-100 transition">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                <span>{{ __('footer.FOOTER_SYSTEM_STATUS', [], $currentLang) }}</span>
            </div>
        </div>
    </div>
</footer>

</main>

</body>

</html>