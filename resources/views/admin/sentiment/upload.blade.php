@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">{{ __('Upload Sentiment Analysis Tasks') }}</h1>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <p>{{ session('success') }}</p>
            </div>
            
            @if(session('analysis_results'))
                <div class="bg-gray-50 p-4 rounded mb-6 text-sm">
                    <h3 class="font-bold mb-2">Results Summary:</h3>
                    <ul class="list-disc pl-5">
                        <li>Total Rows: {{ session('analysis_results')['total_rows'] }}</li>
                        <li>Tasks Created: {{ session('analysis_results')['tasks_created'] }}</li>
                        @if(!empty(session('analysis_results')['domains']))
                            <li>Domains:
                                <ul class="list-circle pl-5 mt-1">
                                    @foreach(session('analysis_results')['domains'] as $domain => $count)
                                        <li>{{ $domain }}: {{ $count }}</li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    </ul>
                    
                    @if(!empty(session('analysis_results')['errors']))
                        <div class="mt-4">
                            <h4 class="font-bold text-red-600 mb-1">Errors ({{ count(session('analysis_results')['errors']) }}):</h4>
                            <div class="max-h-40 overflow-y-auto bg-red-50 p-2 rounded border border-red-200 text-red-700 text-xs">
                                @foreach(session('analysis_results')['errors'] as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        @endif

        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <form action="{{ route('admin.sentiment.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">CSV File</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-indigo-500 transition-colors">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600 justify-center">
                            <label for="csv-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                <span>Upload a file</span>
                                <input id="csv-upload" name="csv_file" type="file" class="sr-only" accept=".csv,.txt">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">CSV up to 10MB</p>
                    </div>
                </div>
                @error('csv_file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-blue-50 p-4 rounded-md">
                <h4 class="text-sm font-medium text-blue-800 mb-2">CSV Format Requirements:</h4>
                <code class="block text-xs text-blue-700 bg-blue-100 p-2 rounded overflow-x-auto">
                    tweet text no handles,التصنيف,المجال<br>
                    Example Comment,إيجابي,طب
                </code>
                <p class="mt-2 text-xs text-blue-600">
                    <strong>Valid Classifications:</strong> إيجابي, سلبي, محايد<br>
                    <strong>Valid Domains:</strong> طب, هندسة, محاماة, تعليم, تقنية, أعمال, عام
                </p>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Upload & Process
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Simple drag and drop visual feedback
    const dropZone = document.querySelector('input[type="file"]').closest('.border-dashed');
    const input = document.getElementById('csv-upload');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight(e) {
        dropZone.classList.add('border-indigo-500', 'bg-indigo-50');
    }
    
    function unhighlight(e) {
        dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
    }
    
    dropZone.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        input.files = files;
    }

    input.addEventListener('change', function() {
        if(this.files && this.files[0]) {
             const fileName = this.files[0].name;
             this.parentElement.querySelector('span').textContent = fileName;
        }
    });
</script>
@endsection
