<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    /**
     * Display email logs
     */
    public function index(Request $request)
    {
        $query = EmailLog::with('calonSiswa')
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('to_email', 'like', "%{$search}%")
                  ->orWhere('to_name', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        // Date filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(20);

        // Stats
        $stats = [
            'total' => EmailLog::count(),
            'sent' => EmailLog::where('status', 'sent')->count(),
            'failed' => EmailLog::where('status', 'failed')->count(),
            'today' => EmailLog::whereDate('created_at', today())->count(),
        ];

        $types = EmailLog::TYPES;

        return view('admin.email-logs.index', compact('logs', 'stats', 'types'));
    }

    /**
     * Show email log detail
     */
    public function show(EmailLog $emailLog)
    {
        $emailLog->load('calonSiswa');
        return view('admin.email-logs.show', compact('emailLog'));
    }

    /**
     * Retry failed email
     */
    public function retry(EmailLog $emailLog)
    {
        if ($emailLog->status !== 'failed') {
            return redirect()->back()->with('error', 'Hanya email yang gagal yang bisa di-retry.');
        }

        // Get calon siswa
        $calonSiswa = $emailLog->calonSiswa;
        if (!$calonSiswa) {
            return redirect()->back()->with('error', 'Data pendaftar tidak ditemukan.');
        }

        try {
            // Determine which email to resend based on type
            if ($emailLog->type === EmailLog::TYPE_NOMOR_TES && $calonSiswa->nomor_tes) {
                \Mail::to($emailLog->to_email)->send(
                    new \App\Mail\NomorTesNotification($calonSiswa, $calonSiswa->nomor_tes)
                );

                // Update log
                $emailLog->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'error_message' => null,
                ]);

                return redirect()->back()->with('success', 'Email berhasil dikirim ulang.');
            }

            return redirect()->back()->with('error', 'Tipe email tidak didukung untuk retry.');
        } catch (\Exception $e) {
            $emailLog->update([
                'error_message' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Gagal mengirim ulang: ' . $e->getMessage());
        }
    }

    /**
     * Delete old logs
     */
    public function cleanup(Request $request)
    {
        $days = $request->input('days', 30);
        
        $deleted = EmailLog::where('created_at', '<', now()->subDays($days))->delete();

        return redirect()->back()->with('success', "Berhasil menghapus {$deleted} log email lama.");
    }
}
