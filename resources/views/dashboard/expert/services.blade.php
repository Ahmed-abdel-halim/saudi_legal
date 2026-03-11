<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة الخدمات | Radiif</title>

    {{-- Favicons --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>body { font-family: 'Cairo', sans-serif; }</style>
</head>
<body class="bg-slate-50 pb-20">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50 px-4 py-3 shadow-sm">
        <div class="container mx-auto max-w-5xl flex justify-between items-center">
            <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                <span class="text-2xl">📦</span> باقاتي وخدماتي
            </h1>
            <a href="{{ route('dashboard.expert') }}" class="text-indigo-600 font-bold hover:bg-indigo-50 px-3 py-1 rounded transition">عودة للرئيسية</a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-5xl">
        
        @if($msg)
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 font-bold">
                {{ $msg }}
            </div>
        @endif

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 font-bold">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 font-bold">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- نموذج الإضافة (ثابت) -->
            <div class="lg:col-span-1">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 sticky top-24">
                    <h2 class="font-bold text-lg text-slate-800 mb-4">إضافة باقة جديدة</h2>
                    <form method="POST" action="{{ route('dashboard.expert.services') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">عنوان الخدمة</label>
                            <input type="text" name="title" placeholder="مثال: مراجعة كود PHP" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">التصنيف</label>
                            <select name="category" class="w-full p-2 border rounded-lg bg-white outline-none">
                                <option value="Tech">تقنية وبرمجة</option>
                                <option value="Design">تصميم وإبداع</option>
                                <option value="Marketing">تسويق</option>
                                <option value="Consulting">استشارات</option>
                                <option value="Other">أخرى</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">السعر (ريال)</label>
                                <input type="number" name="price" placeholder="500" class="w-full p-2 border rounded-lg font-bold text-emerald-600 outline-none" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 mb-1">مدة التسليم (يوم)</label>
                                <input type="number" name="delivery_days" placeholder="2" class="w-full p-2 border rounded-lg outline-none" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">ماذا ستقدم؟ (الوصف)</label>
                            <textarea name="description" rows="3" placeholder="- تقرير مفصل&#10;- إصلاح الأخطاء..." class="w-full p-2 border rounded-lg resize-none outline-none" required></textarea>
                        </div>
                        <button type="submit" name="add_service" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-xl font-bold shadow transition">
                            + نشر الخدمة
                        </button>
                    </form>
                </div>
            </div>

            <!-- قائمة الخدمات -->
            <div class="lg:col-span-2 space-y-4">
                @php
                $catLabels = [
                    'Tech'       => 'تقنية وبرمجة',
                    'Design'     => 'تصميم وإبداع',
                    'Marketing'  => 'تسويق',
                    'Consulting' => 'استشارات',
                    'Other'      => 'أخرى',
                ];
                @endphp
                @forelse($services as $service)
                    <div class="bg-white p-5 rounded-xl border border-slate-200 hover:border-emerald-400 hover:shadow-md transition relative group">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-1 rounded-full font-bold">{{ $catLabels[$service->category] ?? $service->category }}</span>
                                <h3 class="font-bold text-lg text-slate-800 mt-1">{{ $service->title }}</h3>
                            </div>
                            <div class="text-left">
                                <span class="block font-black text-xl text-emerald-600">{{ $service->price }} ر.س</span>
                                <span class="text-xs text-slate-400 font-bold">تسليم {{ $service->delivery_days }} أيام</span>
                            </div>
                        </div>
                        <p class="text-sm text-slate-600 mt-3 bg-slate-50 p-3 rounded-lg border border-slate-100">
                            {!! nl2br(e($service->description)) !!}
                        </p>
                        
                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-2 mt-4 pt-3 border-t border-slate-100">
                            {{-- Edit Button --}}
                            <button
                                onclick="openEditModal({{ $service->service_id }}, '{{ addslashes($service->title) }}', '{{ $service->category }}', {{ $service->price }}, {{ $service->delivery_days }}, '{{ addslashes($service->description) }}')"
                                class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-bold text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                تعديل
                            </button>

                            {{-- Delete Button --}}
                            <form action="{{ route('dashboard.expert.services.delete', $service->service_id) }}" method="POST" onsubmit="return confirm('حذف هذه الخدمة؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="flex items-center gap-1.5 px-3 py-1.5 text-sm font-bold text-red-500 bg-red-50 hover:bg-red-100 rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    حذف
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10 bg-white rounded-xl border border-dashed border-slate-300">
                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        </div>
                        <h3 class="font-bold text-slate-800">لا توجد خدمات مضافة</h3>
                        <p class="text-sm text-slate-500 mt-1">ابدأ بإضافة باقتك الأولى من النموذج الجانبي</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    {{-- ══════════════ Edit Service Modal ══════════════ --}}
    <div id="editModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeEditModal()"></div>

        {{-- Modal Card --}}
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10 transform transition-all duration-300" id="editModalCard">
            <div class="flex justify-between items-center mb-5">
                <h2 class="font-bold text-lg text-slate-800">✏️ تعديل الخدمة</h2>
                <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-700 transition p-1 rounded-lg hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form id="editForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">عنوان الخدمة</label>
                    <input type="text" id="edit_title" name="title" class="w-full p-2.5 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-400 outline-none" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">التصنيف</label>
                    <select id="edit_category" name="category" class="w-full p-2.5 border border-slate-200 rounded-lg bg-white outline-none">
                        <option value="Tech">تقنية وبرمجة</option>
                        <option value="Design">تصميم وإبداع</option>
                        <option value="Marketing">تسويق</option>
                        <option value="Consulting">استشارات</option>
                        <option value="Other">أخرى</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">السعر (ريال)</label>
                        <input type="number" id="edit_price" name="price" min="0" step="0.01" class="w-full p-2.5 border border-slate-200 rounded-lg font-bold text-emerald-600 outline-none" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">مدة التسليم (يوم)</label>
                        <input type="number" id="edit_delivery_days" name="delivery_days" min="1" class="w-full p-2.5 border border-slate-200 rounded-lg outline-none" required>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">الوصف</label>
                    <textarea id="edit_description" name="description" rows="4" class="w-full p-2.5 border border-slate-200 rounded-lg resize-none outline-none" required></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-xl font-bold shadow transition">
                        💾 حفظ التعديلات
                    </button>
                    <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-bold transition">
                        إلغاء
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const catLabels = {
            'Tech': 'تقنية وبرمجة',
            'Design': 'تصميم وإبداع',
            'Marketing': 'تسويق',
            'Consulting': 'استشارات',
            'Other': 'أخرى',
        };

        function openEditModal(id, title, category, price, deliveryDays, description) {
            const baseUrl = "{{ url('dashboard/expert/services') }}";
            document.getElementById('editForm').action = baseUrl + '/' + id;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_category').value = category;
            document.getElementById('edit_price').value = price;
            document.getElementById('edit_delivery_days').value = deliveryDays;
            document.getElementById('edit_description').value = description;

            const modal = document.getElementById('editModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeEditModal();
        });
    </script>

</body>
</html>
