<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function edit()
    {
        $setting = Setting::first();
        return view('settings.edit', compact('setting'));
    }

    public function update(Request $request)
    {
        Setting::updateOrCreate(
            ['id' => 1],
            [
                // 'ticket_watchers' => $request->ticket_watchers,
                // 'send_mail_at' => date('H:i:s', strtotime($request->send_mail_at)),
                // 'should_send_ticket_mail' => $request->has('should_send_ticket_mail') ? 1 : 0,
                'cims' => $request->cims
            ]
        );

        return back()->with('success', 'Settings updated successfully.');
    }
}
