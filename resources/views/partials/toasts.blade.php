<div x-data="{
    toasts: [],
    init() {
        @if(session('success'))
            this.add('success', {{ json_encode(__('admin.toast_success_title') ?: 'تمت العملية بنجاح') }}, {{ json_encode(session('success')) }});
        @endif
        @if(session('error'))
            this.add('error', {{ json_encode(__('admin.toast_error_title') ?: 'حدث خطأ') }}, {{ json_encode(session('error')) }});
        @endif
        @if(session('warning'))
            this.add('warning', {{ json_encode(__('admin.toast_warning_title') ?: 'تنبيه') }}, {{ json_encode(session('warning')) }});
        @endif
        @if(session('info') || session('status'))
            this.add('info', {{ json_encode(__('admin.toast_info_title') ?: 'إشعار') }}, {{ json_encode(session('info') ?: session('status')) }});
        @endif

        // Dynamic Javascript notifications via window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'success', message: '...' } }))
        window.addEventListener('notify', (e) => {
            const d = e.detail || {};
            if (typeof d === 'string') {
                this.add('info', null, d);
            } else {
                this.add(d.type || 'info', d.title, d.message);
            }
        });
    },
    add(type, title, message) {
        if (!message) return;
        const id = Date.now() + Math.random();
        const defaultTitles = {
            'success': {{ json_encode(__('admin.toast_success_title') ?: 'تمت العملية بنجاح') }},
            'error': {{ json_encode(__('admin.toast_error_title') ?: 'حدث خطأ') }},
            'warning': {{ json_encode(__('admin.toast_warning_title') ?: 'تنبيه') }},
            'info': {{ json_encode(__('admin.toast_info_title') ?: 'إشعار') }}
        };
        const toast = {
            id: id,
            type: type || 'info',
            title: title || defaultTitles[type] || 'إشعار',
            message: message,
            visible: true,
            paused: false,
            progress: 100,
            duration: 4500
        };
        this.toasts.push(toast);

        const interval = 25;
        const step = (interval / toast.duration) * 100;
        const timer = setInterval(() => {
            if (!toast.paused) {
                toast.progress -= step;
                if (toast.progress <= 0) {
                    clearInterval(timer);
                    this.remove(id);
                }
            }
        }, interval);
    },
    remove(id) {
        const item = this.toasts.find(t => t.id === id);
        if (item) {
            item.visible = false;
            setTimeout(() => {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }, 300);
        }
    }
}" 
class="fixed top-6 left-6 rtl:left-6 rtl:right-auto ltr:right-6 ltr:left-auto z-[99999] flex flex-col gap-3 pointer-events-none max-w-sm sm:max-w-md w-[calc(100vw-3rem)]">
    
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.visible"
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:-translate-x-8 scale-95"
             x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 sm:-translate-x-8 scale-95"
             @mouseenter="toast.paused = true"
             @mouseleave="toast.paused = false"
             class="pointer-events-auto relative overflow-hidden rounded-2xl p-4 shadow-2xl backdrop-blur-xl border flex flex-col gap-2 transition-all"
             :class="{
                 'bg-slate-900/95 border-emerald-500/40 shadow-emerald-500/10 text-emerald-100': toast.type === 'success',
                 'bg-slate-900/95 border-rose-500/40 shadow-rose-500/10 text-rose-100': toast.type === 'error',
                 'bg-slate-900/95 border-amber-500/40 shadow-amber-500/10 text-amber-100': toast.type === 'warning',
                 'bg-slate-900/95 border-sky-500/40 shadow-sky-500/10 text-sky-100': toast.type === 'info'
             }">
             
             <div class="flex items-start gap-3">
                 {{-- Icon badge with glowing background --}}
                 <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 border mt-0.5"
                      :class="{
                          'bg-emerald-500/20 border-emerald-500/30 text-emerald-400': toast.type === 'success',
                          'bg-rose-500/20 border-rose-500/30 text-rose-400': toast.type === 'error',
                          'bg-amber-500/20 border-amber-500/30 text-amber-400': toast.type === 'warning',
                          'bg-sky-500/20 border-sky-500/30 text-sky-400': toast.type === 'info'
                      }">
                     <i class="fa-solid text-sm"
                        :class="{
                            'fa-circle-check': toast.type === 'success',
                            'fa-triangle-exclamation': toast.type === 'error' || toast.type === 'warning',
                            'fa-circle-info': toast.type === 'info'
                        }"></i>
                 </div>

                 {{-- Text Content --}}
                 <div class="flex-1 min-w-0 pr-1 rtl:pr-1 rtl:pl-0">
                     <h4 class="text-xs font-black tracking-tight"
                         :class="{
                             'text-emerald-300': toast.type === 'success',
                             'text-rose-300': toast.type === 'error',
                             'text-amber-300': toast.type === 'warning',
                             'text-sky-300': toast.type === 'info'
                         }" x-text="toast.title"></h4>
                     <p class="text-xs text-slate-200 mt-0.5 leading-relaxed font-sans" x-text="toast.message"></p>
                 </div>

                 {{-- Close button --}}
                 <button @click="remove(toast.id)" class="text-slate-400 hover:text-white transition p-1 shrink-0 -mt-1 -mr-1">
                     <i class="fa-solid fa-xmark text-xs"></i>
                 </button>
             </div>

             {{-- Progress Bar countdown --}}
             <div class="w-full bg-white/10 h-1 rounded-full overflow-hidden mt-1">
                 <div class="h-full transition-all duration-75 rounded-full"
                      :style="`width: ${toast.progress}%`"
                      :class="{
                          'bg-emerald-400': toast.type === 'success',
                          'bg-rose-400': toast.type === 'error',
                          'bg-amber-400': toast.type === 'warning',
                          'bg-sky-400': toast.type === 'info'
                      }"></div>
             </div>
        </div>
    </template>
</div>

