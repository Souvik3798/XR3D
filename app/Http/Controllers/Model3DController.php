<?php

namespace App\Http\Controllers;

use App\Models\Model3D;
use App\Models\ModelFormats;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Model3DController extends Controller
{
    public function create(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'model_file' => 'required|file|max:30720',
                'image' => 'nullable|file|max:30720', // Added validation for image

            ]);

            $model3D = new Model3D();
            $model3D->title = $validated['title'];
            $model3D->description = $validated['description'];
            $model3D->user_id = Auth::id();

            if ($request->hasFile('model_file')) {
                $file = $request->file('model_file');

                // Store the file in the 'models' folder without changing its name
                $path = $file->storeAs('models', $file->getClientOriginalName(), 'public');

                $model3D->model_file = $path;
            }

            if ($request->hasFile('image')) {
                $imageFile = $request->file('image');

                // Store the image file in the 'images' folder without changing its name
                $imagePath = $imageFile->storeAs('images', $imageFile->getClientOriginalName(), 'public');

                $model3D->image = $imagePath;
            }

            $model3D->save();

            return response()->json(['message' => 'Model uploaded successfully', 'model' => $model3D], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while creating the model', 'error' => $e->getMessage()], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $model3D = Model3D::findOrFail($id);

        // Check if the authenticated user owns this model
        if ($model3D->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validate the request
        $request->validate([
            'title' => 'string|max:255',
            'description' => 'string',
            'model_file' => 'nullable|file|max:30720', // 30MB max
            'image' => 'nullable|file|max:30720', // Added validation for image
        ]);


        // Update title if provided
        if ($request->has('title')) {
            $model3D->title = $request->title;
        }

        // Update description if provided
        if ($request->has('description')) {
            $model3D->description = $request->description;
        }

        // Handle file upload if a new model file is provided
        if ($request->hasFile('model_file')) {
            $file = $request->file('model_file');

            // Store the file in the 'models' folder without changing its name
            $path = $file->storeAs('models', $file->getClientOriginalName(), 'public');

            // Update the model's file path
            $model3D->model_file = $path;
        }

        // Handle image upload if a new image is provided
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');

            // Store the image file in the 'images' folder without changing its name
            $imagePath = $imageFile->storeAs('images', $imageFile->getClientOriginalName(), 'public');

            // Update the model's image path
            $model3D->image = $imagePath;
        }

        // Save the updated model
        try {
            $model3D->update();
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while updating the model', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Model updated successfully', 'model' => $model3D]);
    }


    public function delete($id)
    {
        try {
            $model3D = Model3D::findOrFail($id);

            // Check if the authenticated user owns this model
            if ($model3D->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Get the file path before deleting the model
            $filePath = $model3D->model_file;

            $model3D->delete();

            // Delete the associated file if it exists
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            return response()->json(['message' => 'Model and associated file deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while deleting the model', 'error' => $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        try {
            $model3D = Model3D::findOrFail($id);

            // Check if the authenticated user owns this model
            if ($model3D->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Get all model formats associated with this model
            $modelFormats = ModelFormats::where('model3d_id', $id)->get();

            return response()->json(['model' => $model3D, 'model_formats' => $modelFormats]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while editing the model', 'error' => $e->getMessage()], 500);
        }
    }

    public function listUserModels()
    {
        try {
            $models = Model3D::where('user_id', Auth::id())->get();

            return response()->json(['models' => $models]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while listing user models', 'error' => $e->getMessage()], 500);
        }
    }

    public function listAllModels()
    {
        try {
            $models = Model3D::all();

            return response()->json(['models' => $models]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while listing all models', 'error' => $e->getMessage()], 500);
        }
    }
}
