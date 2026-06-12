<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Contracts\View\View;

class BukuController extends Controller
{
    /**
     * Display a listing of books with pagination.
     * Maximum 20 books per page for optimal performance.
     */
    public function index(): View
    {
        // Fetch books with strict pagination limit (20 per page)
        $books = Buku::orderBy('created_at', 'desc')
            ->paginate(20);

        return view('rak-buku', [
            'books' => $books,
        ]);
    }

    /**
     * Display detailed information about a specific book.
     */
    public function show(string $kodeBuku): View
    {
        $buku = Buku::findOrFail($kodeBuku);

        return view('buku-detail', [
            'buku' => $buku,
        ]);
    }
}
