<?php

/**
 * Google Trends PHP SDK - Laravel Integration Example
 * This example demonstrates how to integrate the Google Trends PHP SDK
 * with a Laravel application. This is a sample controller that shows
 * how to use the SDK with Laravel's dependency injection and facades.
 */

namespace App\Http\Controllers;

use Gtrends\Sdk\Contracts\ClientInterface;
use Gtrends\Sdk\Laravel\Facades\Gtrends;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

class TrendsController extends Controller
{
    /**
     * The trends client instance.
     *
     * @var ClientInterface
     */
    protected $trendsClient;

    /**
     * Create a new controller instance.
     *
     * @param ClientInterface $trendsClient
     * @return void
     */
    public function __construct(ClientInterface $trendsClient)
    {
        $this->trendsClient = $trendsClient;
    }

    /**
     * Display trending searches.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $geo = $request->input('geo', config('gtrends.defaults.geo', 'US'));
        $category = $request->input('category');

        // Build options array
        $options = [];
        if ($category) {
            $options['category'] = $category;
        }

        // Using dependency injection
        $trending = $this->trendsClient->getTrending($geo, $options);

        return view('trends.index', [
            'trending' => $trending,
            'geo' => $geo,
            'category' => $category
        ]);
    }

    /**
     * Display related topics for a keyword.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function related(Request $request)
    {
        $keyword = $request->input('keyword');

        // Validate input
        $request->validate([
            'keyword' => 'required|string|max:100',
            'geo' => 'nullable|string|size:2',
            'timeframe' => 'nullable|string|in:past-24h,past-7d,past-30d,past-90d,past-12m,past-5y'
        ]);

        // Using the facade with caching
        $cacheKey = 'trends.related.' . md5($keyword . $request->input('geo') . $request->input('timeframe'));

        $relatedTopics = Cache::remember($cacheKey, 3600, function () use ($request, $keyword) {
            return Gtrends::getRelatedTopics($keyword, [
                'geo' => $request->input('geo', config('gtrends.defaults.geo', 'US')),
                'timeframe' => $request->input('timeframe', config('gtrends.defaults.timeframe', 'past-30d'))
            ]);
        });

        return view('trends.related', [
            'keyword' => $keyword,
            'relatedTopics' => $relatedTopics
        ]);
    }

    /**
     * Display comparison between keywords.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function compare(Request $request)
    {
        // Validate input
        $request->validate([
            'keywords' => 'required|string',
            'geo' => 'nullable|string|size:2',
            'timeframe' => 'nullable|string|in:past-24h,past-7d,past-30d,past-90d,past-12m,past-5y'
        ]);

        // Parse keywords
        $keywordsString = $request->input('keywords');
        $keywords = array_map('trim', explode(',', $keywordsString));

        // Validate number of keywords
        if (count($keywords) < 2 || count($keywords) > 5) {
            return back()->withErrors([
                'keywords' => 'Please provide between 2 and 5 keywords to compare.'
            ]);
        }

        try {
            // Using the facade with custom configuration
            $comparison = Gtrends::withConfig([
                'timeout' => 60 // Extend timeout for comparison requests
            ])->getComparison($keywords, [
                'geo' => $request->input('geo', config('gtrends.defaults.geo', 'US')),
                'timeframe' => $request->input('timeframe', config('gtrends.defaults.timeframe', 'past-30d'))
            ]);

            return view('trends.comparison', [
                'keywords' => $keywords,
                'comparison' => $comparison
            ]);

        } catch (\Gtrends\Exceptions\ValidationException $e) {
            return back()->withErrors([
                'keywords' => $e->getMessage()
            ]);
        } catch (\Gtrends\Exceptions\GtrendsException $e) {
            report($e); // Log the error

            return back()->withErrors([
                'error' => 'An error occurred while processing your request. Please try again later.'
            ]);
        }
    }

    /**
     * Display geo interest for a keyword.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function geoInterest(Request $request)
    {
        // Validate input
        $request->validate([
            'keyword' => 'required|string|max:100',
            'resolution' => 'nullable|string|in:country,region,city',
            'timeframe' => 'nullable|string|in:past-24h,past-7d,past-30d,past-90d,past-12m,past-5y'
        ]);

        $keyword = $request->input('keyword');
        $resolution = $request->input('resolution', 'country');

        // Using the facade with exception handling
        try {
            $geoInterest = Gtrends::getGeo($keyword, [
                'resolution' => $resolution,
                'timeframe' => $request->input('timeframe', config('gtrends.defaults.timeframe', 'past-30d'))
            ]);

            return view('trends.geo', [
                'keyword' => $keyword,
                'resolution' => $resolution,
                'geoInterest' => $geoInterest
            ]);

        } catch (\Gtrends\Exceptions\ApiException $e) {
            // Log the API error with context
            logger()->error('Google Trends API error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'keyword' => $keyword
            ]);

            return back()->withErrors([
                'api' => 'An error occurred with the Google Trends API: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Check API health status.
     *
     * @return \Illuminate\Http\Response
     */
    public function healthCheck()
    {
        try {
            // Using the facade
            $health = Gtrends::getHealth();

            return response()->json([
                'status' => 'success',
                'message' => 'API is operational',
                'data' => $health
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'API health check failed: ' . $e->getMessage()
            ], 500);
        }
    }
}

/**
 * Example Blade View Template (trends/index.blade.php)
 * @verbatim
 * <div class="container">
 *     <h1>Trending Searches - {{ $geo }}</h1>
 *
 *     @if(!empty($category))
 *         <h2>Category: {{ $category }}</h2>
 *     @endif
 *
 *     <div class="trending-list">
 *         @foreach($trending as $index => $trend)
 *             <div class="trending-item">
 *                 <h3>{{ $index + 1 }}. {{ $trend['title'] }}</h3>
 *                 <p>Search volume: {{ $trend['search_volume'] }}</p>
 *
 *                 @if(!empty($trend['articles']))
 *                     <div class="related-articles">
 *                         <h4>Related Articles:</h4>
 *                         <ul>
 *                             @foreach($trend['articles'] as $article)
 *                                 <li>
 *                                     <a href="{{ $article['url'] }}" target="_blank">
 *                                         {{ $article['title'] }} ({{ $article['source'] }})
 *                                     </a>
 *                                 </li>
 *                             @endforeach
 *                         </ul>
 *                     </div>
 *                 @endif
 *             </div>
 *         @endforeach
 *     </div>
 *
 *     <div class="form-container">
 *         <h2>Change Region</h2>
 *         <form action="{{ route('trends.index') }}" method="get">
 *             <div class="form-group">
 *                 <label for="geo">Geographic Region:</label>
 *                 <select name="geo" id="geo">
 *                     <option value="US" {{ $geo == 'US' ? 'selected' : '' }}>United States</option>
 *                     <option value="GB" {{ $geo == 'GB' ? 'selected' : '' }}>United Kingdom</option>
 *                     <option value="CA" {{ $geo == 'CA' ? 'selected' : '' }}>Canada</option>
 *                     <option value="AU" {{ $geo == 'AU' ? 'selected' : '' }}>Australia</option>
 *                 </select>
 *             </div>
 *
 *             <div class="form-group">
 *                 <label for="category">Category (optional):</label>
 *                 <input type="text" name="category" id="category" value="{{ $category }}">
 *             </div>
 *
 *             <button type="submit" class="btn btn-primary">Update</button>
 *         </form>
 *     </div>
 * </div>
 * @endverbatim
 */
