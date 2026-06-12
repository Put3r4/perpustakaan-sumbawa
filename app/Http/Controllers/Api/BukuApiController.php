<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BukuApiController extends Controller
{
    /**
     * Increment the view count for a specific book.
     * This endpoint is called when a user views a book detail for at least 60 seconds.
     * Uses atomic increment to prevent race conditions.
     *
     * @param  string  $id  Book ID (KodeBuku)
     */
    public function incrementView(Request $request, string $id): JsonResponse
    {
        // Validate the book ID exists
        $validator = Validator::make(['id' => $id], [
            'id' => ['required', 'string', 'exists:buku,KodeBuku'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid book ID.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Find the book
            $buku = Buku::findOrFail($id);

            // Atomic increment - thread-safe operation
            $buku->increment('views_count');

            // Reload to get updated count
            $buku->refresh();

            return response()->json([
                'success' => true,
                'message' => 'View count incremented successfully.',
                'data' => [
                    'KodeBuku' => $buku->KodeBuku,
                    'views_count' => $buku->views_count,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to increment view count.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
