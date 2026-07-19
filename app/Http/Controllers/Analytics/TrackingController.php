<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Http\Requests\TrackFeedbackRequest;
use App\Http\Requests\TrackPageViewRequest;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Internal analytics tracking controller.
 *
 * Routes are protected by 'web' middleware (CSRF + throttle).
 * Never exposed as a public API.
 */
class TrackingController extends Controller
{
    public function __construct(private readonly AnalyticsService $analytics) {}

    /**
     * Track a page view.
     * POST /internal-analytics/track
     */
    public function track(TrackPageViewRequest $request): JsonResponse
    {
        $tracked = $this->analytics->trackView(array_merge(
            $request->validated(),
            [
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent() ?? '',
            ]
        ));

        return response()->json(['tracked' => $tracked]);
    }

    /**
     * Heartbeat ping to update reading time.
     * POST /internal-analytics/ping
     */
    public function ping(Request $request): JsonResponse
    {
        $request->validate([
            'visitor_id' => ['required', 'string', 'max:64'],
            'path'       => ['required', 'string', 'max:500'],
            'seconds'    => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        $this->analytics->pingReadTime(
            $request->input('visitor_id'),
            $request->input('path'),
            $request->input('seconds')
        );

        return response()->json(['ok' => true]);
    }

    /**
     * Submit a like or helpful vote.
     * POST /internal-analytics/feedback
     */
    public function feedback(TrackFeedbackRequest $request): JsonResponse
    {
        $result = $this->analytics->trackFeedback(array_merge(
            $request->validated(),
            ['ip' => $request->ip()]
        ));

        return response()->json($result);
    }

    /**
     * Log a search query.
     * POST /internal-analytics/search
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'visitor_id'    => ['required', 'string', 'max:64'],
            'query'         => ['required', 'string', 'max:300'],
            'search_type'   => ['nullable', 'string', 'in:global,article,roadmap'],
            'results_count' => ['nullable', 'integer'],
            'page'          => ['nullable', 'integer'],
            'duration_ms'   => ['nullable', 'integer'],
        ]);

        $this->analytics->trackSearch(array_merge(
            $request->only(['visitor_id', 'query', 'search_type', 'results_count', 'page', 'duration_ms']),
            ['ip' => $request->ip()]
        ));

        return response()->json(['ok' => true]);
    }
}
