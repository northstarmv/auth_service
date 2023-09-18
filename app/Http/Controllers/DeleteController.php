<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Helpers\ValidationErrrorHandler;

class DeleteController extends Controller
{
    public function delete(Request $request):JsonResponse
    {   
        
        try{
            $validatedData = $this->validate($request, [
                'tableId' => 'required|integer|min:1|max:100',
                'resultId' => 'required|integer|min:1|max:2147483647'
            ]);
            try{

                $controll = json_decode(file_get_contents(storage_path('Json/tableControll.json')), true);

                 // if table id not in controll json
                if (!array_key_exists( $validatedData["tableId"], $controll)) {
                    return response()->json(
                        [
                            ResponseHelper::error("0001")
                        ], 200
                        );
                }

                 // if not status access to table
                if (!array_key_exists('deleteAccess', $controll[$validatedData["tableId"]])) {
                    return response()->json(
                        [
                            ResponseHelper::error("0403")
                        ], 200
                        );
                }

                 // if not status access to user role
                if (!array_key_exists($request->auth->role, $controll[$validatedData["tableId"]]['deleteAccess'])) {
                    
                    return response()->json(
                        [
                            ResponseHelper::error("0403")
                        ], 200
                        );
                }

                 /**
                 * @detail
                 * if isset another function
                 */
                $result = DB::delete("DELETE FROM `{$controll[$validatedData["tableId"]]['table']}` WHERE id=?", [ $validatedData["resultId"]]);

                if (!$result) {

                        if (DB::getPdo()->errorCode() === '23000') {
                            $result = DB::update("UPDATE `{$controll[$validatedData["tableId"]]['table']}` SET status = 403 WHERE id=?", [$validatedData["resultId"]]);
                            if ($result === false) {
                                return response()->json(
                                    [
                                        ResponseHelper::error("0500")
                                    ], 200
                                );
                            }

                            return response()->json([
                                ResponseHelper::success("200","","success")
                            ], 200);
                        }

                        return response()->json(
                            [
                                ResponseHelper::error("0500")
                            ], 200
                            );
                }

                return response()->json([
                    ResponseHelper::success("200","","success")
                ], 200);

            }catch  (\Exception $e) {
                error_log($e);
                return response()->json(
                    [
                        ResponseHelper::error("0500")
                    ], 200
                    );

            }
        }catch  (ValidationException  $e) {
            return response()->json(
                [
                    ResponseHelper::error( "0000", ValidationErrrorHandler::handle($e->validator->errors()) )
                ], 200
                );
        }
    }
}
