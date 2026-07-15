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
<footer class="pt-16 pb-8 mt-auto" style="background-color: #060c18; border-top: 1px solid rgba(13,148,136,0.12);" dir="{{ $direction }}">
    <div class="container mx-auto px-4 lg:px-8 max-w-[1400px]">
        <div class="flex flex-col lg:flex-row justify-between gap-12 lg:gap-8 mb-12">
            
            {{-- Column 1: Logo and Description (Right side in RTL) --}}
            <div class="lg:w-1/4">
                <a href="{{ route('home') }}" class="flex items-center gap-3 mb-6 group">
                    <img src="{{ asset('images/icon.png') }}"
                        onerror="this.src='https://placehold.co/40x40/10b981/0b1120?text=R'"
                        alt="Logo"
                        class="h-10 w-10 rounded-full shadow-md object-cover ring-2 ring-brand-green/20">
                    <span class="text-xl font-black text-white group-hover:text-brand-green transition-colors duration-300">
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

            <div class="lg:w-3/4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
                {{-- Column 2: About Section --}}
                <div>
                    <h4 class="text-lg font-bold mb-6 text-white">{{ __('footer.NAV_ABOUT', [], $currentLang) }}</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="{{ route('about') }}" class="text-slate-400 hover:text-brand-green transition-all duration-200 block">{{ __('footer.NAV_MENU_ABOUT', [], $currentLang) }}</a></li>
                        <li><a href="{{ route('contact') }}" class="text-slate-400 hover:text-brand-green transition-all duration-200 block">{{ __('footer.NAV_MENU_CONTACT', [], $currentLang) }}</a></li>
                        <li><a href="{{ route('careers') }}" class="text-slate-400 hover:text-brand-green transition-all duration-200 block">{{ __('footer.NAV_MENU_CAREERS', [], $currentLang) }}</a></li>
                        <li><a href="{{ route('blog') }}" class="text-slate-400 hover:text-brand-green transition-all duration-200 block">{{ __('footer.NAV_MENU_BLOG', [], $currentLang) }}</a></li>
                    </ul>
                </div>

                {{-- Column 3: How It Works Section --}}
                <div>
                    <h4 class="text-lg font-bold mb-6 text-white">{{ __('footer.NAV_HOW_IT_WORKS', [], $currentLang) }}</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="{{ route('how-it-works') }}" class="text-slate-400 hover:text-brand-green transition-all duration-200 block">{{ __('footer.NAV_MENU_HOW_IT_WORKS', [], $currentLang) }}</a></li>
                        <li><a href="{{ route('how-it-works.benefits') }}" class="text-slate-400 hover:text-brand-green transition-all duration-200 block">{{ __('footer.NAV_MENU_BENEFITS', [], $currentLang) }}</a></li>
                        <li><a href="{{ route('how-it-works.pricing') }}" class="text-slate-400 hover:text-brand-green transition-all duration-200 block">{{ __('footer.NAV_MENU_PRICING', [], $currentLang) }}</a></li>
                        <li><a href="{{ route('how-it-works.faq') }}" class="text-slate-400 hover:text-brand-green transition-all duration-200 block">{{ __('footer.NAV_MENU_FAQ', [], $currentLang) }}</a></li>
                    </ul>
                </div>

                {{-- Column 4: Technical Services Section --}}
                <div>
                    <h4 class="text-lg font-bold mb-6 text-white">{{ __('footer.NAV_TECHNICAL_SERVICES', [], $currentLang) }}</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="{{ route('pages.services.rlhf') }}" class="text-slate-400 hover:text-brand-green transition-all duration-200 block">{{ __('footer.NAV_MENU_RLHF', [], $currentLang) }}</a></li>
                        <li><a href="{{ route('pages.services.hitl') }}" class="text-slate-400 hover:text-brand-green transition-all duration-200 block">{{ __('footer.NAV_MENU_HITL', [], $currentLang) }}</a></li>
                        <li><a href="{{ route('pages.services.data_infrastructure') }}" class="text-slate-400 hover:text-brand-green transition-all duration-200 block">{{ __('footer.NAV_MENU_INFRASTRUCTURE', [], $currentLang) }}</a></li>
                    </ul>
                </div>

                {{-- Column 5: Developers & Security Section --}}
                <div>
                    <h4 class="text-lg font-bold mb-6 text-white">{{ __('footer.NAV_DEVELOPERS_SECURITY', [], $currentLang) }}</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="{{ route('pages.api_docs') }}" class="text-slate-400 hover:text-brand-green transition-all duration-200 block">{{ __('footer.NAV_MENU_API_DOCS', [], $currentLang) }}</a></li>
                        <li><a href="{{ route('pages.security_compliance') }}" class="text-slate-400 hover:text-brand-green transition-all duration-200 block">{{ __('footer.NAV_MENU_SECURITY_COMPLIANCE', [], $currentLang) }}</a></li>
                        <li><a href="{{ route('legal.terms') }}" class="text-slate-400 hover:text-brand-green transition-all duration-200 block">{{ __('footer.NAV_MENU_TERMS', [], $currentLang) }} (B2B)</a></li>
                        <li><a href="{{ route('legal.privacy') }}" class="text-slate-400 hover:text-brand-green transition-all duration-200 block">{{ __('footer.NAV_MENU_PRIVACY', [], $currentLang) }}</a></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Footer Bottom: Copyright and Status --}}
        <div class="flex flex-col md:flex-row justify-between items-center pt-8 border-t gap-4" style="border-color: rgba(13,148,136,0.12);">
            <div class="text-slate-600 text-sm font-medium">
                &copy; {{ date('Y') }} {{ __('footer.PLATFORM_NAME', [], $currentLang) }}. {{ __('footer.FOOTER_RIGHTS', [], $currentLang) }}
            </div>
            
            <a href="#" class="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-brand-green/20 bg-brand-green/5 text-brand-green text-xs font-bold hover:bg-brand-green/10 transition-all duration-200">
                <span>{{ __('footer.FOOTER_SYSTEM_STATUS', [], $currentLang) }}</span>
                <span class="w-2 h-2 rounded-full bg-brand-green animate-pulse"></span>
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