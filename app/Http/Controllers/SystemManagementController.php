<?php

namespace App\Http\Controllers;

use App\Utility;
use App\ReturnMessage;
use App\Models\TechStack;
use Illuminate\Http\Request;
use App\Models\Responsibility;
use App\Models\ProficiencyLevel;
use App\Http\Resources\Employee\TechStackResource;
use App\Http\Resources\Employee\ResponsibilityResource;
use App\Http\Resources\Employee\ProficiencyLevelResource;
use App\Http\Resources\Grade\GradeResource;
use App\Http\Resources\Japanese\JapaneseLevelResource;
use App\Http\Resources\Position\PositionResource;
use App\Models\Grade;
use App\Models\JapaneseLevel;
use App\Models\Position;

class SystemManagementController extends Controller
{
    public function getTechStack()
    {
        try {
            $query  = TechStack::query();
            $data = $query->get();
            $techStack = TechStackResource::collection($data);
            return response()->json($techStack);
        } catch (\Throwable  $e) {
            Utility::log("SystemManagementController::getTechStack", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }
    public function getResponsibility()
    {
        try {
            $query  = Responsibility::query();
            $data = $query->get();
            $responsibility = ResponsibilityResource::collection($data);
            return response()->json($responsibility);
        } catch (\Throwable  $e) {
            Utility::log("SystemManagementController::getResponsibility", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }
    public function getProficiencyLevel()
    {
        try {
            $query  = ProficiencyLevel::query();
            $data = $query->get();
            $proficiencyLevel = ProficiencyLevelResource::collection($data);
            return response()->json($proficiencyLevel);
        } catch (\Throwable  $e) {
            Utility::log("SystemManagementController::getProficiencyLevel", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }
    public function getGrade()
    {
        try {
            $query  = Grade::query();
            $data = $query->get();
            $responsibility = GradeResource::collection($data);
            return response()->json($responsibility);
        } catch (\Throwable  $e) {
            Utility::log("SystemManagementController::getResponsibility", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }
    public function getJapaneseLevel()
    {
        try {
            $query  = JapaneseLevel::query();
            $data = $query->get();
            $responsibility = JapaneseLevelResource::collection($data);
            return response()->json($responsibility);
        } catch (\Throwable  $e) {
            Utility::log("SystemManagementController::getResponsibility", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }
    public function getPosition()
    {
        try {
            $query  = Position::query();
            $data = $query->get();
            $proficiencyLevel = PositionResource::collection($data);
            return response()->json($proficiencyLevel);
        } catch (\Throwable  $e) {
            Utility::log("SystemManagementController::getProficiencyLevel", $e->getMessage());
            return response()->json([], ReturnMessage::INTERNAL_SERVER_ERROR);
        }
    }
}
