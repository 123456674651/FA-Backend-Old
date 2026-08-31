<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeedComment;
use App\Models\FeedCommentLike;
use App\Models\FeedCommentReport;
use App\Models\FeedLike;
use App\Models\FeedReport;
use Illuminate\Http\Request;
use App\Models\Aggriment;
use App\Models\Feed;
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
            // The feed is public to every signed-in customer. customer_id is
            // only used to flag which rows this reader has liked or reported.
            $customer_id = $request->input('customer_id', null);

            $feeds = Feed::with(['customer:id,name', 'customer2:id,name','category:id,category_name,category_image,icon_text'])
                ->withCount(['comments', 'likes'])
                ->withExists([
                    'likes as is_liked' => function ($query) use ($customer_id) {
                        $query->where('customer_id', $customer_id);
                    },
                    'reports as is_reported' => function ($query) use ($customer_id) {
                        $query->where('customer_id', $customer_id);
                    }
                ])
                ->latest()
                ->paginate($perPage, ['*'], 'page', $page);
            // An empty feed is a valid state, not an error — the client renders
            // its own "nothing here yet" instead of an error toast.
            return response()->json([
                'status' => true,
                'message' => 'Feeds retrieved successfully.',
                'data' => $feeds->items(),          // Only records
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
     * store a new feed
     */
    public function store(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'type' => 'required',
                'customer_id' => 'required',
            ]);

            // If validation fails, redirect back with errors
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed.',
                    'data' => $validator->errors()
                ], 422);
            }
            $feed = Feed::create([
                'type'=> $request->input('type'),
                'customer_id'=> $request->input('customer_id'),
                'customer_id2'=> $request->input('customer_id2', null),
                'agreement_id'=> $request->input('agreement_id', null),
                'category_id'=> $request->input('category_id', null),
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Feed created successfully.',
                'data' => $feed
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating feed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Publish an existing agreement to the feed.
     *
     * Creating an agreement no longer posts one by itself — the app asks the
     * creator after payment and calls this only if they say yes. The body
     * carries just the agreement id: everything shown in the feed is read off
     * the agreement here, so the client cannot post under another customer's
     * name or against a category the agreement does not have.
     */
    public function publish(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'agreement_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed.',
                    'data' => $validator->errors()
                ], 422);
            }

            $agreement = Aggriment::find($request->input('agreement_id'));
            if (!$agreement) {
                return response()->json([
                    'status' => false,
                    'message' => 'Agreement not found.',
                    'data' => null
                ], 404);
            }

            // Identity comes from the signed token, never the request body.
            if ((int) $agreement->party_1_id !== (int) $request->user()->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'You can only share an agreement you created.',
                    'data' => null
                ], 403);
            }

            // Idempotent: a retry after a dropped response must not post twice.
            $feed = Feed::firstOrCreate(
                ['agreement_id' => $agreement->id],
                [
                    'type'         => 'agreement_created',
                    'customer_id'  => $agreement->party_1_id,
                    'customer_id2' => $agreement->party_2_id,
                    'category_id'  => $agreement->category_id,
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
                'message' => 'An error occurred while sharing the agreement to feed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * update a feed
     */
    public function update(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required',
                'customer_id' => 'required',
            ]);

            // If validation fails, redirect back with errors
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            $feed = Feed::find($request->id);
            if (!$feed) {
                return response()->json([
                    'status' => false,
                    'message' => 'Feed not found.',
                    'data' => null
                ], 404);
            }
            $feed->update([
                'type'=> $request->input('type'),
                'customer_id'=> $request->input('customer_id'),
                'customer_id2'=> $request->input('customer_id2', null),
                'agreement_id'=> $request->input('agreement_id', null),
                'category_id'=> $request->input('category_id', null),
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Feed updated successfully.',
                'data' => $feed
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating feed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * delete a feed
     */
    public function destroy($id)
    {
        try {
            $feed = Feed::find($id);
            if (!$feed) {
                return response()->json([
                    'status' => false,
                    'message' => 'Feed not found.',
                    'data' => null
                ], 404);
            }
            $feed->delete();
            return response()->json([
                'status' => true,
                'message' => 'Feed deleted successfully.',
                'data' => $feed
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting feed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle like a feed
     */
    public function toggle_like(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'feed_id' => 'required',
                'customer_id' => 'required',
            ]);

            // If validation fails, redirect back with errors
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed.',
                    'data' => $validator->errors()
                ], 422);
            }
            $feed = Feed::find($request->feed_id);
            if (!$feed) {
                return response()->json([
                    'status' => false,
                    'message' => 'Feed not found.',
                    'data' => null
                ], 404);
            }

            $feedLike = $feed->likes()->where('customer_id', $request->customer_id)->first();
            if ($feedLike) {
                $feedLike->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Feed unliked successfully.',
                    'data' => null
                ], 200);
            } else {
                $feed->likes()->create($request->all());
                $feedLike = $feed->likes()->where('customer_id', $request->customer_id)->first();
                return response()->json([
                    'status' => true,
                    'message' => 'Feed liked successfully.',
                    'data' => $feedLike
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while liking feed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * fetch feed likes customers
     */
    public function getFeedLikeCustomers(Request $request, $id)
    {
        try {
            $perPage = $request->input('per_page', 10); // default 10
            $page = $request->input('page', 1);
            $feedLikes = FeedLike::where('feed_id', $id)->with('customer:id,name,email')->paginate($perPage, ['*'], 'page', $page);
            if (!$feedLikes) {
                return response()->json([
                    'status' => false,
                    'message' => 'Feed likes not found.',
                    'data' => null
                ], 404);
            }
            return response()->json([
                'status' => true,
                'message' => 'Feed like customers fetched successfully.',
                'data' => $feedLikes->items(),
                'total_pages' => $feedLikes->lastPage(),
                'total_records' => $feedLikes->total(),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching feed like customers.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * comment added for a feed
     */
    public function addComment(Request $request)
    {
        try {
            $feed = Feed::find($request->feed_id);
            
            $comment = $feed->comments()->create($request->all());
            $comment->load('customer:id,name,email,person_image');
            return response()->json([
                'status' => true,
                'message' => 'Feed commented successfully.',
                'data' => $comment
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while commenting feed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * comment delete for a feed
     */
    public function deleteComment($id)
    {
        try {
            $feedComment = FeedComment::find($id);
            if (!$feedComment) {
                return response()->json([
                    'status' => false,
                    'message' => 'Feed comment not found.',
                    'data' => null
                ], 404);
            }
            $feedComment->delete();
            return response()->json([
                'status' => true,
                'message' => 'Feed comment deleted successfully.',
                'data' => $feedComment
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting feed comment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * fetch feed comments
     */
    public function getFeedComments(Request $request, $id)
    {
        try {
            $perPage = $request->input('per_page', 10); // default 10
            $page = $request->input('page', 1);
            $sort = $request->input('sort', 'desc');
            $customer_id = $request->customer_id;
            
            $feedComments = FeedComment::where('feed_id', $id)
                ->with('customer:id,name,email,person_image')
                ->withCount('likes')
                ->withExists([
                    'likes as is_liked' => function ($query) use ($customer_id) {
                        $query->where('customer_id', $customer_id);
                    },
                    'reports as is_reported' => function ($query) use ($customer_id) {
                        $query->where('customer_id', $customer_id);
                    }
                ])
                ->orderBy('created_at', $sort)
                ->paginate($perPage, ['*'], 'page', $page);
            if (!$feedComments) {
                return response()->json([
                    'status' => false,
                    'message' => 'Feed comments not found.',
                    'data' => null
                ], 404);
            }
            return response()->json([
                'status' => true,
                'message' => 'Feed comments fetched successfully.',
                'data' => $feedComments->items(),
                'total_pages' => $feedComments->lastPage(),
                'total_records' => $feedComments->total(),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching feed comments.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle like a feed comment
     */
    public function toggle_comment_like(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'feed_comment_id' => 'required',
                'customer_id' => 'required',
            ]);

            // If validation fails, redirect back with errors
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed.',
                    'data' => $validator->errors()
                ], 422);
            }
            $feedComment = FeedComment::find($request->feed_comment_id);
            if (!$feedComment) {
                return response()->json([
                    'status' => false,
                    'message' => 'Feed comment not found.',
                    'data' => null
                ], 404);
            }

            $feedCommentLike = $feedComment->likes()->where('customer_id', $request->customer_id)->first();
            if ($feedCommentLike) {
                $feedCommentLike->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Feed comment unliked successfully.',
                    'data' => null
                ], 200);
            } else {
                $feedComment->likes()->create($request->all());
                $feedCommentLike = $feedComment->likes()->where('customer_id', $request->customer_id)->first();
                return response()->json([
                    'status' => true,
                    'message' => 'Feed comment liked successfully.',
                    'data' => $feedCommentLike
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while liking feed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * fetch feed likes customers
     */
    public function getFeedCommentLikeCustomers(Request $request, $id)
    {
        try {
            $perPage = $request->input('per_page', 10); // default 10
            $page = $request->input('page', 1);
            $feedCommentLikes = FeedCommentLike::where('feed_comment_id', $id)
                ->with('customer:id,name,email')
                ->paginate($perPage, ['*'], 'page', $page);
            if (!$feedCommentLikes) {
                return response()->json([
                    'status' => false,
                    'message' => 'Comment likes customers not found.',
                    'data' => null
                ], 404);
            }
            return response()->json([
                'status' => true,
                'message' => 'Comment likes customers fetched successfully.',
                'data' => $feedCommentLikes->items(),
                'total_pages' => $feedCommentLikes->lastPage(),
                'total_records' => $feedCommentLikes->total(),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while fetching comment likes customers.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * comment report added for a feed comment
     */
    public function addCommentReport(Request $request)
    {
        try {
             $validator = Validator::make($request->all(), [
                'feed_comment_id' => 'required',
                'customer_id' => 'required',
                'reason' => 'required',
            ]);

            // If validation fails, redirect back with errors
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed.',
                    'data' => $validator->errors()
                ], 422);
            }
            $feedComment = FeedComment::find($request->feed_comment_id);
            if (!$feedComment) {
                return response()->json([
                    'status' => false,
                    'message' => 'Comment not found.',
                    'data' => null
                ], 404);
            }
            $feedComment->reports()->create([
                'feed_comment_id'=> $request->feed_comment_id,
                'customer_id'=> $request->customer_id,
                'reason'=> $request->reason,
                'status'=> 'pending',
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Comment reported successfully.',
                'data' => $feedComment
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while reporting comment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * comment delete for a feed
     */
    public function deleteFeedCommentReport($id)
    {
        try {
            $feedCommentReport = FeedCommentReport::find($id);
            if (!$feedCommentReport) {
                return response()->json([
                    'status' => false,
                    'message' => 'Comment report not found.',
                    'data' => null
                ], 404);
            }
            $feedCommentReport->delete();
            return response()->json([
                'status' => true,
                'message' => 'Comment report deleted successfully.',
                'data' => $feedCommentReport
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting comment report.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * update feed comment report status
     */
    public function updateCommentReportStatus(Request $request)
    {
        try {
             $validator = Validator::make($request->all(), [
                'id' => 'required',
                'status' => 'required',
            ]);

            // If validation fails, redirect back with errors
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed.',
                    'data' => $validator->errors()
                ], 422);
            }
            $feedCommentReport = FeedCommentReport::find($request->id);
            if (!$feedCommentReport) {
                return response()->json([
                    'status' => false,
                    'message' => 'Comment report not found.',
                    'data' => null
                ], 404);
            }
            $feedCommentReport->update([
                'status'=> $request->status,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Comment report status updated successfully.',
                'data' => $feedCommentReport
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating comment report status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * add feed report
     */
    public function addFeedReport(Request $request)
    {
        try {
             $validator = Validator::make($request->all(), [
                'feed_id' => 'required',
                'customer_id' => 'required',
                'reason' => 'required',
            ]);

            // If validation fails, redirect back with errors
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed.',
                    'data' => $validator->errors()
                ], 422);
            }
            $feed = Feed::find($request->feed_id);
            if (!$feed) {
                return response()->json([
                    'status' => false,
                    'message' => 'Feed not found.',
                    'data' => null
                ], 404);
            }
            $feed->reports()->create([
                'feed_id'=> $request->feed_id,
                'customer_id'=> $request->customer_id,
                'reason'=> $request->reason,
                'status'=> 'pending',
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Feed reported successfully.',
                'data' => $feed
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while reporting feed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * delete feed report
     */
    public function deleteFeedReport($id)
    {
        try {
            $feedReport = FeedReport::find($id);
            if (!$feedReport) {
                return response()->json([
                    'status' => false,
                    'message' => 'Feed report not found.',
                    'data' => null
                ], 404);
            }
            $feedReport->delete();
            return response()->json([
                'status' => true,
                'message' => 'Feed report deleted successfully.',
                'data' => $feedReport
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting feed report.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * update feed report status
     */
    public function updateFeedReportStatus(Request $request)
    {
        try {
             $validator = Validator::make($request->all(), [
                'id' => 'required',
                'status' => 'required',
            ]);

            // If validation fails, redirect back with errors
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed.',
                    'data' => $validator->errors()
                ], 422);
            }
            $feedReport = FeedReport::find($request->id);
            if (!$feedReport) {
                return response()->json([
                    'status' => false,
                    'message' => 'Feed report not found.',
                    'data' => null
                ], 404);
            }
            $feedReport->update([
                'status'=> $request->status,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Feed report status updated successfully.',
                'data' => $feedReport
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating feed report status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
