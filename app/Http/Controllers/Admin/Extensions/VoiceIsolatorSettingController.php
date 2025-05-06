<?php

namespace App\Http\Controllers\Admin\Extensions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExtensionSetting;


class VoiceIsolatorSettingController extends Controller
{
    public function index()
    {
        $extension = ExtensionSetting::first();

        return view('admin.davinci.configuration.extension.voice-isolator.setting', compact('extension'));
    }


    public function store(Request $request)
    {
        $this->storeValues(request('voice_isolator_elevenlabs_api'), 'voice_isolator_elevenlabs_api');

        $this->storeCheckbox(request('voice_isolator_feature'), 'voice_isolator_feature');
        $this->storeCheckbox(request('voice_isolator_free_tier'), 'voice_isolator_free_tier');

        toastr()->success(__('Settings have been saved successfully'));
        return redirect()->back();         
    }


    private function storeCheckbox($checkbox, $field_name)
    {
        if ($checkbox == 'on') {
            $status = true; 
        } else {
            $status = false;
        }

        $settings = ExtensionSetting::first();
        $settings->update([
            $field_name => $status
        ]);
    }


    private function storeValues($value, $field_name)
    {
        $settings = ExtensionSetting::first();
        $settings->update([
            $field_name => $value
        ]);
    }


}


