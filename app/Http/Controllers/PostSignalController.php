<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostSignalResource;
use App\Models\FavouriteSignal;
use App\Models\PostSignal;
use Illuminate\Http\Request;
use App\Validations\FBFXValidations;
use App\Traits\{ValidationTrait};
use Illuminate\Support\Facades\Auth;

class PostSignalController extends Controller
{

    public $successStatus = 200;
    use  ValidationTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 10);
            $search = $request->query('search', null);

            $postSignal = PostSignal::where('closed', '=', 'no');

            if ($search) {
                $postSignal->where('currency_pair', 'LIKE', '%' . $search . '%');
            }

            $postSignal = $this->filter($request, $postSignal);

            $count = $postSignal->count();
            $data = $postSignal->orderBy('id', 'DESC')->paginate($limit, ['*'], 'page', $page);

            $collection = PostSignalResource::collection($data);
            $response = [
                'totalCount' => $count,
                'post_signals' => $collection,
            ];
            return sendResponse(200, 'Data fetching successfully!', $response);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }




    public function getFavourite(Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 10);
            $search = $request->query('search', null);
            $user = Auth::user();
            $favSignalIds = $user->favouriteSignals()->pluck('post_signal_id');
            $postSignal = PostSignal::whereIn('id', $favSignalIds)->where('closed', '=', 'no');

            if ($search) {
                $postSignal->where('currency_pair', 'LIKE', '%' . $search . '%');
            }

            $postSignal = $this->filter($request, $postSignal);

            $count = $postSignal->count();
            $data = $postSignal->orderBy('id', 'DESC')->paginate($limit, ['*'], 'page', $page);

            $collection = PostSignalResource::collection($data);
            $response = [
                'totalCount' => $count,
                'post_signals' => $collection,
            ];
            return sendResponse(200, 'Data fetching successfully!', $response);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }
    public function history(Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $limit = $request->query('limit', 10);
            $search = $request->query('search', null);

            $postSignal = PostSignal::where('closed', '=', 'yes');

            if ($search) {
                $postSignal->where('currency_pair', 'LIKE', '%' . $search . '%');
            }

            $postSignal = $this->filter($request, $postSignal);

            $count = $postSignal->count();
            $data = $postSignal->orderBy('id', 'DESC')->paginate($limit, ['*'], 'page', $page);

            $collection = PostSignalResource::collection($data);
            $response = [
                'totalCount' => $count,
                'post_signals' => $collection,
            ];
            return sendResponse(200, 'Data fetching successfully!', $response);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function filter($request, $postSignal)
    {

        // Filter by action
        if ($request->has('Buy') || $request->has('Sell') || $request->has('All')) {
            $actions = [];
            if ($request->has('Buy')) {
                $actions[] = 'Buy';
            }
            if ($request->has('Sell')) {
                $actions[] = 'Sell';
            }
            if ($request->has('All')) {
                $actions = ['Buy', 'Sell'];
            }
            $postSignal->whereIn('action', $actions);
        }

        // Filter by currency pairs
        $currencyPairs = array_filter($request->only(['EURUSD', 'GBPUSD', 'USDJPY', 'USDCAD', 'USDCHF', 'AUDUSD', 'NZDUSD', 'EURJPY', 'GBPJPY', 'XAUUSD', 'CrudeOil', 'XAGUSD', 'BTCUSD', 'ETHUSD', 'BNBUSD', 'ADAUSD', 'XRPUSD', 'US30', 'SP500', 'DXY']));
        if (!empty($currencyPairs)) {
            $postSignal->whereIn('currency_pair', array_keys($currencyPairs));
        }

        // Timestamp-based filtering


        if ($request->has('Today') || $request->has('Yesterday') || $request->has('LastWeek')) {
            if ($request->has('Today')) {
                $postSignal->whereDate('created_at', '=', now()->toDateString());
            }
            if ($request->has('Yesterday')) {
                $postSignal->whereDate('created_at', '=', now()->subDay()->toDateString());
            }
            if ($request->has('LastWeek')) {
                $startLastWeek = now()->startOfWeek()->subWeek();
                $endLastWeek = now()->endOfWeek()->subWeek();
                $postSignal->whereBetween('created_at', [$startLastWeek, $endLastWeek]);
            }
        }

        return $postSignal;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatorResult = $this->checkValidations(FBFXValidations::validatePostSignal($request));
            if ($validatorResult) return $validatorResult;
            $input = $request->all();

            //after optimization

            $input['user_id'] = Auth::user()->id;
            $input['close_price'] = '0';
            $input['role'] = "0";

            $postSignal = PostSignal::updateOrCreate(['id' => isset($input['id']) ? $input['id'] : null], $input);

            $collection = new PostSignalResource($postSignal);
            return sendResponse(200, 'Signal created successfully!', $collection);

            //  before  optimized
            // $post_signal = new PostSignal;
            // if (isset($input['id']))
            //     $post_signal = PostSignal::where('id', $input['id'])->first();

            // $post_signal->currency_pair = $input['currency_pair'];
            // $post_signal->action = $input['action'];
            // $post_signal->stop_loss = $input['stop_loss'];
            // $post_signal->profit_one = $input['profit_one'];
            // $post_signal->profit_two = $input['profit_two'];
            // $post_signal->profit_three = $input['profit_three'];
            // $post_signal->RRR = $input['RRR'];
            // $post_signal->timeframe = $input['timeframe'];
            // $post_signal->user_id = Auth::user()->id;
            // $post_signal->open_price = $input['open_price'];
            // $post_signal->close_price = '0';
            // $post_signal->role = "0";
            // $post_signal->type = $input['type'];
            // $post_signal->save();
            // $collection = new PostSignalResource($post_signal);
            // return sendResponse(200, 'Create signal successfully!', $collection);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PostSignal $postSignal)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PostSignal $postSignal, $id)
    {
        try {
            $postSignal = PostSignal::find($id);
            if (!$postSignal)
                return sendResponse(202, 'Data does not exists!', (object)[]);

            $collection = new PostSignalResource($postSignal);
            return sendResponse(200, 'Data fetching successfully!', $collection);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function addFavourite($id)
    {
        try {
            $postSignal = PostSignal::find($id);
            if (!$postSignal)
                return sendResponse(202, 'Data does not exists!', (object)[]);

            $favSignal = FavouriteSignal::where(['user_id' => Auth::user()->id, 'post_signal_id' => $id])->first();
            if (!$favSignal) {
                $addFavSignal = new FavouriteSignal();
                $addFavSignal->user_id = Auth::user()->id;
                $addFavSignal->post_signal_id = $id;
                $addFavSignal->save();
                $message = 'Add post signal in favourite!';
            } else {
                $favSignal->delete();
                $message = 'Remove post signal from favourite!';
            }
            $collection = new PostSignalResource($postSignal);
            return sendResponse(200, $message, $collection);
        } catch (\Throwable $th) {
            $response = sendResponse(500, $th->getMessage(), (object)[]);
            return $response;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PostSignal $postSignal)
    {
        //
    }
}
