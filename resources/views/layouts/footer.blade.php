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
<footer class="bg-dark-navy text-white pt-16 pb-8 mt-auto" style="background-color: #0B1120;" dir="{{ $direction }}">
    <div class="container mx-auto px-4 lg:px-8 max-w-[1400px]">
        <div class="flex flex-col lg:flex-row justify-between gap-12 lg:gap-8 mb-12">
            
            {{-- Column 1: Logo and Description (Right side in RTL) --}}
            <div class="lg:w-1/3">
                <a href="{{ route('home') }}" class="flex items-center gap-3 mb-6">
                    <div class="bg-brand-primary rounded-lg text-white font-bold h-10 w-10 flex items-center justify-center text-xl">
                        R
                    </div>
                    <span class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-300">
                        {{ __('footer.PLATFORM_NAME', [], $currentLang) }}
                    </span>
                </a>
                <p class="text-gray-400 text-sm leading-relaxed max-w-sm mb-6 font-normal">
                    @if($currentLang === 'ar')
                    منصة B2B متخصصة تجمع بين شركات الذكاء الاصطناعي الباحثة عن بيانات دقيقة (RLHF)، والشركات الساعية للاستثمار وتأجير كفاءاتها المتاحة في بيئة عمل مرنة وآمنة.
                    @else
                    A specialized B2B platform connecting AI companies seeking accurate data (RLHF) with companies looking to invest and rent their available competencies in a flexible and secure environment.
                    @endif
                </p>
            </div>

            <div class="lg:w-2/3 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
                {{-- Column 2: About Section --}}
                <div>
                    <h4 class="text-lg font-bold mb-6 text-white">{{ __('footer.NAV_ABOUT', [], $currentLang) }}</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="{{ route('about') }}" class="text-gray-400 hover:text-brand-primary transition-all block">{{ __('footer.NAV_MENU_ABOUT', [], $currentLang) }}</a></li>
                        <li><a href="{{ route('contact') }}" class="text-gray-400 hover:text-brand-primary transition-all block">{{ __('footer.NAV_MENU_CONTACT', [], $currentLang) }}</a></li>
                        <li><a href="{{ route('careers') }}" class="text-gray-400 hover:text-brand-primary transition-all block">{{ __('footer.NAV_MENU_CAREERS', [], $currentLang) }}</a></li>
                        <li><a href="{{ route('blog') }}" class="text-gray-400 hover:text-brand-primary transition-all block">{{ __('footer.NAV_MENU_BLOG', [], $currentLang) }}</a></li>
                    </ul>
                </div>

                {{-- Column 3: How It Works Section --}}
                <div>
                    <h4 class="text-lg font-bold mb-6 text-white">{{ __('footer.NAV_HOW_IT_WORKS', [], $currentLang) }}</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="{{ route('how-it-works') }}" class="text-gray-400 hover:text-brand-secondary transition-all block">{{ __('footer.NAV_MENU_HOW_IT_WORKS', [], $currentLang) }}</a></li>
                        <li><a href="{{ route('how-it-works.benefits') }}" class="text-gray-400 hover:text-brand-secondary transition-all block">{{ __('footer.NAV_MENU_BENEFITS', [], $currentLang) }}</a></li>
                        <li><a href="{{ route('how-it-works.pricing') }}" class="text-gray-400 hover:text-brand-secondary transition-all block">{{ __('footer.NAV_MENU_PRICING', [], $currentLang) }}</a></li>
                        <li><a href="{{ route('how-it-works.faq') }}" class="text-gray-400 hover:text-brand-secondary transition-all block">{{ __('footer.NAV_MENU_FAQ', [], $currentLang) }}</a></li>
                    </ul>
                </div>

                {{-- Column 4: Legal Section --}}
                <div>
                    <h4 class="text-lg font-bold mb-6 text-white">{{ __('footer.NAV_LEGAL_TITLE', [], $currentLang) }}</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="{{ route('legal.terms') }}" class="text-gray-400 hover:text-brand-teal transition-all block">{{ __('footer.NAV_MENU_TERMS', [], $currentLang) }} (B2B)</a></li>
                        <li><a href="{{ route('legal.privacy') }}" class="text-gray-400 hover:text-brand-teal transition-all block">{{ __('footer.NAV_MENU_PRIVACY', [], $currentLang) }}</a></li>
                        <li><a href="{{ route('legal.msa') }}" class="text-gray-400 hover:text-brand-teal transition-all block">{{ __('footer.NAV_MENU_MSA', [], $currentLang) }} (SLA)</a></li>
                        <li><a href="{{ route('legal.nda') }}" class="text-gray-400 hover:text-brand-teal transition-all block">@if($currentLang === 'ar') اتفاقية عدم الإفصاح (NDA) @else Non-Disclosure Agreement (NDA) @endif</a></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Footer Bottom: Copyright and Status --}}
        <div class="flex flex-col md:flex-row justify-between items-center pt-8 border-t border-white/10 gap-4">
            <div class="text-gray-500 text-sm">
                &copy; {{ date('Y') }} {{ __('footer.PLATFORM_NAME', [], $currentLang) }}. {{ __('footer.FOOTER_RIGHTS', [], $currentLang) }}
            </div>
            
            <a href="#" class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-teal-800 bg-teal-900/30 text-teal-400 text-xs font-semibold hover:bg-teal-900/50 transition-colors">
                <span>{{ __('footer.FOOTER_SYSTEM_STATUS', [], $currentLang) }}</span>
                <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
            </a>
        </div>
    </div>
</footer>

</main>

<!-- Pusher JS Library -->
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

<!-- Laravel Echo -->
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

<script>
// Initialize Pusher and Echo
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: '{{ config('broadcasting.connections.pusher.key') }}',
    cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
    forceTLS: true,
    encrypted: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    }
});

// Notification Dropdown Component
function notificationDropdown() {
    return {
        open: false,
        loading: false,
        notifications: [],
        unreadCount: 0,
        
        init() {
            // Fetch unread count on load
            this.fetchUnreadCount();
            
            // Poll for new notifications every 30 seconds (fallback)
            setInterval(() => {
                this.fetchUnreadCount();
            }, 30000);

            // Listen for real-time notifications via Pusher
            @auth
            if (window.Echo) {
                window.Echo.private('App.Models.User.{{ auth()->id() }}')
                    .notification((notification) => {
                        console.log('Real-time notification received:', notification);
                        
                        // Add notification to the list
                        this.notifications.unshift(notification);
                        
                        // Increment unread count
                        this.unreadCount++;
                        
                        // Show browser notification if permitted
                        if ('Notification' in window && Notification.permission === 'granted') {
                            new Notification(notification.data.title || 'New Notification', {
                                body: notification.data.message || '',
                                icon: '/images/icon.png',
                                badge: '/images/icon.png'
                            });
                        }
                    });
            }
            @endauth
        },
        
        toggleDropdown() {
            this.open = !this.open;
            if (this.open && this.notifications.length === 0) {
                this.fetchNotifications();
            }
        },
        
        async fetchUnreadCount() {
            try {
                const response = await fetch('/notifications/unread-count', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.unreadCount = data.count;
                }
            } catch (error) {
                console.error('Error fetching unread count:', error);
            }
        },
        
        async fetchNotifications() {
            this.loading = true;
            try {
                const response = await fetch('/notifications', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.notifications = data.notifications;
                    this.unreadCount = data.unread_count;
                }
            } catch (error) {
                console.error('Error fetching notifications:', error);
            } finally {
                this.loading = false;
            }
        },
        
        async markAsRead(notificationId, url) {
            try {
                await fetch(`/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });
                
                // Update local state
                const notification = this.notifications.find(n => n.id === notificationId);
                if (notification) {
                    notification.read_at = new Date().toISOString();
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                }
                
                // Navigate to URL if provided
                if (url) {
                    window.location.href = url;
                }
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        },
        
        async markAllAsRead() {
            try {
                await fetch('/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });
                
                // Update local state
                this.notifications.forEach(n => {
                    n.read_at = new Date().toISOString();
                });
                this.unreadCount = 0;
            } catch (error) {
                console.error('Error marking all as read:', error);
            }
        },
        
        getNotificationIconClass(type) {
            if (type.includes('Message')) {
                return 'bg-blue-500';
            } else if (type.includes('Service')) {
                return 'bg-green-500';
            } else if (type.includes('Review')) {
                return 'bg-yellow-500';
            } else if (type.includes('Dispute')) {
                return 'bg-red-500';
            }
            return 'bg-gray-500';
        }
    }
}
</script>

</body>

</html>