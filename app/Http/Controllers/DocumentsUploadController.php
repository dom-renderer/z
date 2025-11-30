<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentUpload;
use App\Models\DocumentUser;
use App\Models\NotificationTemplate;
use App\Models\Store;
use App\Models\StoreCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class DocumentsUploadController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data_query = DocumentUpload::with( [ 'document', 'store', 'storeCategory' ] );
            return datatables()
            ->eloquent( $data_query )
            ->addColumn('document_name', function ($row) {
                return !empty($row->document) ? $row->document->name : '-';
            })
            ->addColumn('attachment', function ($row) {
                if ( !empty($row->attachment_path) ) {
                    return '<a href="' . $row->attachment_path . '" target="_blank" class="btn btn-sm btn-secondary">View</a>';
                }
                return '-';
            })
            ->addColumn('location', function ($row) {
                return !empty($row->store) ? $row->store->name : '-';
            })
            ->addColumn('location_category', function ($row) {
                return !empty($row->storeCategory) ? $row->storeCategory->name : '-';
            })
            ->editColumn('expiry_date', function ($row) {
                return !empty($row->expiry_date) ? \Carbon\Carbon::parse( $row->expiry_date )->format( 'd-m-Y' ) : '-';
            })
            ->editColumn('issue_date', function ($row) {
                return !empty($row->issue_date) ? \Carbon\Carbon::parse( $row->issue_date )->format( 'd-m-Y' ) : '-';
            })
            ->addColumn('action', function ($row) {
                $action = '';

                if (auth()->user()->can('document-upload.show')) {
                    $action .= '<a href="'.route("document-upload.show", encrypt($row->id)).'" class="btn btn-warning btn-sm me-2"> Show </a>';
                }

                if (auth()->user()->can('document-upload.edit')) {
                    $action .= '<a href="'.route('document-upload.edit', encrypt($row->id)).'" class="btn btn-info btn-sm me-2">Edit</a>';
                }

                if (auth()->user()->can('document-upload.destroy')) {
                    $action .= '<form method="POST" action="'.route("document-upload.destroy", encrypt($row->id)).'" style="display:inline;"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="'.csrf_token().'"><button type="submit" class="btn btn-danger btn-sm deleteGroup">Delete</button></form>';
                }

                return $action;
            })
            ->filterColumn('document_name', function($query, $keyword) {
                $query->whereHas('document', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('location', function($query, $keyword) {
                $query->whereHas('store', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('location_category', function($query, $keyword) {
                $query->whereHas('storeCategory', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns( [ 'document_name', 'attachment', 'location', 'expiry_date', 'issue_date', 'action' ] )
            ->addIndexColumn()
            ->toJson();
        }

        $page_title = 'Document Upload';
        $page_description = 'Manage Document Upload here';

        return view( 'document-upload.index', compact( 'page_title', 'page_description' ) );
    }

    public function create()
    {
        $page_title = 'Document Upload Add';
        $document_arr = Document::all();
        $location_category_arr = StoreCategory::all();
        $notification_template_arr = NotificationTemplate::all();

        return view( 'document-upload.create', compact( 'page_title', 'document_arr', 'location_category_arr', 'notification_template_arr' ) );
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'zp_document_file' => [
                'required',
                'file',
                'max:20480', // 20 MB
                function ($attribute, $value, $fail) {
                    $forbidden = ['exe', 'sh', 'bat', 'php', 'js', 'msi', 'cmd', 'com', 'vbs'];
                    $ext = strtolower($value->getClientOriginalExtension());

                    if (in_array($ext, $forbidden)) {
                        $fail('Please Upload valid file');
                    }
                },
            ],
            'zp_document'          => 'required',
            'zp_location_category' => 'required',
            'zp_location'          => 'required',
            'zp_expiry_date'       => 'required|date_format:Y-m-d',
            'zp_issue_date'        => 'required|date_format:Y-m-d',
            'zp_remark'            => 'required',
        ]);

        $fileName = Str::random( 40 ) . '.' . $request->file( 'zp_document_file' )->getClientOriginalExtension();
        $request->file( 'zp_document_file' )->storeAs( 'documents', $fileName, 'public' );

        $document_other = array();
        if ( !empty($request->zp_template) ) {
            $document_other[ 'notification_template' ] = $request->zp_template;
        }

        $document_upload_data = DocumentUpload::create([
            'file_name'            => $fileName,
            'document_id'          => $request->zp_document,
            'location_category_id' => $request->zp_location_category,
            'location_id'          => $request->zp_location,
            'expiry_date'          => $request->zp_expiry_date,
            'issue_date'           => $request->zp_issue_date,
            'remark'               => $request->zp_remark,
            'document_other'       => !empty($document_other) ? json_encode( $document_other ) : null,
        ]);

        if ( !empty($request->zp_users) ) {
            foreach ( $request->zp_users as $user_id ) {
                DocumentUser::create([
                    'document_upload_id' => $document_upload_data->id,
                    'user_id' => $user_id
                ]);
            }
        }

        return redirect()->route('document-upload.index')->with('success','Document Upload created successfully');
    }

    public function show($id)
    {
        $page_title = 'Document Upload Show';
        $documentupload = DocumentUpload::find(decrypt($id));
        
        $document_other = !empty($documentupload->document_other) ? json_decode( $documentupload->document_other, true ) : array();
        $notification_id_arr = !empty($document_other['notification_template']) ? $document_other['notification_template'] : array();

        $document_arr = Document::all();
        $location_category_arr = StoreCategory::all();
        $notification_template_arr = NotificationTemplate::all();
    
        return view( 'document-upload.show', compact( 'documentupload', 'page_title', 'notification_id_arr', 'document_arr', 'location_category_arr', 'notification_template_arr' ) );
    }

    public function edit($id)
    {
        $page_title = 'Document Upload Edit';
        $documentupload = DocumentUpload::find( decrypt( $id ) );
        $document_other = !empty($documentupload->document_other) ? json_decode( $documentupload->document_other, true ) : array();
        $notification_id_arr = !empty($document_other['notification_template']) ? $document_other['notification_template'] : array();

        $document_arr = Document::all();
        $location_category_arr = StoreCategory::all();
        $notification_template_arr = NotificationTemplate::all();
    
        return view( 'document-upload.edit', compact( 'documentupload', 'page_title', 'id', 'document_other', 'notification_id_arr', 'notification_template_arr', 'location_category_arr', 'document_arr' ) );
    }
    
    public function update(Request $request, $id)
    {
        $cId = decrypt($id);
        $documentUpload = DocumentUpload::findOrFail( $cId );
        $request->validate([
            'zp_document_file' => [
                'nullable',
                'file',
                'max:20480', // 20 MB
                function ($attribute, $value, $fail) {
                    $forbidden = ['exe', 'sh', 'bat', 'php', 'js', 'msi', 'cmd', 'com', 'vbs'];
                    $ext = strtolower($value->getClientOriginalExtension());

                    if (in_array($ext, $forbidden)) {
                        $fail('Please Upload valid file');
                    }
                },
            ],
            'zp_document'          => 'required',
            'zp_location_category' => 'required',
            'zp_location'          => 'required',
            'zp_expiry_date'       => 'required|date_format:Y-m-d',
            'zp_issue_date'        => 'required|date_format:Y-m-d',
            'zp_remark'            => 'required',
        ]);

        if ( $request->hasFile( 'zp_document_file' ) ) {
            if ( !empty($documentUpload->file_name) ) {
                $oldFilePath = public_path( 'storage/documents/' . $documentUpload->file_name );
                if ( file_exists( $oldFilePath ) ) {
                    unlink( $oldFilePath );
                }
            }

            $fileName = Str::random(40) . '.' . $request->file( 'zp_document_file' )->getClientOriginalExtension();
            $request->file( 'zp_document_file' )->storeAs( 'documents', $fileName, 'public' );
            $documentUpload->file_name = $fileName;
        }

        $documentUpload->document_id          = $request->zp_document;
        $documentUpload->location_category_id = $request->zp_location_category;
        $documentUpload->location_id          = $request->zp_location;
        $documentUpload->expiry_date          = $request->zp_expiry_date;
        $documentUpload->issue_date           = $request->zp_issue_date;
        $documentUpload->remark               = $request->zp_remark;

        $document_other = [];
        if ( !empty($request->zp_template) ) {
            $document_other['notification_template'] = $request->zp_template;
        }
        $documentUpload->document_other = !empty($document_other) ? json_encode( $document_other ) : null;

        $documentUpload->save();

        DocumentUser::where( 'document_upload_id', $documentUpload->id )->delete();
        if ( !empty($request->zp_users) ) {
            foreach ( $request->zp_users as $user_id ) {
                DocumentUser::create([
                    'document_upload_id' => $documentUpload->id,
                    'user_id' => $user_id,
                ]);
            }
        }

        return redirect()->route('document-upload.index')->with('success','Document Upload updated successfully');
    }

    public function destroy($id)
    {
        $id = decrypt($id);
        $documentUpload = DocumentUpload::findOrFail( $id );
        if ($documentUpload->file_name) {
            $filePath = public_path( 'storage/documents/' . $documentUpload->file_name );
            if ( file_exists( $filePath ) ) {
                unlink( $filePath );
            }
        }
        DocumentUser::where( 'document_upload_id', $documentUpload->id )->delete();
        $documentUpload->delete();

        return redirect()->back()->with('success','Document Upload deleted successfully');
    }

    public static function sendDocumentExpiryReminder()
    {
        $days = [30, 15, 7, 3, 2, 1];
        $today = \Carbon\Carbon::today();

        foreach ($days as $day) {
            $targetDate = $today->copy()->addDays($day);
            $documents = DocumentUpload::with(['document', 'users'])->whereDate('expiry_date', $targetDate)->get();

            if ( $documents->isNotEmpty() ) {
                foreach ($documents as $doc) {
                    $templatesIds = !empty($doc->document_other) ? json_decode($doc->document_other, true)['notification_template'] ?? [] : [];
    
                    $allTemplates = NotificationTemplate::whereIn('id', $templatesIds)->get();
    
                    foreach ($allTemplates as $template) {
                        foreach ($doc->users as $user) {
                            
                            $content = str_replace(
                                ['{user_name}', '{document_name}', '{expiry_date}'],
                                [$user->name ?? 'N/A', $doc->document->name ?? 'N/A', \Carbon\Carbon::parse( $doc->expiry_date )->format('d-m-Y')],
                                $template->content
                            );
    
                            Mail::send([], [], function ($message) use ($user, $template, $content, $doc) {
                                $message->to( $user->email, $user->name )
                                    ->subject( $template->title . ' - ' . $doc->document->name )
                                    ->setBody( $content, 'text/html' );
                            });
                            sleep(1);
                        }
                    }
                }
            }
        }
        echo 'Document expiry reminders sent successfully.';
    }

}

