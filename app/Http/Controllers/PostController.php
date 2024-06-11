<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    function Create(Request $request){
        $post = new Post();
        $post -> content = $request -> post("content");
        $post -> author = $request -> post("author");
        if($request -> post("attachments"))
            $post -> attachments = $request -> post("attachments");
        $post -> save();
        return redirect("/") -> with("created", true);
    }
}
