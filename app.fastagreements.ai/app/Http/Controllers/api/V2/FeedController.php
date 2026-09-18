<?php

namespace App\Http\Controllers\api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Aggriment;
use App\Models\Feed;
use App\Models\User;
use App\Models\Customer;
use Exception;
use Illuminate\Support\Facades\Validator;

class FeedController extends Controller
{
    /**
     * Display a listing of the feeds.
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // default 10
            $page = $request->input('page', 1);

            // In V2, we strictly use the ID from the authenticated JWT token.
            $user_id = $request->user()->id;

            $feeds = Feed::with([
                'customer:id,name', 
                'customer2:id,name', 
                'user:id,name', 
                'user2:id,name', 
                'agreement:id,party_1_user_type,party_2_user_type',
                'category:id,category_name,category_image,icon_text'
            ])
                ->withCount(['comments', 'likes'])
                ->withExists([
                    'likes as is_liked' => function ($query) use ($user_id) {
                        $query->where('customer_id', $user_id);
                    },
                    'reports as is_reported' => function ($query) use ($user_id) {
                        $query->where('customer_id', $user_id);
                    }
                ])
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);
            
            // Format the items to keep the V1 API response structure identical
            $items = collect($feeds->items())->map(function ($feed) {
                $party1Type = strtolower($feed->agreement->party_1_user_type ?? 'customer');
                $party2Type = strtolower($feed->agreement->party_2_user_type ?? 'customer');

                // Overwrite the 'customer' object with 'user' if the creator was a user
                if ($party1Type === 'user' && $feed->user) {
                    $feed->setRelation('customer', $feed->user);
                }
                
                // Overwrite the 'customer2' object with 'user2' if party 2 was a user
                if ($party2Type === 'user' && $feed->user2) {
                    $feed->setRelation('customer2', $feed->user2);
                }

                // Remove the extra relations so the response matches V1 perfectly
                $feed->unsetRelation('user');
                $feed->unsetRelation('user2');
                $feed->unsetRelation('agreement');

                return $feed;
            });

            return response()->json([
                'status' => true,
                'message' => 'Feeds retrieved successfully.',
                'data' => $items,
                'total_pages' => $feeds->lastPage(),
                'total_records' => $feeds->total(),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving feeds.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Publish an existing agreement to the feed.
     */
    public function publish(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'agreement_id' => 'required|integer',
                'customer_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed.',
                    'data' => $validator->errors()
                ], 422);
            }

            // Using customer_id from request to maintain existing API format
            $userId = $request->input('customer_id');

            $agreement = Aggriment::find($request->input('agreement_id'));

            if (!$agreement) {
                return response()->json([
                    'status' => false,
                    'message' => 'Agreement not found.',
                    'data' => null
                ], 404);
            }

            if ((int) $agreement->party_1_id !== (int) $userId) {
                return response()->json([
                    'status' => false,
                    'message' => 'You can only share an agreement you created.',
                    'data' => null
                ], 403);
            }

            // Find if the user/customer actually exists in the respective table
            $party1UserType = strtolower($agreement->party_1_user_type ?? 'customer'); // default to customer for old records
            if ($party1UserType === 'user') {
                $userExists = User::where('id', $userId)->exists();
            } else {
                $userExists = Customer::where('id', $userId)->exists();
            }

            if (!$userExists) {
                return response()->json([
                    'status' => false,
                    'message' => 'User or Customer not found in the respective table.',
                    'data' => null
                ], 404);
            }

            // Idempotent: a retry after a dropped response must not post twice.
            $feed = Feed::firstOrCreate(
                ['agreement_id' => $agreement->id],
                [
                    'type' => 'agreement_created',
                    'customer_id' => $agreement->party_1_id,
                    'customer_id2' => $agreement->party_2_id,
                    'category_id' => $agreement->category_id,
                ]
            );

            return response()->json([
                'status' => true,
                'message' => 'Agreement shared to feed successfully.',
                'data' => $feed
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while publishing to feed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
