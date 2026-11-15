{{-- Chic Modern Confirmation Modal (Glassmorphism & Neon Glow) --}}
<div x-data="confirmModalManager()" 
     x-cloak
     @keydown.escape.window="close()"
     class="relative z-[999999]">
    
    {{-- Backdrop --}}
    <div x-show="open" 
         x-transition:enter="ease-out duration-300 transition"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200 transition"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
         
        {{-- Modal Dialog Card --}}
        <div x-show="open"
             @click.outside="close()"
             x-transition:enter="ease-out duration-300 transition"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-200 transition"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="bg-slate-900/95 border border-white/10 shadow-2xl rounded-3xl p-6 sm:p-8 max-w-md w-full text-center relative overflow-hidden backdrop-blur-2xl">
             
             {{-- Ambient Glow in Corner --}}
             <div class="absolute -top-24 -left-24 w-48 h-48 rounded-full blur-3xl pointer-events-none opacity-25"
                  :class="isDanger ? 'bg-rose-500' : (isSuccess ? 'bg-emerald-500' : 'bg-amber-500')"></div>
             <div class="absolute -bottom-24 -right-24 w-48 h-48 rounded-full blur-3xl pointer-events-none opacity-15"
                  :class="isDanger ? 'bg-rose-600' : (isSuccess ? 'bg-teal-500' : 'bg-orange-500')"></div>

             {{-- Animated Icon --}}
             <div class="mx-auto w-16 h-16 rounded-2xl flex items-center justify-center mb-5 border shadow-2xl relative z-10 transition-all"
                  :class="isDanger ? 'bg-rose-500/15 border-rose-500/30 text-rose-400 shadow-rose-950/50' : (isSuccess ? 'bg-emerald-500/15 border-emerald-500/30 text-emerald-400 shadow-emerald-950/50' : 'bg-amber-500/15 border-amber-500/30 text-amber-400 shadow-amber-950/50')">
                 <i class="fa-solid text-2xl"
                    :class="isDanger ? 'fa-triangle-exclamation' : (isSuccess ? 'fa-circle-check' : 'fa-circle-question')"></i>
             </div>

             {{-- Modal Title --}}
             <h3 class="text-lg font-black text-white tracking-tight mb-2 relative z-10" x-text="title"></h3>

             {{-- Confirmation Message --}}
             <p class="text-sm text-slate-300 leading-relaxed mb-6 font-sans relative z-10 px-2" x-text="message"></p>

             {{-- Action Buttons --}}
             <div class="flex items-center justify-center gap-3 relative z-10">
                 <button type="button" 
                         @click="confirm()"
                         class="flex-1 py-3 px-5 rounded-xl font-bold text-xs transition-all shadow-lg active:scale-95 flex items-center justify-center gap-2 cursor-pointer"
                         :class="isDanger ? 'bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white shadow-rose-900/40' : (isSuccess ? 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white shadow-emerald-900/40' : 'bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-500 hover:to-orange-500 text-white shadow-amber-900/40')">
                     <i class="fa-solid fa-check text-xs"></i>
                     <span x-text="confirmText"></span>
                 </button>
                 
                 <button type="button" 
                         @click="close()"
                         class="flex-1 py-3 px-5 rounded-xl font-bold text-xs bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white border border-white/10 transition-all active:scale-95 cursor-pointer">
                     <span x-text="cancelText"></span>
                 </button>
             </div>
        </div>
    </div>
</div>

<script>
function confirmModalManager() {
    return {
        open: false,
        title: '',
        message: '',
        confirmText: '',
        cancelText: '',
        isDanger: false,
        isSuccess: false,
        callback: null,

        init() {
            const isAr = document.dir === 'rtl' || (document.documentElement.lang || 'ar').startsWith('ar');

            window.openChicConfirm = (options) => {
                const msg = options.message || '';
                const lower = msg.toLowerCase();
                
                // Smart tone detection
                const isDelete = msg.includes('حذف') || msg.includes('إلغاء اشتراك') || lower.includes('delete') || lower.includes('remove') || lower.includes('destroy') || lower.includes('suspend') || msg.includes('إيقاف');
                const isActivate = msg.includes('تفعيل') || msg.includes('اعادة') || msg.includes('إعادة') || lower.includes('reactivate') || lower.includes('activate') || lower.includes('grant');

                this.isDanger = isDelete;
                this.isSuccess = isActivate && !isDelete;
                
                this.title = options.title || (isAr ? '{{ __('admin.confirm_modal_title') }}' : 'Confirm Action');
                this.message = msg;
                
                if (options.confirmText) {
                    this.confirmText = options.confirmText;
                } else if (isDelete) {
                    this.confirmText = isAr ? '{{ __('admin.confirm_modal_delete_btn') }}' : 'Yes, Delete';
                } else if (isActivate) {
                    this.confirmText = isAr ? '{{ __('admin.confirm_modal_activate_btn') }}' : 'Yes, Reactivate';
                } else {
                    this.confirmText = isAr ? '{{ __('admin.confirm_modal_btn') }}' : 'Yes, Proceed';
                }
                
                this.cancelText = options.cancelText || (isAr ? '{{ __('admin.confirm_modal_cancel') }}' : 'Cancel');
                this.callback = options.onConfirm || null;
                this.open = true;
            };

            // Global capture-phase click interceptor for any button/link with confirm(...)
            document.addEventListener('click', (e) => {
                const btn = e.target.closest('button, a, input[type="submit"]');
                if (!btn) return;

                const onclickAttr = btn.getAttribute('onclick');
                let form = btn.closest('form');
                let onsubmitAttr = form ? form.getAttribute('onsubmit') : null;

                let msg = this.extractConfirm(onclickAttr) || (form && form.tagName === 'FORM' ? this.extractConfirm(onsubmitAttr) : null);

                if (msg) {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    window.openChicConfirm({
                        message: msg,
                        onConfirm: () => {
                            if (form && (btn.type === 'submit' || !btn.type)) {
                                form.__chic_confirmed = true;
                                if (btn.name) {
                                    let hidden = document.createElement('input');
                                    hidden.type = 'hidden';
                                    hidden.name = btn.name;
                                    hidden.value = btn.value;
                                    form.appendChild(hidden);
                                }
                                if (typeof form.submit === 'function') {
                                    form.submit();
                                } else {
                                    HTMLFormElement.prototype.submit.call(form);
                                }
                            } else if (btn.tagName === 'A' && btn.href && !btn.href.startsWith('javascript:')) {
                                window.location.href = btn.href;
                            }
                        }
                    });
                }
            }, true); // Important: Capture phase runs BEFORE inline onclick attributes

            // Global capture-phase submit interceptor for direct form submit (e.g. keyboard Enter)
            document.addEventListener('submit', (e) => {
                const form = e.target;
                if (form.__chic_confirmed) {
                    form.__chic_confirmed = false;
                    return;
                }

                const onsubmitAttr = form.getAttribute('onsubmit');
                let msg = this.extractConfirm(onsubmitAttr);
                if (msg) {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    window.openChicConfirm({
                        message: msg,
                        onConfirm: () => {
                            form.__chic_confirmed = true;
                            if (typeof form.submit === 'function') {
                                form.submit();
                            } else {
                                HTMLFormElement.prototype.submit.call(form);
                            }
                        }
                    });
                }
            }, true);
        },

        extractConfirm(str) {
            if (!str) return null;
            // Matches return confirm('...'), confirm("..."), etc.
            const match = str.match(/confirm\s*\(\s*(['"`])([\s\S]*?)\1\s*\)/i);
            if (match && match[2]) {
                return match[2].replace(/\\'/g, "'").replace(/\\"/g, '"');
            }
            return null;
        },

        confirm() {
            this.open = false;
            if (typeof this.callback === 'function') {
                this.callback();
            }
        },

        close() {
            this.open = false;
            this.callback = null;
        }
    };
}
</script>
