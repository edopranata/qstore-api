<?php

namespace App\Http\Controllers\Api\DeliveryOrder;

use App\Http\Controllers\Controller;
use App\Http\Resources\Data\Customer\CustomerCollection;
use App\Http\Resources\Data\Customer\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    private string $type = 'collector';
    public function index(Request $request): CustomerCollection
    {
        $query = Customer::query()
            ->where('type', $this->type)
            ->when($request->get('name'), function ($query, $search) {
                return $query->where('name', 'LIKE', "%$search%");
            })
            ->when($request->get('type'), function ($query, $search) {
                return $query->where('type', 'LIKE', "%$search%");
            })
            ->when($request->get('phone'), function ($query, $search) {
                return $query->where('phone', 'LIKE', "%$search%");
            })
            ->when($request->get('address'), function ($query, $search) {
                return $query->where('address', 'LIKE', "%$search%");
            })
            ->when($request->get('user'), function ($query, $search) {
                return $query->whereRelation("user", "name", "like", "%$search%");
            })
            ->when($request->get('sortBy'), function ($query, $sort) {
                $sortBy = collect(json_decode($sort));
                return $query->orderBy($sortBy['key'], $sortBy['order']);
            });

        $data = $request->get('limit', 0) > 0 ? $query->paginate($request->get('limit', 10)) : $query->get();

        return new CustomerCollection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->only([
                'name',
                'phone',
            ]), [
                'name' => 'required|string|min:3|max:30',
                'phone' => 'required|string|max:20',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            $customer = Customer::query()
                ->create([
                    'type' => $this->type,
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'distance' => $request->distance,
                    'user_id' => auth()->id()
                ]);

            DB::commit();

            return new CustomerResource($customer->load('user'));

        } catch (\Exception $exception) {
            DB::rollBack();
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->only([
                'name',
                'phone',
            ]), [
                'name' => 'required|string|min:3|max:30',
                'phone' => 'required|string|max:20|unique:customers,phone,' . $request->id,
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'errors' => $validator->errors()->toArray()], 422);
            }

            $customer->update([
                'type' => $this->type,
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'distance' => $request->distance,
                'user_id' => auth()->id()
            ]);

            DB::commit();

            return new CustomerResource($customer->load('user'));

        } catch (\Exception $exception) {
            DB::rollBack();
            abort(403, $exception->getCode() . ' ' . $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer, Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $customers = $request->customer_id;
            if (is_array($customers)) {
                Customer::query()
                    ->whereIn('id', $request->customer_id)->delete();
            } else {
                $customer->delete();
            }

            DB::commit();
            return response()->json(['status' => true], 201);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'error' => [
                    'code' => $exception->getCode(),
                    'massage' => $exception->getMessage()
                ]
            ], 301);
        }
    }
}
