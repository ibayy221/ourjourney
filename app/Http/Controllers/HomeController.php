<?php

namespace App\Http\Controllers;

use App\Models\Memory;
use App\Models\Wishlist;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        // Ambil semua milestone diurutkan berdasarkan order_index
        $milestones = Memory::section('milestone')
            ->ordered()
            ->get();

        // Ambil semua branch, kelompokkan per chapter
        $branches = Memory::section('branch')
            ->ordered()
            ->get()
            ->groupBy('chapter');

        // Ambil semua wishlist bersama, kelompokkan agar yang belum tercapai tampil duluan
        $wishlists = Wishlist::orderBy('is_completed')
            ->ordered()
            ->get();

        // Otomatisasi perhitungan tanggal mulai bersama (start date):
        // 1. Cek jika ada konfigurasi JOURNEY_START_DATE di file .env (misal: JOURNEY_START_DATE=2024-01-01)
        // 2. Jika tidak ada di .env, otomatis ambil tanggal event_date paling awal dari seluruh kenangan di database
        // 3. Jika belum ada event_date, ambil tanggal kenangan pertama dibuat (created_at) atau default 2024-01-01
        $startDate = env('JOURNEY_START_DATE')
            ?: (Memory::whereNotNull('event_date')->min('event_date')
                ?: (Memory::min('created_at') ?: '2024-01-01'));

        if ($startDate instanceof \Carbon\Carbon || $startDate instanceof \DateTimeInterface) {
            $startDate = $startDate->format('Y-m-d');
        } else {
            $startDate = \Carbon\Carbon::parse($startDate)->format('Y-m-d');
        }

        return view('index', compact('milestones', 'branches', 'startDate', 'wishlists'));
    }
}
