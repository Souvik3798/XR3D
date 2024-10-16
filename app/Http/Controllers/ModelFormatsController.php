<?php

namespace App\Http\Controllers;

use App\Models\ModelFormats;
use App\Models\Model3D;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ModelFormatsController extends Controller
{
    public function create(Request $request, $model3dId)
    {
        $request->validate([
            'format' => 'required|string|max:255',
            'model_file' => 'required|file|mimetypes:application/json,obj,fbx,gltf,glb|max:30720', // 30MB max
            'model3d_id' => 'required|exists:model3ds,id',
        ]);

        $model3d = Model3D::findOrFail($model3dId);

        // Check if the authenticated user owns this model
        if ($model3d->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $modelFormat = new ModelFormats();
        $modelFormat->format = $request->format;
        $modelFormat->model3d_id = $model3dId;

        if ($request->hasFile('model_file')) {
            $file = $request->file('model_file');

            // Get the original extension
            $extension = $file->getClientOriginalExtension();

            // Generate a unique filename with the original extension
            $filename = uniqid() . '.' . $extension;

            // Store the file in the 'model_formats' folder on the 'public' disk
            $path = $file->storeAs('model_formats', $filename, 'public');

            $modelFormat->model_file = $path;
        }

        $modelFormat->save();

        return response()->json([
            'message' => 'Model format added successfully',
            'model_format' => $modelFormat
        ], 201);
    }


    public function update(Request $request, $id)
    {
        $modelFormat = ModelFormats::findOrFail($id);

        // Check if the authenticated user owns the parent model
        if ($modelFormat->model3d->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'format' => 'string|max:255',
            'model_file' => 'nullable|file|mimetypes:application/json,obj,fbx,gltf,glb|max:30720', // 30MB max
            'model3d_id' => 'exists:model3ds,id',
        ]);

        if ($request->has('format')) {
            $modelFormat->format = $request->format;
        }

        if ($request->has('model3d_id')) {
            $modelFormat->model3d_id = $request->model3d_id;
        }

        if ($request->hasFile('model_file')) {
            // Delete the old file if it exists
            if ($modelFormat->model_file) {
                Storage::disk('public')->delete($modelFormat->model_file);
            }

            $file = $request->file('model_file');

            // Get the original extension
            $extension = $file->getClientOriginalExtension();

            // Generate a unique filename with the original extension
            $filename = uniqid() . '.' . $extension;

            // Store the file in the 'model_formats' folder on the 'public' disk
            $path = $file->storeAs('model_formats', $filename, 'public');

            $modelFormat->model_file = $path;
        }

        $modelFormat->save();

        return response()->json([
            'message' => 'Model format updated successfully',
            'model_format' => $modelFormat
        ]);
    }


    public function delete($id)
    {
        $modelFormat = ModelFormats::findOrFail($id);

        // Check if the authenticated user owns the parent model
        if ($modelFormat->model3d->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete the file from storage
        if ($modelFormat->model_file) {
            Storage::disk('public')->delete($modelFormat->model_file);
        }

        $modelFormat->delete();

        return response()->json(['message' => 'Model format deleted successfully']);
    }

    public function show($id)
    {
        $modelFormat = ModelFormats::findOrFail($id);

        // Check if the authenticated user owns the parent model
        if ($modelFormat->model3d->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(['model_format' => $modelFormat]);
    }

    public function index($model3dId)
    {
        $model3d = Model3D::findOrFail($model3dId);

        // Check if the authenticated user owns this model
        if ($model3d->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $modelFormats = $model3d->modelFormats;

        return response()->json(['model_formats' => $modelFormats]);
    }
}
