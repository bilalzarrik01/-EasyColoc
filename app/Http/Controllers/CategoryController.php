<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Colocation;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function store(Request $request, Colocation $colocation): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->abortUnlessOwnerAndActive($user, $colocation);

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
        ]);

        try {
            Category::create([
                'colocation_id' => $colocation->id,
                'name' => trim($validated['name']),
            ]);
        } catch (QueryException) {
            return back()->withErrors([
                'category' => 'This category already exists.',
            ]);
        }

        return back()->with('status', 'Category added.');
    }

    public function destroy(Request $request, Colocation $colocation, Category $category): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->abortUnlessOwnerAndActive($user, $colocation);

        abort_unless($category->colocation_id === $colocation->id, 404);

        $category->delete();

        return back()->with('status', 'Category deleted.');
    }

    private function abortUnlessOwnerAndActive(User $user, Colocation $colocation): void
    {
        abort_unless($colocation->owner_id === $user->id, 403);
        abort_unless($colocation->status === 'active', 403);
    }
}
