<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Super Admin API Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => ['auth:api'], 'prefix' => 'user'], function () {

    //Notifications
    Route::group(['prefix' => 'notifications'], function () {
        Route::get('get', [\App\Http\Controllers\APIs\Notifications\NotificationController::class, 'getNotification']);
        Route::get('count', [\App\Http\Controllers\APIs\Notifications\NotificationController::class, 'getNotificationCount']);
        Route::get('clear', [\App\Http\Controllers\APIs\Notifications\NotificationController::class, 'clearNotification']);
    });

    //Objective
    Route::group(['prefix' => 'objective'], function () {
        Route::get('get', [\App\Http\Controllers\APIs\Settings\PackageController::class, 'getObjective']);
    });

    //Announcements
    Route::group(['prefix' => 'announcement'], function () {
        Route::get('get', [\App\Http\Controllers\APIs\Announcements\AnnouncementController::class, 'getAllAnnouncement']);
    });

    //Prompt Me
    Route::group(['prefix' => 'promptt/me'], function () {

        //Category (Type of Questions)
        Route::post('question/category/get', [\App\Http\Controllers\APIs\QuestionBank\QuestionBankController::class, 'getCategory']);

        //Get Question
        Route::post('question/get', [\App\Http\Controllers\APIs\PrompttMe\PrompttMeController::class, 'getQuestion']);

        //Upload Media
        Route::post('upload/media', [\App\Http\Controllers\APIs\PrompttMe\PrompttMeController::class, 'uploadPrompttMeMedia']);

        //Promptt Me save
        Route::post('save', [\App\Http\Controllers\APIs\PrompttMe\PrompttMeController::class, 'savePrompttMe']);

        //Peer Review
        Route::post('peer/review/status', [\App\Http\Controllers\APIs\PrompttMe\PrompttMeController::class, 'statusPeerReview']);

        //Video Library
        Route::post('video/library/status', [\App\Http\Controllers\APIs\PrompttMe\PrompttMeController::class, 'statusVideoLibrary']);

        //Unlink Promptt Me Video
        Route::post('unlink/video', [\App\Http\Controllers\APIs\PrompttMe\PrompttMeController::class, 'unlinkPrompttMeVideo']);
    });

    //Peer Review
    Route::group(['prefix' => 'peer/review'], function () {

        //Pending peer reviews status
        Route::get('pending/get', [\App\Http\Controllers\APIs\PeerReview\PeerReviewController::class, 'getStatus']);

        //Get Question
        Route::post('question/get', [\App\Http\Controllers\APIs\PeerReview\PeerReviewController::class, 'getQuestion']);

        //Report Video Save
        Route::post('report/video/save', [\App\Http\Controllers\APIs\ContactUs\ContactUsController::class, 'saveReportVideo']);

        //Peer Review save
        Route::post('save', [\App\Http\Controllers\APIs\PeerReview\PeerReviewController::class, 'savePeerReview']);

        //Question details
        Route::post('question/details/get', [\App\Http\Controllers\APIs\PeerReview\PeerReviewController::class, 'getQuestionDetail']);
    });

    //Community Hub
    Route::group(['prefix' => 'community/hub'], function () {

        //Get Topics
        Route::post('topic/get', [\App\Http\Controllers\APIs\CommunityHub\CommunityHubController::class, 'getTopic']);

        //Forum
        Route::group(['prefix' => 'forum'], function () {

            //Forum
            Route::post('get', [\App\Http\Controllers\APIs\CommunityHub\ForumController::class, 'getForum']);
            Route::post('add', [\App\Http\Controllers\APIs\CommunityHub\ForumController::class, 'addForum']);
            Route::post('edit', [\App\Http\Controllers\APIs\CommunityHub\ForumController::class, 'editForum']);
            Route::post('detail', [\App\Http\Controllers\APIs\CommunityHub\ForumController::class, 'detailForum']);
            Route::post('update', [\App\Http\Controllers\APIs\CommunityHub\ForumController::class, 'updateForum']);
            Route::post('delete', [\App\Http\Controllers\APIs\CommunityHub\ForumController::class, 'deleteForum']);

            //Post
            Route::group(['prefix' => 'post'], function () {
                Route::post('get', [\App\Http\Controllers\APIs\CommunityHub\ForumController::class, 'getForumPost']);
                Route::post('add', [\App\Http\Controllers\APIs\CommunityHub\ForumController::class, 'addForumPost']);
                Route::post('edit', [\App\Http\Controllers\APIs\CommunityHub\ForumController::class, 'editForumPost']);
                Route::post('update', [\App\Http\Controllers\APIs\CommunityHub\ForumController::class, 'updateForumPost']);
                Route::post('delete', [\App\Http\Controllers\APIs\CommunityHub\ForumController::class, 'deleteForumPost']);
            });

            //Post Reply
            Route::group(['prefix' => 'post/reply'], function () {
                Route::post('/', [\App\Http\Controllers\APIs\CommunityHub\ForumController::class, 'replyForumPost']);
                Route::post('get', [\App\Http\Controllers\APIs\CommunityHub\ForumController::class, 'getReplyForumPost']);
                Route::post('edit', [\App\Http\Controllers\APIs\CommunityHub\ForumController::class, 'editReplyForumPost']);
                Route::post('update', [\App\Http\Controllers\APIs\CommunityHub\ForumController::class, 'updateReplyForumPost']);
                Route::post('delete', [\App\Http\Controllers\APIs\CommunityHub\ForumController::class, 'deleteReplyForumPost']);
            });
        });

        //Chat Rooms
        Route::group(['prefix' => 'chat/room'], function () {
            Route::post('get', [\App\Http\Controllers\APIs\CommunityHub\ChatController::class, 'getChat']);
            Route::post('users/get', [\App\Http\Controllers\APIs\CommunityHub\ChatController::class, 'getChatUsers']);
            Route::post('send/message', [\App\Http\Controllers\APIs\CommunityHub\ChatController::class, 'sendMessage']);
        });
    });

    //My Feedback
    Route::group(['prefix' => 'my/feedback'], function () {
        Route::get('recent/get', [\App\Http\Controllers\APIs\MyFeedback\MyFeedbackController::class, 'getRecentFeedback']);
        Route::get('history/get', [\App\Http\Controllers\APIs\MyFeedback\MyFeedbackController::class, 'getHistoryFeedback']);
        Route::get('personal/analytics/get', [\App\Http\Controllers\APIs\ChatGPT\ChatGPTController::class, 'getPersonalAnalytics']);
    });

    //Personalization
    Route::group(['prefix' => 'personalization'], function () {

        //Account Detail
        Route::group(['prefix' => 'account/detail'], function () {

            //Profile Information
            Route::group(['prefix' => 'profile'], function () {
                Route::get('get', [\App\Http\Controllers\APIs\Settings\ProfileController::class, 'profileUser']);
                Route::post('update', [\App\Http\Controllers\APIs\Settings\ProfileController::class, 'profilePostUser']);
            });

            //Update Password
            Route::post('password/update', [\App\Http\Controllers\APIs\Settings\ProfileController::class, 'passwordUpdateUser']);
        });

        //Update Subscription
        Route::group(['prefix' => 'subscription'], function () {
            Route::get('active/get', [\App\Http\Controllers\APIs\Settings\PackageController::class, 'getActiveSubscription']);
            Route::get('receipt/invoices/get', [\App\Http\Controllers\APIs\Settings\PackageController::class, 'getReceiptInvoices']);
            Route::post('receipt/invoices/download', [\App\Http\Controllers\APIs\Settings\PackageController::class, 'downloadReceiptInvoices']);
            Route::get('payment/history/get', [\App\Http\Controllers\APIs\Settings\PackageController::class, 'getSubscriptionPaymentHistory']);
            Route::get('canceling', [\App\Http\Controllers\APIs\Settings\PackageController::class, 'cancelSubscription']);
            Route::post('update', [\App\Http\Controllers\APIs\Settings\PackageController::class, 'updateSubscription']);
            Route::post('change/method', [\App\Http\Controllers\APIs\Settings\PackageController::class, 'changeSubscriptionMethod']);
        });

        //Contact Us
        Route::group(['prefix' => 'contact/us'], function () {
            Route::get('get', [\App\Http\Controllers\APIs\ContactUs\ContactUsController::class, 'getContactUs']);
            Route::post('save', [\App\Http\Controllers\APIs\ContactUs\ContactUsController::class, 'saveContactUs']);
            Route::post('edit', [\App\Http\Controllers\APIs\ContactUs\ContactUsController::class, 'editContactUs']);
            Route::post('update', [\App\Http\Controllers\APIs\ContactUs\ContactUsController::class, 'updateContactUs']);
            Route::post('delete', [\App\Http\Controllers\APIs\ContactUs\ContactUsController::class, 'deleteContactUs']);

            //Detail
            Route::group(['prefix' => 'detail'], function () {
                Route::post('view', [\App\Http\Controllers\APIs\Support\SupportController::class, 'viewSupport']);
                Route::post('send/message', [\App\Http\Controllers\APIs\Support\SupportController::class, 'sendMessage']);
            });
        });

        //Contact Us
        Route::group(['prefix' => 'video/library'], function () {
            Route::get('get', [\App\Http\Controllers\APIs\VideoLibrary\VideoLibraryController::class, 'getVideoLibrary']);
            Route::post('update/status', [\App\Http\Controllers\APIs\VideoLibrary\VideoLibraryController::class, 'updateStatusVideoLibrary']);
            Route::post('delete', [\App\Http\Controllers\APIs\VideoLibrary\VideoLibraryController::class, 'deleteVideoLibrary']);
        });

        //Learning Resources
        Route::group(['prefix' => 'learning/resources'], function () {

            //Mentorship Programs
            Route::group(['prefix' => 'mentorship/program'], function () {
                Route::get('get', [\App\Http\Controllers\APIs\LearningResources\MentorshipProgramController::class, 'getMentorshipProgram']);
                Route::post('view', [\App\Http\Controllers\APIs\LearningResources\MentorshipProgramController::class, 'editMentorshipProgram']);

                //Suggested For You
                Route::group(['prefix' => 'suggested/for/you'], function () {
                    Route::get('get', [\App\Http\Controllers\APIs\LearningResources\MentorshipProgramController::class, 'getSuggestedForYou']);
                });
            });

            //Interview Tutorials
            Route::group(['prefix' => 'interview/tutorial'], function () {
                Route::get('get', [\App\Http\Controllers\APIs\LearningResources\InterviewTutorialController::class, 'getInterviewTutorial']);
                Route::post('view', [\App\Http\Controllers\APIs\LearningResources\InterviewTutorialController::class, 'editInterviewTutorial']);

                //Suggested For You
                Route::group(['prefix' => 'suggested/for/you'], function () {
                    Route::get('get', [\App\Http\Controllers\APIs\LearningResources\InterviewTutorialController::class, 'getSuggestedForYou']);
                });
            });

            Route::group(['prefix' => 'university'], function () {
                Route::get('get', [\App\Http\Controllers\APIs\LearningResources\UniversityController::class, 'getAllowedUniversity']);

                Route::group(['prefix' => 'detail'], function () {
                    Route::post('get', [\App\Http\Controllers\APIs\LearningResources\UniversityController::class, 'getUniversityDetail']);
                });
            });
        });

        //FAQ
        Route::group(['prefix' => 'faq'], function () {
            Route::get('get', [\App\Http\Controllers\APIs\Settings\FAQController::class, 'getFAQ']);
        });
    });
});
