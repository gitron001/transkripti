<?php

namespace App\Http\Controllers\Admin\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use App\Models\FrontendFeature;
use App\Models\FrontendStep;
use App\Models\FrontendTool;
use App\Models\FrontendCase;
use App\Models\FrontendClient;
use DataTables;

class FrontendSectionController extends Controller
{
    /**
     * Show appearance settings page
     *
     * @return \Illuminate\Http\Response
     */
    public function showSteps(Request $request)
    {
        if ($request->ajax()) {
            $data = FrontendStep::all()->sortByDesc("order");
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('actions', function($row){
                        $actionBtn = '<div>                                            
                                            <a href="'. route("admin.settings.step.edit", $row["id"] ). '"><i class="fa-solid fa-pencil-square table-action-buttons edit-action-button" title="'. __('Edit Step') .'"></i></a>
                                            <a class="deleteButton" id="'. $row["id"] .'" href="#"><i class="fa-solid fa-trash-xmark table-action-buttons delete-action-button" title="'. __('Delete Step') .'"></i></a>
                                        </div>';
                        return $actionBtn;
                    })
                    ->addColumn('updated-on', function($row){
                        $created_on = '<span class="font-weight-bold">'.date_format($row["updated_at"], 'd/m/Y').'</span><br><span>'.date_format($row["updated_at"], 'H:i A').'</span>';
                        return $created_on;
                    })
                    ->addColumn('custom-status', function($row){
                        $custom_status = '<span class="cell-box faq-'.strtolower($row["status"]).'">'.ucfirst($row["status"]).'</span>';
                        return $custom_status;
                    })
                    ->rawColumns(['actions', 'custom-status', 'updated-on'])
                    ->make(true);
                    
        }

        return view('admin.frontend.section.step.index');
    }


    /**
     * Show appearance settings page
     *
     * @return \Illuminate\Http\Response
     */
    public function showTools(Request $request)
    {
        if ($request->ajax()) {
            $data = FrontendTool::all();
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('actions', function($row){
                        $actionBtn = '<div>                                            
                                            <a href="'. route("admin.settings.tool.edit", $row["id"] ). '"><i class="fa-solid fa-pencil-square table-action-buttons edit-action-button" title="'. __('Edit Tool') .'"></i></a>
                                            <a class="deleteButton" id="'. $row["id"] .'" href="#"><i class="fa-solid fa-trash-xmark table-action-buttons delete-action-button" title="'. __('Delete Tool') .'"></i></a>
                                        </div>';
                        return $actionBtn;
                    })
                    ->addColumn('custom-image', function($row){
                        $custom_status = '<div class="widget-banner-image overflow-hidden"><img alt="Banner" src="' . theme_url($row['image']) . '"></div>';
                        return $custom_status;
                    })
                    ->addColumn('custom-status', function($row){
                        $status = ($row['status']) ? 'active' : 'deactive';
                        $custom_status = '<span class="cell-box status-'.$status.'">'.ucfirst($status).'</span>';
                        return $custom_status;
                    })
                    ->rawColumns(['actions', 'custom-status', 'custom-image'])
                    ->make(true);
                    
        }

        return view('admin.frontend.section.tool.index');
    }


    /**
     * Show appearance settings page
     *
     * @return \Illuminate\Http\Response
     */
    public function showCases(Request $request)
    {
        if ($request->ajax()) {
            $data = FrontendCase::all();
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('actions', function($row){
                        $actionBtn = '<div>                                            
                                            <a href="'. route("admin.settings.case.edit", $row["id"] ). '"><i class="fa-solid fa-pencil-square table-action-buttons edit-action-button" title="'. __('Edit Tool') .'"></i></a>
                                            <a class="deleteButton" id="'. $row["id"] .'" href="#"><i class="fa-solid fa-trash-xmark table-action-buttons delete-action-button" title="'. __('Delete Tool') .'"></i></a>
                                        </div>';
                        return $actionBtn;
                    })
                    ->addColumn('custom-status', function($row){
                        $status = ($row['status']) ? 'active' : 'deactive';
                        $custom_status = '<span class="cell-box status-'.$status.'">'.ucfirst($status).'</span>';
                        return $custom_status;
                    })
                    ->rawColumns(['actions', 'custom-status'])
                    ->make(true);
                    
        }

        return view('admin.frontend.section.case.index');
    }


    /**
     * Show appearance settings page
     *
     * @return \Illuminate\Http\Response
     */
    public function showFeatures(Request $request)
    {
        if ($request->ajax()) {
            $data = FrontendFeature::all();
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('actions', function($row){
                        $actionBtn = '<div>                                            
                                            <a href="'. route("admin.settings.feature.edit", $row["id"] ). '"><i class="fa-solid fa-pencil-square table-action-buttons edit-action-button" title="'. __('Edit Feature') .'"></i></a>
                                            <a class="deleteButton" id="'. $row["id"] .'" href="#"><i class="fa-solid fa-trash-xmark table-action-buttons delete-action-button" title="'. __('Delete Feature') .'"></i></a>
                                        </div>';
                        return $actionBtn;
                    })
                    ->addColumn('custom-image', function($row){
                        $custom_status = '<div class="widget-banner-image overflow-hidden"><img alt="Banner" src="' . theme_url($row['image']) . '"></div>';
                        return $custom_status;
                    })
                    ->addColumn('custom-status', function($row){
                        $status = ($row['status']) ? 'active' : 'deactive';
                        $custom_status = '<span class="cell-box status-'.$status.'">'.ucfirst($status).'</span>';
                        return $custom_status;
                    })
                    ->rawColumns(['actions', 'custom-status', 'custom-image'])
                    ->make(true);
                    
        }

        return view('admin.frontend.section.feature.index');
    }


     /**
     * Show appearance settings page
     *
     * @return \Illuminate\Http\Response
     */
    public function showClients(Request $request)
    {
        if ($request->ajax()) {
            $data = FrontendClient::all();
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('actions', function($row){
                        $actionBtn = '<div>                                            
                                            <a class="deleteButton" id="'. $row["id"] .'" href="#"><i class="fa-solid fa-trash-xmark table-action-buttons delete-action-button" title="'. __('Delete Client') .'"></i></a>
                                        </div>';
                        return $actionBtn;
                    })
                    ->addColumn('custom-image', function($row){
                        $custom_status = '<div class="widget-banner-image overflow-hidden"><img alt="Banner" src="' . asset($row['url']) . '"></div>';
                        return $custom_status;
                    })
                    ->rawColumns(['actions','custom-image'])
                    ->make(true);
                    
        }

        return view('admin.frontend.section.client.index');
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createSteps()
    {
        return view('admin.frontend.section.step.create');
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createTools()
    {
        return view('admin.frontend.section.tool.create');
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createCases()
    {
        return view('admin.frontend.section.case.create');
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createFeatures()
    {
        return view('admin.frontend.section.feature.create');
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function createClients()
    {
        return view('admin.frontend.section.client.create');
    }


    /**
     * Store in database
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeSteps(Request $request)
    {
        request()->validate([
            'title' => 'required',
            'order' => 'required',
            'description' => 'required',
        ]);      

        FrontendStep::create([
            'title' => $request->title,
            'order' => $request->order,
            'description' => $request->description,
        ]);

        toastr()->success(__('Step successfully created'));
        return redirect()->route('admin.settings.step');
    }


     /**
     * Update the specified tool
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function storeTools(Request $request)
    {   
        request()->validate([
            'title' => 'required',
            'description' => 'required',
        ]);      

        $status = (request('activate') == 'on') ? true : false;

        $code = 'T' . strtoupper(Str::random(5));

        $tool = new FrontendTool([
            'tool_code' => $code,
            'tool_name' => request('name'),
            'title_meta' => request('subname'),
            'status' => $status,
            'title' => request('title'),
            'description' => request('description'),
            'image_footer' => request('footer'),
        ]); 

        $tool->save();

        if (request()->has('logo')) {
        
            try {
                request()->validate([
                    'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:10048'
                ]);
                
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'PHP FileInfo is disabled: ' . $e->getMessage());
            }
            
            $image = request()->file('logo');

            $name = Str::random(5);

          
            $filePath = '/img/frontend/features/' . $name . '.' . $image->getClientOriginalExtension();
            
            $this->uploadImage($image, 'img/frontend/features/', 'public', $name);
            
            $tool->image = $filePath;
            $tool->save();
        }

        toastr()->success(__('AI Tool has been successfully created'));
        return redirect()->route('admin.settings.tool');     
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeCases(Request $request)
    {   
        $status = (request('activate') == 'on') ? true : false;

        $feature = new FrontendCase([
            'title' => request('title'),
            'status' => $status,
            'description' => request('description'),
            'icon' => request('icon'),
        ]); 

        $feature->save();

        toastr()->success(__('Use case been successfully created'));
        return redirect()->route('admin.settings.case');     
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeFeatures(Request $request)
    {   
        $status = (request('activate') == 'on') ? true : false;

        $feature = new FrontendFeature([
            'title' => request('title'),
            'status' => $status,
            'description' => request('description'),
        ]); 

        $feature->save();

        if (request()->has('logo')) {
        
            try {
                request()->validate([
                    'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:10048'
                ]);
                
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'PHP FileInfo is disabled: ' . $e->getMessage());
            }
            
            $image = request()->file('logo');

            $name = Str::random(5);

          
            $filePath = '/img/frontend/features/' . $name . '.' . $image->getClientOriginalExtension();
            
            $this->uploadImage($image, 'img/frontend/features/', 'public', $name);
            
            $feature->image = $filePath;
            $feature->save();
        }

        toastr()->success(__('Feature been successfully created'));
        return redirect()->route('admin.settings.feature');     
    }


     /**
     * Update the specified tool
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function storeClients(Request $request)
    {   

        if (request()->has('logo')) {
        
            try {
                request()->validate([
                    'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:10048'
                ]);
                
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'PHP FileInfo is disabled: ' . $e->getMessage());
            }
            
            $image = request()->file('logo');

            $name = Str::random(10);

          
            $filePath = '/img/clients/' . $name . '.' . $image->getClientOriginalExtension();
            
            $this->uploadImage($image, 'img/clients/', 'public', $name);
            
            FrontendClient::create([
                'url' => $filePath,
            ]);

        }

        toastr()->success(__('Client has been successfully created'));
        return redirect()->route('admin.settings.client');     
    }


    /**
     * Edit form.
     *
     * @return \Illuminate\Http\Response
     */
    public function editSteps(FrontendStep $id)
    {
        return view('admin.frontend.section.step.edit', compact('id'));
    }


     /**
     * Edit form.
     *
     * @return \Illuminate\Http\Response
     */
    public function editTools(FrontendTool $id)
    {
        return view('admin.frontend.section.tool.edit', compact('id'));
    }


     /**
     * Edit form.
     *
     * @return \Illuminate\Http\Response
     */
    public function editFeatures(FrontendFeature $id)
    {
        return view('admin.frontend.section.feature.edit', compact('id'));
    }


    /**
     * Edit form.
     *
     * @return \Illuminate\Http\Response
     */
    public function editCases(FrontendCase $id)
    {
        return view('admin.frontend.section.case.edit', compact('id'));
    }


    /**
     * Update properly in database
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateSteps(Request $request, $id)
    {
        request()->validate([
            'title' => 'required',
            'order' => 'required',
            'description' => 'required',
        ]);

        $blog = FrontendStep::where('id', $id)->firstOrFail();
        $blog->title = request('title');
        $blog->order = request('order');
        $blog->description = request('description');
        $blog->save();    

        toastr()->success(__('Step successfully updated'));
        return redirect()->route('admin.settings.step');
    }


    /**
     * Update the specified tool
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateTools(Request $request, $id)
    {   
        $tool = FrontendTool::where('id', $id)->first();

        $status = (request('activate') == 'on') ? true : false;

        $tool->update([
            'status' => $status,
            'title' => request('title'),
            'description' => request('description'),
            'image_footer' => request('footer'),
        ]);

        if (request()->has('logo')) {
        
            try {
                request()->validate([
                    'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:10048'
                ]);
                
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'PHP FileInfo is disabled: ' . $e->getMessage());
            }
            
            $image = request()->file('logo');

            $name = Str::random(5);

          
            $filePath = '/img/frontend/features/' . $name . '.' . $image->getClientOriginalExtension();
            
            $this->uploadImage($image, 'img/frontend/features/', 'public', $name);
            
            $tool->image = $filePath;
            $tool->save();
        }

        toastr()->success(__('Tool has been successfully updated'));
        return redirect()->route('admin.settings.tool');     
    }


     /**
     * Update the specified tool
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateFeatures(Request $request, $id)
    {   
        $tool = FrontendFeature::where('id', $id)->first();

        $status = (request('activate') == 'on') ? true : false;

        $tool->update([
            'status' => $status,
            'title' => request('title'),
            'description' => request('description'),
        ]);

        if (request()->has('logo')) {
        
            try {
                request()->validate([
                    'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:10048'
                ]);
                
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'PHP FileInfo is disabled: ' . $e->getMessage());
            }
            
            $image = request()->file('logo');

            $name = Str::random(5);

          
            $filePath = '/img/frontend/features/' . $name . '.' . $image->getClientOriginalExtension();
            
            $this->uploadImage($image, 'img/frontend/features/', 'public', $name);
            
            $tool->image = $filePath;
            $tool->save();
        }

        toastr()->success(__('Feature has been successfully updated'));
        return redirect()->route('admin.settings.feature');     
    }


     /**
     * Update the specified tool
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateCases(Request $request, $id)
    {   
        $tool = FrontendCase::where('id', $id)->first();

        $status = (request('activate') == 'on') ? true : false;

        $tool->update([
            'status' => $status,
            'title' => request('title'),
            'description' => request('description'),
            'icon' => request('icon'),
        ]);


        toastr()->success(__('Use case has been successfully updated'));
        return redirect()->route('admin.settings.case');     
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteSteps(Request $request)
    {  
        if ($request->ajax()) {

            $result = FrontendStep::find(request('id'));

            if($result) {

                $result->delete();

                return response()->json('success');

            } else{
                return response()->json('error');
            } 
        }      
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteTools(Request $request)
    {  
        if ($request->ajax()) {

            $result = FrontendTool::find(request('id'));

            if($result) {

                $result->delete();

                return response()->json('success');

            } else{
                return response()->json('error');
            } 
        }      
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteFeatures(Request $request)
    {  
        if ($request->ajax()) {

            $result = FrontendFeature::find(request('id'));

            if($result) {

                $result->delete();

                return response()->json('success');

            } else{
                return response()->json('error');
            } 
        }      
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteCases(Request $request)
    {  
        if ($request->ajax()) {

            $result = FrontendCase::find(request('id'));

            if($result) {

                $result->delete();

                return response()->json('success');

            } else{
                return response()->json('error');
            } 
        }      
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteClients(Request $request)
    {  
        if ($request->ajax()) {

            $result = FrontendClient::find(request('id'));

            if($result) {

                $result->delete();

                return response()->json('success');

            } else{
                return response()->json('error');
            } 
        }      
    }


    /**
     * Upload voice avatar image
     */
    public function uploadImage(UploadedFile $file, $folder = null, $disk = 'public', $filename = null)
    {
        $name = !is_null($filename) ? $filename : Str::random(25);

        $image = $file->storeAs($folder, $name .'.'. $file->getClientOriginalExtension(), $disk);

        return $image;
    }

}
