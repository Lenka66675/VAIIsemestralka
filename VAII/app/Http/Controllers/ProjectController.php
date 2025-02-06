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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120', // Obrázok max 5MB
            'attachments.*' => 'file|max:10240' // Maximálna veľkosť prílohy: 10MB
        ]);

        $project = new Project();
        $project->name = $data['name'];
        $project->description = $data['description'] ?? '';

        // ✅ Uloženie obrázka (ak bol pridaný)
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('project_images', 'public');
            $project->image = $imagePath; // Uloženie do DB
        }

        if ($request->hasFile('attachments')) {
            $attachments = [];
            foreach ($request->file('attachments') as $file) {
                $filename = $file->getClientOriginalName(); // ⬅ Použijeme pôvodný názov bez ID
                $path = $file->storeAs('project_attachments', $filename, 'public');
                $attachments[] = $path;
            }
            $project->attachments = json_encode($attachments);
        }


        $project->save();

        return redirect()->route('project')->with('success', 'Project created successfully!');
    }







    public function show(Project $project)
    {
        return view('products.projectShow', compact('project'));
    }

    public function edit(Project $project)
    {
        return view('products.projectsEdit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:5120',
            'attachments.*' => 'nullable|file|max:10240'
        ]);

        $project->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? $project->description,
        ]);

        // Ak sa nahrá nový obrázok, uložíme ho
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('project_images', 'public');
            $project->update(['image' => $path]);
        }

        // Ak sa nahrali nové prílohy, aktualizujeme ich
        if ($request->hasFile('attachments')) {
            $attachments = json_decode($project->attachments, true) ?? [];
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('project_attachments', 'public');
                $attachments[] = $path;
            }
            $project->update(['attachments' => json_encode($attachments)]);
        }

        return redirect()->route('project')->with('success', 'Project updated successfully!');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('project')->with('success', 'Project deleted successfully!');
    }
}
