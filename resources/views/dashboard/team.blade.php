@extends('layouts.app')

@section('content')
<div class="bg-slate-50 text-slate-800 min-h-screen pb-20">
    
    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="bg-slate-100 p-2 rounded-lg hover:bg-slate-200 transition">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M{{ app()->getLocale() == 'ar' ? '19 12H5m7 7l-7-7 7-7' : '5 12h14M12 5l7 7-7 7' }}"></path></svg>
                </a>
                <span class="font-bold text-xl text-slate-800">{{ __('dashboard.team_management_title') }}</span>
            </div>
            <div>
                 <button onclick="document.getElementById('inviteModal').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-bold text-sm shadow-md transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    {{ __('dashboard.btn_add_member') }}
                </button>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-8 max-w-6xl">

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span class="block sm:inline font-bold">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                <span class="block sm:inline font-bold">{{ session('error') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="mb-8 p-4 rounded-xl bg-red-50 text-red-700 border border-red-200">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left rtl:text-right">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">{{ __('dashboard.tbl_name') }}</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">{{ __('dashboard.tbl_role') }}</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">{{ __('dashboard.tbl_status') }}</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">{{ __('dashboard.tbl_action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @if ($members->count() > 0)
                            @foreach($members as $member)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-sm mr-3 rtl:ml-3 border border-indigo-100">
                                            @php 
                                                $initial = !empty($member->name) ? mb_substr($member->name, 0, 1) : substr($member->email, 0, 1);
                                                echo strtoupper($initial); 
                                            @endphp
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900">{{ $member->name ?? '---' }}</p>
                                            <p class="text-xs text-slate-500">{{ $member->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded text-xs font-bold uppercase">
                                        {{ $member->role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($member->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                            {{ __('dashboard.status_active') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                            {{ __('dashboard.status_pending') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <button 
                                            onclick="openEditModal({{ json_encode($member) }})"
                                            class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors"
                                            title="{{ __('dashboard.btn_edit') }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        
                                        <form action="{{ route('dashboard.team.delete', $member->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this member?') }}')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                title="{{ __('dashboard.btn_delete') }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                        <p>{{ __('dashboard.no_members') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Invite Modal -->
    <div id="inviteModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 relative transform scale-100 transition-transform">
            <button type="button" onclick="document.getElementById('inviteModal').classList.add('hidden')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            
            <h3 class="text-xl font-bold text-slate-900 mb-4">{{ __('dashboard.modal_title') }}</h3>
            
            <form action="{{ route('dashboard.team.invite') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">{{ __('dashboard.modal_email') }}</label>
                    <input type="email" name="email" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">{{ __('dashboard.modal_name') }}</label>
                    <input type="text" name="name" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">{{ __('dashboard.tbl_role') }}</label>
                    <select name="role" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                        <option value="expert">EXPERT</option>
                        <option value="supplier">SUPPLIER</option>
                        <option value="admin">ADMIN</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">{{ __('dashboard.modal_phone') }}</label>
                    <input type="text" name="phone" placeholder="+966..." class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-bold hover:bg-indigo-700 transition shadow-md">
                    {{ __('dashboard.modal_send') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 relative transform scale-100 transition-transform">
            <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            
            <h3 class="text-xl font-bold text-slate-900 mb-4">{{ __('dashboard.modal_edit_title') }}</h3>
            
            <form id="editForm" action="" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">{{ __('dashboard.tbl_name') }}</label>
                    <input type="text" id="edit-name" name="name" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">{{ __('dashboard.tbl_role') }}</label>
                    <select id="edit-role" name="role" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                        <option value="expert">EXPERT</option>
                        <option value="supplier">SUPPLIER</option>
                        <option value="admin">ADMIN</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">{{ __('dashboard.modal_phone') }}</label>
                    <input type="text" id="edit-phone" name="phone" placeholder="+966..." class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-bold hover:bg-indigo-700 transition shadow-md">
                    {{ __('dashboard.modal_save') }}
                </button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(member) {
            const form = document.getElementById('editForm');
            form.action = `/dashboard/team/${member.id}`;
            
            document.getElementById('edit-name').value = member.name || '';
            document.getElementById('edit-role').value = member.role || 'expert';
            document.getElementById('edit-phone').value = member.phone || '';
            
            document.getElementById('editModal').classList.remove('hidden');
        }

        // Close modal on click outside
        window.onclick = function(event) {
            const inviteModal = document.getElementById('inviteModal');
            const editModal = document.getElementById('editModal');
            if (event.target == inviteModal) {
                inviteModal.classList.add('hidden');
            }
            if (event.target == editModal) {
                editModal.classList.add('hidden');
            }
        }
    </script>

</div>
@endsection
