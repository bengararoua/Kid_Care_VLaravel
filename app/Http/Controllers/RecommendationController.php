<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\Recommendation;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function index(Child $child)
    {
        $recommendations = $child->recommendations()->orderBy('created_at', 'desc')->get();
        return view('recommendations.index', compact('child', 'recommendations'));
    }

    public function store(Request $request, Child $child)
    {
        if (!auth()->user()->isPsychologist()) {
            abort(403, 'Only psychologists can create recommendations.');
        }

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'category'    => 'required|in:focus,social,relaxation,routine,sleep,nutrition',
        ]);

        Recommendation::create([
            'child_id'    => $child->id,
            'title'       => $request->title,
            'description' => $request->description,
            'category'    => $request->category,
            'is_completed'=> false,
        ]);

        return redirect()->back()->with('success', 'Recommendation added!');
    }

    public function update(Request $request, Recommendation $recommendation)
    {
        if (!auth()->user()->isPsychologist()) {
            abort(403);
        }

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'category'    => 'required|in:focus,social,relaxation,routine,sleep,nutrition',
        ]);

        $recommendation->update($request->only('title', 'description', 'category'));
        return redirect()->back()->with('success', 'Recommendation updated!');
    }

    public function toggleComplete(Recommendation $recommendation)
    {
        try {
            $recommendation->update(['is_completed' => !$recommendation->is_completed]);
            return response()->json([
                'success' => true, 
                'is_completed' => $recommendation->is_completed
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Recommendation $recommendation)
    {
        if (!auth()->user()->isPsychologist()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        try {
            $recommendation->delete();
            return response()->json(['message' => 'Recommendation removed.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}