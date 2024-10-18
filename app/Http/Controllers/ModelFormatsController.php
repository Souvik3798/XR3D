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
        try {
            $request->validate([
                'format' => 'required|string|max:255',
                'model_file' => 'required|file|max:30720', // 30MB max
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

                // Store the file in the 'model_formats' folder without changing its name
                $path = $file->storeAs('model_formats', $file->getClientOriginalName(), 'public');

                $modelFormat->model_file = $path;
            }

            $modelFormat->save();

            return response()->json([
                'message' => 'Model format added successfully',
                'model_format' => $modelFormat
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while creating the model format', 'error' => $e->getMessage()], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $modelFormat = ModelFormats::findOrFail($id);

            // Check if the authenticated user owns the parent model
            if ($modelFormat->model3d->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $request->validate([
                'format' => 'string|max:255',
                'model_file' => 'nullable|file|max:30720', // 30MB max
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

                // Store the file in the 'model_formats' folder without changing its name
                $path = $file->storeAs('model_formats', $file->getClientOriginalName(), 'public');

                $modelFormat->model_file = $path;
            }

            $modelFormat->save();

            return response()->json([
                'message' => 'Model format updated successfully',
                'model_format' => $modelFormat
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while updating the model format', 'error' => $e->getMessage()], 500);
        }
    }


    public function delete($id)
    {
        try {
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
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while deleting the model format', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $modelFormat = ModelFormats::findOrFail($id);

            // Check if the authenticated user owns the parent model
            if ($modelFormat->model3d->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return response()->json(['model_format' => $modelFormat]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while showing the model format', 'error' => $e->getMessage()], 500);
        }
    }

    public function index($model3dId)
    {
        try {
            $model3d = Model3D::findOrFail($model3dId);

            // Check if the authenticated user owns this model
            if ($model3d->user_id !== Auth::id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $modelFormats = $model3d->modelFormat;


            return response()->json(['model_formats' => $modelFormats]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while listing model formats', 'error' => $e->getMessage()], 500);
        }
    }
}
