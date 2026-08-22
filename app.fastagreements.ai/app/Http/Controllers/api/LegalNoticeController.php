<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\LegalNotice;
use App\Models\LegalNoticeNotification;
use App\Http\Requests\StoreLegalNoticeRequest;
use App\Http\Requests\UpdateLegalNoticeRequest;
use App\Http\Resources\LegalNoticeResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class LegalNoticeController extends Controller
{
    /**
     * Display a listing of the legal notices.
     */
    public function index(Request $request)
    {
        try {
            $query = LegalNotice::query()->orderBy('created_at', 'desc');

            // Search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('company_name', 'like', "%{$search}%")
                      ->orWhere('company_person_name', 'like', "%{$search}%");
                });
            }

            // Status Filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // User Filter
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            $notices = $query->paginate($request->input('per_page', 15));

            return response()->json([
                'status' => true,
                'message' => 'Legal Notices fetched successfully.',
                'data' => LegalNoticeResource::collection($notices)->response()->getData(true)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch Legal Notices.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created legal notice in storage.
     */
    public function store(StoreLegalNoticeRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data['user_id'] = $request->input('user_id');
            $data['status'] = 'Pending';

            if (!$data['user_id']) {
                throw new Exception("The user_id is required or user must be authenticated.");
            }

            $notice = LegalNotice::create($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Legal Notice created successfully.',
                'data' => new LegalNoticeResource($notice)
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to create Legal Notice.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified legal notice.
     */
   public function show($id)
{
    try {
        $notice = LegalNotice::where('user_id', $id)->get();

        return response()->json([
            'status' => true,
            'message' => 'Legal Notices fetched successfully.',
            'data' => LegalNoticeResource::collection($notice)
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Legal Notices not found.',
            'error' => $e->getMessage()
        ], 404);
    }
}

    /**
     * Update the specified legal notice in storage.
     */
    public function update(UpdateLegalNoticeRequest $request, $id)
    {
      
        DB::beginTransaction();
        try {
            $notice = LegalNotice::findOrFail($id);
            $data = $request->validated();
            
            $notice->update($data);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Legal Notice updated successfully.',
                'data' => new LegalNoticeResource($notice)
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update Legal Notice.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified legal notice from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $notice = LegalNotice::findOrFail($id);
            $notice->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Legal Notice deleted successfully.'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete Legal Notice.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change the status of the specified legal notice.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Pending,Approved,Rejected,In Progress,Closed',
        ]);

        DB::beginTransaction();
        try {
            $notice = LegalNotice::findOrFail($id);
            $oldStatus = $notice->status;
            $newStatus = $request->status;

            if ($oldStatus !== $newStatus) {
                $notice->status = $newStatus;
                $notice->save();

                // Create database notification for user
                if (in_array($newStatus, ['Approved', 'Rejected', 'In Progress', 'Closed'])) {
                    LegalNoticeNotification::create([
                        'user_id' => $notice->user_id,
                        'legal_notice_id' => $notice->id,
                        'title' => 'Legal Notice ' . $newStatus,
                        'message' => "Your legal notice for '{$notice->company_name}' has been updated to {$newStatus}.",
                        'is_read' => false,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Legal Notice status updated successfully.',
                'data' => new LegalNoticeResource($notice)
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
