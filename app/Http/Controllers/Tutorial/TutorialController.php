<?php

namespace App\Http\Controllers\Tutorial;

use App\Models\Tutorial;
use App\ReturnMessage;
use App\Utility;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class TutorialController extends Controller
 {
   public function get(Request $request)
    {

        try {
            $tutorials = Tutorial::all();

            return response()->json([
                'status' => 'success',
                'data' => $tutorials
            ], 200);

        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    public function create(Request $request)
     {
        
        
          $filePath = null;
          if ($request->hasFile('tutorial_file')) {
              $filePath = $request->file('tutorial_file')->store('tutorials', 'public');
          } elseif ($request->input('tutorial_file')) {
              $filePath = $request->input('tutorial_file');
          }

          DB::table('tutorials')->insert([
              'id'            => $request->input('id'),             
              'name'          => $request->input('name'),
              'password'      => Hash::make($request->input('password')),        
            'phnumber'      => $request->input('phnumber'),
              'address'       => $request->input('address'),
              'tutorial_file' => $filePath,
              'position'      => $request->input('position'),
          ]);

         return response()->json([
             'status'  => 200,
             'message' => 'Data inserted successfully!'
         ]);
     }
      public function update(Request $request)
     {
    DB::beginTransaction();
    try {
        $id = $request->input('id');
        $tutorial = Tutorial::find($id);

        if (!$tutorial) {
            return response()->json([
                'status'  => 404,
                'message' => 'Record not found for ID: ' . $id
            ], 404);
        }
  
        $filePath = $tutorial->tutorial_file;
        if ($request->hasFile('tutorial_file')) {
            if ($tutorial->tutorial_file && \Illuminate\Support\Facades\Storage::disk('public')->exists($tutorial->tutorial_file)) {
        \Illuminate\Support\Facades\Storage::disk('public')->delete($tutorial->tutorial_file);
    }
           $filepath = time() . '_' . $request->file('tutorial_file')->getClientOriginalName();
$request->file('tutorial_file')->storeAs('public', $filepath);
        } elseif ($request->input('tutorial_file')) {
            $filePath = $request->input('tutorial_file');
        }


        $positionInput = $request->input('position');
        if (is_array($positionInput)) {
            $positionInput = $positionInput['name'] ?? $positionInput['value'] ?? null;
        }

        $updateData = [
            'name'          => $request->input('name'),
            'phnumber'      => $request->input('phnumber'),
            'address'       => $request->input('address'),
            'tutorial_file' => $filePath,
            'position'      => $positionInput,
        ];

    
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->input('password'));
        }

        $tutorial->update($updateData);

        DB::commit();

        return response()->json([
            'status'   => 200,
            'message'  => 'Data updated successfully!',
            'tutorial' => $tutorial
        ], 200);

    } catch (\Throwable $e) {
        DB::rollBack();

        return response()->json([
            'status'  => 500,
            'message' => $e->getMessage()
        ], 500);
    }
}


     public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $id = $request->input('id');
            $tutorial = Tutorial::find($id);

            if (!$tutorial) {
                return response()->json([
                    'status' => ReturnMessage::NOT_FOUND ?? 404,
                    'message' => 'Record not found'
                ], 404);
            }

           
            $tutorial->delete();

            DB::commit();

            return response()->json([
                'status'  => ReturnMessage::OK ?? 200,
                'message' => 'Data deleted successfully!'
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Utility::log("TutorialController::delete", $e->getMessage());

            return response()->json([
                'status'  => ReturnMessage::INTERNAL_SERVER_ERROR ?? 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

 }
