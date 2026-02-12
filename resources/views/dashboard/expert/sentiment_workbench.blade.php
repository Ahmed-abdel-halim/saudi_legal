@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8" x-data="sentimentWorkbench()">
    <div class="container mx-auto px-4">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">تحليل المشاعر</h1>
                <p class="text-gray-500">قم بمراجعة تصنيف التعليقات أدناه</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="bg-white px-4 py-2 rounded-lg shadow-sm">
                    <span class="text-gray-500 text-sm">التخصص:</span>
                    <span class="font-bold text-indigo-600">{{ Auth::user()->expert_domain ?? 'عام' }}</span>
                </div>
                <div class="bg-white px-4 py-2 rounded-lg shadow-sm">
                    <span class="text-gray-500 text-sm">المنجز اليوم:</span>
                    <span class="font-bold text-green-600">{{ $completed_today ?? 0 }}</span>
                </div>
            </div>
        </div>

        <!-- Task Card -->
        <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden relative">
            
            <!-- Loading Overlay -->
            <div x-show="loading" class="absolute inset-0 bg-white bg-opacity-90 flex flex-col items-center justify-center z-50" style="display: none;">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mb-3"></div>
                <p class="text-gray-600">جاري الحفظ...</p>
            </div>

            <!-- Task Content -->
            <div class="p-8">
                <!-- Domain Badge -->
                <div class="flex justify-end mb-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        {{ $task->domain }}
                    </span>
                </div>

                <!-- Comment Section -->
                <div class="mb-8 text-center">
                    <h3 class="text-gray-400 text-sm uppercase tracking-wider mb-3">التعليق</h3>
                    <div class="text-xl md:text-2xl font-medium text-gray-900 leading-relaxed dir-rtl" style="direction: rtl;">
                        "{{ $task->comment_text }}"
                    </div>
                </div>

                <div class="border-t border-gray-100 my-8"></div>

                <!-- Classification Section -->
                <div class="mb-8 text-center">
                    <h3 class="text-gray-400 text-sm uppercase tracking-wider mb-4">التصنيف المقترح</h3>
                    
                    <div class="inline-block px-8 py-3 rounded-lg text-lg font-bold
                        {{ $task->proposed_classification === 'إيجابي' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $task->proposed_classification === 'سلبي' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $task->proposed_classification === 'محايد' ? 'bg-gray-100 text-gray-800' : '' }}">
                        {{ $task->proposed_classification }}
                    </div>
                </div>

                <!-- Question Section -->
                <div class="text-center mb-8">
                    <h3 class="text-lg font-medium text-gray-800 mb-6">هل هذا التصنيف صحيح؟</h3>
                    
                    <div class="flex justify-center gap-4">
                        <button @click="markCorrect(true)" 
                                class="flex-1 max-w-[150px] py-3 px-4 bg-green-500 hover:bg-green-600 text-white rounded-lg flex items-center justify-center gap-2 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>صحيح</span>
                        </button>
                        
                        <button @click="markCorrect(false)" 
                                class="flex-1 max-w-[150px] py-3 px-4 bg-red-500 hover:bg-red-600 text-white rounded-lg flex items-center justify-center gap-2 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            <span>خطأ</span>
                        </button>
                    </div>
                </div>

                <!-- Correction Section (Conditional) -->
                <div x-show="showCorrection" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="bg-gray-50 rounded-lg p-6" style="display: none;">
                    <h4 class="text-center font-medium text-gray-800 mb-4">ما هو التصنيف الصحيح؟</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <template x-for="type in ['إيجابي', 'محايد', 'سلبي']">
                            <button @click="selectedCorrection = type"
                                    :class="{'ring-2 ring-indigo-500 ring-offset-2': selectedCorrection === type}"
                                    class="py-3 px-4 rounded-lg bg-white border border-gray-200 hover:border-indigo-300 shadow-sm text-gray-700 font-medium transition-all"
                                    x-text="type">
                            </button>
                        </template>
                    </div>

                    <div class="mt-6 flex justify-center">
                        <button @click="submitCorrection()" 
                                :disabled="!selectedCorrection"
                                :class="{'opacity-50 cursor-not-allowed': !selectedCorrection}"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-8 rounded-lg font-medium transition-colors">
                            تأكيد الإجابة
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    function sentimentWorkbench() {
        return {
            loading: false,
            showCorrection: false,
            selectedCorrection: null,
            taskId: {{ $task->id }},
            csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),

            markCorrect(isCorrect) {
                if (isCorrect) {
                    this.submit(true, null);
                } else {
                    this.showCorrection = true;
                }
            },

            submitCorrection() {
                if (!this.selectedCorrection) return;
                this.submit(false, this.selectedCorrection);
            },

            async submit(isCorrect, correctClassification) {
                this.loading = true;

                try {
                    const response = await fetch("{{ route('dashboard.expert.workbench.sentiment') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": this.csrfToken
                        },
                        body: JSON.stringify({
                            task_id: this.taskId,
                            is_correct: isCorrect,
                            correct_classification: correctClassification
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        window.location.reload(); // Load next task
                    } else {
                        alert(result.message || 'Error occurred');
                        this.loading = false;
                    }

                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while submitting.');
                    this.loading = false;
                }
            }
        };
    }
</script>
@endsection
