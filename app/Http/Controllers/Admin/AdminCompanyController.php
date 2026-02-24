<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;

class AdminCompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('cr_number', 'like', "%{$search}%")
                  ->orWhere('industry', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            if ($request->type === 'requester') {
                $query->where('is_requester', true);
            } elseif ($request->type === 'supplier') {
                $query->where('is_supplier', true);
            }
        }

        $companies = $query->orderBy('created_at', 'desc')->paginate(20);

        $totalCompanies   = Company::count();
        $verifiedCount    = Company::where('is_verified_provider', true)->count();
        $requesterCount   = Company::where('is_requester', true)->count();
        $supplierCount    = Company::where('is_supplier', true)->count();

        return view('admin.companies.index', compact(
            'companies', 'totalCompanies', 'verifiedCount', 'requesterCount', 'supplierCount'
        ));
    }

    public function toggleVerified($id)
    {
        $company = Company::findOrFail($id);
        $company->is_verified_provider = !$company->is_verified_provider;
        $company->save();

        $msg = $company->is_verified_provider ? 'Company verified successfully.' : 'Company verification removed.';
        return back()->with('success', $msg);
    }

    public function destroy($id)
    {
        $company = Company::findOrFail($id);
        $company->delete();
        return back()->with('success', 'Company permanently deleted.');
    }
}
