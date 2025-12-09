<?php


   namespace App\Http\Controllers\APIs;

   use Illuminate\Http\JsonResponse;
   use App\Http\Controllers\Controller as Controller;
   class BaseController extends Controller
   {
      /**
       * success response method.
       *
       * @param $result
       * @param $message
       * @return JsonResponse
       */
      public function sendResponse($result, $message): JsonResponse
      {
         $response = [
             'data' => $result,
             'message' => $message,
         ];

         return response()->json($response, 200);
      }


      /**
       * return error response.
       *
       * @param $error
       * @param $errorMessages
       * @param int $code
       * @return JsonResponse
       */
  public function sendError($error, $errorMessages = [], $code): JsonResponse
      {
         $response = [
             'data' => [],
             'message' => $error,
         ];

         if (!empty($errorMessages))
            $response['errors'] = $errorMessages;

         return response()->json($response, $code ?? 200);
      }


      /**
       * return error response.
       *
       * @param $error
       * @param $errorMessage
       * @param $code
       * @return JsonResponse
       */
      public function sendDefaultError($errorIndex, $errorMessage, $code): JsonResponse
      {
         $response = [
             'message' => 'The given data was invalid.',
             'errors' => [
                 $errorIndex => [
                     $errorMessage
                 ]
             ],
         ];

         return response()->json($response, $code ?? 200);
      }
   }
