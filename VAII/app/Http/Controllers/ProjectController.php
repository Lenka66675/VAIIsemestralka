<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

class ProjectController extends Controller
{
    public function index()
    {

        $projects = Project::simplePaginate(16);
        return view('products.projects', compact('projects'));
    }

    public function create()
    {
        return view('products.projectsCreate');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|min:3|max:255',
            'description' => 'nullable|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'attachments.*' => 'nullable|file|max:10240'
        ], [
            'name.required' => 'Project name is required.',
            'name.min' => 'Project name must be at least 3 characters.',
            'name.max' => 'Project name cannot exceed 255 characters.',
            'description.max' => 'Description cannot be longer than 500 characters.',
            'image.mimes' => 'Only JPEG, PNG, JPG, and GIF images are allowed.',
            'image.max' => 'Image size cannot exceed 5MB.',
            'attachments.*.max' => 'Each attachment cannot exceed 10MB.'
        ]);

        $project = new Project();
        $project->name = $data['name'];
        $project->description = $data['description'] ?? '';

        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');
            $imageName = time() . '_' . preg_replace('/\s+/', '_', $imageFile->getClientOriginalName());
            $imagePath = $imageFile->storeAs('project_images', $imageName, 'public');
            $project->image = $imagePath;
        }

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $originalName = preg_replace('/\s+/', '_', $file->getClientOriginalName());
                $path = $file->storeAs('project_attachments', $originalName, 'public');
                $attachments[] = $path;
            }
        }
        $project->attachments = json_encode($attachments);

        $project->save();

        return redirect()->route('project')->with('success', 'Project created successfully!');
    }


    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'attachments.*' => 'nullable|file|max:10240'
        ]);

        $project->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? $project->description,
        ]);

        if ($request->hasFile('image')) {
            if ($project->image) {
                \Storage::disk('public')->delete($project->image);
            }

            $imageFile = $request->file('image');
            $imageName = preg_replace('/\s+/', '_', $imageFile->getClientOriginalName());
            $path = $imageFile->storeAs('project_images', $imageName, 'public');
            $project->update(['image' => $path]);
        }

        $attachments = json_decode($project->attachments, true) ?? [];

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $fileName = preg_replace('/\s+/', '_', $file->getClientOriginalName());
                $path = $file->storeAs('project_attachments', $fileName, 'public');
                $attachments[] = $path;
            }
            $project->update(['attachments' => json_encode($attachments)]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Project updated successfully!',
            'image' => $project->image ? asset('storage/' . $project->image) : null,
            'attachments' => collect(json_decode($project->attachments, true))->map(function ($path) {
                return asset('storage/' . $path);
            })->toArray()
        ], 200);

    }


    public function getProject(Project $project)
    {

        return response()->json([
            'id' => $project->id,
            'name' => $project->name,
            'description' => $project->description,
            'image' => asset('storage/' . $project->image),
        ]);
    }










    public function show(Project $project)
    {
        return view('products.projectShow', compact('project'));
    }

    public function edit(Project $project)
    {
        return view('products.projectsEdit', compact('project'));
    }





    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully!',
            'id' => $project->id
        ]);
    }

}
