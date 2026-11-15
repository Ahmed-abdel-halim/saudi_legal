<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminContactMessageController extends Controller
{
    /**
     * Display a listing of contact messages with filters and KPIs.
     */
    public function index(Request $request)
    {
        $query = ContactMessage::query();

        // Search filter (name, email, phone, subject, message)
        if ($request->filled('q')) {
            $searchTerm = '%' . trim($request->q) . '%';
            $query->where(function ($sub) use ($searchTerm) {
                $sub->where('name', 'LIKE', $searchTerm)
                    ->orWhere('email', 'LIKE', $searchTerm)
                    ->orWhere('phone', 'LIKE', $searchTerm)
                    ->orWhere('subject', 'LIKE', $searchTerm)
                    ->orWhere('message', 'LIKE', $searchTerm);
            });
        }

        // Status filter (unread, read, all)
        if ($request->filled('status')) {
            if ($request->status === 'unread') {
                $query->where('is_read', false);
            } elseif ($request->status === 'read') {
                $query->where('is_read', true);
            }
        }

        // Subject filter
        if ($request->filled('subject')) {
            $query->where('subject', $request->subject);
        }

        $stats = [
            'total'   => ContactMessage::count(),
            'unread'  => ContactMessage::where('is_read', false)->count(),
            'read'    => ContactMessage::where('is_read', true)->count(),
            'today'   => ContactMessage::whereDate('created_at', Carbon::today())->count(),
        ];

        $messages = $query->orderBy('is_read', 'asc') // show unread first
                          ->orderBy('created_at', 'desc')
                          ->paginate(15)
                          ->withQueryString();

        return view('admin.contact_messages.index', compact('messages', 'stats'));
    }

    /**
     * Display the specified contact message details.
     */
    public function show($id)
    {
        $message = ContactMessage::findOrFail($id);

        // Mark as read automatically when opened
        if (!$message->is_read) {
            $message->update(['is_read' => true]);
        }

        return view('admin.contact_messages.show', compact('message'));
    }

    /**
     * Toggle read / unread status.
     */
    public function toggleRead($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->update(['is_read' => !$message->is_read]);

        $statusText = $message->is_read 
            ? __('admin.contact_marked_read') 
            : __('admin.contact_marked_unread');
        return back()->with('success', $statusText);
    }

    /**
     * Update administrative internal notes on a message.
     */
    public function updateNotes(Request $request, $id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->update([
            'admin_notes' => $request->input('admin_notes'),
        ]);

        return back()->with('success', __('admin.contact_notes_saved'));
    }

    /**
     * Remove the specified contact message from storage.
     */
    public function destroy($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->delete();

        return redirect()->route('admin.contact_messages.index')->with('success', __('admin.contact_deleted'));
    }
}

