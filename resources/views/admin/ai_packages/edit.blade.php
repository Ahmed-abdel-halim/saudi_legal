@extends('layouts.admin')

@section('title', 'تعديل الباقة')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" dir="rtl">

    <div class="flex items-center gap-4">
        <a href="{{ route('admin.ai_packages.index') }}" class="w-9 h-9 rounded-xl bg-slate-700/50 hover:bg-slate-700 text-slate-300 flex items-center justify-center transition">
            <i class="fa-solid fa-arrow-right"></i>
        </a>
        <div>
            <h1 class="text-xl font-extrabold text-white">تعديل الباقة: {{ $aiPackage->name }}</h1>
            <p class="text-slate-400 text-sm">قم بتعديل إعدادات وتفاصيل الباقة</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.ai_packages.update', $aiPackage) }}" class="space-y-5">
        @csrf @method('PUT')

        <div class="bg-sidebar rounded-2xl border border-slate-700/50 p-6 space-y-5">
            <h3 class="text-white font-bold text-sm uppercase tracking-wider border-b border-slate-700/50 pb-3">
                <i class="fa-solid fa-circle-info text-emerald-400 me-2"></i>المعلومات الأساسية
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-1.5">اسم الباقة <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $aiPackage->name) }}"
                           class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-emerald-500 transition">
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-1.5">نص الشارة</label>
                    <input type="text" name="badge_text" value="{{ old('badge_text', $aiPackage->badge_text) }}" placeholder="مثال: الأكثر طلباً 🚀"
                           class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm placeholder-slate-500 focus:outline-none focus:border-emerald-500 transition">
                </div>
            </div>
            <div>
                <label class="block text-slate-300 text-sm font-semibold mb-1.5">وصف الباقة</label>
                <textarea name="description" rows="2"
                          class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-emerald-500 transition resize-none">{{ old('description', $aiPackage->description) }}</textarea>
            </div>
        </div>

        <div class="bg-sidebar rounded-2xl border border-slate-700/50 p-6 space-y-5">
            <h3 class="text-white font-bold text-sm uppercase tracking-wider border-b border-slate-700/50 pb-3">
                <i class="fa-solid fa-coins text-amber-400 me-2"></i>السعر والفترة
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-1.5">السعر (ر.س) <span class="text-red-400">*</span></label>
                    <input type="number" name="price" value="{{ old('price', $aiPackage->price) }}" step="0.01" min="0"
                           class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-emerald-500 transition">
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-1.5">فترة الفوترة</label>
                    <select name="billing_period"
                            class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-emerald-500 transition">
                        <option value="monthly" {{ old('billing_period', $aiPackage->billing_period) === 'monthly' ? 'selected' : '' }}>شهري</option>
                        <option value="yearly" {{ old('billing_period', $aiPackage->billing_period) === 'yearly' ? 'selected' : '' }}>سنوي</option>
                        <option value="lifetime" {{ old('billing_period', $aiPackage->billing_period) === 'lifetime' ? 'selected' : '' }}>مدى الحياة</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-1.5">عدد الاستعلامات</label>
                    <input type="number" name="query_limit" value="{{ old('query_limit', $aiPackage->query_limit) }}" min="-1"
                           class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-emerald-500 transition">
                    <p class="text-slate-500 text-xs mt-1">-1 للامحدود</p>
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_unlimited" value="1" {{ old('is_unlimited', $aiPackage->is_unlimited) ? 'checked' : '' }}
                           class="w-4 h-4 rounded accent-emerald-500">
                    <span class="text-slate-300 text-sm">استخدام لامحدود</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_free" value="1" {{ old('is_free', $aiPackage->is_free) ? 'checked' : '' }}
                           class="w-4 h-4 rounded accent-emerald-500">
                    <span class="text-slate-300 text-sm">باقة مجانية</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_popular" value="1" {{ old('is_popular', $aiPackage->is_popular) ? 'checked' : '' }}
                           class="w-4 h-4 rounded accent-emerald-500">
                    <span class="text-slate-300 text-sm">مميزة (Highlighted)</span>
                </label>
            </div>
        </div>

        <div class="bg-sidebar rounded-2xl border border-slate-700/50 p-6 space-y-5">
            <h3 class="text-white font-bold text-sm uppercase tracking-wider border-b border-slate-700/50 pb-3">
                <i class="fa-solid fa-list-check text-blue-400 me-2"></i>مزايا الباقة
            </h3>
            <div id="features-container" class="space-y-2.5">
                @php $features = old('features', $aiPackage->features ?? []); @endphp
                @forelse($features as $feature)
                    <div class="flex gap-2">
                        <input type="text" name="features[]" value="{{ $feature }}"
                               class="flex-1 bg-slate-800/50 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-emerald-500 transition">
                        <button type="button" onclick="removeFeature(this)" class="w-9 h-9 flex items-center justify-center rounded-xl bg-red-500/10 text-red-400 hover:bg-red-500/20 transition text-xs">
                            <i class="fa-solid fa-minus"></i>
                        </button>
                    </div>
                @empty
                    <div class="flex gap-2">
                        <input type="text" name="features[]" placeholder="أدخل ميزة..."
                               class="flex-1 bg-slate-800/50 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm placeholder-slate-500 focus:outline-none focus:border-emerald-500 transition">
                        <button type="button" onclick="removeFeature(this)" class="w-9 h-9 flex items-center justify-center rounded-xl bg-red-500/10 text-red-400 hover:bg-red-500/20 transition text-xs">
                            <i class="fa-solid fa-minus"></i>
                        </button>
                    </div>
                @endforelse
            </div>
            <button type="button" onclick="addFeature()"
                    class="flex items-center gap-2 text-sm text-emerald-400 hover:text-emerald-300 font-semibold transition">
                <i class="fa-solid fa-plus-circle"></i> إضافة ميزة
            </button>
        </div>

        <div class="bg-sidebar rounded-2xl border border-slate-700/50 p-6 space-y-5">
            <h3 class="text-white font-bold text-sm uppercase tracking-wider border-b border-slate-700/50 pb-3">
                <i class="fa-brands fa-stripe text-violet-400 me-2"></i>Stripe & عرض
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-1.5">Stripe Price ID</label>
                    <input type="text" name="stripe_price_id" value="{{ old('stripe_price_id', $aiPackage->stripe_price_id) }}"
                           class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm font-mono text-xs focus:outline-none focus:border-emerald-500 transition">
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-1.5">لون الكارت</label>
                    <select name="color_scheme"
                            class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-emerald-500 transition">
                        <option value="emerald" {{ old('color_scheme', $aiPackage->color_scheme) === 'emerald' ? 'selected' : '' }}>🟢 أخضر (Emerald)</option>
                        <option value="indigo" {{ old('color_scheme', $aiPackage->color_scheme) === 'indigo' ? 'selected' : '' }}>🔵 أزرق (Indigo)</option>
                        <option value="gold" {{ old('color_scheme', $aiPackage->color_scheme) === 'gold' ? 'selected' : '' }}>🟡 ذهبي (Gold)</option>
                        <option value="slate" {{ old('color_scheme', $aiPackage->color_scheme) === 'slate' ? 'selected' : '' }}>⚫ رمادي (Slate)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-semibold mb-1.5">ترتيب العرض</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $aiPackage->sort_order) }}" min="0"
                           class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-emerald-500 transition">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pb-4">
            <a href="{{ route('admin.ai_packages.index') }}"
               class="px-5 py-2.5 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-700 font-semibold text-sm transition">إلغاء</a>
            <button type="submit"
                    class="px-8 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-bold text-sm shadow-lg shadow-emerald-500/20 transition flex items-center gap-2">
                <i class="fa-solid fa-floppy-disk"></i> حفظ التعديلات
            </button>
        </div>
    </form>
</div>

<script>
function addFeature() {
    const container = document.getElementById('features-container');
    const div = document.createElement('div');
    div.className = 'flex gap-2';
    div.innerHTML = `
        <input type="text" name="features[]" placeholder="أدخل ميزة..."
               class="flex-1 bg-slate-800/50 border border-slate-600 rounded-xl px-4 py-2.5 text-white text-sm placeholder-slate-500 focus:outline-none focus:border-emerald-500 transition">
        <button type="button" onclick="removeFeature(this)" class="w-9 h-9 flex items-center justify-center rounded-xl bg-red-500/10 text-red-400 hover:bg-red-500/20 transition text-xs">
            <i class="fa-solid fa-minus"></i>
        </button>
    `;
    container.appendChild(div);
}
function removeFeature(btn) {
    const container = document.getElementById('features-container');
    if (container.children.length > 1) btn.closest('div').remove();
}
</script>
@endsection
