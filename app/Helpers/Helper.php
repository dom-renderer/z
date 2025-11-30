<?php

namespace App\Helpers;

use App\Models\SubmissionTime;
use App\Models\ChecklistTask;
use App\Models\Designation;
use App\Models\TicketMember;
use App\Jobs\TicketMail;
use App\Models\ProductionCategory;
use App\Models\ProductionLog;
use App\Models\ProductionProduct;
use \Carbon\Carbon;

class Helper {

    public static $customForm = [
        'EXPITY_MATERIAL_FORM',
        'NON_AUTHORIZED_MATERIAL_FORM',
        'FRANCHISOR_FEEDBACK_SUMMARY_FORM',
        'PHYSICAL_CLSOING_STOCK_FORM',
        'AUDIT_SUMMARY'
    ];

    public static $customFormWithEloquent = [
        'EXPITY_MATERIAL_FORM' => \App\Models\ExpiryMaterial::class,
        'NON_AUTHORIZED_MATERIAL_FORM' => \App\Models\NonAuthorizedMaterial::class,
        'FRANCHISOR_FEEDBACK_SUMMARY_FORM' => \App\Models\FranchisorFeedback::class,
        'PHYSICAL_CLSOING_STOCK_FORM' => \App\Models\PhysicalClosingStock::class,
        'AUDIT_SUMMARY' => \App\Models\AuditRemark::class
    ];

    public static $customFormTitle = [
        'EXPITY_MATERIAL_FORM' => 'Expiry Material',
        'NON_AUTHORIZED_MATERIAL_FORM' => 'Non Authorized / Rangoli Material',
        'FRANCHISOR_FEEDBACK_SUMMARY_FORM' => 'Franchisor Feedback Summary',
        'PHYSICAL_CLSOING_STOCK_FORM' => 'Physical Closing Stock - 03 Products per Items',
        'AUDIT_SUMMARY' => 'Audit Summary (By Auditor)'
    ];

    public static $productionStatuses = [
        'pending' => 'Ready',
        'dispatch' => 'Dispatched',
        'expire' => 'Wastage',
    ];

    public static $productionStatusColors = [
        'pending' => 'success',
        'dispatch' => 'info',
        'expire' => 'danger',
    ];

    public static $customFormFields = [
        'EXPITY_MATERIAL_FORM' => [
            'category_id',
            'product_id',
            'quantity',
            'uom_id',
            'mrp',
            'manufacturing_date',
            'expiry_date'
        ],
        'NON_AUTHORIZED_MATERIAL_FORM' => [
            'category_id',
            'product_id',
            'quantity',
            'mrp',
            'remark'
        ],
        'FRANCHISOR_FEEDBACK_SUMMARY_FORM' => [
            'remark'
        ],
        'PHYSICAL_CLSOING_STOCK_FORM' => [
            'category_id',
            'product_id',
            'quantity',
            'uom_id'
        ],
        'AUDIT_SUMMARY' => [
            'remark'
        ]
    ];

    public static $status = [
        'pending' => 0,
        'in-progress' => 1,
        'in-verification' => 2,
        'completed' => 3
    ];

    public static $roles = [
        'admin' => 1,
        'store-manager' => 2,
        'store-employee' => 3,
        'store-cashier' => 4,
        'corporate-office-manager' => 5,
        'divisional-operations-manager' => 6,
        'head-of-department' => 7,
        'vice-president' => 8,
        'director' => 9,
        'operations-manager' => 10,
        'store-phone' => 11
    ];

    public static $rolesKeys = [
        1 => 'admin',
        2 => 'store-manager',
        3 => 'store-employee',
        4 => 'store-cashier',
        5 => 'corporate-office-manager',
        6 => 'divisional-operations-manager',
        7 => 'head-of-department',
        8 => 'vice-president',
        9 => 'director',
        10 => 'operations-manager',
        11 => 'store-phone'
    ];

    public static $notificationTemplatePlaceholders = [
        '{$name}' => 'Name',
        '{$username}' => 'Username',
        '{$phone_number}' => 'Phone Number',
        '{$email}' => 'Email',
        '{$branch_name}' => 'Branch Name',
        '{$checklist_name}' => 'Checklist Name',
        '{$section_name}' => 'Section Name'
    ];

    public static $frequency = [
        'Every Hour',
        'Every N Hours',
        'Daily',
        'Every N Days',
        'Weekly',
        'Biweekly',
        'Monthly',
        'Bimonthly',
        'Quarterly',
        'Semi Anually',
        'Anually',
        'Specific Week Days',
        'Once'
    ];
    public static $error = 'Something went wrong! Please try again later.';

    public static function generateTaskNumber($date, $employeeId = null) {
        $index = sprintf('%02d', ChecklistTask::withTrashed()->where(\DB::raw("DATE_FORMAT(date, '%Y-%m-%d')"), date('Y-m-d', strtotime($date)))->count() + 1);

        $sequence = "WO{$index}";

        $employeeId = \App\Models\User::select('employee_id')->where('id', $employeeId)->first()->employee_id ?? '';
        if (!empty($employeeId)) {
            $sequence .= "-{$employeeId}";
        }

        $sequence .= ('-' . date('d-m-y', strtotime($date)));

        return $sequence;
    }

    public static function generateWorfklowTaskNumber() {
        $taskNo = 0;
        
        if (ChecklistTask::withTrashed()->orderBy('id', 'DESC')->first() !== null) {
            $taskNo = ChecklistTask::withTrashed()->orderBy('id', 'DESC')->first()->id;
        }

        $taskNo += 1;
        $taskNo = sprintf('%07d', $taskNo);
        $taskNo = "WF{$taskNo}";

        return $taskNo;
    }

    public static function sendPushNotification($device_ids, $data) {

        $keyFilePath = storage_path('app/firebase.json');
        
        $client = new \Google\Client();
        $client->setAuthConfig($keyFilePath);
        $client->setScopes(['https://www.googleapis.com/auth/firebase.messaging']);
    
        $tokenArray = $client->fetchAccessTokenWithAssertion();
        
        if (isset($tokenArray['error'])) {
            return false;
        }
    
        $accessToken = $tokenArray['access_token'];


        foreach ($device_ids as $did) {
            $notification = json_encode([
                "message" => [
                    "token" => $did, 
                    "notification" => [
                        "body" => $data['description'],
                        "title" => $data['title'],
                    ],
                    "android" => [
                        "priority" => "HIGH",
                    ],
                ]
            ]);
            
            $headers = array(
                'Authorization: Bearer '.$accessToken,
                'Content-Type: application/json'
            );
    
            $ch = curl_init();
            
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/v1/projects/teapost-checklist/messages:send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $notification);
    
            curl_exec($ch);
        }

        return true;
    }

    public static function getKeyValueHavingValue($data, $prefix = '') {
        $prefix = strtolower($prefix);
        $results = [];
        
        foreach ($data as $key => $value) {
            $currentKey = $prefix ? $prefix . '.' . $key : $key;
            
            if (is_array($value) || is_object($value)) {
                $results = array_merge($results, self::getKeyValueHavingValue($value, $currentKey));
            } else {
                if (strtolower($value) === 'no' || strtolower($value) === 'fail') {
                    $results[$currentKey] = $value;
                }
            }
        }
        
        return $results;
    }

    public static function getCountHavingKey($data, $prefix = '')
    {
        $count = 0;
    
        if (is_array($data) || is_object($data)) {
            foreach ($data as $key => $value) {
                if ($key === 'name') {
                    $count++;
                }
                $count += self::getCountHavingKey($value);
            }
        }
    
        return $count;
    }    

    public static function getKeyValueHavingValueDomDashboard($data, $prefix = '') {
        $prefix = strtolower($prefix);
        $results = [];
        
        foreach ($data as $key => $value) {
            $currentKey = $prefix ? $prefix . '.' . $key : $key;
            if (is_array($value) || is_object($value)) {
                $results = array_merge($results, self::getKeyValueHavingValueDomDashboard($value, $currentKey));
            } else {
                if (strtolower($value) === 'no' || strtolower($value) === 'fail') {
                    $results[$currentKey] = $value['label'];
                }
            }
        }

        return $results;
    }

    public static function getCountHavingKeyDomDashboard($data, $prefix = '')
    {
        $count = 0;
    
        if (is_array($data) || is_object($data)) {
            foreach ($data as $key => $value) {
                if ($key === 'name') {
                    $count++;
                }
                $count += self::getCountHavingKeyDomDashboard($value);
            }
        }
    
        return $count;
    }    

    public static function slug($string, $separator = '-') {
        $string = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $string);
        $string = trim($string, $separator);
        if (function_exists('mb_strtolower')) {
            $string = mb_strtolower($string);
        } else {
            $string = strtolower($string);
        }
        $string = preg_replace("/[\/_|+ -]+/", $separator, $string);

        return $string;
    }

    public static function isBase64($string) {
        if (empty($string) || strlen($string) < 4) {
            return false;
        }
    
        $decoded = base64_decode($string, true);
        return base64_encode($decoded) === $string;
    }

    public static function getBase64Extension($base64String) {
        $matches = [];
        preg_match("/data:image\/(.*);base64/", $base64String, $matches);
        return $matches[1] ?? 'png';
    }

    public static function downloadBase64File($base64String, $title, $path)
    {        
        $extension = self::getBase64Extension($base64String);

        $fileData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64String));
        $filename = "{$title}.{$extension}";
        $filePath = "{$path}/{$filename}";

        file_put_contents($filePath, $fileData);

        return $filename;
    }

    public static function createImageThumbnail($source, $destination, $width = 200, $height = 200) {
        list($sourceWidth, $sourceHeight, $sourceType) = getimagesize($source);
        
        switch ($sourceType) {
            case IMAGETYPE_JPEG:
                $sourceGd = imagecreatefromjpeg($source);
                break;
            case IMAGETYPE_PNG:
                $sourceGd = imagecreatefrompng($source);
                break;
            case IMAGETYPE_GIF:
                $sourceGd = imagecreatefromgif($source);
                break;
            default:
                return false;
        }
        
        $thumb = imagecreatetruecolor($width, $height);
        imagecopyresized($thumb, $sourceGd, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);
        
        switch ($sourceType) {
            case IMAGETYPE_JPEG:
                imagejpeg($thumb, $destination, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumb, $destination);
                break;
            case IMAGETYPE_GIF:
                imagegif($thumb, $destination);
                break;
        }
        
        imagedestroy($sourceGd);
        imagedestroy($thumb);
        
        return true;
    }    

    public static function selectPointsQuestions($json)
    {
        $data = [];

        if (is_string($json)) {
            $data = json_decode($json, true);
        } else if (is_array($json) || is_object($json)) {
            $data = json_decode(json_encode($json), true);
        }

        if (!is_array($data)) {
            return [];
        }

        return array_filter($data, function ($item) {
            return isset($item['name']) && (preg_match('/^points?-/', $item['name']) || preg_match('/^point?-/', $item['name']));
        });
    }

    public static function categorizePoints($json)
    {
        $data = [];

        if (is_string($json)) {
            $data = json_decode($json, true);
        } else if (is_array($json) || is_object($json)) {
            $data = json_decode(json_encode($json), true);
        }

        if (!is_array($data)) {
            return [];
        }

        $result = [
            "positive" => [],
            "negative" => [],
            "na" => []
        ];

        foreach ($data as $item) {
            if (!isset($item['name']) || (!preg_match('/^points?-/', $item['name']) && !preg_match('/^point?-/', $item['name']))) {
                continue;
            }

            $valueLabel = isset($item['value_label']) ? strtolower($item['value_label']) : '';

            if (in_array($valueLabel, ["yes", "pass"])) {
                $result["positive"][] = $item;
            } elseif (in_array($valueLabel, ["no", "fail"])) {
                $result["negative"][] = $item;
            } elseif ($valueLabel === "na" || $valueLabel === "n/a") {
                $result["na"][] = $item;
            }
        }

        return $result;
    }

    public function taskLog($id) {
        $task = ChecklistTask::find(decrypt($id));
        $page_title = 'Task ' . $task->code . ' Log';

        return view('task-logs', compact('task', 'page_title'));
    }

    public static function getQuestionField($fields) {
        $data = 'N/A';

        try {
            if (is_array($fields)) {
                $foundYet = false;
                foreach ($fields as $row) {
                    if (strpos($row->name, 'checkbox-group') !== false || strpos($row->name, 'radio-group') !== false) {
                        $data = $row->label;
                        $foundYet = true;
                        break;
                    }
                }

                if ($foundYet === false) {
                    $data = isset($fields[0]->label) ? $fields[0]->label : $data;
                }
            }
        } catch (\Exception $e) {
            $data = isset($fields[0]->label) ? $fields[0]->label : $data;
        }

        return $data;
    }

    public static function isPointChecklist($json)
    {
        $data = $res = [];

        if (is_string($json)) {
            $data = json_decode($json, true);
        } else if (is_array($json) || is_object($json)) {
            $data = json_decode(json_encode($json), true);
        }

        if (!is_array($data)) {
            $res = $data = [];
        }

        $res = array_filter($data, function ($innerItem) {
            return array_filter($innerItem, function ($item) {
                return isset($item['name']) && (preg_match('/^points?-/', $item['name']) || preg_match('/^point?-/', $item['name']));
            });
        });

        return boolval($res);
    }

    public static function getCountOfCountableQuestions($json)
    {
        $data = [];

        if (is_string($json)) {
            $data = json_decode($json, true);
        } else if (is_array($json) || is_object($json)) {
            $data = json_decode(json_encode($json), true);
        }

        if (!is_array($data)) {
            return [];
        }

        return array_filter($data, function ($item) {
            return isset($item['name']) && (preg_match('/^radio?-/', $item['name']) || preg_match('/^select?-/', $item['name']));
        });
    }

    public static function getBooleanFields($json) {
        $data = [];

        if (is_string($json)) {
            $data = json_decode($json, true);
        } else if (is_array($json) || is_object($json)) {
            $data = json_decode(json_encode($json), true);
        }

        if (!is_array($data)) {
            return [];
        }

        $result = [
            "truthy" => [],
            "falsy" => []
        ];

        foreach ($data as $item) {
            if (!(isset($item['name']) && (preg_match('/group-/', $item['name'])) || preg_match('/radio-/', $item['name']))) {
                continue;
            }

            $valueLabel = isset($item['value_label']) ? strtolower($item['value_label']) : '';

            if (in_array($valueLabel, ["yes", "pass"])) {
                $result["truthy"][] = $item;
            } elseif (in_array($valueLabel, ["no", "fail"])) {
                $result["falsy"][] = $item;
            }
        }

        return $result;
    }

    public static function addGraceTime($timestamp, $time) {
        list($hours, $minutes, $seconds) = explode(':', $time);
        $carbonDate = Carbon::createFromFormat('d-m-Y H:i:s', $timestamp);
        $carbonDate->addHours($hours)->addMinutes($minutes)->addSeconds($seconds);

        return $carbonDate->toDateTimeString();
    }

    public static function getFirstBranch($user, $branchType = null) {
        $branch = Designation::select('type_id')
        ->where('type', $branchType)
        ->where('user_id', $user)
        ->first();

        if ($branch) {
            return [
                'branch_type' => $branchType,
                'branch_id' => $branch->type_id ?? null,
                'user_id' => $user
            ];
        } else {
            return [
                'branch_type' => 1,
                'branch_id' => null,
                'user_id' => $user
            ];
        }
    }

    public static function calculateTotalTime($taskId)
    {
        $submissionTimes = SubmissionTime::where('task_id', $taskId)
            ->orderBy('timestamp', 'asc')
            ->get();
        
        $totalSeconds = 0;
        $startTime = null;
        
        foreach ($submissionTimes as $submission) {
            if ($submission->type == 1) {
                $startTime = Carbon::parse($submission->timestamp);
            } elseif ($submission->type == 2 && $startTime) {
                $endTime = Carbon::parse($submission->timestamp);
                $diffInSeconds = $endTime->diffInSeconds($startTime);
                $totalSeconds += $diffInSeconds;
                $startTime = null;
            }
        }
        
        if ($startTime !== null) {
            $now = Carbon::now();
            $diffInSeconds = $now->diffInSeconds($startTime);
            $totalSeconds += $diffInSeconds;
        }
        
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;
        
        $formattedTime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        
        return $formattedTime;
    }

    public static function calculateRemainingTime($totalAllocated, $timeSpent = null, $allowNegative = true)
    {        
        $totalAllocatedParts = explode(':', $totalAllocated);
        $totalAllocatedSeconds = ($totalAllocatedParts[0] * 3600) + ($totalAllocatedParts[1] * 60) + $totalAllocatedParts[2];
        
        $timeSpentParts = explode(':', $timeSpent);
        $timeSpentSeconds = ($timeSpentParts[0] * 3600) + ($timeSpentParts[1] * 60) + $timeSpentParts[2];
        
        $remainingSeconds = $totalAllocatedSeconds - $timeSpentSeconds;
        
        $isNegative = false;
        if (!$allowNegative && $remainingSeconds < 0) {
            $remainingSeconds = 0;
        } elseif ($remainingSeconds < 0) {
            $isNegative = true;
            $remainingSeconds = abs($remainingSeconds);
        }
        
        $hours = floor($remainingSeconds / 3600);
        $minutes = floor(($remainingSeconds % 3600) / 60);
        $seconds = $remainingSeconds % 60;
        
        $formattedTime = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        
        if ($isNegative) {
            $formattedTime = '-' . $formattedTime;
        }
        
        return $formattedTime;
    }

    public static function setting($key = null)
    {
        if($key){
            try{
                return \App\Models\TicketSetting::select($key)->first()->$key;
            } catch (\Exception $e) {
            }
        }
		$settingData = \App\Models\TicketSetting::first();
		if (!$settingData) {
			$settingData = (object) array('name' => '', 'favicon' => '', 'logo' => '');
		}
		return $settingData;
	}

        public static function ticket_mail_send($id,$ticket_type){
        $ticket = \App\Models\Ticket::with(['user','latest_comments'])->find($id);
        $agents = TicketMember::where('ticket_id', $ticket->id)->pluck('user_id')->toArray();
        $users = [];

        if($ticket_type == 'Add' && !empty($agents)){
            $users = \App\Models\User::whereNotIn('id',[auth()->user()->id])->whereIn('id',$agents)->get();
        }
        else if($ticket_type == 'Reply' && !empty($agents)){
            $users = \App\Models\User::whereIn('id',$agents)->get();
        }
        else if($ticket_type == 'Complete'){
            $users = [$ticket->user];
        } else if($ticket_type == 'Estimate date added'){
            $users = [$ticket->user];
        } else if($ticket_type == 'Estimate date changed'){
            $users = [$ticket->user];
        } else if($ticket_type == 'Reopened' && !empty($agents)){
            $users = \App\Models\User::whereIn('id',$agents)->get();
        }

        if(!empty($users)){
            $content = $ticket->content;

            if($ticket_type == 'Estimate date added' || $ticket_type == 'Estimate date changed'){
                $content = 'Estimated date is '.date('d-m-Y',strtotime($ticket->estimate_time));
            }

            $allUsers = $users;

            foreach ($allUsers as $users) {
                $ticket_data = array(
                    'the_ticket' => $ticket,
                    'ticket_type' => $ticket_type,
                    'ticket_added_by' => auth()->user()->email,
                    'ticket_number' => $ticket->ticket_number,
                    'subject' => $ticket->subject,
                    'content' => $content,
                    'ticket_replay_message' => isset($ticket->latest_comments->html) && $ticket->latest_comments->html != null ? $ticket->latest_comments->html : ''
                );

                TicketMail::dispatch($ticket_data, $users, $ticket_type, $ticket);
            }
        }
    }

    public static function check_ticket_replay_by_agent($id){

        if(isset(auth()->user()->roles[0]->id)){
            $status = true;
        } else {
            $ticket_comment = \App\Models\Comment::where(['ticket_id' => $id])->count();
            $status = $ticket_comment > 0 ? true : false;
        }

        return $status;
    }

    public static function getLatestStatus ($taskId, $className) {
        return \App\Models\Ticket::select('status_id')->where('task_id', $taskId)->where('field_id', $className)->first()->status->name ?? 'Pending';
    }

    public static function parseFlexibleDate($dateString) {
        $formats = ['d/m/Y', 'd-m-Y'];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            $errors = \DateTime::getLastErrors();

            if ($errors === false) {
                return $date->format('Y-m-d');
            }

            if ($date && $errors['warning_count'] == 0 && $errors['error_count'] == 0) {
                return $date->format('Y-m-d');
            }
        }

        return '1970-01-01';
    }

    public static function getCustomFormListing($json)
    {
        $data = $res = [];

        if (is_string($json)) {
            $data = json_decode($json, true);
        } else if (is_array($json) || is_object($json)) {
            $data = json_decode(json_encode($json), true);
        }

        if (!is_array($data)) {
            $res = $data = [];
        }

        $res = array_filter($data, function ($innerItem) {
            return array_filter($innerItem, function ($item) {
                return isset($item['name']) && isset($item['type']) && $item['type'] == 'hidden' && in_array($item['name'], self::$customForm);
            });
        });

        return collect($res)->flatten(1)->pluck('name');
    }

    private static function getCategoriesId($category) {
        return [
            'id' => $category->id,
            'children' => $category->children->map(function ($child) {
                return self::getCategoriesId($child);
            })->values()->toArray()
        ];
    }

    public static function getAllProductionSubCategories($id) {
        $categories = ProductionCategory::with('children')->parents()->get()->map(function ($category) {
            return self::getCategoriesId($category);
        })->values()->toArray();

        $categories = \Arr::flatten($categories);
        if (($key = array_search($id, $categories)) !== false) {
            unset($categories[$key]);
        }

        return array_values($categories);
    }

    public static function productionSubCategoryHasProduct($id) {
        return ProductionProduct::whereIn('category_id', self::getAllProductionSubCategories($id))->exists();
    }

    public static function hasProductionSubCategory($id) {
        if (!empty(self::getAllProductionSubCategories($id))) {
            return true;
        }

        return false;
    }

    public static function productionLog( $production_id, $comment ) {
        $dataArray = [
            'production_id' => $production_id,
            'added_by' => auth()->user()->id,
            'comment' => $comment,
        ];

        ProductionLog::create( $dataArray );
    }

    public static function formatNumber($number)
    {
        if (is_float($number)) {
            return number_format($number, 2, '.', ',');
        }
        return number_format($number);
    }

}
