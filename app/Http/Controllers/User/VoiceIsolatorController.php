<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\HelperService;
use App\Models\SubscriptionPlan;
use App\Models\VoiceIsolatorResult;
use App\Models\User;
use App\Models\ExtensionSetting;
use Exception;
use GuzzleHttp\Client;

class VoiceIsolatorController extends Controller
{

    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   

        $check = ExtensionSetting::first();

        $results = VoiceIsolatorResult::where('user_id', auth()->user()->id)->orderBy('created_at','desc')->get();

        if (is_null(auth()->user()->plan_id)) {
            if (is_null($check->voice_isolator_free_tier) || !$check->voice_isolator_free_tier) {
                toastr()->warning(__('Voice Isolator feature is not available for free tier users, subscribe to get a proper access'));
                return redirect()->route('user.plans');
            } else {
                return view('user.voice_isolator.index', compact('results'));
            }
        } else {
            $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            if ($plan->voice_isolator_feature == false) {     
                toastr()->warning(__('Your current subscription plan does not include support for Voice Isolator feature'));
                return redirect()->back();                   
            } else {
                return view('user.voice_isolator.index', compact('results'));
            }
        } 

    }


    /**
	*
	* Process Davinci Image to Video
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function create(Request $request) 
    {
        if ($request->ajax()) {

            $check = ExtensionSetting::first();

            if (is_null($check->voice_isolator_elevenlabs_api) || $check->voice_isolator_elevenlabs_api == '') {
                $data['status'] = 'error';
                $data['message'] = __('You must include your Elevenlabs API key first');
                return $data; 
            } 

            # Verify if user has enough credits
            $credit_status = $this->checkCredits();
            if (!$credit_status) {
                $data['status'] = 'error';
                $data['message'] = __('Not enough characters to proceed, subscribe or top up your character balance and try again');
                return $data;
            }

            $response = $this->generate($request);            

            if ($response) {   
                $data['status'] = 'success';
                $data['result'] = $response;
                $data['message'] = __('Voice isolation task has been successfully created');                  
                return $data; 
                 

            } else {
                $data['status'] = 'error';
                $data['message'] =  __('There was an error while creating Voice isolation task');  ;                  
                return $data; 
            }

          
        }
	}


    public function generate(Request $request)
    {
        set_time_limit(3000);

        $setting = ExtensionSetting::first();        

        $name = request()->file('audio')->getClientOriginalName();
        $path = request()->file('audio')->getRealPath();
        $extension = request()->file('audio')->getClientOriginalExtension();

        try {
            $client = new Client;
            $response = $client->request('POST', 'https://api.elevenlabs.io/v1/audio-isolation', [
                'headers' => [
                    'xi-api-key' => $setting->voice_isolator_elevenlabs_api,
                ],
                'multipart' => [
                    [
                        'name'     => 'audio',
                        'contents' => fopen($path, 'r'),
                        'filename' => $name,
                    ],
                ],
                'timeout' => 3000,
            ]);

 

            $response_audio = $response->getBody();
            $cost = $response->getHeader('character-cost');

            $file_name = 'isolator-' . Str::random(20) . '.mp3';

            Storage::disk('public')->put('isolator/' . $file_name, $response_audio);

            $result = VoiceIsolatorResult::create([
                'user_id' => auth()->user()->id,
                'file_name' => $name,
                'type'=> $extension,
                'url' => 'isolator/' . $file_name,
                'cost' => $cost[0]
            ]);

            $result->save();

            # Update credit balance
            $this->updateBalance((int)$cost[0]);

            $audio_box = '<div class="col-md-6 col-sm-12">
								<div class="card mb-5 border-0 p-4 avatar-voice-samples-box">
									<div class="d-flex avatar-voice-samples">
										<div class="flex">
											<button type="button" class="result-play text-center mr-2" title="'.__('Play Audio').'" onclick="resultPlay(this)" src="'. asset($result->url) .'" id="'. $result->id .'"><i class="fa fa-play table-action-buttons view-action-button"></i></button>											
										</div>
										<div class="flex mt-auto mb-auto">
											<p class="mb-2 font-weight-bold fs-12">'. $result->file_name .'</p>
											<p class="mb-0 fs-11 text-muted">'.__('Cost') . ' ' .  $result->cost . ' ' . __('characters') .'</p>
										</div>
										<div class="btn-group dashboard-menu-button flex" style="top:1.4rem">
											<button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" id="export" data-bs-display="static" aria-expanded="false"><i class="fa-solid fa-ellipsis  table-action-buttons table-action-buttons-big edit-action-button" style="background: none"></i></button>
											<div class="dropdown-menu" aria-labelledby="export" data-popper-placement="bottom-start">								
												<a class="dropdown-item" href="'. asset($result->url) .'" download>'. __('Download') .'</a>	
											</div>
										</div>												
									</div>							
								</div>
							</div>';

            return $audio_box;

        } catch (Exception $e) {
            \Log::info($e->getMessage());
            return false;
        }
        

    }


    public function checkCredits()
    {
        $cost = 500;

        if (auth()->user()->characters != -1) {
            if ((auth()->user()->characters + auth()->user()->characters_prepaid) < $cost) {
                if (!is_null(auth()->user()->member_of)) {
                    if (auth()->user()->member_use_credits_image) {
                        $member = User::where('id', auth()->user()->member_of)->first();
                        if (($member->characters + $member->characters_prepaid) < $cost) {
                            return false;
                        }
                    } else {
                        return false;
                    }
                    
                } else {
                    return false;
                } 
            }
        }

        return true;
    }


    /**
	*
	* Update user character balance
	* @param - total words generated
	* @return - confirmation
	*
	*/
    public function updateBalance($total) 
    {
        HelperService::updateCharacterBalance($total);
    }


    /**
	*
	* Delete File
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function delete(Request $request) 
    {
        if ($request->ajax()) {

            $image = VoiceIsolatorResult::where('id', request('id'))->first(); 

            if ($image->user_id == auth()->user()->id){

                $image->delete();

                return response()->json(200);
    
            } else{
                return response()->json(400);
            }  
        }
	}

}
