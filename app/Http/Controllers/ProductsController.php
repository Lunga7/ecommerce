<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Input;
use Auth;
use Session;
use Image;
use App\Category;
use App\Product;
use App\ProductsAttribute;

class ProductsController extends Controller
{
    //method to add products
    public function addProduct(Request $request)
    {

        if($request->isMethod('post'))
        {
            $data = $request->all();
        
            if(empty($data['category_id']))
            {
                return redirect()->back()->with('flash_message_error','Under Category is missing!');	
            }
            $product = new Product;
            $product->category_id = $data['category_id'];
            $product->product_name = $data['product_name'];
            $product->artist_name = $data['artist_name'];
            if(!empty($data['description'])){
                $product->description = $data['description'];
            }else{
                $product->description = '';    			
            }
            $product->price = $data['price'];

            // Upload Image
            if($request->hasFile('image'))
            {
                $image_tmp = $request->file('image');
                if($image_tmp->isValid()){
                    $extension = $image_tmp->getClientOriginalExtension();
                    $filename = rand(111,99999).'.'.$extension;
                    $large_image_path = 'images/backend_images/products/large/'.$filename;
                    $medium_image_path = 'images/backend_images/products/medium/'.$filename;
                    $small_image_path = 'images/backend_images/products/small/'.$filename;
                    // Resize Images
                    Image::make($image_tmp)->save($large_image_path);
                    Image::make($image_tmp)->resize(600,600)->save($medium_image_path);
                    Image::make($image_tmp)->resize(300,300)->save($small_image_path);

                    // Store image name in products table
                    $product->image = $filename;
                }
            }

            $product->save();
            /*return redirect()->back()->with('flash_message_success','Product has been added successfully!');*/
            return redirect('/admin/view-products')->with('flash_message_success','Product has been added successfully!');
        }

        $categories = Category::where(['parent_id'=>0])->get();
        $categories_dropdown = "<option value='' selected disabled>Select</option>";
        foreach($categories as $cat)
        {
            $categories_dropdown .= "<option value='".$cat->id."'>".$cat->name."</option>";
            $sub_categories = Category::where(['parent_id'=>$cat->id])->get();
            foreach ($sub_categories as $sub_cat) {
                $categories_dropdown .= "<option value = '".$sub_cat->id."'>&nbsp;--&nbsp;".$sub_cat->name."</option>";
            }
        }
        return view('admin.products.add_product')->with(compact('categories_dropdown'));
    }

    //view products method
    public function viewProducts()
    {
        $products = Product::get();
        $products = json_decode(json_encode($products));
        foreach($products as $key => $val){
            $category_name = Category::where(['id'=>$val->category_id])->first();
            $products[$key]->category_name = $category_name->name;
        }
        //echo "<pre>"; print_r($products); die;
        return view('admin.products.view_products')->with(compact('products'));
    }

    //edit products method
    public function editProduct(Request $request, $id = null)
    {
        if($request->isMethod('post'))
        {
            $data = $request->all();
            //echo "<pre>"; print_r($data); die;

            // Upload Image
            if($request->hasFile('image'))
            {
                $image_tmp = $request->file('image');
                if($image_tmp->isValid()){
                    $extension = $image_tmp->getClientOriginalExtension();
                    $filename = rand(111,99999).'.'.$extension;
                    $large_image_path = 'images/backend_images/products/large/'.$filename;
                    $medium_image_path = 'images/backend_images/products/medium/'.$filename;
                    $small_image_path = 'images/backend_images/products/small/'.$filename;
                    // Resize Images
                    Image::make($image_tmp)->save($large_image_path);
                    Image::make($image_tmp)->resize(600,600)->save($medium_image_path);
                    Image::make($image_tmp)->resize(300,300)->save($small_image_path);

                }
            }else
            {
                $filename = $data['current_image'];
            }

            //checking if description is empty
            if(empty($data['description']))
            {
                $data['description'] = '';
            }


            Product::where(['id'=>$id])->update(['category_id'=>$data['category_id'],'product_name'=>$data['product_name'],'artist_name'=>$data['artist_name'],'description'=>$data['description'],'price'=>$data['price'], 'image'=>$filename]);
            return redirect('/admin/view-products')->with('flash_message_success','Category updated Successfully!');
        }

        $productDetails = Product::where(['id'=>$id])->first();
        
        //category dropdown
        $categories = Category::where(['parent_id'=>0])->get();
        $categories_dropdown = "<option value='' selected disabled>Select</option>";
        foreach($categories as $cat)
        {
            if($cat->id==$productDetails->category_id)
            {
                $selected = "selected";
            }else
            {
                $selected = "";
            }

            $categories_dropdown .= "<option value='".$cat->id."' ".$selected.">".$cat->name."</option>";
            $sub_categories = Category::where(['parent_id'=>$cat->id])->get();
            foreach ($sub_categories as $sub_cat) 
            {
                if($sub_cat->id==$productDetails->category_id)
                {
                    $selected = "selected";
                }else
                {
                    $selected = "";
                }
                $categories_dropdown .= "<option value = '".$sub_cat->id."' ".$selected.">&nbsp;--&nbsp;".$sub_cat->name."</option>";
            }
        }

        return view('admin.products.edit_product')->with(compact('productDetails', 'categories_dropdown'));
    }

    //method to delete products
    public function deleteProduct($id = null)
    {
        Product::where(['id'=>$id])->delete();
        return redirect()->back()->with('flash_message_success','Product deleted Successfully!');
    }

    //deleting the product image
    public function deleteProductImage($id = null)
    {
        Product::where(['id'=>$id])->update(['image'=>'']);
        return redirect()->back()->with('flash_message_success', 'Product Image has been deleted successfully');
    }

    //method to add products attributes
    public function addAttributes(Request $request, $id=null)
    {
        $productDetails = Product::with('attributes')->where(['id'=>$id])->first();
        //$productDetails = json_decode(json_encode($productDetails));
        //echo "<pre>"; print_r($productDetails); die;
        if($request->isMethod('post'))
        {
            $data = $request->all();
            
            foreach($data['price'] as $key => $val)
            {
                if(!empty($val))
                {
                    $productAttributes = new ProductsAttribute;
                    $productAttributes->product_id = $id;
                    $productAttributes->price = $val;
                    $productAttributes->stock = $data['stock'][$key];
                    $productAttributes->save();
                }
            }
            return redirect('/admin/add-attribute/'.$id)->with('flash_message_success','Attribute has been added Successfully!');
        }
        return view('admin.products.add_attributes')->with(compact('productDetails'));
    }

    //deleting the attribute
    public function deleteAttribute($id = null)
    {
        ProductsAttribute::where(['id'=>$id])->delete();
        return redirect()->back()->with('flash_message_success', 'Attribute has been deleted successfully');
    }

}
