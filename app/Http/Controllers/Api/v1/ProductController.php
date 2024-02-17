<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     *  @OA\Post(
     *      path="/api/v1/upload-image",
     *      tags={"Product"},
     *      summary="Update Product Image",
     *      operationId="Update Product Image",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="product_image",
     *                      description="Upload Product image",
     *                      type="file"
     *                   ),
     *               ),
     *           ),
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
     *     security={
     *      {"bearerAuth":{}}
     *     }
     * )
     */

    public function uploadImage(Request $request)
    {
        try {
            $rules = [
                'product_image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ];
            $message = [
                'product_image.required' => 'Product image is required',
                'product_image.image' => 'Product image must be an image',
                'product_image.mimes' => 'Product image must be a file of type: jpeg, png, jpg, gif, svg',
                'product_image.max' => 'Product image may not be greater than 2048 kilobytes',
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
            $image = $request->file('product_image');
            $imageName = time() . '.' . $image->extension();
            $image->move(public_path('images'), $imageName);
            $data = [
                'status_code' => 200,
                'message' => 'Image uploaded successfully',
                'data' => ['image_name' => $imageName]
            ];
            return $this->sendJsonResponse($data);
        } catch (\Exception $e) {
            Log::error(['method' => __METHOD__, 'error' => ['file' => $e->getFile(), 'line' => $e->getLine(), 'message' => $e->getMessage()], 'created_at' => date("Y-m-d H:i:s")]);
            return $this->sendJsonResponse(array('status_code' => 500, 'message' => 'Error : ' . $e->getMessage() . $e->getFile()));
        }
    }
    /**
     * @OA\Post(
     *     path="/api/v1/create-product",
     *     summary="Create Product",
     *     tags={"Product"},
     *     description="Create Product",
     *     operationId="createProduct",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         example="32 Inch TV",
     *         description="Enter Product Name",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         example="Lorem Ipsum is simply dummy text of the printing and typesetting industry.",
     *         description="Enter Product Description",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sku",
     *         in="query",
     *         example="TV32MI",
     *         description="Enter Product SKU",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="price",
     *         in="query",
     *         example="1000.00",
     *         description="Enter Product Price",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="categories",
     *         in="query",
     *         example="category1,category2",
     *         description="Enter Product Categories",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="images",
     *         in="query",
     *         example="image1,image2",
     *         description="Enter Product Images",
     *         required=false,
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
    public function createProduct(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|regex:/^[a-zA-Z0-9\s]+$/u|unique:product,name',
                'description' => 'required',
                'sku' => 'required|regex:/^[a-zA-Z0-9\s]+$/u|unique:product,sku',
                'price' => 'required|gt:0',
            ];
            $message = [
                'name.required' => 'Product name is required',
                'name.regex' => 'Product name must be alphanumeric',
                'name.unique' => 'Product name already exists',
                'description.required' => 'Product description is required',
                'sku.required' => 'Product sku is required',
                'sku.regex' => 'Product sku must be alphanumeric',
                'sku.unique' => 'Product sku already exists',
                'price.required' => 'Product price is required',
                'price.gt' => 'Product price must be greater than 0',
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
            $companyId = Company::first();
            if (empty($companyId)) {
                $data = [
                    'status_code' => 400,
                    'message' => 'Company does not exist, Please run php artisan db:seed --class=CompanySeeder command in terminal',
                    'data' => ""
                ];
                return $this->sendJsonResponse($data);
            }
            if (!empty($request->categories)) {
                $categories = explode(',', $request->categories);
                foreach ($categories as $category) {
                    $categoryExist = Category::where('name', trim($category))
                        ->where('status', 'Active')
                        ->first();
                    if (empty($categoryExist)) {
                        $data = [
                            'status_code' => 400,
                            'message' => 'Added Category does not exist, Please add valid category',
                            'data' => ""
                        ];
                        return $this->sendJsonResponse($data);
                    }
                }
            }
            $formatedPrice =  number_format($request->price, 2);
            $price = str_replace(',', '', $formatedPrice);
            $product = new Product();
            $product->company_id = $companyId->id;
            $product->user_id = auth()->user()->id;
            $product->name = trim($request->name);
            $product->description = $request->description;
            $product->sku = trim($request->sku);
            $product->price = $price;
            $product->save();
            if (!empty($request->categories)) {
                $categories = explode(',', $request->categories);
                foreach ($categories as $category) {
                    $productCategory = new ProductCategory();
                    $productCategory->product_id = $product->id;
                    $productCategory->category_id = Category::where('name', trim($category))->first()->id;
                    $productCategory->category_name = trim($category);
                    $productCategory->save();
                }
            }
            if (!empty($request->images)) {
                $images = explode(',', $request->images);
                foreach ($images as $image) {
                    $productImage = new ProductImages();
                    $productImage->product_id = $product->id;
                    $productImage->image = trim($image);
                    $productImage->save();
                }
            }
            $data = [
                'status_code' => 200,
                'message' => 'Product created successfully',
                'data' => [
                    'Product' => $product
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
     *     path="/api/v1/edit-product",
     *     summary="Edit Product",
     *     tags={"Product"},
     *     description="Edit Product",
     *     operationId="editProduct",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         example="1",
     *         description="Enter Product Id",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         example="32 Inch TV",
     *         description="Enter Product Name",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="description",
     *         in="query",
     *         example="Lorem Ipsum is simply dummy text of the printing and typesetting industry.",
     *         description="Enter Product Description",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sku",
     *         in="query",
     *         example="TV32MI",
     *         description="Enter Product SKU",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="price",
     *         in="query",
     *         example="1000.00",
     *         description="Enter Product Price",
     *         required=true,
     *         @OA\Schema(
     *             type="number",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="categories",
     *         in="query",
     *         example="category1,category2",
     *         description="Enter Product Categories",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="images",
     *         in="query",
     *         example="image1,image2",
     *         description="Enter Product Images",
     *         required=false,
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
    public function editProduct(Request $request)
    {
        try {
            $rules = [
                'id' => 'required|exists:product,id',
                'name' => [
                    'required',
                    'string',
                    Rule::unique('product', 'name')->ignore($request->id)->where(function ($query) {
                        $query->whereNull('deleted_at');
                    })
                ],
                'description' => 'required',
                'sku' => [
                    'required',
                    'string',
                    Rule::unique('product', 'sku')->ignore($request->id)->where(function ($query) {
                        $query->whereNull('deleted_at');
                    })
                ],
                'price' => 'required|gt:0',
            ];
            $message = [
                'id.required' => 'Product id is required',
                'id.exists' => 'Product id does not exist',
                'name.required' => 'Product name is required',
                'name.string' => 'Product name must be a string',
                'name.exists' => 'Product name already exists',
                'description.required' => 'Product description is required',
                'sku.required' => 'Product sku is required',
                'sku.string' => 'Product sku must be a string',
                'sku.exists' => 'Product sku already exists',
                'price.required' => 'Product price is required',
                'price.gt' => 'Product price must be greater than 0',
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
            $companyId = Company::first();
            if (empty($companyId)) {
                $data = [
                    'status_code' => 400,
                    'message' => 'Company does not exist, Please run php artisan db:seed --class=CompanySeeder command in terminal',
                    'data' => ""
                ];
                return $this->sendJsonResponse($data);
            }
            if (!empty($request->categories)) {
                $categories = explode(',', $request->categories);
                foreach ($categories as $category) {
                    $categoryExist = Category::where('name', trim($category))
                        ->where('status', 'Active')
                        ->first();
                    if (empty($categoryExist)) {
                        $data = [
                            'status_code' => 400,
                            'message' => 'Added Category does not exist, Please add valid category',
                            'data' => ""
                        ];
                        return $this->sendJsonResponse($data);
                    }
                }
            }
            $formatedPrice =  number_format($request->price, 2);
            $price = str_replace(',', '', $formatedPrice);
            $product = Product::find($request->id);
            $product->company_id = $companyId->id;
            $product->user_id = auth()->user()->id;
            $product->name = trim($request->name);
            $product->description = $request->description;
            $product->sku = trim($request->sku);
            $product->price = $price;
            $product->save();
            if (!empty($request->categories)) {
                ProductCategory::where('product_id', $product->id)->forceDelete();
                $categories = explode(',', $request->categories);
                foreach ($categories as $category) {
                    $productCategory = new ProductCategory();
                    $productCategory->product_id = $product->id;
                    $productCategory->category_id = Category::where('name', trim($category))->first()->id;
                    $productCategory->category_name = trim($category);
                    $productCategory->save();
                }
            }
            if (!empty($request->images)) {
                ProductImages::where('product_id', $product->id)->forceDelete();
                $images = explode(',', $request->images);
                foreach ($images as $image) {
                    $productImage = new ProductImages();
                    $productImage->product_id = $product->id;
                    $productImage->image = trim($image);
                    $productImage->save();
                }
            }
            $data = [
                'status_code' => 200,
                'message' => 'Product Updated successfully',
                'data' => [
                    'Product' => $product
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
     *     path="/api/v1/product-list",
     *     summary="Product List",
     *     tags={"Product"},
     *     description="Product List",
     *     operationId="productList",
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
     *          description="search by Product name, sku",
     *          @OA\Schema(
     *          type="string",
     *         ),
     *       ),
     *      @OA\Parameter(
     *          name="name",
     *          required=false,
     *          in="query",
     *          example="",
     *          description="Product Name",
     *          @OA\Schema(
     *          type="string",
     *         ),
     *       ),
     *      @OA\Parameter(
     *          name="category",
     *          required=false,
     *          in="query",
     *          example="",
     *          description="Category Name",
     *          @OA\Schema(
     *          type="string",
     *         ),
     *       ),
     *      @OA\Parameter(
     *          name="price_range",
     *          required=false,
     *          in="query",
     *          example="0-10000",
     *          description="Price Range",
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
    public function productList(Request $request){
        try {
            $start = $request->start ?? 0;
            $limit = $request->limit ?? 10;
            $search = $request->search ?? '';
            $name = $request->name ?? '';
            $category = $request->category ?? '';
            $priceRange = $request->price_range ?? '';
            $sortBy = $request->sort_by ?? 'id';
            $sortOrder = $request->sort_order ?? 'desc';
            $totalCount = Product::where('user_id', auth()->user()->id)
                ->count();
            $products = Product::with('productCategory', 'productImages')
                ->where('user_id', auth()->user()->id)
                ->where('name', 'like', '%' . $search . '%')
                ->where('sku', 'like', '%' . $search . '%')
                ->where('name', 'like', '%' . $name . '%')
                ->whereHas('productCategory', function ($query) use ($category) {
                    $query->where('category_name', 'like', '%' . $category . '%');
                })
                ->where(function ($query) use ($priceRange) {
                    if (!empty($priceRange)) {
                        $price = explode('-', $priceRange);
                        $query->where('price', '>=', $price[0]);
                        $query->where('price', '<=', $price[1]);
                    }
                })
                ->orderBy($sortBy, $sortOrder)
                ->skip($start)
                ->limit($limit)
                ->get();
            $totalFiltered = $products->count();
            $data = [
                'status_code' => 200,
                'message' => 'Product List',
                'data' => [
                    'Products' => $products,
                    'total_count' => $totalCount,
                    'total_filtered_count' => $totalFiltered
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
     *     path="/api/v1/view-product",
     *     summary="View Product",
     *     tags={"Product"},
     *     description="View Product",
     *     operationId="viewProduct",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         example="1",
     *         description="Enter Product Id",
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
    public function viewProduct(Request $request){
        try{
            $rules = [
                'id' => 'required|exists:product,id'
            ];
            $message = [
                'id.required' => 'Product id is required',
                'id.exists' => 'Product id does not exist',
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
            $product = Product::with('productCategory', 'productImages')
                ->where('id', $request->id)
                ->first();
            $data = [
                'status_code' => 200,
                'message' => 'Product Details',
                'data' => [
                    'Product' => $product
                ]
            ];
            return $this->sendJsonResponse($data);
        }catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
        }
    }
    /**
     * @OA\Post(
     *     path="/api/v1/delete-product",
     *     summary="Delete Product",
     *     tags={"Product"},
     *     description="Delete Product",
     *     operationId="deleteProduct",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         example="1",
     *         description="Enter Product Id",
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
    public function deleteProduct(Request $request){
        try{
            $rules = [
                'id' => 'required|exists:product,id'
            ];
            $message = [
                'id.required' => 'Product id is required',
                'id.exists' => 'Product id does not exist',
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
            $productImage = ProductImages::where('product_id', $request->id)->forceDelete();
            $productCategory = ProductCategory::where('product_id', $request->id)->forceDelete();
            $product = Product::find($request->id);
            $product->delete();
            $data = [
                'status_code' => 200,
                'message' => 'Product deleted successfully',
                'data' => [
                    'Product' => $product
                ]
            ];
            return $this->sendJsonResponse($data);
        }catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 500);
        }
    }
}
