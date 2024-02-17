<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/v1/create-category",
     *     summary="Create Category",
     *     tags={"Category Master"},
     *     description="Create Category",
     *     operationId="createCategory",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         example="Category 1",
     *         description="Enter Category Name",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="json schema",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invalid Request"
     *     ),
     * )
     */
    public function createCategory(Request $request){
        try {
            $rules = [
                'name' => 'required|string|unique:category,name',
            ];
            $message = [
                'name.required' => 'Category Name is required',
                'name.string' => 'Category Name must be string',
                'name.unique' => 'Category Name already exists',
            ];
            $validator = Validator::make($request->all(), $rules, $message);
            if ($validator->fails()) {
                $data = [
                    'status_code' => 400,
                    'message' => $validator->errors()->first(),
                    'data' => ""
                ];
                return $this->sendJsonResponse($data);
            }
            $category = new Category();
            $category->name = $request->name;
            $category->status = "Active";
            $category->save();
            $data = [
                'status_code' => 200,
                'message' => 'Category created successfully',
                'data' => $category
            ];
            return $this->sendJsonResponse($data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/edit-category",
     *     summary="Edit Category",
     *     tags={"Category Master"},
     *     description="Edit Category",
     *     operationId="editCategory",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         example="1",
     *         description="Enter Category Id",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         example="Home Appliances",
     *         description="Enter Category Name",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="json schema",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invalid Request"
     *     ),
     * )
     */
    public function editCategory(Request $request){
        try {
            $rules = [
                'id' => 'required|numeric|exists:category,id',
                'name' => 'required|string',
            ];
            $message = [
                'id.required' => 'Category Id is required',
                'id.numeric' => 'Category Id must be number',
                'id.exists' => 'Category Id does not exists',
                'name.required' => 'Category Name is required',
                'name.string' => 'Category Name must be string',
            ];
            $validator = Validator::make($request->all(), $rules, $message);
            if ($validator->fails()) {
                $data = [
                    'status_code' => 400,
                    'message' => $validator->errors()->first(),
                    'data' => ""
                ];
                return $this->sendJsonResponse($data);
            }
            $category = Category::find($request->id);
            $category->name = $request->name;
            $category->save();
            $data = [
                'status_code' => 200,
                'message' => 'Category Updated successfully',
                'data' => $category
            ];
            return $this->sendJsonResponse($data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
        }
    }
    /**
     * @OA\Post(
     *     path="/api/v1/category-list",
     *     summary="Category List",
     *     tags={"Category Master"},
     *     description="Category List",
     *     operationId="categoryList",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *          name="start",
     *          required=false,
     *          in="query",
     *          example="0",
     *          description="no of record you already get",
     *          @OA\Schema(
     *          type="string",
     *         ),
     *       ),
     *      @OA\Parameter(
     *          name="limit",
     *          required=false,
     *          in="query",
     *          example="10",
     *          description="no of record you want to get",
     *          @OA\Schema(
     *          type="string",
     *         ),
     *       ),
     *     @OA\Parameter(
     *          name="search",
     *          required=false,
     *          in="query",
     *          example="",
     *          description="search by category name",
     *          @OA\Schema(
     *          type="string",
     *         ),
     *       ),
     *      @OA\Parameter(
     *          name="name",
     *          required=false,
     *          in="query",
     *          example="",
     *          description="Category Name",
     *          @OA\Schema(
     *          type="string",
     *         ),
     *       ),
     *      @OA\Parameter(
     *          name="status",
     *          required=false,
     *          in="query",
     *          example="",
     *          description="Category Status (Active or Inactive)",
     *          @OA\Schema(
     *          type="string",
     *         ),
     *       ),
     *      @OA\Parameter(
     *          name="sort_by",
     *          required=false,
     *          in="query",
     *          example="",
     *          description="Sort by column (name)",
     *          @OA\Schema(
     *          type="string",
     *         ),
     *       ),
     *      @OA\Parameter(
     *          name="sort_order",
     *          required=false,
     *          in="query",
     *          example="",
     *          description="Sort order (asc or desc)",
     *          @OA\Schema(
     *          type="string",
     *         ),
     *       ),
     *      @OA\Response(
     *         response=200,
     *         description="json schema",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invalid Request"
     *     ),
     * )
     */
    public function categoryList(Request $request){
        try {
            $start = @$request->start ? $request->start : 0;
            $limit = @$request->limit ? $request->limit : 10;
            $search = @$request->search ? $request->search : '';
            $sort_by = @$request->sort_by ? $request->sort_by : 'id';
            $sort_order = @$request->sort_order ? $request->sort_order : 'asc';
            $name = @$request->name ? $request->name : '';
            $status = @$request->status ? $request->status : '';
            $totalCount = Category::count();
            $data = [
                'search' => $search,
                'name' => $name,
                'status' => $status,
            ];
            $query = Category::getQueryForList($data);
            $filterCount = $query->clone()->count();

            $category = $query->clone()->orderBy($sort_by, $sort_order)
                ->skip($start)
                ->limit($limit)
                ->get();
                $data = [
                    'status_code' => 200,
                    'message' => 'get record Successfully.',
                    'data' => [
                        'total_count' => $totalCount,
                        'filter_count' => $filterCount,
                        'Category' => $category,
                    ]
                ];
                return $this->sendJsonResponse($data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/view-category",
     *     summary="View Category",
     *     tags={"Category Master"},
     *     description="View Category",
     *     operationId="viewCategory",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         example="1",
     *         description="Enter Category Id",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *         )
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="json schema",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invalid Request"
     *     ),
     * )
     */
    public function viewCategory(Request $request){
        try {
            $rules = [
                'id' => 'required|numeric|exists:category,id',
            ];
            $message = [
                'id.required' => 'Category Id is required',
                'id.numeric' => 'Category Id must be number',
                'id.exists' => 'Category Id does not exists',
            ];
            $validator = Validator::make($request->all(), $rules, $message);
            if ($validator->fails()) {
                $data = [
                    'status_code' => 400,
                    'message' => $validator->errors()->first(),
                    'data' => ""
                ];
                return $this->sendJsonResponse($data);
            }
            $category = Category::find($request->id);
            $data = [
                'status_code' => 200,
                'message' => 'Category get successfully',
                'data' => $category
            ];
            return $this->sendJsonResponse($data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
        }
    }
    /**
     * @OA\Post(
     *     path="/api/v1/category-status-change",
     *     summary="Category Status Change",
     *     tags={"Category Master"},
     *     description="Category Status Change",
     *     operationId="categoryStatusChange",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         example="1",
     *         description="Enter Category Id",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         example="Inactive",
     *         description="Enter Status (Active or Inactive)",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="json schema",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invalid Request"
     *     ),
     * )
     */
    public function categoryStatusChange(Request $request){
        try {
            $rules = [
                'id' => 'required|numeric|exists:category,id',
                'status' => 'required|in:Active,Inactive',
            ];
            $message = [
                'id.required' => 'Category Id is required',
                'id.numeric' => 'Category Id must be number',
                'id.exists' => 'Category Id does not exists',
                'status.required' => 'Status is required',
                'status.in' => 'Status must be Active or Inactive',
            ];
            $validator = Validator::make($request->all(), $rules, $message);
            if ($validator->fails()) {
                $data = [
                    'status_code' => 400,
                    'message' => $validator->errors()->first(),
                    'data' => ""
                ];
                return $this->sendJsonResponse($data);
            }
            $category = Category::find($request->id);
            $category->status = $request->status;
            $category->save();
            $data = [
                'status_code' => 200,
                'message' => 'Category Status Change successfully',
                'data' => $category
            ];
            return $this->sendJsonResponse($data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
        }
    }
    /**
     * @OA\Post(
     *     path="/api/v1/category-delete",
     *     summary="Category Delete",
     *     tags={"Category Master"},
     *     description="Category Delete",
     *     operationId="categoryDelete",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         example="1",
     *         description="Enter Category Id",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *         )
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="json schema",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invalid Request"
     *     ),
     * )
     */
    public function categoryDelete(Request $request){
        try {
            $rules = [
                'id' => 'required|numeric|exists:category,id',
            ];
            $message = [
                'id.required' => 'Category Id is required',
                'id.numeric' => 'Category Id must be number',
                'id.exists' => 'Category Id does not exists',
            ];
            $validator = Validator::make($request->all(), $rules, $message);
            if ($validator->fails()) {
                $data = [
                    'status_code' => 400,
                    'message' => $validator->errors()->first(),
                    'data' => ""
                ];
                return $this->sendJsonResponse($data);
            }
            $category = Category::find($request->id);
            $category->delete();
            $data = [
                'status_code' => 200,
                'message' => 'Category Deleted successfully',
                'data' => $category
            ];
            return $this->sendJsonResponse($data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
        }
    }
}
