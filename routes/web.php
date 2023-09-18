<?php

/** @var Router $router */

use Laravel\Lumen\Routing\Router;

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {

    //client routes
    $router->get('story/view', 'SuccessStoryController@all');

    //Auth
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');
    $router->post('forgot-password', 'AuthController@forgotPasswordStepOne');
    $router->post('forgot-password-step-two', 'AuthController@forgotPasswordStepTwo');

    //Admin Auth
    $router->post('admin/auth', 'AdminsController@AuthAdmin');

    //Files
    $router->post('files', 'FilesController@SaveFile');

    //Payments Webhook
    $router->post('payments/confirm', 'PaymentGatewayController@confirmPayment');

    //Payments Redirect
    $router->get('payments/message', 'PaymentGatewayController@paymentRedirect');


    //Public Trainer Search API
    $router->get('public/trainers/all','TrainerController@getPublicTrainerData');
    $router->post('public/notifications/actions/create-notifications','TrainerController@newTrainerRequest');

    //Checks.
    $router->post('checks/checkAccountInfo', 'AuthController@checkAccountInfo');

    //page settings list
    $router->group(['prefix' => 'setting'], function () use ($router) {
        $router->post('list', [ 'uses' => 'SettingController@list']);
    });

    //newest added routes
    $router->group(['middleware' => 'gateway:api'], function () use ($router) {

        //page settings
        $router->group(['prefix' => 'setting'], function () use ($router) {
            $router->post('update', [ 'middleware' => 'auth.check:setting,update', 'uses' => 'SettingController@update']);
        });

        //therapy
        $router->group(['prefix' => 'therapy'], function () use ($router) {
            $router->post('add', [ 'middleware' => 'auth.check:therapy,add', 'uses' => 'TherapyController@add']);
            $router->get('list', [ 'middleware' => 'auth.check:therapy,list', 'uses' => 'TherapyController@list']);
            $router->post('update', [ 'middleware' => 'auth.check:therapy,update', 'uses' => 'TherapyController@update']);
            $router->post('delete', [ 'middleware' => 'auth.check:therapy,delete', 'uses' => 'TherapyController@DeleteTherapy']);
            $router->post('search', [ 'middleware' => 'auth.check:therapy,search', 'uses' => 'TherapyController@searchTherapy']);
            $router->get('therapy', [ 'middleware' => 'auth.check:therapy,getTherapy', 'uses' => 'TherapyController@getTherapy']);
        });

        //therapy meeting
        // $router->group(['prefix' => 'meeting'], function () use ($router) {
        //     $router->post('new-client-therapy-meeting', [ 'middleware' => 'auth.check:meeting,add', 'uses' => 'TherapyMeetingController@add']);
        //     $router->post('reserved-times', [ 'middleware' => 'auth.check:meeting,reserved_time', 'uses' => 'TherapyMeetingController@reserved_time']);
        //     $router->get('my-meetings', [ 'middleware' => 'auth.check:meeting,my_meetings', 'uses' => 'TherapyMeetingController@my_meetings']);
        // });

         //Success Stories
         $router->group(['prefix' => 'story'], function () use ($router) {
            $router->post('add', [ 'middleware' => 'auth.check:story,add', 'uses' => 'SuccessStoryController@add']);
            $router->get('list', [ 'middleware' => 'auth.check:story,list', 'uses' => 'SuccessStoryController@list']);
            $router->post('update', [ 'middleware' => 'auth.check:story,update', 'uses' => 'SuccessStoryController@update']);
        });

         //Site Ads
         $router->group(['prefix' => 'siteAd'], function () use ($router) {
            $router->post('add', [ 'middleware' => 'auth.check:siteAd,add', 'uses' => 'SiteAdController@add']);
            $router->get('order_list', [ 'middleware' => 'auth.check:siteAd,order_list', 'uses' => 'SiteAdController@order_list']);
            $router->get('list', [ 'middleware' => 'auth.check:siteAd,list', 'uses' => 'SiteAdController@list']);
            $router->post('update', [ 'middleware' => 'auth.check:siteAd,update', 'uses' => 'SiteAdController@update']);
        });

        //Previous Projects Ads
        $router->group(['prefix' => 'previousProject'], function () use ($router) {
            $router->post('add', [ 'middleware' => 'auth.check:previousProject,add', 'uses' => 'PreviousProjectController@add']);
            $router->get('list', [ 'middleware' => 'auth.check:previousProject,list', 'uses' => 'PreviousProjectController@list']);
            $router->post('update', [ 'middleware' => 'auth.check:previousProject,update', 'uses' => 'PreviousProjectController@update']);
        });

        
        //Status change
        $router->group(['prefix' => 'status'], function () use ($router) {
            $router->post('change', 'StatusController@change');
        });

        //delete module
        $router->post('delete', 'DeleteController@delete');

        //Image Uploader
        //page settings
        $router->group(['prefix' => 'image', 'middleware' => 'auth.check:image,add'], function () use ($router) {
            $router->post('add', 'FilesController@AddImage');
        });

        $router->group(['prefix' => 'subscriptions', 'middleware' => 'auth.check:image,add'], function () use ($router) {
            $router->post('adminSubscribe', 'UserSubscriptionController@subcriptionAdAdmin');
        });


    });

    $router->group(['middleware' => 'auth:api'], function () use ($router) {

        $router->get('common/subscriptions/get-all', 'SubscriptionPlansController@getSubscriptionPlans');

        //Admin Ops.
        $router->post('admin/users/update-profile', 'AuthController@updateMeAdmin');
        $router->post('admin/users/update-gym', 'AuthController@updateGymInformation');

        $router->post('admin/users/update-trainer', 'AuthController@updateTrainer');
        $router->post('admin/users/update-doctor', 'AuthController@updateDoctor');

        $router->post('admin/gyms/delete-gallery-item', 'GymController@deleteGymGalleryItem');

        $router->group(['prefix' => 'admin'], function () use ($router) {
            $router->post('subscriptions/upsert', 'SubscriptionPlansController@upsertSubscriptionPlan');

            $router->post('staff/upsert', 'AdminsController@upsertAdmin');
            $router->post('staff/delete', 'AdminsController@deleteAdmin');
            $router->post('staff/get', 'AdminsController@GetAdmins');
        });

        //User Common.
        $router->post('users/check', 'AuthController@check');
        $router->post('users/check-without-token-refresh', 'AuthController@checkWithoutTokenRefresh');
        $router->get('users/one/{id}', 'AuthController@getOneUser');
        $router->get('users/me', 'AuthController@getMe');
        $router->get('users/search/{search_key}', 'ClientController@search');
        $router->post('users/get-by-ids', 'UserCommonController@getClientsByIDs');
        $router->post('notifications/actions/mark-as-seen', 'NotificationsAndRequestsController@markAsSeen');
        $router->get('notifications/actions/get-my-notifications', 'NotificationsAndRequestsController@getMyNotifications');
        $router->post('users/update-profile', 'AuthController@updateMe');
        $router->post('users/update-trainer', 'AuthController@updateTrainer');
        $router->post('users/update-doctor', 'AuthController@updateDoctor');
        $router->post('users/update-sub-profile', 'AuthController@updateSubMe');

        //User Health Data Consent.
        $router->post('users/health-data-consent', 'UserCommonController@healthDataConsent');

        //Pro
        //Payment Gateways.
        $router->post('payments/subscribe-now', 'PaymentGatewayController@makePayment');

        //Free Trial
        $router->post('payments/activate-free-trial', 'UserSubscriptionController@activateFreeTrial');

        //Pay for DoctorMeeting
        $router->post('payments/doctor-meeting-payment/pay-now', 'PaymentGatewayController@payForDocMeetingNow');

        //User Wallet.
        $router->post('wallet/top-up-now', 'PaymentGatewayController@makeTopUp');
        $router->post('wallet/common-payments', 'PaymentGatewayController@commonPayments');
        $router->get('wallet/get-balance', 'WalletController@getWalletBalance');
        $router->get('wallet/get-history', 'WalletController@getWalletTransactions');
        $router->post('wallet/get-user-history', 'WalletController@getUserWalletTransactions');

        //Exclusive Gym Payments
        $router->post('exclusive-gyms/pay-now', 'PaymentGatewayController@payForExclusiveGym');
        $router->post('commercial-gyms/pay-now', 'PaymentGatewayController@payForCommercialGym');

        //Gym Finances
        $router->post('gym-finances/details', 'GymFinanceController@GetGymFinanceDetails');

        //FileUploads
        $router->post('users/file-uploads/avatar', 'FilesController@SaveAvatar');
        $router->post('users/file-uploads/gym-gallery', 'FilesController@SaveGymGallery');
        $router->post('users/file-uploads/product', 'FilesController@SaveProductImage');
        $router->post('users/file-uploads/signature', 'FilesController@SaveDocSignature');
        $router->post('users/file-uploads/seal', 'FilesController@SaveDocSeal');
        $router->post('users/file-uploads/lab-report', 'FilesController@SaveLabReport');
        $router->post('users/file-uploads/delete-file', 'FilesController@deleteFile');
        $router->post('users/file-uploads/exercise-files', 'FilesController@SaveExerciseRelatedFiles');
        $router->post('users/file-uploads/resource-files', 'FilesController@SaveResourceRelatedFiles');

        //Ads gallery
        $router->post('users/ads/new-ads-gallery-item', 'AdsGalleryController@SaveAdGalleryItem');
        $router->post('users/ads/get-all', 'AdsGalleryController@getAllAds');
        $router->post('users/ads/delete-ad', 'AdsGalleryController@deleteAd');

        //Trainer.
        $router->get('trainers/get-all', 'TrainerController@getAllTrainers');
        $router->get('trainer/clients/all', 'TrainerController@getTrainerClients');
        $router->get('trainer/clients/all/{trainerID}', 'TrainerController@getTrainerClientsByTrainerID');
        $router->post('trainer/clients/search', 'TrainerController@searchTrainerClients');
        $router->post('trainer/actions/update', 'TrainerController@updateTrainerSpecifics');

        //Refactor THIS.
        $router->get('trainer/clients/search', 'TrainerController@searchTrainerClients');

        $router->post('trainer/clients/invite', 'ClientRequestController@newTrainerRequests');
        $router->get('trainer/search/{search_key}', 'TrainerController@searchTrainers');
        $router->get('trainer/notifications/home', 'TrainerController@getHomeInformation');


        //Doctors
        $router->get('doctors/all', 'DoctorController@getAllDoctors');
        $router->get('doctors/all-old', 'DoctorController@getAllOldDoctors');
        $router->get('doctors/all-new', 'DoctorController@getAllNewDoctors');
        $router->post('doctors/toggle-status', 'DoctorController@toggleStatus');
        $router->post('doctors/approve', 'DoctorController@approveNewDoctor');
        $router->post('doctors/search', 'DoctorController@searchDoctors');
        $router->post('doctors/actions/toggle-online', 'DoctorController@setOnlineStatus');
        $router->get('doctors/actions/get-payment-history', 'WalletController@getMyPaymentsHistory');

        //Clients.
        $router->get('clients/get-all','ClientController@getAll');
        $router->post('clients/requests/{request_id}/accept', 'ClientRequestController@acceptTrainerRequest');
        $router->post('clients/requests/{request_id}/reject', 'ClientRequestController@rejectTrainerRequest');
        $router->post('clients/actions/remove-trainer','ClientController@removeTrainer');
        $router->post('clients/actions/verify-request','ClientController@verifyRequest');
        $router->post('clients/actions/switch-trainers','ClientRequestController@switchTrainers');
        $router->post('clients/actions/save-additional-data','ClientController@saveAdditionalData');

        //Gyms.
        $router->get('gyms/actions/{id}/get', 'GymController@getGymByID');
        $router->post('gyms/commercial/search', 'GymController@searchCommercialGyms');
        $router->post('gyms/exclusive/search', 'GymController@searchExclusiveGyms');

        //Subscription.
        $router->post('subscription/purchase', 'UserSubscriptionController@buySubscription');

        //Notifications and Requests.

        //Qualifications
        $router->get('qualifications/{user_id}', 'QualificationsController@getQualifications');
        $router->post('qualifications/{user_id}', 'QualificationsController@saveQualification');
        $router->post('qualifications/delete/{id}', 'QualificationsController@deleteQualification');

        //Ratings
        $router->post('ratings/actions/new', 'RatingsController@saveReview');
        $router->post('ratings/actions/get/{trainer_id}', 'RatingsController@getReviews');

        //Calls.
        $router->post('calls/actions/new', 'CallHistoryController@newCall');
        $router->get('calls/actions/all', 'CallHistoryController@getCallHistory');

        $router->get('prowidgets', 'UserCommonController@proWidgets');


    });

    //Gateway.
    $router->group(['middleware' => 'gateway:api'], function () use ($router) {

        //Fitness Service.
        $router->group(['prefix' => 'fitness'], function () use ($router) {
            $router->get('/{all:.*}', 'FitnessController@callServiceGET');
            $router->post('/{all:.*}', 'FitnessController@callServicePOST');
        });

        //Meeting Service.
        $router->group(['prefix' => 'meeting'], function () use ($router) {
            $router->get('/{all:.*}', 'MeetingController@callServiceGET');
            $router->post('/{all:.*}', 'MeetingController@callServicePOST');
        });

        //Ecom Service.
        $router->group(['prefix' => 'ecom'], function () use ($router) {
            $router->get('/{all:.*}', 'EcomController@callServiceGET');
            $router->post('/{all:.*}', 'EcomController@callServicePOST');
        });

    });

});
