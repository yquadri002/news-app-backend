<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\UpdatePreferencesRequest;
use App\Http\Resources\UserPreferenceResource;
use App\Services\UserPreferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PreferenceController extends Controller
{
    public function __construct(
        private readonly UserPreferenceService $userPreferenceService,
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $preferences = $this->userPreferenceService->getPreferences($request->user());

        return response()->json(['data' => new UserPreferenceResource($preferences)]);
    }

    public function update(UpdatePreferencesRequest $request): JsonResponse
    {
        $preferences = $this->userPreferenceService->updateAll(
            $request->user(),
            $request->validated(),
        );

        return response()->json([
            'message' => 'Preferences updated.',
            'data' => new UserPreferenceResource($preferences),
        ]);
    }

    public function updateInterests(Request $request): JsonResponse
    {
        $request->validate(['interests' => ['required', 'array']]);
        $preferences = $this->userPreferenceService->updateInterests(
            $request->user(),
            $request->interests,
        );

        return response()->json(['data' => new UserPreferenceResource($preferences)]);
    }

    public function updateCategories(Request $request): JsonResponse
    {
        $request->validate(['category_ids' => ['required', 'array']]);
        $preferences = $this->userPreferenceService->updateCategories(
            $request->user(),
            $request->category_ids,
        );

        return response()->json(['data' => new UserPreferenceResource($preferences)]);
    }

    public function updateSources(Request $request): JsonResponse
    {
        $request->validate(['source_ids' => ['required', 'array']]);
        $preferences = $this->userPreferenceService->updateSources(
            $request->user(),
            $request->source_ids,
        );

        return response()->json(['data' => new UserPreferenceResource($preferences)]);
    }

    public function updateLanguage(Request $request): JsonResponse
    {
        $request->validate(['language' => ['required', 'string', 'max:10']]);
        $preferences = $this->userPreferenceService->updateLanguage(
            $request->user(),
            $request->language,
        );

        return response()->json(['data' => new UserPreferenceResource($preferences)]);
    }

    public function updateLocation(Request $request): JsonResponse
    {
        $request->validate(['location' => ['required', 'string', 'max:255']]);
        $preferences = $this->userPreferenceService->updateLocation(
            $request->user(),
            $request->location,
        );

        return response()->json(['data' => new UserPreferenceResource($preferences)]);
    }
}
