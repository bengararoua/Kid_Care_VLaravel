<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChildController;
use App\Http\Controllers\BehaviorController;
use App\Http\Controllers\InsightController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\RoutineController;
use App\Http\Controllers\ActionPlanController;
use App\Http\Controllers\AppointmentController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/password/email', [PasswordResetController::class, 'sendResetLink']);
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);
Route::post('/password/check-token', [PasswordResetController::class, 'checkToken']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Children routes
    Route::get('/children', [ChildController::class, 'index']);
    Route::post('/children', [ChildController::class, 'store']);
    Route::get('/children/{id}', [ChildController::class, 'show']);
    Route::put('/children/{id}', [ChildController::class, 'update']);
    Route::delete('/children/{id}', [ChildController::class, 'destroy']);
    
    // Notes et Recommendations routes
    Route::get('/children/{id}/notes', [ChildController::class, 'getNotes']);
    Route::post('/children/{id}/notes', [ChildController::class, 'addNote']);
    Route::get('/children/{id}/recommendations', [ChildController::class, 'getRecommendations']);
    Route::post('/children/{id}/recommendations', [ChildController::class, 'addRecommendation']);
    Route::put('/recommendations/{id}/toggle', [RecommendationController::class, 'toggleComplete']);
    Route::delete('/recommendations/{id}', [RecommendationController::class, 'destroy']);
    
    // Behavior logs routes
    Route::get('/logs/{child_id}', [BehaviorController::class, 'index']);
    Route::post('/logs', [BehaviorController::class, 'store']);
    Route::put('/logs/{id}', [BehaviorController::class, 'update']);
    Route::delete('/logs/{id}', [BehaviorController::class, 'destroy']);
    
    // Insights routes
    Route::get('/insights/{child_id}', [InsightController::class, 'getInsights']);
    
    // User lists
    Route::get('/users/psychologists', function () {
        return App\Models\User::where('role', 'psychologist')->get(['id', 'name', 'email']);
    });
    
    Route::get('/users/teachers', function () {
        return App\Models\User::where('role', 'teacher')->get(['id', 'name', 'email']);
    });
    
    Route::get('/users/parents', function () {
        return App\Models\User::where('role', 'parent')->get(['id', 'name', 'email']);
    });
    
    // Get psychologist info for a child
    Route::get('/children/{id}/psychologist', [ChildController::class, 'getPsychologistInfo']);
    
    // Messages routes
    Route::get('/messages', [MessageController::class, 'index']);
    Route::get('/messages/conversation/{userId}', [MessageController::class, 'getConversation']);
    Route::post('/messages/send/{userId}', [MessageController::class, 'send']);
    Route::get('/messages/unread-count', [MessageController::class, 'getUnreadCount']);
    Route::post('/messages/mark-read/{messageId}', [MessageController::class, 'markAsRead']);
    
    // Appointments routes
    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::get('/appointments/{id}', [AppointmentController::class, 'show']);
    Route::put('/appointments/{id}', [AppointmentController::class, 'update']);
    Route::patch('/appointments/{id}/status', [AppointmentController::class, 'updateStatus']);
    Route::delete('/appointments/{id}', [AppointmentController::class, 'destroy']);
    Route::get('/appointments/upcoming/reminders', [AppointmentController::class, 'getUpcomingReminders']);
    
    // Profile routes
    Route::put('/profile', [ProfileController::class, 'updateProfile']);
    Route::put('/password', [ProfileController::class, 'updatePassword']);
    Route::post('/profile/delete', [ProfileController::class, 'deleteAccount']);
    
    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::delete('/notifications', [NotificationController::class, 'destroyAll']);
    
    // Export PDF routes
    Route::get('/export/child/{childId}', [ExportController::class, 'exportChildReport']);
    Route::get('/export/dashboard', [ExportController::class, 'exportDashboardReports']);
    
    // Routines routes
    Route::get('/routines/{childId}', [RoutineController::class, 'index']);
    Route::post('/routines/{childId}', [RoutineController::class, 'store']);
    Route::put('/routines/{id}', [RoutineController::class, 'update']);
    Route::patch('/routines/{id}/toggle', [RoutineController::class, 'toggleComplete']);
    Route::delete('/routines/{id}', [RoutineController::class, 'destroy']);
    
    // Action Plan routes
    Route::get('/action-plan/{childId}/generate', [ActionPlanController::class, 'generate']);
    Route::get('/action-plan/{childId}/latest', [ActionPlanController::class, 'getLatest']);
});