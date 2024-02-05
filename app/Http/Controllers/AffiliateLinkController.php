<?php

namespace App\Http\Controllers;

use App\Http\Resources\AffiliateResource;
use App\Models\AffiliateLink;
use Illuminate\Http\Request;
use App\Validations\FBFXValidations;
use App\Traits\{ValidationTrait};

class AffiliateLinkController extends Controller
{
    public $successStatus = 200;
    use  ValidationTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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

            $validatorResult = $this->checkValidations(FBFXValidations::validateAffiliateLink($request));
            if ($validatorResult) return $validatorResult;
            $input = $request->all();
            $input['id'] = 1;
            $affiliateLink = AffiliateLink::updateOrCreate(['id' => isset($input['id']) ? $input['id'] : null], $input);
            $collection = new AffiliateResource($affiliateLink);
            if (isset($input['id']))
                return sendResponse(200, 'Affiliate Link updated successfully!', $collection);
            return sendResponse(200, 'Affiliate Link created successfully!', $collection);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(AffiliateLink $affiliateLink)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AffiliateLink $affiliateLink, $id)
    {
        try {
            $affiliateLink = AffiliateLink::find($id);
            if (!$affiliateLink)
                return sendResponse(202, 'Affiliate Link does not exists!', (object)[]);

            $collection = new AffiliateResource($affiliateLink);
            return sendResponse(200, 'Affiliate Link feteched successfully!', $collection);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AffiliateLink $affiliateLink)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AffiliateLink $affiliateLink)
    {
        //
    }
}
