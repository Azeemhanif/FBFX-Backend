<?php

namespace App\Http\Controllers;

use App\Http\Resources\AcademyResource;
use App\Models\Academy;
use Illuminate\Http\Request;
use App\Validations\FBFXValidations;
use App\Traits\{ValidationTrait};

class AcademyController extends Controller
{
    public $successStatus = 200;
    use  ValidationTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $validatorResult = $this->checkValidations(FBFXValidations::validateAcademy($request));
            if ($validatorResult) return $validatorResult;
            $input = $request->all();

            if ($request->hasFile('image')) {
                $folderPath = 'uploads/images/';
                $file = $request->file('image');
                $uploadImage = uploadImage($file, $folderPath);
                $input['image'] = $uploadImage;
            }

            $academy = Academy::updateOrCreate(['id' => isset($input['id']) ? $input['id'] : null], $input);
            $collection = new AcademyResource($academy);
            if (isset($input['id']))
                return sendResponse(200, 'Academy updated successfully!', $collection);

            return sendResponse(200, 'Academy created successfully!', $collection);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 10);
            $search = $request->query('search', null);
            $academy = Academy::query();
            if ($search) {
                $academy->where('title', 'LIKE', '%' . $search . '%');
            }
            $count = $academy->count();
            $data = $academy->orderBy('id', 'DESC')->paginate($limit, ['*'], 'page', $page);

            $collection =  AcademyResource::collection($data);
            $response = [
                'totalCount' => $count,
                'academies' => $collection,
            ];

            return sendResponse(200, 'Data feteched successfully!', $response);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Academy $academy, $id)
    {
        try {
            $academy = Academy::find($id);
            if (!$academy)
                return sendResponse(202, 'Data does not exists!', (object)[]);

            $collection = new AcademyResource($academy);
            return sendResponse(200, 'Data feteched successfully!', $collection);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Academy $academy)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Academy $academy, $id)
    {
        try {
            $academy = Academy::find($id);
            if (!$academy) return sendResponse(202, 'Academy does not exist', (object)[]);
            $academy->delete();
            return  sendResponse(200, 'Academy deleted successfully', (object)[]);
        } catch (\Exception $ex) {
            // DB::rollback();
            $response = sendResponse(500, $ex->getMessage(), (object)[]);
            return $response;
        }
    }
}
