<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Verifikator;
use App\Models\Gtk;
use Illuminate\Http\Request;

class VerifikatorController extends Controller
{
    public function index()
    {
        // TODO: List verifikator assignments
        return view('admin.ppdb.verifikator');
    }

    public function assign(Request $request)
    {
        // TODO: Assign GTK staff as verifikator for PPDB documents
        return redirect()->back()->with('success', 'Verifikator berhasil ditambahkan.');
    }

    public function delete($id)
    {
        // TODO: Delete verifikator assignment
        return redirect()->back()->with('success', 'Verifikator berhasil dihapus.');
    }
}
