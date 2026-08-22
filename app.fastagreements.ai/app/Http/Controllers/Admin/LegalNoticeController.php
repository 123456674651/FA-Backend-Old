<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalNotice;
use App\Models\LegalNoticeNotification;
use App\Http\Requests\StoreLegalNoticeRequest;
use App\Http\Requests\UpdateLegalNoticeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Yajra\DataTables\Facades\DataTables;
use App\Models\LegalNoticeReply;
use Illuminate\Support\Facades\Mail;
use Exception;

class LegalNoticeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = LegalNotice::query();

            // Filters
            if ($request->filled('status_filter')) {
                $query->where('status', $request->status_filter);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('total_amount', function ($row) {
                    return '₹' . number_format($row->total_amount, 2);
                })
                ->editColumn('amount_due', function ($row) {
                    return '₹' . number_format($row->amount_due, 2);
                })
                ->editColumn('status', function ($row) {
                    $statusOptions = ['Pending', 'Approved', 'Rejected', 'In Progress', 'Closed'];
                    $badgeClasses = [
                        'Pending' => 'bg-warning text-dark',
                        'Approved' => 'bg-success text-white',
                        'Rejected' => 'bg-danger text-white',
                        'In Progress' => 'bg-info text-dark',
                        'Closed' => 'bg-secondary text-white'
                    ];
                    $currentBadge = $badgeClasses[$row->status] ?? 'bg-light';
                    
                    $optionsHtml = '';
                    foreach ($statusOptions as $option) {
                        $selected = $row->status === $option ? 'selected' : '';
                        $optionsHtml .= "<option value='{$option}' {$selected}>{$option}</option>";
                    }
                    
                    return '
                    <div class="text-center">
                        <select class="form-select form-select-sm status-selector ' . $currentBadge . '" 
                                data-id="' . $row->id . '" 
                                data-current="' . $row->status . '" 
                                style="width: auto; font-weight: 500; font-size: 0.85rem; border-radius: 4px; padding: 0.25rem 0.5rem; border: none; cursor: pointer;">
                            ' . $optionsHtml . '
                        </select>
                    </div>';
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('Y-m-d H:i') : '-';
                })
                ->addColumn('action', function ($row) {
                    $showUrl = route('legal-notices.show', $row->id);
                    $editUrl = route('legal-notices.edit', $row->id);
                    $deleteUrl = route('legal-notices.destroy', $row->id);
                    $csrf = csrf_token();

                    return '<div class="text-center">
                        <a href="' . $showUrl . '" class="btn btn-info btn-sm text-white" title="View"><i class="bi bi-eye"></i></a>
                        <a data-bs-toggle="modal" href="#delete_modal_' . $row->id . '" class="btn btn-danger btn-sm" title="Delete">
                            <i class="bi bi-trash"></i>
                        </a>
                        <div id="delete_modal_' . $row->id . '" class="modal fade text-start" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Confirmation</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-start">
                                        <p>Are you sure you want to delete this Legal Notice? This action cannot be undone.</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <form action="' . $deleteUrl . '" method="POST" style="display:inline;">
                                            <input type="hidden" name="_token" value="' . $csrf . '">
                                            <input type="hidden" name="_method" value="DELETE">
                                            <button type="submit" class="btn btn-danger">Yes, delete it!</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('admin.legal_notices.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.legal_notices.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLegalNoticeRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $data['user_id'] = auth()->id();
            $data['status'] = 'Pending';

            LegalNotice::create($data);

            DB::commit();

            return redirect()->route('legal-notices.index')->with('success', 'Legal Notice created successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error creating Legal Notice: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $notice = LegalNotice::with(['customer', 'replies.admin'])->findOrFail($id);
        return view('admin.legal_notices.show', compact('notice'));
    }

    /**
     * Reply to the specified legal notice.
     */
    public function reply(Request $request, $id)
    {
        $notice = LegalNotice::with('customer')->findOrFail($id);

        $request->validate([
            'message' => 'required',
            'status' => 'required|in:Pending,Approved,Rejected,In Progress,Replied,Closed',
            'customer_email' => 'nullable|email',
        ]);

        $replyMessage = $request->input('message');
        $newStatus = $request->input('status');
        $recipientEmail = $request->input('customer_email') ?: ($notice->customer ? $notice->customer->email : null);

        $sendEmail = $request->boolean('send_email');

        DB::beginTransaction();
        try {
            // Save the reply message and status
            $reply = LegalNoticeReply::create([
                'legal_notice_id' => $notice->id,
                'admin_id' => auth()->id(),
                'message' => $replyMessage,
                'status' => $newStatus,
            ]);

            // Update legal notice status
            $notice->status = $newStatus;
            $notice->save();

            DB::commit();

            // Send Email to Customer
            $emailSent = false;
            $emailError = null;
            if ($sendEmail && $recipientEmail) {
                $customerName = $notice->customer ? $notice->customer->name : 'Customer';
                try {
                    if (!app()->runningUnitTests()) {
                        $mailDriver = setting('mail_driver');
                        if ($mailDriver) {
                            config([
                                'mail.default' => $mailDriver,
                                'mail.mailers.' . $mailDriver . '.transport' => $mailDriver,
                                'mail.mailers.' . $mailDriver . '.host' => setting('mail_host'),
                                'mail.mailers.' . $mailDriver . '.port' => setting('mail_port'),
                                'mail.mailers.' . $mailDriver . '.username' => setting('mail_username'),
                                'mail.mailers.' . $mailDriver . '.password' => setting('mail_password'),
                                'mail.mailers.' . $mailDriver . '.encryption' => setting('mail_encryption'),
                                'mail.from.address' => setting('mail_from_email'),
                                'mail.from.name' => setting('mail_from_name'),
                            ]);
                        }
                    }

                    Mail::raw("Dear {$customerName},\n\nAdmin has replied to your Legal Notice (ID: {$notice->id}).\n\nReply message:\n{$replyMessage}\n\nUpdated Status: {$newStatus}\n\nRegards,\nFast Agreements", function ($message) use ($recipientEmail) {
                        $message->to($recipientEmail)
                            ->subject('Reply to Legal Notice - Fast Agreements');
                    });
                    $emailSent = true;
                } catch (\Exception $e) {
                    $emailError = $e->getMessage();
                    \Illuminate\Support\Facades\Log::error('Failed to send legal notice reply email: ' . $e->getMessage());
                }
            }

            if ($sendEmail && !$emailSent) {
                return redirect()->route('legal-notices.show', $notice->id)
                    ->with('success', 'Reply saved successfully.')
                    ->with('error', 'Failed to send email notification: ' . $emailError);
            }

            return redirect()->route('legal-notices.show', $notice->id)->with('success', 'Reply sent successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error sending reply: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $notice = LegalNotice::findOrFail($id);
        return view('admin.legal_notices.edit', compact('notice'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLegalNoticeRequest $request, $id)
    {
        $notice = LegalNotice::findOrFail($id);

        DB::beginTransaction();
        try {
            $data = $request->validated();
            $notice->update($data);

            DB::commit();

            return redirect()->route('legal-notices.index')->with('success', 'Legal Notice updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error updating Legal Notice: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $notice = LegalNotice::findOrFail($id);

        DB::beginTransaction();
        try {
            $notice->delete();

            DB::commit();

            return redirect()->route('legal-notices.index')->with('success', 'Legal Notice deleted successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('legal-notices.index')->with('error', 'Error deleting Legal Notice: ' . $e->getMessage());
        }
    }

    /**
     * Update the status of the legal notice.
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
                'message' => 'Status updated successfully.',
                'new_status' => $newStatus
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export legal notices to PDF.
     */
    public function exportPdf()
    {
        try {
            $notices = LegalNotice::all();
            $pdf = Pdf::loadView('admin.legal_notices.pdf', compact('notices'));
            return $pdf->download('legal_notices_' . date('Ymd_His') . '.pdf');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export legal notices to CSV (Excel compatible).
     */
    public function exportExcel()
    {
        $notices = LegalNotice::all();

        $headers = [
            'ID', 'Company Name', 'Total Amount (INR)', 'Amount Due (INR)', 'Company Person Name',
            'Company Person Designation', 'Company Address', 'My Company Name', 'My Company Business Nature',
            'User ID', 'Status', 'Created At', 'Updated At'
        ];

        $csvContent = implode(',', $headers) . "\n";

        foreach ($notices as $notice) {
            $csvContent .= implode(',', [
                $notice->id,
                '"' . str_replace('"', '""', $notice->company_name) . '"',
                $notice->total_amount,
                $notice->amount_due,
                '"' . str_replace('"', '""', $notice->company_person_name) . '"',
                '"' . str_replace('"', '""', $notice->company_person_designation) . '"',
                '"' . str_replace('"', '""', $notice->company_address) . '"',
                '"' . str_replace('"', '""', $notice->my_company_name) . '"',
                '"' . str_replace('"', '""', $notice->my_company_business_nature) . '"',
                $notice->user_id,
                $notice->status,
                '"' . $notice->created_at . '"',
                '"' . $notice->updated_at . '"',
            ]) . "\n";
        }

        return Response::make($csvContent, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="legal_notices_' . date('Ymd_His') . '.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }
}
