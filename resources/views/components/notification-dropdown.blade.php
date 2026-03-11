{{-- 
    Reusable Notification Dropdown Component
    
    Usage:
    @include('components.notification-dropdown', [
        'userRole' => 'expert', // or 'company'
        'position' => 'right' // or 'left' for RTL
    ])
--}}

@props([
    'userRole' => 'expert',
    'position' => 'right'
])

<div x-data="notificationDropdown()" x-init="init()" class="relative">
    <!-- Notification Bell -->
    <button 
        @click="toggleDropdown()" 
        class="relative p-2 text-white hover:bg-white/10 rounded-lg transition"
        :class="{ 'bg-white/10': isOpen }"
    >
        <i class="fa-solid fa-bell text-xl"></i>
        
        <!-- Unread Badge -->
        <span 
            x-show="Number(unreadCount) > 0" 
            x-cloak
            x-text="Number(unreadCount) > 99 ? '99+' : unreadCount"
            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full min-w-[20px] h-5 flex items-center justify-center px-1 shadow-lg"
        ></span>
    </button>

    <!-- Dropdown -->
    <div 
        x-show="isOpen" 
        @click.away="isOpen = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute {{ $position === 'left' ? 'left-0' : 'right-0' }} mt-2 w-96 max-w-[calc(100vw-2rem)] bg-gradient-to-br from-indigo-600 to-violet-700 rounded-2xl shadow-2xl shadow-indigo-900/50 overflow-hidden z-50"
        style="display: none;"
    >
        <!-- Header -->
        <div class="p-4 border-b border-white/10 flex justify-between items-center">
            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                <i class="fa-solid fa-bell text-yellow-300"></i>
                {{ __('notifications.title') }}
            </h3>
            <button 
                @click="markAllAsRead()" 
                class="text-xs text-white/80 hover:text-white transition"
                x-show="unreadCount > 0"
            >
                {{ __('notifications.mark_all_read') }}
            </button>
        </div>

        <!-- Notifications List -->
        <div class="max-h-[500px] overflow-y-auto custom-scrollbar">
            <!-- Loading State -->
            <div x-show="loading" class="p-8 text-center text-white/70">
                <i class="fa-solid fa-spinner fa-spin text-3xl mb-2"></i>
                <p class="text-sm">{{ __('notifications.loading') }}</p>
            </div>

            <!-- Error State -->
            <div x-show="error && !loading" class="p-8 text-center text-white">
                <i class="fa-solid fa-exclamation-triangle text-3xl mb-2 text-yellow-300"></i>
                <p class="text-sm" x-text="error"></p>
                <button @click="fetchNotifications()" class="mt-3 text-xs underline hover:no-underline">
                    {{ __('notifications.retry') }}
                </button>
            </div>

            <!-- Notifications -->
            <div x-show="!loading && !error" class="divide-y divide-white/10">
                <!-- Empty State -->
                <template x-if="notifications.length === 0">
                    <div class="p-8 text-center text-white/70">
                        <i class="fa-solid fa-bell-slash text-4xl mb-3 opacity-50"></i>
                        <p class="text-sm">{{ __('notifications.no_notifications') }}</p>
                    </div>
                </template>

                <!-- Notification Items -->
                <template x-for="notification in notifications" :key="notification.id">
                    <div class="p-4 hover:bg-white/5 transition cursor-pointer" :class="{ 'bg-white/5': !notification.read_at }">
                        <!-- Service Request Notification -->
                        <template x-if="notification.type === 'NewServiceRequestNotification'">
                            <div>
                                <div class="flex justify-between items-start mb-3">
                                    <!-- Hours Badge -->
                                    <div class="text-center bg-indigo-900/50 rounded-lg px-2 py-1 border border-white/10 h-fit">
                                        <p class="text-lg font-black leading-none text-white" x-text="notification.data.hours"></p>
                                        <p class="text-[9px] uppercase tracking-wider opacity-75 text-white">{{ __('expert_dashboard.hours') }}</p>
                                    </div>

                                    <!-- Client Info -->
                                    <div class="flex items-center gap-3 text-end flex-row-reverse">
                                        <div class="w-10 h-10 rounded-full bg-slate-200 overflow-hidden border-2 border-white/20 shadow-sm relative">
                                            <template x-if="notification.data.client_avatar">
                                                <img :src="'/uploads/' + notification.data.client_avatar" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!notification.data.client_avatar">
                                                <div class="w-full h-full bg-gradient-to-br from-white to-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                                                    <span x-text="(notification.data.client_name || 'C').charAt(0)"></span>
                                                </div>
                                            </template>
                                        </div>
                                        <div>
                                            <p class="font-bold text-base leading-tight text-white" x-text="notification.data.client_name"></p>
                                            <p class="text-xs text-indigo-100 opacity-80 mt-1 flex items-center gap-1 justify-end">
                                                <span x-text="notification.data.service_title"></span>
                                                <i class="fa-solid fa-layer-group text-[10px]"></i>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center justify-between gap-3 mt-4 pt-3 border-t border-white/10">
                                    <!-- Accept Button -->
                                    <form :action="'/dashboard/expert/purchase/' + notification.data.request_id + '/accept'" method="POST" class="flex-1 max-w-[120px]">
                                        <input type="hidden" name="_token" :value="csrfToken">
                                        <button type="submit" class="w-full bg-white text-indigo-700 hover:bg-indigo-50 py-2 rounded-xl text-xs font-bold transition shadow-lg shadow-indigo-900/10 flex items-center justify-center gap-2">
                                            <span>{{ __('expert_dashboard.btn_accept') }}</span>
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>

                                    <!-- Time -->
                                    <span class="text-xs font-medium opacity-70 flex items-center gap-1 text-white">
                                        <span x-text="notification.created_at_human || notification.created_at"></span>
                                        <i class="fa-regular fa-clock"></i> 
                                    </span>
                                </div>
                            </div>
                        </template>

                        <!-- Standard Notification -->
                        <template x-if="notification.type !== 'NewServiceRequestNotification'">
                            <div @click="markAsRead(notification.id, notification.data.url)">
                                <div class="flex items-start gap-3">
                                    <!-- Icon -->
                                    <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid fa-bell text-white"></i>
                                    </div>
                                    
                                    <!-- Content -->
                                    <div class="flex-1 min-w-0">
                                        <p class="font-bold text-white text-sm mb-1" x-text="notification.data.title"></p>
                                        <p class="text-xs text-white/80 line-clamp-2" x-text="notification.data.message"></p>
                                        <p class="text-xs text-white/60 mt-2" x-text="notification.created_at"></p>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function notificationDropdown() {
    return {
        isOpen: false,
        loading: false,
        error: null,
        notifications: [],
        unreadCount: 0,
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',
        pollInterval: null,

        init() {
            this.fetchNotifications();
            this.fetchUnreadCount();
            
            // Poll every 30 seconds
            this.pollInterval = setInterval(() => {
                if (!this.isOpen) {
                    this.fetchUnreadCount();
                }
            }, 30000);
        },

        toggleDropdown() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.fetchNotifications();
            }
        },

        async fetchUnreadCount() {
            try {
                const response = await fetch('/notifications/unread-count', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    }
                });

                if (!response.ok) throw new Error('Failed to fetch count');

                const data = await response.json();
                this.unreadCount = data.count || 0;
            } catch (err) {
                console.error('Unread count error:', err);
            }
        },

        async fetchNotifications() {
            this.loading = true;
            this.error = null;

            try {
                const response = await fetch('/notifications', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    }
                });

                if (!response.ok) throw new Error('Failed to fetch notifications');

                const data = await response.json();
                this.notifications = data.notifications || [];
                this.unreadCount = data.unread_count || 0;
            } catch (err) {
                console.error('Fetch error:', err);
                this.error = 'Failed to load notifications';
            } finally {
                this.loading = false;
            }
        },

        async markAsRead(id, url) {
            try {
                await fetch(`/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    }
                });

                // Update UI
                const notification = this.notifications.find(n => n.id === id);
                if (notification) {
                    notification.read_at = new Date();
                }
                
                this.fetchUnreadCount();

                // Navigate if URL provided
                if (url) {
                    window.location.href = url;
                }
            } catch (err) {
                console.error('Mark as read error:', err);
            }
        },

        async markAllAsRead() {
            try {
                await fetch('/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken
                    }
                });

                this.notifications.forEach(n => n.read_at = new Date());
                this.unreadCount = 0;
            } catch (err) {
                console.error('Mark all as read error:', err);
            }
        }
    }
}
</script>

<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}
</style>
