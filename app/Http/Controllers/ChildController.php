<?php

namespace App\Http\Controllers;

use App\Models\Child;
use App\Models\User;
use App\Models\PsychologistNote;
use App\Models\Recommendation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ChildController extends Controller
{
    public function index()
{
    try {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }
        
        // Parent : voit seulement ses enfants
        if ($user->isParent()) {
            $children = Child::where('parent_id', $user->id)
                ->with(['parent', 'psychologist', 'teacher'])
                ->orderBy('created_at', 'desc')
                ->get();
        } 
        // Teacher : voit seulement les enfants qui lui sont assignés
        elseif ($user->isTeacher()) {
            $children = Child::where('teacher_id', $user->id)
                ->with(['parent', 'psychologist', 'teacher'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($child) {
                    if ($child->parent) {
                        $child->parent->email = null;
                    }
                    return $child;
                });
        } 
        // Psychologist : voit seulement les enfants qui lui sont assignés
        elseif ($user->isPsychologist()) {
            $children = Child::where('psychologist_id', $user->id)
                ->with(['parent', 'psychologist', 'teacher'])
                ->orderBy('created_at', 'desc')
                ->get();
        } 
        else {
            $children = Child::with(['parent', 'psychologist', 'teacher'])
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        return response()->json($children);
        
    } catch (\Exception $e) {
        \Log::error('Error in ChildController@index: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    public function store(Request $request)
    {
        try {
            \Log::info('Store child attempt - User: ' . Auth::id() . ', Role: ' . Auth::user()->role);
            \Log::info('Store child data: ' . json_encode($request->all()));
            
            $user = Auth::user();
            
            // Parent ou Teacher peut ajouter un enfant
            if (!$user->isParent() && !$user->isTeacher()) {
                return response()->json(['error' => 'Only parents or teachers can add children'], 403);
            }
            
            // Validation simplifiée
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'age' => 'required|integer|min:1|max:18',
                'notes' => 'nullable|string',
            ]);
            
            $data = [
                'name' => $validated['name'],
                'age' => $validated['age'],
                'notes' => $validated['notes'] ?? null,
            ];
            
            // Si c'est un parent, l'associer comme parent
            if ($user->isParent()) {
                $data['parent_id'] = $user->id;
            }
            
            // Si c'est un teacher, l'associer comme teacher
            if ($user->isTeacher()) {
                $data['teacher_id'] = $user->id;
            }
            
            // Pour les teachers : assigner un parent si spécifié
            if ($user->isTeacher() && $request->has('parent_id') && !empty($request->parent_id)) {
                $parentExists = User::where('id', $request->parent_id)->where('role', 'parent')->exists();
                if ($parentExists) {
                    $data['parent_id'] = $request->parent_id;
                }
            }
            
            // Assigner un psychologue si spécifié et si l'ID existe
            if ($request->has('psychologist_id') && !empty($request->psychologist_id)) {
                $psychologistExists = User::where('id', $request->psychologist_id)->where('role', 'psychologist')->exists();
                if ($psychologistExists) {
                    $data['psychologist_id'] = $request->psychologist_id;
                }
            }
            
            \Log::info('Data to save: ' . json_encode($data));
            
            $child = Child::create($data);
            $child->load(['parent', 'psychologist', 'teacher']);
            
            \Log::info('Child created successfully - ID: ' . $child->id);
            
            return response()->json($child, 201);
            
        } catch (ValidationException $e) {
            \Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Error in ChildController@store: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function show($id)
    {
        try {
            $user = Auth::user();
            $child = Child::with(['behaviors', 'parent', 'psychologist', 'teacher'])->findOrFail($id);
            
            if ($user->isParent() && $child->parent_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized - This is not your child'], 403);
            }
            
            if ($user->isTeacher() && $child->teacher_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized - This is not your child'], 403);
            }
            
            if ($user->isPsychologist() && $child->psychologist_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized - This child is not assigned to you'], 403);
            }
            
            return response()->json($child);
            
        } catch (\Exception $e) {
            \Log::error('Error in ChildController@show: ' . $e->getMessage());
            return response()->json(['error' => 'Child not found'], 404);
        }
    }
    
    public function update(Request $request, $id)
    {
        try {
            $child = Child::findOrFail($id);
            $user = Auth::user();
            
            if ($user->isParent() && $child->parent_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized - Only the parent can update this child'], 403);
            }
            
            if ($user->isTeacher() && $child->teacher_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized - Only the teacher who added this child can update it'], 403);
            }
            
            if (!$user->isParent() && !$user->isTeacher()) {
                return response()->json(['error' => 'Unauthorized - Only parents or teachers can update child info'], 403);
            }
            
            // Préparer les données à mettre à jour
            $updateData = [];
            
            if ($request->has('name')) {
                $updateData['name'] = $request->name;
            }
            
            if ($request->has('age')) {
                $updateData['age'] = $request->age;
            }
            
            if ($request->has('notes')) {
                $updateData['notes'] = $request->notes;
            }
            
            // Mise à jour du psychologue (pour les parents et les teachers)
            if (($user->isParent() || $user->isTeacher()) && $request->has('psychologist_id')) {
                $psychologistId = $request->psychologist_id;
                if (!empty($psychologistId)) {
                    $psychologistExists = User::where('id', $psychologistId)->where('role', 'psychologist')->exists();
                    if ($psychologistExists) {
                        $updateData['psychologist_id'] = $psychologistId;
                    } else {
                        $updateData['psychologist_id'] = null;
                    }
                } else {
                    $updateData['psychologist_id'] = null;
                }
            }
            
            // Si c'est un parent : mise à jour du teacher
            if ($user->isParent() && $request->has('teacher_id')) {
                $teacherId = $request->teacher_id;
                if (!empty($teacherId)) {
                    $teacherExists = User::where('id', $teacherId)->where('role', 'teacher')->exists();
                    if ($teacherExists) {
                        $updateData['teacher_id'] = $teacherId;
                    } else {
                        $updateData['teacher_id'] = null;
                    }
                } else {
                    $updateData['teacher_id'] = null;
                }
            }
            
            // Si c'est un teacher : mise à jour du parent
            if ($user->isTeacher() && $request->has('parent_id')) {
                $parentId = $request->parent_id;
                if (!empty($parentId)) {
                    $parentExists = User::where('id', $parentId)->where('role', 'parent')->exists();
                    if ($parentExists) {
                        $updateData['parent_id'] = $parentId;
                    } else {
                        $updateData['parent_id'] = null;
                    }
                } else {
                    $updateData['parent_id'] = null;
                }
            }
            
            \Log::info('Updating child ID: ' . $id . ' - Data: ' . json_encode($updateData));
            
            $child->update($updateData);
            $child->load(['parent', 'psychologist', 'teacher']);
            
            return response()->json($child);
            
        } catch (\Exception $e) {
            \Log::error('Error in ChildController@update: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $child = Child::findOrFail($id);
            $user = Auth::user();
            
            if ($user->isParent() && $child->parent_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized - Only the parent can delete this child'], 403);
            }
            
            if ($user->isTeacher() && $child->teacher_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized - Only the teacher who added this child can delete it'], 403);
            }
            
            if ($user->isPsychologist()) {
                return response()->json(['error' => 'Unauthorized - Psychologists cannot delete children'], 403);
            }
            
            if (!$user->isParent() && !$user->isTeacher()) {
                return response()->json(['error' => 'Unauthorized - Only parents or teachers can delete children'], 403);
            }
            
            $child->delete();
            \Log::info('Child deleted - ID: ' . $id . ' by user: ' . $user->id);
            
            return response()->json(['message' => 'Child deleted successfully']);
            
        } catch (\Exception $e) {
            \Log::error('Error in ChildController@destroy: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function getPsychologistInfo($child_id)
    {
        try {
            $user = Auth::user();
            $child = Child::findOrFail($child_id);
            
            if ($user->isParent() && $child->parent_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized - This is not your child'], 403);
            }
            
            if ($user->isTeacher() && $child->teacher_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized - This is not your child'], 403);
            }
            
            if ($user->isPsychologist() && $child->psychologist_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized - This child is not assigned to you'], 403);
            }
            
            if ($child->psychologist_id) {
                $psychologist = User::where('id', $child->psychologist_id)
                    ->select('id', 'name', 'email')
                    ->first();
                return response()->json($psychologist);
            }
            
            return response()->json(['message' => 'No psychologist assigned'], 404);
            
        } catch (\Exception $e) {
            \Log::error('Error in ChildController@getPsychologistInfo: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getNotes($id)
    {
        try {
            $user = Auth::user();
            $child = Child::findOrFail($id);
            
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
            
            $notes = $child->psychologistNotes()
                ->with('psychologist')
                ->orderBy('session_date', 'desc')
                ->get();
                
            return response()->json($notes);
            
        } catch (\Exception $e) {
            \Log::error('Error in ChildController@getNotes: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function addNote(Request $request, $id)
    {
        try {
            if (!auth()->user()->isPsychologist()) {
                return response()->json(['error' => 'Unauthorized - Only psychologists can add notes'], 403);
            }
            
            $request->validate([
                'note' => 'required|string',
                'session_date' => 'required|date'
            ]);
            
            $child = Child::findOrFail($id);
            
            // Vérifier que le psychologue est bien assigné à cet enfant
            if ($child->psychologist_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized - This child is not assigned to you'], 403);
            }
            
            $note = PsychologistNote::create([
                'child_id' => $id,
                'psychologist_id' => auth()->id(),
                'note' => $request->note,
                'session_date' => $request->session_date
            ]);
            
            $note->load('psychologist');
            
            \Log::info('Note added for child ID: ' . $id . ' by psychologist: ' . auth()->id());
            
            return response()->json($note, 201);
            
        } catch (\Exception $e) {
            \Log::error('Error in ChildController@addNote: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getRecommendations($id)
    {
        try {
            $user = Auth::user();
            $child = Child::findOrFail($id);
            
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
            
            $recommendations = $child->recommendations()
                ->orderBy('created_at', 'desc')
                ->get();
                
            return response()->json($recommendations);
            
        } catch (\Exception $e) {
            \Log::error('Error in ChildController@getRecommendations: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function addRecommendation(Request $request, $id)
    {
        try {
            if (!auth()->user()->isPsychologist()) {
                return response()->json(['error' => 'Unauthorized - Only psychologists can add recommendations'], 403);
            }
            
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'category' => 'required|string|in:focus,social,relaxation,routine,sleep,nutrition'
            ]);
            
            $child = Child::findOrFail($id);
            
            // Vérifier que le psychologue est bien assigné à cet enfant
            if ($child->psychologist_id !== auth()->id()) {
                return response()->json(['error' => 'Unauthorized - This child is not assigned to you'], 403);
            }
            
            $recommendation = Recommendation::create([
                'child_id' => $id,
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'is_completed' => false
            ]);
            
            \Log::info('Recommendation added for child ID: ' . $id . ' by psychologist: ' . auth()->id());
            
            return response()->json($recommendation, 201);
            
        } catch (\Exception $e) {
            \Log::error('Error in ChildController@addRecommendation: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}