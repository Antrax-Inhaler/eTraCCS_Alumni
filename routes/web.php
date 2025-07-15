<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminForgotPasswordController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\GeneralSettingsController;
use App\Http\Middleware\UpdateUserLastSeen;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\JobPostingController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\SampleController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\Admin\LoginAttemptController;
use App\Http\Controllers\Admin\ContentModerationController;
use App\Http\Controllers\Admin\AdminThemeController;
use App\Http\Controllers\Report\GraduateProfileController;
use App\Http\Controllers\Report\AdvancementController;
use App\Http\Controllers\Report\EmploymentStatusController;
use App\Http\Controllers\Report\EmployabilityMetricsController;
use App\Http\Controllers\Report\CompetencyMappingController;
use App\Http\Controllers\Report\GisSpatialController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AlumniMessagingController;
use App\Http\Controllers\Admin\AlumniImportController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Report\SystemPerformanceController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController as AdminLogin;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Admin\VerificationController;
use App\Http\Controllers\Admin\GraduateEmployabilityController;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Admin\AlumniProfileController;
use App\Mail\TestEmail;

use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\EmailController;


        /****************************************************************************************
         |                                                                                      |
        |                                 USER ROUTES SECTION                             |
        |                                                                                      |
        |                                                                                      |
        ****************************************************************************************/


        Route::get('/profile/company-suggestions', [ProfileController::class, 'getCompanySuggestions'])
        ->name('profile.company.suggestions');

        Route::get('/sample-tb', [SampleController::class, 'index']);
        Route::resource('posts', PostController::class);
        Route::post('/posts/{post}/react', [PostController::class, 'react']);
        Route::get('/', function () {
        return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        ]);
        });

        Route::middleware([
        'auth:sanctum',
        config('jetstream.auth_session'),
        'verified',
        ])->group(function () {
        Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
        })->name('dashboard');
        Route::get('/profile', function () {
        return Inertia::render('Profile');
        })->name('profile');
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');


        Route::prefix('profile/unemployment')->group(function () {
        Route::post('/', [JobPostingController::class, 'storeUnemploymentDetail'])
        ->name('profile.unemployment.store');
        });
        Route::prefix('profile/job-hunting')->group(function () {
        Route::get('/', [JobPostingController::class, 'index'])
        ->name('profile.jobHunting.index');

        Route::post('/', [JobPostingController::class, 'storeJobHunting'])
        ->name('profile.jobHunting.store');

        Route::put('/', [JobPostingController::class, 'update'])
        ->name('profile.jobHunting.update');
        });

        // BSIT Program Suggestions Routes
        Route::prefix('profile/program-suggestions')->group(function () {
        Route::get('/', [JobPostingController::class, 'index'])
        ->name('profile.programSuggestions.index');

        Route::post('/', [JobPostingController::class, 'storeProgramSuggestion'])
        ->name('profile.programSuggestions.store');

        Route::delete('/{suggestion}', [JobPostingController::class, 'destroy'])
        ->name('profile.programSuggestions.destroy');
        });
        Route::get('/profile/{id}/educational-background', [ProfileController::class, 'educationalBackgroundIndex'])

        ->name('profile.educationalBackground.index');

        Route::get('/profile/{id}/educational-background', [JobPostingController::class, 'educationalBackgroundIndex'])

        ->name('profile.educationalBackground.index');

        Route::post('/profile/educational-background', [ProfileController::class, 'storeEducationalBackground'])
        ->name('profile.educationalBackground.store');

        Route::put('/profile/educational-background/{background}', [ProfileController::class, 'updateEducationalBackground'])
        ->name('profile.educationalBackground.update');

        Route::delete('/profile/{id}/educational-background/{background}', [ProfileController::class, 'destroyEducationalBackground'])
        ->name('profile.educationalBackground.destroy');
        Route::post('/follow/toggle', [FollowController::class, 'toggle'])->middleware('auth');
        Broadcast::routes();
        Route::get('/profile/{id}', [ProfileController::class, 'index'])->name('profile.index');
        Route::post('/profile/{id}/toggle-follow', [ProfileController::class, 'toggleFollow'])->name('profile.toggleFollow');


        Route::post('/follow/toggle/{user}', [ProfileController::class, 'toggleFollow'])
        ->name('follow.toggle')
        ->middleware('auth');
        Route::get('/profile/{id}/employment-histories', [ProfileController::class, 'employmentHistoryIndex'])->name('profile.employmentHistory.index');

        Route::post('/profile/employment-histories', [ProfileController::class, 'storeEmploymentHistory'])
        ->name('profile.employmentHistory.store');
        Route::put('/profile/employment-histories/{history}', [ProfileController::class, 'updateEmploymentHistory'])->name('profile.employmentHistory.update');
        Route::delete('/profile/{id}/employment-histories/{history}', [ProfileController::class, 'destroyEmploymentHistory'])->name('profile.employmentHistory.destroy');

        Route::get('/profile/employment-status', [ProfileController::class, 'employmentStatusIndex'])->name('profile.employmentStatus.index');
        Route::post('/profile/employment-status', [ProfileController::class, 'storeEmploymentStatus'])->name('profile.employmentStatus.store');
        Route::put('/profile/employment-status/{status}', [ProfileController::class, 'updateEmploymentStatus'])->name('profile.employmentStatus.update');
        Route::delete('/profile/employment-status/{status}', [ProfileController::class, 'destroyEmploymentStatus'])->name('profile.employmentStatus.destroy');

        Route::get('/profile/{id}/professional-exams', [ProfileController::class, 'professionalExamIndex'])->name('profile.professionalExam.index');
        Route::post('/profile/professional-exams', [ProfileController::class, 'storeProfessionalExam'])->name('profile.professionalExam.store');
        Route::put('/profile/professional-exams/{exam}', [ProfileController::class, 'updateProfessionalExam'])->name('profile.professionalExam.update');
        Route::delete('/profile/professional-exams/{exam}', [ProfileController::class, 'destroyProfessionalExam'])->name('profile.professionalExam.destroy');

        Route::get('/profile/{id}/training-attended', [ProfileController::class, 'trainingAttendedIndex'])->name('profile.trainingAttended.index');
        Route::post('/profile/training-attended', [ProfileController::class, 'storeTrainingAttended'])->name('profile.trainingAttended.store');
        Route::put('/profile/training-attended/{training}', [ProfileController::class, 'updateTrainingAttended'])->name('profile.trainingAttended.update');
        Route::delete('/profile/training-attended/{training}', [ProfileController::class, 'destroyTrainingAttended'])->name('profile.trainingAttended.destroy');

        Route::get('/profile/{id}/company-locations', [ProfileController::class, 'companyLocationIndex'])->name('profile.companyLocation.index');
        Route::post('/profile/company-locations', [ProfileController::class, 'storeCompanyLocation'])->name('profile.companyLocation.store');
        Route::put('/profile/company-locations/{location}', [ProfileController::class, 'updateCompanyLocation'])->name('profile.companyLocation.update');
        Route::delete('/profile/company-locations/{location}', [ProfileController::class, 'destroyCompanyLocation'])->name('profile.companyLocation.destroy');
        Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
        // New route specifically for the chat interface
        Route::get('/chat/{conversation?}', [ChatController::class, 'showChatInterface'])->name('chat.show');
        Route::get('/chat/start/{user}', [ChatController::class, 'start'])->name('chat.start');
        Route::get('/chat/conversation/{conversation}', [ChatController::class, 'showConversation'])->name('chat.conversation');
        Route::get('/chat/conversation/{conversation?}', [ChatController::class, 'showConversation'])->name('chat.conversation');
        Route::get('/chat/messages/{user}', [ChatController::class, 'fetchMessages'])->name('chat.messages');
        Route::get('/chat/conversation-messages/{conversation}', [ChatController::class, 'fetchConversationMessages'])->name('chat.conversation-messages');
        Route::post('/chat/send/{user}', [ChatController::class, 'sendMessage'])->name('chat.send');
        Route::post('/chat/conversation/send/{conversation}', [ChatController::class, 'sendToConversation'])->name('chat.conversation.send');
        Route::post('/chat/seen/{user}', [ChatController::class, 'markAsSeen'])->name('chat.markAsSeen');
        Route::post('/chat/conversation/seen/{conversation}', [ChatController::class, 'markConversationAsSeen'])->name('chat.conversation.markAsSeen');
        Route::get('/chat/message/statuses', [ChatController::class, 'getMessageStatuses'])->name('chat.message.statuses');
        Route::get('/chat/search', [ChatController::class, 'searchUsers'])->name('chat.search');
        Route::post('/chat/create-group', [ChatController::class, 'createGroup'])->name('chat.create-group');
        Route::post('/chat/group/add-members', [ChatController::class, 'addGroupMembers'])->name('chat.group.add-members');
        Route::post('/chat/group/remove-member', [ChatController::class, 'removeGroupMember'])->name('chat.group.remove-member');
        Route::post('/chat/group/update', [ChatController::class, 'updateGroup'])->name('chat.group.update');
        Route::post('/chat/group/leave', [ChatController::class, 'leaveGroup'])->name('chat.group.leave');
        Route::get('/chat/typing-status/{conversation}', [ChatController::class, 'getTypingStatus'])
        ->name('chat.typing-status');
        Route::post('/chat/typing', [ChatController::class, 'setTypingStatus'])->name('chat.typing');
        Route::get('/map', [MapController::class, 'getCompanyLocationsWithUsers'])
        ->name('map.index');
        Route::get('/themes', [ThemeController::class, 'index'])->name('user.themes');
        Route::put('/themes', [ThemeController::class, 'update'])->name('user.theme.update');

        // Route::get('/reshown-items', [JobPostingController::class, 'reshownItems'])->name('reshown-items.index');
        Route::get('/job-postings', [JobPostingController::class, 'index'])->name('job-postings.index');

        Route::post('/job-postings', [JobPostingController::class, 'storeContentItem'])->name('job-postings.store');
        Route::delete('/job-postings/{jobPosting}', [JobPostingController::class, 'destroyJobPosting'])->name('job-postings.destroy');

        Route::post('/posts', [JobPostingController::class, 'storePost'])->name('posts.store');
        Route::delete('/posts/{post}', [JobPostingController::class, 'destroyPost'])->name('posts.destroy');

        Route::post('/events', [JobPostingController::class, 'storeEvent'])->name('events.store');
        Route::delete('/events/{event}', [JobPostingController::class, 'destroyEvent'])->name('events.destroy');

        Route::post('/react', [JobPostingController::class, 'react'])->name('react');


        Route::get('/comments', [CommentController::class, 'index']);

        Route::post('/comment', [CommentController::class, 'store']);
        Route::delete('/delete', [JobPostingController::class, 'delete']);



        Route::get('/dashboard', [JobPostingController::class, 'index2'])->name('dashboard');

        Route::post('/dashboard', [JobPostingController::class, 'storeContentItem'])->name('job-postings.store');
        Route::delete('/dashboard/{jobPosting}', [JobPostingController::class, 'destroyJobPosting'])->name('job-postings.destroy');

        Route::get('/profile/{id}/educational-background', [ProfileController::class, 'educationalBackgroundIndex'])
        ->name('profile.educationalBackground.index');

        Route::get('/profile/{id}/employment-histories', [ProfileController::class, 'employmentHistoryIndex'])
        ->name('profile.employmentHistory.index');

        Route::get('/profile/{id}/employment-status', [ProfileController::class, 'employmentStatusIndex'])
        ->name('profile.employmentStatus.index');

        Route::get('/profile/{id}/professional-exams', [ProfileController::class, 'professionalExamIndex'])
        ->name('profile.professionalExam.index');

        Route::get('/profile/{id}/training-attended', [ProfileController::class, 'trainingAttendedIndex'])
        ->name('profile.trainingAttended.index');

        Route::get('/profile/{id}/company-locations', [ProfileController::class, 'companyLocationIndex'])
        ->name('profile.companyLocation.index');


        });
        Route::get('/test-export', function() {
        // Test Excel export
        Excel::store(new class implements FromCollection {
        public function collection() {
        return User::limit(5)->get();
        }
        }, 'test-export.xlsx');

        // Test PDF export
        $pdf = Pdf::loadView('pdf.test', ['data' => User::limit(5)->get()]);
        $pdf->save(storage_path('app/test-export.pdf'));

        return "Exports generated! Check storage/app/";
        });
        Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store')->middleware('auth');
        Route::get('/feedback/check', [FeedbackController::class, 'check'])->name('feedback.check')->middleware('auth');
        // In routes/web.php
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('auth');
        //     Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
        //     Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
        //     Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

        //     Route::get('/password/reset', [AdminForgotPasswordController::class, 'showLinkRequestForm'])->name('admin.password.request');
        //     Route::post('/password/email', [AdminForgotPasswordController::class, 'sendResetLinkEmail'])->name('admin.password.email');

        //     Route::middleware('auth:admin')->group(function () {
        //         Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        //         });
        //         Route::get('/general-settings', [GeneralSettingsController::class, 'viewer'])->name('general.settings.viewer');
        //         Route::get('/settings', [GeneralSettingsController::class, 'index'])->name('admin.settings.index');
        //         Route::put('/settings', [GeneralSettingsController::class, 'update'])->name('admin.settings.update');
        //         Route::get('/style', [GeneralSettingsController::class, 'viewer'])->name('settings');




        /****************************************************************************************
         |                                                                                      |
        |                                🔐 ADMIN ROUTES SECTION 🔐                            |
        |                                                                                      |
        |                                                                                      |
        ****************************************************************************************/


        Route::prefix('admin')->group(function () {

        Route::get('/content-moderation', [ContentModerationController::class, 'index'])->name('admin.content-moderation.index');
        Route::patch('/content-moderation/content/{contentItem}', [ContentModerationController::class, 'updateContentStatus'])->name('admin.content-moderation.update-content');
        Route::patch('/content-moderation/comments/{comment}', [ContentModerationController::class, 'updateCommentStatus'])->name('admin.content-moderation.update-comment');
        Route::post('/content-moderation/bulk-content', [ContentModerationController::class, 'bulkUpdateContentStatus'])->name('admin.content-moderation.bulk-content');
        Route::post('/content-moderation/bulk-comments', [ContentModerationController::class, 'bulkUpdateCommentStatus'])->name('admin.content-moderation.bulk-comments');
        Route::get('/content-moderation/content/{contentItem}', [ContentModerationController::class, 'admin.content-moderation.show']);
        });

        // // Admin Reset Password
        // Route::get('admin/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('admin.password.reset');
        // Route::post('admin/reset-password', [ResetPasswordController::class, 'reset'])->name('admin.password.update');
        // Route::get('/general-settings/viewer', [GeneralSettingsController::class, 'viewer'])->name('general.settings.viewer');
        // Route::get('/settings', [GeneralSettingsController::class, 'viewer'])->name('settings.viewer');

        Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/login', [AdminLogin::class, 'create'])->middleware('guest:admin')->name('login');
        Route::post('/login', [AdminLogin::class, 'store'])->middleware('guest:admin');
        Route::post('/logout', [AdminLogin::class, 'destroy'])->middleware('auth:admin')->name('logout');

        Route::middleware('auth:admin')->group(function () {
        Route::get('/dashboard', fn () => Inertia::render('Admin/Dashboard'))->name('dashboard');

        });
        });

        Route::get('/alumni/{id}', function ($id) {
        return Inertia::render('Alumni/Show', ['id' => $id]);
        })->name('alumni.show');
        Route::middleware('auth:admin')->group(function () {
        Route::get('admin/graduate-employability', [GraduateEmployabilityController::class, 'index'])
        ->name('admin.graduate-employability');

        Route::get('admin/graduate-employability/export-excel', [GraduateEmployabilityController::class, 'exportExcel'])
        ->name('admin.graduate-employability.export-excel');

        Route::get('admin/graduate-employability/export-pdf', [GraduateEmployabilityController::class, 'exportPDF'])
        ->name('admin.graduate-employability.export-pdf');

        Route::get('/admin/companies/{company}', [CompanyController::class, 'show'])
        ->name('admin.companies.show');

        Route::get('admin/alumni/{encryptedId}', [AlumniProfileController::class, 'show'])
        ->name('admin.alumni.show');

        Route::get('/admin/login-attempts', [LoginAttemptController::class, 'index'])
        ->name('admin.login-attempts');

        Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::get('/admin/alumni', [\App\Http\Controllers\Admin\AlumniController::class, 'index'])->name('admin.alumni.index');
        Route::post('/admin/alumni/{user}/unverify', [\App\Http\Controllers\Admin\AlumniController::class, 'unverify'])->name('admin.alumni.unverify');
        Route::get('/admin/verification', [VerificationController::class, 'index'])->name('admin.alumni.verification');
        Route::post('/admin/alumni/verify/{user}', [VerificationController::class, 'verify'])->name('admin.alumni.verify');
        Route::post('/admin/alumni/manual-verify/{user}', [VerificationController::class, 'manualVerify'])->name('admin.alumni.manual-verify');
        Route::post('/admin/alumni/reject/{user}', [VerificationController::class, 'reject'])->name('admin.alumni.reject');

        Route::get('/admin/educational-tracking', [\App\Http\Controllers\Admin\EducationalTrackingController::class, 'index'])
        ->name('admin.educational-tracking');

        Route::get('/admin/employability-analytics', [\App\Http\Controllers\Admin\EmployabilityController::class, 'index'])
        ->name('admin.employability-analytics');
        Route::patch('/user/update-last-seen', [UserController::class, 'updateLastSeen'])
        ->middleware(['auth']);
        Route::get('/admin/mapping', [\App\Http\Controllers\Admin\GisMappingController::class, 'index'])
        ->name('admin.mapping');



        Route::get('/users/{id}', function ($id) {
            return Inertia::render('User/Show', [
                'id' => $id
            ]);
        })->name('user.show');

        Route::get('/api/users/{id}', [EmailController::class, 'showUserMessages']);
        Route::post('/api/messages/send', [EmailController::class, 'sendMessage']);


        Route::get('/system-users/{id}', function ($id) {
            return Inertia::render('User/Show', [
                'id' => $id
            ]);
        })->name('user.show');

        Route::get('/alumni/{id}', function ($id) {
            return Inertia::render('Alumni/Show', ['id' => $id]);
        })->name('alumni.show');

        Route::get('admin/send-email', fn () => Inertia::render('SendEmail'))->name('email.form');
            Route::post('admin/send-email', [EmailController::class, 'send'])->name('email.send');
        Route::get('/api/alumni/{id}', [EmailController::class, 'show'])->name('alumni.show');



        // Alumni Messaging Routes
        Route::prefix('admin/alumni-messaging')->group(function () {
        Route::get('/', [AlumniMessagingController::class, 'index'])->name('admin.alumni-messaging.index');
        Route::get('/{alumni}', [AlumniMessagingController::class, 'show'])->name('admin.alumni-messaging.show');
        Route::post('/send/{alumni}', [AlumniMessagingController::class, 'send'])->name('admin.alumni-messaging.send');
        Route::post('/send-bulk', [AlumniMessagingController::class, 'sendBulk'])->name('admin.alumni-messaging.send-bulk');
        });

        Route::prefix('reports')->group(function () {
        Route::get('/graduate-profiles', [GraduateProfileController::class, 'index'])
        ->name('reports.graduate-profiles.index');

        Route::get('/graduate-profiles/export', [GraduateProfileController::class, 'export'])
        ->name('reports.graduate-profiles.export');

        Route::get('/advancement', [AdvancementController::class, 'index'])
        ->name('reports.advancement.index');

        Route::get('/advancement/export', [AdvancementController::class, 'export'])
        ->name('reports.advancement.export');

        Route::get('/employment-status', [EmploymentStatusController::class, 'index'])
        ->name('reports.employment-status.index');

        Route::get('/employment-status/export', [EmploymentStatusController::class, 'export'])
        ->name('reports.employment-status.export');

        Route::get('/employability-metrics', [EmployabilityMetricsController::class, 'index'])
        ->name('reports.employability-metrics.index');

        Route::get('/employability-metrics/export', [EmployabilityMetricsController::class, 'export'])
        ->name('reports.employability-metrics.export');

        Route::get('/competency-mapping', [CompetencyMappingController::class, 'index'])
        ->name('reports.competency-mapping.index');

        Route::get('/competency-mapping/export', [CompetencyMappingController::class, 'export'])
        ->name('reports.competency-mapping.export');

        Route::get('/gis-spatial', [GisSpatialController::class, 'index'])
        ->name('reports.gis-spatial.index');

        Route::get('/gis-spatial/export', [GisSpatialController::class, 'export'])
        ->name('reports.gis-spatial.export');

        Route::get('/system-performance', [SystemPerformanceController::class, 'index'])
        ->name('reports.system-performance.index');

        Route::get('/system-performance/export', [SystemPerformanceController::class, 'export'])
        ->name('reports.system-performance.export');
        });

        Route::get('/themes', [AdminThemeController::class, 'index'])->name('admin.themes.index');
        Route::get('/themes/create', [AdminThemeController::class, 'create'])->name('admin.themes.create');
        Route::post('/themes', [AdminThemeController::class, 'store'])->name('admin.themes.store');
        Route::post('/themes/{id}/set-default', [AdminThemeController::class, 'setDefault'])->name('admin.themes.set-default');
        Route::get('/themes/{theme}/edit', [AdminThemeController::class, 'edit'])->name('admin.themes.edit');
        Route::put('/themes/{theme}', [AdminThemeController::class, 'update'])->name('admin.themes.update');

        Route::get('/alumni/import', [AlumniImportController::class, 'index'])
        ->name('admin.alumni.import');
        Route::post('/alumni/import', [AlumniImportController::class, 'store'])
        ->name('admin.alumni.import.store');
        });
        Route::prefix('admin/alumni')->middleware(['auth:admin'])->group(function () {
        Route::get('/verification', [VerificationController::class, 'index'])->name('admin.alumni.verification');
        Route::post('/verify/{user}', [VerificationController::class, 'verify'])->name('admin.alumni.verify');
        Route::post('/manual-verify/{user}', [VerificationController::class, 'manualVerify'])->name('admin.alumni.manual-verify');
        Route::post('/reject/{user}', [VerificationController::class, 'reject'])->name('admin.alumni.reject');
        Route::get('/match-details/{user}', [VerificationController::class, 'matchDetails'])
        ->name('admin.alumni.match-details');
        });














        Route::get('/resume', [ResumeController::class, 'index']);
        Route::get('/resume/download', [ResumeController::class, 'downloadPdf'])->name('resume.download');