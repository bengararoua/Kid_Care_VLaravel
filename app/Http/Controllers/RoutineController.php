<?php

namespace App\Http\Controllers;

use App\Models\Routine;
use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoutineController extends Controller
{
    public function index($childId)
    {
        try {
            $user = Auth::user();
            $child = Child::findOrFail($childId);
            
            // Vérifier les permissions
            if ($user->isParent() && $child->parent_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            if ($user->isTeacher() && $child->teacher_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            if ($user->isPsychologist() && $child->psychologist_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            $routines = Routine::where('child_id', $childId)
                ->where('user_id', $user->id)
                ->orderBy('day_of_week')
                ->orderBy('time')
                ->get();
            
            return response()->json($routines);
            
        } catch (\Exception $e) {
            \Log::error('Routine index error: ' . $e->getMessage());
            return response()->json([], 200);
        }
    }
    
    public function store(Request $request, $childId)
    {
        try {
            $request->validate([
                'day_of_week' => 'required|string',
                'time' => 'required|date_format:H:i',
                'activity' => 'required|string|max:255',
                'duration' => 'nullable|integer|min:1|max:180',
            ]);
            
            $routine = Routine::create([
                'child_id' => $childId,
                'user_id' => Auth::id(),
                'day_of_week' => $request->day_of_week,
                'time' => $request->time,
                'activity' => $request->activity,
                'duration' => $request->duration,
                'completed' => false,
                'order_index' => 0,
            ]);
            
            return response()->json($routine, 201);
            
        } catch (\Exception $e) {
            \Log::error('Routine store error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function update(Request $request, $id)
    {
        try {
            $routine = Routine::findOrFail($id);
            
            if ($routine->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            $routine->update($request->only(['time', 'activity', 'duration', 'completed']));
            return response()->json($routine);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function toggleComplete($id)
    {
        try {
            $routine = Routine::findOrFail($id);
            
            if ($routine->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            $routine->completed = !$routine->completed;
            $routine->save();
            
            return response()->json($routine);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $routine = Routine::findOrFail($id);
            
            if ($routine->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            
            $routine->delete();
            return response()->json(['message' => 'Routine deleted successfully']);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}