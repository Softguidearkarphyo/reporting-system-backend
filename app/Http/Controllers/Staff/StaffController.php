<?php

namespace App\Http\Controllers\Staff;
use App\Models\Staff;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StaffCreateRequest;
use App\Http\Resources\Staff\StaffResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function create(StaffCreateRequest $request)
    {
       $data = $request->all();
       $createData = [
            "eng_name"       => $data['eng_name'],
            "jp_name"        => $data['jp_name'],
            "username"       => $data['username'],
            "password"       => Hash::make($data['password']),
            "address"        => $data['address'],
            "ph_number"      => $data['ph_number'],
            "position"       => $data['position'],
            "role"           => $data['role'],
            "email"          => $data['email'],
            "perment_date"   => $data['perment_date'],
            "ref_person"     => $data['ref_person'],
            "ref_ph_number"  => $data['ref_ph_number'],
            "project"        => $data['project'],
            "sort_key"       => $data['sort_key']

        ];
        $staff = new StaffResource(Staff::create($createData));
        return response()->json($staff);
    }
}
